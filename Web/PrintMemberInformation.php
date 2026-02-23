<?php
//-----------------------------------------------------------------------------
// 
// PrintMemberInformation.php
// 
// PURPOSE: Print user information in various ways.
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
//      PrintMemberInformation - set to submit to print aircraft information
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
    $PrintMemberSigninOption = 0;
	$PrintMembershipRosterNameOption = 0;
	$PrintFullMembershipInfoOption = 0;
	$PrintAllActiveMembersOption = 0;
	$PrintAllInactiveMembersOption = 0;
	$PrintAllMaintenanceKeycodesOption = 0;
	$PrintAllResignedMembersOption = 0;
	$PrintAllExpiredWaiversOption = 0;
	$PrintAllExpiredSafetyMeetingOption = 0;
	$PrintPilotStatusOption = 0;
    $SortSelection = "last_name";
    $Rating = "Student";
    
    // get the starting and ending name in the user list
	$UserResult = SQLOpenRecordset(
    			"SELECT $DatabaseNameFormat " .
    			"FROM AircraftScheduling_person " .
    			"WHERE user_level != $UserLevelDisabled " .
                "ORDER by last_name");		
     
    // process the results of the database inquiry
    $row = sql_row($UserResult, 0); 	
    $MembersFromNameOfUser = $row[0];
    $row = sql_row($UserResult, (sql_count($UserResult) - 1)); 	
    $MembersToNameOfUser = $row[0];

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
    if(isset($rdata["PrintMemberInformation"])) $PrintMemberInformation = $rdata["PrintMemberInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["PrintMemberSigninOption"])) $PrintMemberSigninOption = $rdata["PrintMemberSigninOption"];
	if(isset($rdata["PrintMembershipRosterNameOption"])) $PrintMembershipRosterNameOption = $rdata["PrintMembershipRosterNameOption"];
	if(isset($rdata["PrintFullMembershipInfoOption"])) $PrintFullMembershipInfoOption = $rdata["PrintFullMembershipInfoOption"];
	if(isset($rdata["PrintAllActiveMembersOption"])) $PrintAllActiveMembersOption = $rdata["PrintAllActiveMembersOption"];
	if(isset($rdata["PrintAllInactiveMembersOption"])) $PrintAllInactiveMembersOption = $rdata["PrintAllInactiveMembersOption"];
	if(isset($rdata["PrintAllMaintenanceKeycodesOption"])) $PrintAllMaintenanceKeycodesOption = $rdata["PrintAllMaintenanceKeycodesOption"];
	if(isset($rdata["PrintAllResignedMembersOption"])) $PrintAllResignedMembersOption = $rdata["PrintAllResignedMembersOption"];
	if(isset($rdata["PrintAllExpiredWaiversOption"])) $PrintAllExpiredWaiversOption = $rdata["PrintAllExpiredWaiversOption"];
	if(isset($rdata["PrintAllExpiredSafetyMeetingOption"])) $PrintAllExpiredSafetyMeetingOption = $rdata["PrintAllExpiredSafetyMeetingOption"];
	if(isset($rdata["PrintPilotStatusOption"])) $PrintPilotStatusOption = $rdata["PrintPilotStatusOption"];

    if(isset($rdata["MembersFromNameOfUser"])) $MembersFromNameOfUser = $rdata["MembersFromNameOfUser"];
    if(isset($rdata["MembersToNameOfUser"])) $MembersToNameOfUser = $rdata["MembersToNameOfUser"];
    if(isset($rdata["Rating"])) $Rating = $rdata["Rating"];            

    if(isset($rdata["SortSelection"])) $SortSelection = $rdata["SortSelection"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // PrintMemberSigninSheet(
    //                         SortParameter As String,
    //                         SortName As String)
    //
    // Purpose:  Print the user sign-in sheet
    //           The user sign-in sheet has the user name followed by
    //           an underline.
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMemberSigninSheet($SortParameter, $SortName)
    {                                    
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $MemberStatusActive, $MemberStatusInActive, $MemberStatusAircraft, $MemberStatusResigned;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 70;
        $LeftMargin = 5;
        $PageLength = 58;
        $HeaderLength = 1;
        $KeyCodeLength = 8;
        $MemberNameFieldLength = ($MaxLineLength - $KeyCodeLength) / 2;
        $MemberSigninFieldLength = ($MaxLineLength - $KeyCodeLength) / 2;

        // printer setup
        PrinterSetup(11);
        $PageHeader = "************ " . $AircraftScheduling_company . " SIGN-IN ************";
         
        // skip some space at the top of the form
        $TextLines = 0;
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // print the header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
        $TextLines++;
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        $TextLines++;
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
            $TextLines++;
        }
        
        // create a query to get all user records
        $MembersResult =
                SQLOpenRecordset(
                                 "SELECT * FROM AircraftScheduling_person " . 
                                 "WHERE " .
                                    "INSTR(Rules_Field, 'Member_Status,$MemberStatusInActive') OR " . 
                                    "INSTR(Rules_Field, 'Member_Status,$MemberStatusActive') " . 
                                 "ORDER BY $SortParameter");
        
        // loop through all the users and print the users
        $PageNumber = 0;
		for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++) 
        {
            // if we have filled the page, send the page to the printer
            if ($TextLines >= $PageLength) 
            {
                // print the footer
                $PageNumber++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                
                // at max page length, print the page
                PrintNewPage();
                $TextLines = 0;
        
                // skip some space at the top of the form
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
                
                // print the header
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
                              "<br>");
                $TextLines++;
                
                // skip some space below the header
                for ($i = 0; $i < $HeaderLength; $i++) 
                {
                    PrintNonBreakingString(" " . "<br>");
                    $TextLines++;
                }
            }

            // print the user record
            $MemberFullName = Trim($MembersRST[$first_name_offset]) . " " .
                                Trim($MembersRST[$last_name_offset]);
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField(UCase($MembersRST[$username_offset]), $LeftJustify, $KeyCodeLength) .
                        JustifyField($MemberFullName, $LeftJustify, $MemberNameFieldLength) .
                        JustifyField("__________________________________", $LeftJustify, $MemberSigninFieldLength) . 
                        "<br>");
            PrintNonBreakingString(" " . "<br>");
            $TextLines = $TextLines + 2;
        }
        
        // print the last page (if needed)
        if ($TextLines > 0) 
        {
            // skip to the bottom of the page
            for ($i = $TextLines; $i <= $PageLength - 3; $i++) 
            {
                PrintNonBreakingString(" " . "<br>");
                $TextLines++;
            }
            
            // print the footer
            $PageNumber++;
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(" " . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Page " . Str($PageNumber), $CenterJustify, $MaxLineLength) . 
                          "<br>");
            $TextLines = $TextLines + 3;
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintMembershipRoster(
    //                         SortParameter As String,
    //                         SortName As String)
    //
    // Purpose:  Print the membership roster sheet
    //           The membership roster sheet has the user name followed by
    //           address and telephone.
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMembershipRoster($SortParameter, $SortName)
    {                                    
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 99;
        $LeftMargin = 0;
        $PageLength = 60;
        $HeaderLength = 1;
        $KeyCodeLength = 6;
        $MemberNameFieldLength = 16;
        $AddressFieldLength = 20;
        $CityFieldLength = 11;
        $StateFieldLength = 2;
        $ZipFieldLength = 5;
        $HomeFieldLength = 14;
        $WorkFieldLength = 14;
    
        // printer setup
        PrinterSetup(9);
        $PageHeader = "************ " . $AircraftScheduling_company . " ROSTER ************";
                
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
                      JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
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
                    JustifyField("Work Phone", $CenterJustify, $WorkFieldLength) . " " .
                    JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(" " . "<br>");
        
        // create a query to get all membership information sorted by the sort parameter
        $MembersResult = SQLOpenRecordset(
            "SELECT * FROM AircraftScheduling_person ORDER BY " . $SortParameter);
        
        // loop through all the users and print the users
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
                              JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                             JustifyField("Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
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
                            JustifyField("Work Phone", $CenterJustify, $WorkFieldLength) . " " .
                            JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the user record
            $MemberFullName = Trim($MembersRST[$first_name_offset]) . " " .
                                Trim($MembersRST[$last_name_offset]);
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField(UCase($MembersRST[$username_offset]), $LeftJustify, $KeyCodeLength) . " " .
                        JustifyField($MemberFullName, $LeftJustify, $MemberNameFieldLength) . " " .
                        JustifyField($MembersRST[$address1_offset], $LeftJustify, $AddressFieldLength) . " " .
                        JustifyField($MembersRST[$city_offset], $LeftJustify, $CityFieldLength) . " " .
                        JustifyField($MembersRST[$state_offset], $LeftJustify, $StateFieldLength) . " " .
                        JustifyField($MembersRST[$zip_offset], $LeftJustify, $ZipFieldLength) . " " .
                        JustifyField($MembersRST[$phone_number_offset], $LeftJustify, $WorkFieldLength) . " " .
                        JustifyField($MembersRST[$Home_Phone_offset], $LeftJustify, $HomeFieldLength) . "<br>");
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
            PrintNewPage();
        }
    }
    
    //********************************************************************
    // PrintFullSingleUser(UserRecordRST As MYSQL_RS)
    //
    // Purpose:  Print all membership information for a single user
    //           The full membership sheet has all user information
    //           printed one user per page.
    //
    // Inputs:
    //   UserRecordRST - record for the user to be printed
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintFullSingleUser($UserRecordRST)
    {
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 90;
        $LeftMargin = 2;
        $PageLength = 60;
        $HeaderLength = 3;
        $LeftFieldLength = ($MaxLineLength) / 2;
        $RightFieldLength = ($MaxLineLength) / 2;
                
        // printer setup
        PrinterSetup(9);
        $PageHeader = "************ " . $AircraftScheduling_company . " MEMBERS ************";
            
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
                       JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField("Username: " . UCase($UserRecordRST[$username_offset]), $CenterJustify, $MaxLineLength) . 
                      "<br>");
        
        // skip some space below the header
        for ($i = 0; $i < $HeaderLength; $i++) 
        {
            PrintNonBreakingString(" " . "<br>");
        }
        
        // print the user record
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("User", $CenterJustify, $LeftFieldLength) .
                    JustifyField("Notify", $CenterJustify, $RightFieldLength) . 
                    "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                      "<br>");
                      
        // print the user and notify information
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField($UserRecordRST[$first_name_offset] . " " .
                                $UserRecordRST[$middle_name_offset] . " " .
                                $UserRecordRST[$last_name_offset],
                                $LeftJustify, $LeftFieldLength) .
                    JustifyField($UserRecordRST[$Notify_First_Name_offset] . " " .
                                $UserRecordRST[$Notify_Middle_Initial_offset] . " " .
                                $UserRecordRST[$Notify_Last_Name_offset],
                                $LeftJustify, $RightFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField($UserRecordRST[$address1_offset], $LeftJustify, $LeftFieldLength) .
                    JustifyField($UserRecordRST[$Notify_Address_offset], $LeftJustify, $RightFieldLength) . 
                    "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField($UserRecordRST[$city_offset] . ", " .
                                $UserRecordRST[$state_offset] . " " .
                                $UserRecordRST[$zip_offset],
                                $LeftJustify, $LeftFieldLength) .
                    JustifyField($UserRecordRST[$Notify_City_offset] . ", " .
                                $UserRecordRST[$Notify_State_offset] . " " .
                                $UserRecordRST[$Notify_Zip_offset],
                                $LeftJustify, $RightFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField($UserRecordRST[$Home_Phone_offset],
                                $LeftJustify, $LeftFieldLength) .
                    JustifyField(
                                "Phone: " . $UserRecordRST[$Notify_Phone1_offset] .
                                " Alt: " . $UserRecordRST[$Notify_Phone2_offset],
                                $LeftJustify, $RightFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("SSN: " . FormatSSNString(DecryptString($UserRecordRST[$SSN_offset])),
                                $LeftJustify, $LeftFieldLength) .
                    JustifyField("Relationship: " . $UserRecordRST[$Notify_Relation_offset],
                                $LeftJustify, $RightFieldLength) . "<br>");
        PrintNonBreakingString("" . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Organization: ",
                                $LeftJustify, $LeftFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField($UserRecordRST[$Organization_offset],
                                $LeftJustify, $LeftFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField(
                                $UserRecordRST[$phone_number_offset],
                                $LeftJustify, $LeftFieldLength) . "<br>");
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(" " . "<br>");
    
        // load the currency field values for the user from the database
        LoadDBCurrencyFields("", $UserRecordRST[$Rules_Field_offset]);
        
        // print the currency information
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Currency Information", $CenterJustify, $LeftFieldLength) . 
                    "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        PrintCurrencyInformation(
                        $LeftMargin,
                        UCase($UserRecordRST[$username_offset]),
                        $LinesPrinted);
        
        // print the notes fields
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("Notes:", $LeftJustify, $LeftFieldLength) . 
                    "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                                    $UserRecordRST[$Member_Notes_offset] . 
                                    "<br>");
    }
    
    //********************************************************************
    // PrintFullMembershipInfo($SortParameter, $StartName, $EndName)
    //
    // Purpose:  Print all membership information
    //           The full membership sheet has all user information
    //           printed one user per page.
    //
    // Inputs:
    //   SortParameter - the field to sort on for printing
    //   StartName - the starting name for the billing information
    //   EndName - the end name for the billing information
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintFullMembershipInfo($SortParameter, $StartName, $EndName)
    {
        global $DatabaseNameFormat;
        
        // create a query to get all users between the two names requested
        $MembersResult = SQLOpenRecordset(
            "SELECT * FROM AircraftScheduling_person " . 
            "WHERE (CONCAT(last_name, ' ', first_name) >= '" . 
                        GetLastName($StartName) . " " . GetFirstName($StartName) . "' AND " .
                    "CONCAT(last_name, ' ', first_name) <='" . 
                        GetLastName($EndName) . " " . GetFirstName($EndName) . "') " .
                "ORDER BY $SortParameter");
        
        // loop through all the users and print the users
        for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++)  
        {
            // make sure we don't time out
            set_time_limit(30);

            // print the user record
            PrintFullSingleUser($MembersRST);
            
            // if we are at the end of the record set, don't print an
            // new page (the EndDoc will print the last page)
            if ($MembersCnt < sql_count($MembersResult) - 1) 
            {
                // finish the page
                PrintNewPage();
            }
        }
        
        // complete the print job
        PrintNewPage();
    }
    
    //********************************************************************
    // PrintMembersStatusInformation(
    //                                CurrencyField As String,
    //                                CurrencyCriteria As String,
    //                                SortParameter as String,
    //                                SortName As String)
    //
    // Purpose:  Print all users that meet the given criteria.
    //
    // Inputs:
    //   CurrencyField - name of the currency field to check
    //   CurrencyCriteria - the criteria for the currency field. if
    //                   the CurrencyField value is equal to the
    //                   CurrencyCriteria, the user information is
    //                   printed.
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMembersStatusInformation(
                                            $CurrencyField,
                                            $CurrencyCriteria,
                                            $SortParameter,
                                            $SortName)
    {        
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 99;
        $LeftMargin = 0;
        $PageLength = 60;
        $HeaderLength = 1;
        $KeyCodeLength = 6;
        $MemberNameFieldLength = 16;
        $AddressFieldLength = 20;
        $CityFieldLength = 11;
        $StateFieldLength = 2;
        $ZipFieldLength = 5;
        $HomeFieldLength = 14;
        $WorkFieldLength = 14;
    
        // printer setup
        PrinterSetup(9);
        $PageHeader = "************ " . $AircraftScheduling_company . " ROSTER ************";
        
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
        
        // remove the "_" from the currency field for printing
        $tmpCurrencyField = "";
        for ($i = 1; $i <= Len($CurrencyField); $i++) 
        {
            $tmpCurrencyFieldChar = Mid($CurrencyField, $i, 1);
            if ($tmpCurrencyFieldChar != "_") 
            {
                $tmpCurrencyField = $tmpCurrencyField . $tmpCurrencyFieldChar;
            }
            else 
            {
                $tmpCurrencyField = $tmpCurrencyField . " ";
            }
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($tmpCurrencyField . " Equal to " . $CurrencyCriteria . " Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
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
                    JustifyField("Work Phone", $CenterJustify, $WorkFieldLength) . " " .
                    JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(" " . "<br>");
        
        // create a query to get all currency rules for the rating of pilot
        $MembersResult = SQLOpenRecordset(
            "SELECT * FROM AircraftScheduling_person ORDER BY " . $SortParameter);
        
        // loop through all users and print the ones that match the given criteria
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
                              JustifyField($tmpCurrencyField . " Equal to " . $CurrencyCriteria . " Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
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
                            JustifyField("Work Phone", $CenterJustify, $WorkFieldLength) . " " .
                            JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(" " . "<br>");
            }
        
            // load the currency fields from the database
            LoadDBCurrencyFields("", $MembersRST[$Rules_Field_offset]);
            
            // lookup the currency values
            $CurrencyFieldValue = LookupCurrencyFieldname($CurrencyField);
            
            // if it matches the criteria, print the user line
            if ($CurrencyFieldValue == $CurrencyCriteria) 
            {
                // print the user record
                $MemberFullName = Trim($MembersRST[$first_name_offset]) . " " .
                                    Trim($MembersRST[$last_name_offset]);
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField(UCase($MembersRST[$username_offset]), $LeftJustify, $KeyCodeLength) . " " .
                            JustifyField($MemberFullName, $LeftJustify, $MemberNameFieldLength) . " " .
                            JustifyField($MembersRST[$address1_offset], $LeftJustify, $AddressFieldLength) . " " .
                            JustifyField($MembersRST[$city_offset], $LeftJustify, $CityFieldLength) . " " .
                            JustifyField($MembersRST[$state_offset], $LeftJustify, $StateFieldLength) . " " .
                            JustifyField($MembersRST[$zip_offset], $LeftJustify, $ZipFieldLength) . " " .
                            JustifyField($MembersRST[$phone_number_offset], $LeftJustify, $WorkFieldLength) . " " .
                            JustifyField($MembersRST[$Home_Phone_offset], $LeftJustify, $HomeFieldLength) . "<br>");
                PrintNonBreakingString(" " . "<br>");
                $TextLines = $TextLines + 2;
            }
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
            PrintNewPage();
        }
    }
    
    //********************************************************************
    //CurrencyFieldHasExpired(
    //                        CurrencyField As String,
    //                        MembersRST As MYSQL_RS)
    //
    // Purpose:  Return true if the CurrencyField has expired for the given
    //           user.
    //
    // Inputs:
    //   CurrencyField - the field to check for expiration. if the CurrencyField
    //                   has expired, the user information is printed.
    //   MembersRST - record set for the user to check
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function CurrencyFieldHasExpired($CurrencyField, $MembersRST)
    {        
        include "DatabaseConstants.inc";
        global $MaxCurrencyFieldRecords;
       
        // remove the "_" from the currency field for printing
        $CurrencyFieldNoUnderline = "";
        for ($i = 1; $i <= Len($CurrencyField); $i++) 
        {
            $CurrencyFieldNoUnderlineChar = Mid($CurrencyField, $i, 1);
            if ($CurrencyFieldNoUnderlineChar != "_") 
            {
                $CurrencyFieldNoUnderline = $CurrencyFieldNoUnderline . $CurrencyFieldNoUnderlineChar;
            } 
            else 
            {
                $CurrencyFieldNoUnderline = $CurrencyFieldNoUnderline . " ";
            }
        }
        
        // load the currency fields from the database
        LoadDBCurrencyFields("", $MembersRST[$Rules_Field_offset]);
        
        // load the information from the database and compute the curreny
        $CurrencyRuleString = array();
        $CurrencyStatusString = array();
        GetCurrencyValues(
                            LookupCurrencyFieldname("Rating"),
                            $MembersRST[$username_offset],
                            $CurrencyRuleString,
                            $CurrencyStatusString,
                            $PilotIdentificationString,
                            $FlightStatus,
                            $FlightStatusReason,
                            "C-152",
                            "ALL");
    
        // lookup the value for the CurrencyField
        for ($i = 0; $i < $MaxCurrencyFieldRecords; $i++) 
        {
            if (array_key_exists($i, $CurrencyRuleString) &&
				$CurrencyRuleString[$i] == $CurrencyFieldNoUnderline) 
            {
                // field found, has the currency expired?
                if ($CurrencyStatusString[$i] == "EXPIRED") 
                {
                    // currency field has expired, return true
                    $CurrencyFieldHasExpired = true;
                } 
                else 
                {
                    // currency field has not expired, return false
                    $CurrencyFieldHasExpired = false;
                }
               
                // finished, return to caller
                return $CurrencyFieldHasExpired;
            }
        }
        
        // field not found (shouldn't get to here)
        $CurrencyFieldHasExpired = true;
        return $CurrencyFieldHasExpired;
    }
    
    //********************************************************************
    // PrintMembersExpiredCurrency(
    //                             CurrencyField As String
    //                             SortParameter as String,
    //                             SortName As String)
    //
    // Purpose:  Print all users that have the given expired currency field.
    //
    // Inputs:
    //   CurrencyField - the field to check for expiration. if the CurrencyField
    //                   has expired, the user information is printed.
    //   SortParameter - the field to sort on for printing
    //   SortName - the name of the sort parameter
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintMembersExpiredCurrency(
                                            $CurrencyField,
                                            $SortParameter,
                                            $SortName)
    {                                            
        global $AircraftScheduling_company;
        global $RightJustify, $LeftJustify, $CenterJustify;
        
        include "DatabaseConstants.inc";

        $MaxLineLength = 99;
        $LeftMargin = 0;
        $PageLength = 60;
        $HeaderLength = 1;
        $KeyCodeLength = 6;
        $MemberNameFieldLength = 16;
        $AddressFieldLength = 20;
        $CityFieldLength = 11;
        $StateFieldLength = 2;
        $ZipFieldLength = 5;
        $HomeFieldLength = 14;
        $WorkFieldLength = 14;
     
        // printer setup
        PrinterSetup(9);
        $PageHeader = "************ " . $AircraftScheduling_company . " ROSTER ************";
        
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
        
        // remove the "_" from the currency field for printing
        $tmpCurrencyField = "";
        for ($i = 1; $i <= Len($CurrencyField); $i++) 
        {
            $tmpCurrencyFieldChar = Mid($CurrencyField, $i, 1);
            if ($tmpCurrencyFieldChar != "_") 
            {
                $tmpCurrencyField = $tmpCurrencyField . $tmpCurrencyFieldChar;
            } 
            else 
            {
                $tmpCurrencyField = $tmpCurrencyField . " ";
            }
        }
        
        // print the page header
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                      "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($tmpCurrencyField . " Has Expired " . " Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
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
                    JustifyField("Work Phone", $CenterJustify, $WorkFieldLength) . " " .
                    JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
        PrintNonBreakingString(" " . "<br>");
        
        // create a query to get all currency rules for the rating of pilot
        $MembersResult = SQLOpenRecordset(
            "SELECT * " . 
            "FROM AircraftScheduling_person " .
            "WHERE INSTR(Rules_Field, 'Member_Status,Active') " .
            "ORDER BY " . $SortParameter . "");
        
        // loop through all users and print the ones that match the given criteria
        $PageNumber = 0;
        $TextLines = 0;
        for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++)  
        {
            // make sure we don't time out (this takes a while since we have to process
            // each user's currency)
            set_time_limit(30);
            
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
                              JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($tmpCurrencyField . " Has Expired " . " Sorted by " . $SortName, $CenterJustify, $MaxLineLength) . 
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
                            JustifyField("Work Phone", $CenterJustify, $WorkFieldLength) . " " .
                            JustifyField("Home Phone", $CenterJustify, $HomeFieldLength) . "<br>");
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField($UnderLine, $CenterJustify, $MaxLineLength) . "<br>");
                PrintNonBreakingString(" " . "<br>");
            }
            
            // if currency field has expired, print the user line
            if (CurrencyFieldHasExpired($CurrencyField, $MembersRST)) 
            {
                // print the user record
                $MemberFullName = Trim($MembersRST[$first_name_offset]) . " " .
                                    Trim($MembersRST[$last_name_offset]);
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField(UCase($MembersRST[$username_offset]), $LeftJustify, $KeyCodeLength) . " " .
                            JustifyField($MemberFullName, $LeftJustify, $MemberNameFieldLength) . " " .
                            JustifyField($MembersRST[$address1_offset], $LeftJustify, $AddressFieldLength) . " " .
                            JustifyField($MembersRST[$city_offset], $LeftJustify, $CityFieldLength) . " " .
                            JustifyField($MembersRST[$state_offset], $LeftJustify, $StateFieldLength) . " " .
                            JustifyField($MembersRST[$zip_offset], $LeftJustify, $ZipFieldLength) . " " .
                            JustifyField($MembersRST[$phone_number_offset], $LeftJustify, $WorkFieldLength) . " " .
                            JustifyField($MembersRST[$Home_Phone_offset], $LeftJustify, $HomeFieldLength) . "<br>");
                PrintNonBreakingString(" " . "<br>");
                $TextLines = $TextLines + 2;
            }
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
            PrintNewPage();
        }
    }

    //********************************************************************
    // PrintSelectedMemberInformation()
    //
    // Purpose:  Print the selected user information.
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
    function PrintSelectedMemberInformation()
    {        
        global $MemberStatusActive, $MemberStatusInActive, $MemberStatusAircraft, $MemberStatusResigned;

        global $PrintMemberSigninOption;
    	global $PrintMembershipRosterNameOption;
    	global $PrintFullMembershipInfoOption;
    	global $PrintAllActiveMembersOption;
    	global $PrintAllInactiveMembersOption;
    	global $PrintAllMaintenanceKeycodesOption;
    	global $PrintAllResignedMembersOption;
    	global $PrintAllExpiredWaiversOption;
    	global $PrintAllExpiredSafetyMeetingOption;
    	global $PrintPilotStatusOption;
        global $SortSelection;
        global $Rating;
        global $MembersFromNameOfUser;
        global $MembersToNameOfUser;
        
        // build the sort name
        $SortName = Replace($SortSelection, "_", " ");
    
        // should we print the user sign-in sheet?
        if ($PrintMemberSigninOption == 1) 
        {
            PrintMemberSigninSheet($SortSelection, $SortName);
        }
        
        // should we print the membership roster?
        if ($PrintMembershipRosterNameOption == 1) 
        {
            PrintMembershipRoster($SortSelection, $SortName);
        }
        
        // should we print the full membership information for a group of users?
        if ($PrintFullMembershipInfoOption == 1) 
        {
            PrintFullMembershipInfo(
                                    $SortSelection, 
                                    $MembersFromNameOfUser, 
                                    $MembersToNameOfUser);
        }
        
        // should we print all active users?
        if ($PrintAllActiveMembersOption == 1) 
        {
            PrintMembersStatusInformation(
                                        "Member_Status",
                                        $MemberStatusActive,
                                        $SortSelection,
                                        $SortName);
        }
        
        // should we print all inactive users?
        if ($PrintAllInactiveMembersOption == 1) 
        {
            PrintMembersStatusInformation(
                                        "Member_Status",
                                        $MemberStatusInActive,
                                        $SortSelection,
                                        $SortName);
        }
        
        // should we print all maintenance keycodes?
        if ($PrintAllMaintenanceKeycodesOption == 1) 
        {
            PrintMembersStatusInformation(
                                            "Member_Status",
                                            $MemberStatusAircraft,
                                            $SortSelection,
                                            $SortName);
        }
        
        // should we print all resigned users?
        if ($PrintAllResignedMembersOption == 1) 
        {
            PrintMembersStatusInformation(
                                            "Member_Status",
                                            $MemberStatusResigned,
                                            $SortSelection,
                                            $SortName);
        }
        
        // should we print all users with expired waivers?
        if ($PrintAllExpiredWaiversOption == 1) 
        {
            PrintMembersExpiredCurrency(
                                        "Waiver",
                                        $SortSelection,
                                        $SortName);
        }
        
        // should we print all users with expired safety meetings
        if ($PrintAllExpiredSafetyMeetingOption == 1) 
        {
            PrintMembersExpiredCurrency(
                                        "Safety_Meeting",
                                        $SortSelection,
                                        $SortName);
        }
        
        // print the users with pilot status selected
        if ($PrintPilotStatusOption == 1) 
        {
            PrintMembersStatusInformation(
                                    "Rating",
                                    $Rating,
                                    $SortSelection,
                                    $SortName);
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
    if(count($_POST) > 0 && $PrintMemberInformation == "Submit") 
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
        
        // print the requested inventory
        PrintSelectedMemberInformation();
        
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
	echo "<FORM NAME='main' ACTION='PrintMemberInformation.php' METHOD='POST'>";
	
	// set the default print selections
    $PrintMemberSigninOption = 1;
	$PrintMembershipRosterNameOption = 0;
	$PrintFullMembershipInfoOption = 0;
	$PrintAllActiveMembersOption = 0;
	$PrintAllInactiveMembersOption = 0;
	$PrintAllMaintenanceKeycodesOption = 0;
	$PrintAllResignedMembersOption = 0;
	$PrintAllExpiredWaiversOption = 0;
	$PrintAllExpiredSafetyMeetingOption = 0;
	$PrintPilotStatusOption = 0;

    // start the table to display the report information
    echo "<center>";
    echo "<h2>Print User Information</h2>";
    echo "<table border=0>";
    
    // Sort Inventory Reports By: 
    echo "<tr>";
    echo "<td class=CC colspan=2>Sort Reports by:&nbsp;";
    BuildMemberSortSelector($SortSelection);
    echo "</td>";
    echo "</tr>";
    
    // skip some space
    echo "<tr>";
    echo "<td>&nbsp;";
    echo "</td>";
    echo "</tr>";
     
    // Print User Sign-in
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintMemberSigninOption value=1 ";
	if ($PrintMemberSigninOption == 1) echo "checked";
	echo ">Print User Sign-in";
    echo "</td>";
    echo "</tr>";
     
    // Print Membership Roster
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintMembershipRosterNameOption value=1 ";
	if ($PrintMembershipRosterNameOption == 1) echo "checked";
	echo ">Print Membership Roster";
    echo "</td>";
    echo "</tr>";
         
    // Print Full Membership Information
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintFullMembershipInfoOption value=1 ";
	if ($PrintFullMembershipInfoOption == 1) echo "checked";
	echo ">Print Full Membership Information For Users:";
    echo "</td>";
    echo "<td class=CL>";
	BuildMemberSelector($MembersFromNameOfUser, false, "MembersFrom");
	echo "&nbsp;To&nbsp;";
	BuildMemberSelector($MembersToNameOfUser, false, "MembersTo");
    echo "</td>";
    echo "</tr>";
     
    // Print All Active Users
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintAllActiveMembersOption value=1 ";
	if ($PrintAllActiveMembersOption == 1) echo "checked";
	echo ">Print All Active Users";
    echo "</td>";
    echo "</tr>";
     
    // Print All Inactive Users
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintAllInactiveMembersOption value=1 ";
	if ($PrintAllInactiveMembersOption == 1) echo "checked";
	echo ">Print All Inactive Users";
    echo "</td>";
    echo "</tr>";
     
    // Print All Aircraft Maintenance Keycodes
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintAllMaintenanceKeycodesOption value=1 ";
	if ($PrintAllMaintenanceKeycodesOption == 1) echo "checked";
	echo ">Print All Aircraft Maintenance Keycodes";
    echo "</td>";
    echo "</tr>";
     
    // Print All Resigned Users
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintAllResignedMembersOption value=1 ";
	if ($PrintAllResignedMembersOption == 1) echo "checked";
	echo ">Print All Resigned Users";
    echo "</td>";
    echo "</tr>";
     
    // Print All Users with Expired Waivers
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintAllExpiredWaiversOption value=1 ";
	if ($PrintAllExpiredWaiversOption == 1) echo "checked";
	echo ">Print All Users with Expired Waivers";
    echo "</td>";
    echo "</tr>";
     
    // Print All Users with Expired Safety Meeting
    echo "<tr>";
    echo "<td class=CL colspan=2>";
	echo "<input type=checkbox name=PrintAllExpiredSafetyMeetingOption value=1 ";
	if ($PrintAllExpiredSafetyMeetingOption == 1) echo "checked";
	echo ">Print All Users with Expired Safety Meeting";
    echo "</td>";
    echo "</tr>";
     
    // Print All Users with Pilot Status:
    echo "<tr>";
    echo "<td class=CL>";
	echo "<input type=checkbox name=PrintPilotStatusOption value=1 ";
	if ($PrintPilotStatusOption == 1) echo "checked";
	echo ">Print All Users with Pilot Status:";
    echo "</td>";
    echo "<td class=CL>";
	BuildPilotIdentificationSelector($Rating, false);
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
    echo "<TD><input name='PrintMemberInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
