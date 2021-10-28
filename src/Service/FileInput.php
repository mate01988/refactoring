<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\AppException;
use App\Exception\EnumErrors;
use App\Model\Transaction;
use const DIRECTORY_SEPARATOR;

class FileInput
{
    public function map(string $filePath, callable $fn): void
    {
        $fullFilePath = APP_DIR.DIRECTORY_SEPARATOR.$filePath;

        if (!file_exists($fullFilePath)) {
            throw new AppException(EnumErrors::INPUT_FILE_DOES_NOT_EXIST);
        }

        $handle = fopen($fullFilePath, 'r');
        if (!$handle) {
            throw new AppException(EnumErrors::INPUT_FILE_CANNOT_BE_READ);
        }

        while (($line = fgets($handle)) !== false) {
            $data = json_decode($line, true);
            $fn((new Transaction($data)));
        }

        fclose($handle);
    }
}
