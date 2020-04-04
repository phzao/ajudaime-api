<?php

namespace App\Validator\Constraints;

use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LongitudeNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LongitudeNumber) {
            throw new UnexpectedTypeException($constraint, LongitudeNumber::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->isValidLongitude($value)) {

            $msg = ErrorMessage::getArrayMessageToJson(["longitude"=>"Longitude inválido"]);
            throw new UnprocessableEntityHttpException($msg);
        }
    }

    public function isValidLongitude($long) {

        $long_array = explode( '.' , $long );

        if( sizeof($long_array) !=2 ){
            return false;
        }

        if (!( is_numeric($long_array[0]) &&
            $long_array[0]==round($long_array[0], 0) &&
            is_numeric($long_array[1]) && $long_array[1]==round($long_array[1], 0)  ) ){
            return false;
        }

        if( $long >= -180 && $long <= 180 ){
            return true;
        }
        else {
            return false;
        }

    }
}