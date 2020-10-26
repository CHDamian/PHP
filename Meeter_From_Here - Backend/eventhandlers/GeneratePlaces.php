<?php
$response = new stdClass();
$response->status = 'error';

try {
  if (isset($eventData->meetingID) && is_string($eventData->meetingID)) {
    
    $conn->beginTransaction();
    $stmt = $conn->prepare("select M.ID from Meeting M join Meeting_Participant MP on M.ID = MP.Meeting where M.PublicID = :meeting_public_id and MP.User = :user_id and MP.IsOwner = 1");
    $stmt->bindValue(":user_id", $_SESSION['userID'], PDO::PARAM_INT);
    $stmt->bindValue(":meeting_public_id", $eventData->meetingID, PDO::PARAM_STR);
    $stmt->execute();
    $meetingID = $stmt->fetchColumn();
    
    if ($meetingID == NULL)
      $response->errorMessage = 'The user is not the owner of the meeting or the meeting does not exist.';
    
    else {
      $stmt = $conn->prepare("select count(*) from Meeting_SuggestedPlace where Meeting = :meeting_id");
      $stmt->bindValue(":meeting_id", $meetingID, PDO::PARAM_INT);
      $stmt->execute();
      
      if ($stmt->fetchColumn() != 0)
        $response->errorMessage = 'The suggested places for this meeting have already been generated.';
      
      else {
        $stmt = $conn->prepare("select avg(Latitude) Latitude, avg(Longitude) Longitude from Meeting_Participant where Meeting = :meeting_id");
        $stmt->bindValue(":meeting_id", $meetingID, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_OBJ);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 
          'https://maps.googleapis.com/maps/api/place/nearbysearch/json'
          .'?key=AIzaSyCUS0JIDVU29plA1Zd3DV_pP249lsEH-sk'
          .'&location='.$res->Latitude.','.$res->Longitude
          .'&rankby=distance'
          .'&type=cafe');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $places_data = json_decode(curl_exec($ch));
        
        if (!isset($places_data->status) || $places_data->status != "OK" || empty($places_data->results))
          $response->errorMessage = 'No nearby places have been found.';
        
        else {
          $values_span = array();
          $values = array();
          $gmaps_ids = array();
          foreach ($places_data->results as $res) {
            if (isset($res->id) && isset($res->name) && isset($res->geometry->location->lat) && isset($res->geometry->location->lng) && isset($res->vicinity) ) {
              $values_span[] = '(?, ?, ?, ?, ?)';
              $values = array_merge($values, array($res->id, $res->name, $res->vicinity, $res->geometry->location->lat, $res->geometry->location->lng));
              $gmaps_ids[] = $res->id;
            }
          }
          
          $stmt = $conn->prepare("insert into Place (GoogleMapsPlaceID, Name, Address, Latitude, Longitude) values ".implode(', ', $values_span)." on duplicate key update Name = values(Name), Address = values(Address), Latitude = values(Latitude), Longitude = values(Longitude);");
          $stmt->execute($values);
          
          $stmt = $conn->prepare("insert into Meeting_SuggestedPlace (Meeting, Place) select ?, ID from Place where GoogleMapsPlaceID in (".implode(',', array_fill(0, count($gmaps_ids), '?')).")");
          $stmt->execute(array_merge(array($meetingID), $gmaps_ids));
          
          $response->status = 'success';
        }
      }
    }
    
  } else
    $response->errorMessage = 'Incorrect eventData.';

} catch (PDOException $e) {
  $response->errorMessage = "Database error processing the event.";
  
} catch (Exception $e) {
  $response->errorMessage = 'Unknown error.';
  
} finally {
  if ($response->status != 'success')
    $conn->rollBack();
  else
    $conn->commit();
}

echo (json_encode($response));
?>