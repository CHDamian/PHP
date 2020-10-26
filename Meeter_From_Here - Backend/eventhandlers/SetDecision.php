<?php
    $response = new stdClass();
    $response->status = 'error';
    try
    {
        if(isset($eventData->meetingID) && is_string($eventData->meetingID) &&
        isset($eventData->status) && is_string($eventData->status))
        {
            $stmt = $conn->prepare("select MP.isOwner from Meeting_Participant as MP 
            join Meeting on MP.Meeting = Meeting.ID
            where Meeting.PublicID = :publicID and MP.User = :userID");
            $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
            $stmt->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
            $stmt->execute();
            if($stmt->fetchColumn() != true)
            {
                $response->errorMessage = "User is not owner of this meeting or meeting does not exist.";
            }
            else if($eventData->status == "placeChosen" && 
            isset($eventData->placeID) && is_int($eventData->placeID))
            {
                $stmt = $conn->prepare("select count(*) from Meeting_SuggestedPlace as MSP 
                join Meeting on MSP.Meeting = Meeting.ID
                where Meeting.PublicID = :publicID and MSP.Place = :placeID");
                $stmt->bindParam(':placeID', $eventData->placeID, PDO::PARAM_INT);
                $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                $stmt->execute();
                if($stmt->fetchColumn() != 1)
                {
                    $response->errorMessage = "Incorrect eventData.";
                }
                else
                {
                    $stmt = $conn->prepare("update Meeting set IsCancelled = 0, FinalPlace = :placeID
                    where PublicID = :publicID");
                    $stmt->bindParam(':placeID', $eventData->placeID, PDO::PARAM_INT);
                    $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                    $res = $stmt->execute();
                    if(!$res)
                    {
                        $response->errorMessage = "An error occured while executing statement.";
                    }
                    else
                    {
                        $response->status = 'success';
                    }
                }
            }
            else if($eventData->status == "cancelled")
            {
                $stmt = $conn->prepare("update Meeting set IsCancelled = 1, FinalPlace = null
                where PublicID = :publicID");
                $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                $res = $stmt->execute();
                if(!$res)
                {
                    $response->errorMessage = "An error occured while executing statement.";
                }
                else
                {
                    $response->status = 'success';
                }
            }
            else if($eventData->status == "placeUnchosen")
            {
                $stmt = $conn->prepare("update Meeting set IsCancelled = 0, FinalPlace = null
                where PublicID = :publicID");
                $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                $res = $stmt->execute();
                if(!$res)
                {
                    $response->errorMessage = "An error occured while executing statement.";
                }
                else
                {
                    $response->status = 'success';
                }
            }
            else
            {
                $response->errorMessage = 'Incorrect eventData.';
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
        $conn->rollBack();
    } 
    catch (Exception $e) 
    {
        $response->errorMessage = 'Unknown error.';
        $conn->rollBack();
    }
    echo (json_encode($response));
?>