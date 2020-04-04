<?php

namespace App\Services\Validation;

use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @package App\Services\Validation
 */
class ValidationService implements ValidateModelInterface
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function entityIsValidOrFail($model)
    {
        $errors = $this->validator->validate($model);

        if (!$errors instanceof ConstraintViolationList) {
            return ;
        }

        if ($errors->count() < 1) {
            return;
        }

        $errorList = [];

        foreach($errors->getIterator() as $error)
        {
            $incorrectAttribute = $error->getPropertyPath();
            $messageError = $error->getMessage();
            $errorList[$incorrectAttribute] = $messageError;
        }

        $msg = ErrorMessage::getArrayMessageToJson($errorList);

        throw new UnprocessableEntityHttpException($msg);
    }
}