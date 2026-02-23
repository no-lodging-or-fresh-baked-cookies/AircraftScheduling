<?php

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: email_pass.php,v 1.4 2001/12/16 08:52:22 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";

// initialize variables
$sql = '';
$hour = "12";
$minute = "00";

// get the input parameters if they have been passed in
$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
if(isset($rdata["day"])) $day = $rdata["day"];
if(isset($rdata["month"])) $month = $rdata["month"];
if(isset($rdata["year"])) $year = $rdata["year"];
if(isset($rdata["hour"])) $hour = $rdata["hour"];
if(isset($rdata["minute"])) $minute = $rdata["minute"];
if(isset($rdata["make"])) $make = $rdata["make"];
if(isset($rdata["model"])) $model = $rdata["model"];
if(isset($rdata["resource"])) $resource = $rdata["resource"];
if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
if(isset($rdata["username"])) $username = $rdata["username"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];

#If we dont know the right date then make it up 
if(!isset($day) or !isset($month) or !isset($year))
{
	$day   = date("d");
	$month = date("m");
	$year  = date("Y");
}
else
{
    // make sure the date values are valid
    ValidateDate($day, $month, $year);
}

if (empty($resource))
	$resource = get_default_resource();

if($make) $makemodel = "&make=$make";
else if($model) $makemodel = "&model=$model";
else { $all=1; $makemodel = "&all=1"; }

print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");

if(isset($username)) 
{
	$sql = "SELECT email, password FROM AircraftScheduling_person WHERE username='$username'";
	$res = sql_query($sql);
	if(0 != ($row = sql_row($res, 0))) 
	{
		if(!AircraftSchedulingMail($row[0], "Your $AircraftScheduling_company online scheduler password", 
		            $lang['email_message'] . $row[1])) 
		{
			echo "<H1>Unable to Email the password!  Email not configured correctly!</H1>";
		}
		else
		{
			echo "<H2>Password for $username sent to $row[0]";
			echo "<BR><a href='logon.php?$_SERVER[QUERY_STRING]'>Back to the " . $lang["logon"] . " page</a></H2>";
			
			// log the password request to the journal
			$Description = 
						"Password emailed";
			CreateJournalEntry(strtotime("now"), $username, $Description);
		}
	}
	else
	{
		echo "<H1>Unable to Email the password!  Username not found!</H1>";
	}
}
else 
{

?>

    <H3>Enter your username, and your password will be sent to the Email address on file:</H3>
    <FORM name='email_pass' action='<?php echo getenv("SCRIPT_NAME") ?>' method='GET'>
	<INPUT name='username' length=10 maxlength=24>
	<INPUT type='submit' value='submit'>
    </FORM>

<?php
}

include "trailer.inc";

