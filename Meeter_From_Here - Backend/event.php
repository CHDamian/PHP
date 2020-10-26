<?php

require_once('private/connect.php');

header('Content-type: application/json; charset=utf-8');

if (!empty($_POST['eventName'])) {
  $eventData = NULL;
  if (!empty($_POST['eventData']))
    $eventData = json_decode($_POST['eventData']);
  switch ($_POST['eventName']) {
    //eventy, które nie są dla zalogowanych
    case 'UserCreated':
      require_once('eventhandlers/UserCreated.php');
      break;
    case 'UserLoggedIn':
      require_once('eventhandlers/UserLoggedIn.php');
      break;
    default:
      if (!empty($_POST['sessionID'])) {
        session_id($_POST['sessionID']);
        session_start();
        if (empty($_SESSION['userID'])) {
          session_unset();
          die('{"status":"error","errorMessage":"Incorrect sessionID."}');
        } else {
          /*
            Przydatne zmienne:
              - $_SESSION['userID'] - identyfikator usera z bazy danych
              - $eventData - obiekt z danymi naszego eventu
              - $conn - połączenie z bazą danych
          */
          switch ($_POST['eventName']) {
            //eventy, które są dla zalogowanych
            case 'UserLoggedOut':
              require_once('eventhandlers/UserLoggedOut.php');
              break;
            case 'UsernameAmended':
              require_once('eventhandlers/UsernameAmended.php');
              break;
            case 'DownloadMeetings':
              require_once('eventhandlers/DownloadMeetings.php');
              break;
            case 'DownloadMembers':
              require_once('eventhandlers/DownloadMembers.php');
              break;
            case 'DownloadPlaces':
              require_once('eventhandlers/DownloadPlaces.php');
              break;
            case 'VoteForPlaces':
              require_once('eventhandlers/VoteForPlaces.php');
              break;
            case 'UserStatusSet':
              require_once('eventhandlers/UserStatusSet.php');
              break;
            case 'SetDecision':
              require_once('eventhandlers/SetDecision.php');
              break;
            case 'UserJoined':
              require_once('eventhandlers/UserJoined.php');
              break;
            case 'MeetingAdded':
              require_once('eventhandlers/MeetingAdded.php');
              break;
            case 'GeneratePlaces':
              require_once('eventhandlers/GeneratePlaces.php');
              break;
          }
          session_write_close();
        }
      } else
          die('{"status":"error","errorMessage":"Please provide the sessionID."}');
      break;
  }
}

?>