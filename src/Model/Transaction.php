<?php

declare(strict_types=1);

namespace App\Model;

use Decimal\Decimal;
use function is_string;

class Transaction
{
    private string $bin;

    private string $currency;

    private Decimal $amount;

    public function __construct(array $data)
    {
        foreach ($data as $key => $val) {
            if (property_exists(__CLASS__, $key)) {
                $this->{'set'.ucfirst($key)}($val);
            }
        }
    }

    public function getBin(): string
    {
        return $this->bin;
    }

    public function setBin(string $bin): self
    {
        $this->bin = $bin;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getAmount(): Decimal
    {
        return $this->amount;
    }

    public function setAmount(string|Decimal $amount): self
    {
        if (is_string($amount)) {
            $amount = new Decimal($amount);
        }

        $this->amount = $amount;

        return $this;
    }
}
