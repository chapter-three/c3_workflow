<?php

namespace Drupal\c3_workflow\Element;

use Drupal\c3_workflow\Fields;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Element\InlineEntityForm;

/**
 * The idea of this element is not make it possible to have an optional entity.
 *
 * Whether an entity is optional or not is calculated inside
 * \Drupal\c3_workflow\Element\OptionalInlineEntityForm::inputWasEmpty().
 *
 * @RenderElement("inline_entity_form__optional")
 */
class OptionalInlineEntityForm extends InlineEntityForm {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_called_class();
    $info = parent::getInfo();
    $info['#element_validate'] = [
      [$class, 'validateEntityForm'],
    ];
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateEntityForm(&$entity_form, FormStateInterface $form_state) {
    if (static::inputWasEmpty($form_state)) {
      return;
    }
    return parent::validateEntityForm($entity_form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public static function submitEntityForm(&$entity_form, FormStateInterface $form_state) {
    if (static::inputWasEmpty($form_state)) {
      return;
    }
    return parent::submitEntityForm($entity_form, $form_state);
  }

  /**
   * Determines whether the input was in a way that the entity is not needed.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   */
  protected static function inputWasEmpty(FormStateInterface $form_state) {
    $value = $form_state->getValue([Fields::PUBLICATION_DATE_FIELD, 0, 'inline_entity_form', 'update_timestamp']);
    if (!empty($value)) {
      return $value[0]['value'] === NULL;
    }
    return FALSE;
  }

}
