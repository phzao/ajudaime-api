<?php

namespace App\Controller;

use App\Repository\Interfaces\UserRepositoryInterface;
use App\Services\Entity\Interfaces\UserServiceInterface;
use App\Services\Log\Interfaces\LoggerServiceInterface;
use App\Services\Login\LoginServiceInterface;
use App\Services\Validation\ValidationService;
use App\Utils\Enums\GeneralTypes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @package App\Controller
 * @Route("/api/v1/users")
 */
class UserController extends APIController
{

//
//    /**
//     * @Route("/me", methods={"GET"})
//     */
//    public function show()
//    {
//        try {
//            $user = $this->getUser();
//
//            return $this->respondSuccess($user->getFullData());
//
//        } catch (\Exception $exception) {
//
//            return $this->respondBadRequestError($exception->getMessage());
//        }
//    }
//
//    /**
//     * @Route("", methods={"PUT"})
//     */
//    public function update(Request $request,
//                           ValidationService $validationService,
//                           LoginServiceInterface $loginService,
//                           UserRepositoryInterface $userRepository,
//                           UserPasswordEncoderInterface $passwordEncoder)
//    {
//        try {
//
//            $user = $this->getUser();
//            $data = $request->request->all();
//
//            $loginService->checkIfExistPasswordAndIsItRightOrFail($user, $data);
//
//            $data = $user->changePasswordIfWantTo($data);
//            $data = $user->removeEmailIfExist($data);
//
//            $user->setAttributes($data);
//
//            $validationService->entityIsValidOrFail($user);
//
//            $user->encryptPassword($passwordEncoder);
//            $userRepository->save($user);
//
//            return $this->respondUpdatedResource();
//
//        } catch (UnprocessableEntityHttpException $exception) {
//
//            return $this->respondValidationFail($exception->getMessage());
//        }  catch (NotFoundHttpException $exception) {
//
//            return $this->respondNotFoundError($exception->getMessage());
//        } catch (\Exception $exception) {
//
//            return $this->respondBadRequestError($exception->getMessage());
//        }
//    }
//
//    /**
//     * @Route("/my-status-to/{status}", methods={"PUT"})
//     */
//    public function updateStatus(string $status,
//                                 UserServiceInterface $userService)
//    {
//        try {
//
//            GeneralTypes::isValidDefaultStatusOrFail($status);
//
//            $userService->updateStatus($this->getUser(), $status);
//
//            return $this->respondUpdatedResource();
//
//        } catch (UnprocessableEntityHttpException $exception) {
//
//            return $this->respondValidationFail($exception->getMessage());
//        }  catch (NotFoundHttpException $exception) {
//
//            return $this->respondNotFoundError($exception->getMessage());
//        } catch (\Exception $exception) {
//
//            return $this->respondBadRequestError($exception->getMessage());
//        }
//    }
}