<?php

namespace App\Services\Entity;

use App\Entity\Interfaces\TalkInterface;
use App\Repository\ElasticSearch\ElasticSearchRepositoryInterface;
use App\Services\Entity\Interfaces\TalkServiceInterface;
use App\Services\Validation\ValidateModelInterface;
use App\Utils\ElasticSearch\ElasticSearchQueriesInterface;
use App\Utils\Enums\GeneralTypes;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class TalkService implements TalkServiceInterface
{
    private $repository;

    private $elasticQueries;

    private $talk;

    private $talkIndex;

    private $validation;

    public function __construct(ElasticSearchRepositoryInterface $elasticSearchRepository,
                                ElasticSearchQueriesInterface $elasticSearchQueries,
                                ValidateModelInterface $validateModel,
                                TalkInterface $talk)
    {
        $this->repository = $elasticSearchRepository;
        $this->elasticQueries = $elasticSearchQueries;
        $this->talk = $talk;

        $talk_index = $talk->getElasticIndexName();
        $this->validation = $validateModel;

        if (!$this->repository->isIndexExist($talk_index)) {
            $mapping = $talk->getElasticSearchMapping();
            $this->repository->index($mapping);
        }

        $this->talkIndex = $talk_index["index"];
    }

    public function setTalkRead(string $talk_id): array
    {
        $talk = $this->getTalkEntityByIdOrFail($talk_id);
        $talk->setRead();
        $talkUpdated = $talk->getFullDataToUpdateIndex();

        $this->repository->update($talkUpdated);

        return $talk->getOriginalData();
    }

    public function getTalkEntityByIdOrFail(string $talk_id): ?TalkInterface
    {
        $match = [
            "index" => $this->talkIndex,
            "id" => $talk_id
        ];

        $talk = $this->repository->get($match);

        if (empty($talk)) {
            throw new NotFoundHttpException("Mensagem não encontrada");
        }

        $this->talk->setAttributes($talk);

        return $this->talk;
    }

    public function getTalkEntity():?TalkInterface
    {
        return $this->talk;
    }

    public function register(array $data): ?array
    {
        $this->talk->setAttributes($data);
        $this->validation->entityIsValidOrFail($this->talk);

        $need = $this->talk->getDataToInsert();

        $id = $this->repository->index($need);

        $this->talk->setAttribute('id', $id);
        $needUpdated = $this->talk->getFullDataToUpdateIndex();

        $this->repository->update($needUpdated);

        return $this->talk->getOriginalData();
    }

    public function thisTalkGoesBeyondTheUnreadLimitOfOrFail(int $allowed_number,
                                                             string $user_id,
                                                             string $transaction_id)
    {
        $match = [
            "origin" => $user_id,
            "donation.id" => $transaction_id,
            "read_at" => "NULL"
        ];

        $query = $this->elasticQueries->getBoolMustMatchBy($this->talkIndex, $match);
        $query["size"] = $allowed_number;

        $talks = $this->repository->search($query);

        if (count($talks["results"]) >= $allowed_number) {
            throw new BadRequestHttpException("Limite de $allowed_number mensagens não lidas atingido, aguarde a leitura para enviar novamente!");
        }
    }
}