<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * A sample application that runs multiple requests against the AdSense Host
 * API. These include:
 * <ul>
 * <li>Getting a list of all host ad clients</li>
 * <li>Getting a list of all host custom channels</li>
 * <li>Adding a new host custom channel</li>
 * <li>Updating an existing host custom channel</li>
 * <li>Deleting a host custom channel</li>
 * <li>Getting a list of all host URL channels</li>
 * <li>Adding a new host URL channel</li>
 * <li>Deleting an existing host URL channel</li>
 * <li>Running a report for a host ad client, for the past 7 days</li>
 * </ul>
 *
 * If you give PUB_ACCOUNT_ID a real account ID, the following requests will
 * also run:
 * <ul>
 * <li>Getting a list of all publisher ad clients</li>
 * <li>Getting a list of all publisher ad units</li>
 * <li>Adding a new ad unit</li>
 * <li>Updating an existing ad unit</li>
 * <li>Deleting an ad unit</li>
 * <li>Running a report for a publisher ad client, for the past 7 days</li>
 * </ul>
 *
 * Other samples are included for illustration purposes, but won't be run:
 * <ul>
 * <li>Getting the account data for an existing publisher, given their ad client
 * ID</li>
 * <li>Starting an association session</li>
 * <li>Verifying an association session</li>
 * </ul>
 *
 */

require_once 'templates/base.php';
session_start();

/************************************************
  ATTENTION: Change this path to point to your
  client library installation!
 ************************************************/
set_include_path('/path/to/clientlib' . PATH_SEPARATOR . get_include_path());

require_once 'Google/Client.php';
require_once 'Google/Service/AdSenseHost.php';

// Autoload example classes.
function __autoload($class_name) {
  include 'examples/' . $class_name . '.php';
}

// Max results per page.
define('MAX_LIST_PAGE_SIZE', 50, true);

// Change this constant to an example publisher account ID if you want the
// publisher samples to run.
define('PUB_ACCOUNT_ID', 'INSERT_CLIENT_PUB_ID_HERE', true);

// Set up authentication.
$client = new Google_Client();
$client->addScope('https://www.googleapis.com/auth/adsensehost');
$client->setAccessType('offline');

// Be sure to replace the contents of client_secrets.json with your developer
// credentials.
$client->setAuthConfigFile('client_secrets.json');

// Create service.
$service = new Google_Service_AdSenseHost($client);

// If we're logging out we just need to clear our local access token
if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

// If we have a code back from the OAuth 2.0 flow, we need to exchange that
// with the authenticate() function. We store the resultant access token
// bundle in the session, and redirect to this page.
if (isset($_GET['code'])) {
  $client->authenticate($_GET['code']);
  $_SESSION['access_token'] = $client->getAccessToken();
  $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
  header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
  exit;
}

// If we have an access token, we can make requests, else we generate an
// authentication URL.
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
  $client->setAccessToken($_SESSION['access_token']);
} else {
  $authUrl = $client->createAuthUrl();
}

echo pageHeader('AdSense Host API sample');

echo '<div><div class="request">';
if (isset($authUrl)) {
  echo '<a class="login" href="' . $authUrl . '">Connect Me!</a>';
} else {
  echo '<a class="logout" href="?logout">Logout</a>';
};
echo '</div>';

if ($client->getAccessToken()) {
  echo '<pre class="result">';
  // Now we're signed in, we can make our requests.
  makeRequests($service);
  // Note that we re-store the access_token bundle, just in case anything
  // changed during the request - the main thing that might happen here is the
  // access token itself is refreshed if the application has offline access.
  $_SESSION['access_token'] = $client->getAccessToken();
  echo '</pre>';
}

echo '</div>';
echo pageFooter(__FILE__);


// Makes all the API requests.
function makeRequests($service) {
  print "\n";

  $hostAdClients = GetAllAdClientsForHost::run($service, MAX_LIST_PAGE_SIZE);

  if (!empty($hostAdClients)) {
    // Get a host ad client ID, so we can run the dependent samples.
    $exampleHostAdClientId = $hostAdClients[0]['id'];

    GetAllCustomChannelsForHost::run($service, $exampleHostAdClientId,
        MAX_LIST_PAGE_SIZE);

    $customChannel =
        AddCustomChannelToHost::run($service, $exampleHostAdClientId);

    $customChannel = UpdateCustomChannelOnHost::run($service,
        $exampleHostAdClientId, $customChannel->getId());

    DeleteCustomChannelOnHost::run($service, $exampleHostAdClientId,
        $customChannel->getId());

    GetAllUrlChannelsForHost::run($service, $exampleHostAdClientId,
        MAX_LIST_PAGE_SIZE);

    $urlChannel =
        AddUrlChannelToHost::run($service, $exampleHostAdClientId);

    DeleteUrlChannelOnHost::run($service, $exampleHostAdClientId,
        $urlChannel->getId());

    GenerateReportForHost::run($service, $exampleHostAdClientId);
  } else {
    print 'No host ad clients found, unable to run dependent examples.';
  }

  if (PUB_ACCOUNT_ID != 'INSERT_CLIENT_PUB_ID_HERE') {
    $pubAdClients = GetAllAdClientsForPublisher::run($service, PUB_ACCOUNT_ID,
        MAX_LIST_PAGE_SIZE);

    if (!empty($pubAdClients)) {
      // Get a host ad client ID, so we can run the dependent samples.
      $examplePubAdClientId = $pubAdClients[0]['id'];

      GetAllAdUnitsForPublisher::run($service, PUB_ACCOUNT_ID,
          $examplePubAdClientId, MAX_LIST_PAGE_SIZE);

      $adUnit = AddAdUnitToPublisher::run($service, PUB_ACCOUNT_ID,
          $examplePubAdClientId);

      $adUnit = UpdateAdUnitOnPublisher::run($service, PUB_ACCOUNT_ID,
          $examplePubAdClientId, $adUnit->getId());

      DeleteAdUnitOnPublisher::run($service, PUB_ACCOUNT_ID,
          $examplePubAdClientId, $adUnit->getId());

      GenerateReportForPublisher::run($service, PUB_ACCOUNT_ID,
          $examplePubAdClientId);
    } else {
      print 'No publisher ad clients found, unable to run dependent examples.';
    }
  }
}

/**
 * Returns a (hopefully) unique value based on the system time.
 * @return string a unique value based on the system time.
 */
function getUniqueName() {
  return str_replace('.', '', microtime(true));
}
