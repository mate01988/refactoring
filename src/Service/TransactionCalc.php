<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AppException;
use App\Exception\EnumErrors;
use App\Helper\Country;
use App\Infrastructure\Service\BinProviderInterface;
use App\Infrastructure\Service\RateProviderInterface;
use App\Model\Transaction;
use App\Service\BinProvider\BinList;
use App\Service\RateProvider\ExchangerApi;
use App\System;
use Decimal\Decimal;

class TransactionCalc
{
    private const DEFAULT_CURRENCY = 'EUR';

    private BinProviderInterface $binProvider;
    private RateProviderInterface $rateProvider;

    private Decimal $cardCommissionUE;
    private Decimal $cardCommissionNonUE;

    public function __construct()
    {
        $this->binProvider = new BinList();
        $this->rateProvider = new ExchangerApi();

        $this->cardCommissionUE = new Decimal(System::getConfig('CARD_COMMISSION_UE'));
        $this->cardCommissionNonUE = new Decimal(System::getConfig('CARD_COMMISSION_NON_UE'));
    }

    public function commission(Transaction $transaction): Decimal
    {
        return $this->getAmountFixed($transaction)->mul($this->getCommissionByCountry($transaction));
    }

    private function getAmountFixed(Transaction $transaction): Decimal
    {
        $rate = $this->rateProvider->getAmount($transaction->getCurrency());

        if (null === $rate) {
            throw new AppException(EnumErrors::RATE_PROVIDER_INVALID_AMOUNT);
        }

        if ($rate->isZero()) {
            return $transaction->getAmount();
        }

        if (self::DEFAULT_CURRENCY === $transaction->getCurrency()) {
            return $transaction->getAmount();
        }

        return $transaction->getAmount()->div($rate->toFixed(2));
    }

    private function getCommissionByCountry(Transaction $transaction): Decimal
    {
        $countryCode = $this->binProvider->getCountryCode($transaction->getBin());

        return Country::isEU($countryCode) ? $this->cardCommissionUE : $this->cardCommissionNonUE;
    }
}
