<?php
$response = new stdClass();
$response->status = 'error';

try {
  if (isset($eventData->new_username)) {
        
    $eventData->new_username = strtolower(trim($eventData->new_username));
    
    if (!preg_match('/^\w{1,32}$/', $eventData->new_username))
      $response->errorMessage = "Incorrect username format.\nThe username can contain solely letters, digits and \"_\" sign and should contain at least 1 and at most 32 of these.\nAny whitespaces at the beginning or the end are trimmed.";
    
    $stmt = $conn->prepare("update User set Username = :username where ID = :id");
    $stmt->bindValue(":username", $eventData->new_username, PDO::PARAM_STR);
    $stmt->bindValue(":id", $_SESSION['userID'], PDO::PARAM_INT);
    $stmt->execute();
    
    $response->status = 'success';
    
  } else
    $response->errorMessage = 'Incorrect eventData.';

} catch (PDOException $e) {
  $response->errorMessage = "Database error processing the event.";
  
} catch (Exception $e) {
  $response->errorMessage = 'Unknown error.';
}

echo (json_encode($response));
?>