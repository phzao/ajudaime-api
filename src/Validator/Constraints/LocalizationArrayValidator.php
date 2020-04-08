<?php

namespace App\Validator\Constraints;

use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class LocalizationArrayValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LocalizationArray) {
            throw new UnexpectedTypeException($constraint, LocalizationArray::class);
        }

        // custom constraints should ignore null and empty values to allow
        // other constraints (NotBlank, NotNull, etc.) take care of that
        if (null === $value || [] === $value) {
            return;
        }

        if (!$this->isValidLatitude($value[0])) {

            $msg = ErrorMessage::getArrayMessageToJson(["localization"=>"Latitude inválida"]);
            throw new UnprocessableEntityHttpException($msg);
        }

        if (!$this->isValidLongitude($value[1])) {

            $msg = ErrorMessage::getArrayMessageToJson(["localization"=>"Longitude inválida"]);
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