<?php
    $response = new stdClass();
    $response->status = 'error';
    try
    {
        $conn->beginTransaction();
        if(isset($eventData->meetingID) && is_string($eventData->meetingID) &&
        isset($eventData->userLocation) && is_object($eventData->userLocation) &&
        isset($eventData->userLocation->latitude) && is_string($eventData->userLocation->latitude) &&
        isset($eventData->userLocation->longitude) && is_string($eventData->userLocation->longitude))
        {
            $stmt = $conn->prepare("select ID from Meeting where PublicID = :publicID");
            $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
            $stmt->execute();
            $meetingID = $stmt->fetchColumn();
            if($meetingID == false)
            {
                $response->errorMessage = 'The meeting does not exist.';
                $conn->rollBack();
            }
            else if(!preg_match('@^[-+]{0,1}[0-9]+\.[0-9]+$@', $eventData->userLocation->latitude) ||
                !preg_match('@^[-+]{0,1}[0-9]+\.[0-9]+$@', $eventData->userLocation->longitude))
            {
                $response->errorMessage = 'Incorrect coordinates.';
                $conn->rollBack();
            }
            else
            {
                $stmt = $conn->prepare("select count(*) from Meeting_Participant where User = :userID and Meeting = :meetingID");
                $stmt->bindValue(":meetingID", $meetingID, PDO::PARAM_STR);
                $stmt->bindValue(":userID", $_SESSION['userID'], PDO::PARAM_INT);
                $stmt->execute();
                $res = $stmt->fetchColumn();
                if($res > 0)
                {
                  $response->errorMessage = 'The user is already a participant of this event.';
                  $conn->rollBack();
                }
                else if(!preg_match('@^[-+]{0,1}[0-9]+\.[0-9]+$@', $eventData->userLocation->latitude) ||
                    !preg_match('@^[-+]{0,1}[0-9]+\.[0-9]+$@', $eventData->userLocation->longitude))
                {
                    $response->errorMessage = 'Incorrect coordinates.';
                    $conn->rollBack();
                }
                else
                {
                    $stmt = $conn->prepare("insert into Meeting_Participant (Meeting, User, Latitude, Longitude) values (:meetingID, :userID, :lat, :lon)");
                    $stmt->bindValue(":meetingID", $meetingID, PDO::PARAM_STR);
                    $stmt->bindValue(":userID", $_SESSION['userID'], PDO::PARAM_INT);
                    $stmt->bindValue(":lat", $eventData->userLocation->latitude, PDO::PARAM_STR);
                    $stmt->bindValue(":lon", $eventData->userLocation->longitude, PDO::PARAM_STR);
                    $res = $stmt->execute();
                    if(!$res)
                    {
                        $response->errorMessage = 'An error occured while executing statement.';
                        $conn->rollBack();
                    }
                    else
                    {
                        $response->status = 'success';
                        $conn->commit();
                    }
                }
            }
        }
        else 
        {
            $response->errorMessage = 'Incorrect eventData.';
            $conn->rollBack();
        }
    }
    catch (PDOException $e) 
    {
        $response->errorMessage = "Database error processing the event.";
        $conn->rollBack();
    } 
    catch (Exception $e) 
    {
        $response->errorMessage = 'Unknown error.';
        $conn->rollBack();
    }

    echo (json_encode($response));
?>