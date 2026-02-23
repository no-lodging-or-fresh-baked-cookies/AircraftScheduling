<?php
//-----------------------------------------------------------------------------
// 
// PrintAircraftFaultInformation.php
// 
// PURPOSE: Prints the aircraft fault reports.
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
//      PrintAircraftFaultInformation - set to submit to print aircraft information
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
    require_once("DatabaseFunctions.inc");

    // initialize variables
    $all = '';
    $TailNumber = 'All';
    $default_fault_days = 1;
	$FaultTime = mktime(0, 0, 0, date("m"), date("d") - $default_fault_days, date("Y"));
	$Fromday = date("d", $FaultTime);
	$Frommonth = date("m", $FaultTime);
	$Fromyear = date("Y", $FaultTime);
	$Today   = date("d", $FaultTime);
	$Tomonth = date("m", $FaultTime);
	$Toyear  = date("Y", $FaultTime);
    $PrintOpenFaults = 0;
    $PrintRepairedFaults = 0;
    $PrintDeferredFaults = 0;
    $PrintClosedFaults = 0;
    
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
    if(isset($rdata["PrintAircraftFaultInformation"])) $PrintAircraftFaultInformation = $rdata["PrintAircraftFaultInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    if(isset($rdata["Fromday"])) $Fromday = $rdata["Fromday"];
    if(isset($rdata["Frommonth"])) $Frommonth = $rdata["Frommonth"];
    if(isset($rdata["Fromyear"])) $Fromyear = $rdata["Fromyear"];
    if(isset($rdata["Today"])) $Today = $rdata["Today"];
    if(isset($rdata["Tomonth"])) $Tomonth = $rdata["Tomonth"];
    if(isset($rdata["Toyear"])) $Toyear = $rdata["Toyear"];
    if(isset($rdata["PrintOpenFaults"])) $PrintOpenFaults = $rdata["PrintOpenFaults"];
    if(isset($rdata["PrintRepairedFaults"])) $PrintRepairedFaults = $rdata["PrintRepairedFaults"];
    if(isset($rdata["PrintDeferredFaults"])) $PrintDeferredFaults = $rdata["PrintDeferredFaults"];
    if(isset($rdata["PrintClosedFaults"])) $PrintClosedFaults = $rdata["PrintClosedFaults"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // PrintAllDailyFaultSummary(
    //                            TailNumber as String,
    //                            StartDate as String,
    //                            EndDate as String,
    //                            FaultType As FaultStateType)
    //
    // Purpose:  Print the daily fault information for the dates
    //           specified by the user.
    //
    // Inputs:
    //   TailNumber - "All" for all aircraft or else a selected tailnumber
    //   StartDate - the starting date for the billing information
    //   EndDate - the end date for the billing information
    //   FaultType - the type of faults (open, repaired or deferred) to print
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintAllDailyFaultSummary(
                                        $TailNumber,
                                        $StartDate,
                                        $EndDate,
                                        $FaultType)
    {                                        
        // print all the dates requested
        $NewPageIsNeeded = false;
        while (DateValue($StartDate) <= DateValue($EndDate))
        {
            // print the aircraft fault sheets
            PrintSingleDayAircraftFaultRecord(
                                $TailNumber, 
                                $StartDate, 
                                $FaultType, 
                                $NewPageIsNeeded);
            
            // increment to the next date
            $StartDate = FormatField(
                DateSerial(Year($StartDate), Month($StartDate), Day($StartDate) + 1),
                "Date");
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
    if(count($_POST) > 0 && $PrintAircraftFaultInformation == "Submit") 
    {
        // submit button was selected

        // updates to the fault information are complete, take them back to the last screen
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
        
        // generate the start and end dates
        $StartDate = BuildDate($Fromday, $Frommonth, $Fromyear);
        $EndDate = BuildDate($Today, $Tomonth, $Toyear);
    
        // print daily open faults for the requested aircraft
        if ($PrintOpenFaults == 1)
        {
            PrintAllDailyFaultSummary($TailNumber, $StartDate, $EndDate, $OpenFault);
        }
        
        // print daily repaired faults for all aircraft
        if ($PrintRepairedFaults == 1)
        {
            PrintAllDailyFaultSummary($TailNumber, $StartDate, $EndDate, $Repaired);
        }
        
        // print daily deferred faults for all aircraft
        if ($PrintDeferredFaults == 1)
        {
            PrintAllDailyFaultSummary($TailNumber, $StartDate, $EndDate, $Deferred);
        }
        
        // print daily closed faults for all aircraft
        if ($PrintClosedFaults == 1)
        {
            PrintAllDailyFaultSummary($TailNumber, $StartDate, $EndDate, $Closed);
        }
        
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
	echo "<FORM NAME='main' ACTION='PrintAircraftFaultInformation.php' METHOD='POST'>";
	
	// set the default print selections
    $PrintOpenFaults = 1;
    $PrintRepairedFaults = 0;
    $PrintDeferredFaults = 0;
    $PrintClosedFaults = 0;
    
    // start the table to display the aircraft report information
    echo "<center>";
    echo "<h2>Print Aircraft Fault Information</h2>";
    echo "<table border=0>";

    // display the aircraft selection drop down box
    echo "<tr>";
    echo "<td class=CC>Print Fault Information For Aircraft:&nbsp;";
    $TailNumber = DisplayAircraftDropDown($TailNumber, false, true, "1=1");
    echo "</td>";
    echo "</tr>";
    
    // fault date selection
    echo "<tr>";
    echo "<td class=CC>From:&nbsp;";
    genDateSelector("From", "main", $Fromday, $Frommonth, $Fromyear);
    echo "&nbsp;To:&nbsp;";
    genDateSelector("To", "main", $Today, $Tomonth, $Toyear);
    echo "</td>";
    echo "</tr>";
    
    // skip some space
    echo "<tr>";
    echo "<td>&nbsp;";
    echo "</td>";
    echo "</tr>";

    // print open fault type selection
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintOpenFaults value=1 ";
	if ($PrintOpenFaults == 1) echo "checked";
	echo ">Print Open Faults For the Dates and Aircraft Selected";
    echo "</td>";
    echo "</tr>";
    
    // print repaired fault type selection
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintRepairedFaults value=1 ";
	if ($PrintRepairedFaults == 1) echo "checked";
	echo ">Print Repaired Faults For the Dates and Aircraft Selected";
    echo "</td>";
    echo "</tr>";
    
    // print defered fault type selection
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintDeferredFaults value=1 ";
	if ($PrintDeferredFaults == 1) echo "checked";
	echo ">Print Deferred Faults For the Dates and Aircraft Selected";
    echo "</td>";
    echo "</tr>";
    
    // print closed fault type selection
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintClosedFaults value=1 ";
	if ($PrintClosedFaults == 1) echo "checked";
	echo ">Print Closed Faults For the Dates and Aircraft Selected";
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
    echo "<TD><input name='PrintAircraftFaultInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
