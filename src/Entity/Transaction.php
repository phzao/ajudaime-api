<?php

namespace App\Entity;

use App\Entity\Interfaces\TransactionInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class Transaction implements TransactionInterface
{
    use SimpleTime, ModelBase;

    const ELASTIC_INDEX = "transactions";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    protected $user;

    protected $need;

    /**
     * @var string
     * @Assert\NotBlank(message="The status is required!")
     * @Assert\Choice({"processing", "done", "canceled"})
     */
    protected $status;

    protected $created_at;

    protected $updated_at = GeneralTypes::NULL_VALUE;

    protected $done_at = GeneralTypes::NULL_VALUE;

    protected $canceled_at = GeneralTypes::NULL_VALUE;

    protected $need_confirmed_at = GeneralTypes::NULL_VALUE;

    protected $attributes = [
        "id",
        "user",
        "need",
        "created_at"
    ];

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_PROCESSING;
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
            "index" => self::ELASTIC_INDEX
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
            "user" => $this->user,
            "status" => $this->status,
            "need" => $this->need,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "canceled_at" => $this->canceled_at,
            "done_at" => $this->done_at,
            "need_confirmed_at" => $this->need_confirmed_at
        ];
    }

    public function getFullDataToUpdateIndex(): array
    {
        return [
            'index' => self::ELASTIC_INDEX,
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
            "index" => self::ELASTIC_INDEX,
            "body" => [
                "mappings" => [
                    "properties" => [
                        "id" => ["type" => "keyword"],
                        "user" => [
                            "properties" => [
                                "id" => ["type" => "keyword"],
                                "whatsapp" => ["type" => "integer", "null_value" => "NULL"],
                                "email" => ["type" => "keyword"],
                                "name" => ["type" => "text"],
                                "message" => ["type" => "text", "null_value" => "NULL"],
                                "localization" => ["type" => "geo_point"],
                            ]
                        ],
                        "need" => [
                            "properties" => [
                                "id" => ["type" => "keyword"],
                                "needsList" => ["type" => "text"]
                            ]
                        ],
                        "status" => ["type" => "text"],
                        "created_at" => ["type" => "date"],
                        "updated_at" => ["type" => "date", "null_value" => "NULL"],
                        "canceled_at" => ["type" => "date", "null_value" => "NULL"],
                        "done_at" => ["type" => "date", "null_value" => "NULL"],
                        "need_confirmed_at" => ["type" => "date", "null_value" => "NULL"]
                    ]
                ]
            ]
        ];
    }

    public function getDataToInsert(): array
    {
        return [
            "index" => self::ELASTIC_INDEX,
            "body" => $this->getOriginalData()
        ];
    }
}