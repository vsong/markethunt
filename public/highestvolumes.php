<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");

header("Location: https://$NEW_HOST$_SERVER[REQUEST_URI]");
exit();