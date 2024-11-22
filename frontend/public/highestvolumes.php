<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");
require_once($_SERVER['APPDIR'] . "/model/ItemModel.php");

try {
    // get date params
    $from = validateISODate($_GET['from'] ?? '', 
        yesterdayAsISODate(), 
        '2021-12-01', 
        null
    );
    $to = validateISODate($_GET['to'] ?? '', 
        yesterdayAsISODate(),
        null, 
        yesterdayAsISODate()
    );

    if (!isValidISODateRange($from, $to)) {
        throw new Exception("The 'from' date is larger than or equal to 'to' date");
    }

    $date_range = compact('from', 'to');
} catch (Exception $e) {
    $date_range = createISODateRangeYesterday('0 days');
}

if ($date_range['from'] === $date_range['to']) {
    $header_title = 'Highest volume items on ' . $date_range['from'];
} else {
    $header_title = 'Highest volume items from ' . $date_range['from'] . ' to ' . $date_range['to'];
}
echo $twig->render('highestvolumes.twig', [
    'header_title' => $header_title,
    'items' => getCumulativeVolume($date_range['from'], $date_range['to']),
    'from' => $date_range['from'],
    'to' => $date_range['to']
]);
