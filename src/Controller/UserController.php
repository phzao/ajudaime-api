<?php

namespace App\Controller;

use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\Extract\ExtractDataSameKeyInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 * @Route("/api/v1/users")
 */
class UserController extends APIController
{
    /**
     * @Route("", methods={"PUT"})
     */
    public function update(Request $request,
                           ValidateModelInterface $validateModel,
                           ElasticSearchRepositoryInterface $repository,
                           ExtractDataSameKeyInterface $extractDataSameKey)
    {
        try {
            $data = $request->request->all();
            $user = $this->getUser();

            $fieldsAllowedUpdate = $user->getFieldsAllowedUpdate();
            $data = $extractDataSameKey->getDataOnTheSameKeys($data, $fieldsAllowedUpdate);

            $user->setAttributes($data);
            $validateModel->entityIsValidOrFail($user);

            $userUpdated = $user->getFullDataToUpdateIndex();
            $repository->update($userUpdated);

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
     * @Route("", methods={"GET"})
     */
    public function showMe()
    {
        try {

            $user = $this->getUser();

            return $this->respondSuccess($user->getOriginalData());
        } catch (UnprocessableEntityHttpException $exception) {

            return $this->respondValidationFail($exception->getMessage());
        }  catch (NotFoundHttpException $exception) {

            return $this->respondNotFoundError($exception->getMessage());
        } catch (\Exception $exception) {

            return $this->respondBadRequestError($exception->getMessage());
        }
    }
}