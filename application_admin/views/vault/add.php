<?php
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_DriveService.php';

$client = new Google_Client();
// Get your credentials from the APIs Console
$client->setClientId('850644124396.apps.googleusercontent.com');
$client->setClientSecret('_uAv0_nK7ql110p94exhRTX2');
$client->setRedirectUri('https://admin.chinasavvy.com/oauth2callback');
$client->setScopes(array('https://www.googleapis.com/auth/drive'));

$service = new Google_DriveService($client);

$authUrl = $client->createAuthUrl();

//Request authorization
print "Please visit:\n$authUrl\n\n";
print "Please enter the auth code:\n";
$authCode = trim(fgets(STDIN));

// Exchange authorization code for access token
$accessToken = $client->authenticate($authCode);
$client->setAccessToken($accessToken);

//Insert a file
$file = new Google_DriveFile();
$file->setTitle('My document');
$file->setDescription('A test document');
$file->setMimeType('text/plain');

$data = file_get_contents('document.txt');

$createdFile = $service->files->insert($file, array(
      'data' => $data,
      'mimeType' => 'text/plain',
    ));

print_r($createdFile);
?>