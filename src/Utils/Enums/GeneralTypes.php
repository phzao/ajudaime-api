<?php

namespace App\Utils\Enums;

use App\Utils\HandleErrors\ErrorMessage;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @package App\Utils\Enums
 */
class GeneralTypes
{
    const STATUS_ENABLE  = "enable";
    const STATUS_BLOCKED = "blocked";
    const STATUS_DISABLE = "disable";
    const STATUS_OPEN = "open";
    const STATUS_PROCESSING = "processing";
    const STATUS_ACHIEVE = "achieve";
    const NULL_VALUE = "NULL";

    const STATUS_DEFAULT_LIST = [
        self::STATUS_ENABLE,
        self::STATUS_DISABLE,
        self::STATUS_BLOCKED,
        self::STATUS_ACHIEVE,
        self::STATUS_PROCESSING,
        self::STATUS_OPEN
    ];

    const STATUS_DESCRIPTION = [
        self::STATUS_ENABLE  => "ativo",
        self::STATUS_DISABLE => "inativo",
        self::STATUS_OPEN => "bloqueado",
        self::STATUS_PROCESSING => "processando",
        self::STATUS_ACHIEVE => "concretizada"
    ];

    static public function getStatusList(): array
    {
        return self::STATUS_DEFAULT_LIST;
    }

    static public function getDefaultDescription(string $key): string
    {
        return (new self)->getDescription($key, self::STATUS_DESCRIPTION);
    }

    public function getDescription(string $key, array $list): string
    {
        if (!array_key_exists($key, $list)) {
            return $key;
        }

        return $list[$key];
    }

    static public function getStatusDescriptionList(): array
    {
        return self::STATUS_DESCRIPTION;
    }

    static public function isValidDefaultStatusOrFail(string $status)
    {
        $list = self::STATUS_DEFAULT_LIST;
        (new self)->isValidStatusOrFail($status, $list);
    }

    public function isValidStatusOrFail(string $status, array $list): ? bool
    {
        if (!in_array($status, $list)) {

            $list = ["status" => "This status $status is invalid!"];
            $msg  = ErrorMessage::getArrayMessageToJson($list);

            throw new UnprocessableEntityHttpException($msg);
        }

        return true;
    }
}