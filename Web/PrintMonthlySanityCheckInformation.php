<?php
//-----------------------------------------------------------------------------
// 
// PrintMonthlySanityCheckInformation.php
// 
// PURPOSE: Print monthly sanity check (suspect billing) information.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      TailNumber - selected aircraft tailnumber
//      all - select all resources
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      PrintMonthlySanityCheckInformation - set to submit to print aircraft information
//      AircraftCancel - set to Cancel to cancel the printing.
//      debug_flag - set to non-zero to enable debug output information
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
    require_once("DateFunctions.inc");
    require_once("CurrencyFunctions.inc");

    // initialize variables
    $all = '';
    $TailNumber = 'All';
	$FromTime = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
	$ToTime = mktime(0, 0, 0, date("m"), 0, date("Y"));
	$Fromday = date("d", $FromTime);
	$Frommonth = date("m", $FromTime);
	$Fromyear = date("Y", $FromTime);
	$Today   = date("d", $ToTime);
	$Tomonth = date("m", $ToTime);
	$Toyear  = date("Y", $ToTime);

    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    if(isset($rdata["PrintMonthlySanityCheckInformation"])) $PrintMonthlySanityCheckInformation = $rdata["PrintMonthlySanityCheckInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["Fromday"])) $Fromday = $rdata["Fromday"];
    if(isset($rdata["Frommonth"])) $Frommonth = $rdata["Frommonth"];
    if(isset($rdata["Fromyear"])) $Fromyear = $rdata["Fromyear"];
    if(isset($rdata["Today"])) $Today = $rdata["Today"];
    if(isset($rdata["Tomonth"])) $Tomonth = $rdata["Tomonth"];
    if(isset($rdata["Toyear"])) $Toyear = $rdata["Toyear"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // PrintPageHeader(
    //                  StartDate as string,
    //                  EndDate as string,
    //                  LineCounter As Integer,
    //                  LeftMargin As Integer,
    //                  MaxLineLength As Integer,
    //                  HeaderLength As Integer)
    //
    // Purpose:  Print the page header
    //
    // Inputs:
    //   StartDate - the start date to print the billing for
    //   EndDate - the end date to print the billing for
    //   LineCounter - counter to determine end of page
    //   LeftMargin - amount of space to skip on left
    //   MaxLineLength - max characters per line
    //   HeaderLength - lines to skip before and after header
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintPageHeader(
                                $StartDate,
                                $EndDate,
                                &$LineCounter,
                                $LeftMargin,
                                $MaxLineLength,
                                $HeaderLength)
    {                                
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;

        // skip some space above the header
        for ($i = 0; $i < $HeaderLength; $i++);
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter = $LineCounter + 1;
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("***** " . $AircraftScheduling_company . " *****", 
                            $CenterJustify, $MaxLineLength) . "<br>");
        $LineCounter = $LineCounter + 1;
        
        // print the month
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("BILLING FILE ERRORS FROM " .
                                UCase(FormatField($StartDate, "Date")) . " TO " .
                                UCase(FormatField($EndDate, "Date")),
                       $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter = $LineCounter + 1;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter = $LineCounter + 1;
        }
    }
    
    //********************************************************************
    // PrintAircraftHeader(
    //                  AircraftID as String,
    //                  LineCounter As Integer,
    //                  LeftMargin As Integer,
    //                  MaxLineLength As Integer,
    //                  HeaderLength As Integer)
    //
    // Purpose:  Print the aircraft header
    //
    // Inputs:
    //   AircraftID - the tail number of the aircraft
    //   LineCounter - counter to determine end of page
    //   LeftMargin - amount of space to skip on left
    //   MaxLineLength - max characters per line
    //   HeaderLength - lines to skip before and after header
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintAircraftHeader(
                                $AircraftID,
                                &$LineCounter,
                                $LeftMargin,
                                $MaxLineLength,
                                $HeaderLength)
    {    
        global $RightJustify, $LeftJustify, $CenterJustify;

        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       "ERRORS IN FLIGHT FILES FOR AIRCRAFT " . UCase(Trim($AircraftID)) 
                       . "<br>");
        $LineCounter = $LineCounter + 1;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter = $LineCounter + 1;
        }
    }
    
    //********************************************************************
    // PrintMonthlySanityCheck(
    //                           StartDate as string,
    //                           EndDate as string)
    //
    // Purpose:  Print the monthly sanity check information for
    //           the given month
    //
    // Inputs:
    //   StartDate - the start date to print the billing for
    //   EndDate - the end date to print the billing for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMonthlySanityCheck($StartDate, $EndDate)
    {                                  
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
              
        include "DatabaseConstants.inc";

        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 4;
        $ColumnWidth1 = 12;
        $ColumnWidth2 = 35;
        $ColumnWidth3 = 6;
        $ColumnWidth4 = 9;
        $ColumnWidth5 = 9;
        $ColumnWidth6 = 10;
        $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 +
                                $ColumnWidth4 + $ColumnWidth5 + $ColumnWidth6 + 5;
    
        // printer setup
        PrinterSetup(9);
        
        // print the page header
        PrintPageHeader(
                        $StartDate,
                        $EndDate,
                        $LineCounter,
                        $LeftMargin,
                        $MaxLineLength,
                        $HeaderLength);
        
        // Open a recordset for all the aircraft in the database
    	$sql = "SELECT * FROM AircraftScheduling_aircraft";
    	$res = sql_query($sql);
         
        // if we have any errors
        if(!$res) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintMonthlySanityCheck", $sql);
        }
        
        // loop through each aircraft to check the billing information
        if (sql_count($res) > 0)
        {
            //loop through all records and print the results
		    for($AircraftCnt=0; $AircraftRST = sql_row($res, $AircraftCnt); $AircraftCnt++) 
            {
                // if this is a non-rental aircraft, ignore it
                if ($AircraftRST[$hourly_cost_offset] > 0)
                {
                    // print the aircraft header
                    PrintAircraftHeader(
                            $AircraftRST[$n_number_offset],
                            $LineCounter,
                            $LeftMargin,
                            $MaxLineLength,
                            1);
                    
                    // create a query for the flight information for the aircraft we are checking
                    // and the month selected (we back up to the last day of the previous month so
                    // we start with known good information)
                	$FlightSQL = 
                                "SELECT * FROM Flight WHERE (" .
                                    "Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                                    "Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                                    "Instruction_Type <> '" . $InstructorInstruction . "' AND " .
                                    "Aircraft = '" . $AircraftRST[$n_number_offset] . "' AND " .
                                    "Begin_Hobbs <> End_Hobbs)" .
                                    "ORDER BY Date, End_Hobbs";
                	$FlightResult = sql_query($FlightSQL);
                     
                    // if we have any errors
                    if(!$FlightResult) 
                    {
                        // error processing database request, tell the user
                        DisplayDatabaseError("PrintMonthlySanityCheck", $FlightSQL);
                    }
                    
                    // if we have any flight records for this aircraft, check them
                    if (sql_count($FlightResult) > 0)
                    {
                        // the first record in the recordset is the last record of
                        // the previous month. we will use those values to start the
                        // calculations for this month
                        $FlightRST = sql_row($FlightResult, 0);
                        $LastCorrectDate = $FlightRST[$Date_offset];
                        $LastCorrectKeycode = $FlightRST[$Keycode_offset];
                        $LastElapsedTime = $FlightRST[$Hobbs_Elapsed_offset];
                        $LastFlightEndingHobbs = $FlightRST[$End_Hobbs_offset];
                        
                        //loop through all records and check the billing
		                for($FlightCnt=1; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++) 
                        {
                            // if this flight record's start hobbs doesn't match last
                            // record's end hobbs, we have a problem
                            if ($LastFlightEndingHobbs != $FlightRST[$Begin_Hobbs_offset])
                            {
                                // error in beginning hobbs, print it
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "ENDING HOBBS TIME DOESN'T MATCH BEGINNING HOBBS TIME OF NEXT FLIGHT" 
                                       . "<br>");
                                $LineCounter = $LineCounter + 1;
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "PREVIOUS FLIGHT ENDING HOBBS: " .
                                       FormatField(Str($LastFlightEndingHobbs), "Float") . " " .
                                       " DATE: " .
                                       FormatField($LastCorrectDate, "Date") . " " .
                                       " KEYCODE: " .
                                       $LastCorrectKeycode 
                                       . "<br>");
                                $LineCounter = $LineCounter + 1;
                                PrintNonBreakingString(Space($LeftMargin) .
                                        "CURRENT FLIGHT BEGINNING HOBBS: " .
                                       FormatField($FlightRST[$Begin_Hobbs_offset], "Float") . " " .
                                       " DATE: " .
                                       FormatField($FlightRST[$Date_offset], "Date") . " " .
                                       " KEYCODE: " .
                                       $FlightRST[$Keycode_offset]
                                       . "<br>");
                                $LineCounter = $LineCounter + 1;
                                PrintNonBreakingString(Space($LeftMargin) . "<br>");
                                $LineCounter = $LineCounter + 1;
                            }
                            
                            // if this flight record elapsed hobbs is negative, we have a problem
                            if ($FlightRST[$Hobbs_Elapsed_offset] < 0)
                            {
                                // error in beginning hobbs, print it
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "HOBBS TIME USED LESS THAN ZERO" . "<br>");
                                $LineCounter = $LineCounter + 1;
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "LAST CORRECT DATE: " .
                                       FormatField($LastCorrectDate, "Date") . " " .
                                       " KEYCODE: " .
                                       $LastCorrectKeycode 
                                       . "<br>");
                                $LineCounter = $LineCounter + 1;
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "SUSPECT FLIGHT DATE: " .
                                       FormatField($FlightRST[$Date_offset], "Date") . " " .
                                       " KEYCODE: " .
                                       $FlightRST[$Keycode_offset] 
                                       . "<br>");
                                $LineCounter = $LineCounter + 1;
                            }
                            
                            // if the time used on the aircraft is large, flag it
                            if ($FlightRST[$Hobbs_Elapsed_offset] > 20)
                            {
                                // suspect Elapsed time, print it
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "HOBBS TIME LARGE" . "<br>");
                                $LineCounter = $LineCounter + 1;
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "LAST CORRECT DATE: " .
                                       FormatField($LastCorrectDate, "Date") . " " .
                                       " KEYCODE: " .
                                       $LastCorrectKeycode 
                                       . "<br>");
                                $LineCounter = $LineCounter + 1;
                                PrintNonBreakingString(Space($LeftMargin) .
                                       "SUSPECT FLIGHT DATE: " .
                                       FormatField($FlightRST[$Date_offset], "Date") . " " .
                                       " KEYCODE: " .
                                       $FlightRST[$Keycode_offset]
                                       . "<br>");
                                $LineCounter = $LineCounter + 1;
                            }
                            
                            // save this flight information for the next loop
                            $LastCorrectDate = $FlightRST[$Date_offset];
                            $LastCorrectKeycode = $FlightRST[$Keycode_offset];
                            $LastElapsedTime = $FlightRST[$Hobbs_Elapsed_offset];
                            $LastFlightEndingHobbs = $FlightRST[$End_Hobbs_offset];
                        }
                    }
                }
            }
        }
    }
       
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
    #If we dont know the right date then make it up 
    if (!isset($day) or !isset($month) or !isset($year))
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
    
    // are we authorized to perform this function?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }

    // this script will call itself whenever the submit or cancel button is pressed
    // we will check here for the checkout and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $PrintMonthlySanityCheckInformation == "Submit") 
    {
        // submit button was selected

        // updates to the charge are complete, take them back to the last screen
        // after the confirmation sheet is printed
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                $ReturnURL = $goback . CleanGoBackParameters($GoBackParameters);
            else
                // goback parameters not set, use the default
        	    $ReturnURL = $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$makemodel";
        }
        else
        {
            // goback is not set, use the default
        	$ReturnURL = "index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
                        "$makemodel";
        }
        
        // include the print functions here so that the javascript won't
        // interfer with the header functions
        require_once("PrintFunctions.inc");
        
        // setup the print functions
        SetupPrintFunctions($ReturnURL);
        
        // perform and print the sanity checks
        $StartDate = FormatField(BuildDate($Fromday, $Frommonth, $Fromyear), "DatabaseDate");
        $EndDate = FormatField(BuildDate($Today, $Tomonth, $Toyear), "DatabaseDate");
        PrintMonthlySanityCheck($StartDate, $EndDate);
        
        // finish the print form
        CompletePrintFunctions();
                    
        // finished with this part of the script
        exit;
    }
    else if(count($_POST) > 0 && $AircraftCancel == "Cancel") 
    {
        // user canceled the Submit, take them back to the last screen
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                header("Location: $goback" . CleanGoBackParameters($GoBackParameters));
            else
                // goback parameters not set, use the default
        	    header("Location: " . $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$makemodel");
        }
        else
        {
            // goback is not set, use the default
        	Header("Location: index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
                        "$makemodel");
        }
		exit();
    }

    // neither submit or cancel were selected display the main screen
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='PrintMonthlySanityCheckInformation.php' METHOD='POST'>";

    // start the table to display the aircraft report information
    echo "<center>";
    echo "<h2>Print Billing Sanity Check Information</h2>";
    echo "<table border=0>";

    // fault date selection
    echo "<tr>";
    echo "<td class=CC>Perform sanity checking from:&nbsp;";
    genDateSelector("From", "main", $Fromday, $Frommonth, $Fromyear);
    echo "&nbsp;To:&nbsp;";
    genDateSelector("To", "main", $Today, $Tomonth, $Toyear);
    echo "</td>";
    echo "</tr>";

    // finished with the table
    echo "</table>";
            
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='PrintMonthlySanityCheckInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
