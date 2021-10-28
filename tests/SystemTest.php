<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @covers \App\System
 */
final class SystemTest extends TestCase
{
    private const REQUIRED_CONFIGURES = [
        'BIN_PROVIDER_URL',
        'RATE_PROVIDER_URL',
        'RATE_PROVIDER_API_KEY',
        'CARD_COMMISSION_UE',
        'CARD_COMMISSION_NON_UE'
    ];

    public function testGetConfig(): void
    {
        foreach (self::REQUIRED_CONFIGURES as $configName) {
            $this->assertNotEmpty(\App\System::getConfig($configName));
        }

        $this->assertStringContainsString('%s', \App\System::getConfig('BIN_PROVIDER_URL'));
        $this->assertStringContainsString('%s', \App\System::getConfig('RATE_PROVIDER_URL'));
    }

    public function testRunWithoutArguments(): void
    {
        $this->expectException(Exception::class);
        $this->getExpectedExceptionMessage(\App\Exception\EnumErrors::NO_ARGUMENT_INPUT_FILE);
        $system = new \App\System([]);
        $system->run();
    }

    public function testRunWithIncorrectArguments(): void
    {
        $this->expectException(Exception::class);
        $this->getExpectedExceptionMessage(\App\Exception\EnumErrors::INPUT_FILE_DOES_NOT_EXIST);
        $system = new \App\System(['test1234', 'test1234']);
        $system->run();
    }

    public function testRunWithCorrectArguments(): void
    {
        $system = $this->mockSystemExternalProviders(['app.php', 'input.txt']);
        $result = $system->run();

        $this->assertIsArray($result);
        $this->assertEquals($result, ['2.00', '1.00', '200.00', '2.60', '40.00']);
    }

    private function mockSystemExternalProviders(array $instanceArgs): \App\System
    {
        $stubExchangerApi = $this->createMock(\App\Service\RateProvider\ExchangerApi::class);
        $stubExchangerApi->method('getAmount')
            ->willReturn((new \Decimal\Decimal('1.00')));

        $stubBinList = $this->createMock(\App\Service\BinProvider\BinList::class);
        $stubBinList->method('getCountryCode')
            ->willReturn('PL');

        $transactionCalc = new \App\Service\TransactionCalc();
        $reflectorTransactionCalc = new ReflectionClass(App\Service\TransactionCalc::class);
        $propertyRateProvider = $reflectorTransactionCalc->getProperty('rateProvider');
        $propertyRateProvider->setAccessible(true);
        $propertyRateProvider->setValue($transactionCalc, $stubExchangerApi);

        $propertyBinProvider = $reflectorTransactionCalc->getProperty('binProvider');
        $propertyBinProvider->setAccessible(true);
        $propertyBinProvider->setValue($transactionCalc, $stubBinList);

        $system = new \App\System($instanceArgs);
        $reflectorSystem = new ReflectionClass(\App\System::class);

        $propertyTransactionCalc = $reflectorSystem->getProperty('transactionCalc');
        $propertyTransactionCalc->setAccessible(true);
        $propertyTransactionCalc->setValue($system, $transactionCalc);

        return $system;
    }
}