<?php
   $response = new stdClass();
   $response->status = 'error';
   try
   {
        $stmt = $conn->prepare("select * from Meeting 
        join Meeting_Participant as MP on Meeting.ID = MP.Meeting
        where MP.User = :userID");
        $stmt->bindParam(':userID', $_SESSION['userID'], PDO::PARAM_INT);
        $res=$stmt->execute();
        if(!$res)
        {
            $response->errorMessage = "An error occured while executing statement.";
        }
        else
        {
            $out = $stmt->fetchAll(PDO::FETCH_CLASS);
            $finalRes = [];
            $checker = true;
            foreach($out as $el)
            {
                $single = new stdClass();
                $single->name = $el->Name;
                $single->meetingID = $el->PublicID;
                $single->isOwner = $el->IsOwner;
                $single->willAttend = $el->WillAttend;
                $single->time = $el->Time;
                if($el->IsCancelled)
                {
                    $single->decision = "cancelled";
                }
                else if($el->FinalPlace != null)
                {
                    $single->decision = "placeChosen";
                    $stmt = $conn->prepare("select ID as placeID, Name as name, Address as address, 
                    Latitude as latitude, Longitude as longitude
                    from Place where ID = :placeID");
                    $stmt->bindParam(':placeID', $el->FinalPlace, PDO::PARAM_INT);
                    $res=$stmt->execute();
                    if(!$res)
                    {
                        $checker = false;
                        break;
                    }
                    $single->finalPlace = $stmt->fetch(PDO::FETCH_OBJ);
                }
                else
                {
                    $single->decision = "placeUnchosen";
                }
                array_push($finalRes,$single);
            }
            if(!$checker)
            {
                $response->errorMessage = "An error occured while creating output.";
            }
            else
            {
                $response->status = 'success';
                $response->meetings = $finalRes;
            }
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