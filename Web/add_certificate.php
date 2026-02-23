<?PHP
# $Id: add_certificate.php,v 1.4 2001/12/20 07:02:27 mbarclay Exp $
include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
if($auth["type"] == "session") { include "session.inc"; }
include "functions.inc";

// initialize variables
$remove_it = '';

// get the input parameters if they have been passed in
$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
if(isset($rdata["day"])) $day = $rdata["day"];
if(isset($rdata["month"])) $month = $rdata["month"];
if(isset($rdata["year"])) $year = $rdata["year"];
if(isset($rdata["make"])) $make = $rdata["make"];
if(isset($rdata["model"])) $model = $rdata["model"];
if(isset($rdata["resource"])) $resource = $rdata["resource"];
if(isset($rdata["remove_it"])) $remove_it = $rdata["remove_it"];
if(isset($rdata["pilot_certificate_id"])) $pilot_certificate_id = $rdata["pilot_certificate_id"];
if(isset($rdata["ratings"])) $ratings = $rdata["ratings"];
if(isset($rdata["certificates"])) $certificates = $rdata["certificates"];

#If we dont know the right date then make it up 
if(!isset($day) or !isset($month) or !isset($year))
{
	$day   = date("d");
	$month = date("m");
	$year  = date("Y");
}

if($make) $makemodel = "&make=$make";
else if($model) $makemodel = "&model=$model";
else { $all=1; $makemodel = "&all=1"; }

if(!getAuthorised(getUserName(), getUserPassword(), 0))
{
	showAccessDenied($day, $month, $year, $resource, $makemodel);
	exit;
}

// get a record for the user
$sql = "SELECT person_id FROM AircraftScheduling_person " . 
				"WHERE username='" . 
					getUserName() .
					"' AND password='" . 
					getUserPassword() . "'";
$person_res = sql_query($sql);
$row = sql_row($person_res, 0);
$PersonID = $row[0];

// get the certificate number for this pilot if it is not set
if(!isset($rdata["certificates"]))
{
	$sql = "SELECT certificate_id FROM AircraftScheduling_pilot_certificates " . 
					"WHERE pilot_id=$PersonID";
	$certificate_res = sql_query($sql);
	$row = sql_row($certificate_res, 0);
	$certificates = $row[0];
}

// compute the number of pilot certificates
$sql = "SELECT count(*) FROM AircraftScheduling_pilot_certificates 
				WHERE pilot_id=$PersonID" .  
					" AND certificate_id=$certificates";	
$NumberPilotCertificates = sql_command($sql);
if(-1 == $NumberPilotCertificates)
	fatal_error(0, sql_error() . $sql);

// get the pilot certificate ID
$sql = "SELECT pilot_certificate_id FROM AircraftScheduling_pilot_certificates 
				WHERE pilot_id=$PersonID" .  
					" AND certificate_id=$certificates";	
$certificate_res = sql_query($sql);
$row = sql_row($certificate_res, 0);
$CertificateID = $row[0];

if(count($_POST) > 0)
{
	// if we have any certificates defined
	if (1 != $NumberPilotCertificates) 
	{
		// No certificate registered or more than one
		$sql = "DELETE FROM AircraftScheduling_pilot_ratings 
				WHERE AircraftScheduling_pilot_certificates.pilot_id=$PersonID " . 
				"AND AircraftScheduling_pilot_certificates.certificate_id=$certificates 
				AND AircraftScheduling_pilot_ratings.certificate_id=$certificates";
		if(-1 == sql_command($sql))
			fatal_error(0, sql_error() . $sql);
		$sql = "DELETE FROM AircraftScheduling_pilot_certificates 
				WHERE pilot_id=$PersonID 
				AND certificate_id=$certificates ";
		if(-1 == sql_command($sql))
			fatal_error(0, sql_error() . $sql);
		$sql = "INSERT INTO AircraftScheduling_pilot_certificates ( pilot_id, certificate_id ) 
				VALUES ($PersonID, $certificates)";
		if(-1 == sql_command($sql))
			fatal_error(0, sql_error() . $sql);
		$pilotIDSeq = sql_insert_id("AircraftScheduling_pilot_certificates", "pilot_id");


		for($c=0; $c < count($ratings); $c++)
		{
			$sql = "INSERT INTO AircraftScheduling_pilot_ratings ( certificate_id, rating_id ) VALUES ( 
					$pilotIDSeq, $ratings[$c] ) ";
			if(-1 == sql_command($sql))
				fatal_error(0, sql_error() . $sql);
		}
	}
	else
	{
		$sql = "DELETE FROM AircraftScheduling_pilot_ratings 
				WHERE certificate_id=$CertificateID 
				AND AircraftScheduling_pilot_certificates.certificate_id=$certificates 
				AND AircraftScheduling_pilot_certificates.pilot_id=$PersonID";
		$pilot_cert_id = sql_query1("SELECT pilot_certificate_id 
					FROM AircraftScheduling_pilot_certificates 
					WHERE certificate_id=$certificates 
					AND pilot_id=$PersonID");
		for($c=0; $c < count($ratings); $c++)
		{
			$sql = "INSERT INTO AircraftScheduling_pilot_ratings ( certificate_id, rating_id ) VALUES ( $pilot_cert_id, $ratings[$c] ) ";
			if(-1 == sql_command($sql))
				fatal_error(0, sql_error() . $sql);
		}
	}
}
else if($remove_it && $pilot_certificate_id) {
  $sql = "DELETE FROM AircraftScheduling_pilot_ratings WHERE certificate_id=$pilot_certificate_id AND
             AircraftScheduling_pilot_certificates.pilot_id=$PersonID";
  sql_command("$sql");
  $sql = "DELETE FROM AircraftScheduling_pilot_certificates WHERE pilot_certificate_id=$pilot_certificate_id AND
           pilot_id=$PersonID";
  sql_command("$sql");
}

print_header($day, $month, $year, isset($resource) ? $resource : "", $makemodel);

$endorse_sql = "SELECT endorsement_id, endorsement FROM AircraftScheduling_endorsements ORDER BY endorsement";
$cert_sql    = "SELECT certificate_id, certificate FROM AircraftScheduling_certificates ORDER BY certificate";
$rating_sql  = "SELECT rating_id, rating FROM AircraftScheduling_ratings ORDER BY rating";

?>
<form name="add_certificate" action="<?php echo $_SERVER["PHP_SELF"] ?>" method=POST>
<table border=0>
<tr>
<td width="40%">
<table border=0>
<caption><H2>Add a Pilot Certificate and Rating</H2></caption>
<tr>
<th>Pilot Certificate</th>
<th>Certificate Rating</th>
</tr>
<tr>
<td>
    <select name="certificates" SIZE=5>
<?php
  $cert_res = sql_query($cert_sql);

  if ($cert_res) for ($i = 0; ($row = sql_row($cert_res, $i)); $i++) {
    echo "<option VALUE=$row[0]";
    if($row[0] == sql_query1("SELECT certificate_id FROM AircraftScheduling_pilot_certificates WHERE 
                              pilot_certificate_id=$pilot_certificate_id and pilot_id=$PersonID"))
      echo " SELECTED";
    echo ">".stripslashes($row[1])."</option>\n";
  }
?>
    </select>
</td>
<td>
    <select name="ratings[]" SIZE=5 MULTIPLE>
<?php
  $rating_res = sql_query($rating_sql);

  if ($rating_res) for ($i = 0; ($row = sql_row($rating_res, $i)); $i++) {
    echo "<option VALUE=$row[0]";
    if(1 == sql_query1("SELECT count(*) FROM AircraftScheduling_pilot_ratings a, AircraftScheduling_pilot_certificates c WHERE 
                        certificate_id=$pilot_certificate_id AND rating_id=$row[0] AND certificate_id=$pilot_certificate_id
                        AND pilot_rating_id=$PersonID"))
      echo " SELECTED";
    echo ">".stripslashes($row[1])."</option>\n";
  }
?>
    </select>
</td>
</tr>
</table>
</td>
<td width="60%">
<center>
<B>Certificates and Ratings on File</B><BR>
(Click to modify)<BR><BR>
<?php
  $sql = "SELECT pilot_certificate_id, certificate_id, certificate_id FROM AircraftScheduling_pilot_certificates
          WHERE a.certificate_id=$pilot_certificate_id AND pilot_id=$PersonID order by certificate_id";
  $res = sql_query($sql);
  if($res) for($c=0; ($row = sql_row($res,$c)); $c++) {
    echo "<a href=\"" . $_SERVER["PHP_SELF"] . "?pilot_certificate_id=$row[0]\">$row[2]</a>";
    $confirmdel = $lang["confirmdel"];
    echo " - - <a href=\"" . $_SERVER["PHP_SELF"] . "?pilot_certificate_id=$row[0]&remove_it=yes\" onClick=\"return confirm('$confirmdel');\">[delete]</a><br>";
    $sql = "SELECT rating FROM AircraftScheduling_ratings a, AircraftScheduling_pilot_ratings b WHERE b.certificate_id=$row[0] AND b.rating_id=a.rating_id";
    $rating_res = sql_query($sql);
    if($rating_res) for($d=0; ($rating_row = sql_row($rating_res, $d)); $d++)
      echo "<LI>$rating_row[0]<br>";
  }
?>
</center>
</td>
</tr>
</table>
<INPUT TYPE="submit" value="Add">
</form>
<H2>NOTE: You must supply proof of these Certificates and Ratings prior to rental</H2>

<?php
include "trailer.inc";

?>