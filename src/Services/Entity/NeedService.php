<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\NeedInterface;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\NeedServiceInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Enums\GeneralTypes;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NeedService implements NeedServiceInterface
{
    const ROWS_ALLOWED = 3;

    private $repository;

    private $elasticQueries;

    private $need;

    private $validation;

    private $donation;

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

        $this->elasticQueries->setIndex($need_index["index"]);
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
        $match = [
            "user.id" => $user_id
        ];

        $should = [
            "donation.need_confirmed_at" => "NULL"
        ];

        $query = $this->elasticQueries->getMustAndMustNotExist($match, "donation", $should);

        $needs = $this->repository->search($query);

        if (count($needs["results"]) >= $allowed_number) {
            throw new BadRequestHttpException("Quantidade limite de $allowed_number listas em aberto atingida!");
        }
    }

    public function getNeedByIdOrFail(string $need_id): array
    {
        $match = [
            "index" => $this->need->getIndexName(),
            "id" => $need_id
        ];

        $need = $this->repository->get($match);

        if (empty($need)) {
            throw new NotFoundHttpException("Lista não localizada");
        }

        return $need;
    }

    public function update(array $data, array $user): void
    {
        $needSaved = $this->getOneByIdUserIdAndEnable($data["id"], $user["id"]);

        $this->need->setAttributes($needSaved);

        $data["user"] = $user;

        $this->need->setAttributes($data);

        $this->validation->entityIsValidOrFail($this->need);

        $needUpdated = $this->need->getFullDataToUpdateIndex();

        $this->repository->update($needUpdated);
    }

    public function getOneByDonor(string $need_id, string $user_id): array
    {
        $match = [
            "donation.user.id" => $user_id,
            "id" => $need_id
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);

        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Lista não localizada");
        }

        return $res["results"][0];
    }

    public function getOneByIdUserIdAndEnable(string $need_id, string $user_id): array
    {
        $match = [
            "user.id" => $user_id,
            "id" => $need_id,
            "status" => GeneralTypes::STATUS_ENABLE
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);

        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Lista não localizada");
        }

        return $res["results"][0];
    }

    public function remove(string $need_id, string $user_id): void
    {
        $match = [
            "user.id" => $user_id,
            "id" => $need_id,
            "status" => GeneralTypes::STATUS_ENABLE
        ];

        $this->elasticQueries->setIndex($this->need->getIndexName());
        $query = $this->elasticQueries->getBoolMustMatchBy($match);

        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Lista não localizada");
        }

        $match = [
            "index" => $this->need->getIndexName(),
            "id" => $need_id
        ];

        if (!empty($res["results"][0]["donation"])) {
            $this->donation = $res["results"][0]["donation"]["id"];
        }

        $this->repository->delete($match);
    }

    public function loadNeedToDisable(string $need_id, $newData = []): array
    {
        $needSaved = $this->getOneByIdAndEnableOrFail($need_id);

        $this->need->setAttributes($needSaved);

        if (!empty($newData)) {
            $this->need->setAttributes($newData);
        }

        $this->need->disable();

        return $this->need->getFullDataToUpdateIndex();
    }

    public function disableNeedById(string $need_id): void
    {
        $needUpdated = $this->loadNeedToDisable($need_id);

        $this->repository->update($needUpdated);
    }

    public function getOneByIdAndUserOrFail(string $need_id, string $user_id): array
    {
        $match = [
            "id" => $need_id,
            "user.id" => $user_id
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Lista não localizada");
        }

        return $res["results"][0];
    }

    public function getNeedsListByUser(string $user_id): array
    {
        $match = [
            "user.id" => $user_id
        ];

        $this->elasticQueries->setIndex($this->need->getIndexName());
        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            return $res["results"];
        }

        return [];
    }

    public function getOneByIdOrFail(string $id): array
    {
        $params = [
            "index" => $this->need->getIndexName(),
            "id" => $id
        ];

        $need = $this->repository->get($params);
        if (empty($need)) {
            throw new NotFoundHttpException('Ajuda não localizada');
        }

        return $need;
    }

    public function updateAnyway(array $need)
    {
        $this->need->setAttributes($need);
        $this->need->updated();
        $needUpdated = $this->need->getFullDataToUpdateIndex();

        $this->repository->update($needUpdated);
    }

    public function getOneByIdAndEnableOrFail(string $need_id): array
    {
        $this->elasticQueries->setIndex($this->need->getIndexName());;
        $match = [
            "id" => $need_id,
            "status" => GeneralTypes::STATUS_ENABLE
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);

        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Lista não localizada");
        }

        return $res["results"][0];
    }

    public function getAllNeedsNotCanceled(): array
    {
        $match = [
            "status" => GeneralTypes::STATUS_ENABLE
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);

        $res = $this->repository->search($query);

        return $res["results"];
    }

    public function getAllNeedsNotCanceledByCountryOrFail(array $data): array
    {
        if (empty($data["country"])) {
            throw new BadRequestHttpException("Deve-se informar o País da busca");
        }

        $match = [
            "status" => GeneralTypes::STATUS_ENABLE,
            "user.country" => $data["country"],
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);

        $res = $this->repository->search($query);

        return $res["results"];
    }

    public function removeDonationCanceled(string $need_id)
    {
        $needSaved = $this->getOneByIdAndEnableOrFail($need_id);

        $needSaved["donation"] = null;
        $this->need->setAttributes($needSaved);
        $needUpdated = $this->need->getFullDataToUpdateIndex();
        $this->repository->update($needUpdated);
    }

    public function getDonationId():?string
    {
        return $this->donation;
    }
}