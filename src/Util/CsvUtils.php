<?php

namespace App\Util;

use InvalidArgumentException;
use RuntimeException;

class CsvUtils
{
    /**
     * @param array<string, mixed>[] $data Non-empty array of associative arrays, with each array representing a row
     * @param array<string, callable> $columnMap Array of columns to print, with column names as the key and an
     * associated nullable getter method to extract the value from the provided row. If null, then the value is
     * retrieved directly from the row with the column name as the key.
     * @return string The whole csv file as a string
     * @throws RuntimeException Thrown if the temp csv file could not be written to
     */
    public static function CreateCsv(array $data, array $columnMap): string
    {
        if (count($data) == 0) {
            throw new InvalidArgumentException("Data must not be empty");
        }

        $f = fopen('php://memory', 'r+');

        if (fputcsv($f, array_keys($columnMap)) === false) {
            throw new RuntimeException("Error writing csv file");
        }

        foreach ($data as $row) {
            $rowData = [];

            foreach ($columnMap as $columnName => $columnGetter) {
                if ($columnGetter !== null) {
                    $rowData[$columnName] = $columnGetter($row);
                } else {
                    $rowData[$columnName] = $row[$columnName];
                }
            }

            if (fputcsv($f, $rowData) === false) {
                throw new RuntimeException("Error writing csv file");
            }
        }

        rewind($f);
        $csv = stream_get_contents($f);

        return rtrim($csv);
    }
}