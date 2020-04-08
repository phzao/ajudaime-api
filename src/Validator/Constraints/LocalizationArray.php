<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LocalizationArray extends Constraint
{
    public $message = 'Localização "{{ string }}" é inválido.';
}