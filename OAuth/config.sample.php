<?php
require_once __DIR__ . '/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setClientId('YOUR_CLIENT_ID_HERE');
$client->setClientSecret('YOUR_CLIENT_SECRET_HERE');
$client->setRedirectUri('http://localhost/php_wt/OAuth/callback.php');

$client->addScope('email');
$client->addScope('profile');

// Optional but recommended
$client->setAccessType('offline');
$client->setPrompt('select_account');

return $client;
?>
