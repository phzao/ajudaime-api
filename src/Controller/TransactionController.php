<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\NeedServiceInterface;
use App\Services\Entity\Interfaces\TransactionServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 * @Route("/api/v1/transactions")
 */
class TransactionController extends APIController
{
    /**
     * @Route("", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         NeedServiceInterface $needService,
                         TransactionServiceInterface $transactionService)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();

            $transactionService->ifExistATransactionWithThisNeedMustFail($data, $user->getId());
            $transactionService->thisTransactionGoesBeyondTheProcessingLimitOfOrFail(3, $user->getId());

            $data["user"] = $user->getDataResume();
            $data["need"] = $needService->getNeedByIdOrFail($data);

            $transaction = $transactionService->register($data);

            return $this->respondSuccess($transaction);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}