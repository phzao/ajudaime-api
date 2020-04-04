<?php

namespace App\Controller;

use App\Services\Log\Interfaces\LoggerServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @package App\Controller
 */
class APIController extends AbstractController
{
    protected $statusCode = Response::HTTP_OK;

    protected $statusType = 'success';

    protected $responseMessage = [];

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    protected function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    protected function setStatusType(string $statusType)
    {
        $this->statusType = $statusType;

        return $this;
    }

    private function setArrayResponse($data, $namePayload = 'data')
    {
        $this->responseMessage = [
            'status' => $this->statusType,
            $namePayload => $data
        ];

        return $this;
    }

    public function respond($headers = [])
    {
        return new JsonResponse($this->responseMessage,
                                $this->statusCode,
                                $headers);
    }

    public function respondSuccess($data, $headers = [])
    {
        return $this->setArrayResponse($data)
                    ->respond($headers);
    }

    public function respondValidationCustomFail(string $message)
    {
        return $this->setStatusType('fail')
                    ->setArrayResponse($message, 'message')
                    ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
                    ->respond();
    }

    public function respondValidationFail(string $message)
    {
        $errors   = json_decode($message, true);

        return $this->setStatusType('fail')
                    ->setArrayResponse($errors, 'data')
                    ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY)
                    ->respond();
    }

    public function respondCreated($data, $headers = [])
    {
        return $this
                    ->setStatusCode(Response::HTTP_CREATED)
                    ->setArrayResponse($data)
                    ->respond($headers);
    }

    public function respondUpdatedResource()
    {
        return $this->setStatusCode(Response::HTTP_NO_CONTENT)
                    ->respond();
    }

    public function respondNotAllowedError(string $data, $headers = [])
    {
        return $this->setStatusType('error')
                    ->setArrayResponse($data, 'message')
                    ->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED)
                    ->respond($headers);

    }

    public function respondBadRequestError(string $data, $headers = [])
    {
        return $this->setStatusType('error')
                    ->setArrayResponse($data, 'message')
                    ->setStatusCode(Response::HTTP_BAD_REQUEST)
                    ->respond($headers);
    }

    public function respondNotFoundError($message = 'Not found!')
    {
        return $this
                    ->setStatusType('error')
                    ->setStatusCode(Response::HTTP_NOT_FOUND)
                    ->setArrayResponse($message, 'message')
                    ->respond();
    }

    public function respondInvalidCredentialsFail(string $message, $headers = [])
    {

        return $this
                    ->setStatusType('fail')
                    ->setStatusCode(Response::HTTP_UNAUTHORIZED)
                    ->setArrayResponse($message, 'message')
                    ->respond($headers);
    }

    public function respondForbiddenFail(string $message, $headers = [])
    {
        return $this
            ->setStatusType('fail')
            ->setStatusCode(Response::HTTP_FORBIDDEN)
            ->setArrayResponse($message, 'message')
            ->respond($headers);
    }
}