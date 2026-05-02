<?php
require_once __DIR__ . "/auth_lib/Session.php";
require_once __DIR__ . "/auth_lib/UserSession.php";
require_once __DIR__ . "/auth_lib/Auth.php";

$db = new PDO("sqlite:database.db");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$session = new Session();
$auth = new Auth($db, $session);

?>
