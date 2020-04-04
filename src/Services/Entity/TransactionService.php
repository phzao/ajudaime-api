<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\TransactionInterface;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\TransactionServiceInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Enums\GeneralTypes;
use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class TransactionService implements TransactionServiceInterface
{
    private $repository;

    private $elasticQueries;

    private $transaction;

    private $transactionIndex;

    private $validation;

    public function __construct(ElasticSearchRepositoryInterface $elasticSearchRepository,
                                ElasticSearchQueriesInterface $elasticSearchQueries,
                                ValidateModelInterface $validateModel,
                                TransactionInterface $transaction)
    {
        $this->repository = $elasticSearchRepository;
        $this->elasticQueries = $elasticSearchQueries;
        $this->transaction = $transaction;

        $transaction_index = $transaction->getElasticIndexName();
        $this->validation = $validateModel;

        if (!$this->repository->isIndexExist($transaction_index)) {
            $mapping = $transaction->getElasticSearchMapping();
            $this->repository->index($mapping);
        }

        $this->transactionIndex = $transaction_index["index"];
    }

    public function getTransactionsListProcessingByUser(string $user_id, $result_quantity = 1): array
    {
        $match = [
            "user" => $user_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->transactionIndex, $match);
        $query["size"] = $result_quantity;

        return $this->repository->search($query);
    }

    public function register(array $data): ?array
    {
        $this->transaction->setAttributes($data);
        $this->validation->entityIsValidOrFail($this->transaction);

        $need = $this->transaction->getDataToInsert();

        $id = $this->repository->index($need);

        $this->transaction->setAttribute('id', $id);
        $needUpdated = $this->transaction->getFullDataToUpdateIndex();

        $this->repository->update($needUpdated);

        return $this->transaction->getOriginalData();
    }

    public function ifExistATransactionWithThisNeedMustFail(array $data, string $user_id)
    {
        if (empty($data["need"])) {
            $msg = ErrorMessage::getArrayMessageToJson(["need"=>"É necessário uma lista p/ continuar!"]);

            throw new UnprocessableEntityHttpException($msg);
        }

        $match = [
            "user.id" => $user_id,
            "need.id" => $data["need"],
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->transactionIndex, $match);
        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            throw new BadRequestHttpException("Existe uma ajuda em processamento para essa lista!");
        }
    }

    public function thisTransactionGoesBeyondTheProcessingLimitOfOrFail(int $allowed_number, string $user_id)
    {
        $needs = $this->getTransactionsListProcessingByUser($user_id, $allowed_number);

        if (count($needs["results"]) >= 3) {
            throw new BadRequestHttpException("Quantidade limite de $allowed_number listas em aberto atingida!");
        }
    }

    public function getTransactionOnProcessingByIdOrFail(string $transaction_id, array $fields): array
    {
        $match = [
            "id" => $transaction_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->transactionIndex, $match);
        $query["size"] = 1;

        if (!empty($fields)) {
            $query["_source"] = $fields;
        }

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Transação não encontrada");
        }

        return $res["results"];
    }

    public function getTransactionIdOrFail(string $transaction_id): string
    {
        $transaction = $this->getTransactionOnProcessingByIdOrFail($transaction_id, ["id"]);

        return $transaction[0]["id"];
    }
}