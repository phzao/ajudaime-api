<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\DonationInterface;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\DonationServiceInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Enums\GeneralTypes;
use App\Utils\HandleErrors\ErrorMessage;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class DonationService implements DonationServiceInterface
{
    private $repository;

    private $elasticQueries;

    private $donation;

    private $donationIndex;

    private $validation;

    public function __construct(ElasticSearchRepositoryInterface $elasticSearchRepository,
                                ElasticSearchQueriesInterface $elasticSearchQueries,
                                ValidateModelInterface $validateModel,
                                DonationInterface $donation)
    {
        $this->repository = $elasticSearchRepository;
        $this->elasticQueries = $elasticSearchQueries;
        $this->donation = $donation;

        $donation_index = $donation->getElasticIndexName();
        $this->validation = $validateModel;

        if (!$this->repository->isIndexExist($donation_index)) {
            $mapping = $donation->getElasticSearchMapping();
            $this->repository->index($mapping);
        }

        $this->donationIndex = $donation_index["index"];
    }

    public function getDonationsListProcessingByUser(string $user_id, $result_quantity = 1): array
    {
        $match = [
            "user" => $user_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->donationIndex, $match);
        $query["size"] = $result_quantity;

        return $this->repository->search($query);
    }

    public function register(array $data): ?array
    {
        $this->donation->setAttributes($data);
        $this->validation->entityIsValidOrFail($this->donation);

        $need = $this->donation->getDataToInsert();

        $id = $this->repository->index($need);

        $this->donation->setAttribute('id', $id);
        $needUpdated = $this->donation->getFullDataToUpdateIndex();

        $this->repository->update($needUpdated);

        return $this->donation->getOriginalData();
    }

    public function ifExistADonationWithThisNeedMustFail(array $data, string $user_id)
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

        $query = $this->elasticQueries->getBoolMustMatchBy($this->donationIndex, $match);
        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            throw new BadRequestHttpException("Existe uma ajuda em processamento para essa lista!");
        }
    }

    public function thisDonationGoesBeyondTheProcessingLimitOfOrFail(int $allowed_number, string $user_id)
    {
        $needs = $this->getDonationsListProcessingByUser($user_id, $allowed_number);

        if (count($needs["results"]) >= 3) {
            throw new BadRequestHttpException("Quantidade limite de $allowed_number listas em aberto atingida!");
        }
    }

    public function getDonationOnProcessingByIdOrFail(string $transaction_id, array $fields): array
    {
        $match = [
            "id" => $transaction_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->donationIndex, $match);
        $query["size"] = 1;

        if (!empty($fields)) {
            $query["_source"] = $fields;
        }

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Doação não encontrada");
        }

        return $res["results"];
    }

    public function getDonationIdOrFail(string $transaction_id): string
    {
        $transaction = $this->getDonationOnProcessingByIdOrFail($transaction_id, ["id"]);

        return $transaction[0]["id"];
    }
    
    public function doneDonation(string $user_id, string $donation_id): array
    {
        $match = [
            "user.id" => $user_id,
            "id" => $donation_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->donationIndex, $match);
        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Doação não encontrada!");
        }

        $donationSaved = $res["results"][0];
        $this->donation->setAttributes($donationSaved);
        $this->donation->done();

        return $donationSaved;
    }

    public function cancelDonation(string $user_id, string $donation_id): array
    {
        $match = [
            "user.id" => $user_id,
            "id" => $donation_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->donationIndex, $match);
        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Doação não encontrada!");
        }

        $donationSaved = $res["results"][0];
        $this->donation->setAttributes($donationSaved);
        $this->donation->cancel();

        return $donationSaved;
    }

    public function getDonationsByUser(string $user_id): array
    {
        $match = [
            "user.id" => $user_id
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->donationIndex, $match);
        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            return $res["results"];
        }

        return [];
    }

    public function getDonationsByStatus(string $status): array
    {
        GeneralTypes::isValidDonationStatus($status);

        $match = [
            "status" => $status
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->donationIndex, $match);
        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            return $res["results"];
        }

        return [];
    }
}