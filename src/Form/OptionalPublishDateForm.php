<?php

namespace Drupal\c3_workflow\Form;

use Drupal\c3_workflow\Fields;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Helps to make the publish date optional.
 *
 * - Make the update_timestamp field optional on the ER entity form.
 * - Unsets the publish_date ER field has no value
 */
class OptionalPublishDateForm {

  /**
   * Alters the inline entity form to make the update_timestamp field optional.
   *
   * @param array $entity_form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $complete_form
   *
   * @return array
   *   The changed entity form.
   */
  public static function processInlineEntityForm($entity_form, FormStateInterface $form_state, &$complete_form) {
    $entity_form['update_timestamp']['widget']['#required'] = FALSE;
    $entity_form['update_timestamp']['widget'][0]['#required'] = FALSE;
    $entity_form['update_timestamp']['widget'][0]['value']['#required'] = FALSE;

    $entity_form['update_timestamp']['widget'][0]['value']['#description'] = '';
    return $entity_form;
  }

  public function buildEntity($entity_type_id, ContentEntityInterface $entity, &$form, FormStateInterface $form_state) {
    if ($entity->hasField(Fields::PUBLICATION_DATE_FIELD) && ($publish_date_entity = $entity->{Fields::PUBLICATION_DATE_FIELD}->entity)) {
      $key = [Fields::PUBLICATION_DATE_FIELD, 0, 'inline_entity_form', 'update_timestamp', 0, 'value'];
      // Note: We use -1 as default value here in order to ensure the value is
      // really NULL
      $value = $form_state->getValue($key, -1);
      if ($value === NULL) {
        // Empty the field.
        $entity->{Fields::PUBLICATION_DATE_FIELD}->setValue([]);
      }
    }
  }

}
