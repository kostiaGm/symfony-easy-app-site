<?php

namespace App\EventSubscriberService\Interfaces;

interface DBSInterface
{
    public function create($entity): void;
    public function update($entity): void;
}