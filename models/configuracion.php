<?php
  require_once '../vendor/autoload.php'; ;

  $clientID = '452066751265-statvv86qi1ojr8qcqip0f835eoh1t6q.apps.googleusercontent.com';
  $clientSecret = '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';
  $redirectUri = 'http://www.orbispay.com.co/views/perfil.php';

  // create Client Request to access Google API
  $client = new Google_Client();
  $client->setClientId($clientID);
  $client->setClientSecret($clientSecret);
  $client->setRedirectUri($redirectUri);
  $client->addScope("email");
  $client->addScope("profile");

 
?>