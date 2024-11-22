<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");
require_once($_SERVER['APPDIR'] . "/model/ItemModel.php");

$volume_limit = filter_input(INPUT_GET, 'volume_limit', 
    FILTER_VALIDATE_INT, 
    array("options" => array("min_range"=>0, "max_range"=>1000000000)));

if ($volume_limit === false || $volume_limit === null) {
    $volume_limit = 1000000;
}

// period offsets have 1 additional day because price/volume data is delayed 1 day.
$period = $_GET['period'] ?? 'custom';
if ($period === 'day') {
    $date_range = createISODateRangeYesterday('-1 day');
} else if ($period === 'week') {
    $date_range = createISODateRangeYesterday('-7 day');
} else if ($period === 'month') {
    $date_range = createISODateRangeYesterday('-1 month');
} else if ($period === 'qtr') {
    $date_range = createISODateRangeYesterday('-90 day');
} else if ($period === 'year') {
    $date_range = createISODateRangeYesterday('-1 year');
} else if ($period === 'custom') {
    try {
        // get date params
        $from = validateISODate($_GET['from'] ?? '', 
            createISODateRangeYesterday('-7 day')['from'], 
            '2020-08-20', 
            null
        );
        $to = yesterdayAsISODate();

        if (!isValidISODateRange($from, $to)) {
            throw new Exception("The 'from' date is larger than or equal to 'to' date");
        }

        $date_range = compact('from', 'to');
    } catch (Exception $e) {
        $date_range = createISODateRangeYesterday('-7 day');
    }
} else {
    $date_range = createISODateRangeYesterday('-7 day');
}

echo $twig->render('topmovers.twig', [
    'date_range' => $date_range,
    'volume_limit' => $volume_limit,
    'from' => $date_range['from'],
    'to' => $date_range['to'],
    'winner_dataset' => getTopMovers($date_range['from'], $volume_limit, true),
    'loser_dataset' => getTopMovers($date_range['from'], $volume_limit, false),
]);
?>
