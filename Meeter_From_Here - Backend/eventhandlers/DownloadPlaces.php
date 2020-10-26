<?php
   $response = new stdClass();
   $response->status = 'error';
   try
   {
       if(isset($eventData->meetingID) && is_string($eventData->meetingID))
       {
        $stmt = $conn->prepare("select *, Place.Name as Placename from Place 
        join Meeting_SuggestedPlace as MSP on Place.ID = MSP.Place
        join Meeting on MSP.Meeting = Meeting.ID
        where Meeting.PublicID = :publicID");
        $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
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
                $stmt = $conn->prepare("select Username as username, User.ID as userID from User
                join Meeting_VoteForPlace as MVP on MVP.User = User.ID
                join Meeting on Meeting.ID = MVP.Meeting
                where MVP.Place = :placeID and Meeting.PublicID = :publicID");
                $stmt->bindParam(':placeID', $el->Place, PDO::PARAM_INT);
                $stmt->bindParam(':publicID', $eventData->meetingID, PDO::PARAM_STR);
                $res=$stmt->execute();
                if(!$res)
                {
                    $checker = false;
                    break;
                }
                $single = new stdClass();
                $single->placeID = $el->Place;
                $single->address = $el->Address;
                $single->name = $el->Placename;
                $single->latitude = $el->Latitude;
                $single->longitude = $el->Longitude;
                $single->voters = $stmt->fetchAll(PDO::FETCH_CLASS);
                array_push($finalRes,$single);
            }
            if(!$checker)
            {
                $response->errorMessage = "An error occured while creating output.";
            }
            else
            {
                $response->status = 'success';
                $response->places = $finalRes;
                
            }
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