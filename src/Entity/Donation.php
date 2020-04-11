<?php

namespace App\Entity;

use App\Entity\Interfaces\DonationInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class Donation implements DonationInterface
{
    use SimpleTime, ModelBase;

    const ELASTIC_INDEX = "donations";

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
            "user" => $this->user,
            "status" => $this->status,
            "need" => $this->need,
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "updated_at" => $this->getDateTimeStringFrom('updated_at'),
            "canceled_at" => $this->getDateTimeStringFrom('canceled_at'),
            "done_at" => $this->getDateTimeStringFrom('done_at'),
            "need_confirmed_at" => $this->getDateTimeStringFrom('need_confirmed_at')
        ];
    }

    public function getFullDataToUpdateIndex(): array
    {
        return [
            'index' => $this->getIndexName(),
            'id'    => $this->id,
            'type'  => '_doc',
            'body'  => [
                "doc" => $this->getOriginalData()
            ]
        ];
    }

    public function getStatusUpdateToIndex(): array
    {
        return [
            'index' => $this->getIndexName(),
            'id'    => $this->id,
            'type'  => '_doc',
            'body'  => [
                "doc" => ["status" => $this->status]
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
                        "user" => [
                            "properties" => [
                                "id" => ["type" => "keyword"],
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
            "index" => $this->getIndexName(),
            "body" => $this->getOriginalData()
        ];
    }

    public function done(): void
    {
        $this->done_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_DONE;
    }

    public function cancel(): void
    {
        $this->canceled_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_CANCELED;
    }

    public function confirm(): void
    {
        $this->need_confirmed_at = new \DateTime();
    }

    public function getResumeToNeed(): array
    {
        return [
            "id" => $this->id,
            "status" => $this->status,
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "need_confirmed_at" => $this->getDateTimeStringFrom('need_confirmed_at')
        ];
    }

    public function getNeedId(): string
    {
        if (!$this->need) {
            return "";
        }

        return $this->need["id"];
    }
}