<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\NeedServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 * @Route("/api/v1/needs")
 */
class NeedController extends APIController
{
    /**
     * @Route("", methods={"POST"})
     * @throws \Exception
     */
    public function save(Request $request,
                         NeedServiceInterface $needService)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();

            $needService->ifThisNeedListExistAndIsOpenMustFail($user->getId(), $data);
            $needService->thisNeedGoesBeyondTheOpensLimitOfOrFail(3, $user->getId());

            $data["user"] = $user->getDataResume();
            $needs = $needService->register($data);

            return $this->respondSuccess($needs);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}