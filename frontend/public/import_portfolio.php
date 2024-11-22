<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");
require_once($_SERVER['APPDIR'] . "/model/ItemModel.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || strpos($_SERVER['HTTP_REFERER'], 'https://www.mousehuntgame.com') === false) {
    header("Location: /import_howto.php");
    exit();
}

echo $twig->render('import_portfolio.twig', [
    'item_metadata' => getAllItemNamesAndLatestPrice(),
    'import_data' => json_decode($_POST['import-data']),
    'import_portfolio_name' => $_POST['import-portfolio-name']
]);
