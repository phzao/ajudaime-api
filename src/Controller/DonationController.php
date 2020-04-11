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
     * @Route("/api/v1/donations/{need_id}", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         $need_id,
                         NeedServiceInterface $needService,
                         DonationServiceInterface $donationService)
    {
        try {
            $user = $this->getUser();

            $donationService->ifExistADonationWithThisNeedMustFail($need_id, $user->getId());
            $donationService->thisDonationGoesBeyondTheProcessingLimitOfOrFail(3, $user->getId());

            $data = $request->request->all();

            $data["user"] = $user->getDataResume();
            $need = $needService->getNeedByIdOrFail($need_id);
            $data["need"] = $need;

            $donation = $donationService->register($data);

            $need["donation"] = $donationService->getDonationStatusIdCreated();

            $needService->updateAnyway($need);

            return $this->respondCreated($donation);

        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/donations", methods={"GET"})
     */
    public function listByUser(DonationServiceInterface $donationService)
    {
        try {
            $user = $this->getUser();
            $list = $donationService->getDonationsByUser($user->getId());

            return $this->respondSuccess($list);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/donations/{uuid}", methods={"GET"})
     */
    public function getDonation($uuid, DonationServiceInterface $donationService)
    {
        try {
            $user = $this->getUser();
            $donation = $donationService->getDonationByIdAndUserOrFail($uuid, $user->getId());

            return $this->respondSuccess($donation);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
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
            $needId = $donationService->cancelDonation($user->getId(), $uuid);

            $needService->removeDonationCanceled($needId);

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
            $needId = $donationService->doneDonation($user->getId(), $uuid);
            $need = $needService->getOneByIdOrFail($needId);

            $need["donation"] = $donationService->getDonationStatusIdCreated();

            $needService->updateAnyway($need);
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
    public function confirm($uuid,
                            DonationServiceInterface $donationService,
                            NeedServiceInterface $needService)
    {
        try {
            $user = $this->getUser();
            $needId = $donationService->needConfirmation($user->getId(), $uuid);

            $need = $needService->getOneByIdOrFail($needId);

            $need["donation"] = $donationService->getDonationStatusIdCreated();

            $needService->updateAnyway($need);
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