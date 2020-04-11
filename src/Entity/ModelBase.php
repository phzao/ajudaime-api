<?php

namespace App\Entity;

trait ModelBase
{
    public function setAttributes(array $values): void
    {
        if (empty($values) ||
            !$this->attributes ||
            count($this->attributes) < 1) {
            return ;
        }

        foreach ($this->attributes as $attribute)
        {
            if (!array_key_exists($attribute, $values)) {
                continue;
            }

            if (!property_exists($this, $attribute)) {
                continue;
            }

            $this->setAttribute($attribute, $values[$attribute]);
        }
    }

    public function setAttribute(string $key, $value): void
    {
        $this->$key = $value;
    }

    public function remove(): void
    {
        $this->deleted_at = new \DateTime();
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deleted_at;
    }

    public function getIndexName(): string
    {
        if ($_ENV["APP_ENV"]==="test") {
            return self::ELASTIC_INDEX."_test";
        }

        return self::ELASTIC_INDEX;
    }
}