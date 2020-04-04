<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\NeedInterface;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\NeedServiceInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Enums\GeneralTypes;
use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class NeedService implements NeedServiceInterface
{
    private $repository;

    private $elasticQueries;

    private $need;

    private $needIndex;

    private $validation;

    public function __construct(ElasticSearchRepositoryInterface $elasticSearchRepository,
                                ElasticSearchQueriesInterface $elasticSearchQueries,
                                ValidateModelInterface $validateModel,
                                NeedInterface $need)
    {
        $this->repository = $elasticSearchRepository;
        $this->elasticQueries = $elasticSearchQueries;
        $this->need = $need;
        $need_index = $need->getElasticIndexName();
        $this->validation = $validateModel;

        if (!$this->repository->isIndexExist($need_index)) {
            $mapping = $need->getElasticSearchMapping();
            $this->repository->index($mapping);
        }

        $this->needIndex = $need_index["index"];
    }

    public function getNeedsListUnblockedByUser(string $user_id, $result_quantity = 1): array
    {
        $match = [
            "user" => $user_id,
        ];

        $notMatch = [
            "status" => GeneralTypes::STATUS_DISABLE
        ];

        $query = $this->elasticQueries->getBoolMustMatchMustNotBy($this->needIndex, $match, $notMatch);
        $query["size"] = $result_quantity;

        return $this->repository->search($query);
    }

    public function register(array $data): ?array
    {
        $this->need->setAttributes($data);
        $this->validation->entityIsValidOrFail($this->need);

        $need = $this->need->getDataToInsert();

        $id = $this->repository->index($need);

        $this->need->setAttribute('id', $id);
        $needUpdated = $this->need->getFullDataToUpdateIndex();

        $this->repository->update($needUpdated);

        return $this->need->getOriginalData();
    }

    public function thisNeedGoesBeyondTheOpensLimitOfOrFail(int $allowed_number, string $user_id)
    {
        $needs = $this->getNeedsListUnblockedByUser($user_id, $allowed_number);
        if (count($needs["results"]) >= 3) {
            throw new BadRequestHttpException("Quantidade limite de $allowed_number listas em aberto atingida!");
        }
    }

    public function ifThisNeedListExistAndIsOpenMustFail(string $user_id, array $data)
    {
        if (empty($data["needsList"])) {
            $msg = ErrorMessage::getArrayMessageToJson(["needsList"=>"Lista de necessidades é obrigatória!"]);

            throw new UnprocessableEntityHttpException($msg);
        }

        $match = [
            "user.id" => $user_id,
            "needsList" => $data["needsList"],
        ];

        $matchNot = [
            "status" => GeneralTypes::STATUS_DISABLE
        ];

        $query = $this->elasticQueries->getBoolMustMatchMustNotBy($this->needIndex, $match, $matchNot);

        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            throw new BadRequestHttpException("Já existe uma lista identica em aberto!");
        }
    }

    public function getNeedByIdOrFail(array $data): array
    {
        if (empty($data["need"])) {
            $msg = ErrorMessage::getArrayMessageToJson(["need"=>"É necessário uma lista p/ continuar!"]);

            throw new UnprocessableEntityHttpException($msg);
        }
        $match = [
            "index" => $this->needIndex,
            "id" => $data["need"]
        ];

        $need = $this->repository->get($match);

        if (empty($need)) {
            throw new NotFoundHttpException("Lista não localizada");
        }

        return $need;
    }
}