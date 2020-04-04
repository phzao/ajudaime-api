<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\TalkServiceInterface;
use App\Services\Entity\Interfaces\TransactionServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 */
class TalkController extends APIController
{
    /**
     * @Route("/api/v1/talks/{transaction_id}", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         $transaction_id,
                         TransactionServiceInterface $transactionService,
                         TalkServiceInterface $talkService)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();
            $transaction = $transactionService->getTransactionIdOrFail($transaction_id);

            $talkService->thisTalkGoesBeyondTheUnreadLimitOfOrFail(5,
                                                                   $user->getId(),
                                                                   $transaction_id);

            $data["transaction"] = $transaction;
            $data["origin"] = $user->getId();

            $talk = $talkService->register($data);

            return $this->respondSuccess($talk);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}