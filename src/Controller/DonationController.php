<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\DonationServiceInterface;
use App\Services\Entity\Interfaces\NeedServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

class DonationController extends APIController
{
    /**
     * @Route("/api/v1/donations", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         NeedServiceInterface $needService,
                         DonationServiceInterface $donationService)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();

            $donationService->ifExistADonationWithThisNeedMustFail($data, $user->getId());
            $donationService->thisDonationGoesBeyondTheProcessingLimitOfOrFail(3, $user->getId());

            $data["user"] = $user->getDataResume();
            $need = $needService->getNeedByIdOrFail($data);
            $data["need"] = $need;

            $donation = $donationService->register($data);

            $need["donation"] = $donationService->getDonationStatusIdCreated();

            $needService->updateAnyway($need);

            return $this->respondCreated($donation);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/donations/{uuid}/cancel", methods={"PUT"})
     * @throws \Exception
     */
    public function cancel($uuid,
                           NeedServiceInterface $needService,
                           DonationServiceInterface $donationService)
    {
        try {
            $user = $this->getUser();
            $donation = $donationService->cancelDonation($user->getId(), $uuid);

            $needService->removeDonationCanceled($donation["need"]["id"]);

            return $this->respondUpdatedResource();

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/donations/{uuid}/done", methods={"PUT"})
     * @throws \Exception
     */
    public function done($uuid,
                         DonationServiceInterface $donationService,
                         NeedServiceInterface $needService)
    {
        try {
            $user = $this->getUser();
            $donation = $donationService->doneDonation($user->getId(), $uuid);

            $needService->setNeedDone($donation["need"]["id"],
                                      $donationService->getDonationStatusIdCreated());

            return $this->respondUpdatedResource();

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/donations/{uuid}/confirm", methods={"PUT"})
     * @throws \Exception
     */
    public function confirm($uuid, DonationServiceInterface $donationService)
    {
        try {
            $user = $this->getUser();
            $donationService->needConfirmation($user->getId(), $uuid);

            return $this->respondUpdatedResource();

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/public/donations/{user_id}/user", methods={"GET"})
     */
    public function list($user_id, DonationServiceInterface $donationService)
    {
        try {
            $list = $donationService->getDonationsByUser($user_id);

            return $this->respondSuccess($list);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/public/donations/{status}/status", methods={"GET"})
     */
    public function listBy($status, DonationServiceInterface $donationService)
    {
        try {
            $list = $donationService->getDonationsByStatus($status);

            return $this->respondSuccess($list);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}