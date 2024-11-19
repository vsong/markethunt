<?php

namespace App\Util;

use Slim\Http\Response;

class ResponseUtils
{
    public static function Respond400(Response $response, string $message) {
        return $response->withStatus(400)
            ->withHeader('Content-Type', 'text/html')
            ->write('400 Invalid Request: ' . $message);
    }

    public static function Respond404(Response $response, string $message) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('404 not found: ' . $message);
    }

    /**
     * Helper method to create a csv file given the data needed by CsvUtils::CreateCsv() and prepare an appropriate
     * response.
     * @see CsvUtils::CreateCsv()
     */
    public static function RespondCsv(Response $response, array $data, array $columnMap, $filename = null) {
        if (count($data) == 0) {
            return ResponseUtils::Respond404($response, 'No data returned, cannot create csv file');
        }

        $csv = CsvUtils::CreateCsv($data, $columnMap);
        $response->getBody()->write($csv);

        if ($filename !== null) {
            $response = $response->withHeader('Content-Disposition', "attachment; filename=\"{$filename}.csv\"");
        } else {
            $response = $response->withHeader('Content-Disposition', 'attachment');
        }

        return $response->withHeader('Content-Type', 'text/csv');
    }
}