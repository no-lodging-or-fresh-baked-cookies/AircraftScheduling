<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: StandbyRequestView.php,v 1.6 2001/12/20 07:02:27 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "$dbsys.inc";
include "AircraftScheduling_auth.inc";
include "functions.inc";

// initialize variables
$item_name = '';
$sql = '';
$InstructorResource = "";
$InstructorName = "";
$InstructorEntryID = "";
$hour = "12";
$minute = "00";
$goback = "";
$GoBackParameters = "";

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
if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
if(isset($rdata["id"])) $id = $rdata["id"];
if(isset($rdata["StandbyID"])) $StandbyID = $rdata["StandbyID"];
if(isset($rdata["item_name"])) $item_name = $rdata["item_name"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["goback"])) $goback = $rdata["goback"];
if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];

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

if($make) $makemodel = "&make=$make";
else if($model) $makemodel = "&model=$model";
else { $all=1; $makemodel = "&all=1"; }

if(empty($resource))
	$resource = get_default_resource();
		
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

if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
{
	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, $goback, "", $InstructorResource);
	exit();
}

// ************************** local functions *********************************
// set the sql and cost information for an aircraft
function SetAircraftInformation($id)
{
	global $hourly_cost, $sql;
	
	// get the hourly cost from the aircraft information
	$sql = "SELECT hourly_cost FROM AircraftScheduling_aircraft a, AircraftScheduling_resource b, AircraftScheduling_entry c ".
			"WHERE c.entry_id=$id AND c.resource_id=b.resource_id AND b.resource_id=a.resource_id";
	if(($res = sql_query($sql)) && ($row = sql_row($res, 0))) 
	{
		$hourly_cost = $row[0];
	}
	
	// if the cost is not set in the aircraft information, get it from the make information
	if(0 == $hourly_cost) 
	{
		$sql = "SELECT a.hourly_cost FROM AircraftScheduling_make a, AircraftScheduling_aircraft b, AircraftScheduling_resource c, AircraftScheduling_entry d ".
				"WHERE d.entry_id=$id AND d.resource_id=c.resource_id AND c.resource_id=b.resource_id AND b.make_id=a.make_id";
		if(($res = sql_query($sql)) && ($row = sql_row($res, 0))) 
		{
			if(0 == $hourly_cost) $hourly_cost = $row[0];
		}
	}
	
	// if the cost is not set in the aircraft information or the make information, get
	// it from the model information
	if(0 == $hourly_cost) 
	{
		$sql = "SELECT a.hourly_cost FROM AircraftScheduling_model a, AircraftScheduling_aircraft b, AircraftScheduling_resource c, AircraftScheduling_entry d ".
				"WHERE d.entry_id=$id AND d.resource_id=c.resource_id AND c.resource_id=b.resource_id AND b.model_id=a.model_id";
		if(($res = sql_query($sql)) && ($row = sql_row($res, 0))) 
		{
			if(0 == $hourly_cost) $hourly_cost = $row[0];
		}
	}
	
	// set the sql statment for retreiving the aircraft information
	$sql = "
		SELECT AircraftScheduling_entry.name,
		       AircraftScheduling_entry.description,
		       AircraftScheduling_entry.create_by,
		       AircraftScheduling_entry.create_time,
		       AircraftScheduling_schedulable.name,
		       AircraftScheduling_entry.type,
		       AircraftScheduling_entry.resource_id,
		       AircraftScheduling_entry.repeat_id,
		    " . sql_syntax_timestamp_to_unix("AircraftScheduling_entry.timestamp") . ",
		       (AircraftScheduling_entry.end_time - AircraftScheduling_entry.start_time),
		       AircraftScheduling_entry.start_time,
		       AircraftScheduling_entry.end_time,
		       AircraftScheduling_aircraft.n_number,
		       AircraftScheduling_entry.phone_number
		FROM 
			AircraftScheduling_entry, 
			AircraftScheduling_resource, 
			AircraftScheduling_schedulable, 
			AircraftScheduling_aircraft
		WHERE AircraftScheduling_entry.resource_id = AircraftScheduling_resource.resource_id
		  AND AircraftScheduling_resource.schedulable_id = AircraftScheduling_schedulable.schedulable_id
		  AND AircraftScheduling_entry.entry_id=$id
		  AND AircraftScheduling_resource.item_id = AircraftScheduling_aircraft.aircraft_id
	";
}

// set the sql and cost information for an instructor
function SetInstructorInformation($id)
{
	global $hourly_cost, $sql;
	global $DatabaseNameFormat;
	
	// get the hourly cost from the instructor information
	$sql = "SELECT hourly_cost FROM AircraftScheduling_instructors a, AircraftScheduling_resource b, AircraftScheduling_entry c ".
			"WHERE c.entry_id=$id AND c.resource_id=b.resource_id AND b.resource_id=a.resource_id";
	if(($res = sql_query($sql)) && ($row = sql_row($res, 0))) 
	{
		$hourly_cost = $row[0];
	}
	
	// set the sql statment for retreiving the instructor information
	$sql = "
		SELECT AircraftScheduling_entry.name,
		       AircraftScheduling_entry.description,
		       AircraftScheduling_entry.create_by,
		       AircraftScheduling_entry.create_time,
		       AircraftScheduling_schedulable.name,
		       AircraftScheduling_entry.type,
		       AircraftScheduling_entry.resource_id,
		       AircraftScheduling_entry.repeat_id,
		    " . sql_syntax_timestamp_to_unix("AircraftScheduling_entry.timestamp") . ",
		       (AircraftScheduling_entry.end_time - AircraftScheduling_entry.start_time),
		       AircraftScheduling_entry.start_time,
		       AircraftScheduling_entry.end_time,
		       $DatabaseNameFormat,
		       AircraftScheduling_entry.phone_number
		FROM 
			AircraftScheduling_entry, 
			AircraftScheduling_resource, 
			AircraftScheduling_schedulable, 
			AircraftScheduling_instructors, 
			AircraftScheduling_person
		WHERE AircraftScheduling_entry.resource_id = AircraftScheduling_resource.resource_id
		  AND AircraftScheduling_resource.schedulable_id = AircraftScheduling_schedulable.schedulable_id
		  AND AircraftScheduling_entry.entry_id=$id
		  AND AircraftScheduling_resource.item_id=AircraftScheduling_instructors.instructor_id
		  AND AircraftScheduling_instructors.person_id=AircraftScheduling_person.person_id
	";
}

// check for an instructor scheduled for the user at the given start and end times
function InstructorIsScheduled($id, $ScheduleUserName, $ScheduleStartTime, $ScheduleEndTime, $CreationTime)
{
	// see if we have an instructor entry that is for the given user that
	// matches the start and end times
	$sql = "
		SELECT AircraftScheduling_entry.entry_id 
		FROM AircraftScheduling_entry 
		WHERE AircraftScheduling_entry.name = '$ScheduleUserName' 
		  AND AircraftScheduling_entry.create_time = $CreationTime 
		  AND AircraftScheduling_entry.start_time = $ScheduleStartTime 
		  AND AircraftScheduling_entry.end_time = $ScheduleEndTime 
		  AND AircraftScheduling_entry.entry_id <> $id
	";
	$EntryID = sql_query1($sql);
	return $EntryID;
}

// check for an aircraft scheduled for the user at the given start and end times
function AircraftIsScheduled($id, $ScheduleUserName, $ScheduleStartTime, $ScheduleEndTime, $CreationTime)
{
	// see if we have an aircraft entry that is for the given user that
	// matches the start and end times
	$sql = "
		SELECT AircraftScheduling_entry.entry_id 
		FROM AircraftScheduling_entry 
		WHERE AircraftScheduling_entry.name = '$ScheduleUserName' 
		  AND AircraftScheduling_entry.create_time = $CreationTime 
		  AND AircraftScheduling_entry.start_time = $ScheduleStartTime 
		  AND AircraftScheduling_entry.end_time = $ScheduleEndTime 
		  AND AircraftScheduling_entry.entry_id <> $id
	";
	$EntryID = sql_query1($sql);
	return $EntryID;
}
// ************************** end local functions *****************************

print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");

$hourly_cost = 0;

// get the schedule resource type
$ScheduleType = sql_query1("
							SELECT a.name 
							FROM 
								AircraftScheduling_schedulable a, 
								AircraftScheduling_resource b, 
								AircraftScheduling_entry c 
							WHERE 
								c.entry_id=$StandbyID AND 
								c.resource_id=b.resource_id AND 
								b.schedulable_id=a.schedulable_id"); 

// setup the information for the resource we are viewing
if($ScheduleType == "Aircraft") 
{
	// type is an aircraft, setup the information for an aircraft
	SetAircraftInformation($StandbyID);
	
	// get the results of the query
	$res = sql_query($sql);
	if (! $res) fatal_error(0, sql_error());
	if(sql_count($res) < 1) fatal_error(0, "Invalid entry id.");
	$row = sql_row($res, 0);
	sql_free($res);
	
	// get the information for checking the instructor schedule
	$ScheduleUserName = $row[0];
	$CreationTime = $row[3];
	$ScheduleStartTime = $row[10];
	$ScheduleEndTime = $row[11];
	
	// if there is an instructor scheduled at the same time for the
	// same user, link them together
	$InstructorEntryID = InstructorIsScheduled(
												$StandbyID, 
												$ScheduleUserName, 
												$ScheduleStartTime, 
												$ScheduleEndTime,
												$CreationTime);
	if ($InstructorEntryID > 0)
	{
		// instructor is scheduled at the same time, lookup the instructor's name
		// so we can show it in the entry
		$InstructorSQL = "
							SELECT $DatabaseNameFormat
							FROM 
								AircraftScheduling_entry, 
								AircraftScheduling_resource, 
								AircraftScheduling_schedulable, 
								AircraftScheduling_instructors, 
								AircraftScheduling_person
							WHERE AircraftScheduling_entry.resource_id = AircraftScheduling_resource.resource_id
							  AND AircraftScheduling_resource.schedulable_id = AircraftScheduling_schedulable.schedulable_id
							  AND AircraftScheduling_entry.entry_id=$InstructorEntryID
							  AND AircraftScheduling_resource.item_id=AircraftScheduling_instructors.instructor_id
							  AND AircraftScheduling_instructors.person_id=AircraftScheduling_person.person_id
						";
		$InstructorResult = sql_query($InstructorSQL);
		if (!$InstructorResult) fatal_error(0, sql_error());
		if(sql_count($InstructorResult) < 1) fatal_error(0, "Invalid entry id.");
		$InstructorRow = sql_row($InstructorResult, 0);
		sql_free($InstructorResult);
		$InstructorName = $InstructorRow[0];
	}
	else
		$InstructorName = "";
}
else if($ScheduleType == "Instructor") 
{
	// type is an instructor
	// setup the information for an instructor
	SetInstructorInformation($StandbyID);

	// get the results of the query
	$res = sql_query($sql);
	if (!$res) fatal_error(0, sql_error());
	if(sql_count($res) < 1) fatal_error(0, "Invalid entry id.");
	$row = sql_row($res, 0);
	sql_free($res);
	
	// get the information for checking the aircraft schedule
	$ScheduleUserName = $row[0];
	$CreationTime = $row[3];
	$ScheduleStartTime = $row[10];
	$ScheduleEndTime = $row[11];

	// if we have an aircraft scheduled at the same time, edit the 
	// aircraft entry (the aircraft entry will also edit the instructor
	// entry)
	$AircraftEntryID = AircraftIsScheduled(
											$StandbyID, 
											$ScheduleUserName, 
											$ScheduleStartTime, 
											$ScheduleEndTime,
											$CreationTime);
	if ($AircraftEntryID > 0)
	{
		SetAircraftInformation($AircraftEntryID);
		
		// get the results of the query
		$res = sql_query($sql);
		if (! $res) fatal_error(0, sql_error());
		if(sql_count($res) < 1) fatal_error(0, "Invalid entry id.");
		$row = sql_row($res, 0);
		sql_free($res);
		
		// an instructor is scheduled at the same time for the
		// same user, link them together
		$InstructorEntryID = $StandbyID;
		$StandbyID = $AircraftEntryID;
		// instructor is scheduled at the same time, lookup the instructor's name
		// so we can show it in the entry
		$InstructorSQL = "
							SELECT $DatabaseNameFormat 
							FROM 
								AircraftScheduling_entry, 
								AircraftScheduling_resource, 
								AircraftScheduling_schedulable, 
								AircraftScheduling_instructors, 
								AircraftScheduling_person
							WHERE AircraftScheduling_entry.resource_id = AircraftScheduling_resource.resource_id
							  AND AircraftScheduling_resource.schedulable_id = AircraftScheduling_schedulable.schedulable_id
							  AND AircraftScheduling_entry.entry_id=$InstructorEntryID
							  AND AircraftScheduling_resource.item_id=AircraftScheduling_instructors.instructor_id
							  AND AircraftScheduling_instructors.person_id=AircraftScheduling_person.person_id
						";
		$InstructorResult = sql_query($InstructorSQL);
		if (!$InstructorResult) fatal_error(0, sql_error());
		if(sql_count($InstructorResult) < 1) fatal_error(0, "Invalid entry id.");
		$InstructorRow = sql_row($InstructorResult, 0);
		sql_free($InstructorResult);
		$InstructorName = $InstructorRow[0];
	}
}
else
{
	// this shouldn't happen, but treat it as an aircraft just in case
	// type is an aircraft, setup the information for an aircraft
	SetAircraftInformation($StandbyID);
	
	// get the results of the query
	$res = sql_query($sql);
	if (! $res) fatal_error(0, sql_error());
	if(sql_count($res) < 1) fatal_error(0, "Invalid entry id.");
	$row = sql_row($res, 0);
	sql_free($res);
	
	// get the information for checking the instructor schedule
	$ScheduleUserName = $row[0];
	$CreationTime = $row[3];
	$ScheduleStartTime = $row[10];
	$ScheduleEndTime = $row[11];
	
	// if there is an instructor scheduled at the same time for the
	// same user, link them together
	$InstructorEntryID = InstructorIsScheduled(
												$StandbyID, 
												$ScheduleUserName, 
												$ScheduleStartTime, 
												$ScheduleEndTime,
												$CreationTime);
	if ($InstructorEntryID > 0)
	{
		// instructor is scheduled at the same time, lookup the instructor's name
		// so we can show it in the entry
		$InstructorSQL = "
							SELECT $DatabaseNameFormat 
							FROM 
								AircraftScheduling_entry, 
								AircraftScheduling_resource, 
								AircraftScheduling_schedulable, 
								AircraftScheduling_instructors, 
								AircraftScheduling_person
							WHERE AircraftScheduling_entry.resource_id = AircraftScheduling_resource.resource_id
							  AND AircraftScheduling_resource.schedulable_id = AircraftScheduling_schedulable.schedulable_id
							  AND AircraftScheduling_entry.entry_id=$InstructorEntryID
							  AND AircraftScheduling_resource.item_id=AircraftScheduling_instructors.instructor_id
							  AND AircraftScheduling_instructors.person_id=AircraftScheduling_person.person_id
						";
		$InstructorResult = sql_query($InstructorSQL);
		if (!$InstructorResult) fatal_error(0, sql_error());
		if(sql_count($InstructorResult) < 1) fatal_error(0, "Invalid entry id.");
		$InstructorRow = sql_row($InstructorResult, 0);
		sql_free($InstructorResult);
		$InstructorName = $InstructorRow[0];
	}
	else
		$InstructorName = "";
}

# Note: Removed stripslashes() calls from name and description. Previous
# versions of AircraftScheduling mistakenly had the backslash-escapes in the actual database
# records because of an extra addslashes going on. Fix your database and
# leave this code alone, please.
$name         = htmlspecialchars($row[0]);
$description  = htmlspecialchars($row[1]);
$create_by    = htmlspecialchars($row[2]);
$create_time  = strftime('%X - %A %d %B %Y', $row[3]);
$resource_name    = htmlspecialchars($row[4]);
$type         = $row[5];
$resource_id  = $row[6];
$repeat_id    = $row[7];
$updated      = strftime('%X - %A %d %B %Y', $row[8]);
$duration     = $row[9];
$cost         = 0;					// don't display cost ($row[9] / 3600) * $hourly_cost;
$start_time   = $row[10];
$end_time     = $row[11];
$start_date = strftime('%X - %A %d %B %Y', $start_time);
$end_date = strftime('%X - %A %d %B %Y', $row[11]);
if(isset($item_name)) $item_name = htmlspecialchars($row[12]);
$phone_number = $row[13];
$email_address = LookupEmailAddress($row[0]);

$changes_prohibited = strtotime("+$changes_prohibited_interval") - strtotime("now");
if(getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
{
	// changes always allowed if we are administator or a super user
	$changes_allowed = true;
    $ChangesProhibitedReason = "";
}
else if(!getWritable($name, getName()))
{
	// if we aren't the owner of this record, we can't update it
    $changes_allowed = false;
    
    // set the reason that we will show the user
    $ChangesProhibitedReason = "Changes not allowed. " .
        "You do not have privileges to modify this entry.";
}
else if ($changes_prohibited > 0 && abs($start_time - strtotime("now")) < $changes_prohibited)
{
	// no changes if we are within changes_prohibited_interval
    $changes_allowed = false;
    
    // set the reason that we will show the user
    $ChangesProhibitedReason = "Changes not allowed within $changes_prohibited_interval of booking.  " .
            "Please contact the FBO directly: $AircraftScheduling_phone.";
}
else 
{
    $changes_allowed = true;
    $ChangesProhibitedReason = "";
}

toTimeString($duration, $dur_units);

# Now that we know all the data we start drawing it

?>

<H3><?php 
	if ($email_address == "") echo "$name - $phone_number Standby Request";
	else echo "<A href=\"mailto:$email_address\">$name - $phone_number Standby Request</A>" 
	?></H3>
 <table border=0>
   <tr>
    <td><b><?php echo $lang["description"] ?></b></td>
    <td><?php    echo nl2br($description)  ?></td>
   </tr>
   <tr>
    <td><b><?php echo $lang["room"]                           ?></b></td>
	<td>
   	<?php 
   	if (strlen($InstructorName) > 0)
    	echo  nl2br("Standby request for " . $resource_name . " - " . $item_name . " with Instructor: " . $InstructorName);
    else 
        echo  nl2br("Standby request for " . $resource_name . " - " . $item_name);
   ?>	
   </td>
   </tr>
   <tr>
    <td><b><?php echo $lang["start_date"] ?></b></td>
    <td><?php    echo $start_date         ?></td>
   </tr>
   <tr>
    <td><b><?php echo $lang["duration"]            ?></b></td>
    <td><?php    echo $duration . " " . $dur_units ?></td>
   </tr>
<?php if($cost != 0) { ?>
   <tr>
     <td><b><?php echo $lang["estcost"] ?></b></td>
     <td><?php    echo $CurrencyPrefix . $cost         ?></td>
   </tr>
<?php } ?>
<?php if($hourly_cost != 0) { ?>
   <tr>
     <td><b><?php echo $lang["hourlyrate"] ?></b></td>
     <td><?php    echo $CurrencyPrefix . $hourly_cost     ?></td>
   </tr>
<?php } ?>
   <tr>
    <td><b><?php echo $lang["end_date"] ?></b></td>
    <td><?php    echo $end_date         ?></td>
   </tr>
   <!-- tr>
    <td><b><?php echo $lang["type"]   ?></b></td>
    <td><?php    echo empty($typel[$type]) ? "?$type?" : $typel[$type]  ?></td>
   </tr -->
   <tr>
    <td><b><?php echo $lang["createdby"] ?></b></td>
    <td><?php    echo $create_by         ?></td>
   </tr>
   <tr>
     <td><b><?php echo $lang["createtime"] ?></b></td>
     <td><?php    echo $create_time        ?></td>
   </tr>
   <tr>
    <td><b><?php echo $lang["lastupdate"] ?></b></td>
    <td><?php    echo $updated            ?></td>
   </tr>
</table>

<?php
// if changes are allowed, display the links to the change pages
if($changes_allowed) 
{  ?>
	<br>
	<p>
	<?php
	echo "<a href='StandbyRequest.php";
	echo "?id=$id";
	echo "&StandbyRequestID=$StandbyID" .
	        "&InstructorEntryID=$InstructorEntryID" .
	        "&InstructorName=$InstructorName" .
	        "&resource=$resource" .
	        "&resource_id=$resource_id" .
	        "&InstructorResource=$InstructorResource" .
	        "$makemodel";
	if (!empty($goback)) echo "&goback=$goback"; 
	if (!empty($GoBackParameters)) echo "&GoBackParameters=$GoBackParameters"; 
	echo "'>";
	echo $lang["editentry"] . "</a>";
	?>
	<BR>
	    <?php 
	        echo "<A HREF='del_entry.php";
    	    echo "?id=$StandbyID";
			if (isset($InstructorEntryID)) echo "&InstructorEntryID=$InstructorEntryID"; 
    	    echo "&RecordType=standby" .
    	        "&series=0" .
    	        "&resource=$resource" .
	            "&resource_id=$resource_id" .
    	        "&InstructorResource=$InstructorResource" .
    	        "&day=$day&month=$month&year=$year" .
    	        "$makemodel";
    		if (!empty($goback)) echo "&goback=$goback"; 
            if (!empty($GoBackParameters)) echo "&GoBackParameters=$GoBackParameters"; 
	    ?>
	    ' onClick="return confirm('<?php echo $lang["confirmdel"] ?>');"><?php echo $lang["deleteentry"] ?> </A>
	<BR>
	<?php
}
else
{ 
	if ($start_time > strtotime("now"))
		echo "<h3>Changes not allowed.</h3>";
	else
		echo "<h3>Changes not allowed within $changes_prohibited_interval of booking.  Please contact the FBO directly: $AircraftScheduling_phone.</h3>";
}

// if the return parameters were not specified, set the default
if (empty($GoBackParameters))
{
    $GoBackParameters = "?day=$day&month=$month&year=$year$makemodel" . 
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource";
}
else
{
    // since we added special characters to prevent the browser from
    // eating the parameters, fix them here
    $GoBackParameters = CleanGoBackParameters($GoBackParameters);
}

// generate the return URL
GenerateReturnURL($goback, $GoBackParameters);

include "trailer.inc"; ?>
