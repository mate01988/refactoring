<?php

declare(strict_types=1);

namespace App;

use App\Exception\AppException;
use App\Exception\EnumErrors;
use App\Model\Transaction;
use App\Service\FileInput;
use App\Service\TransactionCalc;

class System
{
    private static ?array $config = null;

    public static function getConfig(string $name): ?string
    {
        if (null === self::$config) {
            self::$config = parse_ini_file(APP_DIR.'/config.ini');
        }

        return self::$config[$name] ?? null;
    }
    private FileInput $fileInput;
    private TransactionCalc $transactionCalc;

    public function __construct(private array $arguments)
    {
        $this->fileInput = new FileInput();
        $this->transactionCalc = new TransactionCalc();
    }

    public function run(): array
    {
        $output = [];

        if (!isset($this->arguments[1])) {
            throw new AppException(EnumErrors::NO_ARGUMENT_INPUT_FILE);
        }

        $fileInputPath = $this->arguments[1];

        $this->fileInput->map($fileInputPath, function (Transaction $transaction) use (&$output): void {
            $output[] = $this->transactionCalc->commission($transaction)->toFixed(2);
        });

        return $output;
    }
}
