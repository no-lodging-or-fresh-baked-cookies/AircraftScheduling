<?php
//-----------------------------------------------------------------------------
// 
// PrintDailyDARInformation.php
// 
// PURPOSE: Print daily DAR information in various ways.
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
//      PrintDailyDARInformation - set to submit to print aircraft information
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
    require_once("CurrencyFunctions.inc");
    require_once("DatabaseFunctions.inc");

    // initialize variables
    $all = '';
    $TailNumber = 'All';
    $default_CC_days = 2;
	$CCFromTime = mktime(0, 0, 0, date("m"), date("d") - $default_CC_days, date("Y"));
	$CCFromday = date("d", $CCFromTime);
	$CCFrommonth = date("m", $CCFromTime);
	$CCFromyear = date("Y", $CCFromTime);
	$CCToday   = date("d", $CCFromTime);
	$CCTomonth = date("m", $CCFromTime);
	$CCToyear  = date("Y", $CCFromTime);
    $default_Cash_days = 1;
	$CashFromTime = mktime(0, 0, 0, date("m"), date("d") - $default_Cash_days, date("Y"));
	$CashFromday = date("d", $CashFromTime);
	$CashFrommonth = date("m", $CashFromTime);
	$CashFromyear = date("Y", $CashFromTime);
	$CashToday   = date("d", $CashFromTime);
	$CashTomonth = date("m", $CashFromTime);
	$CashToyear  = date("Y", $CashFromTime);
    $DAROptions = 0;
    $DetailedDAROptions = 0;

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
    if(isset($rdata["PrintDailyDARInformation"])) $PrintDailyDARInformation = $rdata["PrintDailyDARInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["CCFromday"])) $CCFromday = $rdata["CCFromday"];
    if(isset($rdata["CCFrommonth"])) $CCFrommonth = $rdata["CCFrommonth"];
    if(isset($rdata["CCFromyear"])) $CCFromyear = $rdata["CCFromyear"];
    if(isset($rdata["CCToday"])) $CCToday = $rdata["CCToday"];
    if(isset($rdata["CCTomonth"])) $CCTomonth = $rdata["CCTomonth"];
    if(isset($rdata["CCToyear"])) $CCToyear = $rdata["CCToyear"];
    if(isset($rdata["CashFromday"])) $CashFromday = $rdata["CashFromday"];
    if(isset($rdata["CashFrommonth"])) $CashFrommonth = $rdata["CashFrommonth"];
    if(isset($rdata["CashFromyear"])) $CashFromyear = $rdata["CashFromyear"];
    if(isset($rdata["CashToday"])) $CashToday = $rdata["CashToday"];
    if(isset($rdata["CashTomonth"])) $CashTomonth = $rdata["CashTomonth"];
    if(isset($rdata["CashToyear"])) $CashToyear = $rdata["CashToyear"];
    if(isset($rdata["DAROptions"])) $DAROptions = $rdata["DAROptions"];
    if(isset($rdata["DetailedDAROptions"])) $DetailedDAROptions = $rdata["DetailedDAROptions"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // PrintSelectedDailySummary(
    //                            CreditCardStartDate As String, CreditCardEndDate As String,
    //                            CashStartDate As String, CashEndDate As String)
    //
    // Purpose:  Print the daily billing information for the dates
    //           specified by the user if the options are enabled
    //           by the user. This procedure allows the flight information
    //           to have different start and end dates for the DAR
    //           and DAR detail reports.
    //
    // Inputs:
    //   CreditCardStartDate - the starting date for the billing information
    //   CreditCardEndDate - the end date for the billing information
    //   CashStartDate - the starting date for the DAR flight billing information
    //   CashEndDate - the end date for the DAR flight billing information
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSelectedDailySummary(
                                        $CreditCardStartDate, $CreditCardEndDate,
                                        $CashStartDate, $CashEndDate)
    {        
        global $DAROptions;
        global $DetailedDAROptions;

        // reset the line counter
        $LineCounter = 0;
        $PageHasBeenPrinted = false;
        
        // the DAR and DAR detail reports are for a range of dates so we
        // will process them here
        // should we print the DAR report
        if ($DAROptions)
        {
            // if we have already printed a page, start a new page
            if ($PageHasBeenPrinted)
            {
                PrintNewPage();
                $LineCounter = 0;
            }
            
            // print the DAR report
            PrintDARReport(
                            $CreditCardStartDate, $CreditCardEndDate,
                            $CashStartDate, $CashEndDate);
            
            // we have printed a page
            $PageHasBeenPrinted = true;
        }
        
        // should we print the DAR detail report
        if ($DetailedDAROptions)
        {
            // if we have already printed a page, start a new page
            if ($PageHasBeenPrinted)
            {
                PrintNewPage();
                $LineCounter = 0;
            }
            
            // print the credit card report
            PrintDARDetailReport(
                                $CreditCardStartDate, $CreditCardEndDate,
                                $CashStartDate, $CashEndDate);
            
            // we have printed a page
            $PageHasBeenPrinted = true;
        }
    }
   
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
    #if we dont know the right date then make it up 
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
    if(count($_POST) > 0 && $PrintDailyDARInformation == "Submit") 
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
        
        // generate the start and end dates
        $CreditCardStartDate = BuildDate($CCFromday, $CCFrommonth, $CCFromyear);
        $CreditCardEndDate = BuildDate($CCToday, $CCTomonth, $CCToyear);
        $CashStartDate = BuildDate($CashFromday, $CashFrommonth, $CashFromyear);
        $CashEndDate = BuildDate($CashToday, $CashTomonth, $CashToyear);
        
        // print requested daily billing
        PrintSelectedDailySummary(
                                    $CreditCardStartDate, $CreditCardEndDate,
                                    $CashStartDate, $CashEndDate);
        
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
    
    // print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='PrintDailyDARInformation.php' METHOD='POST'>";
	
	// set the default print selections
	$DAROptions = 1;
    $DetailedDAROptions = 1;

    // start the table to display the aircraft report information
    echo "<center>";
    echo "<h2>Print Daily DAR Information</h2>";
    echo "<table border=0>";
    
    // credit card daily report date selection
    echo "<tr>";
    echo "<td class=CC>Credit Card Charges From:&nbsp;";
    genDateSelector("CCFrom", "main", $CCFromday, $CCFrommonth, $CCFromyear);
    echo "&nbsp;To:&nbsp;";
    genDateSelector("CCTo", "main", $CCToday, $CCTomonth, $CCToyear);
    echo "</td>";
    echo "</tr>";
    
    // cash daily report date selection
    echo "<tr>";
    echo "<td class=CC>Cash Charges From:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    genDateSelector("CashFrom", "main", $CashFromday, $CashFrommonth, $CashFromyear);
    echo "&nbsp;To:&nbsp;";
    genDateSelector("CashTo", "main", $CashToday, $CashTomonth, $CashToyear);
    echo "</td>";
    echo "</tr>";
    
    // skip some space
    echo "<tr>";
    echo "<td>&nbsp;";
    echo "</td>";
    echo "</tr>";
     
    // Print DAR Report
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=DAROptions value=1 ";
	if ($DAROptions == 1) echo "checked";
	echo ">Print DAR Report";
    echo "</td>";
    echo "</tr>";
    
    // DAR Detail Report
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=DetailedDAROptions value=1 ";
	if ($DetailedDAROptions == 1) echo "checked";
	echo ">Print DAR Detail Report";
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
    echo "<TD><input name='PrintDailyDARInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
