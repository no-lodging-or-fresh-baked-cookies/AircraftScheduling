<?php
//-----------------------------------------------------------------------------
// 
// PrintDailyBillingInformation.php
// 
// PURPOSE: Print daily billing information in various ways.
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
//      PrintDailyBillingInformation - set to submit to print aircraft information
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
    $default_fault_days = 1;
	$FaultTime = mktime(0, 0, 0, date("m"), date("d") - $default_fault_days, date("Y"));
	$Fromday = date("d", $FaultTime);
	$Frommonth = date("m", $FaultTime);
	$Fromyear = date("Y", $FaultTime);
	$Today   = date("d", $FaultTime);
	$Tomonth = date("m", $FaultTime);
	$Toyear  = date("Y", $FaultTime);
    $DailyAircraftChargesOptions = 0;
    $PrintDailyAircraftRevenueOptions = 0;
    $PrintDailyFlightInstructionInformationOptions = 0;
    $PrintDailyFuelOilChargesOptions = 0;
    $Daily100HrSquawkSummaryOptions = 0;
    $DailyAircraftFaultRecordsOptions = 0;
    $DailyMemberChargesOptions = 0;
    
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
    if(isset($rdata["PrintDailyBillingInformation"])) $PrintDailyBillingInformation = $rdata["PrintDailyBillingInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    if(isset($rdata["Fromday"])) $Fromday = $rdata["Fromday"];
    if(isset($rdata["Frommonth"])) $Frommonth = $rdata["Frommonth"];
    if(isset($rdata["Fromyear"])) $Fromyear = $rdata["Fromyear"];
    if(isset($rdata["Today"])) $Today = $rdata["Today"];
    if(isset($rdata["Tomonth"])) $Tomonth = $rdata["Tomonth"];
    if(isset($rdata["Toyear"])) $Toyear = $rdata["Toyear"];
    if(isset($rdata["DailyAircraftChargesOptions"])) $DailyAircraftChargesOptions = $rdata["DailyAircraftChargesOptions"];
    if(isset($rdata["PrintDailyAircraftRevenueOptions"])) $PrintDailyAircraftRevenueOptions = $rdata["PrintDailyAircraftRevenueOptions"];
    if(isset($rdata["PrintDailyFlightInstructionInformationOptions"])) $PrintDailyFlightInstructionInformationOptions = $rdata["PrintDailyFlightInstructionInformationOptions"];
    if(isset($rdata["PrintDailyFuelOilChargesOptions"])) $PrintDailyFuelOilChargesOptions = $rdata["PrintDailyFuelOilChargesOptions"];
    if(isset($rdata["Daily100HrSquawkSummaryOptions"])) $Daily100HrSquawkSummaryOptions = $rdata["Daily100HrSquawkSummaryOptions"];
    if(isset($rdata["DailyAircraftFaultRecordsOptions"])) $DailyAircraftFaultRecordsOptions = $rdata["DailyAircraftFaultRecordsOptions"];
    if(isset($rdata["DailyMemberChargesOptions"])) $DailyMemberChargesOptions = $rdata["DailyMemberChargesOptions"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // PrintSingleDayAircraftSummary(
    //                           BillingDate As String,
    //                           LineCounter As Integer)
    //
    // Purpose:  Print the daily aircraft billing and credit information for
    //           the given date
    //
    // Inputs:
    //   BillingDate - the date to print the billing for
    //   LineCounter - counter for number of lines (used to start a new page)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleDayAircraftSummary($BillingDate, &$LineCounter)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;

        // flight recordset fields
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 102;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 2;
        $AircraftFieldWidth = 8;
        $DateFieldWidth = 12;
        $KeycodeFieldWidth = 10;
        $BeginHobbFieldWidth = 7;
        $EndHobbFieldWidth = 7;
        $BeginTachFieldWidth = 7;
        $BeginTachFieldWidth = 7;
        $TimeFieldWidth = 6;
        $ChargeFieldWidth = 9;
        $ReimburseFieldWidth = 9;
        $NetFieldWidth = 9;
        
        $PageHeader = "************ " . $AircraftScheduling_company . " ************";
           
        // printer setup
        PrinterSetup(9);
        
        // build the underline string
        $UnderLine = "";
        for ($i = 0;  $i < $MaxLineLength; $i++)
        {
            $UnderLine = $UnderLine . "_";
        }
    
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
            
        // print the sub header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DAILY AIRCRAFT CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength)
                       . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
                
        // create a query with the billing date as the query identifier
    	$FlightSQL = 
                "SELECT * FROM Flight WHERE (Flight.Date = '" . FormatField($BillingDate, "DatabaseDate") . "') " .
                    "ORDER BY Aircraft";
    	$FlightResult = sql_query($FlightSQL);
         
        // if we have any errors
        if(!$FlightResult) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDayAircraftSummary", $FlightSQL);
        }
                
        //print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                       JustifyField("DATE", $CenterJustify, $DateFieldWidth) . " " .
                       JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldWidth) . " " .
                       JustifyField("BG HOBBS", $CenterJustify, $BeginHobbFieldWidth) . " " .
                       JustifyField("ED HOBBS", $CenterJustify, $EndHobbFieldWidth) . " " .
                       JustifyField("BG TACH", $CenterJustify, $BeginTachFieldWidth) . " " .
                       JustifyField("ED TACH", $CenterJustify, $BeginTachFieldWidth) . " " .
                       JustifyField("TIME", $CenterJustify, $TimeFieldWidth) . " " .
                       JustifyField("CHARGE", $CenterJustify, $ChargeFieldWidth) . " " .
                       JustifyField("REIMBURSE", $CenterJustify, $ReimburseFieldWidth) . " " .
                       JustifyField("NET", $CenterJustify, $NetFieldWidth)
                       . "<br>");
        $LineCounter++;
                     
        // see if we found any flight records for the given date
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and print the results
            $TotalTime = 0;
            $TotalCost = 0;
            $TotalReimburseAmount = 0;
            $TotalNetAmount = 0;
		    for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++) 
            {
                // if this was the instructors flight hours, ignore it (since the
                // student pays the bill, not the instructor)
                if ($FlightRST[$Instruction_Type_offset] != $InstructorInstruction) 
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
                             PrintNonBreakingString("<br>");
                             $LineCounter++;
                         }
                          
                         // print the page header
                         PrintNonBreakingString(Space($LeftMargin) .
                                        JustifyField(PageHeader, $CenterJustify, $MaxLineLength)
                                        . "<br>");
                         $LineCounter++;
                         PrintNonBreakingString(Space($LeftMargin) .
                                        JustifyField("DAILY AIRCRAFT CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength)
                                        . "<br>");
                         $LineCounter++;
                        
                         // skip some space below the header
                         for ($i = 0; $i < $HeaderLength; $i++)
                         {
                             PrintNonBreakingString("<br>");
                             $LineCounter++;
                         }
                        
                        //print the column headers
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                                       JustifyField("DATE", $CenterJustify, $DateFieldWidth) . " " .
                                       JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldWidth) . " " .
                                       JustifyField("BG HOBBS", $CenterJustify, $BeginHobbFieldWidth) . " " .
                                       JustifyField("ED HOBBS", $CenterJustify, $EndHobbFieldWidth) . " " .
                                       JustifyField("BG TACH", $CenterJustify, $BeginTachFieldWidth) . " " .
                                       JustifyField("ED TACH", $CenterJustify, $BeginTachFieldWidth) . " " .
                                       JustifyField("TIME", $CenterJustify, $TimeFieldWidth) . " " .
                                       JustifyField("CHARGE", $CenterJustify, $ChargeFieldWidth) . " " .
                                       JustifyField("REIMBURSE", $CenterJustify, $ReimburseFieldWidth) . " " .
                                       JustifyField("NET", $CenterJustify, $NetFieldWidth)
                                       . "<br>");
                        $LineCounter++;
                    }
                    
                    // get the aircraft cost and reimbursement amounts
                    $ElapsedHobbs = $FlightRST[$End_Hobbs_offset] - $FlightRST[$Begin_Hobbs_offset];
                    $AircraftCost = $FlightRST[$Aircraft_Cost_offset];
                    $ReimburseAmount = $FlightRST[$Owner_Reimbursement_offset];
                    $NetAmount = $AircraftCost - $ReimburseAmount;
                    
                    // add amounts to the running totals
                    $TotalTime = $TotalTime + $ElapsedHobbs;
                    $TotalCost = $TotalCost + $AircraftCost;
                    $TotalReimburseAmount = $TotalReimburseAmount + $ReimburseAmount;
                    $TotalNetAmount = $TotalNetAmount + $NetAmount;
                    
                    // print the aircraft record
                    PrintNonBreakingString(Space($LeftMargin) .
                                    JustifyField($FlightRST[$Aircraft_offset], $CenterJustify, $AircraftFieldWidth) . " " .
                                    JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $DateFieldWidth) . " " .
                                    JustifyField($FlightRST[$Keycode_offset], $CenterJustify, $KeycodeFieldWidth) . " " .
                                    JustifyField(FormatField($FlightRST[$Begin_Hobbs_offset], "Float"), $RightJustify, $BeginHobbFieldWidth) . " " .
                                    JustifyField(FormatField($FlightRST[$End_Hobbs_offset], "Float"), $RightJustify, $EndHobbFieldWidth) . " " .
                                    JustifyField(FormatField($FlightRST[$Begin_Tach_offset], "Float"), $RightJustify, $BeginTachFieldWidth) . " " .
                                    JustifyField(FormatField($FlightRST[$End_Tach_offset], "Float"), $RightJustify, $BeginTachFieldWidth) . " " .
                                    JustifyField(FormatField(Str($ElapsedHobbs), "Float"), $RightJustify, $TimeFieldWidth) . " " .
                                    JustifyField(FormatField(Str($AircraftCost), "Currency"), $RightJustify, $ChargeFieldWidth) . " " .
                                    JustifyField(FormatField(Str($ReimburseAmount), "Currency"), $RightJustify, $ReimburseFieldWidth) . " " .
                                    JustifyField(FormatField(Str($NetAmount), "Currency"), $RightJustify, $NetFieldWidth) . 
                                    "<br>");
                    $LineCounter++;
                }
            }
            
            // print the totals
            PrintNonBreakingString(Space($LeftMargin) .
                    " " . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("TOTALS", $CenterJustify, $AircraftFieldWidth) . " " .
                    JustifyField(" ", $CenterJustify, $DateFieldWidth) . " " .
                    JustifyField(" ", $CenterJustify, $KeycodeFieldWidth) . " " .
                    JustifyField(" ", $RightJustify, $BeginHobbFieldWidth) . " " .
                    JustifyField(" ", $RightJustify, $EndHobbFieldWidth) . " " .
                    JustifyField(" ", $RightJustify, $BeginTachFieldWidth) . " " .
                    JustifyField(" ", $RightJustify, $BeginTachFieldWidth) . " " .
                    JustifyField(FormatField(Str($TotalTime), "Float"), $RightJustify, $TimeFieldWidth) . " " .
                    JustifyField(FormatField(Str($TotalCost), "Currency"), $RightJustify, $ChargeFieldWidth) . " " .
                    JustifyField(FormatField(Str($TotalReimburseAmount), "Currency"), $RightJustify, $ReimburseFieldWidth) . " " .
                    JustifyField(FormatField(Str($TotalNetAmount), "Currency"), $RightJustify, $NetFieldWidth)
                    . "<br>");
            $LineCounter++;
        }
    }
    
    //********************************************************************
    // PrintSingleDayMemberSummary(
    //                           BillingDate As String,
    //                           LineCounter As Integer)
    //
    // Purpose:  Print the daily member billing and credit information for
    //           the given date
    //
    // Inputs:
    //   BillingDate - the date to print the billing for
    //   LineCounter - counter for number of lines (used to start a new page)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleDayMemberSummary($BillingDate, &$LineCounter)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 100;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 1;
        
        $PageHeader = "************ " . $AircraftScheduling_company . " ************";
        
        // print field lenghts
        $KeycodeFieldLength = 8;
        $PartNumberLength = 10;
        $DescriptionLength = 18;
        $QuantityLength = 4;
        $RetailPriceLength = 8;
        $TotalPriceLength = 8;
        $WholesalePriceLength = 9;
        $TotalWholesalePriceLength = 8;
        $CategoryLength = 20;
        
        // printer setup
        PrinterSetup(9);
        
        // build the underLine string
        $UnderLine = "";
        for ($i = 0;  $i < $MaxLineLength; $i++)
        {
            $UnderLine = $UnderLine . "_";
        }
    
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
            
        // print the sub header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DAILY MEMBER CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength)
                       . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
                
        // create a query with the billing date as the query identifier
    	$ChargesSQL = 
                "SELECT * FROM Charges WHERE (Charges.Date = '" . FormatField($BillingDate, "DatabaseDate") . "') " .
                    "ORDER BY Keycode";
    	$ChargesResult = sql_query($ChargesSQL);
         
        // if we have any errors
        if(!$ChargesResult) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDayMemberSummary", $ChargesSQL);
        }
                
        //print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
            JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldLength) . " " .
            JustifyField("PART", $CenterJustify, $PartNumberLength) . " " .
            JustifyField("DESCRIPTION", $CenterJustify, $DescriptionLength) . " " .
            JustifyField("QNTY", $CenterJustify, $QuantityLength) . " " .
            JustifyField("WHOLESALE", $CenterJustify, $WholesalePriceLength) . " " .
            JustifyField("TOTAL", $CenterJustify, $TotalWholesalePriceLength) . " " .
            JustifyField("RETAIL", $CenterJustify, $RetailPriceLength) . " " .
            JustifyField("TOTAL", $CenterJustify, $TotalPriceLength) . " " .
            JustifyField("CATEGORY", $CenterJustify, $CategoryLength)
            . "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
            JustifyField("    ", $CenterJustify, $KeycodeFieldLength) . " " .
            JustifyField("NUMBER", $CenterJustify, $PartNumberLength) . " " .
            JustifyField("    ", $CenterJustify, $DescriptionLength) . " " .
            JustifyField("    ", $CenterJustify, $QuantityLength) . " " .
            JustifyField("PRICE", $CenterJustify, $WholesalePriceLength) . " " .
            JustifyField("PRICE", $CenterJustify, $TotalWholesalePriceLength) . " " .
            JustifyField("PRICE", $CenterJustify, $RetailPriceLength) . " " .
            JustifyField("PRICE", $CenterJustify, $TotalPriceLength) . " " .
            JustifyField("     ", $CenterJustify, $CategoryLength)
            . "<br>");
        $LineCounter++;
                     
        // see if we found any flight records for the given date
        if (sql_count($ChargesResult) > 0) 
        {
            // loop through all records and print the results
            $TotalQuantity = 0;
            $TotalRetailPrice = 0;
            $TotalTotalRetailPrice = 0;
            $TotalWholesalePrice = 0;
            $TotalTotalWholesalePrice = 0;
		    for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++) 
            {
                // if this was the instructors flight hours, ignore it (since the
                // student pays the bill, not the instructor)
                if ($ChargesRST[$Instruction_Type_offset] != $InstructorInstruction) 
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
                             PrintNonBreakingString("<br>");
                             $LineCounter++;
                         }
                          
                         // print the page header
                         PrintNonBreakingString(Space($LeftMargin) +
                                        JustifyField($PageHeader, $CenterJustify, $MaxLineLength)
                                        . "<br>");
                         $LineCounter++;
                         PrintNonBreakingString(Space($LeftMargin) .
                                        JustifyField("DAILY AIRCRAFT CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength)
                                        . "<br>");
                         $LineCounter++;
                        
                         // skip some space below the header
                         for ($i = 0; $i < $HeaderLength; $i++)
                         {
                             PrintNonBreakingString("<br>");
                             $LineCounter++;
                         }
                        
                        //print the column headers
                        PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldLength) . " " .
                            JustifyField("PART", $CenterJustify, $PartNumberLength) . " " .
                            JustifyField("DESCRIPTION", $CenterJustify, $DescriptionLength) . " " .
                            JustifyField("QNTY", $CenterJustify, $QuantityLength) . " " .
                            JustifyField("WHOLESALE", $CenterJustify, $WholesalePriceLength) . " " .
                            JustifyField("TOTAL", $CenterJustify, $TotalWholesalePriceLength) . " " .
                            JustifyField("RETAIL", $CenterJustify, $RetailPriceLength) . " " .
                            JustifyField("TOTAL", $CenterJustify, $TotalPriceLength) . " " .
                            JustifyField("CATEGORY", $CenterJustify, $CategoryLength) . 
                            "<br>");
                        $LineCounter++;
                        PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField("    ", $CenterJustify, $KeycodeFieldLength) . " " .
                            JustifyField("NUMBER", $CenterJustify, $PartNumberLength) . " " .
                            JustifyField("    ", $CenterJustify, $DescriptionLength) . " " .
                            JustifyField("    ", $CenterJustify, $QuantityLength) . " " .
                            JustifyField("PRICE", $CenterJustify, $WholesalePriceLength) . " " .
                            JustifyField("PRICE", $CenterJustify, $TotalWholesalePriceLength) . " " .
                            JustifyField("PRICE", $CenterJustify, $RetailPriceLength) . " " .
                            JustifyField("PRICE", $CenterJustify, $TotalPriceLength) . " " .
                            JustifyField("     ", $CenterJustify, $CategoryLength) . 
                            "<br>");
                        $LineCounter++;
                    }
                    
                    // add amounts to the running totals
                    $TotalQuantity = $TotalQuantity + $ChargesRST[$Quantity_offset];
                    $TotalRetailPrice = $TotalRetailPrice + $ChargesRST[$Price_offset];
                    $TotalTotalRetailPrice = $TotalTotalRetailPrice + $ChargesRST[$Total_Price_offset];
                    $TotalWholesalePrice = $TotalWholesalePrice + $ChargesRST[$Unit_Price_offset];
                    $TotalTotalWholesalePrice = $TotalTotalWholesalePrice +
                                                ($ChargesRST[$Quantity_offset] * $ChargesRST[$Unit_Price_offset]);
                    
                    // print the charge record
                    PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField(
                            $ChargesRST[$Keycode_offset], $LeftJustify, $KeycodeFieldLength) . " " .
                        JustifyField(
                            $ChargesRST[$Part_Number_offset], $LeftJustify, $PartNumberLength) . " " .
                        JustifyField(
                            $ChargesRST[$Part_Description_offset], $LeftJustify, $DescriptionLength) . " " .
                        JustifyField(
                            FormatField($ChargesRST[$Quantity_offset], "Integer"), $RightJustify, $QuantityLength) . " " .
                        JustifyField(
                            FormatField($ChargesRST[$Unit_Price_offset], "Currency"), $RightJustify, $WholesalePriceLength) . " " .
                        JustifyField(
                            FormatField(($ChargesRST[$Quantity_offset] * $ChargesRST[$Unit_Price_offset]), "Currency"), $RightJustify, $TotalWholesalePriceLength) . " " .
                        JustifyField(
                            FormatField($ChargesRST[$Price_offset], "Currency"), $RightJustify, $RetailPriceLength) . " " .
                        JustifyField(
                            FormatField($ChargesRST[$Total_Price_offset], "Currency"), $RightJustify, $TotalPriceLength) . " " .
                        JustifyField(
                            $ChargesRST[$Category_offset], $CenterJustify, $CategoryLength) . 
                        "<br>");
                    $LineCounter++;
                }
            }
            
            // print the totals
            PrintNonBreakingString(Space($LeftMargin) .
                    " <br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                JustifyField(" ", $LeftJustify, $KeycodeFieldLength) . " " .
                JustifyField(" ", $LeftJustify, $PartNumberLength) . " " .
                JustifyField("TOTALS", $LeftJustify, $DescriptionLength) . " " .
                JustifyField(FormatField(Str($TotalQuantity), "Integer"), $RightJustify, $QuantityLength) . " " .
                JustifyField(FormatField(Str($TotalWholesalePrice), "Currency"), $RightJustify, $WholesalePriceLength) . " " .
                JustifyField(FormatField(Str($TotalTotalWholesalePrice), "Currency"), $RightJustify, $TotalWholesalePriceLength) . " " .
                JustifyField(FormatField(Str($TotalRetailPrice), "Currency"), $RightJustify, $RetailPriceLength) . " " .
                JustifyField(FormatField(Str($TotalTotalRetailPrice), "Currency"), $RightJustify, $TotalPriceLength) . " " .
                JustifyField(" ", $CenterJustify, $CategoryLength) . 
                "<br>");
            $LineCounter++;
        }
    }
    
    //********************************************************************
    // PrintSingleDayFlightInstructionSummary(
    //                           BillingDate As String,
    //                           LineCounter As Integer)
    //
    // Purpose:  Print the daily flight instruction billing information
    //           for the given date
    //
    // Inputs:
    //   BillingDate - the date to print the billing for
    //   LineCounter - counter for number of lines (used to start a new page)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleDayFlightInstructionSummary($BillingDate, &$LineCounter)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 80;
        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 2;
        $ColumnWidth1 = 8;
        $ColumnWidth2 = 12;
        $ColumnWidth3 = 10;
        $ColumnWidth4 = 12;
        $ColumnWidth5 = 6;
        $ColumnWidth6 = 8;
        $ColumnWidth7 = 11;
        
        $PageHeader = "************ " . $AircraftScheduling_company . " ************";
           
        // printer setup
        PrinterSetup(9);
        
        // build the underLine string
        $UnderLine = "";
        for ($i = 0;  $i < $MaxLineLength; $i++)
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // if we need a new page, print the header
        if ($LineCounter > $PageLength - 5) 
        {
            // at max page length, print the page
            PrintNewPage();
            $LineCounter = 0;
    
            // skip some space at the top of the form
            for ($i = 0; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString("<br>");
                $LineCounter++;
            }
             
            // print the page header
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                           "<br>");                           
            $LineCounter++;
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
                          
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DAILY FLIGHT INSTRUCTION CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
                        
        // skip some space below the header
        PrintNonBreakingString("<br>");
        $LineCounter++;
                
        // create a query with the billing date as the query identifier
    	$FlightSQL = 
                "SELECT * FROM Flight WHERE (Flight.Date = '" . FormatField($BillingDate, "DatabaseDate") . "') " .
                    "ORDER BY Aircraft";
    	$FlightResult = sql_query($FlightSQL);
         
        // if we have any errors
        if(!$FlightResult) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDayFlightInstructionSummary", $FlightSQL);
        }
                
        //print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("AIRCRAFT", $CenterJustify, $ColumnWidth1) . " " .
                       JustifyField("DATE", $CenterJustify, $ColumnWidth2) . " " .
                       JustifyField("KEYCODE", $CenterJustify, $ColumnWidth3) . " " .
                       JustifyField("INST KEYCODE", $CenterJustify, $ColumnWidth4) . " " .
                       JustifyField("TIME", $CenterJustify, $ColumnWidth5) . " " .
                       JustifyField("CHARGE", $CenterJustify, $ColumnWidth6) . " " .
                       JustifyField("INST CREDIT", $CenterJustify, $ColumnWidth7) . 
                       "<br>");
        $LineCounter++;
                     
        // see if we found any flight records for the given date
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and print the results
            $TotalTime = 0;
            $TotalCharges = 0;
            $TotalCredits = 0;   
		    for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++) 
            {
                // if this is a flight with an instructor, print it
                if (Len(Trim($FlightRST[$Instructor_Keycode_offset])) > 0) 
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
                            PrintNonBreakingString("<br>");
                            $LineCounter++;
                        }
                          
                        // print the page header
                        PrintNonBreakingString(Space($LeftMargin) .
                                        JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                                        "<br>");
                        $LineCounter++;
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("DAILY FLIGHT INSTRUCTION CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                                       "<br>");
                        $LineCounter++;
                        
                        // skip some space below the header
                        for ($i = 0; $i < $HeaderLength; $i++)
                        {
                            PrintNonBreakingString("<br>");
                            $LineCounter++;
                        }
                        
                        //print the column headers
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("AIRCRAFT", $CenterJustify, $ColumnWidth1) . " " .
                                       JustifyField("DATE", $CenterJustify, $ColumnWidth2) . " " .
                                       JustifyField("KEYCODE", $CenterJustify, $ColumnWidth3) . " " .
                                       JustifyField("INST KEYCODE", $CenterJustify, $ColumnWidth4) . " " .
                                       JustifyField("TIME", $CenterJustify, $ColumnWidth5) . " " .
                                       JustifyField("CHARGE", $CenterJustify, $ColumnWidth6) . " " .
                                       JustifyField("INST CREDIT", $CenterJustify, $ColumnWidth7) . 
                                       "<br>");
                        $LineCounter++;
                    }
                    
                    // print the instruction record
                    $InstructionTimes = Val($FlightRST[$Dual_Time_offset]) + Val($FlightRST[$Dual_PP_Time_offset]);
                    
                    // get the instructor charge rates from the database
                    $FlightInstCharges = $InstructionTimes * 
                                         GetInstructionRate($FlightRST[$Instruction_Type_offset]);
                    
                    // get the instructor credit rates from the database
                    $FlightInstCredit = $InstructionTimes *
                            GetInstructorCreditRate(
                                $FlightRST[$Instructor_Keycode_offset],
                                $FlightRST[$Instruction_Type_offset]);
                    
                    // print the record
                    $TotalTime = $TotalTime + $InstructionTimes;
                    $TotalCharges = $TotalCharges + $FlightInstCharges;
                    $TotalCredits = $TotalCredits + $FlightInstCredit;
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($FlightRST[$Aircraft_offset], $CenterJustify, $ColumnWidth1) . " " .
                                   JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $ColumnWidth2) . " " .
                                   JustifyField($FlightRST[$Keycode_offset], $CenterJustify, $ColumnWidth3) . " " .
                                   JustifyField($FlightRST[$Instructor_Keycode_offset], $CenterJustify, $ColumnWidth4) . " " .
                                   JustifyField(FormatField(Str($InstructionTimes), "Float"), $RightJustify, $ColumnWidth5) . " " .
                                   JustifyField(FormatField(Str($FlightInstCharges), "Currency"), $RightJustify, $ColumnWidth6) . " " .
                                   JustifyField(FormatField(Str($FlightInstCredit), "Currency"), $RightJustify, $ColumnWidth7) . 
                                   "<br>");
                    $LineCounter++;
                }
            }
            
            // print the totals line
            PrintNonBreakingString(Space($LeftMargin) .
                " <br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                   JustifyField("TOTALS", $CenterJustify, $ColumnWidth1) . " " .
                   JustifyField(" ", $CenterJustify, $ColumnWidth2) . " " .
                   JustifyField(" ", $CenterJustify, $ColumnWidth3) . " " .
                   JustifyField(" ", $CenterJustify, $ColumnWidth4) . " " .
                   JustifyField(FormatField(Str($TotalTime), "Float"), $RightJustify, $ColumnWidth5) . " " .
                   JustifyField(FormatField(Str($TotalCharges), "Currency"), $RightJustify, $ColumnWidth6) . " " .
                   JustifyField(FormatField(Str($TotalCredits), "Currency"), $RightJustify, $ColumnWidth7) . 
                   "<br>");
            $LineCounter++;
        }
    }
    
    //********************************************************************
    // PrintSingleDayFuelSummary(
    //                           BillingDate As String,
    //                           LineCounter As Integer)
    //
    // Purpose:  Print the daily fuel billing information for
    //           the given date
    //
    // Inputs:
    //   BillingDate - the date to print the billing for
    //   LineCounter - counter for number of lines (used to start a new page)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleDayFuelSummary($BillingDate, &$LineCounter)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 80;
        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 2;
        $AircraftFieldWidth = 8;
        $DateFieldWidth = 12;
        $KeycodeFieldWidth = 10;
        $LocalFuelFieldWidth = 10;
        $LocalFuelCostFieldWidth = 10;
        $XCntryFuelFieldWidth = 12;
        $XCntryFuelCreditFieldWidth = 14;
        
        $PageHeader = "************ " . $AircraftScheduling_company . " ************";
           
        // printer setup
        PrinterSetup(9);
        
        // build the underLine string
        $UnderLine = "";
        for ($i = 0;  $i < $MaxLineLength; $i++)
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // if we need a new page, print the header
        if ($LineCounter > $PageLength - 5) 
        {
            // at max page length, print the page
            PrintNewPage();
            $LineCounter = 0;
    
            // skip some space at the top of the form
            for ($i = 0; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString("<br>");
                $LineCounter++;
            }
             
            // print the page header
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                           "<br>");
            $LineCounter++;
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
                          
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DAILY FUEL CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
                        
        // skip some space below the header
        PrintNonBreakingString("<br>");
        $LineCounter++;
                
        // create a query with the billing date as the query identifier
    	$FlightSQL = 
                "SELECT * FROM Flight WHERE (Flight.Date = '" . FormatField($BillingDate, "DatabaseDate") . "') " .
                    "ORDER BY Aircraft";
    	$FlightResult = sql_query($FlightSQL);
         
        // if we have any errors
        if(!$FlightResult) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDayFuelSummary", $FlightSQL);
        }
                
        //print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                       JustifyField("DATE", $CenterJustify, $DateFieldWidth) . " " .
                       JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldWidth) . " " .
                       JustifyField("LOCAL GALS", $CenterJustify, $LocalFuelFieldWidth) . " " .
                       JustifyField("LOCAL COST", $CenterJustify, $LocalFuelCostFieldWidth) . " " .
                       JustifyField("X-CNTRY GALS", $CenterJustify, $XCntryFuelFieldWidth) . " " .
                       JustifyField("X-CNTRY CREDIT", $CenterJustify, $XCntryFuelCreditFieldWidth) . 
                       "<br>");
        $LineCounter++;
                     
        // see if we found any flight records for the given date
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and print the results
            $TotalLocalFuel = 0;
            $TotalXCntryFuel = 0;
			$TotalLocalFuelCost = 0;
			$TotalXCntryFuelCredit = 0;
    
		    for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++) 
            {
                // if this flight had fuel charges, print it
                if ($FlightRST[$Local_Fuel_offset] > 0 ||
                    $FlightRST[$Cross_Country_Fuel_offset] > 0)
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
                            PrintNonBreakingString("<br>");
                            $LineCounter++;
                        }
                          
                        // print the page header
                        PrintNonBreakingString(Space($LeftMargin) .
                                        JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                                        "<br>");
                        $LineCounter++;
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("DAILY FUEL CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                                       "<br>");
                        $LineCounter++;
                        
                        // skip some space below the header
                        for ($i = 0; $i < $HeaderLength; $i++)
                        {
                            PrintNonBreakingString("<br>");
                            $LineCounter++;
                        }
                        
                        //print the column headers
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                                       JustifyField("DATE", $CenterJustify, $DateFieldWidth) . " " .
                                       JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldWidth) . " " .
                                       JustifyField("LOCAL GALS", $CenterJustify, $LocalFuelFieldWidth) . " " .
                                       JustifyField("LOCAL COST", $CenterJustify, $LocalFuelCostFieldWidth) . " " .
                                       JustifyField("X-CNTRY GALS", $CenterJustify, $XCntryFuelFieldWidth) . " " .
                                       JustifyField("X-CNTRY CREDIT", $CenterJustify, $XCntryFuelCreditFieldWidth) . 
                                       "<br>");
                        $LineCounter++;
                    }
                    
                    // get the fuel charges
                    $LocalFuel = $FlightRST[$Local_Fuel_offset];
                    $XCntryFuel = $FlightRST[$Cross_Country_Fuel_offset];
                    
                    // compute the local fuel cost for the local fuel used.
                    if ($FlightRST[$Aircraft_Cost_offset] > 0) 
                    {
                        // aircraft is a rental aircraft, compute the fuel cost
                        $LocalFuelCost = $FlightRST[$Local_Fuel_offset] *
                                                GetGeneralPreferenceValue("Fuel_Cost");
                        $XCntryFuelCredit = -$FlightRST[$Cross_Country_Fuel_Credit_offset];
                    } 
                    else 
                    {
                        // aircraft is a non-rental aircraft, compute the fuel credit
                        $LocalFuelCost = ($FlightRST[$Local_Fuel_offset] *
                                                GetGeneralPreferenceValue("Fuel_Cost"));
                        $XCntryFuelCredit = 0;
                    }
                    
                    // compute the totals
                    $TotalLocalFuel = $TotalLocalFuel + $LocalFuel;
                    $TotalXCntryFuel = $TotalXCntryFuel + $XCntryFuel;
                    $TotalLocalFuelCost = $TotalLocalFuelCost + $LocalFuelCost;
                    $TotalXCntryFuelCredit = $TotalXCntryFuelCredit + $XCntryFuelCredit;
                    
                    // print the record
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($FlightRST[$Aircraft_offset], $CenterJustify, $AircraftFieldWidth) . " " .
                                   JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $DateFieldWidth) . " " .
                                   JustifyField($FlightRST[$Keycode_offset], $CenterJustify, $KeycodeFieldWidth) . " " .
                                   JustifyField(FormatField(Str($LocalFuel), "Float"), $RightJustify, $LocalFuelFieldWidth) . " " .
                                   JustifyField(FormatField(Str($LocalFuelCost), "Currency"), $RightJustify, $LocalFuelCostFieldWidth) . " " .
                                   JustifyField(FormatField(Str($XCntryFuel), "Float"), $RightJustify, $XCntryFuelFieldWidth) . " " .
                                   JustifyField(FormatField(Str($XCntryFuelCredit), "Currency"), $RightJustify, $XCntryFuelCreditFieldWidth) . 
                                   "<br>");
                    $LineCounter++;
                }
            }
            
            // print the totals line
            PrintNonBreakingString(Space($LeftMargin) .
                " <br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                   JustifyField("TOTALS", $CenterJustify, $AircraftFieldWidth) . " " .
                   JustifyField(" ", $CenterJustify, $DateFieldWidth) . " " .
                   JustifyField(" ", $CenterJustify, $KeycodeFieldWidth) . " " .
                   JustifyField(FormatField(Str($TotalLocalFuel), "Float"), $RightJustify, $LocalFuelFieldWidth) . " " .
                   JustifyField(FormatField(Str($TotalLocalFuelCost), "Currency"), $RightJustify, $LocalFuelCostFieldWidth) . " " .
                   JustifyField(FormatField(Str($TotalXCntryFuel), "Float"), $RightJustify, $XCntryFuelFieldWidth) . " " .
                   JustifyField(FormatField(Str($TotalXCntryFuelCredit), "Currency"), $RightJustify, $XCntryFuelCreditFieldWidth) . 
                   "<br>");
            $LineCounter++;
        }
    }
    
    //********************************************************************
    // PrintSingleDayOilSummary(
    //                           BillingDate As String,
    //                           LineCounter As Integer)
    //
    // Purpose:  Print the daily oil billing information for
    //           the given date
    //
    // Inputs:
    //   BillingDate - the date to print the billing for
    //   LineCounter - counter for number of lines (used to start a new page)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleDayOilSummary($BillingDate, &$LineCounter)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 80;
        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 2;
        $AircraftFieldWidth = 8;
        $DateFieldWidth = 12;
        $KeycodeFieldWidth = 10;
        $OilFieldWidth = 5;
        $OilCostFieldWidth = 12;
        
        $PageHeader =
            "************ " . $AircraftScheduling_company . " ************";
           
        // printer setup
        PrinterSetup(9);
        
        // build the underLine string
        $UnderLine = "";
        for ($i = 0;  $i < $MaxLineLength; $i++)
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // if we need a new page, print the header
        if ($LineCounter > $PageLength - 5) 
        {
            // at max page length, print the page
            PrintNewPage();
            $LineCounter = 0;
    
            // skip some space at the top of the form
            for ($i = 0; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString("<br>");
                $LineCounter++;
            }
             
            // print the page header
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                           "<br>");
            $LineCounter++;
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
                          
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DAILY OIL CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
                        
        // skip some space below the header
        PrintNonBreakingString("<br>");
        $LineCounter++;
                
        // create a query with the billing date as the query identifier
    	$FlightSQL = 
                "SELECT * FROM Flight WHERE (Flight.Date = '" . FormatField($BillingDate, "DatabaseDate") . "') " .
                    "ORDER BY Aircraft";
    	$FlightResult = sql_query($FlightSQL);
         
        // if we have any errors
        if(!$FlightResult) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDayOilSummary", $FlightSQL);
        }
                
        // print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                       JustifyField("DATE", $CenterJustify, $DateFieldWidth) . " " .
                       JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldWidth) . " " .
                       JustifyField("OIL", $CenterJustify, $OilFieldWidth) . " " .
                       JustifyField("OIL COST", $CenterJustify, $OilCostFieldWidth) . 
                       "<br>");
        $LineCounter++;
                     
        // see if we found any flight records for the given date
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and print the results
            $TotalLocalOil = 0;
            $TotalLocalOilCost = 0;
    
		    for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++) 
            {
                // if this flight had oil charges, print it
                if ($FlightRST[$Oil_offset] > 0) 
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
                            PrintNonBreakingString("<br>");
                            $LineCounter++;
                        }
                          
                        // print the page header
                        PrintNonBreakingString(Space($LeftMargin) .
                                        JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                                        "<br>");
                        $LineCounter++;
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("DAILY OIL CHARGES SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                                       "<br>");
                        $LineCounter++;
                        
                        // skip some space below the header
                        for ($i = 0; $i < $HeaderLength; $i++)
                        {
                            PrintNonBreakingString("<br>");
                            $LineCounter++;
                        }
                        
                        //print the column headers
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                                       JustifyField("DATE", $CenterJustify, $DateFieldWidth) . " " .
                                       JustifyField("KEYCODE", $CenterJustify, $KeycodeFieldWidth) . " " .
                                       JustifyField("OIL", $CenterJustify, $OilFieldWidth) . " " .
                                       JustifyField("OIL COST", $CenterJustify, $OilCostFieldWidth) . 
                                       "<br>");
                        $LineCounter++;
                    }
                    
                    // get the oil charges
                    $LocalOil = $FlightRST[$Oil_offset];
                    $LocalOilCost = $FlightRST[$Oil_offset] * GetGeneralPreferenceValue("Oil_Charge");
                    $TotalLocalOil = $TotalLocalOil + $LocalOil;
                    $TotalLocalOilCost = $TotalLocalOilCost + $LocalOilCost;
                    
                    // print the record
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField($FlightRST[$Aircraft_offset], $CenterJustify, $AircraftFieldWidth) . " " .
                                   JustifyField(FormatField($FlightRST[$Date_offset], "Date"), $CenterJustify, $DateFieldWidth) . " " .
                                   JustifyField($FlightRST[$Keycode_offset], $CenterJustify, $KeycodeFieldWidth) . " " .
                                   JustifyField(FormatField(Str($LocalOil), "Float"), $RightJustify, $OilFieldWidth) . " " .
                                   JustifyField(FormatField(Str($LocalOilCost), "Currency"), $RightJustify, $OilCostFieldWidth) . 
                                   "<br>");
                    $LineCounter++;
                }
            }
            
            // print the totals line
            PrintNonBreakingString(Space($LeftMargin) .
                " <br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("TOTALS", $CenterJustify, $AircraftFieldWidth) . " " .
                    JustifyField(" ", $CenterJustify, $DateFieldWidth) . " " .
                    JustifyField(" ", $CenterJustify, $KeycodeFieldWidth) . " " .
                    JustifyField(FormatField(Str($TotalLocalOil), "Float"), $RightJustify, $OilFieldWidth) . " " .
                    JustifyField(FormatField(Str($TotalLocalOilCost), "Currency"), $RightJustify, $OilCostFieldWidth) . 
                    "<br>");
            $LineCounter++;
        }
    }
    
    //********************************************************************
    // SingleDayAircraftRevenueSummarySummary(
    //                           BillingDate As String,
    //                           LineCounter As Integer)
    //
    // Purpose:  Print the daily revenue information for
    //           the given date
    //
    // Inputs:
    //   BillingDate - the date to print the billing for
    //   LineCounter - counter for number of lines (used to start a new page)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function SingleDayAircraftRevenueSummarySummary($BillingDate, &$LineCounter)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 97;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 2;
        $AircraftFieldWidth = 9;
        $ReimburseFieldWidth = 9;
        $FuelCostsFieldWidth = 8;
        $OilCostsFieldWidth = 7;
        $MaintenanceFlightHoursFieldWidth = 7;
        $RevenueHoursFieldWidth = 8;
        $RentalRevenueFieldWidth = 10;
        $MaintenanceCostsFieldWidth = 10;
        $PartsCostsFieldWidth = 10;
        $BalanceFieldWidth = 10;
        
        $PageHeader = "************ " . $AircraftScheduling_company . " ************";
      
        // printer setup
        PrinterSetup(9);
        
        // build the underLine string
        $UnderLine = "";
        for ($i = 0;  $i < $MaxLineLength; $i++)
        {
            $UnderLine = $UnderLine . "_";
        }
        
        // if we need a new page, print the header
        if ($LineCounter > $PageLength - 5) 
        {
            // at max page length, print the page
            PrintNewPage();
            $LineCounter = 0;
    
            // skip some space at the top of the form
            for ($i = 0; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString("<br>");
                $LineCounter++;
            }
             
            // print the page header
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                           "<br>");
            $LineCounter++;
        }
        
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
                          
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DAILY AIRCRAFT REVENUE SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
                        
        // skip some space below the header
        PrintNonBreakingString("<br>");
        $LineCounter++;
                
        // print the column headers
        PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                        JustifyField("REVENUE", $CenterJustify, $RevenueHoursFieldWidth) . " " .
                        JustifyField("MAINT", $CenterJustify, $MaintenanceFlightHoursFieldWidth) . " " .
                        JustifyField("RENTAL", $CenterJustify, $RentalRevenueFieldWidth) . " " .
                        JustifyField("OWNER", $CenterJustify, $ReimburseFieldWidth) . " " .
                        JustifyField("FUEL", $CenterJustify, $FuelCostsFieldWidth) . " " .
                        JustifyField("OIL", $CenterJustify, $OilCostsFieldWidth) . " " .
                        JustifyField("MAINT", $CenterJustify, $MaintenanceCostsFieldWidth) . " " .
                        JustifyField("PARTS", $CenterJustify, $PartsCostsFieldWidth) . " " .
                        JustifyField("BALANCE", $CenterJustify, $BalanceFieldWidth) . 
                        "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("    ", $CenterJustify, $AircraftFieldWidth) . " " .
                        JustifyField("HOURS", $CenterJustify, $RevenueHoursFieldWidth) . " " .
                        JustifyField("HOURS", $CenterJustify, $MaintenanceFlightHoursFieldWidth) . " " .
                        JustifyField("REVENUE", $CenterJustify, $RentalRevenueFieldWidth) . " " .
                        JustifyField("REIMB", $CenterJustify, $ReimburseFieldWidth) . " " .
                        JustifyField("COSTS", $CenterJustify, $FuelCostsFieldWidth) . " " .
                        JustifyField("COSTS", $CenterJustify, $OilCostsFieldWidth) . " " .
                        JustifyField("COSTS", $CenterJustify, $MaintenanceCostsFieldWidth) . " " .
                        JustifyField("COSTS", $CenterJustify, $PartsCostsFieldWidth) . " " .
                        JustifyField("     ", $CenterJustify, $BalanceFieldWidth) . 
                        "<br>");
        $LineCounter++;
        
        // Open Recordset from the database
    	$AircraftSQL = "SELECT * FROM AircraftScheduling_aircraft";
    	$AircraftResult = sql_query($AircraftSQL);
         
        // if we have any errors
        if(!$AircraftResult) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("SingleDayAircraftRevenueSummarySummary", $AircraftSQL);
        }
        $TotalRevenueHours = 0;
        $TotalMaintenanceFlightHours = 0;
        $TotalRentalRevenue = 0;
        $TotalOwnerReimburse = 0;
        $TotalFuelCost = 0;
        $TotalOilCost = 0;
        $TotalMainenanceCosts = 0;
        $TotalPartsCost = 0;
        $TotalBalance = 0;
    
        // did we find any records?
        if (sql_count($AircraftResult) > 0) 
        {
		    for($AircraftCnt=0; $AircraftRST = sql_row($AircraftResult, $AircraftCnt); $AircraftCnt++) 
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
                        PrintNonBreakingString("<br>");
                        $LineCounter++;
                    }
                      
                    // print the page header
                    PrintNonBreakingString(Space($LeftMargin) .
                                    JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                                    "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) .
                                   JustifyField("DAILY AIRCRAFT REVENUE SUMMARY FOR " . $BillingDate, $CenterJustify,$MaxLineLength) . 
                                   "<br>");
                    $LineCounter++;
                    
                    // skip some space below the header
                    for ($i = 0; $i < $HeaderLength; $i++)
                    {
                        PrintNonBreakingString("<br>");
                        $LineCounter++;
                    }
                    
                    //print the column headers
                    PrintNonBreakingString(Space($LeftMargin) .
                                    JustifyField("AIRCRAFT", $CenterJustify, $AircraftFieldWidth) . " " .
                                    JustifyField("REVENUE", $CenterJustify, $RevenueHoursFieldWidth) . " " .
                                    JustifyField("MAINT", $CenterJustify, $MaintenanceFlightHoursFieldWidth) . " " .
                                    JustifyField("RENTAL", $CenterJustify, $RentalRevenueFieldWidth) . " " .
                                    JustifyField("OWNER", $CenterJustify, $ReimburseFieldWidth) . " " .
                                    JustifyField("FUEL", $CenterJustify, $FuelCostsFieldWidth) . " " .
                                    JustifyField("OIL", $CenterJustify, $OilCostsFieldWidth) . " " .
                                    JustifyField("MAINT", $CenterJustify, $MaintenanceCostsFieldWidth) . " " .
                                    JustifyField("PARTS", $CenterJustify, $PartsCostsFieldWidth) . " " .
                                    JustifyField("BALANCE", $CenterJustify, $BalanceFieldWidth) . 
                                    "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) .
                                    JustifyField("    ", $CenterJustify, $AircraftFieldWidth) . " " .
                                    JustifyField("HOURS", $CenterJustify, $RevenueHoursFieldWidth) . " " .
                                    JustifyField("HOURS", $CenterJustify, $MaintenanceFlightHoursFieldWidth) . " " .
                                    JustifyField("REVENUE", $CenterJustify, $RentalRevenueFieldWidth) . " " .
                                    JustifyField("REIMB", $CenterJustify, $ReimburseFieldWidth) . " " .
                                    JustifyField("COSTS", $CenterJustify, $FuelCostsFieldWidth) . " " .
                                    JustifyField("COSTS", $CenterJustify, $OilCostsFieldWidth) . " " .
                                    JustifyField("COSTS", $CenterJustify, $MaintenanceCostsFieldWidth) . " " .
                                    JustifyField("COSTS", $CenterJustify, $PartsCostsFieldWidth) . " " .
                                    JustifyField("     ", $CenterJustify, $BalanceFieldWidth) . 
                                    "<br>");
                    $LineCounter++;
                }
                
                // if this is a non-rental aircraft, ignore it
                if ($AircraftRST[$hourly_cost_offset] > 0) 
                {
                    // create a query for the flight charges for the date and aircraft selected
                	$FlightSQL = 
                            "SELECT * FROM Flight WHERE (" .
                                "Flight.Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                "Flight.Hobbs_Elapsed <> 0 AND " .
                                "Flight.Aircraft = '" . $AircraftRST[$n_number_offset] . "')" .
                                "ORDER BY Date, End_Hobbs";
                	$FlightResult = sql_query($FlightSQL);
                     
                    // if we have any errors
                    if(!$FlightResult) 
                    {
                        // error processing database request, tell the user
                        DisplayDatabaseError("SingleDayAircraftRevenueSummarySummary", $FlightSQL);
                    }
                            
                    // create a query for the maintenance flight charges for the date and aircraft selected
                    // the keycode for a maintenance flight is the tail number of the aircraft
                	$MaintFlightSQL = 
                            "SELECT * FROM Flight WHERE (" .
                                "Flight.Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                "Flight.Keycode = '" . Trim($AircraftRST[$n_number_offset]) . "' AND " .
                                "Flight.Hobbs_Elapsed <> 0 AND " .
                                "Flight.Aircraft = '" . Trim($AircraftRST[$n_number_offset]) . "')" .
                                "ORDER BY Date, End_Hobbs";
                	$MaintFlightResult = sql_query($MaintFlightSQL);
                     
                    // if we have any errors
                    if(!$MaintFlightResult) 
                    {
                        // error processing database request, tell the user
                        DisplayDatabaseError("SingleDayAircraftRevenueSummarySummary", $MaintFlightSQL);
                    }
                            
                    // create a query for the misc charges for the date and aircraft selected
                	$ChargesSQL = 
                                "SELECT * FROM Charges WHERE (" .
                                    "Charges.Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                    "Charges.Keycode = '" . $AircraftRST[$n_number_offset] . "')" .
                                    "ORDER BY Date";
                	$ChargesResult = sql_query($ChargesSQL);
                     
                    // if we have any errors
                    if(!$ChargesResult) 
                    {
                        // error processing database request, tell the user
                        DisplayDatabaseError("SingleDayAircraftRevenueSummarySummary", $ChargesSQL);
                    }
                    
                    // get the beginning and ending numbers
                    if (sql_count($FlightResult) > 0) 
                    {
                        $FlightRST = sql_row($FlightResult, 0);
                        $BeginningHobbs = $FlightRST[$Begin_Hobbs_offset];
                        $FlightRST = sql_row($FlightResult, sql_count($FlightResult) - 1);
                        $EndingHobbs = $FlightRST[$End_Hobbs_offset];
                    } 
                    else 
                    {
                        $BeginningHobbs = 0;
                        $EndingHobbs = 0;
                    }
                                 
                    // compute the amount of oil and fuel used during the period
                    $OilUsed = 0;
                    $FuelUsed = 0;
                    $FuelCost = 0;
                    $OilCost = 0;
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
                                
                                // compute the fuel costs
                                // if we are not reimbursing the actual cost, compute the credit
                                if (GetGeneralPreferenceValue("Fuel_Reimbursement") != 0)
                                {
                                    // not reimbursing the full cost, compute the fuel cost using
                                    // the reimbursement amount for the cross-country fuel
                                    $FuelCost = $FuelCost + 
                                                $FlightRST[$Local_Fuel_offset] * GetGeneralPreferenceValue("Fuel_Reimbursement") +
                                                $FlightRST[$Cross_Country_Fuel_offset] * GetGeneralPreferenceValue("Fuel_Reimbursement");
                                }
                                else
                                {
                                    // reimbursing the full cost, compute the fuel cost using
                                    // the full amount for the cross-country fuel
                                    $FuelCost = $FuelCost + 
                                                $FlightRST[$Local_Fuel_offset] * GetGeneralPreferenceValue("Fuel_Reimbursement") +
                                                $FlightRST[$Cross_Country_Fuel_Credit_offset];
                                }
                        
                                // compute the oil costs
                                $OilCost = $OilCost + 
                                            $FlightRST[$Oil_offset] * GetGeneralPreferenceValue("Oil_Charge");
                            }
                        }
                    }
                                 
                    // see if we found any maintenance flight charges for the given date
                    $MaintenanceFlightHours = 0;
                    if (sql_count($MaintFlightResult) > 0) 
                    {
                        // loop through all records and add the results
		                for($MaintFlightCnt=0; $MaintFlightRST = sql_row($MaintFlightResult, $MaintFlightCnt); $MaintFlightCnt++) 
                        {
                            $MaintenanceFlightHours = $MaintenanceFlightHours + $MaintFlightRST[$Hobbs_Elapsed_offset];
                        }
                    }
                    
                    // compute the reimbursement rates for rental and non-rental aircraft
                    if ($AircraftRST[$hourly_cost_offset] == $AircraftRST[$rental_fee_offset])
                    {
                        // club aircraft, reimbursement rate is 0 since we don't have to pay anyone
                        $ReimbursementRate = 0;
                        $ActivityRate = $AircraftRST[$hourly_cost_offset];
                    } 
                    else 
                    {
                        // non-club aircraft, reimbursement rate is the owner rental rate
                        $ActivityRate = $AircraftRST[$hourly_cost_offset];
                        $ReimbursementRate = $AircraftRST[$rental_fee_offset];
                    }
                    
                    // see if we found any misc charge records for the given date
                    $PartsCost = 0;
                    if (sql_count($ChargesResult) > 0) 
                    {
                        // loop through all records and print the results
		                for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++) 
                        {
                            // add the misc billing if there is a cost associated with it
                            $PartsCost = $PartsCost + $ChargesRST[$Total_Price_offset];
                        }
                    }
                    
                    // compute the remaining report values
                    $RevenueHours = $EndingHobbs - $BeginningHobbs - $MaintenanceFlightHours;
                    $RentalRevenue = ($EndingHobbs - $BeginningHobbs - $MaintenanceFlightHours) * $ActivityRate;
                    $OwnerReimburse = ($EndingHobbs - $BeginningHobbs - $MaintenanceFlightHours) * $ReimbursementRate;
                    $MainenanceCosts = $MaintenanceFlightHours * $ActivityRate;
                    $Balance = ($EndingHobbs - $BeginningHobbs - $MaintenanceFlightHours) * $ActivityRate -
                                                 (($EndingHobbs - $BeginningHobbs - $MaintenanceFlightHours) * $ReimbursementRate) -
                                                 ($MaintenanceFlightHours * ($ActivityRate - $ReimbursementRate)) - $PartsCost - $FuelCost - $OilCost;
                    
                    // compute the total costs
                    $TotalRevenueHours = $TotalRevenueHours + $RevenueHours;
                    $TotalMaintenanceFlightHours = $TotalMaintenanceFlightHours + $MaintenanceFlightHours;
                    $TotalRentalRevenue = $TotalRentalRevenue + $RentalRevenue;
                    $TotalOwnerReimburse = $TotalOwnerReimburse + $OwnerReimburse;
                    $TotalFuelCost = $TotalFuelCost + $FuelCost;
                    $TotalOilCost = $TotalOilCost + $OilCost;
                    $TotalMainenanceCosts = $TotalMainenanceCosts + $MainenanceCosts;
                    $TotalPartsCost = $TotalPartsCost + $PartsCost;
                    $TotalBalance = $TotalBalance + $Balance;
                        
                    // print the aircraft information
                    PrintNonBreakingString(Space($LeftMargin) .
                                    JustifyField($AircraftRST[$n_number_offset], $LeftJustify, $AircraftFieldWidth) . " " .
                                    JustifyField(FormatField(Str($RevenueHours), "Float"), $RightJustify, $RevenueHoursFieldWidth) . " " .
                                    JustifyField(FormatField(Str($MaintenanceFlightHours), "Float"), $RightJustify, $MaintenanceFlightHoursFieldWidth) . " " .
                                    JustifyField(FormatField(Str($RentalRevenue), "Currency"), $RightJustify, $RentalRevenueFieldWidth) . " " .
                                    JustifyField(FormatField(Str($OwnerReimburse), "Currency"), $RightJustify, $ReimburseFieldWidth) . " " .
                                    JustifyField(FormatField(Str($FuelCost), "Currency"), $RightJustify, $FuelCostsFieldWidth) . " " .
                                    JustifyField(FormatField(Str($OilCost), "Currency"), $RightJustify, $OilCostsFieldWidth) . " " .
                                    JustifyField(FormatField(Str($MainenanceCosts), "Currency"), $RightJustify, $MaintenanceCostsFieldWidth) . " " .
                                    JustifyField(FormatField(Str($PartsCost), "Currency"), $RightJustify, $PartsCostsFieldWidth) . " " .
                                    JustifyField(FormatField(Str($Balance), "Currency"), $RightJustify, $BalanceFieldWidth) . 
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
                        JustifyField("TOTALS", $LeftJustify, $AircraftFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalRevenueHours), "Float"), $RightJustify, $RevenueHoursFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalMaintenanceFlightHours), "Float"), $RightJustify, $MaintenanceFlightHoursFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalRentalRevenue), "Currency"), $RightJustify, $RentalRevenueFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalOwnerReimburse), "Currency"), $RightJustify, $ReimburseFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalFuelCost), "Currency"), $RightJustify, $FuelCostsFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalOilCost), "Currency"), $RightJustify, $OilCostsFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalMainenanceCosts), "Currency"), $RightJustify, $MaintenanceCostsFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalPartsCost), "Currency"), $RightJustify, $PartsCostsFieldWidth) . " " .
                        JustifyField(FormatField(Str($TotalBalance), "Currency"), $RightJustify, $BalanceFieldWidth) . 
                        "<br>");
        $LineCounter++;
    }
    
    //********************************************************************
    // PrintSingleDaySquawkSummary(
    //                           BillingDate As String,
    //                           LineCounter As Integer)
    //
    // Purpose:  Print the daily squawk summary, hours until 100 hr and hours
    //           until next 50 hr oil change.
    //
    // Inputs:
    //   BillingDate - the date to print the billing for
    //   LineCounter - counter for number of lines (used to start a new page)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintSingleDaySquawkSummary($BillingDate, &$LineCounter)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 80;
        $LeftMargin = 6;
        $PageLength = 60;
        $HeaderLength = 2;
        
        $PageHeader = "************ " . $AircraftScheduling_company . " ************";
           
        // printer setup
        PrinterSetup(9);
    
        // skip some space at the top of the form
        for ($i = 0; $i < $HeaderLength; $i++)
        {
            PrintNonBreakingString("<br>");
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
            PrintNonBreakingString("<br>");
            $LineCounter++;
        }
            
        // print the sub header
        PrintNonBreakingString(Space($LeftMargin) .
                       JustifyField("DAILY AIRCRAFT SQUAWK, 100 HR AND OIL CHANGE SUMMARY FOR " .
                       $BillingDate, $CenterJustify, $MaxLineLength) . 
                       "<br>");
        $LineCounter++;
        PrintNonBreakingString(Space($LeftMargin) . "<br>");
        $LineCounter++;
                
        // open the aircraft recordset
    	$AircraftSQL = "SELECT * FROM AircraftScheduling_aircraft ORDER BY n_number";
    	$AircraftResult = sql_query($AircraftSQL);
         
        // if we have any errors
        if(!$AircraftResult) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDaySquawkSummary", $AircraftSQL);
        }
                     
        // see if we found any aircraft records
        if (sql_count($AircraftResult) > 0) 
        {
            // loop through all records and print the results
		    for($AircraftCnt=0; $AircraftRST = sql_row($AircraftResult, $AircraftCnt); $AircraftCnt++) 
            {
                // only process rental aircraft
                if ($AircraftRST[$hourly_cost_offset] > 0) 
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
                            PrintNonBreakingString("<br>");
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
                            PrintNonBreakingString("<br>");
                            $LineCounter++;
                        }
                            
                        // print the sub header
                        PrintNonBreakingString(Space($LeftMargin) .
                                       JustifyField("DAILY AIRCRAFT SQUAWK, 100 HR AND OIL CHANGE SUMMARY FOR " . $BillingDate, $CenterJustify, $MaxLineLength) . 
                                       "<br>");
                        $LineCounter++;
                        PrintNonBreakingString(Space($LeftMargin) . "<br>");
                        $LineCounter++;
                    }
                    
                    // print the aircraft ID
                    PrintNonBreakingString(Space($LeftMargin) . " " . "<br>");
                    $LineCounter++;
                    PrintNonBreakingString(Space($LeftMargin) .
                                   "AIRCRAFT: " . Trim($AircraftRST[$n_number_offset]) . "<br>");
                    $LineCounter++;
                    
                    // print the hours until 100 hr
                    PrintNonBreakingString(Space($LeftMargin) .
                       Space(10) .
                       "HOURS UNTIL 100 HR " .
                       FormatField($AircraftRST[$Hrs_Till_100_Hr_offset], "Float") . 
                       "<br>");
                    $LineCounter++;
        
                    // print the hours until 50 hr oil change
                    if ($AircraftRST[$Hrs_Till_100_Hr_offset] > 50) 
                    {
                        PrintNonBreakingString(Space($LeftMargin) .
                           Space(10) .
                           "HOURS UNTIL 50 HR OIL CHANGE " .
                           FormatField($AircraftRST[$Hrs_Till_100_Hr_offset] - 50, "Float") . 
                           "<br>");
                        $LineCounter++;
                    } 
                    else 
                    {
                        PrintNonBreakingString(Space($LeftMargin) .
                           Space(10) .
                           "OIL CHANGE SCHEDULED FOR NEXT 100 HR" . 
                           "<br>");
                        $LineCounter++;
                    }
        
                    // get any open squawks for this date and aircraft
                	$SquawkSQL = 
                                "SELECT * FROM Squawks WHERE " .
                                    "(Squawks.Aircraft = '" . Trim($AircraftRST[$n_number_offset]) . "' AND " .
                                    "Squawks.Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                    "Squawks.Repair_Date = ' ')";
                	$SquawkResult = sql_query($SquawkSQL);
                     
                    // if we have any errors
                    if(!$SquawkResult) 
                    {
                        // error processing database request, tell the user
                        DisplayDatabaseError("PrintSingleDaySquawkSummary", $SquawkSQL);
                    }
                                 
                    // see if we found any squawks for this aircraft and date
                    if (sql_count($SquawkResult) > 0) 
                    {
                        // loop through all records and print the squawks
                        PrintNonBreakingString(Space($LeftMargin) .
                                       Space(10) . "OPEN SQUAWKS: " . "<br>");
                        $LineCounter++;
		                for($SquawkCnt=0; $SquawkRST = sql_row($SquawkResult, $SquawkCnt); $SquawkCnt++) 
                        {
                            // print the squawk information
                            if ($SquawkRST[$SquawkGrounding_offset]) 
                            {
                                PrintNonBreakingString(Space($LeftMargin) .
                                               Space(20) .
                                               $SquawkRST[$SquawkKeyCode_offset] . " " .
                                               $SquawkRST[$SquawkDescription_offset] . " " .
                                               "GROUNDING" . "<br>");
                                $LineCounter++;
                            } 
                            else 
                            {
                                PrintNonBreakingString(Space($LeftMargin) .
                                               Space(20) .
                                               $SquawkRST[$SquawkKeyCode_offset] . " " .
                                               $SquawkRST[$SquawkDescription_offset] . "<br>");
                                $LineCounter++;
                            }
                        }
                    } 
                    else 
                    {
                        // no open squawks, tell the user
                        PrintNonBreakingString(Space($LeftMargin) .
                                       Space(10) . "NO NEW SQUAWKS ENTERED ON THIS DATE" . 
                                       "<br>");
                        $LineCounter++;
                    }
                }
            }
        }
    }
    
    //********************************************************************
    // PrintSelectedDailySummary(
    //                           StartDate As String, EndDate As String)
    //
    // Purpose:  Print the daily billing information for the dates
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
    function PrintSelectedDailySummary($StartDate, $EndDate)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        global $OpenFault, $Repaired, $Deferred, $ClosedFault;

    	global $DailyAircraftChargesOptions;
        global $PrintDailyAircraftRevenueOptions;
        global $PrintDailyFlightInstructionInformationOptions;
        global $PrintDailyFuelOilChargesOptions;
        global $Daily100HrSquawkSummaryOptions;
        global $DailyAircraftFaultRecordsOptions;
        global $DailyMemberChargesOptions;
        
        $PageHeader = "************ " . $AircraftScheduling_company . " ************";
        $MaxLineLength = 80;
        $LeftMargin = 6;
        
        // reset the line counter
        $LineCounter = 0;
        $PageHasBeenPrinted = false;
        $NewPageIsNeeded = false;
           
        // printer setup
        PrinterSetup(9);
                
        // print all reports for all the dates requested
        while (DateValue($StartDate) <= DateValue($EndDate))
        {
            // reset the line counter
            $LineCounter = 0;
            $PageHasBeenPrinted = false;
         
            // handle printing of the daily aircraft summaries
            if ($DailyAircraftChargesOptions == 1) 
            {
                // if we have already printed a page, start a new page
                if ($PageHasBeenPrinted) 
                {
                    PrintNewPage();
                    $LineCounter = 0;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                               JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                               "<br>");
                $LineCounter++;
                
                // print the daily aircraft charge summary
                PrintSingleDayAircraftSummary($StartDate, $LineCounter);
                
                // we have printed a page
                $PageHasBeenPrinted = true;
            }
            
            // should we print the flight instruction daily summary?
            if ($PrintDailyFlightInstructionInformationOptions == 1) 
            {
                // if we have already printed a page, start a new page
                if ($PageHasBeenPrinted) 
                {
                    PrintNewPage();
                    $LineCounter = 0;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                               JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                               "<br>");
                $LineCounter++;
                
                // print the flight instruction daily summary
                PrintSingleDayFlightInstructionSummary($StartDate, $LineCounter);
                 
                // we have printed a page
                $PageHasBeenPrinted = true;
            }
            
            // should we print the fuel and oil daily summary?
            if ($PrintDailyFuelOilChargesOptions == 1) 
            {
                // if we have already printed a page, start a new page
                if ($PageHasBeenPrinted) 
                {
                    PrintNewPage();
                    $LineCounter = 0;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                               JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                               "<br>");
                $LineCounter++;
                                   
                // print the fuel daily summary
                PrintSingleDayFuelSummary($StartDate, $LineCounter);
                            
                // print the oil daily summary
                PrintSingleDayOilSummary($StartDate, $LineCounter);
                
                // we have printed a page
                $PageHasBeenPrinted = true;
            }
            
            // should we print the maintenance daily summary?
            if ($PrintDailyAircraftRevenueOptions == 1) 
            {
                // if we have already printed a page, start a new page
                if ($PageHasBeenPrinted) 
                {
                    PrintNewPage();
                    $LineCounter = 0;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                               JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                               "<br>");
                $LineCounter++;
                                   
                // print the aircraft revenue daily summary
                SingleDayAircraftRevenueSummarySummary($StartDate, $LineCounter);
                
                // we have printed a page
                $PageHasBeenPrinted = true;
            }
            
            // should we print the squawk, 100 hr and 50 oil change daily summary?
            if ($Daily100HrSquawkSummaryOptions == 1) 
            {
                // if we have already printed a page, start a new page
                if ($PageHasBeenPrinted) 
                {
                    PrintNewPage();
                    $LineCounter = 0;
                }
                
                // print the squawk, 100 hr and 50 oil change daily summary
                PrintSingleDaySquawkSummary($StartDate, $LineCounter);
                
                // we have printed a page
                $PageHasBeenPrinted = true;
            }
            
            // should we print the aircraft fault sheets?
            if ($DailyAircraftFaultRecordsOptions == 1) 
            {
                // if we have already printed a page, start a new page
                if ($PageHasBeenPrinted) 
                {
                    PrintNewPage();
                    $LineCounter = 0;
                }
                            
                // print the aircraft fault sheets
                PrintSingleDayAircraftFaultRecord("", $StartDate, $OpenFault, $NewPageIsNeeded);
                
                // we have printed a page
                $PageHasBeenPrinted = true;
            }
         
            // handle printing of the daily member charges
            if ($DailyMemberChargesOptions == 1)
            {
                // if we have already printed a page, start a new page
                if ($PageHasBeenPrinted) 
                {
                    PrintNewPage();
                    $LineCounter = 0;
                }
                
                // print the page header
                PrintNonBreakingString(Space($LeftMargin) .
                               JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                               "<br>");
                $LineCounter++;
                
                // print the daily Member charge summary
                PrintSingleDayMemberSummary($StartDate, $LineCounter);
                
                // we have printed a page
                $PageHasBeenPrinted = true;
            }
        
            // increment to the next date
            $StartDate = FormatField(
                DateSerial(Year($StartDate), Month($StartDate), Day($StartDate) + 1),
                "Date");
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
    if(count($_POST) > 0 && $PrintDailyBillingInformation == "Submit") 
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
        $StartDate = BuildDate($Fromday, $Frommonth, $Fromyear);
        $EndDate = BuildDate($Today, $Tomonth, $Toyear);
    
        // print daily reports for the dates selected
        PrintSelectedDailySummary($StartDate, $EndDate);
        
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
	echo "<FORM NAME='main' ACTION='PrintDailyBillingInformation.php' METHOD='POST'>";
	
	// set the default print selections
	$DailyAircraftChargesOptions = 1;
    $PrintDailyAircraftRevenueOptions = 1;
    $PrintDailyFlightInstructionInformationOptions = 1;
    $PrintDailyFuelOilChargesOptions = 1;
    $Daily100HrSquawkSummaryOptions = 1;
    $DailyAircraftFaultRecordsOptions = 1;
    $DailyMemberChargesOptions = 1;

    // start the table to display the aircraft report information
    echo "<center>";
    echo "<h2>Print Daily Billing Information</h2>";
    echo "<table border=0>";
    
    // daily report date selection
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
     
    // daily aircraft summaries
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=DailyAircraftChargesOptions value=1 ";
	if ($DailyAircraftChargesOptions == 1) echo "checked";
	echo ">Print Daily Aircraft Charges";
    echo "</td>";
    echo "</tr>";
    
    // Daily Aircraft Revenue
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintDailyAircraftRevenueOptions value=1 ";
	if ($PrintDailyAircraftRevenueOptions == 1) echo "checked";
	echo ">Print Daily Aircraft Revenue";
    echo "</td>";
    echo "</tr>";
    
    // flight instruction daily summary
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintDailyFlightInstructionInformationOptions value=1 ";
	if ($PrintDailyFlightInstructionInformationOptions == 1) echo "checked";
	echo ">Print Daily Flight Instruction Information";
    echo "</td>";
    echo "</tr>";
    
    // fuel and oil daily summary
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintDailyFuelOilChargesOptions value=1 ";
	if ($PrintDailyFuelOilChargesOptions == 1) echo "checked";
	echo ">Print Daily Fuel & Oil Charges";
    echo "</td>";
    echo "</tr>";
    
    // squawk, 100 hr and 50 oil change daily summary?
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=Daily100HrSquawkSummaryOptions value=1 ";
	if ($Daily100HrSquawkSummaryOptions == 1) echo "checked";
	echo ">Print Daily 100 Hr, Oil Change & Squawk Summay";
    echo "</td>";
    echo "</tr>";
    
    // aircraft fault sheets?
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=DailyAircraftFaultRecordsOptions value=1 ";
	if ($DailyAircraftFaultRecordsOptions == 1) echo "checked";
	echo ">Daily Aircraft Fault Records";
    echo "</td>";
    echo "</tr>";
 
    // daily member charges
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=DailyMemberChargesOptions value=1 ";
	if ($DailyMemberChargesOptions == 1) echo "checked";
	echo ">Print Daily Member Charges";
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
    echo "<TD><input name='PrintDailyBillingInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
