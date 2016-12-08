<?php

namespace Drupal\c3_workflow;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\scheduled_updates\Entity\ScheduledUpdate;

/**
 * Adapts the \Drupal\workbench_moderation\Form\EntityModerationForm
 *
 * - It includes the publish date.
 */
class CustomEntityModerationForm {

  use StringTranslationTrait;

  public function alter(&$form, FormStateInterface $form_state) {
    $form['submit']['#weight'] = 10;


    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $form_state->getBuildInfo()['args'][0];
    /** @var \Drupal\Core\Entity\ContentEntityInterface $default_revision_entity */
    $default_revision_entity = \Drupal::entityTypeManager()->getStorage($entity->getEntityTypeId())->load($entity->id());

    // Some entity types might not have the publish_date field.
    if (!$entity->hasField('publish_date')) {
      return;
    }

    $hide_publish_date = $default_revision_entity->hasField('field_has_been_published') && $default_revision_entity->get('field_has_been_published')->value;

    $publish_timestamp = FALSE;
    /** @var \Drupal\scheduled_updates\Entity\ScheduledUpdate $publish_date */
    if (($publish_date = $entity->get('publish_date')->entity) && !$publish_date->get('update_timestamp')->isEmpty()) {
      $publish_timestamp = $entity->get('publish_date')->entity->get('update_timestamp')->value;
    }

    $form['publish_date'] = [
      '#weight' => 5,
      '#type' => 'datetime',
      '#title' => t('Publish Date'),
      '#description' => t('Optionally, enter a date this article should be published. If omitted, the date will be set automatically when an editor publishes it.'),
      '#default_value' => $publish_timestamp ? DrupalDateTime::createFromTimestamp($publish_timestamp) : FALSE,
      '#access' => !$hide_publish_date,
    ];
    $form['submit']['#validate'][] = static::class . '::validateEntityModerationForm';
    $form['submit']['#submit'] = [static::class . '::submitEntityModerationForm'];
  }

  public static function validateEntityModerationForm($form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Datetime\DrupalDateTime $publish_date */
    if ($publish_date = $form_state->getValue('publish_date')) {
      if ($publish_date->format('U') < REQUEST_TIME) {
        $form_state->setErrorByName('publish_date', t('Date is in the past'));
      }
    }
  }

  /**
   * {@inheritdoc]
   */
  public static function submitEntityModerationForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $form_state->get('entity');

    $new_state = $form_state->getValue('new_state');
    $entity->get('moderation_state')->target_id = $new_state;

    /** @var \Drupal\Core\Datetime\DrupalDateTime $publish_date */
    if ($publish_date = $form_state->getValue('publish_date')) {
      if ($entity->get('publish_date')->isEmpty()) {
        $entity->get('publish_date')->entity = ScheduledUpdate::create([
          'type' => 'publish_date',
          'entity_ids' => $entity->id(),
        ]);
      }
      $entity->get('publish_date')->entity->get('update_timestamp')->value = $publish_date->format('U');
      $entity->get('publish_date')->entity->save();
    }

    // @fixme

    $entity->revision_log = $form_state->getValue('revision_log');

    $entity->save();

    drupal_set_message(t('The moderation state has been updated.'));

    /** @var \Drupal\content_moderation\Entity\ModerationState $state */
    $state = \Drupal::entityTypeManager()
      ->getStorage('moderation_state')
      ->load($new_state);

    // The page we're on likely won't be visible if we just set the entity to
    // the default state, as we hide that latest-revision tab if there is no
    // forward revision. Redirect to the canonical URL instead, since that will
    // still exist.
    if ($state->isDefaultRevisionState()) {
      $form_state->setRedirectUrl($entity->toUrl('canonical'));
    }
  }

}
