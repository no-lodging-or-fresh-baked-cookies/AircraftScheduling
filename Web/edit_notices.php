<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: edit_notices.php,v 1.4 2001/12/20 07:02:27 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";
require_once("StringFunctions.inc");

// initialize variables
$Update = '';
$Cancel = '';
$sql = '';

// set default data for input parameters
$Notices = "";

// get the input parameters if they have been passed in
$rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
if(isset($rdata["day"])) $day = $rdata["day"];
if(isset($rdata["month"])) $month = $rdata["month"];
if(isset($rdata["year"])) $year = $rdata["year"];
if(isset($rdata["make"])) $make = $rdata["make"];
if(isset($rdata["model"])) $model = $rdata["model"];
if(isset($rdata["resource"])) $resource = $rdata["resource"];
if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
if(isset($rdata["Update"])) $Update = $rdata["Update"];
if(isset($rdata["Cancel"])) $Cancel = $rdata["Cancel"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["Notices"])) $Notices = $rdata["Notices"];

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
		
// if the login has timed out
if (user_logged_on() && 
		LoginHasTimedOut() && 
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelNormal)
{
	// login has timed out, logout the user
	user_logoff();
	
	// show the problem
	showLoginTimedOut($day, $month, $year, $resource, $resource_id, $makemodel);
	exit();
}

if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelMaintenance) &&
	!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice)) 
{
	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
	exit();
}

// should we update the notices?
if(count($_POST) > 0 && $Update == "Update") 
{
    // stip any special characters from the description fields
    $Notices = AddEscapes($Notices);

	// update the notices	
	$sql = "UPDATE AircraftScheduling_notices SET Notices = '$Notices'";
	$sql_result = sql_command($sql);
	
	if(-1 == $sql_result) 
	{
		print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
		echo "SQL error $sql " . sql_error();
		include "trailer.inc";
		exit;
	} 
	else 
	{
		// log the change in to the journal
		$Description = 
					"Updating login notice";
		CreateJournalEntry(strtotime("now"), getUserName(), $Description);
		
		// back to the admin screen
		session_write_close();
		header("Location: admin.php");
		exit;
	}
}
// did the user cancel?
else if(count($_POST) > 0 && $Cancel == "Cancel") 
{
	// user canceled, just exit
	session_write_close();
	header("Location: admin.php");
	exit;
}

print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");

$sql = "SELECT Notices, " . sql_syntax_timestamp_to_unix("timestamp") . 
        " FROM AircraftScheduling_notices";

$res = sql_query($sql);

echo "<center><H2>Change Login Notice Messages</H2>";
echo "<form name=\"edit_notices\" ENCTYPE=\"multipart/form-data\" action=\"edit_notices.php\" method=\"POST\">";
echo "<table border=1>";

if ($res) 
{
	$row = sql_row($res, 0);
	echo "<tr>";
	echo "<TD CLASS='CellHeader'>" . $lang["notices"] . 
	        "<BR><FONT SIZE=2>Last Updated " . 
	            strftime('%X - %d %B %Y', $row[1] - TimeZoneAdjustment()) . 
	        "</FONT></TD>";
	echo "</tr>";
	echo "<tr>";
	echo "<TD CLASS=TL><TEXTAREA NAME='Notices' ROWS=22 COLS=68 WRAP='virtual'>" . 
	        htmlentities ( $row[0] ) . "</TEXTAREA></TD></TR>";
	echo "</tr>";
	echo "</table>";
	echo "<br>";
}
else echo sql_error();

echo "</table>";

echo "<input name=\"Update\" type=submit value=\"Update\">";
echo "<input name=\"Cancel\" type=submit value=\"Cancel\">";

echo "</form>";
echo "</center>";
echo "<br>";
include "trailer.inc" 
?>
