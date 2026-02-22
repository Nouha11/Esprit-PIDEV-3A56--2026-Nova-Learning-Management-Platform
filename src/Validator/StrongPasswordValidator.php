<?php

namespace App\Validator;

use App\Service\PasswordPolicyService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StrongPasswordValidator extends ConstraintValidator
{
    public function __construct(
        private PasswordPolicyService $passwordPolicyService
    ) {}

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof StrongPassword) {
            throw new UnexpectedTypeException($constraint, StrongPassword::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        $result = $this->passwordPolicyService->validatePassword($value);

        if (!$result['valid']) {
            foreach ($result['errors'] as $error) {
                $this->context->buildViolation($error)
                    ->addViolation();
            }
        }
    }
}
