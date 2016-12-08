<?php

namespace Drupal\c3_workflow\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Ensures that a timestamp is in the future.
 *
 * @Constraint(
 *   id = "FutureTimestamp",
 *   label = @Translation("Future timestamp", context = "Validation"),
 * )
 */
class FutureTimestamp extends Constraint implements ConstraintValidatorInterface {

  public $message = "Date is in the past";

  protected $bundle = [];

  /**
   * Stores the validator's state during validation.
   *
   * @var \Symfony\Component\Validator\ExecutionContextInterface
   */
  protected $context;

  /**
   * {@inheritdoc}
   */
  public function initialize(ExecutionContextInterface $context) {
    $this->context = $context;
  }

  /**
   * {@inheritdoc}
   */
  public function validatedBy() {
    return static::class;
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    /** @var \Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem $value */
    $timestamp = $value->value;
    if ($this->bundle && !in_array($value->getEntity()->bundle(), $this->bundle, TRUE)) {
      return;
    }

    if ($timestamp < REQUEST_TIME) {
      $this->context->addViolation($this->message);
    }
  }

}
