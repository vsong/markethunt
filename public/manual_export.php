<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");

echo $twig->render('manual_export.twig', [
    'new_host' => $NEW_HOST
]);
