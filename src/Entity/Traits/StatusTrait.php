<?php

namespace App\Entity\Traits;

use App\Entity\Interfaces\SafeDeleteInterface;
use App\Entity\Interfaces\StatusInterface;

trait StatusTrait
{
    private static array $statuses = [
        StatusInterface::STATUS_ACTIVE => 'Active',
        StatusInterface::STATUS_INACTIVE => 'Inactive',
    ];

    /**
     * @ORM\Column(type="smallint")
     */
    private ?int $status = null;

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public static function getStatuses(): array
    {
        if (!empty(\class_implements(self::class)['SafeDeleteInterface'])) {
            self::$statuses[SafeDeleteInterface::STATUS_DELETED] = 'Deleted';
        }

        return self::$statuses;
    }
}
