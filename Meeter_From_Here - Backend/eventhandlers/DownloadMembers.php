<?php
    $response = new stdClass();
    $response->status = 'error';
    try
    {
        if(isset($eventData->meetingID) && is_string($eventData->meetingID))
        {
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
                    $stmt = $conn->prepare("Select User.ID as userID, User.Username as username, 
                        MP.WillAttend as willAttend, MP.Latitude as latitude, MP.Longitude as longitude from User 
                        join Meeting_Participant as MP on User.ID = MP.User where MP.Meeting = :meetingID");
                    $stmt->bindValue(":meetingID", $meetingID, PDO::PARAM_INT);
                    $stmt->execute();
                    $res = $stmt->fetchAll(PDO::FETCH_CLASS);
                    if(!$res)
                    {
                        $response->errorMessage = 'An error occured while executing statement.';
                    }
                    else
                    {
                        $response->status = 'success';
                        $response->users = $res;
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