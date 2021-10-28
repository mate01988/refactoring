<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use Decimal\Decimal;

interface RateProviderInterface
{
    public function getAmount(string $currencyCode): ?Decimal;
}
