<?php

namespace App\Entity;

use App\Entity\Interfaces\ApiTokenInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use App\Utils\Generators\TokenGeneratorInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="api_tokens")
 */
class ApiToken implements ApiTokenInterface
{
    use SimpleTime, ModelBase;

    const ELASTIC_INDEX = "api_tokens";
    const LIMIT_BY_MINUTE = 50;
    const LIMIT_BY_HOUR = 1000;
    const SIZE_TOKEN = 123;

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    protected $token;

    protected $expire_at;

    protected $expired_at = GeneralTypes::NULL_VALUE;

    protected $created_at;

    protected $device_type;

    protected $device_brand;

    protected $user_agent;

    protected $device_os;

    protected $localization;

    protected $city;

    protected $country;

    protected $postal;

    protected $state;

    protected $attributes = [
        "id",
        "device_type",
        "device_brand",
        "user_agent",
        "device_os",
        "localization",
        "city",
        "user",
        "state",
        "postal",
        "country"
    ];

    protected $user;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->expire_at = new \DateTime('+30 days');
        $this->created_at = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getExpireAt(): ?\DateTimeInterface
    {
        return $this->expire_at;
    }

    public function generateToken(TokenGeneratorInterface $tokenGenerator): void
    {
        $this->token = $tokenGenerator->generate(self::SIZE_TOKEN);
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getDetailsToken(): array
    {
        return [
            "id" => $this->id,
            "token" => $this->token,
            "logged_at" => $this->getDateTimeStringFrom("created_at"),
            "expire_at" => $this->getDateTimeStringFrom("expire_at"),
            "expired_at" => $this->getDateTimeStringFrom("expired_at")
        ];
    }

    /**
     * @throws \Exception
     */
    public function invalidateToken():void
    {
        $this->expired_at = new \DateTime("now");
    }

    public function getElasticSearchMapping(): array
    {
        return [
            "index" => $this->getIndexName(),
            "body" => [
                "mappings" => [
                    "properties" => [
                        "token" => ["type" => "text"],
                        "user" => ["type" => "keyword"],
                        "created_at" => ["type" => "date", "format"=> "yyyy-MM-dd HH:mm:ss"],
                        "expire_at" => ["type" => "date", "format"=> "yyyy-MM-dd HH:mm:ss"],
                        "expired_at" => [
                            "type" => "date",
                            "format"=> "yyyy-MM-dd HH:mm:ss",
                            "null_value" => "NULL"
                        ],
                        "device_type" => ["type" => "text"],
                        "device_brand" => ["type" => "text"],
                        "user_agent" => ["type" => "text"],
                        "localization" => ["type" => "geo_point"],
                        "city" => ["type" => "text"],
                        "state" => ["type" => "text"],
                        "postal" => ["type" => "text"],
                        "country" => ["type" => "text"],
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
            "token" => $this->token,
            "user" => $this->user,
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "expire_at" => $this->getDateTimeStringFrom('expire_at'),
            "expired_at" =>$this->getDateTimeStringFrom('expired_at'),
            "device_type" => $this->device_type,
            "device_brand" => $this->device_brand,
            "user_agent" => $this->user_agent,
            "localization" => $this->localization,
            "city" => $this->city,
            "state" => $this->state,
            "postal" => $this->postal,
            "country" => $this->country
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
}
