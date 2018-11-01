<?php
$db = new SQLite3('final.db');
$id = $_GET["id"];
$lat = $_GET["x"];
$long = $_GET["y"];
$alarm = $_GET["alarm"];
/*

* ALARM CODES
***alarm:0 means ready state for deliver
***alarm:1 means emergency state from that phone
***alarm:2 means emergency state clear

TEST PAGES
#1: FOR TEST OF EMERGENCY
localhost/hraed.php?id=P7&x=37.464&y=127.02531&alarm=1
#2: FOR TEST OF EMERGENCY DELIVER
localhost/hraed.php?id=P6&x=37.46454&y=127.025&alarm=0
#3: FOR TEST OF EMERGENCY NON-DELIVER
localhost/hraed.php?id=P3&x=37.466541&y=127.023322&alarm=0
#4: FOR TEST OF EMERGENCY CLOSE
localhost/hraed.php?id=P6&x=37.46454&y=127.025&alarm=2
#5: FOR TEST OF CODE ERROR
localhost/hraed.php?id=P4&x=37.4657&y=127.02437&alarm=5
#6: FOR NON-EMERGENCY STATE
localhost/hraed.php?id=P4&x=37.4657&y=127.02437&alarm=0

*/
//UPDATE USER INFO FROM 'GET'
$db->exec("UPDATE USERS SET latitude = '$lat', longitude = '$long', alarm = '$alarm' WHERE id = '$id'");

//HAVERSINE FORMULA!!!!
function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {
    $earth_radius = 6371000;
    $dLat = deg2rad($latitude2 - $latitude1);
    $dLon = deg2rad($longitude2 - $longitude1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * asin(sqrt($a));
    $d = $earth_radius * $c;
    return $d;
}
/*if ($alarm==0 && $id == 0 && $lat == 0 && $long ==0){
  print "This is page for db";
  print"<table border = 1>";
  print"<tr><td>id</td><td>lat</td><td>long</td><td>distance</td></tr>";
  $result = $db -> query('SELECT * FROM AED order by id asc');
  while($row = $result->fetchArray()){
    $row[3] = getDistance($row[1],$row[2],$lat,$long); #GETDISTANCE COPY FROM HERE
    $db->exec("UPDATE AED SET distance = $row[3] WHERE latitude = $row[1] and longitude = $row[2]");

    print "<tr><td>".$row[0]."</td>";
    print "<td>".$row[1]."</td>";
    print "<td>".$row[2]."</td>";
    print "<td>".$row[3]."</td></tr>";
  }
  print "</table>";

  print "<table border=1>";
  print "<tr><td>ID</td><td>X</td><td>Y</td><td>Alarm</td><td>distance</td></tr>";
  $result = $db->query('SELECT * FROM USERS;');
  while($row = $result->fetchArray()){
    $row[4] = getDistance($aedlat,$aedlon, $row[1], $row[2]);
    $db -> exec("UPDATE USERS SET distance = $row[4] WHERE latitude = $row[1] and longitude = $row[2];");
    print "<tr><td>".$row[0]."</td>";
    print "<td>".$row[1]."</td>";
    print "<td>".$row[2]."</td>";
    print "<td>".$row[3]."</td>";
    print "<td>".$row[4]."</td></tr>";
  }
  print "</table>";

}
//to print the original db table*/

if ($alarm==1) { //ALARM=1 MEANS EMERGENCY STATUS #1
  $patlat = $lat;
  $patlon = $long;
  //SELECT AND PRINT CLOSEST AED
  print "<p>your location is sent for AED deliver.</p>";
  print "<p class='em'>BUT YOU SHOULD START CPR NOW.</p>";
  print "<a href='https://carrington.edu/wp-content/uploads/2014/08/CPR-How-To-Adults.gif' id='hyper'> DON'T KNOW HOW TO? </a>";
  print"<table border = 1>";
  print"<tr><td>id</td><td>lat</td><td>long</td><td>distance</td></tr>";
  $result = $db -> query('SELECT * FROM AED order by id asc');
  while($row = $result->fetchArray()){
    $row[3] = getDistance($row[1],$row[2],$lat,$long); #GETDISTANCE COPY FROM HERE
    $db->exec("UPDATE AED SET distance = $row[3] WHERE latitude = $row[1] and longitude = $row[2]");

    print "<tr><td>".$row[0]."</td>";
    print "<td>".$row[1]."</td>";
    print "<td>".$row[2]."</td>";
    print "<td>".$row[3]."</td></tr>";
  }
  print "</table>";

  //GET INFO OF CLOSEST AED
  $result = $db->query('SELECT latitude, longitude, min(distance) FROM AED;');
  while($row = $result->fetchArray()){
    $aedlat = $row[0];
    $aedlon = $row[1];
  }

  //GET AND PRINT AED-USER DISTANCE
  print "<table border=1>";
  print "<tr><td>ID</td><td>X</td><td>Y</td><td>Alarm</td><td>distance</td></tr>";
  $result = $db->query('SELECT * FROM USERS;');
  while($row = $result->fetchArray()){
    $row[4] = getDistance($aedlat,$aedlon, $row[1], $row[2]);
    $db -> exec("UPDATE USERS SET distance = $row[4] WHERE latitude = $row[1] and longitude = $row[2];");
    print "<tr><td>".$row[0]."</td>";
    print "<td>".$row[1]."</td>";
    print "<td>".$row[2]."</td>";
    print "<td>".$row[3]."</td>";
    print "<td>".$row[4]."</td></tr>";
  }
  print "</table>";

  //SELECT CLOSEST USER TO CLOSEST AED FROM PATIENT
  $result = $db -> query("SELECT id, min(distance) FROM USERS WHERE id!= '$id';");
  while($row = $result->fetchArray()){
    print $row[0]."</br>";
    print $row[1];
    $CLOUSR = $row[0];
  }
  //UPDATE EMERGENCY: ADD PATLAT PATLON AEDLAT AEDLON CLOSEST
  $db->exec("UPDATE EMERGENCY SET patlat = '$patlat', patlon = '$patlon', aedlat = '$aedlat', aedlon = '$aedlon', closest = '$CLOUSR' WHERE id='ON';");

} elseif ($alarm ==2 ) { //ALARM=2 MEANS EMERGENCY STATUS DONE; #4
  //EMERGENCY 클리어하고 "I hope it ended greatly"출력
  $db->exec("UPDATE EMERGENCY SET patlat = 0, patlon = 0, aedlat = 0, aedlon = 0, closest = '';");
  print "<h1>EMERGENCY CLOSED:</br>I hope it ended greatly</h1>";
} elseif ($alarm ==0) { //ALARM=0 MEANS READY STATUS FOR DELIVER;
  //EMERGENCY 불러와서 CLOUSR 확인하고 본인ID 아니면 안내문..
  $result = $db->query("SELECT closest FROM EMERGENCY;");
  while($row = $result->fetchArray()){
    $closest = $row[0];
  }
  if ($closest == $id){ //EMERGENCY CLOUSR가 본인인 경우 #2
    $result = $db-> query ("SELECT patlat, patlon, aedlat, aedlon FROM EMERGENCY;");
    while($row=$result->fetchArray()){
      print "<h1>YOU're the user to deliver AED to the patient.</h1>";
      print "<p>PATIENT IS AT: (".$row[0]." , ".$row[1].")</p>";
      print "<h3 href='https://www.google.com/maps/search/".$row[2]."+".$row[3]."' style='color: black;'> Go To (".$row[2]." , ".$row[3].") first and get AED.</h3>";
      print "<h3 href='https://www.google.com/maps/search/".$row[0]."+".$row[1]."' style='color: black;'> Go To PATIENT(".$row[0]." , ".$row[1].") then with AED.</h3>";
      print "<h3 style='color:crimson;'> REMEMBER, ONLY YOU CAN SAVE THE PATIENT.</h3>";
    }
  } elseif ($closest == '') { //NO EMERGENCY STATE일 때 #6
    print "<p>There's no Emergency State Right Now :)</p>";
  }  else { //EMERGENCY CLOUSR가 본인이 아닌 경우 #3
    print "<p>There's an emergency, but it seems like you're not the closest.</p>";
    print "<p>YOUR LOCATION IS SHARED FOR EMERGENCY PATIENT</p>";
  }
} else { //코드에러 #5
  # for the cases of non-EMG location change;
  print "<p>ERROR: UNKNOWN CODE</p>";
}

$db->close();
?>
