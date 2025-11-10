<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/../vendor/autoload.php';

$client = new Google_Client();
$client->setClientId('452066751265-c9488mfv2dc1ht5p369mtmgm03ioraj9.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('https://orbispay.com.co/orbispay/controllers/google_callback.php');
$client->setScopes(['openid','email','profile']);
$client->setAccessType('offline');
$client->setPrompt('consent');

if (empty($_SESSION['oauth2state'])) {
  $_SESSION['oauth2state'] = bin2hex(random_bytes(16));
}
$client->setState($_SESSION['oauth2state']);

header('Location: '.$client->createAuthUrl());
exit;
