<?php

namespace App\Entity;

use App\Lib\Interfaces\FilterEntityInterface;

class UserFilter implements FilterEntityInterface
{

    public function getFields(): array
    {
        return [

        ];
    }

    /**
     * @param string $field
     * @return string
     */
    public function getFiledType(string $field): string
    {
        return [

        ];
    }

}