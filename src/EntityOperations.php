<?php

namespace Drupal\c3_workflow;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\workbench_moderation\EntityOperations as BaseEntityOperations;
use Drupal\workbench_moderation\Form\EntityModerationForm;

/**
 * Overrides the entityView method.
 */
class EntityOperations extends BaseEntityOperations {

  /**
   * Overrides the parent to show the form on all non published revisions.
   */
  public function entityView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    if (!$this->moderationInfo->isModeratableEntity($entity)) {
      return;
    }
    if (!$this->moderationInfo->isLatestRevision($entity)) {
      return;
    }
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    /** @var \Drupal\workbench_moderation\Entity\ModerationState $moderation_state */
    if (($moderation_state = $entity->get('moderation_state')->entity) && $moderation_state->isPublishedState()) {
      return;
    }

    $component = $display->getComponent('workbench_moderation_control');
    if ($component) {
      $build['workbench_moderation_control'] = $this->formBuilder->getForm(EntityModerationForm::class, $entity);
      $build['workbench_moderation_control']['#weight'] = $component['weight'];
    }
  }
}
