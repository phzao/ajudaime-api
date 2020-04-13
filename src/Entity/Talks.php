<?php

namespace App\Entity;

use App\Entity\Interfaces\TalkInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class Talks implements TalkInterface
{
    use SimpleTime, ModelBase;

    const ELASTIC_INDEX = "talks";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    protected $origin;

    /**
     * @var string
     * @Assert\NotBlank(message="The status is required!")
     * @Assert\Choice({"enable", "deleted"})
     */
    protected $status;

    /**
     * @Assert\NotBlank(message="É necessário informar uma doação!")
     */
    protected $donation;

    /**
     * @Assert\NotBlank(message="Uma mensagem é obrigatória!")
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "Mensagem pode ter no mínimo {{ limit }} caracteres"
     * )
     */
    protected $message;

    protected $created_at;

    protected $read_at = GeneralTypes::NULL_VALUE;

    protected $attributes = [
        "id",
        "origin",
        "donation",
        "message",
        "created_at"
    ];

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_ENABLE;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getElasticIndexName():array
    {
        return [
            "index" => $this->getIndexName()
        ];
    }

    public function getFullData(): array
    {
        return [];
    }

    public function getOriginalData(): array
    {
        return [
            "id" => $this->id,
            "origin" => $this->origin,
            "donation" => $this->donation,
            "status" => $this->status,
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "message" => $this->message,
            "read_at" => $this->getDateTimeStringFrom('read_at')
        ];
    }

    public function getResume(): array
    {
        return [
            "id" => $this->id,
            "origin" => $this->origin,
            "status" => $this->status,
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "message" => $this->message,
            "read_at" => $this->getDateTimeStringFrom('read_at')
        ];
    }

    public function getFullDataToUpdateIndex(): array
    {
        $this->updated();
        return [
            'index' => $this->getIndexName(),
            'id'    => $this->id,
            'type'  => '_doc',
            'body'  => [
                "doc" => $this->getOriginalData()
            ]
        ];
    }

    public function getElasticSearchMapping(): array
    {
        return [
            "index" => $this->getIndexName(),
            "body" => [
                "mappings" => [
                    "properties" => [
                        "id" => ["type" => "keyword"],
                        "origin" => ["type" => "keyword"],
                        "donation" => ["type" => "keyword"],
                        "status" => ["type" => "text"],
                        "message" => ["type" => "text", "null_value" => "NULL"],
                        "created_at" => ["type" => "date"],
                        "read_at" => ["type" => "date", "null_value" => "NULL"]
                    ]
                ]
            ]
        ];
    }

    public function getDataToInsert(): array
    {
        return [
            "index" => $this->getIndexName(),
            "body" => $this->getOriginalData()
        ];
    }

    public function setRead():void
    {
        $this->read_at = new \DateTime();
    }
}