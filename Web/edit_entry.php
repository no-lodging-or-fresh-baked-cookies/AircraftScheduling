<?php
//-----------------------------------------------------------------------------
// 
// edit_entry.php
// 
// PURPOSE: Edits a scheduled entry.
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
    if(isset($rdata["InstructorEntryID"])) $InstructorEntryID = $rdata["InstructorEntryID"];
    if(isset($rdata["InstructorName"])) $InstructorName = $rdata["InstructorName"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["edit_type"])) $edit_type = $rdata["edit_type"];
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
    if(!isset($edit_type))
    	$edit_type = "";
    
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
    
    // can we modify this item?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
        showAccessDenied(
                         $day, $month, $year, 
                         $resource, 
                         $resource_id, 
                         $makemodel, 
                         $goback, 
                         "&hour=$hour&minute=$minute", 
                         $InstructorResource);
    	exit;
    }
    
    // This page will either add or modify a booking
    // We need to know:
    //  Name of booker
    //  Phone Number
    //  Description (optional)
    //  Date (option select box for day, month, year)
    //  Time
    //  Duration
    //  Internal/External
    
    // Firstly we need to know if this is a new booking or modifying an old one
    // and if it's a modification we need to get all the old data from the database.
    // If we had $id passed in then it's a modification.
    if (isset($id))
    {
    	$sql = "SELECT " .
    	            "name,  " .
    	            "create_by,  " .
    	            "description,  " .
    	            "start_time,  " .
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
    	if (sql_count($res) != 1) fatal_error(1, "Entry ID $id not found");
    	
    	$row = sql_row($res, 0);
    	sql_free($res);
    	
        // Note: Removed stripslashes() calls from name and description. Previous
        // versions of AircraftScheduling mistakenly had the backslash-escapes in the actual database
        // records because of an extra addslashes going on. Fix your database and
        // leave this code alone, please.
    	$NameOfUser  = $row[0];
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
    	
    	if($entry_type >= 1)
    	{
    		$sql = "SELECT rep_type, start_time, end_date, rep_opt
    		        FROM AircraftScheduling_repeat WHERE repeat_id=$rep_id";
    		
    		$res = sql_query($sql);
    		if (! $res) fatal_error(1, sql_error());
    		if (sql_count($res) != 1) fatal_error(1, "Repeat ID $rep_id not found");
    		
    		$row = sql_row($res, 0);
    		sql_free($res);
    		
    		$rep_type = $row[0];
    		
    		if($edit_type == "series")
    		{
    			$start_day   = (int)strftime('%d', $row[1]);
    			$start_month = (int)strftime('%m', $row[1]);
    			$start_year  = (int)strftime('%Y', $row[1]);
    			
    			$rep_end_day   = (int)strftime('%d', $row[2]);
    			$rep_end_month = (int)strftime('%m', $row[2]);
    			$rep_end_year  = (int)strftime('%Y', $row[2]);
    			
    			switch($rep_type)
    			{
    				case 2:
    					$rep_day[0] = $row[3][0] != "0";
    					$rep_day[1] = $row[3][1] != "0";
    					$rep_day[2] = $row[3][2] != "0";
    					$rep_day[3] = $row[3][3] != "0";
    					$rep_day[4] = $row[3][4] != "0";
    					$rep_day[5] = $row[3][5] != "0";
    					$rep_day[6] = $row[3][6] != "0";
    					
    					break;
    				
    				default:
    					$rep_day = array(0, 0, 0, 0, 0, 0, 0);
    			}
    		}
    		else
    		{
    			$rep_type     = $row[0];
    			$rep_end_date = strftime('%A %d %B %Y',$row[2]);
    			$rep_opt      = $row[3];
    		}
    	}
    }
    else
    {
    	// It is a new booking. The data comes from whichever button the user clicked
    	$edit_type   = "series";
    	$NameOfUser        = getName();
    	$create_by   = getUserName();
    	$description = "";
    	$start_day   = $day;
    	$start_month = $month;
    	$start_year  = $year;
    	$start_hour  = $hour;
    	$start_min   = $minute;
    	$duration    = 60 * 60 * 2;			// number of hours to book in seconds
    	$type        = "I";
        $phone_number= FormatPhoneNumber(getPhoneNumber(), getPhoneNumber2());
    	
    	$rep_id        = 0;
    	$rep_type      = 0;
    	$rep_end_day   = $day;
    	$rep_end_month = $month;
    	$rep_end_year  = $year;
    	$rep_day       = array(0, 0, 0, 0, 0, 0, 0);
    }
    
    // get the schedule resource type
    $ScheduleType = sql_query1("
    							SELECT a.name 
    							FROM 
    								AircraftScheduling_schedulable a, 
    								AircraftScheduling_resource b 
    							WHERE 
    								b.resource_id=$resource_id AND 
    								b.schedulable_id=a.schedulable_id"); 
    
    // get the name of the item we are scheduling
    if($ScheduleType == "Aircraft") 
    {
    	$ScheduleName = sql_query1(
    	                        "SELECT n_number 
    	                         FROM AircraftScheduling_aircraft 
    	                         WHERE resource_id=$resource_id");
    }
    else if($ScheduleType == "Instructor") 
    {
    	$PersonID = sql_query1(
    	                        "SELECT person_id 
    	                         FROM AircraftScheduling_instructors 
    	                         WHERE resource_id=$resource_id");
    	$ScheduleName = sql_query1(
    	                        "SELECT $DatabaseNameFormat 
    	                         FROM AircraftScheduling_person 
    	                         WHERE person_id=$PersonID");
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
    
    echo "<h2>";
    echo isset($id) ? $lang["editentry"] . " - $ScheduleName" : $lang["addentry"] . " - $ScheduleName";
    echo "</h2>";

    echo "<FORM NAME='main' ACTION='edit_entry_handler.php' METHOD='GET'>";
    echo "<TABLE BORDER=0>";

	// if the user is a normal user, put in the user name as the booker
	if (authGetUserLevel(getUserName(), $auth["admin"]) <= $UserLevelNormal)
	{
		echo "<tr><td class=cr><b>" . $lang["namebooker"] . "</b></td>";
  		echo "<td class=cl>" . htmlentities($NameOfUser) . "</td></tr>";
		echo "<input type=hidden name='NameOfUser' value=\"" . $NameOfUser . "\">";

		echo "<tr><td class=cr><b>" . $lang["phonenumber"] . "</b></td>";
		echo "<td class=cl>";
		echo "<input name='phone_number' id='phone_number' size=25 value=\"" . htmlentities(stripslashes($phone_number)) . "\">";
		echo "</td></tr>";
  	}
  	else
  	{
  		// admin or super user
		echo "<tr><td class=cr><b>" . $lang["namebooker"] . "</b></td>";

		// build the selection entries
		echo "<td class=cl>";
        BuildMemberSelector(
                            $NameOfUser, 
                            false, 
                            "",
                            20,
                            true,
                            false,
                            "SelectPhonenumber");
  		echo "</TD></TR>";

		$SQLNameSelect = "SELECT  " .
            					"phone_number, " .
            					"Allow_Phone_Number_Display, " .
            					"Home_Phone " . 
            		      "FROM AircraftScheduling_person " . 
            		      "WHERE user_level != $UserLevelDisabled " .
                          "ORDER by last_name";
		
		echo "<td class=cl>";
		$NameSelectResult = sql_query($SQLNameSelect);
		if(0 != ($row = sql_row_keyed($NameSelectResult, 0))) 
		{	
			// setup script to select phone number when the name changes
			echo "<script language='JavaScript'>";
			echo "function SelectPhonenumber()";
			echo "{";
			echo "	var PhoneNumbers = new Array;";

			// build the phone number array
			for($i=0; $row = sql_row($NameSelectResult, $i); $i++) 
			{
			    // if the user is allowing phone number display, show the phone
			    // number
				if ($row[1] == 1)
				    echo " PhoneNumbers[$i] = \"" . FormatPhoneNumber($row[0], $row[2]) . "\";";
				else
				    echo " PhoneNumbers[$i] = \"" . "UNLISTED" . "\";";
			}
				
			echo "	document.forms['main'].phone_number.value = PhoneNumbers[document.forms['main'].NameOfUser.selectedIndex];";
			echo "}";
			echo "</script>";
		}
		
		// display the phone number field
		echo "<tr><td class=cr><b>" . $lang["phonenumber"] . "</b></td>";
		echo "<td class=cl>";
		echo "<input name='phone_number' SIZE=25 value=\"" . htmlentities(stripslashes($phone_number)) . "\">";
		echo "</TD></TR>";
  	}
    
    // description
    echo "<tr><td class=tr><b>";
    echo $lang["fulldescription"];
    echo "</b></td>";
    echo "<TD CLASS=TL><TEXTAREA NAME='description' ROWS=8 COLS=40 WRAP='virtual'>";
    echo htmlentities ( $description );
    echo "</TEXTAREA></TD></TR>";

    // put up a list of instructors that the user may book at the same time
    // if we are not schedling an instructor
    if($ScheduleType == "Aircraft") 
    {
    	echo "<TR><TD CLASS=CR><B>" . $lang["nameinstructor"] . "</B></TD>";	
    
    	// echo a "none" option as the default if we don't have an instructor
    	// already set
    	if (strlen($InstructorName) == 0) $InstructorName = "None";
    
    	// build the selection entries
    	echo "<TD CLASS=CL>";
        BuildInstructorSelector(
                            $InstructorName, 
                            true, 
                            "InstructorName",
                            20,
                            true,
                            false,
                            "DisplayCurrency",
                            false);
    	echo "</TD></TR>";
    }
    
    // date of booking
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
    echo "<TD CLASS=CL>";
    echo "<INPUT NAME='hour' SIZE=2 value=\"$start_hour\" MAXLENGTH=2>" . ":" . 
         "<INPUT NAME='minute' SIZE=2 value=\"$start_min\" MAXLENGTH=2>";
    echo "</TD></TR>";
    
    // duration
    echo "<TR><TD CLASS=CR><B>" . $lang["duration"] . "</B></TD>";
    echo "<TD CLASS=CL><INPUT NAME='duration' SIZE=7 value=\"$duration\">";
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

    // repeat types
    if($edit_type == "series") 
    { 
        // repeat type is series
        echo "<TR>";
        echo "<TD CLASS=CR><B>";
        echo $lang["rep_type"];
        echo "</B></TD>";
        echo "<TD CLASS=CL>";
        
        for($i = 0; isset($lang["rep_type_$i"]); $i++)
        {
        	echo "<INPUT NAME='rep_type' TYPE='RADIO' VALUE='" . $i . "'";
        	
        	if($i == $rep_type)
        		echo " CHECKED";
        	
        	echo ">" . $lang["rep_type_$i"] . "\n";
        }
    
        echo "</TD>";
        echo "</TR>";
    
        echo "<TR>";
        echo "<TD CLASS=CR><B>";
        echo $lang["rep_end_date"];
        echo "</B></TD>";
        echo "<TD CLASS=CL>";
        genDateSelector("rep_end_", "main", $rep_end_day, $rep_end_month, $rep_end_year);
        echo "</TD>";
        echo "</TR>";
    
        echo "<TR>";
        echo "<TD CLASS=CR><B>";
        echo $lang["rep_rep_day"];
        echo "</B>";
        echo $lang["rep_for_weekly"];
        echo "</TD>";
        echo "<TD CLASS=CL>";

        // Display day name checkboxes according to language and preferred weekday start.
        for ($i = 0; $i < 7; $i++)
        {
        	$wday = ($i + $weekstarts) % 7;
        	echo "<INPUT NAME='rep_day[$wday]' TYPE=CHECKBOX";
        	if ($rep_day[$wday]) echo " CHECKED";
        	echo ">" . day_name($wday) . "\n";
        }
        echo "</TD>";
        echo "</TR>";
    
    }
    else
    {
    	$key = "rep_type_" . (isset($rep_type) ? $rep_type : "0");
    	
    	echo "<tr><td class='CR'><b>$lang[rep_type]</b></td><td class='CL'>$lang[$key]</td></tr>\n";
    	
    	if(isset($rep_type) && ($rep_type != 0))
    	{
    		$opt = "";
    		if ($rep_type == 2)
    		{
    			// Display day names according to language and preferred weekday start.
    			for ($i = 0; $i < 7; $i++)
    			{
    				$wday = ($i + $weekstarts) % 7;
    				if ($rep_opt[$wday]) $opt .= day_name($wday) . " ";
    			}
    		}
    		if($opt)
    			echo "<tr><td class='CR'><b>$lang[rep_rep_day]</b></td><td class='CL'>$opt</td></tr>\n";
    		
    		echo "<tr><td class='CR'><b>$lang[rep_end_date]</b></td><td class='CL'>$rep_end_date</td></tr>\n";
    	}
    }
    
    // generate the save button
    echo "<TR>";
    echo "<TD colspan=2 align=center>";
    echo "<input name='EditEntry' type=submit value='" . $lang["save"] . "' ONCLICK='return ValidateAndSubmit()'>";
    echo "</TD></TR>";
    echo "</TABLE>";
    
    // save the input data
    echo "<INPUT TYPE=HIDDEN NAME='resource_id' VALUE='$resource_id'>";
    echo "<INPUT TYPE=HIDDEN NAME='resource' value=\"$resource\">";
    echo "<INPUT TYPE=HIDDEN NAME='InstructorResource' value=\"$InstructorResource\">";
    echo "<INPUT TYPE=HIDDEN NAME='make' value=\"$make\">";
    echo "<INPUT TYPE=HIDDEN NAME='model' value=\"$model\">";
    echo "<INPUT TYPE=HIDDEN NAME='create_by' value=\"$create_by\">";
    echo "<INPUT TYPE=HIDDEN NAME='rep_id' value=\"$rep_id\">";
    echo "<INPUT TYPE=HIDDEN NAME='edit_type' value=\"$edit_type\">";
    echo "<INPUT TYPE=HIDDEN NAME='ScheduleName' value=\"$ScheduleName\">";
    if(isset($id)) 
        echo "<INPUT TYPE=HIDDEN NAME='id' VALUE='$id'>\n";
    if(isset($InstructorEntryID)) 
        echo "<INPUT TYPE=HIDDEN NAME='InstructorEntryID' VALUE='$InstructorEntryID'>\n";
    if(isset($goback)) 
        echo "<INPUT NAME='goback' TYPE='HIDDEN' value=\"$goback\">\n";
    if(!empty($GoBackParameters)) 
        echo "<INPUT NAME=\"GoBackParameters\" TYPE='HIDDEN' VALUE=\"$GoBackParameters\">\n";
    
    // save the variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    if(getAuthorised(getUserName(), getUserPassword(), $UserLevelSuper))
    {
        // privileged users are not restricted on the number of days to schedule
        echo "var Number_Repeat_Days = 0;";
        echo "var MaxScheduleDaysAllowed = 0;";
    }
    else
    {
        // non privileged are restricted if the administrator set a limit
        echo "var Number_Repeat_Days = $Number_Repeat_Days;";
        echo "var MaxScheduleDaysAllowed = $MaxScheduleDaysAllowed;";
    }
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
    // check for a valid name selected
    if(document.forms['main'].NameOfUser.value == "")
    {
        alert ( "You have not entered a name for the schedule." );
        return false;
    }
    
    // check for a valid phone number selected
    if(document.forms['main'].phone_number.value == "")
    {
        alert ( "You have not entered a phone number for the schedule." );
        return false;
    }

    // get the current date
    var CurrentDate = new Date();       
    
    // is a repeat type selected (other than none)
    if (document.forms['main'].rep_type != null)
    {
        if(!document.forms['main'].rep_type[0].checked)
        {    
            // are the number of repeat days to schedule limited?
            if (Number_Repeat_Days > 0)
            {
                var LastRepeatDateAllowed = new Date(
                                                     CurrentDate.getFullYear(), 
                                                     CurrentDate.getMonth(), 
                                                     CurrentDate.getDate() + Number_Repeat_Days);
                var EndRepeatDate = new Date(
                                     parseInt(document.main.rep_end_year[document.main.rep_end_year.selectedIndex].value), 
                                     parseInt(document.main.rep_end_month[document.main.rep_end_month.selectedIndex].value) - 1, 
                                     parseInt(document.main.rep_end_day[document.main.rep_end_day.selectedIndex].value));
                            
                // see if the user requested more repeat days than allowed
                if (EndRepeatDate.getTime() > LastRepeatDateAllowed.getTime())
                {
                    // attempting to schedule too many repeat days, slap them down
                    alert ( "You have tried to schedule more repeat days than is allowed.\n" +  
                                "No more than " + Number_Repeat_Days + 
                                " days from today are allowed." );
                    return false;
                }
            }
        }
    }
    
    // are the number of days to schedule limited?
    if (MaxScheduleDaysAllowed > 0)
    {
        // if the user is trying to schedule too far in the future
        var LastScheduleDateAllowed = new Date(
                                             CurrentDate.getFullYear(), 
                                             CurrentDate.getMonth(), 
                                             CurrentDate.getDate() + MaxScheduleDaysAllowed);
        var ScheduleDate = new Date(
                             parseInt(document.main.year[document.main.year.selectedIndex].value), 
                             parseInt(document.main.month[document.main.month.selectedIndex].value) - 1, 
                             parseInt(document.main.day[document.main.day.selectedIndex].value));
        if (ScheduleDate.getTime() > LastScheduleDateAllowed.getTime())
        {
            // attempting to schedule too many days out, slap them down
            alert ( "You have tried to schedule too far into the future.\n" +  
                        "No more than " + MaxScheduleDaysAllowed + 
                        " days from today are allowed." );
            return false;
        }
    }
    
    // check the time of day
    h = parseInt(document.forms["main"].hour.value, 10);
    m = parseInt(document.forms["main"].minute.value, 10);    
    if(h > 23 || m > 59)
    {
        alert("You have not entered a\nvalid time of day.");
        return false;
    }
    
    // no errors found, return
	return true;
}
</SCRIPT>
