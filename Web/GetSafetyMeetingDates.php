<?php
//-----------------------------------------------------------------------------
// 
// GetSafetyMeetingDates.php
// 
// PURPOSE: Display and update the safety meeting dates.
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
//      UpdateData - set to Update to update the config information
//      CancelData - set to Cancel to cancel the updates
//      Lastday - day of last safety meeting
//      Lastmonth - month of last safety meeting
//      Lastyear - year of last safety meeting
//      Nextday - day of next safety meeting
//      Nextmonth - month of next safety meeting
//      Nextyear - year of next safety meeting
//      order_by - parameter to sort the display by
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
    include "AircraftScheduling_auth.inc";
    include "$dbsys.inc";
    include "functions.inc";
    require_once("CurrencyFunctions.inc");
    include "DatabaseConstants.inc";

    // initialize variables
    $order_by = "last_name";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["UpdateData"])) $UpdateData = $rdata["UpdateData"];
    if(isset($rdata["CancelData"])) $CancelData = $rdata["CancelData"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["Lastday"])) $Lastday = $rdata["Lastday"];
    if(isset($rdata["Lastmonth"])) $Lastmonth = $rdata["Lastmonth"];
    if(isset($rdata["Lastyear"])) $Lastyear = $rdata["Lastyear"];
    if(isset($rdata["Nextday"])) $Nextday = $rdata["Nextday"];
    if(isset($rdata["Nextmonth"])) $Nextmonth = $rdata["Nextmonth"];
    if(isset($rdata["Nextyear"])) $Nextyear = $rdata["Nextyear"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // SetUsersSafetyMeeting($SafetyMeetingDate)
    //
    // Purpose: Set the selected members safety meeting date. The input
    //          parameters are processed to determine the users that
    //          should be updated.
    //
    // Inputs:
    //   SafetyMeetingDate - date of the safety meeting for updating the
    //                       users.
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function SetUsersSafetyMeeting($SafetyMeetingDate)
    {
        global $rdata;
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        
        // process the seleted users. the checkboxes are the usernames for 
        // the members so any valid usernames within the script input
        // parameters will be set
        $InputNames = array_keys($rdata);
        $InputParameters = array_values($rdata);
    	for ($i = 0; $i < count($InputNames); $i++)
    	{
    		// is this a checkbox parameter?
    		if ($InputParameters[$i] == "on")
    		{
    		    // yes, set the safety meeting date for the username
    		    LoadDBCurrencyFields($InputNames[$i]);
                UpdateCurrencyFieldname("Safety_Meeting", $SafetyMeetingDate);
                SaveDBCurrencyFields($RulesField);
                
                // update the database record
                $DatabaseFields = array();
                SetDatabaseRecord("Rules_Field", $RulesField, $RSConversionString, $DatabaseFields[0]);
                UpdateDatabaseRecord(
                                    "AircraftScheduling_person",
                                    $DatabaseFields,
                                    "username='" . UCase(Trim($InputNames[$i])) . "'");
            	$Description = 
            				"Updating safety meeting for user " . $InputNames[$i];
            	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    		}
    	}
    }
    
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################

    # if we dont know the right date then make it up 
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
    
    // if the make and model is not set, set the default
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else { $all=1; $makemodel = "&all=1"; }
    
    // if the resource is not set, get the default
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
    
    // are we authorized for this operation?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // if we are updating the data, save it and return to previous screen
    if ($UpdateData == "Update")
    {
        SetDatabaseRecord("Next_Safety_Meeting", 
                                FormatField(BuildDate($Nextday, $Nextmonth, $Nextyear), "DatabaseDate"), 
                                $RSConversionDate, $DatabaseFields[0]);
        SetDatabaseRecord("Last_Safety_Meeting",                                 
                                FormatField(BuildDate($Lastday, $Lastmonth, $Lastyear), "DatabaseDate"),
                                $RSConversionDate, $DatabaseFields[1]);
        
        // compute the number of days from the next meeting to the last so that
        // the currency checks will expire at the right time
        $NumberOfDays = FormatField(DateValue(BuildDate($Nextday, $Nextmonth, $Nextyear)) - 
                            DateValue(BuildDate($Lastday, $Lastmonth, $Lastyear)), "Integer");
        SetDatabaseRecord("Safety_Meeting_Expiration_Days", $NumberOfDays . "d",
                            $RSConversionString, $DatabaseFields[2]);
        
        // update the safety meeting information
        UpdateDatabaseRecord("Safety_Meeting", $DatabaseFields, "1");

        // log the change in the journal
        $Description = 
                        "Last safety meeting set to " . 
                                FormatField(BuildDate($Lastday, $Lastmonth, $Lastyear), "Date") .
                        " next safety meeting set to " . 
                                FormatField(BuildDate($Nextday, $Nextmonth, $Nextyear), "Date");
     	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        
        // update the user safety meeting dates
        SetUsersSafetyMeeting(FormatField(BuildDate($Lastday, $Lastmonth, $Lastyear), "DatabaseDate"));
   		
    	// updates complete, return to admin pages
    	session_write_close();
    	header("Location: admin.php");
    	exit;
    }
    
    // if we are updating the data, save it and return to previous screen
    else if ($CancelData == "Cancel")
    {
    	// updates canceled, return to admin pages
    	session_write_close();
    	header("Location: admin.php");
    	exit;
    }
    else
    {
        // display the safety meeting information
    	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");

	    // display the safety date information
		echo "<center>";
        echo "<h2>Get Safety Meeting Dates</h2>";
		echo "<form name='GetSafetyMeetingDates' action='" . getenv('SCRIPT_NAME') . "' method='post'>";
		echo "<table border=0>\n";
		
		// last safety meeting
        $LastSafetyMeetingExpiration = GetSafetyMeetingExpiration("Last_Safety_Meeting");
    	$Lastday = Day($LastSafetyMeetingExpiration);
    	$Lastmonth = Month($LastSafetyMeetingExpiration);
    	$Lastyear = Year($LastSafetyMeetingExpiration);
		echo "<tr>";
		echo "<td>Date of the last safety meeting:</td>";
		echo "<td>";			
        genDateSelector("Last", "GetSafetyMeetingDates", $Lastday, $Lastmonth, $Lastyear, "", "");
		echo "</td>";			
		echo "</tr>";
		
		// next safety meeting
        $NextSafetyMeetingExpiration = GetSafetyMeetingExpiration("Next_Safety_Meeting");
    	$Nextday = Day($NextSafetyMeetingExpiration);
    	$Nextmonth = Month($NextSafetyMeetingExpiration);
    	$Nextyear = Year($NextSafetyMeetingExpiration);
		echo "<tr>";
		echo "<td>Date of the next safety meeting:</td>";
		echo "<td>";			
        genDateSelector("Next", "GetSafetyMeetingDates", $Nextday, $Nextmonth, $Nextyear, "", "");
		echo "</td>";			
		echo "</tr>";
		
		echo "</table>";
		
		// get the member information from the database
        $MembersResult =
                SQLOpenRecordset(
                                 "SELECT * FROM AircraftScheduling_person " . 
                                 "WHERE " .
                                    "INSTR(Rules_Field, 'Member_Status,$MemberStatusInActive') OR " . 
                                    "INSTR(Rules_Field, 'Member_Status,$MemberStatusActive') " . 
                                 "ORDER BY $order_by");
		        
        echo "<H2>Check the users that attended the last safety meeting</H2>";

        // process the results of the database inquiry
        $UserNamesArray = array();
        $NamesArray = array();
		for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++) 
		{
		    // username column
			$UserNamesArray[$MembersCnt] = $MembersRST[$username_offset];
			
			// name column
			$NamesArray[$MembersCnt] = BuildName($MembersRST[$first_name_offset], $MembersRST[$last_name_offset]);
		}
    
        // start the table
        $NumberOfColumns = 3;
        echo "<table border=0>";
        
        // display the columns and rows
        DisplayNameColumns(
                            count($UserNamesArray), 
                            $NumberOfColumns, 
                            $UserNamesArray, 
                            $NamesArray);

        // complete the table
        echo "</table>";

		// submit and cancel buttons
		echo "<br>";
		echo "<input type=submit value='Update' name='UpdateData'>";
		echo "<input type=submit value='Cancel' name='CancelData' onClick=\"return confirm('" .  
                $lang["CancelSafety"] . "')\">";
		echo "</form>";
		echo "</center>";
    }
    
    include "trailer.inc";

?>