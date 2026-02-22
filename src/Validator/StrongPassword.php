<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class StrongPassword extends Constraint
{
    public string $message = 'The password does not meet the security requirements.';
    public string $tooWeakMessage = 'The password is too weak. Please choose a stronger password.';
}
