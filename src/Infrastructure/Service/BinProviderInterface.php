<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

use stdClass;

interface BinProviderInterface
{
    public function details(string $bin): stdClass;

    public function getCountryCode(string $bin): string;
}
