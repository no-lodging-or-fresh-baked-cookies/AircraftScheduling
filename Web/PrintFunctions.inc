<?php
//-----------------------------------------------------------------------------
// 
// PrintFunctions
// 
// PURPOSE: Provides the functions for printing reports.
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

require_once("StringFunctions.inc");
require_once("CurrencyFunctions.inc");

// global constants
$Cash = 0;                     // use cash in the transactions
$CreditCard = 1;               // use credit card in the transactions

//********************************************************************
// SetupPrintFunctions($ReturnURL)
//
// Purpose: Setup up the form for the print functions.
//
// Inputs:
//   ReturnURL - URL to return to after printing is completed
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function SetupPrintFunctions($ReturnURL)
{            
    // put the header information in the form
    echo "<HEAD>";
    echo "<STYLE TYPE='text/css'>";
    echo "P.breakhere {page-break-before: always}";
    echo "</STYLE>";        
    echo "</HEAD>";
    echo "<BODY onload='CompleteForm()'>";
    echo "<FORM NAME='main'>";

    // save the URL for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var ReturnURL = '$ReturnURL';";
    echo "</SCRIPT>";
}

//********************************************************************
// CompletePrintFunctions()
//
// Purpose: Complete the print function form.
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
function CompletePrintFunctions()
{                  
    // end the form
    echo "</FORM>";
    
    // complete the form
    echo "</BODY>";
}

//********************************************************************
// PrintNonBreakingString($PrintString)
//
// Purpose: Print the given string with the spaces converted to
//          non-breaking spaces.
//
// Inputs:
//   PrintString - string to print
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintNonBreakingString($PrintString)
{
    print Replace($PrintString, " ", "&nbsp;");
}

//********************************************************************
// PrintNewPage()
//
// Purpose: Generate the HTML to print a new page.
//
// Inputs:
//   none
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Note: For this to work, the following lines must be in the header:
//        echo "<HEAD>";
//        echo "<STYLE TYPE='text/css'>";
//        echo "P.breakhere {page-break-before: always}";
//        echo "</STYLE>";
//
//*********************************************************************
function PrintNewPage()
{
    print "<P CLASS='breakhere'>";
}
                               
//********************************************************************
// PrintSingleDayAircraftFaultRecord(
//                           AircraftID as string,
//                           BillingDate As String,
//                           FaultType as FaultStateType,
//                           NewPageIsNeeded As Boolean)
//
// Purpose:  Print the aircraft fault records for the given aircraft
//           and the given dates.
//
// Inputs:
//   AircraftID - the aircraft ID to print the fault records for. Leave
//                blank for all aircraft.
//   BillingDate - the date to print the fault records for.
//   FaultType - the type of faults (open, repaired or deferred) to print
//   NewPageIsNeeded - set to true if a new page should be printed
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintSingleDayAircraftFaultRecord(
                                    $AircraftID,
                                    $BillingDate,
                                    $FaultType,
                                    &$NewPageIsNeeded)
{
    global $NotRepairedString, $RepairedString, $DeferredString, $ClosedString;
    global $OpenFault, $Repaired, $Deferred, $ClosedFault;
        
    include "DatabaseConstants.inc";
               
    // open the aircraft recordset
    if (Len(Trim($AircraftID)) == 0 || UCase($AircraftID) == "ALL")
    {
        // aircraft ID not specified, do all aircraft IDs
    	$sql = "SELECT * FROM AircraftScheduling_aircraft";
    	$res = sql_query($sql);
         
        // if we didn't have any errors
        if(!$res) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDayAircraftFaultRecord", $sql);
        }
    }
    else
    {
        // aircraft ID specified, open recordset for just that aircraft
    	$sql = 
                "SELECT * FROM AircraftScheduling_aircraft WHERE " .
                    "(n_number = '" . Trim($AircraftID) . "')";
    	$res = sql_query($sql);
         
        // if we didn't have any errors
        if(!$res) 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintSingleDayAircraftFaultRecord", $sql);
        }
    }
                 
    // see if we found any aircraft records
    if (sql_count($res) > 0)
    {
        // loop through all records and print the results
		for($AircraftRecordCnt=0; $AircraftRST = sql_row($res, $AircraftRecordCnt); $AircraftRecordCnt++) 
        {
            // only process rental aircraft
            if ($AircraftRST[$hourly_cost_offset] > 0)
            {
                // get any open squawks of the type requested for this date and aircraft
                switch ($FaultType)
                {
                case $OpenFault:
                    // only print open faults
                    $SquawksSQL = "SELECT * FROM Squawks WHERE " .
                                        "(Aircraft = '" . Trim($AircraftRST[$n_number_offset]) . "' AND " .
                                        "Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                        "Repair_Date = ' ')";
                    break;
                case $Repaired:
                    // only print closed (repaired) faults
                    $SquawksSQL = "SELECT * FROM Squawks WHERE " .
                                        "(Aircraft = '" . Trim($AircraftRST[$n_number_offset]) . "' AND " .
                                        "Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                        "Repair_Date <> ' ')";
                    break;
                case $Deferred:
                    // only print deferred faults
                    $SquawksSQL = "SELECT * FROM Squawks WHERE " .
                                        "(Aircraft = '" . Trim($AircraftRST[$n_number_offset]) . "' AND " .
                                        "Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                        "Repair_Date = '" . $DeferredString . "')";
                    break;
                case $Closed:
                    // only print closed faults
                    $SquawksSQL = "SELECT * FROM Squawks WHERE " .
                                        "(Aircraft = '" . Trim($AircraftRST[$n_number_offset]) . "' AND " .
                                        "Date = '" . FormatField($BillingDate, "DatabaseDate") . "' AND " .
                                        "Repair_Date = '" . $ClosedString . "')";
                    break;
                }
            	$SquawkResult = sql_query($SquawksSQL);
                 
                // if we didn't have any errors
                if(!$SquawkResult) 
                {
                    // error processing database request, tell the user
                    DisplayDatabaseError("PrintSingleDayAircraftFaultRecord", $SquawksSQL);
                }
                            
                // see if we found any squawks for this aircraft and date
                if (sql_count($SquawkResult) > 0)
                {
                    // if this is the second squawk record we found, start a new page
                    if ($NewPageIsNeeded) PrintNewPage();
                    
                    // loop through all records and print the fault record
		            for($SquawkRecordCnt=0; $SquawksRST = sql_row($SquawkResult, $SquawkRecordCnt); $SquawkRecordCnt++) 
                    {
                        // get the member information from the database
    
                        // open a record set for the member information
                    	$MemberSQL = "SELECT * FROM AircraftScheduling_person WHERE username='" .
                                    UCase(Trim($SquawksRST[$SquawkKeyCode_offset])) . "'";
                    	$MemberResult = sql_query($MemberSQL);
                         
                        // if we didn't have any errors
                        if(!$MemberResult) 
                        {
                            // error processing database request, tell the user
                            DisplayDatabaseError("PrintSingleDayAircraftFaultRecord", $sql);
                        }
                        
                        // if we didn't find the member's keycode, mark as unknown
                        if (sql_count($MemberResult) == 0)
                        {
                            // member not found, mark as unknown
                            $FullName = "Unknown";
                            $HomePhone = "Unknown";
                            $WorkPhone = "Unknown";
                        }
                        else
                        {
                            // member found, get the name and phone numbers
                            $MembersInformationRST = sql_row($MemberResult, 0);
                            $FullName = Trim($MembersInformationRST[$first_name_offset]) . " " .
                                        Trim($MembersInformationRST[$middle_name_offset]) . " " .
                                        Trim($MembersInformationRST[$last_name_offset]);
                            $HomePhone = $MembersInformationRST[$Home_Phone_offset];
                            $WorkPhone = $MembersInformationRST[$phone_number_offset];
                        }

                        // print the squawk fault information
                        PrintAircraftFaultRecord(
                                $AircraftRST,
                                $SquawksRST[$SquawkDescription_offset],
                                $SquawksRST[$SquawkRepair_Description_offset],
                                $SquawksRST[$SquawkRepair_Date_offset],
                                $SquawksRST[$SquawkRepair_Tach_offset],
                                $SquawksRST[$SquawkMechanic_offset],
                                $FullName,
                                $HomePhone,
                                $WorkPhone,
                                $BillingDate);
                        
                        // if we are not at the end of the recordset, print a new page
                        if ($SquawkRecordCnt < sql_count($SquawkResult) - 1) PrintNewPage();
                    }
                    
                    // new page will be needed if we print any more aircraft squawk records
                    $NewPageIsNeeded = true;
                }
            }
        }
    }
}

//********************************************************************
// PrintAircraftFaultRecord(
//                           AircraftRST as MYSQL_RS,
//                           SquawkText As String,
//                           RepairText As String,
//                           RepairDate As String,
//                           RepairTach As String,
//                           MechanicName As String,
//                           FullName As String,
//                           HomePhone As String,
//                           WorkPhone As String,
//                           FaultDate as String)
//
//
// Purpose:      Print the fault record sheet for the aircraft when
//               a new squawk is entered
//
// Inputs:
//   AircraftRST - recordset for the aircraft
//   SquawkText - description of the squawk
//   RepairText - description of the repair or blank if not repaired
//   RepairDate - date of the repair or blank if not repaired
//   RepairTach - the tach at the time of the repair or blank if not repaired
//   MechanicName - name of the mechanic repairing the sqawk
//   FullName - full name of the person logging the squawk
//   HomePhone - home phone number of the person logging the squawk
//   WorkPhone - work phone number of the person logging the squawk
//   FaultDate - date that the fault was entered
//
// Outputs:
//   none
//
// Returns:
//   none
// 
// Note:
//
//*********************************************************************
function PrintAircraftFaultRecord(
                            $AircraftRST,
                            $SquawkText,
                            $RepairText,
                            $RepairDate,
                            $RepairTach,
                            $MechanicName,
                            $FullName,
                            $HomePhone,
                            $WorkPhone,
                            $FaultDate)
{                            
    global $RightJustify, $LeftJustify, $CenterJustify;
    global $AircraftScheduling_company;
        
    include "DatabaseConstants.inc";

    $LeftMargin = 10;
    $HeaderField = 76;
    $MaxLineLength = 60;
    
    // printer setup
    PrinterSetup(9);
    
    // skip some space at the top of the form
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    
    // header
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField($AircraftScheduling_company, $CenterJustify, $HeaderField) . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField("AIRCRAFT FAULT RECORD", $CenterJustify, $HeaderField) 
                  . "<br>");
   
    // aircraft number
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField($AircraftRST[$n_number_offset], $CenterJustify, $HeaderField) 
                  . "<br>");
    
    // skip some space
    PrintNonBreakingString(" " . "<br>");
    
    // date and tach time
    PrintNonBreakingString(Space($LeftMargin) . "DATE: " .
                  JustifyField(FormatField($FaultDate, "Date"), $LeftJustify, 11) .
                  Space(7) . "@ Tach Time: " .
                  JustifyField(FormatField($AircraftRST[$tach1_offset], "Float"), $RightJustify, 8) 
                  . "<br>");
    
    // skip space
    PrintNonBreakingString(" " . "<br>");
          
    // header
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField("FAULT / COMPLAINT", $CenterJustify, $HeaderField) 
                  . "<br>");
    // skip space
      
    // fault information. since this may be more than one line, parse the line
    $TextLines = 0;
    $SquawkLine = $SquawkText;
    $PrintLine = "";
    while (Len($SquawkLine) > 0)
    {
        if (Len($PrintLine) > $MaxLineLength)
        {
            // at max line length, print the line
            PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<br>");
            $PrintLine = "";
            $TextLines = $TextLines + 1;
        }
        
        // get the next token and add to the printline
        $PrintLine = $PrintLine . GetNextToken($SquawkLine, " ") . " ";
    }
    // print the last line (if needed)
    if (Len($PrintLine) > 0)
    {
        PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<br>");
        $TextLines = $TextLines + 1;
    }
    
    // if we didn't take all the lines, fill with blanks
    for ($i = $TextLines + 1; $i <= 5; $i++)
    {
        PrintNonBreakingString(" " . "<br>");
    }
    
    // skip space
    PrintNonBreakingString(" " . "<br>");
    
    // Fault entered by and phone
    PrintNonBreakingString(Space($LeftMargin) .
                "Entered By: " .
                $FullName .
                " Phone (h) " . $HomePhone .
                " Phone (w) " . $WorkPhone .
                "<br>");

    // skip some space
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    
    // header
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField("CORRECTIVE ACTION", $CenterJustify, $HeaderField) 
                  . "<br>");
    
    // skip some space
    PrintNonBreakingString(" " . "<br>");
    
    // corrective action body
    PrintNonBreakingString(Space($LeftMargin) .
                  "Deferred By:_______________________________ Transferred to RAFA-14 on:________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "If correction was deferred, enter date this RAFA-13 was re-opened:______________" 
                  . "<br>");
    
    // if we have correction action, print the corrective action otherwise print blank lines
    // so that the mechanic can fill out the form
    if (Len(Trim($RepairText)) > 0)
    {
        // repair description, print the description
        PrintNonBreakingString(" " . "<br>");
        $TextLines = 1;
        $SquawkLine = $RepairText;
        $PrintLine = "";
        while (Len($SquawkLine) > 0)
        {
            if (Len($PrintLine) > $MaxLineLength)
            {
                // at max line length, print the line
                PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<br>");
                $PrintLine = "";
                $TextLines = $TextLines + 1;
            }
            
            // get the next token and add to the printline
            $PrintLine = $PrintLine . GetNextToken($SquawkLine, " ") . " ";
        }
        // print the last line (if needed)
        if (Len($PrintLine) > 0)
        {
            PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<br>");
            $TextLines = $TextLines + 1;
        }
        
        // if we didn't take all the lines, fill with blanks
        for ($i = $TextLines + 1; $i <= 10; $i++)
        {
            PrintNonBreakingString(" " . "<br>");
        }
    }
    else
    {
        // no repair description, print the blank lines
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      "________________________________________________________________________________"
                      . "<br>");
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      "________________________________________________________________________________" 
                      . "<br>");
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      "________________________________________________________________________________" 
                      . "<br>");
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      "________________________________________________________________________________" 
                      . "<br>");
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      "________________________________________________________________________________" 
                      . "<br>");
    }
    
    // if the squawk is repaired, print the date and mechanic name otherwise print
    // blank lines so that the mechanic can fill in the form
    if (Len(Trim($RepairDate)) > 0)
    {
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      "Date: " . JustifyField($RepairDate, $LeftJustify, 16) .
                      " Mechanic: " . JustifyField($MechanicName, $LeftJustify, 26) .
                      " Cert No:_____________" 
                      . "<br>");
    
    }
    else
    {
        // no repair description, print the blank lines
        PrintNonBreakingString(" " . "<br>");
        PrintNonBreakingString(Space($LeftMargin) .
                      "Date:_______________ Mechanic:__________________________ Cert No:_____________" 
                      . "<br>");
    }
                  
    // skip some space
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    
    // header
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField("ENGINE RUN TIME TO BE CHARGED TO MAINTENANCE",
                  $CenterJustify, $HeaderField) 
                  . "<br>");
    
    // skip some space
    PrintNonBreakingString(" " . "<br>");
    
    // ending hobbs and ending tach
    PrintNonBreakingString(Space($LeftMargin) .
                  Space(10) . JustifyField("ENDING HOBBS", $LeftJustify, 14) .
                              "___________" .
                  Space(10) . JustifyField("ENDING TACH", $LeftJustify, 14) .
                              "___________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  Space(10) . JustifyField("Begin_Hobbs", $LeftJustify, 14) .
                              "___________" .
                  Space(10) . JustifyField("BEGIN TACH", $LeftJustify, 14) .
                              "___________"
                  . "<br>");                              
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  Space(10) . JustifyField("TOTAL HOBBS", $LeftJustify, 14) .
                              "___________" .
                  Space(10) . JustifyField("TOTAL TACH", $LeftJustify, 14) .
                              "___________"
                  . "<br>");
                  
    // skip some space
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    
    // header
    PrintNonBreakingString(Space($LeftMargin) .
                  JustifyField("REMARKS & ADDITIONAL COMMENTS",
                  $CenterJustify, $HeaderField) 
                  . "<br>");
    
    // remarks & additional comments body
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________"
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                  "________________________________________________________________________________" 
                  . "<br>");
    
    // skip some space
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    PrintNonBreakingString(" " . "<br>");
    
    // footer
    PrintNonBreakingString(Space($LeftMargin) .
                  "RAFA-13 (3/97)" .  
                  "<br>");
}

//********************************************************************
// PrintDARDetailReport(
//                   CreditCardStartDate as string, 
//                   CreditCardEndDate as string,
//                   CashStartDate As String, 
//                   CashEndDate As String)
//
// Purpose:  Print the DAR detail report for the given dates. Note that the transactions
//           from the start to the end are added into one report.
//
// Inputs:
//   CreditCardStartDate - the start date to print the credit card billing for
//   CreditCardEndDate - the end date to print the credit card billing for
//   CashStartDate - the starting date for the cash billing information
//   CashEndDate - the end date for the cash billing information
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintDARDetailReport(
                        $CreditCardStartDate,
                        $CreditCardEndDate,
                        $CashStartDate, 
                        $CashEndDate)
{                                
    include "DatabaseConstants.inc";

    // printer setup
    PrinterSetup(9);
            
    // create a query for any credit categories
    $CategoryResult = SQLOpenRecordset(
        "SELECT * FROM Categories WHERE (" .
            "INSTR(Categories.GLAC, 'C') > 0) " .
            "ORDER BY Name");
    
    // print the category transactions
    if (sql_count($CategoryResult) > 0) 
    {
        for($CategoryCnt=0; $CategoryRST = sql_row($CategoryResult, $CategoryCnt); $CategoryCnt++)
        {
            // a category, print the description, GLAC account and total
            $TransactionDescription = $CategoryRST[$CategoryName_offset];
            $GLACAccount = $CategoryRST[$CategoryGLAC_offset];
            $PageIsBlank = true;
            
            // print the cash and credit card transactions for the given dates
            PrintCategoryDetail(
                                    $TransactionDescription,
                                    $GLACAccount,
                                    $CreditCardStartDate,
                                    $CreditCardEndDate,
                                    $CashStartDate,
                                    $CashEndDate,
                                    $PageIsBlank);
            
            // if we are not at the end of records, start a new page
            if ($CategoryCnt < (sql_count($CategoryResult) - 1) && !$PageIsBlank) 
            {
                PrintNewPage();
            }
        }
    }
    
    // start a new page for the deductions
    PrintNewPage();
            
    // create a query for any deduction categories
    $CategoryResult = SQLOpenRecordset(
        "SELECT * FROM Categories WHERE (" .
            "INSTR(Categories.GLAC, 'D') > 0) " .
            "ORDER BY Name");
    
    // print the category transactions
    if (sql_count($CategoryResult) > 0) 
    {
        for($CategoryCnt=0; $CategoryRST = sql_row($CategoryResult, $CategoryCnt); $CategoryCnt++)
        {
            // a category, print the description, GLAC account and total
            $TransactionDescription = $CategoryRST[$CategoryName_offset];
            $GLACAccount = $CategoryRST[$CategoryGLAC_offset];
            $PageIsBlank = true;
            
            // print the cash and credit card transactions for the given dates
            PrintCategoryDetail(
                                    $TransactionDescription,
                                    $GLACAccount,
                                    $CreditCardStartDate,
                                    $CreditCardEndDate,
                                    $CashStartDate,
                                    $CashEndDate,
                                    $PageIsBlank);
            
            // if we are not at the end of records, start a new page
            if ($CategoryCnt < (sql_count($CategoryResult) - 1) && !$PageIsBlank) 
            {
                PrintNewPage();
            }
        }
    }
}

//********************************************************************
// PrintDARReport(
//                   CreditCardStartDate as string, 
//                   CreditCardEndDate as string,
//                   CashStartDate As String, 
//                   CashEndDate As String)
//
// Purpose:  Print the DAR report for the given dates. Note that the transactions
//           from the start to the end are added into one report.
//
// Inputs:
//   CreditCardStartDate - the start date to print the credit card billing for
//   CreditCardEndDate - the end date to print the credit card billing for
//   CashStartDate - the starting date for the cash billing information
//   CashEndDate - the end date for the cash billing information
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintDARReport(
                    $CreditCardStartDate, 
                    $CreditCardEndDate,
                    $CashStartDate, 
                    $CashEndDate)
{                    
    global $Cash, $CreditCard;
    global $RightJustify, $LeftJustify, $CenterJustify;
    global $FBOLocation;
        
    include "DatabaseConstants.inc";

    $LeftMargin = 6;
    $PageLength = 60;
    $HeaderLength = 0;
    $ColumnWidth1 = 8;
    $ColumnWidth2 = 21;
    $ColumnWidth3 = 11;
    $ColumnWidth4 = 16;
    $ColumnWidth5 = 16;
    $MaxLineLength = $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 +
                            $ColumnWidth4 + $ColumnWidth5 + 4 + 2;
    
    // printer setup
    PrinterSetup(9);
    
    // build the underline string
    $UnderLine = "";
    for ($i = 1; $i <= $MaxLineLength - 2; $i++)
    {
        $UnderLine = $UnderLine . "_";
    }
    
    // skip some space at the top of the form
    for ($i = 0; $i < $HeaderLength; $i++) 
    {
        PrintNonBreakingString(" " . "<br>");
    }
    
    // print the title lines
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("DAILY ACTIVITY REPORT",
                            $CenterJustify, $MaxLineLength) . 
                            "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("CREDIT CARD CHARGES" .
                            " FROM " .
                            FormatField($CreditCardStartDate, "Date") .
                            " TO " .
                            FormatField($CreditCardEndDate, "Date"),
                            $CenterJustify, $MaxLineLength) . 
                            "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("CASH CHARGES" .
                            " FROM " .
                            FormatField($CashStartDate, "Date") .
                            " TO " .
                            FormatField($CashEndDate, "Date"),
                            $CenterJustify, $MaxLineLength) . 
                            "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                " " . $UnderLine . " " . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" .
                JustifyField("INSTALLATION: " . $FBOLocation . "       FUND:  IMWRF      LOCATION: FLYING CLUB LP", $CenterJustify, $MaxLineLength - 2) .
                "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("| DC INST| FUND|LOC| DATE (YMD)            | TRANSACTION ID |       |  |  |  |", $LeftJustify, $MaxLineLength) . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("| 1 2 3 4|5 6 7|8 9| 10 11 12 13 14 15     | 17 18 19 20 21 |       |  |78|7 |", $LeftJustify, $MaxLineLength) . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("|        |     |   |                       |                |                |", $LeftJustify, $MaxLineLength) . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField("4 T DW", $CenterJustify, $ColumnWidth1) . "|" .
                JustifyField("100", $CenterJustify, 5) . "|" .
                JustifyField("45", $RightJustify, 3) . "|" .
                JustifyField(FormatField($CashEndDate, "Date"), $CenterJustify, 23) . "|" .
                JustifyField("CRV", $CenterJustify, $ColumnWidth4) . "|" .
                JustifyField("CR", $RightJustify, $ColumnWidth5) . "|" . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("|        |     |   |                       |                |                |", $LeftJustify, $MaxLineLength) . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("|        |    TRANSACTION      |   GLAC    |                |                |", $LeftJustify, $MaxLineLength) . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                JustifyField("|        |    DESCRIPTION      |  ACCOUNT  |  CASH AMOUNT   |      MEMO      |", $LeftJustify, $MaxLineLength) . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField(" ", $CenterJustify, $ColumnWidth1) . "|" .
                JustifyField($UnderLine, $LeftJustify, $ColumnWidth2 + $ColumnWidth3 + $ColumnWidth4 + $ColumnWidth5 + 3) .
                "|" . "<br>");
            
    // create a query for any credit categories
    $CategoryResult = SQLOpenRecordset(
        "SELECT * FROM Categories WHERE (" .
            "INSTR(Categories.GLAC, 'C') > 0) " .
            "ORDER BY Name");
    
    // print the credit transactions
    $CreditTotal = 0;
    $TotalCreditCardPurchases = 0;
    $TotalFuelReimbursements = 0;
    for ($i = 0; $i <= 32; $i++)
    {
        // build the line label
        if ($i < 26) 
        {
            $LineLabel = Chr($i + ord("A"));
        } 
        else 
        {
            $LineLabel = Chr(($i - 26) + ord("a"));
        }
        
        // if we have a category, print that as this line otherwise print a blank line
        if ($i > (sql_count($CategoryResult) - 1)) 
        {
            // no more categories, print blanks
            $TransactionDescription = " ";
            $GLACAccount = " ";
            $CashAmount = " ";
        } 
        else 
        {
            // get the next record
            $CategoryRST = sql_row($CategoryResult, $i);
            
            // a credit category, print the description, GLAC account and total
            $TransactionDescription = $CategoryRST[$CategoryName_offset];
            $GLACAccount = $CategoryRST[$CategoryGLAC_offset];
            
            // add the credit card totals for the given dates
            $CategoryTotal = ComputeCategoryTotal(
                                                $CategoryRST[$CategoryName_offset],
                                                $CreditCardStartDate, $CreditCardEndDate,
                                                $CreditCard,
                                                $TotalCreditCardPurchases,
                                                $TotalFuelReimbursements);
            $TotalCreditCardPurchases = $TotalCreditCardPurchases + $CategoryTotal;
            
            // add the cash totals for the given dates
            $CategoryTotal = $CategoryTotal + ComputeCategoryTotal(
                                                $CategoryRST[$CategoryName_offset],
                                                $CashStartDate, $CashEndDate,
                                                $Cash,
                                                $TotalCreditCardPurchases,
                                                $TotalFuelReimbursements);
            if ($CategoryTotal > 0) 
            {
                $CashAmount = FormatField(Str($CategoryTotal), "Currency");
                $CreditTotal = $CreditTotal + $CategoryTotal;
            } 
            else 
            {
                $CashAmount = " ";
            }
        }
        
        // print the transaction line
        if ($LineLabel == "S") 
        {
            // special line, print "CR"
            PrintNonBreakingString(Space($LeftMargin) . "|" .
                        JustifyField("CR   " . $LineLabel, $RightJustify, $ColumnWidth1) . "|" .
                        JustifyField($TransactionDescription, $LeftJustify, $ColumnWidth2) . "|" .
                        JustifyField($GLACAccount, $LeftJustify, $ColumnWidth3) . "|" .
                        JustifyField($CashAmount, $RightJustify, $ColumnWidth4) . "|" .
                        JustifyField(" ", $LeftJustify, $ColumnWidth5) . "|" . "<br>");
        } 
        else 
        {
            PrintNonBreakingString(Space($LeftMargin) . "|" .
                        JustifyField($LineLabel, $RightJustify, $ColumnWidth1) . "|" .
                        JustifyField($TransactionDescription, $LeftJustify, $ColumnWidth2) . "|" .
                        JustifyField($GLACAccount, $LeftJustify, $ColumnWidth3) . "|" .
                        JustifyField($CashAmount, $RightJustify, $ColumnWidth4) . "|" .
                        JustifyField(" ", $LeftJustify, $ColumnWidth5) . "|" . "<br>");
        }
    }
    
    // print the total sales line
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField("h. TOTAL SALES, REV & INC (ADD LINES A-g)", $LeftJustify, $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 + 2) . "|" .
                JustifyField(FormatField(Str($CreditTotal), "Currency"), $RightJustify, $ColumnWidth4) . "|" .
                JustifyField(" ", $LeftJustify, $ColumnWidth5) . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
            
    // create a query for any deduction categories
    $CategoryResult = SQLOpenRecordset(
        "SELECT * FROM Categories WHERE (" .
            "INSTR(Categories.GLAC, 'D') > 0) " .
            "ORDER BY Name");
    
    // print the deduction transactions
    $DebitTotal = 0;
    for ($i = 0; $i <= 9; $i++)
    {
        // build the line label
        $LineLabel = Chr(($i) + ord("i"));
        
        // if we have a category, print that as this line otherwise print a blank line
        if ($i > (sql_count($CategoryResult) - 1)) 
        {
            // no more categories, print blanks
            $TransactionDescription = " ";
            $GLACAccount = " ";
            $CashAmount = " ";
        } 
        else 
        {
            // get the next record
            $CategoryRST = sql_row($CategoryResult, $i);
            
            // a debit category, print the description, GLAC account and total
            $TransactionDescription = $CategoryRST[$CategoryName_offset];
            $GLACAccount = $CategoryRST[$CategoryGLAC_offset];
            // add the credit card totals for the given dates
            $CategoryTotal = ComputeCategoryTotal(
                                                $CategoryRST[$CategoryName_offset],
                                                $CreditCardStartDate, $CreditCardEndDate,
                                                $CreditCard,
                                                $TotalCreditCardPurchases,
                                                $TotalFuelReimbursements);
            
            // add the cash totals for the given dates
            $CategoryTotal = $CategoryTotal + ComputeCategoryTotal(
                                                $CategoryRST[$CategoryName_offset],
                                                $CashStartDate, $CashEndDate,
                                                $Cash,
                                                $TotalCreditCardPurchases,
                                                $TotalFuelReimbursements);
            if ($CategoryTotal > 0) 
            {
                $CashAmount = FormatField(Str($CategoryTotal), "Currency");
                $DebitTotal = $DebitTotal + $CategoryTotal;
            } 
            else 
            {
                $CashAmount = " ";
            }
        }
        
        // print the transaction line
        if ($LineLabel == "n") 
        {
            // special line, print "DR"
            PrintNonBreakingString(Space($LeftMargin) . "|" .
                        JustifyField("DR   " . $LineLabel, $RightJustify, $ColumnWidth1) . "|" .
                        JustifyField($TransactionDescription, $LeftJustify, $ColumnWidth2) . "|" .
                        JustifyField($GLACAccount, $LeftJustify, $ColumnWidth3) . "|" .
                        JustifyField($CashAmount, $RightJustify, $ColumnWidth4) . "|" .
                        JustifyField(" ", $LeftJustify, $ColumnWidth5) . "|" . "<br>");
        } 
        else 
        {
            PrintNonBreakingString(Space($LeftMargin) . "|" .
                        JustifyField($LineLabel, $RightJustify, $ColumnWidth1) . "|" .
                        JustifyField($TransactionDescription, $LeftJustify, $ColumnWidth2) . "|" .
                        JustifyField($GLACAccount, $LeftJustify, $ColumnWidth3) . "|" .
                        JustifyField($CashAmount, $RightJustify, $ColumnWidth4) . "|" .
                        JustifyField(" ", $LeftJustify, $ColumnWidth5) . "|" . "<br>");
        }
    }
    
    // print the total deductions line
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField("s. TOTAL DEDUCTIONS (ADD LINES i-r)", $LeftJustify, $ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3 + 2) . "|" .
                JustifyField(FormatField(Str($DebitTotal), "Currency"), $RightJustify, $ColumnWidth4) . "|" .
                JustifyField(" ", $LeftJustify, $ColumnWidth5) . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    
    // print the total deposits line
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField("BANK DEPOSIT AMT (h minus s)", $LeftJustify, $ColumnWidth1 + $ColumnWidth2 + 1) . "|" .
                JustifyField("D 111 00", $LeftJustify, $ColumnWidth3) . "|" .
                JustifyField(FormatField(Str($CreditTotal - $DebitTotal), "Currency"), $RightJustify, $ColumnWidth4) . "|" .
                JustifyField(" ", $LeftJustify, $ColumnWidth5) . "|" . "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
    
    // print the verification lines
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField("PREPARED BY/DATE", $CenterJustify, 18) . "|" .
                JustifyField("MANAGER VERIFICATION", $CenterJustify, 23) . "|" .
                JustifyField("CAO REVIEW/DATE", $CenterJustify, 17) . "|" .
                JustifyField("KEYPUNCHED/DATE", $CenterJustify, 15) . "|" . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField(" ", $CenterJustify, 18) . "|" .
                JustifyField(" ", $CenterJustify, 23) . "|" .
                JustifyField(" ", $CenterJustify, 17) . "|" .
                JustifyField(" ", $CenterJustify, 15) . "|" . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField(" ", $CenterJustify, 18) . "|" .
                JustifyField(" ", $CenterJustify, 23) . "|" .
                JustifyField(" ", $CenterJustify, 17) . "|" .
                JustifyField(" ", $CenterJustify, 15) . "|" . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) . "|" .
                JustifyField(" ", $CenterJustify, 18) . "|" .
                JustifyField(" ", $CenterJustify, 23) . "|" .
                JustifyField(" ", $CenterJustify, 17) . "|" .
                JustifyField(" ", $CenterJustify, 15) . "|" . 
                "<br>");
    PrintNonBreakingString(Space($LeftMargin) .
                "|" . $UnderLine . "|" . "<br>");
}
    
//********************************************************************
// PrintCategoryFlightInformation(
//                    CategoryName As String,
//                    GLACCode as String,
//                    StartDate as String,
//                    EndDate as String,
//                    CreditCardStartDate as String,
//                    CreditCardEndDate as String,
//                    CashStartDate As String,
//                    CashEndDate As String)
//                    PageIsBlank As Boolean,
//                    TotalCategoryCharges As Double,
//                    HeaderPrinted As Boolean,
//                    LineCounter As Integer,
//                    FlightResult As MYSQL_RS,
//                    FlightFieldName1 As String,
//                    FlightFieldName2 As String)
//
// Purpose: Print individual information for this category from the flight
//          table. Loop through each keycode and print a charge for
//          each member.
//
// Inputs:
//   CategoryName - name of the category to compute the totals for
//   GLACCode - GLAC code for the category
//   StartDate - the start date to print the billing for
//   EndDate - the end date to print the billing for
//   CreditCardStartDate - the start date to print the credit card billing for (header info)
//   CreditCardEndDate - the end date to print the credit card billing for (header info)
//   CashStartDate - the starting date for the cash billing information (header info)
//   CashEndDate - the end date for the cash billing information (header info)
//   PageIsBlank - set false when something is printed on the page
//   TotalCategoryCharges - total charges for this category
//   HeaderPrinted - set true after the header is printer
//   LineCounter - number of lines on the page
//   FlightResult - record set for the information to process
//   FlightFieldName1 - flight field name to add for the category charge
//   FlightFieldName2 - flight field name to add for the category charge
//                       (may be -1 if not used)
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintCategoryFlightInformation(
                            $CategoryName,
                            $GLACCode,
                            $StartDate,
                            $EndDate,
                            $CreditCardStartDate,
                            $CreditCardEndDate,
                            $CashStartDate,
                            $CashEndDate,
                            &$PageIsBlank,
                            &$TotalCategoryCharges,
                            &$HeaderPrinted,
                            &$LineCounter,
                            $FlightResult,
                            $FlightFieldName1,
                            $FlightFieldName2)
{                    
        
    include "DatabaseConstants.inc";

    // if we had any flight charges for the month, add them up
    if (sql_count($FlightResult) > 0) 
    {
        // get the first record
        $FlightCnt = 0;
        $FlightRST = sql_row($FlightResult, $FlightCnt);
        
        // save the current keycode so we can print the totals for this keycode
        $CurrentKeyCode = UCase(Trim($FlightRST[$Keycode_offset]));

        // loop through all records and add the cost
        $TotalKeycodeCharges = 0;
        while ($FlightCnt < sql_count($FlightResult))
        {            
            // get the total charge for this flight
            // don't add instructor credits to the DAR report
            if (!($FlightRST[$Instructor_Charge_offset] < 0)) 
            {
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($FlightRST[$Keycode_offset])) 
                {
                    // if FlightFieldName2 is specified, use both fields in the
                    // calculations otherwise just use FlightFieldName1
                    if ($FlightFieldName2 != -1) 
                    {
                        // use both fields
                        $TotalKeycodeCharges = $TotalKeycodeCharges +
                                            RoundToDecimalPlaces($FlightRST[$FlightFieldName1]) +
                                            RoundToDecimalPlaces($FlightRST[$FlightFieldName2]);
                    } 
                    else 
                    {
                        // only use FlightFieldName1
                        $TotalKeycodeCharges = $TotalKeycodeCharges +
                                            RoundToDecimalPlaces($FlightRST[$FlightFieldName1]);
                    }
                }
            }
            
            // move to the next record
            $FlightCnt++;
            
            // if we have finished with this keycode, print the line
            if ($FlightCnt >= (sql_count($FlightResult))) 
            {
                // end of file, add in the misc charges for this keycode
                // and category
                $TotalKeycodeCharges = $TotalKeycodeCharges +
                                        RoundToDecimalPlaces(ComputeMemberTotalMonthlyMiscCharge(
                                                                    $CurrentKeyCode,
                                                                    $StartDate,
                                                                    $EndDate,
                                                                    $CategoryName));
                $TotalCategoryCharges = $TotalCategoryCharges + $TotalKeycodeCharges;
                
                // print the charges
                PrintCategoryLine(
                                $HeaderPrinted,
                                $PageIsBlank,
                                $CurrentKeyCode,
                                $TotalKeycodeCharges,
                                $CategoryName,
                                $GLACCode,
                                $LineCounter,
                                $CreditCardStartDate,
                                $CreditCardEndDate,
                                $CashStartDate,
                                $CashEndDate);
                                
            } 
            else
            {
                // get the next database record
                $FlightRST = sql_row($FlightResult, $FlightCnt);
                
                // if the username has changed, print the totals
                if (UCase($CurrentKeyCode) != UCase($FlightRST[$Keycode_offset])) 
                {
                    // change in keycode, add in the misc charges for this keycode
                    // and category
                    $TotalKeycodeCharges = $TotalKeycodeCharges +
                                            RoundToDecimalPlaces(ComputeMemberTotalMonthlyMiscCharge(
                                                                        $CurrentKeyCode,
                                                                        $StartDate,
                                                                        $EndDate,
                                                                        $CategoryName));
                    $TotalCategoryCharges = $TotalCategoryCharges + $TotalKeycodeCharges;
                                                                        
                    // print the charges
                    PrintCategoryLine(
                                    $HeaderPrinted,
                                    $PageIsBlank,
                                    $CurrentKeyCode,
                                    $TotalKeycodeCharges,
                                    $CategoryName,
                                    $GLACCode,
                                    $LineCounter,
                                    $CreditCardStartDate,
                                    $CreditCardEndDate,
                                    $CashStartDate,
                                    $CashEndDate);
                    
                    // save the new current keycode
                    $CurrentKeyCode = $FlightRST[$Keycode_offset];
                    $TotalKeycodeCharges = 0;
               }
            }
        }
    }
}
    
//********************************************************************
// PrintCategoryChargesInformation(
//                    CategoryName As String,
//                    GLACCode as String,
//                    StartDate as String,
//                    EndDate as String,
//                    CreditCardStartDate as String,
//                    CreditCardEndDate as String,
//                    CashStartDate As String,
//                    CashEndDate As String)
//                    PageIsBlank As Boolean,
//                    TotalCategoryCharges As Double,
//                    HeaderPrinted As Boolean,
//                    LineCounter As Integer,
//                    ChargesResult As MYSQL_RS,
//                    ChargesFieldName1 As String,
//                    ChargesFieldName2 As String,
//                    CheckFlightInformation as boolean)
//
// Purpose: Print individual information for this category from the misc
//          charges table. Loop through each keycode and print a charge for
//          each member.
//
// Inputs:
//   CategoryName - name of the category to compute the totals for
//   GLACCode - GLAC code for the category
//   StartDate - the start date to print the billing for
//   EndDate - the end date to print the billing for
//   CreditCardStartDate - the start date to print the credit card billing for (header info)
//   CreditCardEndDate - the end date to print the credit card billing for (header info)
//   CashStartDate - the starting date for the cash billing information (header info)
//   CashEndDate - the end date for the cash billing information (header info)
//   PageIsBlank - set false when somethis is printed on the page
//   TotalCategoryCharges - total charges for this category
//   HeaderPrinted - set true after the header is printer
//   LineCounter - number of lines on the page
//   ChargesResult - record set for the information to process
//   ChargesFieldName1 - Charges field name to add for the category charge
//   ChargesFieldName2 - Charges field name to add for the category charge
//                       (may be blank)
//   CheckFlightInformation - set true to test for flight records. if
//                           a record exists in the flight table, we have already
//                           processed this information, we only want to cover those
//                           charges missed by the flight processing
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintCategoryChargesInformation(
                            $CategoryName,
                            $GLACCode,
                            $StartDate,
                            $EndDate,
                            $CreditCardStartDate,
                            $CreditCardEndDate,
                            $CashStartDate,
                            $CashEndDate,
                            &$PageIsBlank,
                            &$TotalCategoryCharges,
                            &$HeaderPrinted,
                            &$LineCounter,
                            $ChargesResult,
                            $ChargesFieldName1,
                            $ChargesFieldName2,
                            $CheckFlightInformation)
{                        
        
    include "DatabaseConstants.inc";

    // if we had any misc charges for the month, add them up
    if (sql_count($ChargesResult) > 0) 
    {
        // get the first record
        $ChargesCnt = 0;
        $ChargesRST = sql_row($ChargesResult, $ChargesCnt);
        
        // save the current keycode so we can print the totals for this keycode
        $CurrentKeyCode = UCase(Trim($ChargesRST[$ChargesKeyCode_offset]));

        // loop through all records and add the cost
        $TotalKeycodeCharges = 0;
        // get the total charge for this misc charge
        while ($ChargesCnt < sql_count($ChargesResult))
        {            
            // if we have already processed this information in the flight table and
            // the caller requested that we test for flight records,
            // ignore this record (flight table processing adds misc charges, we only
            // want to cover those charges missed by the flight processing)
            // create a query for the flight charges for the date and for this
            // keycode for the month
            if ($CheckFlightInformation) 
            {
                // caller wants to check the flight records
                $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                        "Flight.Keycode = '" .
                            $ChargesRST[$ChargesKeyCode_offset] .
                        "') " . "ORDER BY Keycode");
                
                // if a duplicate record was found clear the processing flag
                if (sql_count($FlightResult) > 0) 
                {
                    $ProcessThisRecord = false;
                } 
                else 
                {
                    $ProcessThisRecord = true;
                }
           } 
           else 
           {
                // user asked to not check the flight records, force the
                // processing of this record
                $ProcessThisRecord = true;
            }
            
            // if we didn't find a record in the flight table, we haven't processed
            // this record, we will do so now
            if ($ProcessThisRecord) 
            {
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($ChargesRST[$ChargesKeyCode_offset])) 
                {
                    // if ChargesFieldName2 is specified, use both fields in the
                    // calculations otherwise just use ChargesFieldName1
                    if ($ChargesFieldName2 != -1) 
                    {
                        // use both fields
                        $TotalKeycodeCharges = $TotalKeycodeCharges +
                                            RoundToDecimalPlaces($ChargesRST[$ChargesFieldName1]) +
                                            RoundToDecimalPlaces($ChargesRST[$ChargesFieldName2]);
                    } 
                    else 
                    {
                        // only use ChargesFieldName1
                        $TotalKeycodeCharges = $TotalKeycodeCharges +
                                            RoundToDecimalPlaces($ChargesRST[$ChargesFieldName1]);
                    }
                }
            }
            
            // move to the next record
            $ChargesCnt++;
            
            // if we have finished with this keycode, print the line
            if ($ChargesCnt >= (sql_count($ChargesResult))) 
            {
                // add keycode charges to total charges
                $TotalCategoryCharges = $TotalCategoryCharges + $TotalKeycodeCharges;
                
                // print the charges
                PrintCategoryLine(
                                $HeaderPrinted,
                                $PageIsBlank,
                                $CurrentKeyCode,
                                $TotalKeycodeCharges,
                                $CategoryName,
                                $GLACCode,
                                $LineCounter,
                                $CreditCardStartDate,
                                $CreditCardEndDate,
                                $CashStartDate,
                                $CashEndDate);
                                
            } 
            else
            {
                // get the next database record
                $ChargesRST = sql_row($ChargesResult, $ChargesCnt);
                
                // if the username has changed, print the totals
                if (UCase($CurrentKeyCode) != UCase($ChargesRST[$ChargesKeyCode_offset])) 
                {
                    // change in keycode, add keycode charges to total charges
                    $TotalCategoryCharges = $TotalCategoryCharges + $TotalKeycodeCharges;
                    
                    //print the charges
                    PrintCategoryLine(
                                    $HeaderPrinted,
                                    $PageIsBlank,
                                    $CurrentKeyCode,
                                    $TotalKeycodeCharges,
                                    $CategoryName,
                                    $GLACCode,
                                    $LineCounter,
                                    $CreditCardStartDate,
                                    $CreditCardEndDate,
                                    $CashStartDate,
                                    $CashEndDate);
                    
                    // save the new current keycode
                    $CurrentKeyCode = $ChargesRST[$ChargesKeyCode_offset];
                    $TotalKeycodeCharges = 0;
               }
            }
        }
    }
}

//********************************************************************
// PrintCategoryDetail(
//                    CategoryName As String,
//                    GLACCode as String,
//                    CreditCardStartDate as String,
//                    CreditCardEndDate as String,
//                    CashStartDate As String,
//                    CashEndDate As String)
//                    PageIsBlank as Boolean)
//
// Purpose:  Print the detailed category records for the dates given
//           for the given category.
//
// Inputs:
//   CategoryName - name of the category to compute the totals for
//   GLACCode - GLAC code for the category
//   CreditCardStartDate - the start date to print the credit card billing for
//   CreditCardEndDate - the end date to print the credit card billing for
//   CashStartDate - the starting date for the cash billing information
//   CashEndDate - the end date for the cash billing information
//
// Outputs:
//   PageIsBlank - set false when something is printed on the page
//
// Returns:
//   none
//*********************************************************************
function PrintCategoryDetail(
                    $CategoryName,
                    $GLACCode,
                    $CreditCardStartDate,
                    $CreditCardEndDate,
                    $CashStartDate,
                    $CashEndDate,
                    &$PageIsBlank)
{    
    global $Cash, $CreditCard;
        
    include "DatabaseConstants.inc";

    $LineCounter = 0;
    $TotalCategoryCharges = 0;
    
    // header has not yet been printed
    $HeaderPrinted = false;
           
    for ($TypeOfTransaction = $Cash; $TypeOfTransaction <= $CreditCard; $TypeOfTransaction++)
    {
        // handle the category names, some are special
        if (UCase(Trim($CategoryName)) == "FLIGHT TIME") 
        {
            // special category - compute flight time
            // create a query for the flight charges for the date and for all aircraft for the month
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                        "Flight.Keycode <> 'OPEN' AND " .
                        "Flight.Keycode <> 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                        "Flight.Keycode = 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
        
            // print the individual information for this category
            PrintCategoryFlightInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $FlightResult,
                                        $Aircraft_Cost_offset,
                                        -1);
                                        
            // create a query for the misc charges for the date that
            // purchased flight time
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode <> 'OPEN' AND " .
                        "Charges.Keycode <> 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode = 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
           
            // print the individual information for this category
            PrintCategoryChargesInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $ChargesResult,
                                        $Total_Price_offset,
                                        -1,
                                        true);
        } 
        else if (UCase(Trim($CategoryName)) == "INSTRUCTOR TIME") 
        {
            // special category - compute instructor time
            // create a query for the flight charges for the date and for all aircraft for the month
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                        "Flight.Keycode <> 'OPEN' AND " .
                        "Flight.Keycode <> 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                        "Flight.Keycode = 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
        
            // print the individual information for this category
            PrintCategoryFlightInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $FlightResult,
                                        $Instructor_Charge_offset,
                                        -1);
                                        
            // create a query for the misc charges for the date that
            // purchased instructor time
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode <> 'OPEN' AND " .
                        "Charges.Keycode <> 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode = 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
           
            // print the individual information for this category
            PrintCategoryChargesInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $ChargesResult,
                                        $Total_Price_offset,
                                        -1,
                                        true);
        } 
        else if (UCase(Trim($CategoryName)) == "FUEL REIMBURSEMENT") 
        {
            // special category - compute fuel payments for cross country fuel
            // create a query for the flight charges for the date and for all aircraft that
            // were reimbursed for purchased fuel
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $FlightResult = SQLOpenRecordset(
                        "SELECT * FROM Flight WHERE (" .
                            "Flight.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                            "Flight.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                            "Flight.Cross_Country_Fuel_Credit <> 0 AND " .
                            "Flight.Keycode <> 'OPEN' AND " .
                            "Flight.Keycode <> 'CASH') " .
                            "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $FlightResult = SQLOpenRecordset(
                        "SELECT * FROM Flight WHERE (" .
                            "Flight.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                            "Flight.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                            "Flight.Cross_Country_Fuel_Credit <> 0 AND " .
                            "Flight.Keycode = 'CASH') " .
                            "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
        
            // print the individual information for this category
            PrintCategoryFlightInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $FlightResult,
                                        $Cross_Country_Fuel_Credit_offset,
                                        -1);
                                        
            // create a query for the misc charges for the date that reimbursed fuel
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode <> 'OPEN' AND " .
                        "Charges.Keycode <> 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode = 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
        
            // print the individual information for this category
            PrintCategoryChargesInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $ChargesResult,
                                        $Total_Price_offset,
                                        -1,
                                        true);
         } 
        else if (UCase(Trim($CategoryName)) == "FUEL/OIL PUR. RSAFA") 
        {
            // special category - compute fuel and oil purchases
            // create a query for the flight charges for the date and for all aircraft that
            // purchased fuel or oil
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $FlightResult = SQLOpenRecordset(
                        "SELECT * FROM Flight WHERE (" .
                            "Flight.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                            "Flight.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "') AND " .
                            "(Flight.Fuel_Cost <> 0 OR " .
                            "Flight.Oil_Cost <> 0)  AND " .
                            "Flight.Keycode <> 'OPEN' AND " .
                            "Flight.Keycode <> 'CASH' " .
                            "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $FlightResult = SQLOpenRecordset(
                        "SELECT * FROM Flight WHERE (" .
                            "Flight.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                            "Flight.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "') AND " .
                            "(Flight.Fuel_Cost <> 0 OR " .
                            "Flight.Oil_Cost <> 0)  AND " .
                            "Flight.Keycode = 'CASH' " .
                            "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
        
            // print the individual information for this category
            PrintCategoryFlightInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $FlightResult,
                                        $Local_Fuel_Cost_offset,
                                        $Oil_Cost_offset);
                                        
            // create a query for the misc charges for the date that
            // purchased fuel or oil
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode <> 'OPEN' AND " .
                        "Charges.Keycode <> 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode = 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
        
            // print the individual information for this category
            PrintCategoryChargesInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $ChargesResult,
                                        $Total_Price_offset,
                                        -1,
                                        true);
        } 
        else 
        {
            // general category, get the charges
            // create a query for the misc charges for the date
            if ($TypeOfTransaction == $CreditCard) 
            {
                // credit card transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CreditCardStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CreditCardEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode <> 'OPEN' AND " .
                        "Charges.Keycode <> 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CreditCardStartDate;
                $EndDate = $CreditCardEndDate;
            } 
            else 
            {
                // cash transactions
                $ChargesResult = SQLOpenRecordset(
                    "SELECT * FROM Charges WHERE (" .
                        "Charges.Date >= '" . FormatField($CashStartDate, "DatabaseDate") . "' AND " .
                        "Charges.Date <= '" . FormatField($CashEndDate, "DatabaseDate") . "' AND " .
                        "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                        "Charges.Keycode = 'CASH') " .
                        "ORDER BY KeyCode");
                $StartDate = $CashStartDate;
                $EndDate = $CashEndDate;
            }
        
            // print the individual information for this category
            PrintCategoryChargesInformation(
                                        $CategoryName,
                                        $GLACCode,
                                        $StartDate,
                                        $EndDate,
                                        $CreditCardStartDate,
                                        $CreditCardEndDate,
                                        $CashStartDate,
                                        $CashEndDate,
                                        $PageIsBlank,
                                        $TotalCategoryCharges,
                                        $HeaderPrinted,
                                        $LineCounter,
                                        $ChargesResult,
                                        $Total_Price_offset,
                                        -1,
                                        false);
        }
    }
    
    // print the total line
    PrintCategoryLine(
                    $HeaderPrinted,
                    $PageIsBlank,
                    "TOTAL",
                    $TotalCategoryCharges,
                    $CategoryName,
                    $GLACCode,
                    $LineCounter,
                    $CreditCardStartDate,
                    $CreditCardEndDate,
                    $CashStartDate,
                    $CashEndDate);
}

//********************************************************************
// Function ComputeCategoryTotal(
//                    CategoryName As String,
//                    StartDate as string,
//                    EndDate as string)
//                    TypeOfTransaction As TransactionType,
//                    TotalCreditCardPurchases as Double,
//                    TotalFuelReimbursements As Double)
//                    As Double
//
// Purpose:  Compute the total for the month for the given category.
//           This procedure allows the flight information to have
//           different start and end dates for the report.
//
// Inputs:
//   CategoryName - name of the category to compute the totals for
//   StartDate - the start date to print the billing for
//   EndDate - the end date to print the billing for
//   TotalCreditCardPurchases - the total of all credit card purchases
//   TotalFuelReimbursements - the total of all fuel reimbursements
//   TypeOfTransaction - type of transactions (cash or credit card)
//                       to add for the category
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function ComputeCategoryTotal(
                    $CategoryName,
                    $StartDate,
                    $EndDate,
                    $TypeOfTransaction,
                    $TotalCreditCardPurchases,
                    &$TotalFuelReimbursements)
{                    
    global $Cash, $CreditCard;
        
    include "DatabaseConstants.inc";

    // handle the category names, some are special
    if (UCase(Trim($CategoryName)) == "FLIGHT TIME") 
    {
        // special category - compute flight time
        // create a query for the flight charges for the date and for all aircraft for the month
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $FlightResult = SQLOpenRecordset(
                "SELECT * FROM Flight WHERE (" .
                    "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Flight.Keycode <> 'OPEN' AND " .
                    "Flight.Keycode <> 'CASH') " .
                    "ORDER BY Date, End_Hobbs");
        } 
        else 
        {
            // cash transactions
            $FlightResult = SQLOpenRecordset(
                "SELECT * FROM Flight WHERE (" .
                    "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Flight.Keycode = 'CASH') " .
                    "ORDER BY Date, End_Hobbs");
        }
        
        // if we had any flight charges for the month, add them up
        $TotalCategoryCharges = 0;
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and add the cost
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // get the total charge for this flight
                // don't add instructor credits to the DAR report
                if (!($FlightRST[$Instructor_Charge_offset] < 0)) 
                {
                    // don't add aircraft maintance time to the DAR report
                    if (!IsAircraftID($FlightRST[$Keycode_offset])) 
                    {
                        $TotalCategoryCharges = $TotalCategoryCharges +
                                                RoundToDecimalPlaces($FlightRST[$Aircraft_Cost_offset]);
                    }
                }
            }
        }
        
        // create a query for any additional special charges for the dates and category
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode <> 'OPEN' AND " .
                    "Charges.Keycode <> 'CASH')");
        } 
        else 
        {
            // cash transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode = 'CASH')");
        }
                                                
        // compute the monthly category charges
        if (sql_count($ChargesResult) > 0) 
        {            
            // loop through all records and add the cost
            for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
            {
                // get the total charge for this item
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($ChargesRST[$ChargesKeyCode_offset])) 
                {
                    $TotalCategoryCharges = $TotalCategoryCharges +
                                            RoundToDecimalPlaces($ChargesRST[$Total_Price_offset]);
                }
            }
        }
    } 
    else if (UCase(Trim($CategoryName)) == "INSTRUCTOR TIME") 
    {
        // special category - compute instructor time
        // create a query for the flight charges for the date and for all aircraft for the month
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $FlightResult = SQLOpenRecordset(
                "SELECT * FROM Flight WHERE (" .
                    "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Flight.Keycode <> 'OPEN' AND " .
                    "Flight.Keycode <> 'CASH') " .
                    "ORDER BY Date, End_Hobbs");
        } 
        else 
        {
            // cash transactions
            $FlightResult = SQLOpenRecordset(
                "SELECT * FROM Flight WHERE (" .
                    "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Flight.Keycode = 'CASH') " .
                    "ORDER BY Date, End_Hobbs");
        }
        
        // if we had any flight charges for the month, add them up
        $TotalCategoryCharges = 0;
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and add the cost
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // get the total charge for this flight
                // don't add instructor credits to the DAR report
                if (!($FlightRST[$Instructor_Charge_offset] < 0)) 
                {
                    // don't add aircraft maintance time to the DAR report
                    if (!IsAircraftID($FlightRST[$Keycode_offset])) 
                    {
                        $TotalCategoryCharges = $TotalCategoryCharges +
                                                RoundToDecimalPlaces($FlightRST[$Instructor_Charge_offset]);
                    }
                }
            }
        }
        
        // create a query for any additional special charges for the dates and category
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode <> 'OPEN' AND " .
                    "Charges.Keycode <> 'CASH')");
        } 
        else 
        {
            // cash transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode = 'CASH')");
        }
                                                
        // compute the monthly category charges
        if (sql_count($ChargesResult) > 0) 
        {            
            // loop through all records and add the cost
            for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
            {
                // get the total charge for this item
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($ChargesRST[$ChargesKeyCode_offset])) 
                {
                    $TotalCategoryCharges = $TotalCategoryCharges +
                                            RoundToDecimalPlaces($ChargesRST[$Total_Price_offset]);
                }
            }
        }
    } 
    else if (UCase(Trim($CategoryName)) == "FUEL REIMBURSEMENT") 
    {
        // special category - compute fuel payments for cross country fuel
        // create a query for the flight charges for the date and for all aircraft that
        // were reimbursed for purchased fuel
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                        "Flight.Cross_Country_Fuel_Credit <> 0 AND " .
                        "Flight.Keycode <> 'OPEN' AND " .
                        "Flight.Keycode <> 'CASH') " .
                        "ORDER BY Date, End_Hobbs");
        } 
        else 
        {
            // cash transactions
             $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                        "Flight.Cross_Country_Fuel_Credit <> 0 AND " .
                        "Flight.Keycode = 'CASH') " .
                        "ORDER BY Date, End_Hobbs");
       }
        
        // if we had any flight charges for the month, add them up
        $TotalCategoryCharges = 0;
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and add the cost
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // get the total charge for this flight
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($FlightRST[$Keycode_offset])) 
                {
                    $TotalCategoryCharges = $TotalCategoryCharges +
                                            RoundToDecimalPlaces($FlightRST[$Cross_Country_Fuel_Credit_offset]);
                }
            }
        }
        
        // create a query for any additional special charges for the dates and category
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode <> 'OPEN' AND " .
                    "Charges.Keycode <> 'CASH')");
        } 
        else 
        {
            // cash transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode = 'CASH')");
        }
                                                
        // compute the monthly category charges
        if (sql_count($ChargesResult) > 0) 
        {
            // loop through all records and add the cost
            for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
            {
                // get the total charge for this item
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($ChargesRST[$ChargesKeyCode_offset])) 
                {
                    $TotalCategoryCharges = $TotalCategoryCharges +
                                            RoundToDecimalPlaces($ChargesRST[$Total_Price_offset]);
                }
            }
        }
            
        // save the total fuel reimbursement credits
        $TotalFuelReimbursements = $TotalFuelReimbursements + $TotalCategoryCharges;
    } 
    else if (UCase(Trim($CategoryName)) == "FUEL/OIL PUR. RSAFA") 
    {
        // special category - compute fuel and oil purchases
        // create a query for the flight charges for the date and for all aircraft that
        // purchased fuel or oil
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "') AND " .
                        "(Flight.Fuel_Cost <> 0 OR " .
                        "Flight.Oil_Cost <> 0)  AND " .
                        "Flight.Keycode <> 'OPEN' AND " .
                        "Flight.Keycode <> 'CASH'" .
                        "ORDER BY Date, End_Hobbs");
        } 
        else 
        {
            // cash transactions
            $FlightResult = SQLOpenRecordset(
                    "SELECT * FROM Flight WHERE (" .
                        "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                        "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "') AND " .
                        "(Flight.Fuel_Cost <> 0 OR " .
                        "Flight.Oil_Cost <> 0)  AND " .
                        "Flight.Keycode = 'CASH'" .
                        "ORDER BY Date, End_Hobbs");
        }
        
        // if we had any charges for the month, add them up
        $TotalCategoryCharges = 0;
        if (sql_count($FlightResult) > 0) 
        {
            // loop through all records and add the cost
            $TotalCategoryCharges = 0;
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
            {
                // get the total charge for this flight
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($FlightRST[$Keycode_offset])) 
                {
                    $TotalCategoryCharges = $TotalCategoryCharges +
                                            RoundToDecimalPlaces($FlightRST[$Local_Fuel_Cost_offset]) +
                                            RoundToDecimalPlaces($FlightRST[$Oil_Cost_offset]);
                }
            }
        }
        
        // create a query for any additional special charges for the dates and category
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode <> 'OPEN' AND " .
                    "Charges.Keycode <> 'CASH')");
        } 
        else 
        {
            // cash transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode = 'CASH')");
        }
                                                
        // compute the monthly category charges
        if (sql_count($ChargesResult) > 0) 
        {
            // loop through all records and add the cost
            for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
            {
                // get the total charge for this item
                // don't add aircraft maintance time to the DAR report
                if (!IsAircraftID($ChargesRST[$ChargesKeyCode_offset])) 
                {
                    $TotalCategoryCharges = $TotalCategoryCharges +
                                    RoundToDecimalPlaces($ChargesRST[$Total_Price_offset]);
                }
            }
        }
    } 
    else if (UCase(Trim($CategoryName)) == "MASTER CARD/VISA") 
    {
        // special category - show the total credit card purchases
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $TotalCategoryCharges = $TotalCreditCardPurchases +
                                    RoundToDecimalPlaces($TotalFuelReimbursements);
        } 
        else 
        {
            // cash transactions
            $TotalCategoryCharges = 0;
        }
    } 
    else 
    {
        // general category, get the charges
        // create a query for any charges for the date and keycode
        if ($TypeOfTransaction == $CreditCard) 
        {
            // credit card transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode <> 'OPEN' AND " .
                    "Charges.Keycode <> 'CASH')");
        } 
        else 
        {
            // cash transactions
            $ChargesResult = SQLOpenRecordset(
                "SELECT * FROM Charges WHERE (" .
                    "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
                    "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
                    "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
                    "Charges.Keycode = 'CASH')");
        }
                                                
        // compute the monthly category charges
        $TotalCategoryCharges = 0;
        if (sql_count($ChargesResult) > 0) 
        {
            // loop through all records and add the cost
            for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
            {
                // get the total charge for this item if it is not a club aircraft
                if (!IsAircraftID($ChargesRST[$ChargesKeyCode_offset])) 
                {
                    $TotalCategoryCharges = $TotalCategoryCharges +
                                            RoundToDecimalPlaces($ChargesRST[$Total_Price_offset]);
                }
            }
        }
    }
    
    // return the total category charges
    return Abs($TotalCategoryCharges);
}

//********************************************************************
// PrintCategoryLine(
//                    HeaderPrinted As Boolean,
//                    PageIsBlank as Boolean,
//                    UserKeycode As String,
//                    TotalKeycodeCharges As Double,
//                    CategoryName As String,
//                    GLACCode as String,
//                    LineCounter As Integer,
//                    CreditCardStartDate as String,
//                    CreditCardEndDate as String,
//                    CashStartDate As String,
//                    CashEndDate As String)
//
// Purpose:  Print the detailed category records
//           for the given category.
//
// Inputs:
//   HeaderPrinted - set true if the page header hasn't been printed
//   PageIsBlank - set false if anything is printed on the page
//   UserKeycode - keycode for the user
//   TotalKeycodeCharges - total charges for the keycode
//   CategoryName - namr of the category
//   GLACCode - GLAC code for the category
//   LineCounter - counter for page breaks
//   CreditCardStartDate - the start date to print the credit card billing for
//   CreditCardEndDate - the end date to print the credit card billing for
//   CashStartDate - the starting date for the cash billing information
//   CashEndDate - the end date for the cash billing information
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function PrintCategoryLine(
                    &$HeaderPrinted,
                    &$PageIsBlank,
                    $UserKeycode,
                    $TotalKeycodeCharges,
                    $CategoryName,
                    $GLACCode,
                    &$LineCounter,
                    $CreditCardStartDate,
                    $CreditCardEndDate,
                    $CashStartDate,
                    $CashEndDate)
{                    
    global $AircraftScheduling_company;
    global $RightJustify, $LeftJustify, $CenterJustify;
        
    include "DatabaseConstants.inc";

    $LeftMargin = 6;
    $PageLength = 70;
    $HeaderLength = 2;
    $ColumnWidth1 = 8;
    $ColumnWidth2 = 21;
    $ColumnWidth3 = 11;
    $MaxLineLength = 80;
    
    $PageHeader = "**************** " . $AircraftScheduling_company . " ****************";
    
    // build the underline string
    $UnderLine = "";
    for ($i = 0; $i < ($ColumnWidth1 + $ColumnWidth2 + $ColumnWidth3) + 2; $i++)
    {
        $UnderLine = $UnderLine . "_";
    }
    
    // if the total is non-zero, print it
    $TotalKeycodeCharges = Abs($TotalKeycodeCharges);
    if ($TotalKeycodeCharges > 0) 
    {
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
            
            // skip some space below the header
            for ($i = 0; $i < $HeaderLength; $i++) 
            {
                PrintNonBreakingString(" " . "<br>");
                $LineCounter++;
            }
            
            // print the title information
            PrintNonBreakingString(Space($LeftMargin) .
                           "DETAILED LISTING OF " .
                           $CategoryName .
                           " (CONT)" . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "CASH SALES FROM " .
                           FormatField($CashStartDate, "Date") .
                           " TO " .
                           FormatField($CashEndDate, "Date") . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "CREDIT CARD SALES FROM " .
                           FormatField($CreditCardStartDate, "Date") .
                           " TO " .
                           FormatField($CreditCardEndDate, "Date") . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "GLAC CODE: " .
                           $GLACCode . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) . "<br>");
            $LineCounter++;
            
            // print the column headers
            PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("KEYCODE", $CenterJustify, $ColumnWidth1) . " " .
                    JustifyField("NAME", $CenterJustify, $ColumnWidth2) . " " .
                    JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    $UnderLine . "<br>");
            $LineCounter++;
        }
        
        // if the header has not been printed, print it
        if (!$HeaderPrinted) 
        {
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
                PrintNonBreakingString(" <br>");
                $LineCounter++;
            }
            
            // print the title information
            PrintNonBreakingString(Space($LeftMargin) .
                           "DETAILED LISTING OF " .
                           $CategoryName . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "CASH SALES FROM " .
                           FormatField($CashStartDate, "Date") .
                           " TO " .
                           FormatField($CashEndDate, "Date") . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "CREDIT CARD SALES FROM " .
                           FormatField($CreditCardStartDate, "Date") .
                           " TO " .
                           FormatField($CreditCardEndDate, "Date") . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                           "GLAC CODE: " .
                           $GLACCode . 
                           "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) . "<br>");
            $LineCounter++;
            
            // print the column headers
            PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("KEYCODE", $CenterJustify, $ColumnWidth1) . " " .
                    JustifyField("NAME", $CenterJustify, $ColumnWidth2) . " " .
                    JustifyField("AMOUNT", $CenterJustify, $ColumnWidth3) . 
                    "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    $UnderLine . "<br>");
            $LineCounter++;
           
            // only print the page header once
            $HeaderPrinted = true;
        }
        
        // if this is a total line, handle it special
        if (UCase(Trim($UserKeycode)) == "TOTAL") 
        {
            // total line
            PrintNonBreakingString(Space($LeftMargin) . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    $UnderLine . "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField("TOTAL", $LeftJustify, $ColumnWidth1) .
                    JustifyField(" ", $LeftJustify, $ColumnWidth2) .
                    JustifyField(FormatField(Str($TotalKeycodeCharges), "Currency"), $RightJustify, $ColumnWidth3) . 
                    "<br>");
            $LineCounter++;
        } 
        else 
        {
            // if we didn't find the member's keycode, mark as unknown
        	$MemberResult = SQLOpenRecordset("SELECT * FROM AircraftScheduling_person WHERE username='" .
                        UCase(Trim($UserKeycode)) . "'");
            
            // if we didn't find the member's keycode, mark as unknown
            if (sql_count($MemberResult) == 0)
            {
                // member not found, mark as unknown
                $FullName = "Unknown";
            }
            else
            {
                // member found, get the name and phone numbers
                $MembersInformationRST = sql_row($MemberResult, 0);
                $FullName = Trim($MembersInformationRST[$first_name_offset]) . " " .
                            Trim($MembersInformationRST[$middle_name_offset]) . " " .
                            Trim($MembersInformationRST[$last_name_offset]);
            }
            PrintNonBreakingString(Space($LeftMargin) .
                    JustifyField($UserKeycode, $LeftJustify, $ColumnWidth1) .
                    JustifyField($FullName, $LeftJustify, $ColumnWidth2) .
                    JustifyField(FormatField(Str($TotalKeycodeCharges), "Currency"), $RightJustify, $ColumnWidth3) . 
                    "<br>");
            $LineCounter++;
            PrintNonBreakingString(Space($LeftMargin) .
                    " " . "<br>");
            $LineCounter++;
        }
                
        // flag that something is printed on the page
        $PageIsBlank = false;
    }
}

//********************************************************************
// ComputeMemberTotalMonthlyCharge(
//                                 Keycode As String,
//                                 StartDate As String,
//                                 EndDate as String)
//                                 as Double
//
// Purpose:  Compute the total charges for the given month and
//           for the given keycode
//
// Inputs:
//   Keycode - the keycode for the member to compute the charges
//   StartDate - the start date to print the billing for
//   EndDate - the end date to print the billing for
//
// Outputs:
//   none
//
// Returns:
//   The total charges for the month
//*********************************************************************
function ComputeMemberTotalMonthlyCharge($KeyCode, $StartDate, $EndDate)
{    
    global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        
    include "DatabaseConstants.inc";

    // create a query for the flight charges for the date and keycode
    $FlightResult = SQLOpenRecordset(
        "SELECT * FROM Flight WHERE (" .
            "Flight.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
            "Flight.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
            "Flight.KeyCode = '" . $KeyCode . "')");
            
    // create a query for the misc charges for the date and keycode
    $ChargesResult = SQLOpenRecordset(
        "SELECT * FROM Charges WHERE (" .
            "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
            "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
            "Charges.KeyCode = '" . $KeyCode . "')");
                                            
    // compute the monthly flight charges
    $TotalFlightCharges = 0;
    if (sql_count($FlightResult) > 0)
    {
        //loop through all records and add up the flight charges
        for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++)
        {
            // if this is an instructor's flight, ignore it
            if ($FlightRST[$Instruction_Type_offset] != $InstructorInstruction)
            {
                // get the aircraft charge
                $TotalFlightCharges = $TotalFlightCharges + $FlightRST[$Aircraft_Cost_offset];
                
                // get the flight instructor charges
                $TotalFlightCharges = $TotalFlightCharges + $FlightRST[$Instructor_Charge_offset];
                
                // get the fuel charges
                $TotalFlightCharges = $TotalFlightCharges + $FlightRST[$Local_Fuel_Cost_offset];
                
                // get the oil charges
                $TotalFlightCharges = $TotalFlightCharges + $FlightRST[$Oil_Cost_offset];
                
                // subtract the cross country fuel charges
                $TotalFlightCharges = $TotalFlightCharges + $FlightRST[$Cross_Country_Fuel_Credit_offset];
            }
        }
    }
                                            
    // compute the monthly misc charges
    $TotalMiscCharges = 0;
    if (sql_count($ChargesResult) > 0)
    {
        //loop through all records and add up the misc charges
        for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
        {
            // get the total charge for this item
            $TotalMiscCharges = $TotalMiscCharges + $ChargesRST[$Total_Price_offset];
        }
    }
    
    // return the total charges
    return ($TotalFlightCharges + $TotalMiscCharges);
}

//********************************************************************
// ComputeMemberTotalMonthlyMiscCharge(
//                                 Keycode As String,
//                                 StartDate As String,
//                                 EndDate As String,
//                                 CategoryName as String)
//                                 as Double
//
// Purpose:  Compute the total miscellaneous charges for the given month,
//           the given category and for the given keycode
//
// Inputs:
//   Keycode - the keycode for the member to compute the charges
//   StartDate - the start date to print the billing for
//   EndDate - the end date to print the billing for
//   CategoryName - the category for the given charges
//
// Outputs:
//   none
//
// Returns:
//   The total charges for the month
//*********************************************************************
function ComputeMemberTotalMonthlyMiscCharge(
                                            $KeyCode,
                                            $StartDate,
                                            $EndDate,
                                            $CategoryName)
{           
    include "DatabaseConstants.inc";
                
    // create a query for any additional special charges for the dates and category
    $ChargesResult = SQLOpenRecordset(
        "SELECT * FROM Charges WHERE (" .
            "Charges.Date >= '" . FormatField($StartDate, "DatabaseDate") . "' AND " .
            "Charges.Date <= '" . FormatField($EndDate, "DatabaseDate") . "' AND " .
            "Charges.Category = '" . UCase(Trim($CategoryName)) . "' AND " .
            "Charges.Keycode = '" . UCase(Trim($KeyCode)) . "') ");
                                            
    // compute the monthly category charges
    $TotalMiscCharges = 0;
    if (sql_count($ChargesResult) > 0)
    {
        //loop through all records and add the cost
        for($ChargesCnt=0; $ChargesRST = sql_row($ChargesResult, $ChargesCnt); $ChargesCnt++)
        {
            // get the total charge for this item
            // don't add aircraft maintance time to the DAR report
            if (!IsAircraftID($ChargesRST[$ChargesKeyCode_offset]))
            {
                $TotalMiscCharges = $TotalMiscCharges + $ChargesRST[$Total_Price_offset];
            }
        }
    }

    // return the total charges
    return $TotalMiscCharges;
}

//********************************************************************
// PrintCurrencyInformation(
//                    LeftMargin As Integer,
//                    CurrentUserKeycode as String,
//                    LinesPrinted As Integer)
//
// Purpose:  Print currency information for a member
//
// Inputs:
//   LeftMargin - space to skip on right of paper
//   CurrentUserKeycode - the user to print the currency for
//
// Outputs:
//   LinesPrinted - the number of lines used to print the currency info
//
// Returns:
//   none
//*********************************************************************
function PrintCurrencyInformation(
                    $LeftMargin,
                    $CurrentUserKeycode,
                    &$LinesPrinted)
{                      
    global $RightJustify, $LeftJustify, $CenterJustify;
    global $MaxCurrencyFieldRecords;

    $CurrencyRuleString = array();
    $CurrencyStatusString = array();
    
    // load the information from the database and compute the curreny
    GetCurrencyValues(
                        LookupCurrencyFieldname("Rating"),
                        $CurrentUserKeycode,
                        $CurrencyRuleString,
                        $CurrencyStatusString,
                        $PilotIdentificationString,
                        $FlightStatus,
                        $FlightStatusReason,
                        "C-152",
                        "ALL");
    
    // print the pilot currency information
    $LinesPrinted = 0;
    for ($i = 0; $i < $MaxCurrencyFieldRecords; $i++)
    {
        if (array_key_exists($i, $CurrencyRuleString))
        {
            $CurrencyDateString = "";
            $CurrencyDateString = DateValue($CurrencyStatusString[$i]);
            if (Len($CurrencyDateString) > 0 && $CurrencyStatusString[$i] != "EXPIRED" && 
                    $CurrencyStatusString[$i] != "FALSE" && $CurrencyStatusString[$i] != "TRUE")
            {
                // date value, format the field text and value
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField($CurrencyRuleString[$i], $LeftJustify, 45) .
                            JustifyField(FormatField($CurrencyStatusString[$i], "Date"), $LeftJustify, 20) .
                            "<br>");
            }
            else
            {
                // non-date field, don't format the field and value
                PrintNonBreakingString(Space($LeftMargin) .
                            JustifyField($CurrencyRuleString[$i], $LeftJustify, 45) .
                            JustifyField($CurrencyStatusString[$i], $LeftJustify, 20) .
                            "<br>");
            }
            $LinesPrinted++;
        }
    }
}
 
?>
     
<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// CompleteForm()
//
// Purpose: Complete the processing of the form.
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
function CompleteForm()
{	
	// print the sheets
    window.print();

    // return to the last screen after a short delay to give the print
	// dialog time to load
	setTimeout(function(){ window.location.href = ReturnURL; }, 2000);
}

//-->
</script>
