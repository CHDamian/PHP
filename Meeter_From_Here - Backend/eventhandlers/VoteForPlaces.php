<?php
   $response = new stdClass();
   $response->status = 'error';
   try
   {
    $conn->beginTransaction();   
    if(isset($eventData->meetingID) && is_string($eventData->meetingID) &&
       isset($eventData->placeIDs) && is_array($eventData->placeIDs))
       {
            $checker = true;
            $map = array();
            foreach($eventData->placeIDs as $id)
            {
                if(!is_int($id))
                {
                    $checker = false;
                    break;
                }
                if(array_key_exists($id,$map))
                {
                    $checker = false;
                    break;
                }
                $map[$id] = true;
                $stmt = $conn->prepare("select count(*) from Meeting_SuggestedPlace as MSP 
                join Meeting on MSP.Meeting = Meeting.ID
                where Meeting.PublicID = :publicID and MSP.Place = :placeID");
                $stmt->bindParam(':placeID', $id, PDO::PARAM_INT);
                $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                $stmt->execute();
                if($stmt->fetchColumn() != 1)
                {
                    $checker = false;
                    break;
                }
            }
            if(!$checker)
            {
                $response->errorMessage = 'Incorrect eventData.';
                $conn->rollBack();
            }
            else
            {
                $stmt = $conn->prepare("delete MVP from Meeting_VoteForPlace MVP 
                join Meeting on MVP.Meeting = Meeting.ID 
                where Meeting.PublicID = :publicID and MVP.User = :userID");
                $stmt->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
                $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                $res = $stmt->execute();
                if(!$res)
                {
                    $response->errorMessage = 'An error occured while preparing for saving votes.';
                    $conn->rollBack();
                }
                else
                {
                    $checker = true;
                    foreach($eventData->placeIDs as $id)
                    {
                        $stmt = $conn->prepare("insert into Meeting_VoteForPlace (Meeting, User, Place)
                        values ((select ID from Meeting where PublicID = :publicID), :userID, :placeID)");
                        $stmt->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
                        $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                        $stmt->bindParam(':placeID', $id, PDO::PARAM_INT);
                        $res = $stmt->execute();
                        if(!$res)
                        {
                            $checker = false;    
                            break;
                        }
                    }
                    if(!$checker)
                    {
                        $response->errorMessage = 'An error occured while inserting votes.';
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