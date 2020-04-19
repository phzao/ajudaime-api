<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\ApiTokenInterface;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\ApiTokenServiceInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Generators\TokenGeneratorInterface;

/**
 * @package App\Services\Entity
 */
final class ApiTokenService implements ApiTokenServiceInterface
{
    private $repository;

    private $tokenGenerator;

    private $apiToken;

    private $elasticQueries;

    public function __construct(ElasticSearchRepositoryInterface $elasticSearchRepository,
                                ElasticSearchQueriesInterface $elasticSearchQueries,
                                ApiTokenInterface $apiToken,
                                TokenGeneratorInterface $tokenGenerator)
    {
        $this->repository = $elasticSearchRepository;
        $this->tokenGenerator = $tokenGenerator;
        $this->apiToken = $apiToken;
        $this->elasticQueries = $elasticSearchQueries;

        $apiTokenIndex = $this->apiToken->getElasticIndexName();

        if (!$this->repository->isIndexExist($apiTokenIndex)) {
            $mapping = $apiToken->getElasticSearchMapping();
            $this->repository->index($mapping);
        }

        $this->elasticQueries->setIndex($apiTokenIndex["index"]);
    }

    /**
     * @throws \Exception
     */
    public function registerAndGetApiTokenTo(string $user, array $data): array
    {
        $this->apiToken->setUser($user);
        $this->apiToken->setAttributes($data);
        $this->apiToken->generateToken($this->tokenGenerator);

        $apiToken = $this->apiToken->getDataToInsert();

        $id = $this->repository->index($apiToken);

        $token = $this->apiToken->getDetailsToken();
        $token["id"] = $id;

        return $token;
    }

    public function getAValidApiTokenToUser(string $user_id): array
    {
        $params = [
            "user" => $user_id,
            "expired_at" => 'NULL'
        ];

        $this->elasticQueries->setIndex($this->apiToken->getIndexName());

        $query = $this->elasticQueries->getBoolMustMatchBy($params);

        return $this->repository->getOneBy($query);
    }

    public function getTokenNotExpiredBy(string $token): array
    {
        $params = [
            "token" => $token,
            "expired_at" => 'NULL'
        ];

        $this->elasticQueries->setIndex($this->apiToken->getIndexName());
        $query = $this->elasticQueries->getBoolMustMatchBy($params);

        return $this->repository->getOneBy($query);
    }

    public function invalidateToken(array $apiToken)
    {
        $apiToken["expired_at"] = new \DateTime();

        $this->apiToken->setAttributes($apiToken);
        $apiTokenUpdated = $this->apiToken->getFullDataToUpdateIndex();
        $this->repository->update($apiTokenUpdated);
    }
}