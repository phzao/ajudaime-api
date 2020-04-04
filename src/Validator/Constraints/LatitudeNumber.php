<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LatitudeNumber extends Constraint
{
    public $message = 'Latitude "{{ string }}" é inválido.';
}