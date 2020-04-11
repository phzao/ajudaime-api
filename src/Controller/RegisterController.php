<?php

namespace App\Controller;

use App\Services\Entity\Interfaces\UserServiceInterface;
use App\Services\External\Google\GoogleCheckServiceInterface;
use App\Services\Login\LoginServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 */
class RegisterController extends APIController
{
    /**
     * @Route("/google-authenticate", methods={"POST"})
     * @throws \Exception
     */
    public function loginGoogle(Request $request,
                                GoogleCheckServiceInterface $googleCheckService,
                                UserServiceInterface $userService,
                                LoginServiceInterface $loginService)
    {
        try {
            $data = $request->request->all();
            $googleCheckService->requestHasNameEmailAndAccessTokenOrFail($data);
            $googleCheckService->isValidGoogleAccessTokenOrFail($data);

            $user = $userService->getUserByEmailAnyway($data);

            $loginData = $loginService->getTokenCreateIfNotExist($user, $data);

            $loginData["user"] = $user;

            return $this->respondSuccess($loginData);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }

    /**
     * @Route("/public/login/teste", methods={"POST"})
     * @throws \Exception
     */
    public function teste(Request $request,
                          UserServiceInterface $userService,
                          LoginServiceInterface $loginService)
    {
        try {
            $data = $request->request->all();
            $user = $userService->getUserByEmailAnyway($data);

            $loginData = $loginService->getTokenCreateIfNotExist($user, $data);


            $loginData["user"] = $user;

            return $this->respondSuccess($loginData);

        } catch (UnauthorizedHttpException $exception) {

            return $this->respondForbiddenFail($exception->getMessage());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}