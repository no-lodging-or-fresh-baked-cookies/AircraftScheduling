<?php
//-----------------------------------------------------------------------------
// 
// view_entry.php
// 
// PURPOSE: Displays a scheduled entry.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//
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
    
    // initialize variables
    $id = '';
    $item_name = '';
    $sql = '';
    $InstructorResource = "";
    $InstructorName = "";
    $InstructorEntryID = "";
    $hour = "12";
    $minute = "00";
    $goback = "";
    $GoBackParameters = "";
    $DisplayAllStandbyRequest = 0;			// set to 1 to display all requests
    										// set to 0 to display only this resource
    
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
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // SetAircraftInformation($id)
    //
    // Purpose: Set the sql and cost information for an aircraft.
    //
    // Inputs:
    //   id - the entry ID of the current schedule entry.
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
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
    
    //********************************************************************
    // SetInstructorInformation($id)
    //
    // Purpose: Set the sql and cost information for an instructor.
    //
    // Inputs:
    //   id - the entry ID of the current schedule entry.
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
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
    
    //********************************************************************
    // InstructorIsScheduled(
    //                      $id, 
    //                      $ScheduleUserName, 
    //                      $ScheduleStartTime, 
    //                      $ScheduleEndTime, 
    //                      $CreationTime)
    //
    // Purpose: Check for an instructor scheduled for the user at the given
    //          start and end times
    //
    // Inputs:
    //   id - the entry ID of the current schedule entry.
    //   ScheduleUserName - user name of the schedule entry
    //   ScheduleStartTime - start time of the schedule entry
    //   ScheduleEndTime - end time of the schedule entry
    //   CreationTime - creation time of the schedule entry
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   The entry that matches the input requirements.
    //*********************************************************************
    function InstructorIsScheduled(
                                    $id, 
                                    $ScheduleUserName, 
                                    $ScheduleStartTime, 
                                    $ScheduleEndTime, 
                                    $CreationTime)
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
    
    //********************************************************************
    // AircraftIsScheduled(
    //                      $id, 
    //                      $ScheduleUserName, 
    //                      $ScheduleStartTime, 
    //                      $ScheduleEndTime, 
    //                      $CreationTime)
    //
    // Purpose: Check for an aircraft scheduled for the user at the given
    //          start and end times
    //
    // Inputs:
    //   id - the entry ID of the current schedule entry.
    //   ScheduleUserName - user name of the schedule entry
    //   ScheduleStartTime - start time of the schedule entry
    //   ScheduleEndTime - end time of the schedule entry
    //   CreationTime - creation time of the schedule entry
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   The entry that matches the input requirements.
    //*********************************************************************
    function AircraftIsScheduled(
                                $id, 
                                $ScheduleUserName, 
                                $ScheduleStartTime, 
                                $ScheduleEndTime, 
                                $CreationTime)
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

    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
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
    								c.entry_id=$id AND 
    								c.resource_id=b.resource_id AND 
    								b.schedulable_id=a.schedulable_id"); 
    
    // setup the information for the resource we are viewing
    if($ScheduleType == "Aircraft") 
    {
    	// type is an aircraft, setup the information for an aircraft
    	SetAircraftInformation($id);
    	
    	// get the results of the query
    	$res = sql_query($sql);
    	if (! $res) fatal_error(0, sql_error() . "<BR>SQL:" . $sql);
    	if(sql_count($res) < 1) fatal_error(0, "Invalid entry id:" . $id . " for ScheduleType: Aircraft");
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
    												$id, 
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
    		if (!$InstructorResult) fatal_error(0, sql_error() . "<BR>SQL:" . $InstructorSQL);
    		
    		// if we have an error looking up the instructor, clear the instructor info
    		// and delete the bad record
    		if(sql_count($InstructorResult) < 1)
    		{			
    			// delete the incorrect entry in the database
    			sql_command("DELETE FROM AircraftScheduling_entry WHERE entry_id = $InstructorEntryID");
    
    			// error in the database, clear the instructor information
    			unset($InstructorEntryID);
    			$InstructorName = "";
    			$DisplayAllStandbyRequest = 0;
    		}
    		else
    		{
    			$InstructorRow = sql_row($InstructorResult, 0);
    			sql_free($InstructorResult);
    			$InstructorName = $InstructorRow[0];
    			$DisplayAllStandbyRequest = 1;
    		}
    	}
    	else
    	{
    		unset($InstructorEntryID);
    		$InstructorName = "";
    		$DisplayAllStandbyRequest = 0;
    	}
    	$IDStandby = $id;
    }
    else if($ScheduleType == "Instructor") 
    {
    	// type is an instructor
    	// setup the information for an instructor
    	SetInstructorInformation($id);
    
    	// get the results of the query
    	$res = sql_query($sql);
    	if (!$res) fatal_error(0, sql_error() . "<BR>SQL:" . $sql);
    	if(sql_count($res) < 1) fatal_error(0, "Invalid entry id:" . $id . " for ScheduleType: Instructor");
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
    											$id, 
    											$ScheduleUserName, 
    											$ScheduleStartTime, 
    											$ScheduleEndTime,
    											$CreationTime);
    	if ($AircraftEntryID > 0)
    	{
    		SetAircraftInformation($AircraftEntryID);
    		
    		// get the results of the query
    		$res = sql_query($sql);
    		if (! $res) fatal_error(0, sql_error() . "<BR>SQL:" . $sql);
    		if(sql_count($res) < 1) fatal_error(0, "Invalid entry AircraftEntryID:" . $AircraftEntryID . " for ScheduleType: Instructor");
    		$row = sql_row($res, 0);
    		sql_free($res);
    		
    		// an instructor is scheduled at the same time for the
    		// same user, link them together
    		$InstructorEntryID = $id;
    		$id = $AircraftEntryID;
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
    		if (!$InstructorResult) fatal_error(0, sql_error() . "<BR>SQL:" . $InstructorSQL);
    		if(sql_count($InstructorResult) < 1) fatal_error(0, "Invalid entry InstructorEntryID:" . $InstructorEntryID . " for ScheduleType: Instructor");
    		$InstructorRow = sql_row($InstructorResult, 0);
    		sql_free($InstructorResult);
    		$InstructorName = $InstructorRow[0];
    		$DisplayAllStandbyRequest = 1;
    	}
    	else
    	{
    		$InstructorEntryID = $id;
    	}
    	$IDStandby = $InstructorEntryID;
    }
    else
    {
    	// this shouldn't happen, but treat it as an aircraft just in case
    	// type is an aircraft, setup the information for an aircraft
    	SetAircraftInformation($id);
    	
    	// get the results of the query
    	$res = sql_query($sql);
    	if (! $res) fatal_error(0, sql_error() . "<BR>SQL:" . $sql);
    	if(sql_count($res) < 1) fatal_error(0, "Invalid entry id:" . $id . " for ScheduleType: default");
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
    												$id, 
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
    		if (!$InstructorResult) fatal_error(0, sql_error() . "<BR>SQL:" . $InstructorSQL);
    		if(sql_count($InstructorResult) < 1) fatal_error(0, "Invalid entry InstructorEntryID:" . $InstructorEntryID . " for ScheduleType: default");
    		$InstructorRow = sql_row($InstructorResult, 0);
    		sql_free($InstructorResult);
    		$InstructorName = $InstructorRow[0];
    		$DisplayAllStandbyRequest = 1;
    	}
    	else
    	{
    		unset($InstructorEntryID);
    		$InstructorName = "";
    	}
    	$IDStandby = $id;
    }
    
    // Note: Removed stripslashes() calls from name and description. Previous
    // versions of AircraftScheduling mistakenly had the backslash-escapes in the actual database
    // records because of an extra addslashes going on. Fix your database and
    // leave this code alone, please.
    $name         = htmlspecialchars($row[0]);
    $description  = htmlspecialchars($row[1]);
    $create_by    = htmlspecialchars($row[2]);
    $create_time  = strftime('%X - %A %d %B %Y', $row[3] - TimeZoneAdjustment());
    $resource_name    = htmlspecialchars($row[4]);
    $type         = $row[5];
    $resource_id  = $row[6];
    $repeat_id    = $row[7];
    $updated      = strftime('%X - %A %d %B %Y', $row[8] - TimeZoneAdjustment());
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
    
    
    $rep_type = 0;
    
    if($repeat_id != 0)
    {
    	$sql = "SELECT rep_type, end_date, rep_opt
    	                    FROM AircraftScheduling_repeat WHERE repeat_id=$repeat_id";
    	$res = sql_query($sql);
    	if (! $res) fatal_error(0, sql_error() . "<BR>SQL:" . $sql);
    
    	if (sql_count($res) == 1)
    	{
    		$row = sql_row($res, 0);
    		
    		$rep_type     = $row[0];
    		$rep_end_date = strftime('%A %d %B %Y',$row[1]);
    		$rep_opt      = $row[2];
    	}
    	sql_free($res);
    }
    
    toTimeString($duration, $dur_units);
    
    $repeat_key = "rep_type_" . $rep_type;
    
    // Now that we know all the data we start drawing it
    
    // show a link to the user's email address if one is on file
    echo "<H3>";
  	if ($email_address == "") echo "$name - $phone_number";
   	else echo "<A href='mailto:$email_address'>$name - " . stripslashes($phone_number) . "</A>" ;
   	echo "</H3>";
   	
   	// scheduling information
    echo "<table border=0>";
    echo "<tr>";
    
    // description
    echo "<td><b>";
    echo $lang["description"];
    echo "</b></td>";
    echo "<td>";
    echo nl2br($description);
    echo "</td>";
    echo "</tr>";
    
    // resource name
    echo "<tr>";
    echo "<td><b>";
    echo $lang["room"];
    echo "</b></td>";
    echo "<td>";
   	if (strlen($InstructorName) > 0)
    	echo  nl2br($resource_name . " - " . $item_name . " with Instructor: " . $InstructorName);
    else 
        echo  nl2br($resource_name . " - " . $item_name);
    echo "</td>";
    echo "</tr>";
    
    // starting date
    echo "<tr>";
    echo "<td><b>";
    echo $lang["start_date"];
    echo "</b></td>";
    echo "<td>";
    echo $start_date;
    echo "</td>";
    echo "</tr>";
    
    // duration
    echo "<tr>";
    echo "<td><b>";
    echo $lang["duration"];
    echo "</b></td>";
    echo "<td>";
    echo $duration . " " . $dur_units;
    echo "</td>";
    echo "</tr>";

    // cost
    if($cost != 0) 
    {
        echo "<tr>";
        echo "<td><b>";
        echo $lang["estcost"];
        echo "</b></td>";
        echo "<td>";
        echo $CurrencyPrefix . $cost;
        echo "</td>";
        echo "</tr>";
    }
    
    // hourly rate
    if($hourly_cost != 0) 
    {
        echo "<tr>";
        echo "<td><b>";
        echo $lang["hourlyrate"];
        echo "</b></td>";
        echo "<td>";
        echo $CurrencyPrefix . $hourly_cost;
        echo "</td>";
        echo "</tr>";
    }
    
    // end date
    echo "<tr>";
    echo "<td><b>";
    echo $lang["end_date"];
    echo "</b></td>";
    echo "<td>";
    echo $end_date;
    echo "</td>";
    echo "</tr>";
    
    // schedule type
    // echo "<!-- tr>";
    // echo "<td><b>";
    // echo $lang["type"];
    // echo "</b></td>";
    // echo "<td>";
    // echo empty($typel[$type]) ? "?$type?" : $typel[$type];
    // echo "</td>";
    // echo "</tr -->";
    
    // created by
    echo "<tr>";
    echo "<td><b>";
    echo $lang["createdby"];
    echo "</b></td>";
    echo "<td>";
    echo $create_by;
    echo "</td>";
    echo "</tr>";
    
    // creation time
    echo "<tr>";
    echo "<td><b>";
    echo $lang["createtime"];
    echo "</b></td>";
    echo "<td>";
    echo $create_time;
    echo "</td>";
    echo "</tr>";
    
    // last updated time
    echo "<tr>";
    echo "<td><b>";
    echo $lang["lastupdate"];
    echo "</b></td>";
    echo "<td>";
    echo $updated;
    echo "</td>";
    echo "</tr>";
    
    // repeat type
    echo "<tr>";
    echo "<td><b>";
    echo $lang["rep_type"];
    echo "</b></td>";
    echo "<td>";
    echo $lang[$repeat_key];
    echo "</td>";
    echo "</tr>";
   
    // display the repeat days if we need to
    if($rep_type != 0)
    {
    	$opt = "";
    	if ($rep_type == 2)
    	{
    		// Display day names according to language and preferred weekday start.
    		for ($i = 0; $i < 7; $i++)
    		{
    			$daynum = ($i + $weekstarts) % 7;
    			if ($rep_opt[$daynum]) $opt .= day_name($daynum) . " ";
    		}
    	}
    	
    	if($opt)
    		echo "<tr><td><b>$lang[rep_rep_day]</b></td><td>$opt</td></tr>\n";
    	
    	echo "<tr><td><b>$lang[rep_end_date]</b></td><td>$rep_end_date</td></tr>\n";
    }

    // display any standby requests that may have been made
    $sql = "SELECT " .
    			"name, " .
    			"create_time, " .
    			"start_time, " .
    			"end_time, " .
    			"entry_id, " .
    			"phone_number, " .
    			"(end_time - start_time) " .
    		"FROM " .
    			"AircraftScheduling_entry " .
    		"WHERE " .
    			"entry_type = $EntryTypeStandby AND " .
    			"((start_time >= $start_time AND " .
    			"  start_time < $end_time) OR" .
    			" (end_time > $start_time AND " .
    			"  end_time <= $end_time)) AND ";
    if ($DisplayAllStandbyRequest)
    {
    	// display instructor and aircraft standby requests
    	// get the resource ID for the instructor
    	$InstructorResourceID = sql_query1(
    	                "SELECT resource_id " .
    	                "FROM AircraftScheduling_entry " .
    	                "WHERE entry_id = $InstructorEntryID");
    	$sql = $sql . "(resource_id = $resource_id OR resource_id = $InstructorResourceID)";
    }
    else
    {
    	// display only the selected resource standby requests
    	$sql = $sql . "resource_id = $resource_id ";
    }
    $sql = $sql . " ORDER BY create_time";
    
    // if we had any standby requests, display them
    $res = sql_query($sql);
    if ($res)
    {
    	echo "<tr>";
    	echo "<td><b>Standby Requests: </b></td><br>";
    	echo "</tr>";
    	$Priority = 1;
    	for ($i = 0; ($row = sql_row($res, $i)); $i++) 
    	{
    		// display the standby requests
    		$StandbyDuration = $row[6];
    		$phone_number = $row[5];
    		toTimeString($StandbyDuration, $standby_units);
    		echo "<tr>";
    		echo "<td></td>";
    		echo 
    				"<td><a href='StandbyRequestView.php?" .
    					"id=$id" .
    					"&StandbyID=$row[4]" .
    					"&resource=$resource" .
                        "&resource_id=$resource_id" .
    			        "&InstructorResource=$InstructorResource" .
     					"&day=$day&month=$month&year=$year$makemodel" .
    				    "&goback=" . GetScriptName() .
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    					"'> $Priority) $row[0] - $phone_number at " .
    					strftime('%X - %A %d %B %Y', $row[2]) . 
    					" for $StandbyDuration $standby_units" .  
    				"</td>";
    		echo "</tr>";
    		$Priority++;
    	}
    }
    
    // display a link to add standby requests
    $StandbyRequestString = $lang["StandbyRequest"];
    echo "<tr>";
    echo "<td></td>";
    echo "<td><A HREF='" .
    				"StandbyRequest.php?" .
    						"id=$IDStandby" .
    						"&resource=$resource" .
                            "&resource_id=$resource_id" .
    						"&InstructorResource=$InstructorResource" .
    						"&day=$day&month=$month&year=$year$makemodel" .
        				    "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    						"'>" .
    						$StandbyRequestString . "</A>" . 
    						"</td>";
    echo "</table>";

    // if changes are allowed, display the links to the change pages
    if($changes_allowed) 
    {
        // changes allowed, show the change links
    	echo "<br>";
    	echo "<p>";
        echo "<a href='edit_entry.php";
        echo "?id=$id"; 
        if (isset($InstructorEntryID)) echo "&InstructorEntryID=$InstructorEntryID"; 
    	echo "&InstructorName=$InstructorName&resource=$resource&resource_id=$resource_id&InstructorResource=$InstructorResource$makemodel";
        if (isset($goback) && !empty($goback)) echo "&goback=$goback"; 
        if (!empty($GoBackParameters)) echo "&GoBackParameters=$GoBackParameters"; 
        echo "'>";
        echo $lang["editentry"] . "</a>";
    	
    	if($repeat_id)
    	{
    		echo " - <a href='edit_entry.php?id=$id";
    		if (isset($InstructorEntryID)) echo "&InstructorEntryID=$InstructorEntryID";
    		echo "&InstructorName=$InstructorName&edit_type=series&day=$day&month=$month&year=$year&resource=$resource&resource_id=$resource_id&InstructorResource=$InstructorResource$makemodel";
    		if (isset($goback)) echo "&goback=$goback"; 
            if (!empty($GoBackParameters)) echo "&GoBackParameters=$GoBackParameters"; 
    		echo "'>$lang[editseries]</a>";
    	}
    	
        echo "<BR>";
    	echo "<A HREF='del_entry.php";
    	echo "?id=$id"; 
    	if (isset($InstructorEntryID)) echo "&InstructorEntryID=$InstructorEntryID"; 
    	echo "&RecordType=Normal&series=0&resource=$resource&resource_id=$resource_id&InstructorResource=$InstructorResource&day=$day&month=$month&year=$year$makemodel";
    	if (isset($goback)) echo "&goback=$goback"; 
        if (!empty($GoBackParameters)) echo "&GoBackParameters=$GoBackParameters"; 
        echo "' onClick=\"return confirm('$lang[confirmdel]');";
        echo "\">$lang[deleteentry]</A>";
    	
    	if($repeat_id)
    	{
    		$Confirmdel = $lang["confirmdel"];
    		$DeleteSeriers = $lang["deleteseries"];
    		echo " - <A HREF='del_entry.php?id=$id&RecordType=Normal";
    		if (isset($InstructorEntryID)) echo "&InstructorEntryID=$InstructorEntryID";
    		echo "&series=1&resource=$resource&resource_id=$resource_id&InstructorResource=$InstructorResource&day=$day&month=$month&year=$year$makemodel";
        	if (isset($goback)) echo "&goback=$goback"; 
            if (!empty($GoBackParameters)) echo "&GoBackParameters=$GoBackParameters"; 
    		echo "' onClick=\"return confirm('$Confirmdel');";
    		echo "\">$DeleteSeriers</A>";
    	}
    	echo "<BR>";
    }
    else
    { 
        // changes not allowed. show the reason.
        echo "<h3>$ChangesProhibitedReason</h3>";
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
    
    // generate the trailer information
    include "trailer.inc"; 
?>
