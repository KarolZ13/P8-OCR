<?php

namespace App\Entity;

use Symfony\Component\Config\Definition\Exception\Exception;

class Product
{
    const FOOD_PRODUCT = 'food';
    
    private string $name;
    private string $type;
    private float $price;

    public function __construct(string $name, string $type, float $price)
    {
        $this->name = $name;
        $this->type = $type;
        $this->price = $price;
    }

    public function computeTVA(): float | Exception
    {
        if (self::FOOD_PRODUCT == $this->type) {
            return $this->price * 0.055;
        }

        return $this->price * 0.196;
    }
}
