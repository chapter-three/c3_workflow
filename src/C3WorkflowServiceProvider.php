<?php

namespace Drupal\c3_workflow;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

class C3WorkflowServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $container->getDefinition('workbench_moderation.entity_operations')
      ->setClass(EntityOperations::class);
  }

}
