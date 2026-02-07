<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class SingleCorrectAnswerValidator extends ConstraintValidator
{
   public function validate($value, Constraint $constraint): void
    {
        /* @var App\Validator\SingleCorrectAnswer $constraint */

        if (null === $value || '' === $value) {
            return;
        }

        // $value is the Collection of choices from the Question entity
        $correctCount = 0;
        foreach ($value as $choice) {
            if ($choice->isCorrect()) { // We check the boolean flag
                $correctCount++;
            }
        }

        // Error 1: No correct answer
        if ($correctCount === 0) {
            $this->context->buildViolation($constraint->messageNoCorrect)
                ->addViolation();
        }

        // Error 2: Too many correct answers
        if ($correctCount > 1) {
            $this->context->buildViolation($constraint->messageTooManyCorrect)
                ->addViolation();
        }
    }
}
