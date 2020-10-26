## Dokumentacja eventów

Wszystkie zdarzenia obsługiwane są przez plik event.php. Wykonujemy zapytanie *POST* do tego pliku z następującymi zmiennymi:

 - *sessionID* – potrzebne do wszystkich zdarzeń, które dotyczą zalogowanych użytkowników, w p.p. parametr ignorowany;
 - *eventName* – parametr zawsze wymagany;
 - *eventData* – obiekt w JSONie zazwyczaj wymagany,
w zależności od eventu obiekt ten musi zawierać odpowiednie pola, przy czym pola niepotrzebne są ignorowane.

Każde zdarzenie zwraca w odpowiedzi parametr *status*, który przyjmuje wartość *error* w przypadku błędu lub *success* w przypadku sukcesu. W przypadku błędu zazwyczaj odpowiedź zawiera również parametr *errorMessage*. Pozostałe parametry są specyficzne dla poszczególnych zdarzeń.


### *UserCreated*
#### *sessionID* – ignorowane.
#### *eventData*:
    {
	    "username": "abc",
	    "email": "valid@and.credible.com",
	    "password": "przynajmniejszescznakow"
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *UserLoggedIn*
#### *sessionID* – ignorowane.
#### *eventData*:
    {
	    "username_or_email": "abc",
	    "password": "przynajmniejszescznakow"
    }
#### odpowiedź:
    {
	    "status": "success",
	    "sessionID": "user-lu6bg5e0lc48skeovdb31591r6"
		"userID": 10,
	    "username": "dsa",
	    "email": "valid@and.incredible.com",
    }
### *UserLoggedOut*
#### *sessionID* – wymagane.
#### *eventData* – ignorowane.
#### odpowiedź:
    {
	    "status": "success"
    }
### *UsernameAmended*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "new_username": "abc1"
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *MeetingAdded*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "name":"meet",
	    "time":"2020-11-18 11:45:21 PM",
	    "userLocation":
		{
		 "latitude": "34.456457",
		 "longitude": "-423.678568"
		}
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *UserJoined*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "meetingID": "meet",
	    "userLocation":
		{
		 "latitude": "34.456457",
		 "longitude": "-423.678568"
		}
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *GeneratePlaces*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "meetingID": "meet"
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *VoteForPlaces*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "meetingID": "meet",
	    "placeIDs": [4]
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *UserStatusSet*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "meetingID": "meet",
	    "willAttend": true
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *SetDecision*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "meetingID": "meet",
	    "status": "placeChosen", 	//dopuszczalne wartości to "placeChosen", "cancelled", "placeUnchosen"
	    "placeID": 4 		//ignorowane w przypadku gdy status różny od placeChosen
    }
#### odpowiedź:
    {
	    "status": "success"
    }
### *DownloadPlaces*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "meetingID": "meet"
    }
#### odpowiedź:
    {
	    "status": "success",
	    "places": [
		 {
		  "placeID": 12941,
		  "address": 12143,
		  "name": "Green Cafe Nero",
		  "latitude": "34.456457",
		  "longitude": "-423.678568",
		  "voters":
		   [
		     {
		       "username": "abc",
		       "userID": 1
		     },
		     {
		       "username": "ziom",
		       "userID": 4
		     } 
		   ]
		 },
		 //kolejne miejsca
		]
    }
### *DownloadMembers*
#### *sessionID* – wymagane.
#### *eventData*:
    {
	    "meetingID": "meet"
    }
#### odpowiedź:
    {
	    "status": "success",
	    "users":
		 [
		  {
		   "username": "abc",
		   "userID": 2,
		   "WillAttend": 1
		   	"latitude": "34.456457",
		 	"longitude": "-423.678568"
		  },
		  {
		   "username": "ziom",
		   "userID": 4,
		   "WillAttend": 0
		   "latitude": "31.456457",
		   "longitude": "-423.678123"
		  }
		 ]
    }
### *DownloadMeetings*
#### *sessionID* – wymagane.
#### *eventData* – ignorowane.
#### odpowiedź:
    {
	    "status": "success",
	    "meetings": 
		 [
		  {
		   "name":"Spotkanie",
		   "meetingID": "cf694be51fcfbce474a5339debcbe8a8",
		   "isOwner": true,
		   "willAttend": true,
		   "time": "2020-11-18 15:45:21",
		   "status": "placeChosen",
		   "finalPlace": 
		    {
		     "ID": 123,
		     "name": Green Cafe Nero",
		     "address": "Ul. Marszałkowska 11 09-090 Warszawa",
			 "latitude": "34.456457",
		 	 "longitude": "-423.678568"
		    }
		  },
		  {
		   "name":"wyjscie",
		   "meetingID": "1c680f534c5a8b85d646d5184bda374c",
		   "isOwner": false,
		   "willAttend": false,
		   "time": "2020-11-18 15:45:21",
		   "status": "placeUnchosen",
		  }
		 ]
    }
  
