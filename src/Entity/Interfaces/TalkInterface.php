<?php

namespace App\Entity\Interfaces;

interface TalkInterface extends ModelInterface
{
    public function getResume(): array;
}