<?php

namespace App\Entity\Traits;

/**
 * @package App\Entity\Traits
 */
trait SimpleTime
{
    public function getDateTimeStringFrom(string $column, string $format = "Y-m-d H:i:s"): string
    {
        if (empty($this->$column)) {
            return "";
        }

        if (!$this->$column instanceof \DateTime) {
            return $this->$column;
        }

        return $this->$column->format($format);
    }

    public function getAllAttributesDateAndFormat(): array
    {
        return [
        ];
    }
}