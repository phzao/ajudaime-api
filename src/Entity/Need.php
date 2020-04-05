<?php

namespace App\Entity;

use App\Entity\Interfaces\NeedInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="api_tokens")
 */
class Need implements NeedInterface
{
    use SimpleTime, ModelBase;

    const ELASTIC_INDEX = "needs";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    protected $user;

    protected $donation;

    /**
     * @Assert\NotBlank(message="Uma lista de necessidades é obrigatória!")
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "A lista deve ter no máximo {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=200, unique=true)
     */
    protected $needsList;

    /**
     * @Assert\NotBlank(message="Uma mensagem para essa ajuda é requerida!")
     * @Assert\Length(
     *      max = 200,
     *      maxMessage = "A Mensagem deve ter no máximo {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=200, unique=true)
     */
    protected $message;

    /**
     * @var string
     * @Assert\NotBlank(message="The status is required!")
     * @Assert\Choice({"enable", "disable"})
     */
    protected $status;

    protected $created_at;

    protected $updated_at = GeneralTypes::NULL_VALUE;

    protected $attributes = [
        "id",
        "user",
        "needsList",
        "donation",
        "message",
        "created_at"
    ];

    public function getId(): ?string
    {
        return $this->id;
    }
    public function __construct()
    {
        $this->created_at = new \DateTime("now");
        $this->status = GeneralTypes::STATUS_ENABLE;
    }

    public function setUser($user)
    {
        $this->user = $user;
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
            "donation" => $this->donation,
            "message" => $this->message,
            "needsList" => $this->needsList,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
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
                                "localization" => ["type" => "geo_point"]
                            ]
                        ],
                        "donation" => [
                            "properties" => [
                                "id" => ["type" => "keyword"],
                                "status" => ["type" => "integer", "null_value" => "NULL"],
                                "created_at" => ["type" => "date"]
                            ]
                        ],
                        "needsList" => ["type" => "text"],
                        "message" => ["type" => "text"],
                        "status" => ["type" => "text"],
                        "created_at" => ["type" => "date"],
                        "updated_at" => ["type" => "date", "null_value" => "NULL"],
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

    public function disable(): void
    {
        $this->updated_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_DISABLE;
    }
}
