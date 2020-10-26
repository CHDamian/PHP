<?php
$response = new stdClass();
$response->status = 'error';

try {
  if (isset($eventData->username) && is_string($eventData->username)
      && isset($eventData->password) && is_string($eventData->password)
      && isset($eventData->email) && is_string($eventData->email)) {
        
    $eventData->username = strtolower(trim($eventData->username));
    $eventData->email = strtolower(trim($eventData->email));
    
    if (!preg_match('/^\w{1,32}$/', $eventData->username))
      $response->errorMessage = "Incorrect username format.\nThe username can contain solely letters, digits and \"_\" sign and should contain at least 1 and at most 32 of these.\nAny whitespaces at the beginning or the end are trimmed.";
    
    else if (strlen($eventData->password) < 6 || strlen($eventData->password) > 255)
      $response->errorMessage = 'The password should be at least 6 and at most 255 characters long.';
    
    else if (!filter_var($eventData->email, FILTER_VALIDATE_EMAIL) || strlen($eventData->email) > 255)
      $response->errorMessage = 'Sorry, this email address cannot be considered credible...';
    
    else {
      $stmt = $conn->prepare("select count(*) from User where Username = :username");
      $stmt->bindValue(":username", $eventData->username, PDO::PARAM_STR);
      $stmt->execute();
      if ($stmt->fetchColumn() != 0)
        $response->errorMessage = 'The username is already taken.';
      
      else {
        $stmt = $conn->prepare("select count(*) from User where Email = :email");
        $stmt->bindValue(":email", $eventData->email, PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->fetchColumn() != 0)
          $response->errorMessage = 'The email is already associated with some account.';
        
        else {
          $stmt = $conn->prepare("insert into User (Username, Email, Password) values(:username, :email, :password)");
          $stmt->bindValue(":username", $eventData->username, PDO::PARAM_STR);
          $stmt->bindValue(":email", $eventData->email, PDO::PARAM_STR);
          $stmt->bindValue(":password", password_hash($eventData->password, PASSWORD_DEFAULT), PDO::PARAM_STR);
          if (!$stmt->execute())
            $response->errorMessage('Could not add this user.');
          else {
            $response->status = 'success';
          }
        }
      }
    }
    
  } else
    $response->errorMessage = 'Incorrect eventData.';

} catch (PDOException $e) {
  $response->errorMessage = "Database error processing the event:.";
  
} catch (Exception $e) {
  $response->errorMessage = 'Unknown error.';
}

echo (json_encode($response));
?>