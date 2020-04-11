<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\DonationServiceInterface;
use App\Services\Entity\Interfaces\NeedServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

class NeedController extends APIController
{
    /**
     * @Route("/api/v1/needs", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         NeedServiceInterface $needService)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();

            $needService->thisNeedGoesBeyondTheOpensLimitOfOrFail(1, $user->getId());

            $data["user"] = $user->getDataResume();
            $needs = $needService->register($data);

            return $this->respondCreated($needs);

        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/needs/{uuid}", methods={"PUT"})
     */
    public function update(Request $request,
                           $uuid,
                           NeedServiceInterface $needService)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();
            $data["id"] = $uuid;
            $needService->update($data, $user->getDataResume());

            return $this->respondUpdatedResource();
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/needs/{uuid}", methods={"DELETE"})
     */
    public function remove($uuid,
                           NeedServiceInterface $needService,
                           DonationServiceInterface $donationService)
    {
        try {
            $user = $this->getUser();
            $needService->remove($uuid, $user->getId());
            $donation_id = $needService->getDonationId();

            $donationService->cancelDonationById($donation_id);

            return $this->respondUpdatedResource();
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/needs/{uuid}", methods={"GET"})
     */
    public function getOne($uuid, NeedServiceInterface $needService)
    {
        try {
            $user = $this->getUser();
            $need = $needService->getOneByIdAndUserOrFail($uuid, $user->getId());

            return $this->respondSuccess($need);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/api/v1/needs", methods={"GET"})
     */
    public function listUserNeeds(NeedServiceInterface $needService)
    {
        try {
            $user = $this->getUser();
            $list = $needService->getNeedsListByUser($user->getId());

            return $this->respondSuccess($list);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }


    /**
     * @Route("/public/needs/{user_id}/user", methods={"GET"})
     */
    public function list($user_id, NeedServiceInterface $needService)
    {
        try {
            $list = $needService->getNeedsListByUser($user_id);

            return $this->respondSuccess($list);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/public/needs", methods={"GET"})
     */
    public function listNotCanceled(Request $request, NeedServiceInterface $needService)
    {
        try {

            $data = $request->query->all();

            $list = $needService->getAllNeedsNotCanceledByCountryOrFail($data);

            return $this->respondSuccess($list);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}