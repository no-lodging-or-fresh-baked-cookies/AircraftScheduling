<?php
//-----------------------------------------------------------------------------
// 
// AircraftScheduling_sql.inc
// 
// PURPOSE: Contains functions to check and set scheduling information in the
//          database.
// 
// PARAMETERS:
//      none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------

//********************************************************************
// AircraftSchedulingCheckFree(
//                                $resource_id, 
//                                $starttime, 
//                                $endtime, 
//                                $ignore, 
//                                $repignore)
//
// Purpose: Check to see if the time period specified is free.
//
// Inputs:
//   resource_id   - Which room are we checking
//   starttime - The start of period
//   endtime   - The end of the period
//   ignore    - An entry ID to ignore, 0 to ignore no entries
//   repignore - A repeat ID to ignore everything in the series, 0 to ignore no series
//
// Outputs:
//   none
//
// Returns:
//   nothing   - The area is free
//   something - An error occured, the return value is human readable
//*********************************************************************
function AircraftSchedulingCheckFree(
                                $resource_id, 
                                $starttime, 
                                $endtime, 
                                $ignore, 
                                $repignore)
{
	global $lang;
	global $EntryTypeStandby, $EntryTypeNormal, $EntryTypeRepeating, $EntryTypeModified;
    global $_SERVER;

	// Select any meetings which overlap ($starttime,$endtime) for this room:
	$sql = "SELECT entry_id, name, start_time, end_time, phone_number FROM AircraftScheduling_entry WHERE
		start_time < $endtime AND end_time > $starttime
		AND resource_id = $resource_id
	    AND entry_type != $EntryTypeStandby";

	if ($ignore > 0)
		$sql .= " AND entry_id <> $ignore";
	if ($repignore > 0)
		$sql .= " AND repeat_id <> $repignore";
	$sql .= " ORDER BY start_time";

	$res = sql_query($sql);
	if(! $res)
		return sql_error();
	if (sql_count($res) == 0)
	{
		sql_free($res);
		return "";
	}
	// Get the room's area ID for linking to day, week, and month views:
	$resource = AircraftSchedulingGetResourceName($resource_id);
	
	// Build a string listing all the conflicts:
	$err = "";
	for ($i = 0; ($row = sql_row($res, $i)); $i++)
	{
		$starts = getdate($row[2]);
		$param_ym = "resource=$resource&year=$starts[year]&month=$starts[mon]";
		$param_ymd = $param_ym . "&day=$starts[mday]";

		$err .= "<LI><A HREF='view_entry.php" 
		            . "?resource=$resource"
		            . "&resource_id=$resource_id"
		            . "&year=$starts[year]&month=$starts[mon]&day=$starts[mday]"
		            . "&id=$row[0]"
		            . "&goback=" . GetScriptName()
		            . "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"])
		            . "'>$row[1] ($row[4])</A>"
		. " (" . strftime('%A %d %B %Y %H:%M:%S', $row[2]) . " - " . strftime('%H:%M:%S', $row[3]) . ") "
		. "(<A HREF='day.php?$param_ymd'>$lang[viewday]</a>"
		. " | <A HREF='week.php?resource_id=$resource_id&$param_ymd'>$lang[viewweek]</a>"
		. " | <A HREF='month.php?resource_id=$resource_id&$param_ym'>$lang[viewmonth]</a>)";
	}
	
	return $err;
}

//********************************************************************
// AircraftSchedulingDelEntry($user, $id, $series, $all)
//
// Purpose: Delete an entry, or optionally all entrys.
//
// Inputs:
//   resource_id - Which room are we checking
//   user - Who's making the request
//   id - The entry to delete
//   series - If set, delete the series, except user modified entrys
//   all - If set, include user modified entrys in the series delete
//
// Outputs:
//   none
//
// Returns:
//   0        - An error occured
//   >= zero  - The entry was deleted
//*********************************************************************
function AircraftSchedulingDelEntry($user, $id, $series, $all)
{
	global $changes_prohibited_interval;
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
	global $EntryTypeStandby, $EntryTypeNormal, $EntryTypeRepeating, $EntryTypeModified;
	
	$repeat_id = sql_query1("SELECT repeat_id FROM AircraftScheduling_entry WHERE entry_id=$id");
	$starttime = sql_query1("SELECT start_time FROM AircraftScheduling_entry WHERE entry_id=$id");

	// if changes beyond the NO change interval
	$changes_prohibited = strtotime("+$changes_prohibited_interval") - strtotime("now");
	if ($changes_prohibited > 0)
		$and = " AND (start_time > ".strtotime("+$changes_prohibited_interval");
	else
		$and = " AND (start_time > 0";
	
	// OR if we are admin, allow the changes
	$and .= " OR $_SESSION[ActiveUserLevel] > $UserLevelNormal) ";

	if(abs($starttime - strtotime("now")) < $changes_prohibited && $_SESSION["ActiveUserLevel"] <= $UserLevelNormal)
	     return 0;

	// repeat_id is NULL (sql_query1 returns -1) for non-repeating entries.
	// For series deletes we need a valid positive repeat_id.
	if ($series && $repeat_id <= 0)
	{
	    // error attempting to delete a series without a valid repeat_id
        $Description = "Program Error in AircraftSchedulingDelEntry: " .
                        "Attempting to delete a series without a valid repeat_id. " .
                        "user: $user id: $id series: $series all: $all";
        CreateJournalEntry(strtotime("now"), getUserName(), $Description);

        // return error
        return 0;
	}
	
	$sql = "SELECT create_by, entry_id, entry_type, name, start_time, end_time, resource_id 
			FROM AircraftScheduling_entry WHERE ";
	
	if($series)
		$sql .= "repeat_id=$repeat_id";
	else
		$sql .= "entry_id=$id";
	
	// Selects all entries for the series or ID that are beyond the changes prohibited
 	// interval OR all entries that can be deleted by the administrator.
	// That is: if it is a series DO NOT delete entries within the prohibited interval
	//          unless we are an administrator
	$res = sql_query("$sql $and");
	$removed = 0;
	//   Delete the selected entries one by one...
	for ($i = 0; ($row = sql_row($res, $i)); $i++)
	{
		if(!getWritable($row[3], $user))
			continue;
		
		if($series && $row[2] == 2 && !$all)
			continue;
		
		if (sql_command("DELETE FROM AircraftScheduling_entry WHERE entry_id=" . $row[1]) > 0)
			$removed++;
					
		// check to see if we need to move any standby requests into an open slot
		AircraftSchedulingCheckStandbyRequest($row[4], $row[5], $row[6]);
	}

	if ($repeat_id > 0 &&
            sql_query1("SELECT count(*) FROM AircraftScheduling_entry WHERE repeat_id=$repeat_id $and") == 0) 
	{
		sql_command("DELETE FROM AircraftScheduling_repeat WHERE repeat_id=$repeat_id");
		// There may still be some entries from this series left over if they fall within
		//  the changes prohibited interval, so be clean and zero the repeat_id.
		sql_command("UPDATE AircraftScheduling_entry SET repeat_id=NULL WHERE repeat_id=$repeat_id");
	}
	
	return $removed > 0;
}

//********************************************************************
// AircraftSchedulingCheckStandbyRequest(
//                                       $start_time, 
//                                       $end_time, 
//                                       $resource_id)
//
// Purpose: Check the given time slot to see if it is open. If it is 
//          open and there are standby requests, move the first 
//          standby request into the open slot.
//
// Inputs:
//   start_time - the start time of the request
//   end_time - the end time of the request
//   resource_id - the id of the rosource that we are checking for
//
// Outputs:
//   none
//
// Returns:
//   0        - An error occured
//*********************************************************************
function AircraftSchedulingCheckStandbyRequest(
                                                $start_time, 
                                                $end_time, 
                                                $resource_id)
{
	global $EntryTypeStandby, $EntryTypeNormal, $EntryTypeRepeating, $EntryTypeModified;
	global $AircraftScheduling_company;

	// get a list of all standby requests for this resource from the original
	// start time to the end time
	$sql = "SELECT " .
					"entry_id, " .
					"start_time, " .
					"end_time, " .
					"entry_type, " .
					"repeat_id, " .
					"resource_id, " .
					"create_by, " .
					"name, " .
					"type, " .
					"description, " .
					"phone_number, " .
					"create_time " .
			"FROM AircraftScheduling_entry  " .
			"WHERE  " .
        			"((start_time >= $start_time AND " .
        			"  start_time < $end_time) OR " .
        			" (end_time > $start_time AND " .
        			"  end_time <= $end_time)) AND " .
					"resource_id = $resource_id AND " .
	    			"entry_type = $EntryTypeStandby " .
	    	"ORDER BY create_time";
	$res = sql_query($sql);
	if (!$res) fatal_error(0, sql_error());
	if(sql_count($res) > 0)
	{
		// we have standby entries, see if we can schedule them
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
			// is this time block free?
			$err = AircraftSchedulingCheckFree($row[5], $row[1], $row[2]-1, 0, 0);
			
			// if we did not have any errors, schedule the entry
			if(empty($err))
			{
				// create a new entry and delete the standby atomically
				sql_begin();
				$new_id = AircraftSchedulingCreateSingleEntry($row[1], $row[2], $EntryTypeNormal, $row[4], $row[5],
			                         $row[6], $row[7], $row[8], $row[9], $row[10], $row[11]);

			    // delete the original standby entry
				if ($new_id && sql_command("DELETE FROM AircraftScheduling_entry WHERE entry_id=" . $row[0]) >= 0)
					sql_commit();
				else
					sql_rollback();
				
				// send the user email telling them that they are now scheduled
				$email_address = LookupEmailAddress($row[7]);
				$StartTime = strftime("%A %d %B %Y", $row[1]);
				$Duration = $row[2] - $row[1];
				toTimeString($Duration, $dur_units);
				$ResourceType = AircraftSchedulingGetResourceName($row[5]);
				$ResourceName = sql_query1("SELECT resource_name FROM AircraftScheduling_resource WHERE resource_id = $row[5]");
				$StandbyMessage = 
				      "The standby request you made for $StartTime\n" .
				      "has been released. You are now scheduled for $ResourceType $ResourceName\n" .
				      "on $StartTime for $Duration $dur_units.\n" . 
				      "at $AircraftScheduling_company\n";
				AircraftSchedulingMail($email_address, "Aircraft Standby Request", $StandbyMessage);

				// create a journal entry
				$Description = 
							"Standby request assigned for " . $ResourceType . " " . $ResourceName . " " .
							" to user " . $row[7] .
							" at " .
							date("H:i", $row[1]) . " - " . date("H:i", $row[2]) . 
							" on " . strftime('%m/%d/%y', $row[2]);
				CreateJournalEntry(strtotime("now"), getUserName(), $Description);
			}
		}
	}
	return 1;
}

//********************************************************************
// AircraftSchedulingCreateSingleEntry(
//                                     $starttime, 
//                                     $endtime, 
//                                     $entry_type, 
//                                     $repeat_id, 
//                                     $resource_id,
//                                     $owner, 
//                                     $name, 
//                                     $type, 
//                                     $description, 
//                                     $PhoneNumber, 
//                                     $create_time)
//
// Purpose: Create a single (non-repeating) entry in the database.
//
// Inputs:
//   starttime   - Start time of entry
//   endtime     - End time of entry
//   entry_type  - Entry type
//   repeat_id   - Repeat ID
//   resource_id     - Room ID
//   owner       - Owner
//   name        - Name
//   type        - Type (Internal/External)
//   description - Description
//   PhoneNumber - Phone number of the person making the booking
//   create_time - Time stamp for the create time of this entry
//
// Outputs:
//   none
//
// Returns:
//   0        - An error occured while inserting the entry
//   non-zero - The entry's ID
//*********************************************************************
function AircraftSchedulingCreateSingleEntry(
                                             $starttime, 
                                             $endtime, 
                                             $entry_type, 
                                             $repeat_id, 
                                             $resource_id,
                                             $owner, 
                                             $name, 
                                             $type, 
                                             $description, 
                                             $PhoneNumber, 
                                             $create_time)
{
	$name        = slashes($name);
	$description = slashes($description);
	$repeat_id_sql = ($repeat_id ? $repeat_id : "NULL");
	$sql = "INSERT INTO AircraftScheduling_entry ( " .
						"start_time, " .
						"end_time, " .
						"entry_type," .
						"repeat_id, " .
						"resource_id, " .
						"create_by, " .
						"name, " .
						"type, " .
						"description, " .
						"create_time, " .
						"phone_number " .
						") " .
	                "VALUES ( " .
	                	"$starttime, " .
	                	"$endtime, " .
	                	"$entry_type, " .
	                	"$repeat_id_sql, " .
	                	"$resource_id," .
	                    "'" . addescapes($owner) . "', " .
	                    "'" . addescapes($name) . "', " .
	                    "'" . addescapes($type) . "',  " .
	                    "'" . addescapes($description) . "', " .
	                    "$create_time, " .
	                    "'" . addescapes($PhoneNumber) . "')";
	
	if (sql_command($sql) < 0) 
	{
		echo "SQL error in AircraftSchedulingCreateSingleEntry";
		return 0;
	}
	
	return sql_insert_id("AircraftScheduling_entry", "entry_id");
}

//********************************************************************
// AircraftSchedulingCreateRepeatEntry(
//                                     $starttime, 
//                                     $endtime, 
//                                     $rep_type, 
//                                     $rep_enddate, 
//                                     $rep_opt,
//                                     $resource_id, 
//                                     $owner, 
//                                     $name, 
//                                     $type, 
//                                     $description, 
//                                     $PhoneNumber)
//
// Purpose: Creates a repeat entry in the data base.
//
// Inputs:
//   starttime   - Start time of entry
//   endtime     - End time of entry
//   rep_type    - The repeat type
//   rep_enddate - When the repeating ends
//   rep_opt     - Any options associated with the entry
//   resource_id     - Room ID
//   owner       - Owner
//   name        - Name
//   type        - Type (Internal/External)
//   description - Description
//   PhoneNumber - Phone number of the person making the booking
//
// Outputs:
//   none
//
// Returns:
//   0        - An error occured while inserting the entry
//   non-zero - The entry's ID
//*********************************************************************
function AircraftSchedulingCreateRepeatEntry(
                                             $starttime, 
                                             $endtime, 
                                             $rep_type, 
                                             $rep_enddate, 
                                             $rep_opt,
                                             $resource_id, 
                                             $owner, 
                                             $name, 
                                             $type, 
                                             $description, 
                                             $PhoneNumber)
{
	$name        = slashes($name);
	$description = slashes($description);
	
	$sql = "INSERT INTO AircraftScheduling_repeat (  start_time,   end_time,    rep_type,    end_date,       rep_opt, " . 
	                                   "resource_id, create_by, type, name, description, phone_number) " .
	                         "VALUES ($starttime, $endtime,  $rep_type, $rep_enddate, '$rep_opt', " .
	                                 "$resource_id,   '" . 
	                                 addescapes($owner) . "',    '" . 
	                                 addescapes($type) . "', '" .
	                                 addescapes($name) . "', '" . 
	                                 addescapes($description) . "', '" . 
	                                 addescapes($PhoneNumber) . "')";
	
	if (sql_command($sql) < 0)
	{
		echo "SQL error in AircraftSchedulingCreateRepeatEntry";
		return 0;
	}
	
	return sql_insert_id("AircraftScheduling_repeat", "repeat_id");
}


//********************************************************************
// same_day_next_month($time)
//
// Purpose: Return the number of days to step forward for a 
//          "monthly repeat, corresponding day" series - same week number
//          and day of week next month. This function always returns 
//          either 28 or 35. For dates after the 28th day of a month, 
//          the results are undefined.
//
// Inputs:
//   time- start time of entry
//
// Outputs:
//   none
//
// Returns:
//   35  - days for a normal month
//   28  - days for a short month
//*********************************************************************
function same_day_next_month($time)
{
	$days_in_month = date("t", $time);
	$day = date("d", $time);
	$weeknumber = (int)(($day - 1) / 7) + 1;
	if ($day + 7 * (5 - $weeknumber) <= $days_in_month) return 35;
	else return 28;
}

//********************************************************************
// AircraftSchedulingGetRepeatEntryList(
//                                      $time, 
//                                      $enddate, 
//                                      $rep_type, 
//                                      $rep_opt, 
//                                      $max_ittr)
//
// Purpose: Returns a list of the repeating entrys.
//
// Inputs:
//   time     - The start time
//   enddate  - When the repeat ends
//   rep_type - What type of repeat is it
//   rep_opt  - The repeat entrys
//   max_ittr - After going through this many entrys assume an error has occured
//
// Outputs:
//   none
//
// Returns:
//   empty     - The entry does not repeat
//   an array  - This is a list of start times of each of the repeat entrys
//*********************************************************************
function AircraftSchedulingGetRepeatEntryList(
                                              $time, 
                                              $enddate, 
                                              $rep_type, 
                                              $rep_opt, 
                                              $max_ittr)
{
	$sec   = date("s", $time);
	$min   = date("i", $time);
	$hour  = date("G", $time);
	$day   = date("d", $time);
	$month = date("m", $time);
	$year  = date("Y", $time);
	
	$entrys = array(0 => 0);
	for($i = 0; $i < $max_ittr; $i++)
	{
		$time = mktime($hour, $min, $sec, $month, $day, $year);
		if ($time > $enddate)
			break;

		$entrys[$i] = $time;
		
		switch($rep_type)
		{
			// Daily repeat
			case 1:
				$day += 1;
				break;
			
			// Weekly repeat
			case 2:
				$j = $cur_day = date("w", $entrys[$i]);
				// Skip over days of the week which are not enabled:
				while (($j = ($j + 1) % 7) != $cur_day && !$rep_opt[$j])
					$day += 1;

				$day += 1;
				break;
			
			// Monthly repeat
			case 3:
				$month += 1;
				break;
			
			// Yearly repeat
			case 4:
				$year += 1;
				break;
			
			// Monthly repeat on same week number and day of week
			case 5:
				$day += same_day_next_month($time);
				break;
				
			// Unknown repeat option
			default:
				return;
		}
	}
	
	return $entrys;
}

//********************************************************************
// AircraftSchedulingCreateRepeatingEntrys(
//                                         $starttime, 
//                                         $endtime, 
//                                         $rep_type, 
//                                         $rep_enddate, 
//                                         $rep_opt,
//                                         $resource_id, 
//                                         $owner, 
//                                         $name, 
//                                         $type, 
//                                         $description, 
//                                         $PhoneNumber, 
//                                         $create_time)
//
// Purpose: Creates a repeat entry in the data base + all the repeating 
//          entrys.
//
// Inputs:
//   time     - The start time
//   starttime   - Start time of entry
//   endtime     - End time of entry
//   rep_type    - The repeat type
//   rep_enddate - When the repeating ends
//   rep_opt     - Any options associated with the entry
//   resource_id     - Room ID
//   owner       - Owner
//   name        - Name
//   type        - Type (Internal/External)
//   description - Description
//   PhoneNumber - Phone number of the person making the booking
//   create_time - Time stamp for the create time of this entry
//
// Outputs:
//   none
//
// Returns:
//   0        - An error occured while inserting the entry
//   non-zero - The entry's ID
//*********************************************************************
function AircraftSchedulingCreateRepeatingEntrys(
                                                $starttime, 
                                                $endtime, 
                                                $rep_type, 
                                                $rep_enddate, 
                                                $rep_opt,
                                                $resource_id, 
                                                $owner, 
                                                $name, 
                                                $type, 
                                                $description, 
                                                $PhoneNumber, 
                                                $create_time)
{
	global $max_rep_entrys;
	
	$reps = AircraftSchedulingGetRepeatEntryList(
	                                             $starttime, 
	                                             $rep_enddate, 
	                                             $rep_type, 
	                                             $rep_opt, 
	                                             $max_rep_entrys);

	// if there aren't any repeats, create just one entry
	if(empty($reps))
	{
		AircraftSchedulingCreateSingleEntry(
		                                    $starttime, 
		                                    $endtime,
		                                    0, 
		                                    0, 
		                                    $resource_id, 
		                                    $owner, 
		                                    $name, 
		                                    $type, 
		                                    $description, 
		                                    $PhoneNumber, 
		                                    $create_time);
		return;
	}
	
	// if we are trying to create too many entries, return an error
	if(count($reps) > $max_rep_entrys)
		return 0;
		
	// create repeating entries inside a transaction for atomicity
	sql_begin();
	$ent = AircraftSchedulingCreateRepeatEntry(
	                                           $starttime,
	                                           $endtime,
	                                           $rep_type,
	                                           $rep_enddate,
	                                           $rep_opt,
	                                           $resource_id,
	                                           $owner,
	                                           $name,
	                                           $type,
	                                           $description,
	                                           $PhoneNumber);
	if($ent)
	{
		$diff = $endtime - $starttime;

		for($i = 0; $i < count($reps); $i++)
		{
			if(!AircraftSchedulingCreateSingleEntry(
			                                    $reps[$i],
			                                    $reps[$i] + $diff,
			                                    1,
			                                    $ent,
				                                $resource_id,
				                                $owner,
				                                $name,
				                                $type,
				                                $description,
				                                $PhoneNumber,
				                                $create_time))
			{
				sql_rollback();
				return 0;
			}
		}
	}
	else
	{
		sql_rollback();
		return 0;
	}
	sql_commit();
	return $ent;
}

//********************************************************************
// AircraftSchedulingGetEntryInfo($id)
//
// Purpose: Get the booking's entrys.
//
// Inputs:
//   id = The ID for which to get the info for.
//
// Outputs:
//   none
//
// Returns:
//   nothing = The ID does not exist
//   array   = The bookings info
//*********************************************************************
function AircraftSchedulingGetEntryInfo($id)
{
	$sql = "SELECT start_time, end_time, entry_type, repeat_id, resource_id,
	               timestamp, create_by, name, type, description, create_time 
                FROM AircraftScheduling_entry WHERE (entry_id = $id)";
	
	$res = sql_query($sql);
	if (! $res) return;
	
	if(sql_count($res) > 0)
	{
		$row = sql_row($res, 0);
		
		$ret = [
		         'start_time' => $row[0],
		         'end_time'    => $row[1],
		         'entry_type'  => $row[2],
		         'repeat_id'   => $row[3],
		         'resource_id' => $row[4],
		         'timestamp'   => $row[5],
		         'create_by'   => $row[6],
		         'name'        => $row[7],
		         'type'        => $row[8],
		         'description' => $row[9],
		         'create_time' => $row[10]
		];
	}
	sql_free($res);
	
	return $ret;
}

//********************************************************************
// AircraftSchedulingGetResourceName($resource_id)
//
// Purpose: Return the resource name given the id.
//
// Inputs:
//   resource_id - the ID for which to get the info for.
//
// Outputs:
//   none
//
// Returns:
//   id - the name of the resource from the database or -1 if not found.
//*********************************************************************
function AircraftSchedulingGetResourceName($resource_id)
{
	$id = sql_query1(
	                "SELECT a.name " .
	                "FROM AircraftScheduling_schedulable a, AircraftScheduling_resource b " .
	                "WHERE b.resource_id=$resource_id AND a.schedulable_id=b.schedulable_id");
	return $id;
}

?>
