<?php

namespace App\Tests\Services\Validation;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class ValidationServiceTest
 * @package App\Tests\Services\Validation
 */
class ValidationServiceTest extends WebTestCase
{
    use LoadValidationService;

    public function testValidationFail()
    {
        $user = new User();
        $validationService = $this->getValidationService();

        $this->expectException(UnprocessableEntityHttpException::class);
        $validationService->entityIsValidOrFail($user);
    }
}