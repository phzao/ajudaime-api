<?php

namespace App\Utils\HandleErrors;

/**
 * Class ErrorMessage
 * @package App\Utils\HandleErrors
 */
final class ErrorMessage
{
    /**
     * @param string $message
     * @param string $status
     *
     * @return string
     */
    public static function getErrorMessage(string $message,
                                           string $status = "error"): string
    {
        $errormsg["status"]  = $status;
        $errormsg["message"] = $message;

        return json_encode($errormsg);
    }

    /**
     * @param array $messageList
     *
     * @return string
     */
    public static function getArrayMessageToJson(array $messageList): string
    {
        return json_encode($messageList);
    }

    /**
     * @param string $key
     * @param string $message
     *
     * @return string
     */
    public static function getStringMessageToJson(string $key, string $message): string
    {
        return json_encode([$key => $message]);
    }
}