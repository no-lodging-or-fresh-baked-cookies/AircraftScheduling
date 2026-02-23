<?php
//-----------------------------------------------------------------------------
// 
// PrintMemberStatistics.php
// 
// PURPOSE: Print daily member statistics in various ways.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      all - select all resources
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      PrintMemberStatistics - set to submit to print aircraft information
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
	$JoinFromTime = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));
	$JoinFromday = date("d", $JoinFromTime);
	$JoinFrommonth = date("m", $JoinFromTime);
	$JoinFromyear = date("Y", $JoinFromTime);
	$JoinToday   = date("d");
	$JoinTomonth = date("m");
	$JoinToyear  = date("Y");
	$ResignedFromTime = mktime(0, 0, 0, date("m") - 1, date("d"), date("Y"));
	$ResignedFromday = date("d", $ResignedFromTime);
	$ResignedFrommonth = date("m", $ResignedFromTime);
	$ResignedFromyear = date("Y", $ResignedFromTime);
	$ResignedToday   = date("d");
	$ResignedTomonth = date("m");
	$ResignedToyear  = date("Y");

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
    if(isset($rdata["PrintMemberStatistics"])) $PrintMemberStatistics = $rdata["PrintMemberStatistics"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["JoinFromday"])) $JoinFromday = $rdata["JoinFromday"];
    if(isset($rdata["JoinFrommonth"])) $JoinFrommonth = $rdata["JoinFrommonth"];
    if(isset($rdata["JoinFromyear"])) $JoinFromyear = $rdata["JoinFromyear"];
    if(isset($rdata["JoinToday"])) $JoinToday = $rdata["JoinToday"];
    if(isset($rdata["JoinTomonth"])) $JoinTomonth = $rdata["JoinTomonth"];
    if(isset($rdata["JoinToyear"])) $JoinToyear = $rdata["JoinToyear"];
    if(isset($rdata["ResignedFromday"])) $ResignedFromday = $rdata["ResignedFromday"];
    if(isset($rdata["ResignedFrommonth"])) $ResignedFrommonth = $rdata["ResignedFrommonth"];
    if(isset($rdata["ResignedFromyear"])) $ResignedFromyear = $rdata["ResignedFromyear"];
    if(isset($rdata["ResignedToday"])) $ResignedToday = $rdata["ResignedToday"];
    if(isset($rdata["ResignedTomonth"])) $ResignedTomonth = $rdata["ResignedTomonth"];
    if(isset($rdata["ResignedToyear"])) $ResignedToyear = $rdata["ResignedToyear"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // PrintMembersStatisticsInformation(
    //                                    StatisticsFieldName As String,
    //                                    StatisticsField As String,
    //                                    StatisticsStartDate As String,
    //                                    StatisticsEndDate as String)
    //
    // Purpose:  Print all members that meet the criteria of
    //           StatisticsField >= StatisticsStartDate and
    //           StatisticsField <= StatisticsEndDate.
    //
    // Inputs:
    //   StatisticsFieldName - name of the database field to check
    //   StatisticsField - offset of the database field to check
    //   StatisticsStartDate - the stating date for the statistics check
    //   StatisticsEndDate - the stating date for the statistics check
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMembersStatisticsInformation(
                                    $StatisticsFieldName,
                                    $StatisticsField,
                                    $StatisticsStartDate,
                                    $StatisticsEndDate)
    {     
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 96;
        $LeftMargin = 0;
        $PageLength = 60;
        $HeaderLength = 1;
        $KeyCodeLength = 6;
        $MemberNameFieldLength = 15;
        $AddressFieldLength = 19;
        $CityFieldLength = 11;
        $StateFieldLength = 2;
        $ZipFieldLength = 5;
        $HomeFieldLength = 14;
        $DateFieldLength = 15;
        $SortParameter = "last_name";
    
        // printer setup
        PrinterSetup(9);
        $PageHeader = "************ " . $AircraftScheduling_company . " ROSTER ************";
        
        // build the column title for the statistics report
        $StatisticsFieldTitle = Replace($StatisticsFieldName, "_", " ");
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($StatisticsFieldTitle . " From " . $StatisticsStartDate .
                      " To " . $StatisticsEndDate .
                      " Sorted by " . $SortParameter, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
        
        // print the column header
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Key", $CenterJustify, $KeyCodeLength) . " " .
                    JustifyField("Name", $CenterJustify, $MemberNameFieldLength) . " " .
                    JustifyField("Address", $CenterJustify, $AddressFieldLength + $CityFieldLength + $StateFieldLength + $ZipFieldLength + 3) . " " .
                    JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . " " .
                    JustifyField($StatisticsFieldTitle, $CenterJustify, $DateFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(" " . "<br>");
        
        // create a query to get all currency rules for the rating of pilot
        $MembersResult = SQLOpenRecordset(
            "SELECT * FROM AircraftScheduling_person WHERE (" .
                $StatisticsFieldName . " >= '" . FormatField($StatisticsStartDate, "DatabaseDate") . "' AND " .
                $StatisticsFieldName . " <= '" . FormatField($StatisticsEndDate, "DatabaseDate") . "') " .
                "ORDER BY " . $SortParameter . "");
        
        // loop through all members and print the ones that match the given criteria
        $PageNumber = 0;
        $TextLines = 0;
		for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++) 
        {
            // if we have filled the page, send the page to the printer
            if ($TextLines > $PageLength)
            {
                // print the footer
                $PageNumber++;
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(" " . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                              "<br>");
                
                // at max page length, print the page
                PrintNewPage();
                $TextLines = 0;
        
                // skip some space at the top of the form
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                              "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($StatisticsFieldTitle . " From " . $StatisticsStartDate .
                              " To " . $StatisticsEndDate .
                              " Sorted by " . $SortParameter, $CenterJustify, $MaxLineLength) . 
                              "<br>");
               
                // skip some space below the header
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                }
        
                // print the column header
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("Key", $CenterJustify, $KeyCodeLength) . " " .
                            JustifyField("Name", $CenterJustify, $MemberNameFieldLength) . " " .
                            JustifyField("Address", $CenterJustify, $AddressFieldLength + $CityFieldLength + $StateFieldLength + $ZipFieldLength + 3) . " " .
                            JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . " " .
                            JustifyField($StatisticsFieldTitle, $CenterJustify, $DateFieldLength) . 
                            "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                              "<br>");
                PrintNonBreakingString(" " . "<br>");
            }
        
            // print the member record
            $MemberFullName = Trim($MembersRST[$first_name_offset]) . " " .
                                Trim($MembersRST[$last_name_offset]);
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField(UCase($MembersRST[$username_offset]), $LeftJustify, $KeyCodeLength) . " " .
                        JustifyField($MemberFullName, $LeftJustify, $MemberNameFieldLength) . " " .
                        JustifyField($MembersRST[$address1_offset], $LeftJustify, $AddressFieldLength) . " " .
                        JustifyField($MembersRST[$city_offset], $LeftJustify, $CityFieldLength) . " " .
                        JustifyField($MembersRST[$state_offset], $LeftJustify, $StateFieldLength) . " " .
                        JustifyField($MembersRST[$zip_offset], $LeftJustify, $ZipFieldLength) . " " .
                        JustifyField($MembersRST[$Home_Phone_offset], $LeftJustify, $HomeFieldLength) . " " .
                        JustifyField(FormatField($MembersRST[$StatisticsField], "Date"), $LeftJustify, $DateFieldLength) . 
                        "<br>");
            PrintNonBreakingString(" " . "<br>");
            $TextLines = $TextLines + 2;
        }
        
        // print the last page (if needed)
         if ($TextLines > 0)
         {
            // skip to the bottom of the page
            for ($i = $TextLines; $i <= $PageLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the footer
            $PageNumber++;
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                          "<br>");
        }
        PrintNewPage();
    }

    //********************************************************************
    // PrintSelectedStatistics(
    //                            JoinStartDate As String, JoinEndDate As String,
    //                            ResignedStartDate As String, ResignedEndDate As String)
    //
    // Purpose:  Print the requested member statistics for the dates given.
    //
    // Inputs:
    //   JoinStartDate - the starting date for members that have joined
    //   JoinEndDate - the end date for members that have joined
    //   ResignedStartDate - the starting date for members that have resigned
    //   ResignedEndDate - the end date for members that have resigned
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSelectedStatistics(
                                        $JoinStartDate, $JoinEndDate,
                                        $ResignedStartDate, $ResignedEndDate)
    {                
        include "DatabaseConstants.inc";
        
        // print the requested reports
        PrintMembersStatisticsInformation(
                                    "Membership_Date",
                                    $Membership_Date_offset,
                                    $JoinStartDate,
                                    $JoinEndDate);
        PrintMembersStatisticsInformation(
                                    "Resign_Date",
                                    $Resign_Date_offset,
                                    $ResignedStartDate,
                                    $ResignedEndDate);
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
    if(count($_POST) > 0 && $PrintMemberStatistics == "Submit") 
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
        $JoinStartDate = BuildDate($JoinFromday, $JoinFrommonth, $JoinFromyear);
        $JoinEndDate = BuildDate($JoinToday, $JoinTomonth, $JoinToyear);
        $ResignedStartDate = BuildDate($ResignedFromday, $ResignedFrommonth, $ResignedFromyear);
        $ResignedEndDate = BuildDate($ResignedToday, $ResignedTomonth, $ResignedToyear);
        
        // print requested statistics
        PrintSelectedStatistics(
                                $JoinStartDate, $JoinEndDate,
                                $ResignedStartDate, $ResignedEndDate);
        
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
	echo "<FORM NAME='main' ACTION='PrintMemberStatistics.php' METHOD='POST'>";

    // start the table to display the aircraft report information
    echo "<center>";
    echo "<h2>Print User Statistics</h2>";
    echo "<table border=0>";
    
    // members joined report date selection
    echo "<tr>";
    echo "<td class=CL>Print All Users That Joined From:";
    echo "</td>";
    echo "<td>";
    genDateSelector("JoinFrom", "main", $JoinFromday, $JoinFrommonth, $JoinFromyear);
    echo "&nbsp;To:&nbsp;";
    genDateSelector("JoinTo", "main", $JoinToday, $JoinTomonth, $JoinToyear);
    echo "</td>";
    echo "</tr>";
    
    // members resigned report date selection
    echo "<tr>";
    echo "<td class=CL>Print All Users That Resigned From:";
    echo "</td>";
    echo "<td>";
    genDateSelector("ResignedFrom", "main", $ResignedFromday, $ResignedFrommonth, $ResignedFromyear);
    echo "&nbsp;To:&nbsp;";
    genDateSelector("ResignedTo", "main", $ResignedToday, $ResignedTomonth, $ResignedToyear);
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
    echo "<TD><input name='PrintMemberStatistics' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
