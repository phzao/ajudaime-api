<?php

namespace App\Utils\Datetime\Interfaces;

/**
 * @package App\Utils\Datetime\Interfaces
 */
interface DatetimeCheckServiceInterface
{
    public function thisDatetimeIsGreaterThanNow(\DateTimeInterface $datetime):bool;

    public function stringDatetimeIsGreaterThanNow(string $datetime):bool;

    public function getDatesListConvertedToDatetimeOrFail(array $attributeList, array $params): array;

    public function getMonthInTheFutureFrom(\DateTime $dateTime, int $num_month_ahead): \DateTime;
}