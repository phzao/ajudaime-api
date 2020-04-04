<?php

namespace App\Entity\Traits;

use App\Utils\Enums\GeneralTypes;

trait ControlStatusAndIsDefault
{
    public function changeStatusAndSetNoDefaultIfNecessary(string $status): void
    {
        if ($status === GeneralTypes::STATUS_DISABLE &&
            $this->is_default === GeneralTypes::DEFAULT_SET) {
            $this->is_default = GeneralTypes::DEFAULT_UNSET;
        }
        $this->status = $status;
    }

    public function setDefaultAndEnableIfIsDisable():void
    {
        if ($this->status === GeneralTypes::STATUS_DISABLE) {
            $this->status = GeneralTypes::STATUS_ENABLE;
        }
        $this->is_default = GeneralTypes::DEFAULT_SET;
    }
}