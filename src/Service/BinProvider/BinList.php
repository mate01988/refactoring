<?php

declare(strict_types=1);

namespace App\Service\BinProvider;

use App\Exception\AppException;
use App\Exception\EnumErrors;
use App\Infrastructure\Service\BinProviderInterface;
use App\System;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JsonException;
use stdClass;

class BinList implements BinProviderInterface
{
    private static array $cache = [];
    private ?string $binListUrl;
    private Client $client;

    public function __construct()
    {
        $this->binListUrl = System::getConfig('BIN_PROVIDER_URL');
        $this->client = new \GuzzleHttp\Client();
    }

    public function details(string $bin): stdClass
    {
        if (!isset(self::$cache[$bin])) {
            self::$cache[$bin] = $this->callProvider($bin);
        }

        return self::$cache[$bin];
    }

    public function getCountryCode(string $bin): string
    {
        return $this->details($bin)?->country?->alpha2 ?? throw new AppException(EnumErrors::BIN_PROVIDER_RESPONSE_CONTENT_ERROR);
    }

    private function getProviderUrl(string $bin): string
    {
        if (null === $this->binListUrl || !mb_strstr($this->binListUrl, '%s')) {
            throw new AppException(EnumErrors::BIN_PROVIDER_INVALID_URL);
        }

        return sprintf($this->binListUrl, $bin);
    }

    private function callProvider(string $bin): stdClass
    {
        try {
            $response = $this->client->request('GET', $this->getProviderUrl($bin));
        } catch (RequestException $requestException) {
            // todo: logging
            throw new AppException(EnumErrors::BIN_PROVIDER_RESPONSE_ERROR);
        }

        if (200 !== $response->getStatusCode()) {
            throw new AppException(EnumErrors::BIN_PROVIDER_RESPONSE_ERROR);
        }

        if ('application/json; charset=utf-8' !== $response->getHeaderLine('content-type')) {
            throw new AppException(EnumErrors::BIN_PROVIDER_RESPONSE_ERROR);
        }

        try {
            return json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new AppException(EnumErrors::BIN_PROVIDER_RESPONSE_CONTENT_ERROR);
        }
    }
}
