<?php

namespace App\Entity;

use App\Entity\Interfaces\ModelInterface;
use App\Entity\Interfaces\SimpleTimeInterface;
use App\Entity\Interfaces\UsuarioInterface;
use App\Entity\Traits\SimpleTime;
use App\Utils\Enums\GeneralTypes;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users",
 *     indexes={
 *     @ORM\Index(name="users_email_status_type_idx", columns={"email", "status"}),
 * })
 */
class User implements UsuarioInterface, ModelInterface, SimpleTimeInterface
{
    use SimpleTime, ModelBase;

    const ELASTIC_INDEX = "users";

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", columnDefinition="DEFAULT uuid_generate_v4()")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    protected $id;

    /**
     * @Assert\NotBlank(message="Email is required!")
     * @Assert\Email
     * @Assert\Length(
     *      min = 6,
     *      max = 180,
     *      minMessage = "Email must be at least {{ limit }} characters long",
     *      maxMessage = "Email cannot be longer than {{ limit }} characters"
     * )
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="json")
     */
    protected $roles = [];

    /**
     * @Assert\Length(
     *      min = 10,
     *      max = 20,
     *      minMessage = "Telefone pode ter no máximo {{ limit }} caracteres",
     *      maxMessage = "Telefone pode ter no mínimo {{ limit }} caracteres"
     * )
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $phone = GeneralTypes::NULL_VALUE;

    /**
     * @Assert\Length(
     *      min = 10,
     *      max = 20,
     *      minMessage = "Telefone pode ter no máximo {{ limit }} caracteres! Formato (xx) xxxxx-xxxx",
     *      maxMessage = "Telefone pode ter no mínimo {{ limit }} caracteres! Formato (xx) xxxxx-xxxx"
     * )
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    protected $whatsapp = GeneralTypes::NULL_VALUE;

    /**
     * @Assert\Length(
     *      min = 10,
     *      max = 500,
     *      minMessage = "Mensagem pode ter no máximo {{ limit }} caracteres",
     *      maxMessage = "Mensagem pode ter no mínimo {{ limit }} caracteres"
     * )
     */
    protected $message = GeneralTypes::NULL_VALUE;

    /**
     * @var string
     * @Assert\NotBlank(message="The status is required!")
     * @Assert\Choice({"enable", "disable", "blocked"})
     */
    protected $status;

    /**
     * @Assert\Length(
     *      max = 70,
     *      maxMessage = "Name cannot be longer than {{ limit }} characters"
     * )
     */
    protected $name;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    protected $attributes = [
        "id",
        "email",
        "name",
        "phone",
        "status",
        "whatsapp",
        "message",
        "location",
        "apiToken",
        "created_at",
        "deleted_at"
    ];

    private $apiToken = [];

    protected $deleted_at = GeneralTypes::NULL_VALUE;

    protected $updated_at = GeneralTypes::NULL_VALUE;

    protected $location;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->status = GeneralTypes::STATUS_ENABLE;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function encryptPassword(UserPasswordEncoderInterface $passwordEncoder)
    {
        $password = empty($this->password) ? "" : $this->password;
        $this->password = $passwordEncoder->encodePassword($this, $password);
    }

    public function getFullData(): array
    {
        $data = [
            "id" => $this->id,
            "email" => $this->email,
            "name" => $this->name,
            "phone" => $this->phone,
            "whatsapp" => $this->whatsapp,
            "message" => $this->message,
            "location" => $this->location,
            "status" => $this->status,
            "status_description" => GeneralTypes::getDefaultDescription($this->status),
            "created_at" => $this->getDateTimeStringFrom('created_at'),
            "deleted_at" => $this->getDateTimeStringFrom('deleted_at'),
            "updated_at" => $this->getDateTimeStringFrom('updated_at')
        ];

        if ($this->id) {
            $data["id"] = $this->id;
        }

        return $data;
    }

    public function getOriginalData(): array
    {
        $data = [
            "id" => $this->id,
            "email" => $this->email,
            "name" => $this->name,
            "status" => $this->status,
            "location" => $this->location,
            "phone" => $this->phone,
            "whatsapp" => $this->whatsapp,
            "message" => $this->message,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at
        ];

        return $data;
    }

    public function setDisable()
    {
        $this->status = GeneralTypes::STATUS_DISABLE;
    }

    public function getUser(): User
    {
        return $this;
    }

    public function getStatus(): string
    {
        return (string) $this->status;
    }

    /**
     * @throws \Exception
     */
    public function delete(): void
    {
        $this->deleted_at = new \DateTime('now');
    }

    public function getName(): string
    {
        return (string)$this->name;
    }

    public function getNameAndId(): array
    {
        $name = $this->name ?? "";

        return [
            "id" => $this->id,
            "name" => $name
        ];
    }

    public function jsonSerialize()
    {
        return $this->getFullData();
    }

    public function canAuthenticate(): bool
    {
        if ($this->status === GeneralTypes::STATUS_BLOCKED ||
            $this->status === GeneralTypes::STATUS_DISABLE ||
            empty($this->id) ||
            $this->deleted_at!==GeneralTypes::NULL_VALUE){
            return false;
        }

        return true;
    }

    public function getElasticSearchMapping(): array
    {
        return [
            "index" => self::ELASTIC_INDEX,
            "body" => [
                "mappings" => [
                    "properties" => [
                        "phone" => ["type" => "text", "null_value" => "NULL"],
                        "whatsapp" => ["type" => "text", "null_value" => "NULL"],
                        "status" => ["type" => "text"],
                        "email" => ["type" => "keyword"],
                        "name" => ["type" => "text"],
                        "message" => ["type" => "text", "null_value" => "NULL"],
                        "localization" => ["type" => "geo_point"],
                        "api_token" => [
                            "properties" => [
                                "id" => ["type"=>"keyword"],
                                "token" => ["type"=> "keyword"],
                                "logged_at" => ["type"=>"date"],
                                "expire_at" => ["type"=> "date"],
                                "expired_at" => [
                                    "type" => "date",
                                    "null_value" => "NULL"
                                ]
                            ]
                        ],
                        "created_at" => ["type" => "date"],
                        "updated_at" => ["type" => "date", "null_value" => "NULL"],
                        "deleted_at" => ["type" => "date", "null_value" => "NULL"]
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

    public function getElasticIndexName():array
    {
        return [
            "index" => self::ELASTIC_INDEX
        ];
    }

    public function getDataResume(): array
    {
        return [
            "id" => $this->id,
            "email" => $this->email,
            "name" => $this->name,
            "whatsapp" => $this->whatsapp,
            "message" => $this->message,
            "location" => $this->location
        ];
    }
}
