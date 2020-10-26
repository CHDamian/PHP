<?php
    $response = new stdClass();
    $response->status = 'error';
    try{
        if(isset($eventData->meetingID) && is_string($eventData->meetingID) &&
        isset($eventData->willAttend) && is_bool($eventData->willAttend)){
            $stmt = $conn->prepare("select ID from Meeting where PublicID = :publicID");
            $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
            $stmt->execute();
            $meetingID = $stmt->fetchColumn();
            if($meetingID == false)
            {
                $response->errorMessage = 'The meeting does not exist.';
            }
            else
            {
                $stmt = $conn->prepare("select count(*) from Meeting_Participant where Meeting = :meetingID and User = :userID");
                $stmt->bindValue(":meetingID", $meetingID, PDO::PARAM_INT);
                $stmt->bindValue(":userID", $_SESSION['userID'], PDO::PARAM_INT);
                $res = $stmt->execute();
                if(!$res)
                {
                    $response->errorMessage = 'An error occured while searching for info about user.';
                }
                else if($stmt->fetchColumn() != 1)
                {
                    $response->errorMessage = 'User is not a participant.';
                }
                else
                {

                    $stmt = $conn->prepare("update Meeting_Participant set WillAttend = :status where Meeting = :meetingID and User = :userID");
                    $stmt->bindValue(":meetingID", $meetingID, PDO::PARAM_INT);
                    $stmt->bindValue(":userID", $_SESSION['userID'], PDO::PARAM_INT);
                    $stmt->bindValue(":status", $eventData->willAttend, PDO::PARAM_BOOL);
                    $res = $stmt->execute();
                    if(!$res)
                    {
                        $response->errorMessage = 'An error occured while executing statement.';
                    }
                    else
                    {
                        $response->status = 'success';
                    }
                }
            }
        }
        else 
        {
            $response->errorMessage = 'Incorrect eventData.';
        }
    }
    catch (PDOException $e) 
    {
        $response->errorMessage = "Database error processing the event.";
    } 
    catch (Exception $e) 
    {
        $response->errorMessage = 'Unknown error.';
    }
  
    echo (json_encode($response));
?>