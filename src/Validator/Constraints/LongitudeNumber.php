<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LongitudeNumber extends Constraint
{
    public $message = 'Longitude "{{ string }}" é inválido.';
}