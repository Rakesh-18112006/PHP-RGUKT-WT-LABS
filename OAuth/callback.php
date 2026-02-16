<?php
$client = require __DIR__ . '/config.php';

if (!isset($_GET['code'])) {
  exit('No code returned from Google.');
}

try {
  $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

  if (isset($token['error'])) {
    exit('Token error: ' . htmlspecialchars($token['error_description'] ?? $token['error']));
  }

  $client->setAccessToken($token);

  $oauth2 = new Google_Service_Oauth2($client);
  $userInfo = $oauth2->userinfo->get();

  // Save in session (simple)
  $_SESSION['user'] = [
    'id' => $userInfo->id,
    'email' => $userInfo->email,
    'name' => $userInfo->name,
    'picture' => $userInfo->picture,
  ];

  header('Location: dashboard.php');
  exit;

} catch (Exception $e) {
  exit('Exception: ' . htmlspecialchars($e->getMessage()));
}
