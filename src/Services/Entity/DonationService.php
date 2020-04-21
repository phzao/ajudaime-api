<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\DonationInterface;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\DonationServiceInterface;
use App\Services\Entity\Interfaces\NeedServiceInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Enums\GeneralTypes;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DonationService implements DonationServiceInterface
{
    private $repository;

    private $elasticQueries;

    private $donation;

    private $validation;

    private $donationToNeed;

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

        $this->elasticQueries->setIndex($donation_index["index"]);
    }

    public function getDonationsListProcessingByUser(string $user_id, $result_quantity = 1): array
    {
        $match = [
            "user.id" => $user_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);
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

        $this->donationToNeed = $this->donation->getResumeToNeed();

        return $this->donation->getOriginalData();
    }

    public function update(array $donation): void
    {
        $this->repository->update($donation);
    }

    public function ifExistADonationWithThisNeedMustFail(string $need_id, string $user_id)
    {
        $match = [
            "user.id" => $user_id,
            "need.id" => $need_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            throw new BadRequestHttpException("Existe uma ajuda em processamento para essa lista!");
        }
    }

    public function thisDonationGoesBeyondTheProcessingLimitOfOrFail(int $allowed_number, string $user_id)
    {
        $donations = $this->getDonationsListProcessingByUser($user_id, $allowed_number);

        if (count($donations["results"]) >= 3) {
            throw new BadRequestHttpException("Quantidade limite de $allowed_number listas em aberto atingida!");
        }
    }

    public function getDonationByIdAndUserOrFail(string $donation_id, string $user_id): array
    {
        $match = [
            "id" => $donation_id,
            "user.id" => $user_id
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $query["size"] = 1;

        if (!empty($fields)) {
            $query["_source"] = $fields;
        }

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Doação não encontrada");
        }

        return $res["results"][0];
    }

    public function getDonationEntityByIdOrFail(string $donation_id): ?DonationInterface
    {
        $match = [
            "index" => $this->donation->getIndexName(),
            "id" => $donation_id
        ];

        $donation = $this->repository->get($match);

        if (empty($donation)) {
            throw new NotFoundHttpException("Doação não encontrada");
        }

        $this->donation->setAttributes($donation);

        return $this->donation;
    }

    public function getDonationByIdOrFail(string $donation_id): array
    {
        $match = [
            "id" => $donation_id
        ];

        $this->elasticQueries->setIndex($this->donation->getIndexName());
        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $query["size"] = 1;

        if (!empty($fields)) {
            $query["_source"] = $fields;
        }

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Doação não encontrada");
        }

        return $res["results"][0];
    }

    public function getDonationOnProcessingByIdOrFail(string $transaction_id, array $fields): array
    {
        $match = [
            "id" => $transaction_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);
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

    public function needConfirmation(string $user_id, string $donation_id): string
    {
        $match = [
            "id" => $donation_id,
            "need.user.id" => $user_id
        ];

        $this->elasticQueries->setIndex($this->donation->getIndexName());
        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Doação não encontrada!");
        }

        $donationSaved = $res["results"][0];
        $this->donation->setAttributes($donationSaved);
        $this->donation->confirm();

        $donationUpdated = $this->donation->getFullDataToUpdateIndex();

        $this->repository->update($donationUpdated);
        $this->donationToNeed = $this->donation->getResumeToNeed();

        return $this->donation->getNeedId();
    }

    public function getOneByIdUserIdProcessingOrFail(string $user_id, string $donation_id): array
    {
        $match = [
            "user.id" => $user_id,
            "id" => $donation_id,
            "status" => GeneralTypes::STATUS_PROCESSING
        ];

        $this->elasticQueries->setIndex($this->donation->getIndexName());
        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $query["size"] = 1;

        $res = $this->repository->search($query);

        if (empty($res["results"])) {
            throw new NotFoundHttpException("Doação não encontrada!");
        }

        return $res["results"][0];
    }

    public function getDonationsToTalk(string $user_id): array
    {
        $must = [
            "need_confirmed_at" => "NULL"
        ];

        $should = [
            "user.id" => $user_id,
            "need.user.id" => $user_id
        ];

        $this->elasticQueries->setIndex($this->donation->getIndexName());
        $query = $this->elasticQueries->getBoolMustOrShouldBy($must, $should);

        $query["body"]["query"]["bool"]["must_not"] = ["match" => ["status" => "canceled"]];

        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            return $res["results"];
        }

        return [];
    }

    public function doneDonation(string $user_id, string $donation_id): string
    {
        $donationSaved = $this->getOneByIdUserIdProcessingOrFail($user_id, $donation_id);

        $this->donation->setAttributes($donationSaved);
        $this->donation->done();

        $donationUpdated = $this->donation->getFullDataToUpdateIndex();

        $this->repository->update($donationUpdated);

        $this->donationToNeed = $this->donation->getResumeToNeed();

        return $this->donation->getNeedId();
    }

    public function cancelDonation(string $user_id, $donation_id): string
    {
        $donationSaved = $this->getOneByIdUserIdProcessingOrFail($user_id, $donation_id);
        $this->donation->setAttributes($donationSaved);
        $this->donation->cancel();

        $donationUpdated = $this->donation->getStatusUpdateToIndex();

        $this->repository->update($donationUpdated);
        $this->donationToNeed = $this->donation->getResumeToNeed();

        return $this->donation->getNeedId();
    }

    public function cancelDonationById($donation_id): string
    {
        if (empty($donation_id)) {
            return '';
        }

        $donationSaved = $this->getDonationByIdOrFail($donation_id);

        $this->donation->setAttributes($donationSaved);
        $this->donation->cancel();

        $donationUpdated = $this->donation->getStatusUpdateToIndex();

        $this->repository->update($donationUpdated);

        return $this->donation->getNeedId();
    }

    public function getDonationsByUser(string $user_id): array
    {
        $match = [
            "user.id" => $user_id
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($match);
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

        $query = $this->elasticQueries->getBoolMustMatchBy($match);
        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            return $res["results"];
        }

        return [];
    }

    public function getDonationStatusIdCreated(): array
    {
        return $this->donationToNeed;
    }

    public function updateTalk(array $talk): void
    {
        $donation = $this->getDonationEntityByIdOrFail($talk["donation"]["id"]);
        unset($talk["donation"]);
        $donation->updateTalk($talk);
        $donationUpdated = $donation->getFullDataToUpdateIndex();
        $this->repository->update($donationUpdated);
    }

    public function cancelWithMoreThanTwoDaysProcessing(string $dateToCheck,
                                                        NeedServiceInterface $needService):void
    {
        $this->elasticQueries->setIndex($this->donation->getIndexName());
        $query = $this->elasticQueries->getMustAndOldestDateBy(["status" => "processing"],
                                                               "created_at",
                                                               $dateToCheck);

        $res = $this->repository->search($query);

        if (!empty($res["results"])) {
            foreach($res["results"] as $donation)
            {
                $this->donation->setAttributes($donation);
                $this->donation->cancel();

                $donationUpdated = $this->donation->getStatusUpdateToIndex();

                $this->repository->update($donationUpdated);

                $needId = $this->donation->getNeedId();
                $needService->removeDonationCanceled($needId);
            }
        }
    }
}