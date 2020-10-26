<?php
    $response = new stdClass();
    $response->status = 'error';
    try {
        $conn->beginTransaction();
        if(isset($eventData->userLocation) && is_object($eventData->userLocation) &&
        isset($eventData->userLocation->latitude) && is_string($eventData->userLocation->latitude) &&
        isset($eventData->userLocation->longitude) && is_string($eventData->userLocation->longitude) &&
        isset($eventData->name) && is_string($eventData->name) &&
        isset($eventData->time) && is_string($eventData->time))
        {
            if(!preg_match('@^[-+]{0,1}[0-9]+\.[0-9]+$@', $eventData->userLocation->latitude) ||
                !preg_match('@^[-+]{0,1}[0-9]+\.[0-9]+$@', $eventData->userLocation->longitude))
            {
                $response->errorMessage = 'Incorrect coordinates.';
                $conn->rollBack();
            }
            else if(strtotime($eventData->time) && $eventData->time!="")
            {
                $stmt = $conn->prepare("insert into Meeting (PublicID, Time, Name) values (:public_id, :data, :name)");
                $publicID = md5(rand());
                $stmt->bindValue(":public_id", $publicID, PDO::PARAM_STR);
                $stmt->bindValue(":data", $eventData->time, PDO::PARAM_STR);
                $stmt->bindValue(":name", $eventData->name, PDO::PARAM_STR);
                $res = $stmt->execute();
                if(!$res)
                {
                    $response->errorMessage = 'An error occured while executing statement.';
                    $conn->rollBack();
                }
                else
                {
                    $stmt = $conn->prepare("insert into Meeting_Participant (Meeting, User, WillAttend, isOwner
                    , Latitude, Longitude) values (LAST_INSERT_ID(), :userID, false, true, :lat, :lon)");
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
                        $response->meetingID = $publicID;
                        $conn->commit();
                    }
                }
            }
            else
            {
                $response->errorMessage = 'Incorrect data format.';
                $conn->rollBack();
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