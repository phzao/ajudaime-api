<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\NeedServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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

            $needService->thisNeedGoesBeyondTheOpensLimitOfOrFail(3, $user->getId());

            $data["user"] = $user->getDataResume();
            $needs = $needService->register($data);

            return $this->respondCreated($needs);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
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
                           NeedServiceInterface $needService)
    {
        try {
            $user = $this->getUser();
            $needService->remove($uuid, $user->getId());

            return $this->respondUpdatedResource();
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
    public function listNotCanceled(NeedServiceInterface $needService)
    {
        try {
            $list = $needService->getAllNeedsNotCanceled();

            return $this->respondSuccess($list);
        } catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}