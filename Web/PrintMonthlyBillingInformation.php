<?php
//-----------------------------------------------------------------------------
// 
// PrintMonthlyBillingInformation.php
// 
// PURPOSE: Print monthly billing information in various ways.
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
//      PrintMonthlyBillingInformation - set to submit to print aircraft information
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
	$FromTime = mktime(0, 0, 0, date("m") - 1, 1, date("Y"));
	$ToTime = mktime(0, 0, 0, date("m"), 0, date("Y"));
	$Fromday = date("d", $FromTime);
	$Frommonth = date("m", $FromTime);
	$Fromyear = date("Y", $FromTime);
	$Today   = date("d", $ToTime);
	$Tomonth = date("m", $ToTime);
	$Toyear  = date("Y", $ToTime);
    $ActivitySummaryOptions = 0;
    $DAROptions = 0;
    $DetailedDAROptions = 0;
    $MonthlyBillingOptions = 0;
    $MonthlyCreditOptions = 0;
    $AircraftSummaryOption = 0;
    
    // get the starting and ending name in the user list
	$UserResult = SQLOpenRecordset(
    			"SELECT $DatabaseNameFormat " .
    			"FROM AircraftScheduling_person " .
    			"WHERE user_level != $UserLevelDisabled " .
                "ORDER by last_name");		
     
    // process the results of the database inquiry
    $row = sql_row($UserResult, 0); 	
    $BillsFromNameOfUser = $row[0];
    $CreditsFromNameOfUser = $row[0];
    $row = sql_row($UserResult, (sql_count($UserResult) - 1)); 	
    $BillsToNameOfUser = $row[0];
    $CreditsToNameOfUser = $row[0];

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
    if(isset($rdata["PrintMonthlyBillingInformation"])) $PrintMonthlyBillingInformation = $rdata["PrintMonthlyBillingInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["Fromday"])) $Fromday = $rdata["Fromday"];
    if(isset($rdata["Frommonth"])) $Frommonth = $rdata["Frommonth"];
    if(isset($rdata["Fromyear"])) $Fromyear = $rdata["Fromyear"];
    if(isset($rdata["Today"])) $Today = $rdata["Today"];
    if(isset($rdata["Tomonth"])) $Tomonth = $rdata["Tomonth"];
    if(isset($rdata["Toyear"])) $Toyear = $rdata["Toyear"];    
    if(isset($rdata["BillsFromNameOfUser"])) $BillsFromNameOfUser = $rdata["BillsFromNameOfUser"];
    if(isset($rdata["BillsToNameOfUser"])) $BillsToNameOfUser = $rdata["BillsToNameOfUser"];
    if(isset($rdata["CreditsFromNameOfUser"])) $CreditsFromNameOfUser = $rdata["CreditsFromNameOfUser"];
    if(isset($rdata["CreditsToNameOfUser"])) $CreditsToNameOfUser = $rdata["CreditsToNameOfUser"];
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    
    if(isset($rdata["ActivitySummaryOptions"])) $ActivitySummaryOptions = $rdata["ActivitySummaryOptions"];
    if(isset($rdata["DAROptions"])) $DAROptions = $rdata["DAROptions"];
    if(isset($rdata["DetailedDAROptions"])) $DetailedDAROptions = $rdata["DetailedDAROptions"];
    if(isset($rdata["MonthlyBillingOptions"])) $MonthlyBillingOptions = $rdata["MonthlyBillingOptions"];
    if(isset($rdata["MonthlyCreditOptions"])) $MonthlyCreditOptions = $rdata["MonthlyCreditOptions"];
    if(isset($rdata["AircraftSummaryOption"])) $AircraftSummaryOption = $rdata["AircraftSummaryOption"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // PrintSingleKeycodeBill(
    //                        BillingKeycodeRST As MYSQL_RS,
    //                        StartDate as string,
    //                        EndDate as string,
    //                        StartNewPage as Boolean)
    //
    // Purpose:  Print the monthly billing information for
    //           the given keycode
    //
    // Inputs:
    //   BillingKeycodeRST - the recordset to print the billing for
    //   StartDate - the start date to print the billing for
    //   EndDate - the end date to print the billing for
    //   StartNewPage - set true if we wrote anything to the page
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleKeycodeBill(
                                    $BillingKeycodeRST,
                                    $StartDate,
                                    $EndDate,
                                    &$StartNewPage)
    {                                
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $FBOLocation;
              
        include "DatabaseConstants.inc";

        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 4;
        $ColumnWidth1 = 12;
        $ColumnWidth2 = 34;
        $ColumnWidth3 = 6;
        $ColumnWidth4 = 9;
        $ColumnWidth5 = 9;
        $ColumnWidth6 = 11;
        $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 +
                                $ColumnWidth4 + $ColumnWidth5 + $ColumnWidth6 + 5;
            
        // printer setup
        PrinterSetup(9);
        
        $PageHeader = "***** " . $AircraftScheduling_company . " *****";
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
                
        // create a query for the flight charges for the date and keycode selected
        $FlightResult = SQLOpenRecordset(
            "SELECT * FROM Flight WHERE (" .
                "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                "Flight.Keycode = '" . $BillingKeycodeRST[$username_offset] . "')" .
                "ORDER BY Date");
                
        // create a query for the misc charges for the date and keycode selected
        $ChargesResult = SQLOpenRecordset(
            "SELECT * FROM Charges WHERE (" .
                "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                "Charges.Keycode = '" . $BillingKeycodeRST[$username_offset] . "')" .
                "ORDER BY Date");
        
        // if we don't have any charges for this user, exit
        if ((sql_count($FlightResult) == 0) &&
            (sql_count($ChargesResult) == 0))
        {
            // no flight or charge records found, don't print the bill
            $StartNewPage = false;
            return;
        }
        $StartNewPage = true;
        
        $LineCounter = 0;
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
         
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
            
        // print the user's social security number
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField(FormatSSNString(DecryptString($BillingKeycodeRST[$SSN_offset])),
                       $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the month
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("CHARGES FROM " .
                                UCase(FormatField($StartDate, "Date")) . " TO " .
                                UCase(FormatField($EndDate, "Date")),
                       $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the user's name and address
        $FullName = Trim($BillingKeycodeRST[$first_name_offset]) . " " .
                               Trim($BillingKeycodeRST[$middle_name_offset]) . " " .
                               Trim($BillingKeycodeRST[$last_name_offset]);
        PrintNonBreakingString(Space($LeftMargin) .
                       Space(10) .
                       JustifyField($FullName, $LeftJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                       Space(10) .
                       JustifyField($BillingKeycodeRST[$address1_offset], $LeftJustify, $MaxLineLength) . 
                       "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                       Space(10) .
                       JustifyField(
                            $BillingKeycodeRST[$city_offset] . " " .
                            $BillingKeycodeRST[$state_offset] . " " .
                            $BillingKeycodeRST[$zip_offset],
                            $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
                
        //print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField("DESCRIPTION", $CenterJustify, $ColumnWidth2) . " " .
                       JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . " " .
                       JustifyField("RATE", $CenterJustify, $ColumnWidth4) . " " .
                       JustifyField("TOTAL", $CenterJustify, $ColumnWidth5) . " " .
                       JustifyField("BALANCE", $CenterJustify, $ColumnWidth6) . "<br>");
        $LineCounter++;
                     
        // see if we found any flight charge records for the given date
        $Balance = 0;
        if (sql_count($FlightResult) > 0)
        {
            // loop through all records and print the results
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {            

                // if we have filled the page, send the page to the printer
                If ($LineCounter > $PageLength)
                {
                    // at max page length, print the page
                    PrintNewPage();
                    $LineCounter = 0;
                     
                    // skip some space at the top of the form
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                     
                    // print the page header
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                                   "<br>");
                    $LineCounter++;
                    
                    // skip some space below the header
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                        
                    // print the month
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("CHARGES FROM " .
                                            UCase(FormatField($StartDate, "Date")) . " TO " .
                                            UCase(FormatField($EndDate, "Date")) .
                                            " (CONT)",
                                   $LeftJustify, $MaxLineLength) . "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) . "<br>");
                    $LineCounter++;
                        
                    // print the user's name
                    $FullName = Trim($BillingKeycodeRST[$first_name_offset]) . " " .
                                           Trim($BillingKeycodeRST[$middle_name_offset]) . " " .
                                           Trim($BillingKeycodeRST[$last_name_offset]);
                    PrintNonBreakingString(Space($LeftMargin) .
                                   Space(10) .
                                   JustifyField($FullName, $LeftJustify, $MaxLineLength) . 
                                   "<br>");
                
                    PrintNonBreakingString(" " . "<br>");
                    $LineCounter++;
                    
                    //print the column headers
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                                   JustifyField("DESCRIPTION", $CenterJustify, $ColumnWidth2) . " " .
                                   JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . " " .
                                   JustifyField("RATE", $CenterJustify, $ColumnWidth4) . " " .
                                   JustifyField("TOTAL", $CenterJustify, $ColumnWidth5) . " " .
                                   JustifyField("BALANCE", $CenterJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
                
                // print the flight billing record if there is a cost associated with it
                if ($FlightRST[$Aircraft_Cost_offset] > 0) 
                {
                    $Balance = $Balance + $FlightRST[$Aircraft_Cost_offset];
                    PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                           JustifyField("FLIGHT IN " . $FlightRST[$Aircraft_offset], $LeftJustify, $ColumnWidth2) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Hobbs_Elapsed_offset]), "Float"), $RightJustify, $ColumnWidth3) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Aircraft_Rate_offset]), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Aircraft_Cost_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                           JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . 
                           "<br>");
                    $LineCounter++;
                }
                
                // print the flight instruction billing record if there is a cost associated with it
                if ($FlightRST[$Instructor_Charge_offset] > 0) 
                {
                    $Balance = $Balance + $FlightRST[$Instructor_Charge_offset];
                    PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                           JustifyField("FLIGHT LESSON WITH " . $FlightRST[$Instructor_Keycode_offset], $LeftJustify, $ColumnWidth2) . " " .
                           JustifyField(FormatField(Str(Val($FlightRST[$Dual_Time_offset]) . Val($FlightRST[$Dual_PP_Time_offset])), "Float"), $RightJustify, $ColumnWidth3) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Instruction_Rate_offset]), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Instructor_Charge_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                           JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
                
                // are there fuel reimbursement credits in this record?
                if ($FlightRST[$Cross_Country_Fuel_Credit_offset] != 0) 
                {
                    // check for divide by 0
                    if ($FlightRST[$Cross_Country_Fuel_offset] == 0)
                    {
                        $tmpCrossCountry = 0;
                    }
                    else
                    {
                        $tmpCrossCountry = $FlightRST[$Cross_Country_Fuel_Credit_offset] / $FlightRST[$Cross_Country_Fuel_offset];
                    }
                    $Balance = $Balance + $FlightRST[$Cross_Country_Fuel_Credit_offset];
                    PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                           JustifyField("CROSS COUNTRY FUEL REIMBURSEMENT", $LeftJustify, $ColumnWidth2) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Cross_Country_Fuel_offset]), "Float"), $RightJustify, $ColumnWidth3) . " " .
                           JustifyField(FormatField(Str(RoundToDecimalPlaces($tmpCrossCountry)), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Cross_Country_Fuel_Credit_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                           JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
                
                // are there local fuel charges in this record?
                if ($FlightRST[$Local_Fuel_Cost_offset] != 0) 
                {
                    $Balance = $Balance + $FlightRST[$Local_Fuel_Cost_offset];
                    PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                           JustifyField("FUEL PURCHASED AT " . $FBOLocation, $LeftJustify, $ColumnWidth2) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Local_Fuel_offset]), "Float"), $RightJustify, $ColumnWidth3) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Fuel_Cost_offset]), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Local_Fuel_Cost_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                           JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
                
                // are there local oil charges in this record?
                if ($FlightRST[$Oil_Cost_offset] != 0) 
                {
                    $Balance = $Balance + $FlightRST[$Oil_Cost_offset];
                    PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                           JustifyField("OIL PURCHASED AT " . $FBOLocation, $LeftJustify, $ColumnWidth2) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Oil_offset]), "Float"), $RightJustify, $ColumnWidth3) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Oil_Rate_offset]), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                           JustifyField(FormatField(Str($FlightRST[$Oil_Cost_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                           JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
            }
        }
        
        // see if we found any misc charge records for the given date
        if (sql_count($ChargesResult) > 0) 
        {
            // loop through all records and print the results
            for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
            {
                // if we have filled the page, send the page to the printer
                if ($LineCounter > $PageLength) 
                {
                    // at max page length, print the page
                    PrintNewPage();
                    $LineCounter = 0;
                     
                    // skip some space at the top of the form
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                     
                    // print the page header
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                                   "<br>");
                    $LineCounter++;
                    
                    // skip some space below the header
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                        
                    // print the month
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("CHARGES FROM " .
                                            UCase(FormatField($StartDate, "Date")) . " TO " .
                                            UCase(FormatField($EndDate, "Date")) .
                                            " (CONT)",
                                   $LeftJustify, $MaxLineLength) . "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) . "<br>");
                    $LineCounter++;
                        
                    // print the user's name
                    $FullName = Trim($BillingKeycodeRST[$first_name_offset]) . " " .
                                           Trim($BillingKeycodeRST[$middle_name_offset]) . " " .
                                           Trim($BillingKeycodeRST[$last_name_offset]);
                    PrintNonBreakingString(Space($LeftMargin) .
                                   Space(10) .
                                   JustifyField($FullName, $LeftJustify, $MaxLineLength) . "<br>");
                
                    PrintNonBreakingString(" " . "<br>");
                    $LineCounter++;
                
                    //print the column headers
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                                   JustifyField("DESCRIPTION", $CenterJustify, $ColumnWidth2) . " " .
                                   JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . " " .
                                   JustifyField("RATE", $CenterJustify, $ColumnWidth4) . " " .
                                   JustifyField("TOTAL", $CenterJustify, $ColumnWidth5) . " " .
                                   JustifyField("BALANCE", $CenterJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
                
                // print the misc billing record if there is a cost associated with it
                $Balance = $Balance + $ChargesRST[$Total_Price_offset];
                PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField(FormatField($ChargesRST[$ChargesDate_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField($ChargesRST[$Part_Description_offset], $LeftJustify, $ColumnWidth2) . " " .
                       JustifyField(FormatField(Str($ChargesRST[$Quantity_offset]), "Float"), $RightJustify, $ColumnWidth3) . " " .
                       JustifyField(FormatField(Str($ChargesRST[$Price_offset]), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                       JustifyField(FormatField(Str($ChargesRST[$Total_Price_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                       JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . "<br>");
                $LineCounter++;
            }
        }
            
        // print the totals
        PrintNonBreakingString(Space($LeftMargin) .
                $UnderLine . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
               JustifyField(" ", $CenterJustify, $ColumnWidth1) . " " .
               JustifyField("TOTALS", $LeftJustify, $ColumnWidth2) . " " .
               JustifyField(" ", $CenterJustify, $ColumnWidth3) . " " .
               JustifyField(" ", $RightJustify, $ColumnWidth4) . " " .
               JustifyField(" ", $RightJustify, $ColumnWidth5) . " " .
               JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . 
               "<br>");
        $LineCounter++;
    }
    
    //********************************************************************
    // PrintSingleKeycodeCredit(
    //                        BillingKeycodeRST As MYSQL_RS,
    //                        StartDate as string,
    //                        EndDate as string,
    //                        StartNewPage as Boolean)
    //
    // Purpose:  Print the monthly credit information for
    //           the given keycode
    //
    // Inputs:
    //   BillingKeycodeRST - the recordset to print the billing for
    //   StartDate - the start date to print the billing for
    //   EndDate - the end date to print the billing for
    //   StartNewPage - set true if we wrote anything to the page
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleKeycodeCredit(
                                $BillingKeycodeRST,
                                $StartDate,
                                $EndDate,
                                &$StartNewPage)
    {                                        
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
              
        include "DatabaseConstants.inc";

        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 4;
        $ColumnWidth1 = 12;
        $ColumnWidth2 = 34;
        $ColumnWidth3 = 6;
        $ColumnWidth4 = 9;
        $ColumnWidth5 = 9;
        $ColumnWidth6 = 11;
        $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 +
                                $ColumnWidth4 + $ColumnWidth5 + $ColumnWidth6 + 5;
        
        // printer setup
        PrinterSetup(9);
        $PageHeader = "***** " . $AircraftScheduling_company . " *****";
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // create a query for the flight charges for the date and keycode selected
        $FlightResult = SQLOpenRecordset(
            "SELECT * FROM Flight WHERE (" .
                "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                "Flight.Keycode = '" . $BillingKeycodeRST[$username_offset] . "' AND " .
                "Flight.Instructor_Charge < 0) " .
                "ORDER BY Date");
        
        // if we don't have any credits for this user, exit
        if (sql_count($FlightResult) == 0) 
        {
            // no flight credits found, don't print the credit
            $StartNewPage = false;
            return;
        }
        $StartNewPage = true;
        
        $LineCounter = 0;
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
         
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
            
        // print the user's social security number
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField(FormatSSNString(DecryptString($BillingKeycodeRST[$SSN_offset])),
                       $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the month
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("INVOICE FROM " .
                                UCase(FormatField($StartDate, "Date")) . " TO " .
                                UCase(FormatField($EndDate, "Date")),
                       $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the user's name and address
        $FullName = Trim($BillingKeycodeRST[$first_name_offset]) . " " .
                               Trim($BillingKeycodeRST[$middle_name_offset]) . " " .
                               Trim($BillingKeycodeRST[$last_name_offset]);
        PrintNonBreakingString(Space($LeftMargin) .
                       Space(10) .
                       JustifyField($FullName, $LeftJustify, $MaxLineLength) . 
                       "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                       Space(10) .
                       JustifyField($BillingKeycodeRST[$address1_offset], $LeftJustify, $MaxLineLength) . 
                       "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                       Space(10) .
                       JustifyField(
                            $BillingKeycodeRST[$city_offset] . " " .
                            $BillingKeycodeRST[$state_offset] . " " .
                            $BillingKeycodeRST[$zip_offset],
                            $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
                
        // print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField("DESCRIPTION", $CenterJustify, $ColumnWidth2) . " " .
                       JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . " " .
                       JustifyField("RATE", $CenterJustify, $ColumnWidth4) . " " .
                       JustifyField("TOTAL", $CenterJustify, $ColumnWidth5) . " " .
                       JustifyField("BALANCE", $CenterJustify, $ColumnWidth6) . "<br>");
        $LineCounter++;
                     
        // see if we found any flight charge records for the given date
        $Balance = 0;
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and print the results
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // if we have filled the page, send the page to the printer
                if ($LineCounter > $PageLength) 
                {
                    // at max page length, print the page
                    PrintNewPage();
                    $LineCounter = 0;
                     
                    // skip some space at the top of the form
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                     
                    // print the page header
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                                   "<br>");
                    $LineCounter++;
                    
                    // skip some space below the header
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                        
                    // print the month
                    PrintNonBreakingString(Space($LeftMargin) .
                                    JustifyField("INVOICE FROM " .
                                            UCase(FormatField($StartDate, "Date")) . " TO " .
                                            UCase(FormatField($EndDate, "Date")) .
                                            " (CONT)",
                                   $LeftJustify, $MaxLineLength) . "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) . "<br>");
                    $LineCounter++;
                        
                    // print the user's name
                    $FullName = Trim($BillingKeycodeRST[$first_name_offset]) . " " .
                                           Trim($BillingKeycodeRST[$middle_name_offset]) . " " .
                                           Trim($BillingKeycodeRST[$last_name_offset]);
                    PrintNonBreakingString(Space($LeftMargin) .
                                   Space(10) .
                                   JustifyField($FullName, $LeftJustify, $MaxLineLength) . 
                                   "<br>");
                
                    PrintNonBreakingString(" " . "<br>");
                    $LineCounter++;
                    
                    //print the column headers
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                                   JustifyField("DESCRIPTION", $CenterJustify, $ColumnWidth2) . " " .
                                   JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . " " .
                                   JustifyField("RATE", $CenterJustify, $ColumnWidth4) . " " .
                                   JustifyField("TOTAL", $CenterJustify, $ColumnWidth5) . " " .
                                   JustifyField("BALANCE", $CenterJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
                
                // print the flight instruction credit record if there is a credit associated with it
                if ($FlightRST[$Instructor_Charge_offset] < 0) 
                {
                    $Balance = $Balance + (-1 * $FlightRST[$Instructor_Charge_offset]);
                    PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                           JustifyField("FLIGHT INSTRUCTION WITH " . $FlightRST[$Student_Keycode_offset], $LeftJustify, $ColumnWidth2) . " " .
                           JustifyField(FormatField(Str(Val($FlightRST[$Dual_Time_offset]) + Val($FlightRST[$Dual_PP_Time_offset])), "Float"), $RightJustify, $ColumnWidth3) . " " .
                           JustifyField(FormatField(Str(-$FlightRST[$Instruction_Rate_offset]), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                           JustifyField(FormatField(Str(-$FlightRST[$Instructor_Charge_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                           JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . 
                           "<br>");
                    $LineCounter++;
                }
            }
        }
            
        // print the totals
        PrintNonBreakingString(Space($LeftMargin) .
                $UnderLine . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
               JustifyField(" ", $CenterJustify, $ColumnWidth1) . " " .
               JustifyField("TOTALS", $LeftJustify, $ColumnWidth2) . " " .
               JustifyField(" ", $CenterJustify, $ColumnWidth3) . " " .
               JustifyField(" ", $RightJustify, $ColumnWidth4) . " " .
               JustifyField(" ", $RightJustify, $ColumnWidth5) . " " .
               JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . 
               "<br>");
        $LineCounter++;
        
        // if we have credits for this month, display the contract number and
        // approval line
        if ($Balance > 0) 
        {
            // credit due, display credit lines
            PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    "CONTRACT NUMBER: " .
                    Trim($BillingKeycodeRST[$Contract_Number_offset]) . 
                    "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    "APPROVED FOR PAYMENT BY: " . "<br>");
            $LineCounter++;
        
            // signature block. since this may be more than one line, parse the line
            PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
            $LineCounter = $LineCounter + 3;
            $SignatureBlock = GetGeneralPreferenceValue("Signature_Block_Text");
            while (Len($SignatureBlock) > 0) 
            {
                // print each part of the signature block
                $PrintLine = GetNextToken($SignatureBlock, Chr(10));
                if (Right($PrintLine, 1) == Chr(13)) 
                {
                    $PrintLine = Left($PrintLine, Len($PrintLine) - 1);
                }
                if (Len($PrintLine) > 0) 
                { 
                    PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<br>");
                    $LineCounter++;
                }
            }
        }
    }
    
    //********************************************************************
    // PrintSingleAircraftSummary(
    //                        BillingAircraftRST As MYSQL_RS,
    //                        StartDate as string,
    //                        EndDate as string)
    //
    // Purpose:  Print the monthly aircraft summary for
    //           the given aircraft
    //
    // Inputs:
    //   BillingAircraftRST - the recordset to print the billing for
    //   StartDate - the start date to print the billing for
    //   EndDate - the end date to print the billing for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleAircraftSummary(
                                        $BillingAircraftRST,
                                        $StartDate,
                                        $EndDate)
    {                                        
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
                      
        include "DatabaseConstants.inc";

        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 4;
        $ColumnLeft = 25;
        $ColumnRight = 10;
        $ColumnWidth1 = 12;
        $ColumnWidth2 = 35;
        $ColumnWidth3 = 6;
        $ColumnWidth4 = 9;
        $ColumnWidth5 = 9;
        $ColumnWidth6 = 10;
        $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 +
                                $ColumnWidth4 + $ColumnWidth5 + $ColumnWidth6 + 5;
        
        $PageHeader = "**************** " . $AircraftScheduling_company . " ****************";
    
        // printer setup
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
                
        // create a query for the flight charges for the date and aircraft selected
        $FlightResult = SQLOpenRecordset(
            "SELECT * FROM Flight WHERE (" .
                "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                "Flight.Hobbs_Elapsed <> 0 AND " .
                "Flight.Aircraft = '" . $BillingAircraftRST[$n_number_offset] . "')" .
                "ORDER BY Date, End_Hobbs");
                
        // create a query for the maintenance flight charges for the date and aircraft selected
        // the keycode for a maintenance flight is the tail number of the aircraft
        $MaintFlightResult = SQLOpenRecordset(
            "SELECT * FROM Flight WHERE (" .
                "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                "Flight.Keycode = '" . Trim($BillingAircraftRST[$n_number_offset]) . "' AND " .
                "Flight.Hobbs_Elapsed <> 0 AND " .
                "Flight.Aircraft = '" . Trim($BillingAircraftRST[$n_number_offset]) . "')" .
                "ORDER BY Date, End_Hobbs");
                
        // create a query for the misc charges for the date and aircraft selected
        $ChargesResult = SQLOpenRecordset(
            "SELECT * FROM Charges WHERE (" .
                "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                "Charges.Keycode = '" . $BillingAircraftRST[$n_number_offset] . "')" .
                "ORDER BY Date");
        
        // get the beginning and ending numbers
        if (sql_count($FlightResult) > 0) 
        {
            $FlightRST = sql_row($FlightResult, 0);
            $BeginningHobbs = $FlightRST[$Begin_Hobbs_offset];
            $FlightRST = sql_row($FlightResult, (sql_count($FlightResult) - 1));
            $EndingHobbs = $FlightRST[$End_Hobbs_offset];
            $EndingTach = $FlightRST[$End_Tach_offset];
        } 
        else 
        {
            $BeginningHobbs = 0;
            $BeginningHobbs = 0;
            $EndingHobbs = 0;
            $EndingTach = 0;
        }
                     
        // compute the amount of oil and fuel used during the month
        $OilUsed = 0;
        $FuelUsed = 0;
		$NumberOfFlights = 0;
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and add the results
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // if this is an instructor's flight, ignore it
                if ($FlightRST[$Instruction_Type_offset] != $InstructorInstruction) 
                {
                    $NumberOfFlights = $NumberOfFlights + 1;
                    $FuelUsed = $FuelUsed +
                                        $FlightRST[$Local_Fuel_offset] +
                                        $FlightRST[$Cross_Country_Fuel_offset];
                    $OilUsed = $OilUsed + $FlightRST[$Oil_offset];
                }
            }
        }
                     
        // see if we found any maintenanc flight charge records for the given date
        $MaintFlightHours = 0;
        if (sql_count($MaintFlightResult) > 0) 
        {
            // loop through all records and add the results
            for($MaintFlightCnt=0; $MaintFlightRST = sql_row($MaintFlightResult, $MaintFlightCnt); $MaintFlightCnt++)
            {
                $MaintFlightHours = $MaintFlightHours + $MaintFlightRST[$Hobbs_Elapsed_offset];
            }
        }
        
        // compute the reimbursement rates for rental and non-rental aircraft
        If ($BillingAircraftRST[$hourly_cost_offset] ==
            $BillingAircraftRST[$rental_fee_offset])
        {
            // club aircraft, reimbursement rate is 100 % of the rental rate
            $ReimbursementRate = $BillingAircraftRST[$hourly_cost_offset];
            $ActivityRate = $BillingAircraftRST[$hourly_cost_offset];
        } 
        else 
        {
            // non-club aircraft, reimbursement rate is the owner rental rate
            $ReimbursementRate = $BillingAircraftRST[$rental_fee_offset];
            $ActivityRate = $BillingAircraftRST[$hourly_cost_offset] -
                                $BillingAircraftRST[$rental_fee_offset];
        }
        
        $LineCounter = 0;
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
         
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
            
        // print the aircraft information
        PrintNonBreakingString(Space($LeftMargin) .
                       $BillingAircraftRST[$n_number_offset] .
                       " FLIGHT SUMMARY SHEET" . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the month
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("FROM " .
                                UCase(FormatField($StartDate, "Date")) . " TO " .
                                UCase(FormatField($EndDate, "Date")) .
                                " BILLING PERIOD",
                       $LeftJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the reimbursement rate and activity rate
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("REIMBURSEMENT RATE:", $RightJustify, $ColumnLeft) .
                       JustifyField(FormatField(Str($ReimbursementRate), "Currency"), $RightJustify, $ColumnRight) . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("ACTIVITY'S RATE:", $RightJustify, $ColumnLeft) .
                       JustifyField(
                            FormatField(Str($ActivityRate), "Currency"), $RightJustify, $ColumnRight) . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the ending tach
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("ENDING TACH:", $RightJustify, $ColumnLeft) .
                       JustifyField(FormatField(Str($EndingTach), "Float"), $RightJustify, $ColumnRight) . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
            
        // print the hobbs times and the owner's name and address
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("BEGINNING HOBBS:", $RightJustify, $ColumnLeft) .
                       JustifyField(FormatField(Str($BeginningHobbs), "Float"), $RightJustify, $ColumnRight) .
                       Space(15) . $BillingAircraftRST[$Aircraft_Owner_Name_offset] . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("ENDING HOBBS:", $RightJustify, $ColumnLeft) .
                       JustifyField(FormatField(Str($EndingHobbs), "Float"), $RightJustify, $ColumnRight) .
                       Space(15) . $BillingAircraftRST[$Aircraft_Owner_Address_offset] . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("TIME ON AIRCRAFT:", $RightJustify, $ColumnLeft) .
                       JustifyField(
                                FormatField(Str($EndingHobbs - $BeginningHobbs), "Float"),
                                $RightJustify, $ColumnRight) .
                       Space(15) . Trim($BillingAircraftRST[$Aircraft_Owner_City_offset]) . " " .
                                   Trim($BillingAircraftRST[$Aircraft_Owner_State_offset]) . " " .
                                   Trim($BillingAircraftRST[$Aircraft_Owner_Zip_offset]) . "<br>");
        $LineCounter++;
        
        // print the maintenance flight hours
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("MAINTENANCE HRS:", $RightJustify, $ColumnLeft) .
                       JustifyField(
                                FormatField(Str($MaintFlightHours), "Float"),
                                $RightJustify, $ColumnRight) . "<br>");
        $LineCounter++;
        
        // print the revenue hours
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("REVENUE HRS:", $RightJustify, $ColumnLeft) .
                       JustifyField(
                                FormatField(Str($EndingHobbs - $BeginningHobbs - $MaintFlightHours), "Float"),
                                $RightJustify, $ColumnRight) . "<br>");
        $LineCounter++;
        
        // print the rental revenue
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("RENTAL REVENUE:", $RightJustify, $ColumnLeft) .
                       JustifyField(
                                FormatField(Str(($EndingHobbs - $BeginningHobbs - $MaintFlightHours) *
                                    $ReimbursementRate), "Currency"),
                                $RightJustify, $ColumnRight) . "<br>");
        $LineCounter++;
        
        // print the maintenance costs
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("MAINTENANCE COST:", $RightJustify, $ColumnLeft) .
                       JustifyField(
                                FormatField(Str($MaintFlightHours * $ActivityRate), "Currency"),
                                $RightJustify, $ColumnRight) . "<br>");
        $LineCounter++;
        
        // print the balance
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("BALANCE:", $RightJustify, $ColumnLeft) .
                       JustifyField(
                                FormatField(Str(($EndingHobbs - $BeginningHobbs - $MaintFlightHours) * $ReimbursementRate -
                                     ($MaintFlightHours * $ActivityRate)), "Currency"),
                                $RightJustify, $ColumnRight) . "<br>");
        $LineCounter++;
        
        // print the statistics title
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("***** STATISTICS *****", $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
        
        // print the statistics
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("NUMBER OF FLIGHTS: ", $RightJustify, $ColumnLeft) .
                       JustifyField(
                                FormatField(Str($NumberOfFlights), "Integer"),
                                $RightJustify, $ColumnRight) . "<br>");
        $LineCounter++;
        if ($NumberOfFlights > 0) 
        {
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField("AVERAGE LENGTH OF FLIGHT: ", $RightJustify, $ColumnLeft) .
                           JustifyField(
                                    number_format((($EndingHobbs - $BeginningHobbs) / $NumberOfFlights), 2, ".", ""),
                                    $RightJustify, $ColumnRight) . "<br>");
        } 
        else 
        {
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField("AVERAGE LENGTH OF FLIGHT: ", $RightJustify, $ColumnLeft) .
                           JustifyField("0.00", $RightJustify, $ColumnRight) . "<br>");
        }
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
        
        // oil consumed
        if (($EndingHobbs - $BeginningHobbs) > 0) 
        {
            PrintNonBreakingString(Space($LeftMargin) .
                           "OIL CONSUMED: " .
                           FormatField(Str($OilUsed), "Float") .
                           " QUARTS OR " .
                           number_format(($OilUsed / ($EndingHobbs - $BeginningHobbs)), 2, ".", "") . 
                           " QUARTS PER HOUR" . "<br>");
            $LineCounter++;
            
            // fuel consumed
            PrintNonBreakingString(Space($LeftMargin) .
                           "FUEL CONSUMED: " .
                           FormatField(Str($FuelUsed), "Float") .
                           " GALLONS AT " .
                           FormatField(Str(GetGeneralPreferenceValue("Fuel_Cost")), "Currency") . 
                           "/GALLON = " .
                           FormatField(Str($FuelUsed * GetGeneralPreferenceValue("Fuel_Cost")), "Currency") . 
                           " TOTAL COST" .
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "               " .
                           number_format(($FuelUsed / ($EndingHobbs - $BeginningHobbs)), 2, ".", "") . 
                           " GALLONS PER HOUR AT " .
                           FormatField(Str(GetGeneralPreferenceValue("Fuel_Cost")), "Currency") . 
                           "/GALLON = " .
                           FormatField(Str(($FuelUsed / ($EndingHobbs - $BeginningHobbs)) * GetGeneralPreferenceValue("Fuel_Cost")), "Currency") . 
                           " COST PER HOUR" .
                           "<br>");
            $LineCounter++;
            
            // maintenance time
            if (($EndingHobbs - $BeginningHobbs - $MaintFlightHours) == 0)
            {
                // avoid dividing by zero
                PrintNonBreakingString(Space($LeftMargin) .
                               "MAINTENANCE TIME: " .
                               FormatField(Str($MaintFlightHours), "Float") .
                               " HOURS OR " .
                               number_format(0.0, 3, ".", "") .
                               " HOURS MAINTENANCE HOURS PER REVENUE HOUR" . "<br>");
            }
            else
            {
                PrintNonBreakingString(Space($LeftMargin) .
                               "MAINTENANCE TIME: " .
                               FormatField(Str($MaintFlightHours), "Float") .
                               " HOURS OR " .
                               number_format($MaintFlightHours / ($EndingHobbs - $BeginningHobbs - $MaintFlightHours), 3, ".", "") .
                               " HOURS MAINTENANCE HOURS PER REVENUE HOUR" . "<br>");
            }
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) . "<br>");
            $LineCounter++;
        } 
        else 
        {
            // no Elapsed time, don't divide by zero
            // oil consumed
            PrintNonBreakingString(Space($LeftMargin) .
                           "OIL CONSUMED: " .
                           FormatField(Str($OilUsed), "Float") .
                           " QUARTS OR 0.00 QUARTS PER HOUR" . 
                           "<br>");
            $LineCounter++;
            
            // fuel consumed
            PrintNonBreakingString(Space($LeftMargin) .
                           "FUEL CONSUMED: " .
                           FormatField(Str($FuelUsed), "Float") .
                           " GALLONS AT " .
                           FormatField(Str(GetGeneralPreferenceValue("Fuel_Cost")), "Currency") . 
                           "/GALLON = " .
                           FormatField(Str($FuelUsed * GetGeneralPreferenceValue("Fuel_Cost")), "Currency") . 
                           " TOTAL COST" .
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "               " .
                           "0.0 GALLONS PER HOUR AT " .
                           FormatField(Str(GetGeneralPreferenceValue("Fuel_Cost")), "Currency") . 
                           "/GALLON = $0.00 COST PER HOUR" .
                           "<br>");
            $LineCounter++;
            
            // maintenance time
            PrintNonBreakingString(Space($LeftMargin) .
                           "MAINTENANCE TIME: " .
                           FormatField(Str($MaintFlightHours), "Float") .
                           " HOURS OR 0.000 HOURS MAINTENANCE HOURS PER REVENUE HOUR" . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) . "<br>");
            $LineCounter++;
        }
        
        //print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField("DESCRIPTION", $CenterJustify, $ColumnWidth2) . " " .
                       JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . " " .
                       JustifyField("RATE", $CenterJustify, $ColumnWidth4) . " " .
                       JustifyField("TOTAL", $CenterJustify, $ColumnWidth5) . " " .
                       JustifyField("BALANCE", $CenterJustify, $ColumnWidth6) . "<br>");
        $LineCounter++;
        
        // see if we found any misc charge records for the given date
        $Balance = 0;
        if (sql_count($ChargesResult) > 0) 
        {
            // loop through all records and print the results
            for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
            {
                // if we have filled the page, send the page to the printer
                if ($LineCounter > $PageLength) 
                {
                    // at max page length, print the page
                    PrintNewPage();
                    $LineCounter = 0;
                     
                    // skip some space at the top of the form
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                     
                    // print the page header
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($PageHeader, $LeftJustify, $MaxLineLength) . 
                                   "<br>");
                    $LineCounter++;
                    
                    // skip some space below the header
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
    
                    // print the aircraft information
                    PrintNonBreakingString(Space($LeftMargin) .
                                   $BillingAircraftRST[$n_number_offset] .
                                   " FLIGHT SUMMARY SHEET (CONT)" . "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) . "<br>");
                    $LineCounter++;
                        
                    // print the month
                    PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("FROM " .
                                     UCase(FormatField($StartDate, "Date")) . " TO " .
                                     UCase(FormatField($EndDate, "Date")) .
                                     " BILLING PERIOD",
                                   $LeftJustify, $MaxLineLength) . "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) . "<br>");
                    $LineCounter++;
                
                    PrintNonBreakingString(" " . "<br>");
                    $LineCounter++;
                
                    //print the column headers
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                                   JustifyField("DESCRIPTION", $CenterJustify, $ColumnWidth2) . " " .
                                   JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . " " .
                                   JustifyField("RATE", $CenterJustify, $ColumnWidth4) . " " .
                                   JustifyField("TOTAL", $CenterJustify, $ColumnWidth5) . " " .
                                   JustifyField("BALANCE", $CenterJustify, $ColumnWidth6) . "<br>");
                    $LineCounter++;
                }
                
                // print the misc billing record if there is a cost associated with it
                $Balance = $Balance + $ChargesRST[$Total_Price_offset];
                PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField(FormatField($ChargesRST[$ChargesDate_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField($ChargesRST[$Part_Description_offset], $LeftJustify, $ColumnWidth2) . " " .
                       JustifyField(FormatField(Str($ChargesRST[$Quantity_offset]), "Float"), $RightJustify, $ColumnWidth3) . " " .
                       JustifyField(FormatField(Str($ChargesRST[$Price_offset]), "Currency"), $RightJustify, $ColumnWidth4) . " " .
                       JustifyField(FormatField(Str($ChargesRST[$Total_Price_offset]), "Currency"), $RightJustify, $ColumnWidth5) . " " .
                       JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . "<br>");
                $LineCounter++;
            }
        }
            
        // print the totals
        PrintNonBreakingString(Space($LeftMargin) .
                $UnderLine . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
               JustifyField(" ", $CenterJustify, $ColumnWidth1) . " " .
               JustifyField("TOTALS", $LeftJustify, $ColumnWidth2) . " " .
               JustifyField(" ", $CenterJustify, $ColumnWidth3) . " " .
               JustifyField(" ", $RightJustify, $ColumnWidth4) . " " .
               JustifyField(" ", $RightJustify, $ColumnWidth5) . " " .
               JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $ColumnWidth6) . 
               "<br>");
        $LineCounter++;
        
        // display the grand total
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                       "GRAND TOTAL:" . " " .
                       JustifyField(
                                FormatField(Str(($EndingHobbs - $BeginningHobbs - $MaintFlightHours) * $ReimbursementRate -
                                     ($MaintFlightHours * $ActivityRate) - $Balance), "Currency"),
                                $RightJustify, $ColumnRight) . "<br>");
        $LineCounter++;
        
        // display the contract number and approval line
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                "PAY TO CONTRACT NUMBER: " .
                Trim($BillingAircraftRST[$Aircraft_Owner_Contract_offset]) . 
                "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                "APPROVED FOR PAYMENT BY: " . "<br>");
        $LineCounter++;
        
        // signature block. since this may be more than one line, parse the line
        PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
        $LineCounter = $LineCounter + 3;
        $SignatureBlock = GetGeneralPreferenceValue("Signature_Block_Text");
        while (Len($SignatureBlock) > 0) 
        {
            // print each part of the signature block
            $PrintLine = GetNextToken($SignatureBlock, Chr(10));
            if (Right($PrintLine, 1) == Chr(13)) 
            {
                $PrintLine = Left($PrintLine, Len($PrintLine) - 1);
            }
            if (Len($PrintLine) > 0) 
            { 
                PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<br>");
                $LineCounter++;
            }
        }
    }
    
    //********************************************************************
    // PrintClubSummary(
    //                  StartDate as string,
    //                  EndDate as string)
    //
    // Purpose:  Print the monthly club summary
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
    function PrintClubSummary($StartDate, $EndDate)
    {                                  
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
              
        include "DatabaseConstants.inc";

        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 4;
        $ColumnLeft = 40;
        $ColumnRight = 10;
        $ColumnWidth1 = 12;
        $ColumnWidth2 = 35;
        $ColumnWidth3 = 6;
        $ColumnWidth4 = 9;
        $ColumnWidth5 = 9;
        $ColumnWidth6 = 10;
        $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 +
                                $ColumnWidth4 + $ColumnWidth5 + $ColumnWidth6 + 5;
        
        $PageHeader =
            "**************** " . $AircraftScheduling_company . " ****************";
    
        // printer setup
        PrinterSetup(9);
                
        // create a query for the flight charges for the date and for all aircraft
        $FlightResult = SQLOpenRecordset(
            "SELECT * FROM Flight WHERE (" .
                "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "')" .
                "ORDER BY Date, End_Hobbs");
                     
        // compute the amount of fuel purchased during the month
        $FuelPurchased = 0;
        $TotalFuelUsed = 0;
        $CrossCountryFuel = 0;
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and add the results
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // if this is an instructor's flight, ignore it
                if ($FlightRST[$Instruction_Type_offset] != $InstructorInstruction) 
                {
                    // is this a fuel purchase by non-club aircraft?
                    if ($FlightRST[$Fuel_Cost_offset] > 0) 
                    {
                        $FuelPurchased = $FuelPurchased +
                                        $FlightRST[$Local_Fuel_offset];
                    } 
                    else 
                    {
                        $TotalFuelUsed = $TotalFuelUsed +
                                        $FlightRST[$Local_Fuel_offset];
                    }
                    
                    // add in any cross country fuel
                    $CrossCountryFuel = $CrossCountryFuel +
                                        $FlightRST[$Cross_Country_Fuel_offset];
                }
            }
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
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
            
        // print the title information
        PrintNonBreakingString(Space($LeftMargin) .
                       "SUMMARY OF FUEL ACTIVITY FROM " .
                                UCase(FormatField($StartDate, "Date")) . " TO " .
                                UCase(FormatField($EndDate, "Date")) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        
        // print the total fuel pumped
        PrintNonBreakingString(Space($LeftMargin) .
               JustifyField("FUEL PUMPED FROM TANK (GALS):", $LeftJustify, $ColumnLeft) . " " .
               JustifyField(FormatField($FuelPurchased + $TotalFuelUsed, "Float"), $RightJustify, $ColumnRight) . 
               "<br>");
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        
        // print the fuel purchased
        PrintNonBreakingString(Space($LeftMargin) .
               JustifyField("FUEL PURCHASED (GALS):", $LeftJustify, $ColumnLeft) . " " .
               JustifyField(FormatField($FuelPurchased, "Float"), $RightJustify, $ColumnRight) . 
               "<br>");
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        
        // print the fuel purchased on cross country
        PrintNonBreakingString(Space($LeftMargin) .
               JustifyField("CROSS COUNTRY FUEL PURCHASED (GALS):", $LeftJustify, $ColumnLeft) . " " .
               JustifyField(FormatField($CrossCountryFuel, "Float"), $RightJustify, $ColumnRight) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        
        // print the fuel consumed by club aircraft
        PrintNonBreakingString(Space($LeftMargin) .
               JustifyField("FUEL USED BY CLUB AIRCRAFT (GALS):", $LeftJustify, $ColumnLeft) . " " .
               JustifyField(FormatField($TotalFuelUsed, "Float"), $RightJustify, $ColumnRight) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        
        // complete the print
        PrintNewPage();
    }
    
    //********************************************************************
    // PrintCrossCountryFuelSummary(
    //                  StartDate as string,
    //                  EndDate as string)
    //
    // Purpose:  Print the monthly cross country fuel summary
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
    function PrintCrossCountryFuelSummary($StartDate, $EndDate)
    {                                  
        global $AircraftScheduling_company;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        global $RightJustify, $LeftJustify, $CenterJustify;
              
        include "DatabaseConstants.inc";

        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 4;
        $ColumnWidth1 = 12;
        $ColumnWidth2 = 12;
        $ColumnWidth3 = 6;
        $ColumnWidth4 = 9;
        $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 +
                                $ColumnWidth4 + 3;
        
        $PageHeader = "******* " . $AircraftScheduling_company . " *******";
   
        // printer setup
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
                
        // create a query for the flight charges for the date and for all aircraft with
        // cross country fuel charges
        $FlightResult = SQLOpenRecordset(
            "SELECT * FROM Flight WHERE (" .
                "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                "Flight.Cross_Country_Fuel_Credit <> 0) " .
                "ORDER BY Date, End_Hobbs");
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
         
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
            
        // print the title information
        PrintNonBreakingString(Space($LeftMargin) .
                       "CROSS COUNTRY REIMBURSEMENTS FROM " .
                                UCase(FormatField($StartDate, "Date")) . " TO " .
                                UCase(FormatField($EndDate, "Date")) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
                     
        // print the cross country fuel purchased during the month
        $CrossCountryFuelGallons = 0;
        $CrossCountryFuelCost = 0;
        if (sql_count($FlightResult) > 0) 
        {
            //print the column headers
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField("DATE", $CenterJustify, $ColumnWidth1) . " " .
                           JustifyField("KEY", $CenterJustify, $ColumnWidth2) . " " .
                           JustifyField("GALLONS", $CenterJustify, $ColumnWidth3) . " " .
                           JustifyField("AMOUNT", $CenterJustify, $ColumnWidth4) . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                    $UnderLine . "<br>");
            
            // loop through all records and print a line for each record
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // if this is an instructor's flight, ignore it
                if ($FlightRST[$Instruction_Type_offset] != $InstructorInstruction) 
                {
                    // add in any cross country fuel
                    $CrossCountryFuelGallons = $CrossCountryFuelGallons +
                                        $FlightRST[$Cross_Country_Fuel_offset];
                    $CrossCountryFuelCost = $CrossCountryFuelCost +
                                        $FlightRST[$Cross_Country_Fuel_Credit_offset];
                    
                    //print the cross country fuel information
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth1) . " " .
                                   JustifyField($FlightRST[$Keycode_offset], $CenterJustify, $ColumnWidth2) . " " .
                                   JustifyField(FormatField($FlightRST[$Cross_Country_Fuel_offset], "Float"), $RightJustify, $ColumnWidth3) . " " .
                                   JustifyField(FormatField(Str(-$FlightRST[$Cross_Country_Fuel_Credit_offset]), "Currency"), $RightJustify, $ColumnWidth4) . 
                                   "<br>");
                }
            }
        }
                    
        //print the totals
        PrintNonBreakingString(Space($LeftMargin) .
                $UnderLine . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("TOTALS", $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField(" ", $CenterJustify, $ColumnWidth2) . " " .
                       JustifyField(FormatField(Str($CrossCountryFuelGallons), "Float"), $RightJustify, $ColumnWidth3) . " " .
                       JustifyField(FormatField(Str(-$CrossCountryFuelCost), "Currency"), $RightJustify, $ColumnWidth4) . 
                       "<br>");
        
        // complete the print
        PrintNewPage();
    }
    
    //********************************************************************
    // PrintMonthlyMemberSummary(
    //                        StartDate as string,
    //                        EndDate as string)
    //
    // Purpose:  Print the monthly user summary
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
    function PrintMonthlyMemberSummary($StartDate, $EndDate)
    {                                        
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
              
        include "DatabaseConstants.inc";

        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 2;
        $ColumnLeft = 40;
        $ColumnRight = 10;
        $ColumnWidth1 = 14;
        $ColumnWidth2 = 50;
        $ColumnWidth3 = 12;
        $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 + 2;
        
        $PageHeader =
            "**************** " . $AircraftScheduling_company . " ****************";

        // printer setup
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0; $i < $MaxLineLength; $i++) 
        {
            $UnderLine = $UnderLine . "_";
        }
                
        // create a query for the users in last name order
        $MembersResult = SQLOpenRecordset(
            "SELECT * FROM AircraftScheduling_person ORDER BY username");
         
        $LineCounter = 0;
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
         
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
            
        // print the month
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("CHARGES FROM " .
                                UCase(FormatField($StartDate, "Date")) . " TO " .
                                UCase(FormatField($EndDate, "Date")),
                       $CenterJustify, $MaxLineLength) . "<br>");
        $LineCounter++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $LineCounter++;
        }
        
        //print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("SSN", $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField("NAME", $CenterJustify, $ColumnWidth2) . " " .
                       JustifyField("CHARGE", $CenterJustify, $ColumnWidth3) . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . $UnderLine . "<br>");
        $LineCounter++;
                     
        // print total for each user for the month
        $TotalMonthlyCharges = 0;
        if (sql_count($MembersResult) > 0) 
        {
            // loop through all records and print a summary line for each user
            for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++)
            {
                // if we have filled the page, send the page to the printer
                if ($LineCounter > $PageLength) 
                {
                    // at max page length, print the page
                    PrintNewPage();
                    $LineCounter = 0;
                     
                    // skip some space at the top of the form
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                     
                    // print the page header
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                                   "<br>");
                    $LineCounter++;
                        
                    // print the month
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("CHARGES FROM " .
                                            UCase(FormatField($StartDate, "Date")) . " TO " .
                                            UCase(FormatField($EndDate, "Date")) .
                                            " (CONT)",
                                   $CenterJustify, $MaxLineLength) . "<br>");
                    $LineCounter++;
                    
                    // skip some space below the header
                    for ($i = 0; $i < $HeaderLength; $i++) 
                    {
                        PrintNonBreakingString(" " . "<br>");
                        $LineCounter++;
                    }
                    
                    //print the column headers
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("SSN", $CenterJustify, $ColumnWidth1) . " " .
                                   JustifyField("NAME", $CenterJustify, $ColumnWidth2) . " " .
                                   JustifyField("CHARGE", $CenterJustify, $ColumnWidth3) . 
                                   "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) . $UnderLine . "<br>");
                    $LineCounter++;
                }
                
                // if this is a keycode for an aircraft or for cash, ignore the cost
                If (!IsAircraftID($MembersRST[$username_offset]) &&
                    UCase(Trim($MembersRST[$username_offset])) != "CASH" &&
                    UCase(Trim($MembersRST[$username_offset])) != "OPEN")
                {                    
                    // get the total for this user for the month
                    $MemberMonthlyCharge = ComputeMemberTotalMonthlyCharge(
                                                    $MembersRST[$username_offset],
                                                    $StartDate,
                                                    $EndDate);
                    
                    // add to the montly club total
                    $TotalMonthlyCharges = $TotalMonthlyCharges + $MemberMonthlyCharge;
                    
                    //print the total charge for each user that has a charge or credit
                    if ($MemberMonthlyCharge != 0) 
                    {
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField(FormatSSNString(DecryptString($MembersRST[$SSN_offset])), $CenterJustify, $ColumnWidth1) . " " .
                                       JustifyField(Trim($MembersRST[$first_name_offset]) . " " .
                                                    Trim($MembersRST[$middle_name_offset]) . " " .
                                                    Trim($MembersRST[$last_name_offset]),
                                                    $LeftJustify, $ColumnWidth2) . " " .
                                       JustifyField(FormatField(Str($MemberMonthlyCharge), "Currency"), $RightJustify, $ColumnWidth3) . 
                                       "<br>");
                        $LineCounter++;
                    }
                }
            }
        }
                    
        //print the totals
        PrintNonBreakingString(Space($LeftMargin) . $UnderLine . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("TOTALS", $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField(" ", $CenterJustify, $ColumnWidth2) . " " .
                       JustifyField(FormatField(Str($TotalMonthlyCharges), "Currency"), $RightJustify, $ColumnWidth3) . 
                       "<br>");
        $LineCounter++;
    
        // complete the print
        PrintNewPage();
    }
    
    //********************************************************************
    // PrintMonthlyBills($StartDate, $EndDate, $StartName, $EndName)
    //
    // Purpose:  Print the monthly billing information for the keycodes
    //           specified by the user.
    //
    // Inputs:
    //   StartDate - the start date to print the billing for
    //   EndDate - the end date to print the billing for
    //   StartName - the starting name for the billing information
    //   EndName - the end name for the billing information
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMonthlyBills($StartDate, $EndDate, $StartName, $EndName)
    {
        global $DatabaseNameFormat;
        
        // create a query for the user requested
        $MembersKeycodeResult = SQLOpenRecordset(
            "SELECT * FROM AircraftScheduling_person " . 
            "WHERE (CONCAT(last_name, ' ', first_name) >= '" . 
                        GetLastName($StartName) . " " . GetFirstName($StartName) . "' AND " .
                    "CONCAT(last_name, ' ', first_name) <='" . 
                        GetLastName($EndName) . " " . GetFirstName($EndName) . "') " .
                "ORDER BY last_name");
    
        // did we find any records?
        if (sql_count($MembersKeycodeResult) == 0) 
        {
            // no records found, exit
            return;
        } 
        else 
        {
            for($MembersKeycodeCnt=0; $MembersKeycodeRST = sql_row($MembersKeycodeResult, $MembersKeycodeCnt); $MembersKeycodeCnt++)
            {
                // make sure we don't time out
                set_time_limit(30);
                
                // print all the keycodes requested
                PrintSingleKeycodeBill(
                                $MembersKeycodeRST,
                                $StartDate,
                                $EndDate,
                                $StartNewPage);
                
                // if we are at the end of the record set, don't print an
                // new page (the EndDoc will print the last page)
                if ($MembersKeycodeCnt < (sql_count($MembersKeycodeResult) - 1) && $StartNewPage) 
                {
                    // finish the page
                    PrintNewPage();
                }
            }
        
            // complete the print job
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintMonthlyCredits($StartDate, $EndDate, $StartName, $EndName)
    //
    // Purpose:  Print the monthly credit information for the keycodes
    //           specified by the user.
    //
    // Inputs:
    //   StartDate - the starting date for the billing information
    //   EndDate - the end date for the billing information
    //   StartName - the starting name for the billing information
    //   EndName - the end name for the billing information
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMonthlyCredits($StartDate, $EndDate, $StartName, $EndName)
    {
        global $DatabaseNameFormat;
        
        // create a query with the keycodes as the query identifier
        $MembersKeycodeResult = SQLOpenRecordset(
            "SELECT * FROM AircraftScheduling_person " . 
            "WHERE (last_name >= '" . GetLastName($StartName) . "' AND " .
                "last_name <= '" . GetLastName($EndName) . "') " .
                "ORDER BY last_name");
    
        // did we find any records?
        if (sql_count($MembersKeycodeResult) == 0) 
        {
            // no records found, exit
            return;
        } 
        else 
        {
            for($MembersKeycodeCnt=0; $MembersKeycodeRST = sql_row($MembersKeycodeResult, $MembersKeycodeCnt); $MembersKeycodeCnt++)
            {
                // print all the keycodes requested
                PrintSingleKeycodeCredit(
                                $MembersKeycodeRST,
                                $StartDate,
                                $EndDate,
                                $StartNewPage);
                
                // if we are at the end of the record set, don't print an
                // new page (the EndDoc will print the last page)
                if ($MembersKeycodeCnt < (sql_count($MembersKeycodeResult) - 1) && $StartNewPage) 
                {
                    // finish the page
                    PrintNewPage();
                }
            }
        
            // complete the print job
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintMonthlyAircraftSummary($StartDate, $EndDate, $AircraftID)
    //
    // Purpose:  Print the monthly aircraft summary information for the
    //           aircraft specified by the user.
    //
    // Inputs:
    //   StartDate - the start date to print the billing for
    //   EndDate - the end date to print the billing for
    //   AircraftID - the aircraft ID for the summary information
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMonthlyAircraftSummary($StartDate, $EndDate, $AircraftID)
    {
        // Open Recordset for the aircraft database
        $AircraftResult = SQLOpenRecordset(
                    "SELECT * FROM AircraftScheduling_aircraft WHERE n_number='" .
                    Trim($AircraftID) . "'");
        
        // see if we found a valid aircraft ID
        if (sql_count($AircraftResult) == 0) 
        {
            // no records found, exit
            return;
        } 
        else 
        {
            // print the aircraft summary
            $AircraftRST = sql_row($AircraftResult, 0);
            PrintSingleAircraftSummary(
                                        $AircraftRST,
                                        $StartDate,
                                        $EndDate);
        
            // complete the print job
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintAllMonthlyAircraftSummary($StartDate, $EndDate, )
    //
    // Purpose:  Print all the monthly aircraft summaries.
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
    function PrintAllMonthlyAircraftSummary($StartDate, $EndDate)
    {
              
        include "DatabaseConstants.inc";

        // Open Recordset from the database
        $AircraftResult = SQLOpenRecordset(
                        "SELECT * FROM AircraftScheduling_aircraft ORDER BY n_number");
    
        // did we find any records?
        if (sql_count($AircraftResult) == 0) 
        {
            // no records found, exit
            return;
        }
        else 
        {
            for($AircraftCnt=0; $AircraftRST = sql_row($AircraftResult, $AircraftCnt); $AircraftCnt++)
            {
                // if this is a non-rental aircraft, ignore it
                if ($AircraftRST[$hourly_cost_offset] > 0) 
                {
                    // print all the aircraft requested
                    PrintSingleAircraftSummary($AircraftRST, $StartDate, $EndDate);
                }
                
                // if we are at the end of the record set, don't print an
                // new page (the EndDoc will print the last page)
                if ($AircraftCnt < (sql_count($AircraftResult) - 1)) 
                {
                    if ($AircraftRST[$hourly_cost_offset] > 0) 
                    {
                        // finish the page
                        PrintNewPage();
                    }
                }
            }
        
            // complete the print job
            PrintNewPage();
        }
    }

    //********************************************************************
    // PrintSelectedMonthlySummary(
    //                            StartDate As String, EndDate As String)
    //
    // Purpose:  Print the montly billing information for the dates
    //           specified by the user if the options are enabled
    //           by the user.
    //
    // Inputs:
    //   StartDate - the starting date for the billing information
    //   EndDate - the end date for the billing information
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSelectedMonthlySummary($StartDate, $EndDate)
    {        
        global $ActivitySummaryOptions;
        global $DAROptions;
        global $DetailedDAROptions;
        global $MonthlyBillingOptions;
        global $MonthlyCreditOptions;
        global $AircraftSummaryOption;
        
        global $BillsFromNameOfUser;
        global $BillsToNameOfUser;
        global $CreditsFromNameOfUser;
        global $CreditsToNameOfUser;
        global $TailNumber;
    
        // handle printing of the monthly summaries
        if ($ActivitySummaryOptions == 1)
        {
            PrintClubSummary($StartDate, $EndDate);
            PrintCrossCountryFuelSummary($StartDate, $EndDate);
            PrintMonthlyMemberSummary($StartDate, $EndDate);
        }
        
        // handle printing of the DAR report
        if ($DAROptions == 1)
        {
            PrintDARReport($StartDate, $EndDate, $StartDate, $EndDate);
        
            // complete the print job
            PrintNewPage();
        }
        
        // handle printing of the DAR detail report
        if ($DetailedDAROptions == 1)
        {
            PrintDARDetailReport($StartDate, $EndDate, $StartDate, $EndDate);
            
            // complete the print job
            PrintNewPage();
        }
        
        // handle printing of the monthly user bills
        if ($MonthlyBillingOptions == 1)
        {
            // print selected user's monthly bills
            PrintMonthlyBills(
                                $StartDate, 
                                $EndDate, 
                                $BillsFromNameOfUser, 
                                $BillsToNameOfUser);
        }
        
        // handle printing of the monthly user credits
        if ($MonthlyCreditOptions == 1)
        {
            // print selected user's monthly credits
            PrintMonthlyCredits(
                                $StartDate, 
                                $EndDate, 
                                $CreditsFromNameOfUser, 
                                $CreditsToNameOfUser);
        }
        
        // handle printing of the monthly aircraft summaries
        if ($AircraftSummaryOption == 1)
        {
            // if all is selected, print all the summaries otherwise
            // print the requested aircraft summary
            if (UCase($TailNumber) == "ALL")
            {
                // print all aircraft monthly smmaries
                PrintAllMonthlyAircraftSummary($StartDate, $EndDate);
            }
            else
            {
                // print the selected aircraft monthly summaries
                PrintMonthlyAircraftSummary($StartDate, $EndDate, $TailNumber);
            }
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
    if(count($_POST) > 0 && $PrintMonthlyBillingInformation == "Submit") 
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
        // interfere with the header functions
        require_once("PrintFunctions.inc");
        
        // setup the print functions
        SetupPrintFunctions($ReturnURL);
        
        // generate the start and end dates
        $StartDate = BuildDate($Fromday, $Frommonth, $Fromyear);
        $EndDate = BuildDate($Today, $Tomonth, $Toyear);
        
        // print the requested montly billing
        PrintSelectedMonthlySummary($StartDate, $EndDate);
        
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
	echo "<FORM NAME='main' ACTION='PrintMonthlyBillingInformation.php' METHOD='POST'>";
	
	// set the default print selections
    $ActivitySummaryOptions = 1;
	$DAROptions = 1;
    $DetailedDAROptions = 1;
    $MonthlyBillingOptions = 1;
    $MonthlyCreditOptions = 1;
    $AircraftSummaryOption = 1;

    // start the table to display the report information
    echo "<center>";
    echo "<h2>Print Monthly Billing Information</h2>";
    echo "<table border=0>";
    
    // date selection
    echo "<tr>";
    echo "<td class=CC colspan=2>Print From:&nbsp;";
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
     
    // Print Activity Summary
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=ActivitySummaryOptions value=1 ";
	if ($ActivitySummaryOptions == 1) echo "checked";
	echo ">Print Activity Summary";
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
          
    // Print Monthly Billing for key codes
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=MonthlyBillingOptions value=1 ";
	if ($MonthlyBillingOptions == 1) echo "checked";
	echo ">Print Monthly Billing for Users";
    echo "</td>";
    echo "<td class=CL>";
	BuildMemberSelector($BillsFromNameOfUser, false, "BillsFrom");
	echo "&nbsp;To&nbsp;";
	BuildMemberSelector($BillsToNameOfUser, false, "BillsTo");
    echo "</td>";
    echo "</tr>";
    
    // Print Monthly Credit for key codes
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=MonthlyCreditOptions value=1 ";
	if ($MonthlyCreditOptions == 1) echo "checked";
	echo ">Print Monthly Credit for Users";
    echo "</td>";
    echo "<td class=CL>";
	BuildMemberSelector($CreditsFromNameOfUser, false, "CreditsFrom");
	echo "&nbsp;To&nbsp;";
	BuildMemberSelector($CreditsToNameOfUser, false, "CreditsTo");
    echo "</td>";
    echo "</tr>";
    
    // Print Summary for Aircraft ID
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=AircraftSummaryOption value=1 ";
	if ($AircraftSummaryOption == 1) echo "checked";
	echo ">Print Summary for Aircraft ID";
    echo "</td>";
    echo "<td class=CL>";
	BuildAircraftSelector($TailNumber, true);
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
    echo "<TD><input name='PrintMonthlyBillingInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
