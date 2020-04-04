<?php

namespace App\Services\Entity;

use App\Entity\User;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\UserServiceInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Enums\GeneralTypes;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @package App\Services\Entity
 */
final class UserService implements UserServiceInterface
{
    /**
     * @var ElasticSearchRepositoryInterface
     */
    private $repository;

    private $passwordEncoder;

    /**
     * @var User
     */
    private $user;

    private $elasticQueries;

    private $validationModel;

    private $userIndex;

    /**
     * @throws \Exception
     */
    public function __construct(ElasticSearchRepositoryInterface $elasticSearchRepository,
                                ElasticSearchQueriesInterface $elasticSearchQueries,
                                ValidateModelInterface $validateModel,
                                UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->repository = $elasticSearchRepository;
        $this->passwordEncoder = $userPasswordEncoder;
        $this->user = new User();
        $this->elasticQueries = $elasticSearchQueries;
        $this->validationModel = $validateModel;

        $user_index = $this->user->getElasticIndexName();

        if (!$this->repository->isIndexExist($user_index)) {
            $mapping = $this->user->getElasticSearchMapping();
            $this->repository->index($mapping);
        }

        $this->userIndex = $user_index["index"];
    }

    /**
     * @throws \Exception
     */
    public function getUserByEmailAnyway(array $data): ?array
    {

        $query = $this->elasticQueries->getQueryToSingleSearch($this->userIndex, $data["email"], "email");

        $query["size"] = 1;
        $user = $this->repository->getOneBy($query);

        if (!empty($user)) {
            if ($user["status"] === GeneralTypes::STATUS_BLOCKED) {
                throw new UnauthorizedHttpException("User blocked!");
            }

            if ($user["status"] === GeneralTypes::STATUS_DISABLE) {
                throw new UnauthorizedHttpException("User disable!");
            }

            if ($user["deleted_at"] !== "NULL") {
                throw new UnauthorizedHttpException("User not exist!");
            }

            return $user;
        }

        $this->user->setAttributes($data);

        $this->validationModel->entityIsValidOrFail($this->user);

        $user = $this->user->getDataToInsert();

        $id = $this->repository->index($user);

        $this->user->setAttribute('id', $id);
        $userUpdated = $this->user->getFullDataToUpdateIndex();

        $this->repository->update($userUpdated);

        return $this->user->getOriginalData();
    }

    public function getUserById(string $id): array
    {
        $params = [
            "index" => $this->userIndex,
            "id" => $id
        ];

        return $this->repository->get($params);
    }

    public function update(array $user): void
    {
        $user_index = $this->user->getElasticIndexName();
        $this->repository->update($user);
    }
}