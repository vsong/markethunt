<?php

namespace App\Controller;

use App\DataService\MarketInfoQueryService;
use App\DataTransferObject\ItemMarketHistory;
use App\Util\ResponseUtils;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Http\Response as Response;

class MarketInfoController
{
    private MarketInfoQueryService $marketInfoQueryService;

    public function __construct(ContainerInterface $container) {
        $this->marketInfoQueryService = $container->get('marketInfoQueryService');
    }

    public function GetAllItemHeaders(Request $request, Response $response, $args) {
        return $response->withJson($this->marketInfoQueryService->getAllItemHeaders());
    }

    public function GetItem(Request $request, Response $response, $args) {
        $itemId = filter_var($args['itemId'], FILTER_VALIDATE_INT);

        if ($itemId === false) {
            return ResponseUtils::Basic404Response($response, 'Item ID not found');
        }

        $itemInfo = $this->marketInfoQueryService->getItemInfo($itemId);

        if ($itemInfo === null) {
            return ResponseUtils::Basic404Response($response, 'Item ID not found');
        }

        $marketData = $this->marketInfoQueryService->getItemMarketHistory($itemId);

        return $response->withJson(new ItemMarketHistory($itemInfo, $marketData));
    }
}