<?php

namespace App\Validator\Constraints;

use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LatitudeNumberValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LatitudeNumber) {
            throw new UnexpectedTypeException($constraint, LatitudeNumber::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || '' === $value) {
            return;
        }

        if (!$this->isValidLatitude($value)) {

            $msg = ErrorMessage::getArrayMessageToJson(["latitude"=>"Latitude inválido"]);
            throw new UnprocessableEntityHttpException($msg);
        }
    }

    public function isValidLatitude($lat) {


        $lat_array = explode( '.' , $lat );

        if( sizeof($lat_array) !=2 ){
            return false;
        }

        if ( ! ( is_numeric($lat_array[0]) &&
            $lat_array[0]==round($lat_array[0], 0) &&
            is_numeric($lat_array[1]) &&
            $lat_array[1]==round($lat_array[1], 0)  ) ){
            return false;
        }

        if( $lat >= -90 && $lat <= 90 ){
            return true;
        }
        else {
            return false;
        }

    }
}