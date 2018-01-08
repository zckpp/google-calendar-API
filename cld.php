<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
        Google_Service_Calendar::CALENDAR_READONLY)
));

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
    $client = new Google_Client();
    $client->setApplicationName(APPLICATION_NAME);
    $client->setScopes(SCOPES);
    $client->setAuthConfig(CLIENT_SECRET_PATH);
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if(!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

// Print the next 10 events on the user's calendar.
//1st floor conference room
$calendarId = 'carnegiescience.edu_3637333135313334333534@resource.calendar.google.com';
$optParams = array(
    'maxResults' => 10,
    'orderBy' => 'startTime',
    'singleEvents' => TRUE,
    'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);

$events = [];

if (count($results->getItems()) == 0) {
    print "No upcoming events found.\n";
} else {
    print "get first floor events succeed:\n";
    foreach ($results->getItems() as $key => $event) {
        $start =  strtotime($event->start->dateTime);
        if (empty($start)) {
            $start = strtotime($event->start->date);
        }
        $end =  strtotime($event->end->dateTime);
        if (empty($end)) {
            $end = strtotime($event->end->date);
        }
        $events[$key]['sum'] = $event->getSummary();
        $events[$key]['time'] = $start;
        $events[$key]['end'] = $end;
        $events[$key]['loc'] = $event->getLocation();
        $events[$key]['room'] = 1;
        if (empty($event->getLocation())) {
            $events[$key]['loc'] = "HQ-1530 1st Floor Conference Room";
        }
    }
}

// Get the API client and construct the service object.
$client1 = getClient();
$service1 = new Google_Service_Calendar($client1);

// Print the next 10 events on the user's calendar.
//3rd floor conference room
$calendarId1 = 'carnegiescience.edu_3835353632353036313937@resource.calendar.google.com';
$optParams1 = array(
    'maxResults' => 10,
    'orderBy' => 'startTime',
    'singleEvents' => TRUE,
    'timeMin' => date('c'),
);
$results1 = $service1->events->listEvents($calendarId1, $optParams1);

$events1 = [];

if (count($results1->getItems()) == 0) {
    print "No upcoming events found.\n";
} else {
    print "get third floor events succeed:\n";
    foreach ($results1->getItems() as $key => $event) {
        $start =  strtotime($event->start->dateTime);
        if (empty($start)) {
            $start = strtotime($event->start->date);
        }
        $end =  strtotime($event->end->dateTime);
        if (empty($end)) {
            $end = strtotime($event->end->date);
        }
        $events1[$key]['sum'] = $event->getSummary();
        $events1[$key]['time'] = $start;
        $events1[$key]['end'] = $end;
        $events1[$key]['loc'] = $event->getLocation();
        $events1[$key]['room'] = 2;
        if (empty($event->getLocation())) {
            $events1[$key]['loc'] = "HQ-1530 3rd Floor Conference Room";
        }
    }
}
//merge 1st and 3rd floor calendar array
$result_array = array_merge($events, $events1);
//set start time as key value pair
foreach ($result_array as $key => $part) {
    $sort[$key] = $part['time'];
}
//sort array by start time
array_multisort($sort, SORT_ASC, $result_array);
//format time for front-end display
foreach ($result_array as $key => $result) {
    $result_array[$key]['time'] = date('h:i A Y-m-d',$result['time']);
    $result_array[$key]['end'] = date('h:i A Y-m-d',$result['end']);
}

//eventbooking import
$client2 = getClient();
$service2 = new Google_Service_Calendar($client2);
$calendarId2 = '5d7m8b67bmg48ob0eolb3g9e51l9v7m9@import.calendar.google.com';
$optParams2 = array(
    'maxResults' => 10,
    'orderBy' => 'startTime',
    'singleEvents' => TRUE,
    'timeMin' => date('c'),
);
$results2 = $service2->events->listEvents($calendarId2, $optParams2);

$events2 = [];

if (count($results2->getItems()) == 0) {
    print "No upcoming events found.\n";
} else {
    print "get events succeed:\n";
    foreach ($results2->getItems() as $key => $event) {
        $start =  strtotime($event->start->dateTime);
        if (empty($start)) {
            $start = strtotime($event->start->date);
        }
        $end =  strtotime($event->end->dateTime);
        if (empty($end)) {
            $end = strtotime($event->end->date);
        }
        $events2[$key]['sum'] = $event->getSummary();
        $events2[$key]['time'] = $start;
        $events2[$key]['end'] = $end;
        $events2[$key]['loc'] = $event->getLocation();
        $events2[$key]['room'] = 2;
        if (empty($event->getLocation())) {
            $events2[$key]['loc'] = "testing";
        }
    }
}
var_dump($events2);
file_put_contents("array.json",json_encode($result_array));
file_put_contents("array_eb.json",json_encode($events2));