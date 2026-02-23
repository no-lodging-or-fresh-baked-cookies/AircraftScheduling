<?php
//-----------------------------------------------------------------------------
// 
// StandbyRequest.php
// 
// PURPOSE: Edits a standby request schedule entry.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      InstructorResource - selected instructor resource ID to pass to selected URLs
//      id - database id of the schedule item we are editing
//      StandbyRequestID - database id of the standby schedule item we are editing
//      InstructorEntryID - instructor ID for the entry
//      InstructorName - instructor name for the entry
//      edit_type - edit type of the schedule
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------

    include "global_def.inc";
    include "config.inc";
    include "$dbsys.inc";
    include "AircraftScheduling_auth.inc";
    include "functions.inc";
    require_once("StringFunctions.inc");
    
    global $pview;
    
    // initialize variables
    $sql = '';
    $InstructorResource = "";
    $make = "";
    $model = "";
    $InstructorName = "";
    $InstructorEntryID = "";
    $hour = "12";
    $minute = "00";
    $GoBackParameters = "";
    $id = "";
    
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
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    if(isset($rdata["id"])) $id = $rdata["id"];
    if(isset($rdata["StandbyRequestID"])) $StandbyRequestID = $rdata["StandbyRequestID"];
    if(isset($rdata["InstructorEntryID"])) $InstructorEntryID = $rdata["InstructorEntryID"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["InstructorName"])) $InstructorName = $rdata["InstructorName"];
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
    
    if(empty($resource))
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit;
    }
    
    // This page will add a standby request to an existing booking
    // We need to know:
    //  Name of booker
    //  Phone Number
    //  Description (optional)
    //  Date (option select box for day, month, year)
    //  Time
    //  Duration
    //  Internal/External
    
    // are we changing an existing standby request or adding a new request?
    if (isset($StandbyRequestID))
    {
    	// existing request, get the existing information from the database
    	$sql = "SELECT " .
    	            "name, " .
    	            "create_by, " .
    	            "description, " .
    	            "start_time, " .
    	            "end_time - start_time, " .
    	            "type, " .
    	            "resource_id, " .
    	            "entry_type, " .
    	            "repeat_id, " .
    	            "phone_number " . 
    	        "FROM AircraftScheduling_entry  " .
    	        "WHERE entry_id=$StandbyRequestID";
    	
    	$res = sql_query($sql);
    	if (! $res) fatal_error(1, sql_error());
    	if (sql_count($res) != 1) fatal_error(1, "Standby Entry ID $StandbyRequestID not found");
    	$row = sql_row($res, 0);
    	sql_free($res);
    	
    	// Note: Removed stripslashes() calls from name and description. Previous
    	// versions of AircraftScheduling mistakenly had the backslash-escapes in the actual database
    	// records because of an extra addslashes going on. Fix your database and
    	// leave this code alone, please.
    	$NameOfUser        = $row[0];
    	$create_by   = $row[1];
    	$description = $row[2];
    	$start_day   = strftime('%d', $row[3]);
    	$start_month = strftime('%m', $row[3]);
    	$start_year  = strftime('%Y', $row[3]);
    	$start_hour  = strftime('%H', $row[3]);
    	$start_min   = strftime('%M', $row[3]);
    	$duration    = $row[4];
    	$type        = $row[5];
    	$resource_id = $row[6];
    	$entry_type  = $row[7];
    	$rep_id      = $row[8];
    	$phone_number= $row[9];
    	
    	// if the id of the existing booking (not the standby) isn't set, look it up
    	// in the database
    	if (empty($id))
    	{
    		// id not set, look it up in the database
    		$sql = "SELECT entry_id " . 
        			"FROM AircraftScheduling_entry " . 
        			"WHERE start_time <= $row[3] AND " . 
        				"end_time >= $row[3] AND " . 
        				"resource_id = $resource_id AND " . 
        		    	"entry_type != $EntryTypeStandby";
    		
    		$id = sql_query1($sql);
    	}
    }
    else
    {
    	// new request, get the information from the database about the existing 
    	// booking that we are scheduling a standby request for
    	$sql = "SELECT " . 
    	            "name, " . 
    	            "create_by, " . 
    	            "description, " . 
    	            "start_time, " . 
    	            "end_time - start_time, " . 
    	            "type, " . 
    	            "resource_id, " . 
    	            "entry_type, " . 
    	            "repeat_id, " . 
    	            "phone_number " . 
    	         "FROM AircraftScheduling_entry " . 
    	         "WHERE entry_id=$id";
    	
    	$res = sql_query($sql);
    	if (! $res) fatal_error(1, sql_error());
    	if (sql_count($res) != 1) fatal_error(1, "Existing Entry ID $id not found");
    	
    	$row = sql_row($res, 0);
    	sql_free($res);
    	// Note: Removed stripslashes() calls from name and description. Previous
    	// versions of AircraftScheduling mistakenly had the backslash-escapes in the actual database
    	// records because of an extra addslashes going on. Fix your database and
    	// leave this code alone, please.
    	$start_day   = strftime('%d', $row[3]);
    	$start_month = strftime('%m', $row[3]);
    	$start_year  = strftime('%Y', $row[3]);
    	$start_hour  = strftime('%H', $row[3]);
    	$start_min   = strftime('%M', $row[3]);
    	$duration    = $row[4];
    	$type        = $row[5];
    	$resource_id = $row[6];
    	$entry_type  = $row[7];
    	$rep_id      = $row[8];
    
    	// fill in the information for the standby requester
    	$NameOfUser  = getName();
    	$create_by   = getUserName();
    	$phone_number= FormatPhoneNumber(getPhoneNumber(), getPhoneNumber2());
    	$description = "";
    }
    
    // get the schedule resource type
    $ScheduleType = sql_query1(
    							"SELECT a.name " .
    							"FROM " . 
    								"AircraftScheduling_schedulable a, " .
    								"AircraftScheduling_resource b " .
    							"WHERE " .
    								"b.resource_id=$resource_id AND " .
    								"b.schedulable_id=a.schedulable_id"); 
    
    // get the name of the item we are scheduling
    if($ScheduleType == "Aircraft") 
    {
    	$ScheduleName = sql_query1("SELECT n_number FROM AircraftScheduling_aircraft WHERE resource_id=$resource_id");
    }
    else if($ScheduleType == "Instructor") 
    {
    	$PersonID = sql_query1("SELECT person_id FROM AircraftScheduling_instructors WHERE resource_id=$resource_id");
    	$ScheduleName = sql_query1("SELECT $DatabaseNameFormat FROM AircraftScheduling_person WHERE person_id=$PersonID");
    }
    else
    	$ScheduleName = "Unknown";
    
    toTimeString($duration, $dur_units);
    
    // now that we know all the data to fill the form with we start drawing it
    if(!getWritable($NameOfUser, getName()))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, $goback, "", $InstructorResource);
    	exit;
    }
    
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    // compute the start times and durations from the original schedule for validating the request
    $sql = "SELECT start_time, end_time - start_time " .
    		"FROM AircraftScheduling_entry " .
            "WHERE entry_id=$id";
    
    $res = sql_query($sql);
    if (! $res) fatal_error(0, sql_error() . "<BR>SQL:" . $sql);
    if (sql_count($res) != 1) fatal_error(0, "Entry ID $id not found");
    $row = sql_row($res, 0);
    $Orig_start_day   = strftime('%d', $row[0]);
    $Orig_start_month = strftime('%m', $row[0]);
    $Orig_start_year  = strftime('%Y', $row[0]);
    $Orig_start_hour  = strftime('%H', $row[0]);
    $Orig_start_min   = strftime('%M', $row[0]);
    $Orig_duration    = $row[1];
    
    toTimeString($Orig_duration, $Orig_dur_units);
    
    sql_free($res);
    
    // Units start in seconds
    $Orig_units = 1.0;
    switch($Orig_dur_units)
    {
    	case "years":
    		$Orig_units *= 52;
    	case "weeks":
    		$Orig_units *= 7;
    	case "days":
    		$Orig_units *= 24;
    	case "hours":
    		$Orig_units *= 60;
    	case "minutes":
    		$Orig_units *= 60;
    	case "seconds":
    		break;
    }
    $OriginalStartTime = (((((($Orig_start_year - 1970) * 365 + $Orig_start_month) * 31 + $Orig_start_day) * 24 + $Orig_start_hour) * 60) + $Orig_start_min) * 60 ;
    $OriginalEndTime   = (((((($Orig_start_year - 1970) * 365 + $Orig_start_month) * 31 + $Orig_start_day) * 24 + $Orig_start_hour) * 60) + $Orig_start_min) * 60  + ($Orig_units * $Orig_duration);
    
    echo "<h2>";
    echo $lang["addstandby"] . " - $ScheduleName <br>";
    echo "</H2>";
    
    echo "<FORM NAME='main' ACTION='StandbyRequestHandler.php' METHOD='GET'>";
    
    echo "<TABLE BORDER=0>";
    
	// if the user is a normal user, put in the user name as the booker
	if (authGetUserLevel(getUserName(), $auth["admin"]) <= $UserLevelNormal)
	{
		echo "<TR><TD CLASS=CR><B>" . $lang["namebooker"] . "</B></TD>";
  		echo "<TD CLASS=CL>" . htmlentities($NameOfUser) . "</TD></TR>";
		echo "<INPUT TYPE=HIDDEN NAME='NameOfUser' VALUE='" . $NameOfUser . "'>";

		echo "<TR><TD CLASS=CR><B>" . $lang["phonenumber"] . "</B></TD>";
		echo "<TD CLASS=CL><INPUT NAME='phone_number' SIZE=25 VALUE='" . htmlentities($phone_number) . "'></TD></TR>";
  	}
  	else
  	{
  		// admin or super user
		echo "<TR><TD CLASS=CR><B>" . $lang["namebooker"] . "</B></TD>";

		// build the selection entries
		echo "<TD CLASS=CL>";
        BuildMemberSelector(
                            $NameOfUser, 
                            false, 
                            "",
                            20,
                            true,
                            false,
                            "SelectPhonenumber");
		echo "</TD></TR>";

		$SQLNameSelect = "SELECT 
					phone_number,
					Allow_Phone_Number_Display,
					Home_Phone  
				FROM AircraftScheduling_person 
				WHERE user_level != $UserLevelDisabled ";
		$SQLNameSelect .= "ORDER by last_name";
		
		echo "<TD CLASS=CL>";
		$NameSelectResult = sql_query($SQLNameSelect);
		if(0 != ($row = sql_row_keyed($NameSelectResult, 0))) 
		{	
			// setup script to select phone number when the name changes
			echo "<SCRIPT LANGUAGE='JavaScript'>";
			echo "function SelectPhonenumber()";
			echo "{";
			echo "	var PhoneNumbers = new Array;";
	
			// build the phone number array
			for($i=0; $row = sql_row($NameSelectResult, $i); $i++) 
			{
			    // if the user is allowing phone number display, show the phone
			    // number
				if ($row[1] == 1)
				    echo "	PhoneNumbers[$i] = \"" . FormatPhoneNumber($row[0], $row[2]) . "\";";
				else
				    echo "	PhoneNumbers[$i] = \"" . "UNLISTED" . "\";";
			}
				
			echo "	document.forms['main'].phone_number.value = PhoneNumbers[document.forms['main'].NameOfUser.selectedIndex];";
			echo "}";
			echo "</SCRIPT>";
		}
		
		// display the phone number field
		echo "<TR><TD CLASS=CR><B>" . $lang["phonenumber"] . "</B></TD>";
		echo "<TD CLASS=CL><INPUT NAME='phone_number' SIZE=25 VALUE='" . htmlentities($phone_number) . "'></TD></TR>";
  	}
    
    // description
    echo "<TR><TD CLASS=TR><B>";
    echo $lang["fulldescription"];
    echo "</B></TD>";
    echo "<TD CLASS=TL>";
    echo "<TEXTAREA NAME='description' ROWS=8 COLS=40 WRAP='virtual'>";
    echo htmlentities ( $description );
    echo "</TEXTAREA></TD></TR>";
    
    // date of the schedule
    echo "<TR><TD CLASS=CR><B>";
    echo $lang["date"];
    echo "</B></TD>";
    echo "<TD CLASS=CL>";
    genDateSelector("", "main", $start_day, $start_month, $start_year);
    echo "</TD>";
    echo "</TR>";
    
    // start time
    echo "<TR><TD CLASS=CR><B>";
    echo $lang["time"];
    echo "</B></TD>";
    echo "<TD CLASS=CL><INPUT NAME='hour' SIZE=2 VALUE='$start_hour' MAXLENGTH=2>" . 
                    ":" .
                    "<INPUT NAME='minute' SIZE=2 VALUE='$start_min' MAXLENGTH=2>";
    echo "</TD></TR>";
    
    // duration
    echo "<TR><TD CLASS=CR><B>";
    echo $lang["duration"];
    echo "</B></TD>";
    echo "<TD CLASS=CL>";
    echo "<INPUT NAME='duration' SIZE=7 VALUE='$duration'>";
    echo "<SELECT NAME='dur_units'>";
    $units = array("minutes", "hours", "days", "weeks");
	foreach ($units as $unit)
    {
    	echo "<OPTION VALUE=$unit";
    	if ($dur_units == $lang[$unit]) echo " SELECTED";
    	echo ">$lang[$unit]";
    }
    echo "</SELECT>";
    echo "<INPUT NAME='all_day' TYPE='checkbox' VALUE='yes'>" . $lang["all_day"];
    echo "</TD></TR>";
     
    // generate the save button
    echo "<TR>";
    echo "<TD colspan=2 align=center>";
    echo "<input name='EditEntry' type=submit value='" . $lang["save"] . "' ONCLICK='return ValidateAndSubmit()'>";
    echo "</TD></TR>";
    echo "</TABLE>";
    
    // save the input data
    echo "<INPUT TYPE=HIDDEN NAME='resource_id' VALUE='$resource_id'>";
    echo "<INPUT TYPE=HIDDEN NAME='resource' VALUE='$resource'>";
    echo "<INPUT TYPE=HIDDEN NAME='InstructorResource' VALUE='$InstructorResource'>";
    echo "<INPUT TYPE=HIDDEN NAME='make' VALUE='$make'>";
    echo "<INPUT TYPE=HIDDEN NAME='model' VALUE='$model'>";
    echo "<INPUT TYPE=HIDDEN NAME='create_by' VALUE='$create_by'>";
    echo "<INPUT TYPE=HIDDEN NAME='rep_id' VALUE='$rep_id'>";
    echo "<INPUT TYPE=HIDDEN NAME='ScheduleName' VALUE='$ScheduleName'>";
    if(isset($id)) 
        echo "<INPUT TYPE=HIDDEN NAME='id' VALUE='$id'>\n";
    if(isset($InstructorEntryID))
        echo "<INPUT TYPE=HIDDEN NAME='InstructorEntryID' VALUE='$InstructorEntryID'>\n";
    if(isset($StandbyRequestID))
        echo "<INPUT TYPE=HIDDEN NAME='StandbyRequestID' VALUE='$StandbyRequestID'>\n";
    echo "<INPUT TYPE=HIDDEN NAME='InstructorName' VALUE='None'>";
    if(isset($goback)) 
        echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>";
    if(!empty($GoBackParameters)) 
        echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>";
    
    // save the variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var OrgStartTime = $OriginalStartTime;";
    echo "var OrgEndTime = $OriginalEndTime;";
    echo "</SCRIPT>";
    
    echo "</FORM>";

    include "trailer.inc" 
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// ValidateAndSubmit()
//
// Purpose: Verify the data the user entered before submitting the form.
//
// Inputs:
//   none
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function ValidateAndSubmit()
{
    if(document.forms["main"].NameOfUser.value == "")
    {
        alert ( "You have not entered a\nname for the schedule." );
        return false;
    }
    if(document.forms["main"].phone_number.value == "")
    {
        alert ( "You have not entered a\nphone number for the schedule." );
        return false;
    }
    
    // Units start in seconds
    units = 1.0;
    switch(document.forms["main"].dur_units.value)
    {
        case "years":
            units *= 52;
        case "weeks":
            units *= 7;
        case "days":
            units *= 24;
        case "hours":
            units *= 60;
        case "minutes":
            units *= 60;
        case "seconds":
        break;
    }
    
    // if all day is checked, units is 24 hours
    if (document.forms["main"].all_day.checked) units = 24 * 60 * 60;
    
    // set the start and day information from the entered values
    year = parseInt(document.forms["main"].year.options[document.forms["main"].year.selectedIndex].text, 10);
    mon = parseInt(document.forms["main"].month.selectedIndex, 10) + 1;
    day = parseInt(document.forms["main"].day.options[document.forms["main"].day.selectedIndex].text, 10);
    h = parseInt(document.forms["main"].hour.value, 10);
    m = parseInt(document.forms["main"].minute.value, 10);
    
    StartTime = (((((year - 1970) * 365 + mon) * 31 + day) * 24 + h) * 60 + m) * 60;
    EndTime   = (((((year - 1970) * 365 + mon) * 31 + day) * 24 + h) * 60 + m) * 60 + 
                (units * parseFloat(document.forms["main"].duration.value));
    
    if(StartTime > OrgEndTime || StartTime < OrgStartTime)
    {
        alert("The standby request start time must be greater than the scheduled \nstart time and less than the scheduled end time.");
        return false;
    }
    
    if(EndTime < OrgStartTime || EndTime > OrgEndTime)
    {
        alert("The standby request end time must be less than the scheduled \nend time and greater than the scheduled start time.");
        return false;
    }
    
    if(h > 23 || m > 59)
    {
        alert("You have not entered a\nvalid time of day.");
        return false;
    }
    
    // no errors found, return
	return true;
}
</SCRIPT>
