<?php

declare(strict_types=1);

namespace App\Service\RateProvider;

use App\Exception\AppException;
use App\Exception\EnumErrors;
use App\Infrastructure\Service\RateProviderInterface;
use App\System;
use Decimal\Decimal;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use stdClass;

class ExchangerApi implements RateProviderInterface
{
    private static ?stdClass $cache = null;
    private ?string $exchangerApiUrl;
    private ?string $exchangerApiKey;
    private Client $client;

    public function __construct()
    {
        $this->exchangerApiUrl = System::getConfig('RATE_PROVIDER_URL');
        $this->exchangerApiKey = System::getConfig('RATE_PROVIDER_API_KEY');
        $this->client = new \GuzzleHttp\Client();
    }

    public function getAmount(string $currencyCode): ?Decimal
    {
        if (null === self::$cache) {
            self::$cache = $this->callProvider();

            if (null === self::$cache) {
                throw new AppException(EnumErrors::RATE_PROVIDER_RESPONSE_CONTENT_ERROR);
            }
        }

        if (null !== self::$cache->{$currencyCode}) {
            return new Decimal((string) self::$cache->{$currencyCode});
        }

        return null;
    }

    private function getProviderUrl(): string
    {
        if (null === $this->exchangerApiUrl || !mb_strstr($this->exchangerApiUrl, '%s')) {
            throw new AppException(EnumErrors::RATE_PROVIDER_INVALID_URL);
        }

        if (null === $this->exchangerApiKey) {
            throw new AppException(EnumErrors::RATE_PROVIDER_INVALID_KEY);
        }

        return sprintf($this->exchangerApiUrl, $this->exchangerApiKey);
    }

    private function callProvider(): stdClass
    {
        try {
            $response = $this->client->request('GET', $this->getProviderUrl());
        } catch (RequestException $requestException) {
            // todo: logging
            throw new AppException(EnumErrors::RATE_PROVIDER_RESPONSE_ERROR);
        }

        if (200 !== $response->getStatusCode()) {
            throw new AppException(EnumErrors::RATE_PROVIDER_RESPONSE_ERROR);
        }

        if ('application/json; charset=utf-8' !== mb_strtolower($response->getHeaderLine('content-type'))) {
            throw new AppException(EnumErrors::RATE_PROVIDER_RESPONSE_ERROR);
        }

        try {
            $content = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

            return $content?->rates;
        } catch (JsonException $exception) {
            throw new AppException(EnumErrors::RATE_PROVIDER_RESPONSE_CONTENT_ERROR);
        }
    }
}
