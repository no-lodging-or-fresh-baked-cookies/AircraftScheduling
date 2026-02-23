<?php
//-----------------------------------------------------------------------------
// 
// edit_entry_handler.php
// 
// PURPOSE: Receives the data from StandbyRequest.php to complete the editing
//          of a standby scheduled entry.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      id - database id of the schedule item we are editing
//      dur_units - units that duration is specified in (minutes, hours, days, etc.)
//      duration - length of schedule entry
//      NameOfUser - user name for the schedule
//      phone_number - phone number for the user
//      description - description of the schedule
//      rep_type - type of repition for the schedule
//      rep_day - day to repeat schedule on
//      rep_end_day - end day of the repeat
//      rep_end_month - end month of the repeat
//      rep_end_year - end year of the repeat
//      create_by -  username that created the schedule
//      edit_type - edit type of the schedule
//      type - not used
//      all_day - set true if the schedule is all day
//      InstructorEntryID - entry ID for the instrutor if one is scheduled
//      InstructorName - name of the instrutor if one is scheduled
//      StandbyRequestID - database id of the standby schedule item we are editing
//      ScheduleName - name of the resource we are scheduling
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
    include "AircraftScheduling_sql.inc";
    
    // initialize variables
    $sql = '';
    $type = "Internal";					// dummy data - not used
    $InstructorName = "None";
    $InstructorResource = "";
    $InstructorEntryID = "";
    $hour = "12";
    $minute = "00";
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
    if(isset($rdata["dur_units"])) $dur_units = $rdata["dur_units"];
    if(isset($rdata["duration"])) $duration = $rdata["duration"];
    if(isset($rdata["NameOfUser"])) $NameOfUser = $rdata["NameOfUser"];
    if(isset($rdata["phone_number"])) $phone_number = $rdata["phone_number"];
    if(isset($rdata["description"])) $description = $rdata["description"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["create_by"])) $create_by = $rdata["create_by"];
    if(isset($rdata["type"])) $type = $rdata["type"];
    if(isset($rdata["all_day"])) $all_day = $rdata["all_day"];
    if(isset($rdata["InstructorEntryID"])) $InstructorEntryID = $rdata["InstructorEntryID"];
    if(isset($rdata["InstructorName"])) $InstructorName = $rdata["InstructorName"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["StandbyRequestID"])) $StandbyRequestID = $rdata["StandbyRequestID"];
    if(isset($rdata["ScheduleName"])) $ScheduleName = $rdata["ScheduleName"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    
    // if we dont know the right date then make it up 
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
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, $goback, "", $InstructorResource);
    	exit;
    }
    
    if(!getWritable($NameOfUser, getName()))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, $goback, "", $InstructorResource);
    	exit;
    }
    
    // Units start in seconds
    $units = 1.0;
    
    switch($dur_units)
    {
    	case "years":
    		$units *= 52;
    	case "weeks":
    		$units *= 7;
    	case "days":
    		$units *= 24;
    	case "hours":
    		$units *= 60;
    	case "minutes":
    		$units *= 60;
    	case "seconds":
    		break;
    }
    
    // Units are now in "$dur_units" numbers of seconds
    if(isset($all_day) && ($all_day == "yes"))
    {
    	$starttime = mktime(0, 0, 0, $month, $day  , $year);
    	$endtime   = mktime(0, 0, 0, $month, $day+1, $year);
    }
    else
    {
    	$starttime = mktime($hour, $minute, 0, $month, $day, $year);
    	$endtime   = mktime($hour, $minute, 0, $month, $day, $year) + ($units * $duration);
    	
    	// Round up the duration to the next whole resolution unit.
    	// If they asked for 0 minutes, push that up to 1 resolution unit.
    	$diff = $endtime - $starttime;
    	if (($tmp = $diff % $resolution) != 0 || $diff == 0)
    		$endtime += $resolution - $tmp;
    }
    
    // set the creation time
    if (isset($StandbyRequestID))
    {
    	// we are updating an existing record, get the creation time from the existing record
    	$CreationTime = sql_query1("SELECT create_time FROM AircraftScheduling_entry WHERE entry_id=$StandbyRequestID");
    }
    else
    {
    	// we are adding a new record, get the creation time from the system clock
    	$CreationTime = strtotime("now");
    }
    
    // Acquire mutex to lock out others trying to book the same slot(s).
    if (!sql_mutex_lock('AircraftScheduling_entry'))
    	fatal_error(1, "Failed to acquire exclusive database access");
    
    // if we are scheduling an instructor, get the resource ID
    if ($InstructorName != "None")
    {
    	// scheduling an instructor, get the resource ID
    	$InstructorResourceID = sql_query1("SELECT resource_id FROM AircraftScheduling_resource WHERE resource_name='$InstructorName'");
    }
    
    // Mark standby request entry type
    $entry_type = $EntryTypeStandby;
    
    // Create the entry:
    $NewID = AircraftSchedulingCreateSingleEntry($starttime, $endtime, $entry_type, 0, $resource_id,
                             $create_by, $NameOfUser, $type, $description, $phone_number, $CreationTime);
    
    // log the creation in the journal
    $Schedulable_ID = sql_query1("SELECT schedulable_id FROM AircraftScheduling_resource WHERE resource_id = $resource_id");
    $ResourceName = sql_query1("SELECT name FROM AircraftScheduling_schedulable WHERE schedulable_id = $Schedulable_ID");
    $Description = 
    			"Adding standby request for " . $ResourceName . " " . $ScheduleName . " " .
    			" for user $NameOfUser" .
    			" at " .
    			date("H:i", $starttime) . " - " . date("H:i", $endtime) . 
    			" on " . strftime('%m/%d/%y', $starttime);
    CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    
    // delete the old entry if we are changing an existing entry
    if (isset($StandbyRequestID))
    {
    	// changing an existing entry, delete the old
    	AircraftSchedulingDelEntry(getName(), $StandbyRequestID, 0, 0);
    }
    
    sql_mutex_unlock('AircraftScheduling_entry');
    
    // Now its all done go back to the default view
    session_write_close();
    if(isset($goback))
    {
        if (!empty($GoBackParameters))
        	    header("Location: $goback" . CleanGoBackParameters($GoBackParameters));
        else
    	    header("Location: " . $goback . "?" .
    	                "day=$day&month=$month&year=$year" .
    	                "&resource=$resource" .
    	                "&resource_id=$resource_id" .
    	                "&InstructorResource=$InstructorResource" .
    	                "$makemodel");
    }
    else
    {
    	Header("Location: index.php?" .
                    "day=$day&month=$month&year=$year" .
                    "&resource=$resource" .
                    "&resource_id=$resource_id" .
                    "&InstructorResource=$InstructorResource" .
                    "$makemodel");
    }
?>
