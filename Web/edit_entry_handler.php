<?php
//-----------------------------------------------------------------------------
// 
// edit_entry_handler.php
// 
// PURPOSE: Receives the data from edit_entry.php to complete the editing
//          of a scheduled entry.
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
    $type = "I";					// dummy data - not used
    $InstructorName = "None";
    $InstructorResource = "";
    $InstructorIgnoreID = 0;
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
    if(isset($rdata["rep_type"])) $rep_type = $rdata["rep_type"];
    if(isset($rdata["rep_day"])) $rep_day = $rdata["rep_day"];
    if(isset($rdata["rep_end_day"])) $rep_end_day = $rdata["rep_end_day"];
    if(isset($rdata["rep_end_month"])) $rep_end_month = $rdata["rep_end_month"];
    if(isset($rdata["rep_end_year"])) $rep_end_year = $rdata["rep_end_year"];
    if(isset($rdata["create_by"])) $create_by = $rdata["create_by"];
    if(isset($rdata["edit_type"])) $edit_type = $rdata["edit_type"];
    if(isset($rdata["type"])) $type = $rdata["type"];
    if(isset($rdata["all_day"])) $all_day = $rdata["all_day"];
    if(isset($rdata["InstructorEntryID"])) $InstructorEntryID = $rdata["InstructorEntryID"];
    if(isset($rdata["InstructorName"])) $InstructorName = $rdata["InstructorName"];
    if(isset($rdata["ScheduleName"])) $ScheduleName = $rdata["ScheduleName"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
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
    
    $err = "";
    
    if(isset($rep_type) && isset($rep_end_month) && isset($rep_end_day) && isset($rep_end_year))
    { 
    	// Get the repeat entry settings
    	$rep_enddate = mktime($hour, $minute, 0, $rep_end_month, $rep_end_day, $rep_end_year);
    }
    else
    {
    	$rep_type = 0;
    }
    
    if(!isset($rep_day))
    	for ($i = 0; $i < 7; $i++) $rep_day[$i] = "";
    
    // For weekly repeat(2), build string of weekdays to repeat on:
    $rep_opt = "";
    if ($rep_type == 2)
    	for ($i = 0; $i < 7; $i++) $rep_opt .= empty($rep_day[$i]) ? "0" : "1";
    
    
    // Expand a series into a list of start times:
    if ($rep_type != 0)
    	$reps = AircraftSchedulingGetRepeatEntryList($starttime, isset($rep_enddate) ? $rep_enddate : 0,
    		$rep_type, $rep_opt, $max_rep_entrys);
    
    // When checking for overlaps, for Edit (not New), ignore this entry and series:
    $repeat_id = 0;
    $InstructorRepeatID = 0;
    if (isset($id))
    {
    	// updating an existing entry
    	$ignore_id = $id;
    	$repeat_id = sql_query1("SELECT repeat_id FROM AircraftScheduling_entry WHERE entry_id=$id");
    	if ($repeat_id < 0)
    		$repeat_id = 0;  // NULL repeat_id (non-repeating) treated as 0 for overlap checks
    	if (isset($InstructorEntryID))
    	{
    		$InstructorIgnoreID = $InstructorEntryID;
    		$InstructorRepeatID = sql_query1("SELECT repeat_id FROM AircraftScheduling_entry WHERE entry_id=$InstructorEntryID");
    		if ($InstructorRepeatID < 0)
    			$InstructorRepeatID = 0;  // NULL repeat_id (non-repeating) treated as 0 for overlap checks
    	}
    	
    	// we are updating an existing record, get the creation time from the existing record
    	$CreationTime = sql_query1("SELECT create_time FROM AircraftScheduling_entry WHERE entry_id=$id");
    	
    	// save information for journal entry
    	$Action = "Changing";
    }
    else
    {
    	// creating a new entry
    	$ignore_id = 0;
    	$InstructorIgnoreID = 0;
    	
    	// we are adding a new record, get the creation time from the system clock
    	// and adjust for the local time zone
    	$CreationTime = strtotime("now");
    	
    	// save information for journal entry
    	$Action = "Creating";
    }
    
    // Acquire mutex to lock out others trying to book the same slot(s).
    if (!sql_mutex_lock('AircraftScheduling_entry'))
    	fatal_error(1, "Failed to acquire exclusive database access");
    
    // if we are scheduling an instructor, get the resource ID
    if ($InstructorName != "None")
    {
    	// scheduling an instructor, get the resource ID
    	$InstructorResourceID = sql_query1("SELECT resource_id FROM AircraftScheduling_resource WHERE resource_name='$InstructorName'");
    	
    	// if the resource ID is -1, we had an error
    	if ($InstructorResourceID == -1)
    	{
    	    // bad resource ID, we have an error
    	    $InstructorName = "None";
    		$err = $err . $lang["InstructorError"] . "<P>";
    		$hide_title = 1;
    	}
    }
    
    // Check for any schedule conflicts
    if ($rep_type != 0 && !empty($reps))
    {
    	// check for conficts for repeating entries
    	if(count($reps) < $max_rep_entrys)
    	{
    		$diff = $endtime - $starttime;
    		
    		for($i = 0; $i < count($reps); $i++)
    		{
    			// check for scheduling conficts for the main item we are scheduling
    			$tmp = AircraftSchedulingCheckFree($resource_id, $reps[$i], $reps[$i] + $diff, $ignore_id, $repeat_id);
    			
    			// save any errors
    			if(!empty($tmp))
    				$err = $err . $tmp;
    				
    			// if we are scheduling an instructor, check for conflicts
    			if ($InstructorName != "None")
    			{
    				// scheduling an instructor, check for conflicts
    				$tmp = AircraftSchedulingCheckFree($InstructorResourceID, $reps[$i], $reps[$i] + $diff, $InstructorIgnoreID, $InstructorRepeatID);
    				
    				// save any errors
    				if(!empty($tmp))
    					$err = $err . $tmp;
    			}
    		}
    	}
    	else
    	{
    		$err = $err . $lang["too_may_entrys"] . "<P>";
    		$hide_title = 1;
    	}
    }
    else
    {
    	// check for non-repeating conflicts
    	$tmp = AircraftSchedulingCheckFree($resource_id, $starttime, $endtime-1, $ignore_id, 0);
    		
    	// save any errors
    	if(!empty($tmp))
    		$err = $err . $tmp;
    				
    	// if we are scheduling an instructor, check for conflicts
    	if ($InstructorName != "None")
    	{
    		// scheduling an instructor, check for conflicts
    		$tmp = AircraftSchedulingCheckFree($InstructorResourceID, $starttime, $endtime-1, $InstructorIgnoreID, 0);
    		
    		// save any errors
    		if(!empty($tmp))
    			$err = $err . $tmp;
    	}
    }
    
    // if we did not have any errors, schedule the entries
    if(empty($err))
    {
    	if($edit_type == "series")
    	{
    		// schedule the main entry
    		AircraftSchedulingCreateRepeatingEntrys($starttime, $endtime, $rep_type, $rep_enddate, $rep_opt, 
    		                          $resource_id, $create_by, $NameOfUser, $type, $description, $phone_number, 
    		                          $CreationTime);
    						
    		// if we are scheduling an instructor, add the schedule
    		if ($InstructorName != "None")
    		{
    			AircraftSchedulingCreateRepeatingEntrys($starttime, $endtime, $rep_type, $rep_enddate, $rep_opt, 
    			                          $InstructorResourceID, $create_by, $NameOfUser, $type, $description, $phone_number,
    			                          $CreationTime);
    		}
    	}
    	else
    	{
    		// Mark changed entry in a series with entry_type 2:
    		if ($repeat_id > 0)
    			$entry_type = 2;
    		else
    			$entry_type = 0;
    		
    		// Create the entry:
    		$NewID = AircraftSchedulingCreateSingleEntry($starttime, $endtime, $entry_type, $repeat_id, $resource_id,
    		                         $create_by, $NameOfUser, $type, $description, $phone_number, $CreationTime);
    				
    		// if we are scheduling an instructor, add the schedule
    		if ($InstructorName != "None")
    		{
    			$NewID = AircraftSchedulingCreateSingleEntry($starttime, $endtime, $entry_type, $InstructorRepeatID, $InstructorResourceID,
    			                         $create_by, $NameOfUser, $type, $description, $phone_number, $CreationTime);
    		}
    	}
    	
    	// remove the original entry and reschedule standby requests
    	if(isset($id))
    	{
    		// save the original information so we can check for standby request
    		$info = AircraftSchedulingGetEntryInfo($id);
    		
    		// delete the original entry
    		AircraftSchedulingDelEntry(getName(), $id, ($edit_type == "series"), 1);
    	}
    	if(isset($InstructorEntryID))
    	{
    		if ($InstructorEntryID != $id)
    		{
    			// save the original information so we can check for standby request
    			$info = AircraftSchedulingGetEntryInfo($InstructorEntryID);
    			
    			// lookup the instructor name for the journal entry in case it is deleted
    			$DeletedInstructorName = sql_query1(
    									"SELECT $DatabaseNameFormat 
    									FROM AircraftScheduling_person LEFT JOIN AircraftScheduling_instructors USING (person_id) 
    									WHERE resource_id=" . $info["resource_id"]);
    			
    			// delete the original entry
    			AircraftSchedulingDelEntry(getName(), $InstructorEntryID, ($edit_type == "series"), 1);
    		}
    		else
    		{
    			$DeletedInstructorName = -1;
    		}
    	}
    	
    	sql_mutex_unlock('AircraftScheduling_entry');
    
        // get the repeat information so we can log it
        if ($rep_type != 0)
    	{
    	    // repeat entry, build the log string
            $RepeatDescription = " ";
            if ($rep_type == 0)         // none
                $RepeatDescription = $RepeatDescription . "";
            else if ($rep_type == 1)    // daily
                $RepeatDescription = $RepeatDescription . "daily repeat";
            else if ($rep_type == 2)    // weekly
            {
                $RepeatDescription = $RepeatDescription . "weekly repeat on ";
                if ($rep_day[0]) $RepeatDescription = $RepeatDescription . "Sunday";
                else if ($rep_day[1]) $RepeatDescription = $RepeatDescription . "Monday";
                else if ($rep_day[2]) $RepeatDescription = $RepeatDescription . "Tuesday";
                else if ($rep_day[3]) $RepeatDescription = $RepeatDescription . "Wednesday";
                else if ($rep_day[4]) $RepeatDescription = $RepeatDescription . "Thursday";
                else if ($rep_day[5]) $RepeatDescription = $RepeatDescription . "Friday";
                else if ($rep_day[6]) $RepeatDescription = $RepeatDescription . "Saturday";          
                else $RepeatDescription = $RepeatDescription . 
                			date("l", mktime(0, 0, 0, $month, $day, $year));
            }    
            else if ($rep_type == 3)    // monthly
                $RepeatDescription = $RepeatDescription . "monthly repeat";
            else if ($rep_type == 4)    // yearly
                $RepeatDescription = $RepeatDescription . "yearly repeat";
            else if ($rep_type == 5)    // monthly, corresponding day 
                $RepeatDescription = $RepeatDescription . "monthly, corresponding day repeat";
            
            // add in the end day
            $RepeatDescription = $RepeatDescription . " ending " . 
                    strftime('%m/%d/%y', mktime(0, 0, 0, $rep_end_month, $rep_end_day, $rep_end_year)); 
        }
        else
        {
            // not a repeat entry
            $RepeatDescription = "";
        }
        
    	// log the journal entry
    	$Schedulable_ID = sql_query1("SELECT schedulable_id FROM AircraftScheduling_resource WHERE resource_id = $resource_id");
    	$ResourceName = sql_query1("SELECT name FROM AircraftScheduling_schedulable WHERE schedulable_id = $Schedulable_ID");
    	if (isset($id))
    	{
    		// updating an existing entry
    		$Description = 
    					"Changing " . $ResourceName . " " . $ScheduleName . " " .
    					" for user $NameOfUser" .
    					" from " . 
    					date("H:i", $info["start_time"]) . " - " . date("H:i", $info["end_time"]) . 
    					" on " . strftime('%m/%d/%y', $info["start_time"]) .
    					" to " .
    					date("H:i", $starttime) . " - " . date("H:i", $endtime) . 
    					" on " . strftime('%m/%d/%y', $starttime);
    		CreateJournalEntry(strtotime("now"), getUserName(), $Description . $RepeatDescription);
    		
    		// did we change or add an instructor?
    		if ($InstructorName != "None")
    		{
    			// added or changed an instructor
    			if (isset($InstructorEntryID))
    			{
    				// changed an instructor
    				$Description = 
    							"Changing Instructor " . $InstructorName . " " .
    							" for user $NameOfUser" .
    							" from " . 
    							date("H:i", $info["start_time"]) . " - " . date("H:i", $info["end_time"]) . 
    							" on " . strftime('%m/%d/%y', $info["start_time"]) .
    							" to " .
    							date("H:i", $starttime) . " - " . date("H:i", $endtime) . 
    							" on " . strftime('%m/%d/%y', $starttime);
    				CreateJournalEntry(strtotime("now"), getUserName(), $Description . $RepeatDescription);
    			}
    			else
    			{
    				// added an instructor
    				$Description = 
    							"Added Instructor " . $InstructorName . " " .
    							" for user $NameOfUser" .
    							" on " .
    							date("H:i", $starttime) . " - " . date("H:i", $endtime) . 
    							" on " . strftime('%m/%d/%y', $starttime);
    				CreateJournalEntry(strtotime("now"), getUserName(), $Description . $RepeatDescription);
    			}
    		}
    		else
    		{
    			// did we delete an instructor?
    			if (isset($InstructorEntryID) && $DeletedInstructorName != -1)
    			{
    				// deleted an instructor
    				$Description = 
    							"Deleted Instructor " . $DeletedInstructorName . " " .
    							" for user $NameOfUser" .
    							" from " .
    							date("H:i", $starttime) . " - " . date("H:i", $endtime) . 
    							" on " . strftime('%m/%d/%y', $starttime);
    				CreateJournalEntry(strtotime("now"), getUserName(), $Description . $RepeatDescription);
    			}
    		}
    	}
    	else
    	{
    		// creating a new entry
    		$Description = 
    					"Added " . $ResourceName . " " . $ScheduleName . " " .
    					" for user $NameOfUser" .
    					" at " .
    					date("H:i", $starttime) . " - " . date("H:i", $endtime) . 
    					" on " . strftime('%m/%d/%y', $starttime);
    		CreateJournalEntry(strtotime("now"), getUserName(), $Description . $RepeatDescription);
    		if ($InstructorName != "None")
    		{
    			$Description = 
    						"Added Instructor " . $InstructorName . " " .
    						" for user $NameOfUser" .
    						" at " .
    						date("H:i", $starttime) . " - " . date("H:i", $endtime) . 
    						" on " . strftime('%m/%d/%y', $starttime);
    			CreateJournalEntry(strtotime("now"), getUserName(), $Description . $RepeatDescription);
    		}
    	}
    	
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
    	exit;
    }
    
    // The item was not free.
    sql_mutex_unlock('AircraftScheduling_entry');
    
    // if we had any errors, display the errors
    if(strlen($err))
    {
    	print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    	
    	echo "<H2>" . $lang["sched_conflict"] . "</H2>";
    	if(!isset($hide_title))
    	{
    		echo $lang["conflict"];
    		echo "<UL>";
    	}
    	
    	echo $err;
    	
    	if(!isset($hide_title))
    		echo "</UL>";
    }
    
    // generate the return URL
    GenerateReturnURL($goback, 
                        "?day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" . 
                        $makemodel);
    
    include "trailer.inc"; 
?>
