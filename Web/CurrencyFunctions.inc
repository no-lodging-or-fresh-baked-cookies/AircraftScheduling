<?php
//-----------------------------------------------------------------------------
// 
// CurrencyProcessing
// 
// PURPOSE: Handles the currency processing for the Flying
//          Club Database program.
// 
// PARAMETERS:
//      none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      This file based on Meeting Room Booking System http://mrbs.sourceforge.net
//      It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
//      12/19/2001 and released under the terms of the GNU Public License
//
// -----------------------------------------------------------------------------

require_once("class.stack.php");
require_once("DateFunctions.inc");
require_once("StringFunctions.inc");
require_once("DatabaseFunctions.inc");

// declare the currency field record array
$CurrencyFieldNames = array();
$CurrencyFieldValues = array();
$MaxCurrencyFieldRecords = 100; // maximum number of records for the currency records

// constants
$vbCancel = 0;                  // visual basic cancel constant
$vbOK = 1;                      // visual basic OK constant
$vbTextCompare = 0;             // text compare constant

// curreny constants
$DoesntApply = 0;               // rule doesn't apply in this case
$ClearedToFly = 1;              // rule passes
$ClearedToFlyDayOnly = 2;       // night rule fails, allow VFR day flight
$ClearedToFlyNoInstruments = 3; // rule fails, allow VFR but not instruments
$NotClearedToFly = 4;           // rule failed, no overrides allowed
$NotClearedToFlyOverride = 5;   // rule failed, overrides allowed
$InformationOnly = 6;           // rule failed, overrides allowed

// pilot types
$NoCurrencyPilot = "No Currency Required"; // a pilot that does not have currency requirements"
$StudentPilot = "Student";           // student pilot type
$PrivatePilotUnder200 = "Private Under 200"; // private pilot with less than 200 hours
$PrivatePilotOver200 = "Private Over 200"; // private pilot with more than 200 hours
$InstrumentPilot = "Instrument";     // instrument rated private pilot
$CFIPilot = "CFI";                   // flight instuctor
$InstructorInstruction = "Instructor"; // instructor description string

// member status
$MemberStatusActive = "Active";      // member is active
$MemberStatusInActive = "Inactive";  // member is inactive
$MemberStatusAircraft = "Aircraft";  // member is an airplane (member keycode is
                                     // used for maintenance flights)                                               
$MemberStatusResigned = "Resigned" ; // member has resigned

// medical status
$FirstClassMedical = "1st Class Medical"; // medical class description string
$SecondClassMedical = "2nd Class Medical"; // medical class description string
$ThirdClassMedical = "3rd Class Medical"; // medical class description string
$SpecialClassMedical = "Special Medical"; // medical class description string

// currency rule constants
$CurrencyRequiredToFly = "REQUIRED TO FLY";  // currency rule for required to fly
$CurrencyRequiredToSolo = "REQUIRED TO SOLO";  // currency rule for required to solo
$CurrencyRequiredToSoloRental = "REQUIRED TO SOLO RENTAL";  // currency rule for required to solo rental aircraft
$CurrencyRequiredToFlyInstrument = "REQUIRED TO FLY INSTRUMENT";  // currency rule for required to fly instrument
$CurrencyInformation = "INFORMATION";  // currency rule for information

$WithinField = "";                // the number of days, months or years set by the Within rule
$WithinDate = "";                 // the date set by the Within currency rule
$NullDate = "01-Jan-1900";        // empty date
$FlightTest = "Flight_Test";            // defines a flight test for an aircraft
$WrittenTest = "Written_Test";          // defines a written test for an aircraft
$InitialCheckout = "Initial_Checkout";   // defines an initial checkout for an aircraft
$FlightTimeExpirationTime = "1Y";    // flight tests expire after this time

//********************************************************************
// DisplayColumns(
//              NumberElements, 
//              NumberColumns, 
//              CurrencyField, 
//              CurrencyResults,
//              AllowModifications,
//              FieldFormat)
//
// Purpose: Display the currency information in the number of columns
//          requested.
//
// Inputs:
//   NumberElements - number of elements to print 
//   NumberColumns - number of columns to print
//   CurrencyField - the currency fields
//   CurrencyResults - the result of the currency rule
//   AllowModifications - set to true to allow modifying fields
//   FieldFormat - type of field to display (date, integer, float, etc)
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function DisplayColumns(
                        $NumberElements, 
                        $NumberColumns, 
                        $CurrencyField, 
                        $CurrencyResults,
                        $AllowModifications,
                        $FieldFormat)
{ 
    // compute the number of rows
    $NumberRows = (int) ($NumberElements / $NumberColumns);
    if ($NumberRows * 2 < $NumberElements) $NumberRows++;
    
    // print the elements of the table
    for ($Row = 0; $Row < $NumberRows; $Row++)
    {
        // print the row
   		echo "<tr>";
   		
        // print the columns
        for ($Column=0; $Column < $NumberColumns; $Column++)
        {
            // compute the offset
            $ColumnOffset = $NumberRows * $Column;
            
            // if we have a valid subscript
            if ($Row + $ColumnOffset < $NumberElements)
            {
                // if we have a valid rule, print the rule
                If (Len($CurrencyField[$Row + $ColumnOffset]) > 0)
                {                    
                    // checked for special processing cases within the date fields
                    $CurrencyDateStringInValid = ($CurrencyResults[$Row + $ColumnOffset] == "EXPIRED") ||
                                                   ($CurrencyResults[$Row + $ColumnOffset] == "TRUE") ||
                                                   ($CurrencyResults[$Row + $ColumnOffset] == "FALSE");
                                                   
                    // if modifications are allowed
                    if ($AllowModifications)
                    {
                        // modifications are allowed
                        echo "<td class=CL>" . $CurrencyField[$Row + $ColumnOffset] . "</td>";
                        if ($FieldFormat[$Row + $ColumnOffset] == "Date" && !$CurrencyDateStringInValid)
                        {
                            // date value, format the field text and value
                            echo "<td class=CR>" . 
                                    "<input " .
                                        "type=text " .
                                        "NAME='" . $CurrencyField[$Row + $ColumnOffset] . "' " . 
                                        "ID='" . $CurrencyField[$Row + $ColumnOffset] . "' " .
                                        "align=left " . 
                                        "SIZE=10 " .
                                        "onblur='FormatDate(\"" . $CurrencyField[$Row + $ColumnOffset] . "\")'" .
                                        "VALUE='" . $CurrencyResults[$Row + $ColumnOffset] . "' " . 
                                        ">" . 
                                    "</td>";
                        }
                        else
                        {
                            // non-date field, don't format the field and value
                            echo "<td class=CR>" . 
                                    "<input " .
                                        "type=text " .
                                        "NAME='" . $CurrencyField[$Row + $ColumnOffset] . "' " . 
                                        "ID='" . $CurrencyField[$Row + $ColumnOffset] . "' " .
                                        "align=left " . 
                                        "SIZE=10 " . 
                                        "VALUE='" . $CurrencyResults[$Row + $ColumnOffset] . "' " . 
                                        ">" . 
                                    "</td>";
                        }
                    }
                    else
                    {
                        // modifications are not allowed
                        if ($FieldFormat[$Row + $ColumnOffset] == "Date" && !$CurrencyDateStringInValid)
                        {
                            // date value, format the field text and value
                            echo "<td class=CL>" . $CurrencyField[$Row + $ColumnOffset] . "</td>";
                            echo "<td class=CR>" . FormatDate($CurrencyResults[$Row + $ColumnOffset]) . "</td>";
                        }
                        else
                        {
                            // non-date field, don't format the field and value
                            echo "<td class=CL>" . $CurrencyField[$Row + $ColumnOffset] . "</td>";
                            echo "<td class=CR>" . $CurrencyResults[$Row + $ColumnOffset] . "</td>";
                        }
                    }
                }
            }
            
            // space between columns (except for the last)
            if ($Column < $NumberColumns - 1)
            {
                for ($Spaces=0; $Spaces < 2; $Spaces++)
                {
                    echo "<td class=CL> </td>";
                }
            }
        }
        
        // complete the row
        echo "</tr>";
    }
}

//********************************************************************
// RemoveCurrencyFieldQuotes(CurrencyFieldName As String) As String
//
// Purpose:  Remove any leading and trailing quotes from the currency field.
//
// Inputs:
//   CurrencyFieldName - currency name to remove quotes from
//
// Outputs:
//   none
//
// Returns:
//   CurrencyFieldName with trailing and leading quotes removed
//*********************************************************************
function RemoveCurrencyFieldQuotes($CurrencyFieldName)
{
    global $debug_flag;

    return str_replace("\"", "", $CurrencyFieldName);
}

//********************************************************************
// LookupUserName($PilotName)
//
// Purpose:      Given the pilot's name, lookup the username
//
// Inputs:
//   PilotName - member we are interested in
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function LookupUserName($PilotName)
{
    global $debug_flag;
    global $DatabaseNameFormat;
    
    // lookup the username
	$UserName = sql_query1(
							"SELECT username " .
							"FROM AircraftScheduling_person " .
							"WHERE $DatabaseNameFormat = '$PilotName'"
							);

    return $UserName;								
}

//********************************************************************
// IsAircraftID(
//              AircraftID As String)
//              As Boolean
//
// Purpose:  Determine if the aircraft ID is a valid aircraft
//
// Inputs:
//   AircraftID - the aircraft tail number to test
//
// Outputs:
//   none
//
// Returns:
//   True if the input aircraft ID is valid
//
// Notes:
//
//*********************************************************************
function IsAircraftID($AircraftID)
{    
    // find the selected aircraft
	$AircraftStatus = sql_query1(
						"SELECT status " .
						"FROM AircraftScheduling_aircraft " .
						"WHERE n_number='" . Trim($AircraftID) . "'");
    
    // see if we found a valid aircraft ID
    if ($AircraftStatus == -1)
        // invalid aircraft, return false
        return false;
    else
        // valid aircraft, return true
        return true;
}

//********************************************************************
// IsAircraftType(
//              AircraftType As String)
//              As Boolean
//
// Purpose:  Determine if the given aircraft type is a valid aircraft
//           type in the database.
//
// Inputs:
//   AircraftType - the aircraft type to test
//
// Outputs:
//   none
//
// Returns:
//   True if the input aircraft type is valid
//
// Notes:
//
//*********************************************************************
function IsAircraftType($AircraftType)
{    
    global $debug_flag;

    // remove any quotes from the string
    $tmpAircraftType = RemoveCurrencyFieldQuotes($AircraftType);
    
    // lookup the type
	$Type = sql_query1(
						"SELECT model_id " .
						"FROM AircraftScheduling_model " .
						"WHERE model = '$tmpAircraftType'");
    
    // return true if it the type was found
    if ($Type == -1)
        return false;
    else
        return true;
}

//********************************************************************
// IsRentalAircraftType(
//              AircraftType As String)
//              As Boolean
//
// Purpose:  Determine if the given aircraft type is a valid aircraft
//           type for an aircraft for rent in the database.
//
// Inputs:
//   AircraftType - the aircraft type to test
//
// Outputs:
//   none
//
// Returns:
//   True if the input aircraft type is a rental aircraft type
//
// Notes:
//
//*********************************************************************
function IsRentalAircraftType($AircraftType)
{        
    global $debug_flag;

    // get the model ID from the database
    $ModelID = LookupModelID(RemoveCurrencyFieldQuotes($AircraftType));
    
    // is it a valid model ID?
    if ($ModelID != -1)
    {    // create a query to see if this type of aircraft is in the database
    	$SQLResult = sql_query("SELECT hourly_cost FROM AircraftScheduling_aircraft WHERE model_id = " . 
                        $ModelID);
    
        // see if the aircraft type is found in the database
    	if ($SQLResult != -1)
    	{
            // valid aircraft type, see if any of the aircraft are rental aircraft
    		for ($i = 0; ($row = sql_row($SQLResult, $i)); $i++) 
    		{
                If ($row[0] > 0)
                {
                    // rental aircraft found, return true
                    return true;
                }
            }
            
            // no rental aircraft found, return false
            $IsRentalAircraftTypeResult = false;
        }
        else
        {
       	    // sql error, tell the user
    	    fatal_error(0, sql_error());
        }
    }
    Else
    {
        // invalid aircraft type, return false
        $IsRentalAircraftTypeResult = false;
    }

    //return the results
    return $IsRentalAircraftTypeResult;
}

//********************************************************************
// IsRentalAircraft(
//              AircraftID As String)
//              As Boolean
//
// Purpose:  Determine if the given aircraft ID is a valid aircraft
//           for rent in the database.
//
// Inputs:
//   AircraftID - the aircraft tailnumber to test
//
// Outputs:
//   none
//
// Returns:
//   True if the input aircraft ID is a rental aircraft
//
// Notes:
//
//*********************************************************************
function IsRentalAircraft($AircraftID)
{    
    global $debug_flag;

    // create a query to see if this type of aircraft is in the database
	$SQLResult = sql_query("SELECT hourly_cost FROM AircraftScheduling_aircraft " .
                    "WHERE n_number='" . Trim($AircraftID) . "'");

    // see if the aircraft type is found in the database
	if ($SQLResult)
	{
        // valid aircraft type, see if any of the aircraft are rental aircraft
		$row = sql_row($SQLResult, 0);
        If ($row[0] > 0)
            // rental aircraft found, return true
            $IsRentalAircraftResult = True;
        Else
            // not a rental aircraft, return false
            $IsRentalAircraftResult = False;
    }
    Else
    {
	    // sql error, tell the user
	    fatal_error(0, sql_error());
    }

    //return the results
    return $IsRentalAircraftResult;
}

//********************************************************************
// GetSafetyMeetingExpiration(FieldName As String) As Variant
//
// Purpose:  Return the value for the given safety meeting field.
//
// Inputs:
//   FieldName - field name of the value to retreive
//
// Outputs:
//   none
//
// Returns:
//   The value of the preference field
//*********************************************************************
function GetSafetyMeetingExpiration($FieldName)
{
    global $debug_flag;

    // get the requested field from the database
	$SafetyMeetingField = sql_query1("SELECT $FieldName FROM Safety_Meeting");
	
	// return it to the caller
	if (IsValidDate($SafetyMeetingField))
	    $ReturnValue = DateValue($SafetyMeetingField);
	else
	    $ReturnValue = $SafetyMeetingField;
	return $ReturnValue;
}

//********************************************************************
// IsValidCurrencyFieldName(CurrencyFieldName As String) As Integer
//
// Purpose: Check CurrencyFieldName to see if it is a valid currency
//          field name (no operators, begins with an alpha).
//
// Inputs:
//   CurrencyFieldName - the name to check for validity
//
// Outputs:
//   none
//
// Returns:
// Returns:
//   vbCancel - if CurrencyFieldName is invalid
//   vbOK - if CurrencyFieldName is valid
//*********************************************************************
function IsValidCurrencyFieldName($CurrencyFieldName)
{
    global $vbCancel, $vbOK, $vbTextCompare;
    global $debug_flag;
    
    // if the string starts and ends with quotes, it must be a valid string
    If (Left(Trim($CurrencyFieldName), 1) == Chr(34) &&
        Right(Trim($CurrencyFieldName), 1) == Chr(34))
    {
        // quoted string, return a good status
        return $vbOK;
    }
    
    // if the string is null, return error
    If (Len(Trim($CurrencyFieldName)) == 0)
    {
        // null string, return an error
        return $vbCancel;
    }
    
    // see if the name starts with an alpha
    If (!((Mid($CurrencyFieldName, 1, 1) >= "A" && Mid($CurrencyFieldName, 1, 1) <= "Z") ||
           (Mid($CurrencyFieldName, 1, 1) >= "a" && Mid($CurrencyFieldName, 1, 1) <= "z")))
    {
        // name doesn't begin with an alpha, return an error
        return $vbCancel;
    }
     
    // don't allow _ within the field name
    If (InStr(1, $CurrencyFieldName, "_", $vbTextCompare) != 0)
    {
        // field name contains _, return an error
        return $vbCancel;
    }
     
    // don't allow blanks within the field name
    If (InStr(1, $CurrencyFieldName, " ", $vbTextCompare) != 0)
    {
        // field name contains a blank, return an error
        return $vbCancel;
    }
    
    // check for an operator within the field name
    $InputStringPointer = 1;
    While ($InputStringPointer <= Len($CurrencyFieldName))
    {
        // get a character from the input string
        $InputCharacter = Mid($CurrencyFieldName, $InputStringPointer, 1);
        
        // is this character an operator
        switch (UCase($InputCharacter))
        {
        Case "(":
            // operator found, return an error
            return $vbCancel;
        Case ")":
            // operator found, return an error
            return $vbCancel;
        Case "+":                // add the previous two fields
            // operator found, return an error
            return $vbCancel;
        Case "-":                // subtract the previous two fields or
                                // if operand1 is true, evaluate operand2
            // operator found, return an error
            return $vbCancel;
        Case "*":                // multiply the previous two fields
            // operator found, return an error
            return $vbCancel;
        Case "/":                // divide by the previous two fields
            // operator found, return an error
            return $vbCancel;
        Case "&":                // AND the previous two fields
            // operator found, return an error
            return $vbCancel;
        Case "|":                // OR the previous two fields
            // operator found, return an error
            return $vbCancel;
        Case ">":                // compare greater than to the previous two fields or
                                // compare greater than or equal to the previous two fields
            // operator found, return an error
            return $vbCancel;
        Case "<":                // compare less than to the previous two fields or
                                // compare less than or equal to the previous two fields or
                                // not equal to the previous two fields
            // operator found, return an error
            return $vbCancel;
        Case "W":                // check for the "WITHIN Operator
            If (UCase(Mid($CurrencyFieldName, $InputStringPointer + 1, 1)) == "I" &&
                UCase(Mid($CurrencyFieldName, $InputStringPointer + 2, 1)) == "T" &&
                UCase(Mid($CurrencyFieldName, $InputStringPointer + 3, 1)) == "H" &&
                UCase(Mid($CurrencyFieldName, $InputStringPointer + 4, 1)) == "I" &&
                UCase(Mid($CurrencyFieldName, $InputStringPointer + 5, 1)) == "N")
            {
                // WITHIN operator
                // operator found, return an error
                return $vbCancel;
            }
        Case "=":                // compare equal to the previous two fields
        default:
            // not an operator, continue searching
        }
        
        // increment to the next character
        $InputStringPointer = $InputStringPointer + 1;
    }
    
    // valid field name, return no errors
    return $vbOK;
}

//********************************************************************
// Priority(Operator As String) As Integer
//
// Purpose: Return an integer for the priority of the operator
//
// Inputs:
//   Operator - the operator for the function
//
// Outputs:
//   none
//
// Returns:
//   An integer priority for the operator
//*********************************************************************
function Priority($Operator)
{
    global $debug_flag;

    // if the operator is non-null, assign the priority
    If (Len(Trim($Operator)) > 0)
    {
        // non-null operator, assign the priority
        switch ($Operator)
        {
        Case "(":
            $OperatorPriority = 0;
            break;
        Case ")":
            $OperatorPriority = 0;
            break;
        Case "WITHIN":           // the following rule applies within the past given time
            $OperatorPriority = 5;
            break;
        Case "+":                // add the previous two fields
            $OperatorPriority = 3;
            break;
        Case "-":                // subtract the previous two fields
            $OperatorPriority = 3;
            break;
        Case "*":                // multiply the previous two fields
            $OperatorPriority = 4;
            break;
        Case "/":                // divide by the previous two fields
            $OperatorPriority = 4;
            break;
        Case "&":                // AND the previous two fields
            $OperatorPriority = 1;
            break;
        Case "|":                // OR the previous two fields
            $OperatorPriority = 1;
            break;
        Case ">":                // compare greater than to the previous two fields
            $OperatorPriority = 2;
            break;
        Case "<":                // compare less than to the previous two fields
            $OperatorPriority = 2;
            break;
        Case ">=":               // compare greater than or equal to the previous two fields
            $OperatorPriority = 2;
            break;
        Case "<=":               // compare less than or equal to the previous two fields
            $OperatorPriority = 2;
            break;
        Case "=":                // compare equal to the previous two fields
            $OperatorPriority = 2;
            break;
        Case "<>":               // if operand1 is not true, evaluate operand2
            $OperatorPriority = 2;
            break;
        Case "->":               // if operand1 is true, evaluate operand2
            $OperatorPriority = 1;
            break;
        default:
            // error operator has no priority
            $OperatorPriority = 0;
        }
    }
    Else
    {
        // operator is null
        $OperatorPriority = 0;
    }
    
    // return the results
    return $OperatorPriority;
}

//********************************************************************
// PadOperators(OperatorString As String) As String
//
// Purpose: Add spaces around all operators (+, -, *, /, etc.)
//          in the string.
//
// Inputs:
//   OperatorString - a string to add padding to
//
// Outputs:
//   none
//
// Returns:
//   The string with all operators seperated by spaces
//*********************************************************************
function PadOperators($OperatorString)
{
    global $debug_flag;

    // if the input string is null, pass back a null
    If (Len(Trim($OperatorString)) == 0)
    {
        return "";
    }
    
    // initialize the output
    $OutputString = "";
    
    // search for any operators in the input string
    $InputStringPointer = 1;
    while ($InputStringPointer <= Len($OperatorString))
    {
        // get a character from the input string
        $InputCharacter = Mid($OperatorString, $InputStringPointer, 1);
        
        // is this character an operator
        switch(UCase($InputCharacter))
        {
        Case Chr(34):            // " character
            // found a quoted string, skip all characters until next quote
            While ($InputStringPointer <= Len($OperatorString))
            {
                // get a character from the input string
                $InputStringPointer = $InputStringPointer + 1;
                $InputCharacter = Mid($OperatorString, $InputStringPointer, 1);
                
                // exit the loop when we find the next quote
                If ($InputCharacter == Chr(34))
                    break;
                
                // add the character to the output string
                $OutputString = $OutputString . $InputCharacter;
            }
            break;
        Case "(":
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        Case ")":
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        Case "+":               // add the previous two fields
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        Case "-":               // subtract the previous two fields or
                                // if operand1 is true, evaluate operand2
            // may be a "->"
            If (Mid($OperatorString, $InputStringPointer + 1, 1) == ">")
            {
                // "->" operator
                $OutputString = $OutputString . " " . $InputCharacter . ">" . " ";
                $InputStringPointer = $InputStringPointer + 1;
            }
            Else
            {
                // - operator
                $OutputString = $OutputString . " " . $InputCharacter . " ";
            }
            break;
        Case "*":               // multiply the previous two fields
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        Case "/":               // divide by the previous two fields
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        Case "&":               // AND the previous two fields
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        Case "|":               // OR the previous two fields
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        Case ">":               // compare greater than to the previous two fields or
                                // compare greater than or equal to the previous two fields
            // may be a ">=" or ">"
            If (Mid($OperatorString, $InputStringPointer + 1, 1) == "=")
            {
                // ">=" operator
                $OutputString = $OutputString . " " . $InputCharacter . "=" . " ";
                $InputStringPointer = $InputStringPointer + 1;
            }
            Else
            {
                // ">" operator
                $OutputString = $OutputString . " " . $InputCharacter . " ";
            }
            break;
        Case "<":               // compare less than to the previous two fields or
                                // compare less than or equal to the previous two fields or
                                // not equal to the previous two fields
            // may be a "<=" or "<" or "<>"
            If (Mid($OperatorString, $InputStringPointer + 1, 1) == "=")
            {
                // "<=" operator
                $OutputString = $OutputString . " " . $InputCharacter . "=" . " ";
                $InputStringPointer = $InputStringPointer + 1;
            }
            ElseIf (Mid($OperatorString, $InputStringPointer + 1, 1) == ">")
            {
                // "<>" operator
                $OutputString = $OutputString . " " . $InputCharacter . ">" . " ";
                $InputStringPointer = $InputStringPointer + 1;
            }
            Else
            {
                // "<" operator
                $OutputString = $OutputString . " " . $InputCharacter . " ";
            }
            break;
        Case "W":               // check for the "WITHIN Operator
            If (UCase(Mid($OperatorString, $InputStringPointer + 1, 1)) == "I" && 
                UCase(Mid($OperatorString, $InputStringPointer + 2, 1)) == "T" &&
                UCase(Mid($OperatorString, $InputStringPointer + 3, 1)) == "H" &&
                UCase(Mid($OperatorString, $InputStringPointer + 4, 1)) == "I" &&
                UCase(Mid($OperatorString, $InputStringPointer + 5, 1)) == "N")
            {
                // WITHIN operator, add to the string with pads
                $OutputString = $OutputString . " " . "WITHIN" . " ";
                $InputStringPointer = $InputStringPointer + 5;
            }
            Else
            {
                // not the WITHIN operator, add character to the string
                $OutputString = $OutputString . $InputCharacter;
            }
            break;
        Case "=":               // compare equal to the previous two fields
            $OutputString = $OutputString . " " . $InputCharacter . " ";
            break;
        default:
            // not an operator, copy to the output string
            $OutputString = $OutputString . $InputCharacter;
            break;
        }
        
        // increment to the next character
        $InputStringPointer = $InputStringPointer + 1;
    }
    
    // remove any excess blanks (two together) in the output string
    $InputStringPointer = 2;
    $OutputString = Trim($OutputString);
    $PadOperatorsOutput = Mid($OutputString, 1, 1);
    while ($InputStringPointer <= Len($OutputString))
    {
        // get a character from the string
        $InputCharacter = Mid($OutputString, $InputStringPointer, 1);
        
        // if this character is a blank and the previous character is a blank,
        // ignore this blank
        If (!($InputCharacter == " " &&
            Mid($OutputString, $InputStringPointer - 1, 1) == " "))
        {
            $PadOperatorsOutput = $PadOperatorsOutput . $InputCharacter;
        }
        
        // point to the next character
        $InputStringPointer = $InputStringPointer + 1;
    }
    
    // return the results
    return $PadOperatorsOutput;
}

//********************************************************************
// InfixToPostfix(Infix As String) As String
//
// Purpose: Convert the infix string to a postfix string
//
// Inputs:
//   Infix - a string in infix notation to be converted
//
// Outputs:
//   none
//
// Returns:
//   A postfix string conversion of the infix string
//*********************************************************************
function InfixToPostfix($Infix)
{
    global $debug_flag;

    // initialize the stack
    $OperatorStack = new Stack();
    $OperatorStack->Initialize();
    
    // pad all operators with spaces so that the GetNextToken function
    // will work correctly
    $ParseString = PadOperators($Infix);
    
    // Initialize output
    $Postfix = "";

    // While not end of expression
    while (Len($ParseString) > 0)
    {
        // get a token from the string
        $Token = GetNextToken($ParseString, " ");
        
        // If token is an operand append it to the postfix
        If ((UCase(Mid($Token, 1, 1)) >= "A" &&
            UCase(Mid($Token, 1, 1)) <= "Z") ||
            (UCase(Mid($Token, 1, 1)) >= "0" &&
            UCase(Mid($Token, 1, 1)) <= "9"))
        {
            $Postfix = $Postfix . $Token . " ";
        }
        // If token is (, push character
        ElseIf ($Token == "(")
        {
            If (!$OperatorStack->Push($Token))
            {
                // error unable to push operator
            }
        }   
        // If token is )
        ElseIf ($Token == ")")
        {
            // Pop stack
            $C1 = $OperatorStack->Pop();
            
            // While popped value is not (
            while ($C1 <> "(")
            {
                // Append popped value to postfix
                $Postfix = $Postfix . $C1 . " ";
                
                // if the stack is empty, we have mismatched parenthesis
                If ($OperatorStack->StackIsEmpty())
                {
                    break;
                }
                    
                // Pop stack
                $C1 = $OperatorStack->Pop();
            }
        }
        // If ch is an operator
        // While top of stack != (, and priority of top of stack >= priority of ch,
        // and stack not empty
        Else
        {
            while ($OperatorStack->TopOfStack($C1) And $C1 <> "(" &&
                Priority($C1) >= Priority($Token))
            {
                // Pop stack
                $C1 = $OperatorStack->Pop();
                
                // Append popped value to postfix
                $Postfix = $Postfix . $C1 . " ";
            }
            
            // Push ch
            If (!$OperatorStack->Push($Token))
            {
                // error unable to push operator
            }
        }
    }

    // While stack not empty
    while (!$OperatorStack->StackIsEmpty())
    {
        // Pop stack
        $C1 = $OperatorStack->Pop();

        // Append popped value to postfix
        $Postfix = $Postfix . $C1 . " ";
    }

    // return the postfix string to the caller
    return $Postfix;
}

//********************************************************************
// CheckCurrencyRule(Requirement As String,
//                   CurrencyRule as String,
//                   RulesResult as Boolean,
//                   CurrentlySelectedAircraftType as string,
//                   CurrentlySelectedAircraftID as string)
//                   As CurrencyCheckType
//
// Purpose:      Check the currency rule result to see if the pilot can
//               fly
//
// Inputs:
//   Requirement - the type of rule (required to fly, required to solo, etc)
//   CurrencyRule - the rule to check
//   RulesResult - the result of the rules evaluation
//   CurrentlySelectedAircraftType - the current aircraft type (set to ALL to check
//                                   all aircraft types)
//   CurrentlySelectedAircraftID - the tail number of the aircraft (set to ALL to check
//                                   all aircraft IDs)
//
// Outputs:
//   none
//
// Returns:
//   DoesntApply - the rule doesn't apply
//   NotCLearedToFly - the rule failed, flight not allowed
//   ClearedToFly - the rule passed
//   ClearedToFlyDayOnly - the rule passed for daytime only
//   ClearedToFlyNoInstruments - the rule passed (VFR only)
//   NotClearedToFlyOverride - the rule failed, flight instructor may
//                             override
//   InformationOnly - rule is for information only
//
// Notes:
//
//*********************************************************************
function CheckCurrencyRule(
                   $Requirement,
                   $CurrencyRule,
                   $RulesResult,
                   $CurrentlySelectedAircraftType,
                   $CurrentlySelectedAircraftID)
{
    global $DoesntApply, $ClearedToFly, $ClearedToFlyDayOnly;
    global $ClearedToFlyNoInstruments, $NotClearedToFly, $NotClearedToFlyOverride;
    global $InformationOnly;
    global $CurrencyRequiredToFly, $CurrencyRequiredToSolo, $CurrencyRequiredToSoloRental;
    global $CurrencyRequiredToFlyInstrument, $CurrencyInformation;
    global $debug_flag;
    global $vbCancel, $vbOK, $vbTextCompare;
                                   
    // If this rule is a aircraft type rating rule see if it applies to this
    // aircraft. We assume that the first part of the CurrencyRule (up to the "_") is
    // the aircraft type
    $tmpCurrencyRule = $CurrencyRule;
    $AircraftType = GetNextToken($tmpCurrencyRule, "_");
    
    // see if the aircraft type is found in the database
    If (!IsAircraftType($AircraftType))
    {
        // aircraft was not found, do the normal rules processing
        switch (UCase($Requirement))
        {
        Case $CurrencyRequiredToFly:
            // rule is required to fly, if the expiration date is passed
            // ground the pilot
            If ($RulesResult)
                $CheckCurrencyRuleResult = $ClearedToFly;
            Else
                $CheckCurrencyRuleResult = $NotClearedToFly;
            break;
        Case $CurrencyRequiredToSolo:
            // rule is required to solo, if the expiration date is passed
            // allow an instructor to override
            If ($RulesResult)
            {
                $CheckCurrencyRuleResult = $ClearedToFly;
            }
            Else
            {
                // if this a night time flight rule, just fail the pilot for night time
                If (InStr(1, $CurrencyRule, "Night_Flight_Hours", $vbTextCompare) != 0)
                    // night time, cleared for day only
                    $CheckCurrencyRuleResult = $ClearedToFlyDayOnly;
                Else
                    $CheckCurrencyRuleResult = $NotClearedToFlyOverride;
            }
            break;
        Case $CurrencyRequiredToSoloRental:
            // rule is required to solo rental aircraft, if the expiration date is passed
            // allow an instructor to override
            // if the caller has requested we check all aircraft or the aircraft ID is for
            // a rental aircraft, check the rule
            If (UCase($CurrentlySelectedAircraftID) == "ALL" || 
                IsRentalAircraft($CurrentlySelectedAircraftID))
            {
                If ($RulesResult)
                {
                    $CheckCurrencyRuleResult = $ClearedToFly;
                }
                Else
                {
                    // if this a night time flight rule, just fail the pilot for night time
                    If (InStr(1, $CurrencyRule, "Night_Flight_Hours", $vbTextCompare) != 0)
                    {
                        // night time, cleared for day only
                        $CheckCurrencyRuleResult = $ClearedToFlyDayOnly;
                    }
                    Else
                    {
                        $CheckCurrencyRuleResult = $NotClearedToFlyOverride;
                    }
                }
            }
            Else
            {
                // we are checking a rule that does not apply to the type of aircraft we
                // are trying to fly, ignore the rule
                $CheckCurrencyRuleResult = $DoesntApply;
            }
            break;
        Case $CurrencyRequiredToFlyInstrument:
            // rule is required to fly instrument, this is only a warning
            If ($RulesResult)
                $CheckCurrencyRuleResult = $ClearedToFly;
            Else
                $CheckCurrencyRuleResult = $ClearedToFlyNoInstruments;
            break;
        Case $CurrencyInformation:
            // rule is for information only, so it doesn't apply
            $CheckCurrencyRuleResult = $InformationOnly;
            break;
        default:
            // rule doesn't apply
            $CheckCurrencyRuleResult = $DoesntApply;
            break;
        }
    }
    Else
    {
        // rule applies to an aircraft type. if this is a non-rental (private) aircraft type
        // the rule does not apply
        If (IsRentalAircraftType($AircraftType))
        {
            // if the caller has requested we check all aircraft or the aircraft ID is for
            // a rental aircraft, check the rule
            If (UCase($CurrentlySelectedAircraftID) == "ALL" || IsRentalAircraft($CurrentlySelectedAircraftID))
            {
                // if the user satisfies the requirement for the
                // aircraft they are trying to fly, none of the other aircraft type rules apply
                // if it is specified as "All" check all aircraft
                If (UCase(RemoveCurrencyFieldQuotes($CurrentlySelectedAircraftType)) == UCase(RemoveCurrencyFieldQuotes($AircraftType)) || 
                    UCase($CurrentlySelectedAircraftType) == "ALL")
                {
                    // we are checking a rule that applies to the type of aircraft we are
                    // trying to fly, see if the user passes
                    switch (UCase($Requirement))
                    {
                    Case $CurrencyRequiredToFly:
                        // rule is required to fly, if the expiration date is passed
                        // ground the pilot
                        If ($RulesResult)
                            $CheckCurrencyRuleResult = $ClearedToFly;
                        Else
                            $CheckCurrencyRuleResult = $NotClearedToFly;
                        break;
                    Case $CurrencyRequiredToSolo:
                        // rule is required to solo, if the expiration date is passed
                        // allow an instructor to override
                        If ($RulesResult)
                        {
                            $CheckCurrencyRuleResult = $ClearedToFly;
                        }
                        Else
                        {
                            // if this a night time flight rule, just fail the pilot for night time
                            If (InStr(1, $CurrencyRule, "Night_Flight_Hours", $vbTextCompare) != 0)
                                // night time, cleared for day only
                                $CheckCurrencyRuleResult = $ClearedToFlyDayOnly;
                            Else
                                $CheckCurrencyRuleResult = $NotClearedToFlyOverride;
                        }
                        break;
                    Case $CurrencyRequiredToFlyInstrument:
                        // rule is required to fly instrument, if the expiration date is passed
                        // allow an instructor to override
                        If ($RulesResult)
                            $CheckCurrencyRuleResult = $ClearedToFly;
                        Else
                            $CheckCurrencyRuleResult = $ClearedToFlyNoInstruments;
                        break;
                    Case $CurrencyInformation:
                        // rule is for information only, so it doesn't apply
                        $CheckCurrencyRuleResult = $InformationOnly;
                        break;
                    default:
                        // rule doesn't apply
                        $CheckCurrencyRuleResult = $DoesntApply;
                        break;
                    }
                }
                Else
                {
                    // we are checking a rule that does not apply to the type of aircraft we
                    // are trying to fly, ignore the rule
                    $CheckCurrencyRuleResult = $DoesntApply;
                    if ($debug_flag)
                        echo "DEBUG: CheckCurrencyRule: Rule doesn't apply because it is not a rental<BR>";
                }
            }
            Else
            {
                // we are checking a rule that does not apply to the type of aircraft we
                // are trying to fly, ignore the rule
                $CheckCurrencyRuleResult = $DoesntApply;
                if ($debug_flag)
                    echo "DEBUG: CheckCurrencyRule: Rule doesn't apply to the type of aircraft we are flying<BR>";
            }
        }
        Else
        {
            // we are checking a rule that does not apply to the type of aircraft we
            // are trying to fly, ignore the rule
            $CheckCurrencyRuleResult = $DoesntApply;
            if ($debug_flag)
                echo "DEBUG: CheckCurrencyRule: Rule doesn't apply because it isn't an aircraft type<BR>";
        }
    }
    
    // return the result
    return $CheckCurrencyRuleResult;
}

//********************************************************************
// ParseCurrencyRules(
//                       PilotType As String,
//                       UserKeycode As String,
//                       CurrencyRuleText() As String,
//                       CurrencyStatusText() As String,
//                       FlightStatusValue As CurrencyCheckType,
//                       FlightStatusReason As String,
//                       CurrentlySelectedAircraftType as string,
//                       CurrentlySelectedAircraftID as string)
//
// Purpose:      Parse the currency information and return the results
//
// Inputs:
//   PilotType - Student, Private, Instrument or Instructor rating
//               for selecting currency information
//   UserKeycode - the keycode for the current user
//   CurrentlySelectedAircraftType - the current aircraft type
//   CurrentlySelectedAircraftID - the tail number of the aircraft
//
// Outputs:
//   CurrencyRuleText() - array of text describing the currency rules
//   CurrencyStatusText() - array of text describing the status or the
//                          currency rules
//   FlightStatusValue - results of the curreny check
//   FlightStatusReason - reason (if needed) for not clearing the pilot
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function ParseCurrencyRules(
                       $PilotType,
                       $UserKeycode,
                       &$CurrencyRuleText,
                       &$CurrencyStatusText,
                       &$FlightStatusValue,
                       &$FlightStatusReason,
                       $CurrentlySelectedAircraftType,
                       $CurrentlySelectedAircraftID)
{    
    global $NoCurrencyPilot, $StudentPilot, $PrivatePilotUnder200;
    global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
    global $DoesntApply, $ClearedToFly, $ClearedToFlyDayOnly;
    global $ClearedToFlyNoInstruments, $NotClearedToFly, $NotClearedToFlyOverride;
    global $InformationOnly;
    global $WithinField, $WithinDate;
    global $debug_flag;
    global $MaxCurrencyFieldRecords;
    
    // if we have an invalid pilot type, don't process the currency records
    switch ($PilotType)
    {
    Case $NoCurrencyPilot:
        // no currency required for this type of pilot
        $FlightStatusValue = $ClearedToFly;
        $FlightStatusReason = "";
        return;
        break;
    Case $StudentPilot:
        break;
    Case $PrivatePilotUnder200:
        break;
    Case $PrivatePilotOver200:
        break;
    Case $InstrumentPilot:
        break;
    Case $CFIPilot:
        break;
    Case $InstructorInstruction:
        break;
    default:
        // invalid pilot type, tell the user
        $FlightStatusValue = $NotClearedToFly;
        $FlightStatusReason = "Invalid Pilot Type";
        return;
    }
        
    // create a query to get all currency rules for the rating of pilot
    // Results in these rows:
    //          [0] - ID               
    //          [1] - Item                
    //          [2] - Pass_Criteria               
    //          [3] - Expires_Month_End                
    //          [4] - Student
    //          [5] - Private_Under_200                
    //          [6] - Private_Over_200               
    //          [7] - Instrument                
    //          [8] - CFI
	$SQLResult = sql_query("SELECT * FROM CurrencyRules WHERE (" .
                        Replace(Trim($PilotType), " ", "_") . " <> 'No') ORDER BY ID");
    
    // assume that the status will be cleared to fly
    $FlightStatusValue = $ClearedToFly;
    $FlightStatusReason = "";
    
    // if no records are found for this pilot type, don't display
    // the currency fields
	if ($SQLResult)
	{
        // loop through all the rules and put the expiration dates on the screen
        $Index = 0;
        $CurrentResult = False;
		for ($i = 0; ($row = sql_row($SQLResult, $i)); $i++) 
		{
            // If this rule is a aircraft type rating rule there should be a matching
            // aircraft in the database. We assume that the first part of the subrule
            // (up to the "_" or " ") is the aircraft type
            $AircraftCurrencyFieldName = Trim($row[1]);
            $AircraftType = GetNextToken($AircraftCurrencyFieldName, " ");
            
            // if this is a valid aircraft type and it is a rental aircraft type
            // process the rule (non-rental aircraft don't have currency rules)
            If (IsAircraftType($AircraftType))
            {
                // an aircraft type, is it a rental aircraft type
                If (IsRentalAircraftType($AircraftType))
                {
                    $AddFieldToList = True;
                }
                Else
                {
                    // non-rental (private aircraft) don't show flight test
                    // or written test fields
                    $AddFieldToList = False;
                }
            }
            Else
            {
                // not an aircraft type, display the field
                $AddFieldToList = True;
            }
    
            // should this field be processed?
            If ($AddFieldToList)
            {
                // get the rule from the record
                $CompleteRule = $row[2];
                $RuleExpiresEndOfMonth = $row[3];
                if ($debug_flag)
                    echo "DEBUG: CompleteRule: $CompleteRule<BR>";
                
                // clear the last operator
                $Operator = "";
                 
                // clear the last within fields
                $WithinField = "";
                $WithinDate = "";
                
                // convert the rule from infix to postfix
                $RuleFields = InfixToPostfix($CompleteRule);
                                
                // get the result of this rule
                CurrencyGetRuleResult(
                                            $RuleFields,
                                            $ExpirationDate,
                                            $CurrentResult,
                                            $UserKeycode,
                                            $RuleExpiresEndOfMonth,
                                            $PilotType);
                    
                // if the rule expires at the end of the month, set the expiration to the last day
                // of the month
                If ($RuleExpiresEndOfMonth)
                {
                    // set date to the end of the month
                    If (UCase($ExpirationDate) != "FALSE" &&
                        UCase($ExpirationDate) != "TRUE" &&
                        UCase($ExpirationDate) != "EXPIRED")
                    {
                        $ExpirationDate = DateSerial(Year($ExpirationDate), Month($ExpirationDate) + 1, 0);
                    }
                }
                                
                // get the currency rule for the pilot type
                switch ($PilotType)
                {
                Case $StudentPilot:
                    $CurrencyRule = Trim($row[4]);
                    break;
                Case $PrivatePilotUnder200:
                    $CurrencyRule = Trim($row[5]);
                    break;
                Case $PrivatePilotOver200:
                    $CurrencyRule = Trim($row[6]);
                    break;
                Case $InstrumentPilot:
                    $CurrencyRule = Trim($row[7]);
                    break;
                Case $CFIPilot:
                    $CurrencyRule = Trim($row[8]);
                    break;
                }

                // check the currency rule to see if we are still cleared to fly
                $CurrencyRuleResult = CheckCurrencyRule(
                                            $CurrencyRule,
                                            $CompleteRule,
                                            $CurrentResult,
                                            $CurrentlySelectedAircraftType,
                                            $CurrentlySelectedAircraftID);
                if ($debug_flag)
                    echo "DEBUG: CurrencyRule: $CurrencyRule CurrencyRuleResult: $CurrencyRuleResult ExpirationDate: " . FormatDate($ExpirationDate) . "<BR>";
                switch ($CurrencyRuleResult)
                {
                Case $DoesntApply:
                    // rule doesn't apply in this case, ignore
                    break;
                Case $InformationOnly:
                    // rule is for information only, ignore
                    break;
                Case $ClearedToFly:
                    // cleared, but don't override any previous failures
                    If (!($FlightStatusValue == $ClearedToFlyNoInstruments ||
                            $FlightStatusValue == $NotClearedToFly ||
                            $FlightStatusValue == $NotClearedToFlyOverride ||
                            $FlightStatusValue == $ClearedToFlyDayOnly))
                    {
                        // don't lower the flight status
                        $FlightStatusReason = "";
                        $FlightStatusValue = $ClearedToFly;
                    }
                    break;
                Case $ClearedToFlyDayOnly:
                    // not cleared for night time flight, save the results
                    If (!($FlightStatusValue == $NotClearedToFly ||
                            $FlightStatusValue == $NotClearedToFlyOverride))
                    {
                        // don't lower the flight status
                        $FlightStatusValue = $ClearedToFlyDayOnly;
                        $FlightStatusReason = "";
                    }
                    break;
                Case $ClearedToFlyNoInstruments:
                    // not cleared for instruments, save the results
                    If (!($FlightStatusValue == $NotClearedToFly ||
                            $FlightStatusValue == $NotClearedToFlyOverride))
                    {
                        // don't lower the flight status
                        $FlightStatusValue = $ClearedToFlyNoInstruments;
                        $FlightStatusReason = "Expired " . Trim($row[1]);
                    }
                    break;
                Case $NotClearedToFlyOverride:
                    // not cleared, save the results
                    If (!($FlightStatusValue == $NotClearedToFly))
                    {
                        // if we have already set the reason for an equal priority rule, don't
                        // change the reason
                        If ($FlightStatusValue != $NotClearedToFlyOverride)
                        {
                            $FlightStatusReason = "Expired " . Trim($row[1]);
                        }
                        // don't lower the flight status
                        $FlightStatusValue = $NotClearedToFlyOverride;
                    }
                    break;
                Case $NotClearedToFly:
                    // not cleared, save the results
                    $FlightStatusValue = $NotClearedToFly;
                    $FlightStatusReason = Trim($row[1]);
                    break;
                }
                
                // does the expiration date apply to this rule
                If (UCase($ExpirationDate) != "N/A" &&
                    UCase($ExpirationDate) != "FALSE" &&
                    UCase($ExpirationDate) != "TRUE" &&
                    UCase($ExpirationDate) != "EXPIRED")
                {
                    // if the date has expired, mark the entry as "EXPIRED"
                    If (Len(Trim($ExpirationDate)) > 0)
                    {
                        if ($debug_flag)
                        {
                            echo "DEBUG: ParseCurrencyRules. Expiration date check ExpirationDate: $ExpirationDate Serial: " . 
                                DateSerial(Year($ExpirationDate), Month($ExpirationDate), Day($ExpirationDate)) . "<BR>";
                        }
                        If (DateSerial(Year($ExpirationDate), Month($ExpirationDate), Day($ExpirationDate)) < DateValue("now"))
                        {
                            $ExpirationDate = "EXPIRED";
                        }
                    }
                    Else
                    {
                        $ExpirationDate = "EXPIRED";
                    }
                }
            
                // add the currency rule title and expiration date to the form
                $CurrencyRuleText[$Index] = Trim($row[1]);
                $CurrencyStatusText[$Index] = $ExpirationDate;
                $Index = $Index + 1;
            }
        }
    }
	else
	{
	    // sql error, tell the user
	    fatal_error(0, sql_error());
	}
}

//********************************************************************
// Sub GetCurrencyValues(PilotType As String,
//                       UserKeycode As String,
//                       CurrencyRuleText() As String,
//                       CurrencyStatusText() As String,
//                       PilotIdentificationText As String,
//                       FlightStatusValue As CurrencyCheckType,
//                       FlightStatusReason as String,
//                       CurrentlySelectedAircraftType as string,
//                       CurrentlySelectedAircraftID as string)
//
// Purpose:      Return the currency information
//
// Inputs:
//   PilotType - Student, Private, Instrument or Instructor rating
//               for selecting currency information
//   UserKeycode - the keycode for the current user
//             CurrentlySelectedAircraftType as string
//   CurrentlySelectedAircraftType - the current aircraft type
//   CurrentlySelectedAircraftID - the tail number of the aircraft
//
// Outputs:
//   CurrencyRuleText() - array of text describing the currency rules
//   CurrencyStatusText() - array of text describing the status of the
//                          currency rules
//   PilotIdentificationText - string for the screen descriping the
//                             pilot
//   FlightStatusValue - results of the curreny check
//   FlightStatusReason - reason (if needed) that the pilot is not cleared
//
// Returns:
//   none
//
// Notes:
//       The Currency table of the database contains the following
//       fields:
//           Item - description of the currency rule
//           Pass Criteria - rule that defines how the rule passes
//           Student - set to true if the rule applies to student pilots
//           PrivateUnder200 - set to CurrencyRequiredToFly, CurrencyRequiredToSolo
//                           or "No" to define how the rule applies to
//                             private pilots with less than 200 hours
//           PrivateOver200 - set to CurrencyRequiredToFly, CurrencyRequiredToSolo
//                           or "No" to define how the rule applies to
//                            private pilots with over 200 hours
//           Instrument - set to CurrencyRequiredToFly, CurrencyRequiredToSolo
//                           or "No" to define how the rule applies to
//                           instrument pilots
//           CFI - set to CurrencyRequiredToFly, CurrencyRequiredToSolo
//                           or "No" to define how the rule applies to
//                           CFI pilots
//*********************************************************************
function GetCurrencyValues(
                       $PilotType,
                       $UserKeycode,
                       &$CurrencyRuleText,
                       &$CurrencyStatusText,
                       &$PilotIdentificationText,
                       &$FlightStatusValue,
                       &$FlightStatusReason,
                       $CurrentlySelectedAircraftType,
                       $CurrentlySelectedAircraftID)
{                       
    global $debug_flag;

    // get the pilot type information
    switch ($PilotType)
    {
    Case "Student":
        $PilotIdentificationText = "Student Pilot Rules";
        break;
    Case "Private Under 200":
        $PilotIdentificationText = "Private Pilot (Under 200 hrs) Rules";
        break;
    Case "Private Over 200":
        $PilotIdentificationText = "Private Pilot (Over 200 hrs) Rules";
        break;
    Case "Instrument":
        $PilotIdentificationText = "Instrument Pilot Rules";
        break;
    Case "CFI":
        $PilotIdentificationText = "CFI Pilot Rules";
        break;
    }

    // load the date currency information into the screen
    ParseCurrencyRules(
                        $PilotType,
                        $UserKeycode,
                        $CurrencyRuleText,
                        $CurrencyStatusText,
                        $FlightStatusValue,
                        $FlightStatusReason,
                        $CurrentlySelectedAircraftType,
                        $CurrentlySelectedAircraftID);

}

//********************************************************************
// CurrencyPerformOperation(FirstOperand as string,
//                          SecondOperand as string,
//                          Operator as string
//                          RuleExpiresEndOfMonth as Boolean) as string
//
// Purpose:      Apply the operator to the first operand and the
//               second operand passing back the result. This procedure
//               checks the type of operator (date or integer) and
//               performs the proper operation.
//
// Inputs:
//   FirstOperand - first operand for the operation
//   SecondOperand - second operand for the operation
//   Operator - operator for the operation
//   RuleExpiresEndOfMonth - true if the rule expires at the end of the month
//
// Outputs:
//   none
//
// Returns:
//   The result of the operation
//
// Notes:
//
//*********************************************************************
function CurrencyPerformOperation(
                            $FirstOperand,
                            $SecondOperand,
                            $Operator,
                            $RuleExpiresEndOfMonth)
{                                
    global $debug_flag;

    // check to see if operand one is a valid date
    $OperandOneValidDate = IsValidDate($FirstOperand);
    
    // check to see if operand two is a valid date
    $OperandTwoValidDate = IsValidDate($SecondOperand);
    
    // if the date modifier (Y - Year, M - Month, or D - Day) or the
    // keyword "Now" is present assume it is a date operation
    $DateModifier = UCase(Right($SecondOperand, 1));
    $DateOperand = Left($SecondOperand, Len($SecondOperand) - 1);
    If (UCase($SecondOperand) == "NOW") $DateModifier = "N";
    $DateModifierPresent = ($DateModifier == "Y" || $DateModifier == "M" || $DateModifier == "D" || $DateModifier == "N");
    If ($DateModifierPresent ||
        ($OperandOneValidDate && $OperandTwoValidDate))
    {
        // date operation
        switch ($Operator)
        {
        Case "+":               // add the previous two fields
            If ($DateModifier == "Y")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand) + $DateOperand,
                                                      Month($FirstOperand),
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "M")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand) + $DateOperand, 
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "D")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand) + $DateOperand);
            ElseIf ($DateModifier == "N")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand)) +
                                           DateValue("now");
            Else
                $CurrencyPerformOperationResult = DateValue($FirstOperand) + 
                                           DateValue($SecondOperand);
            
            // if the rule expires at the end of the month, adjust the date
            If ($RuleExpiresEndOfMonth && $DateModifierPresent)
            {
                // rule expires at the end of the month, compute the date
                $CurrencyPerformOperationResult = 
                                DateSerial(
                                        Year($CurrencyPerformOperationResult), 
                                        Month($CurrencyPerformOperationResult) + 1, 
                                        0);
            }
            break;
        Case "-":               // subtract the previous two fields
            If ($DateModifier == "Y")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand) - $DateOperand,
                                                      Month($FirstOperand),
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "M")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand) - $DateOperand,
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "D")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand) - $DateOperand);
            ElseIf ($DateModifier == "N")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand)) -
                                           DateValue("now");
            Else
                $CurrencyPerformOperationResult = DateValue($FirstOperand) -
                                           DateValue($SecondOperand);
            
            // if the rule expires at the end of the month, adjust the date
            if ($RuleExpiresEndOfMonth && $DateModifierPresent)
            {
                // rule expires at the end of the month, compute the date
                $CurrencyPerformOperationResult = 
                                DateSerial(
                                        Year($CurrencyPerformOperationResult), 
                                        Month($CurrencyPerformOperationResult) + 1, 
                                        0);
            }
            break;
        Case "*":               // multiply the previous two fields
            If ($DateModifier == "Y")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand) * $DateOperand,
                                                      Month($FirstOperand),
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "M")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand) * $DateOperand,
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "D")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand) * $DateOperand);
            ElseIf ($DateModifier == "N")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand)) *
                                           DateValue("now");
            Else
                $CurrencyPerformOperationResult = DateValue($FirstOperand) *
                                           DateValue($SecondOperand);
            
            // if the rule expires at the end of the month, adjust the date
            if ($RuleExpiresEndOfMonth && $DateModifierPresent)
            {
                // rule expires at the end of the month, compute the date
                $CurrencyPerformOperationResult = 
                                DateSerial(
                                        Year($CurrencyPerformOperationResult), 
                                        Month($CurrencyPerformOperationResult) + 1, 
                                        0);
            }
            break;
        Case "/":               // divide by the previous two fields
            If ($DateModifier == "Y")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand) / $DateOperand,
                                                      Month($FirstOperand),
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "M")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand) / $DateOperand,
                                                      Day($FirstOperand));
            ElseIf ($DateModifier == "D")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand) / $DateOperand);
            ElseIf ($DateModifier == "N")
                $CurrencyPerformOperationResult = DateSerial(Year($FirstOperand),
                                                      Month($FirstOperand),
                                                      Day($FirstOperand)) /
                                           DateValue("now");
            Else
                $CurrencyPerformOperationResult = DateValue($FirstOperand) / 
                                           DateValue($SecondOperand);
            
            // if the rule expires at the end of the month, adjust the date
            if ($RuleExpiresEndOfMonth && $DateModifierPresent)
            {
                // rule expires at the end of the month, compute the date
                $CurrencyPerformOperationResult = 
                                DateSerial(
                                        Year($CurrencyPerformOperationResult), 
                                        Month($CurrencyPerformOperationResult) + 1, 
                                        0);
            }
            break;
        Case ">":               // compare greater than to the previous two fields
            If ($DateModifier == "Y")
            {
                if (DateValue($FirstOperand) > DateSerial(Year("now") + $DateOperand, Month("now"), Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "M")
            {
                if (DateValue($FirstOperand) > DateSerial(Year("now"), Month("now") + $DateOperand, Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "D")
            {
                if (DateValue($FirstOperand) > DateSerial(Year("now"), Month("now"), Day("now") + $DateOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "N")
                if (DateValue($FirstOperand) > DateValue("now"))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            Else
            {
                if (DateValue($FirstOperand) > DateValue($SecondOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            break;
        Case "<":               // compare less than to the previous two fields
            If ($DateModifier == "Y")
            {
                if (DateValue($FirstOperand) < DateSerial(Year("now") + $DateOperand, Month("now"), Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "M")
            {
                if (DateValue($FirstOperand) < DateSerial(Year("now"), Month("now") + $DateOperand, Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "D")
            {
                if (DateValue($FirstOperand) < DateSerial(Year("now"), Month("now"), Day("now") + $DateOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "N")
            {
                if (DateValue($FirstOperand) < DateValue("now"))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            Else
            {
                if (DateValue($FirstOperand) < DateValue($SecondOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            break;
        Case ">=":              // compare greater than or equal to the previous two fields
            If ($DateModifier == "Y")
            {
                if (DateValue($FirstOperand) >= DateSerial(Year("now") + $DateOperand, Month("now"), Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "M")
            {
                if (DateValue($FirstOperand) >= DateSerial(Year("now"), Month("now") + $DateOperand, Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "D")
            {
                if (DateValue($FirstOperand) >= DateSerial(Year("now"), Month("now"), Day("now") + $DateOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "N")
            {
                if (DateValue($FirstOperand) >= DateValue("now"))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            Else
            {
                if (DateValue($FirstOperand) >= DateValue($SecondOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            break;
        Case "<=":             // compare less than or equal to the previous two fields
            If ($DateModifier == "Y")
            {
                if (DateValue($FirstOperand) <= DateSerial(Year("now") + $DateOperand, Month("now"), Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "M")
            {
                if (DateValue($FirstOperand) <= DateSerial(Year("now"), Month("now") + $DateOperand, Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "D")
            {
                if (DateValue($FirstOperand) <= DateSerial(Year("now"), Month("now"), Day("now") + $DateOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "N")
            {
                if (DateValue($FirstOperand) <= DateValue("now"))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            Else
            {
                if (DateValue($FirstOperand) <= DateValue($SecondOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            break;
        Case "=":               // compare equal to the previous two fields
            If ($DateModifier == "Y")
            {
                if (DateValue($FirstOperand) == DateSerial(Year("now") + $DateOperand, Month("now"), Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "M")
            {
                if (DateValue($FirstOperand) == DateSerial(Year("now"), Month("now") + $DateOperand, Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "D")
            {
                if (DateValue($FirstOperand) == DateSerial(Year("now"), Month("now"), Day("now") + $DateOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "N")
            {
                if (DateValue($FirstOperand) == DateValue("now"))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            Else
            {
                If (DateValue($FirstOperand) == DateValue($SecondOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            break;
        Case "<>":              // compare not equal to the previous two fields
            If ($DateModifier == "Y")
            {
                if (DateValue($FirstOperand) != DateSerial(Year("now") + $DateOperand, Month("now"), Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "M")
            {
                if (DateValue($FirstOperand) != DateSerial(Year("now"), Month("now") + $DateOperand, Day("now")))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "D")
            {
                if (DateValue($FirstOperand) != DateSerial(Year("now"), Month("now"), Day("now") + $DateOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            ElseIf ($DateModifier == "N")
            {
                if (DateValue($FirstOperand) != DateValue("now"))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            Else
            {
                if (DateValue($FirstOperand) != DateValue($SecondOperand))
                    $CurrencyPerformOperationResult = "1";
                Else
                    $CurrencyPerformOperationResult = "0";
            }
            break;
        Case "->":               // if operand1 is true, evaluate operand2
            // shouldn't be here with this operator
            break;
        }
    }
    Else
    {
        // integer operation
        switch ($Operator)
        {
        Case "+":               // add the previous two fields
            $CurrencyPerformOperationResult = Str(Val($FirstOperand) + Val($SecondOperand));
            break;
        Case "-":               // subtract the previous two fields
             $CurrencyPerformOperationResult = Str(Val($FirstOperand) - Val($SecondOperand));
            break;
        Case "*":               // multiply the previous two fields
            $CurrencyPerformOperationResult = Str(Val($FirstOperand) * Val($SecondOperand));
            break;
        Case "/":               // divide by the previous two fields
            $CurrencyPerformOperationResult = Str(Val($FirstOperand) / Val($SecondOperand));
            break;
        Case ">":               // compare greater than to the previous two fields
            if (Val($FirstOperand) > Val($SecondOperand))
                $CurrencyPerformOperationResult = "1";
            Else
                $CurrencyPerformOperationResult = "0";
            break;
        Case "<":               // compare less than to the previous two fields
            if (Val($FirstOperand) < Val($SecondOperand))
                $CurrencyPerformOperationResult = "1";
            Else
                $CurrencyPerformOperationResult = "0";
            break;
        Case ">=":              // compare greater than or equal to the previous two fields
            if (Val($FirstOperand) >= Val($SecondOperand))
                $CurrencyPerformOperationResult = "1";
            Else
                $CurrencyPerformOperationResult = "0";
            break;
        Case "<=":              // compare less than or equal to the previous two fields
            if (Val($FirstOperand) <= Val($SecondOperand))
                $CurrencyPerformOperationResult = "1";
            Else
                $CurrencyPerformOperationResult = "0";
            break;
        Case "=":               // compare equal to the previous two fields
            if (Val($FirstOperand) == Val($SecondOperand))
                $CurrencyPerformOperationResult = "1";
            Else
                $CurrencyPerformOperationResult = "0";
            break;
        Case "<>":              // compare not equal to the previous two fields
            if (Val($FirstOperand) != Val($SecondOperand))
                $CurrencyPerformOperationResult = "1";
            Else
                $CurrencyPerformOperationResult = "0";
            break;
        Case "&":               // logical AND of the previous two fields
            if (Val($FirstOperand) == 0 || Val($SecondOperand) == 0)
                $CurrencyPerformOperationResult = "0";
            Else
                $CurrencyPerformOperationResult = "1";
            break;
        Case "|":               // logical OR of the previous two fields
            if (Val($FirstOperand) == 1 || Val($SecondOperand) == 1)
                $CurrencyPerformOperationResult = "1";
            Else
                $CurrencyPerformOperationResult = "0";
            break;
        Case "->":              // if operand1 is true, evaluate operand2
            // shouldn't be here with this operator
            break;
        }
    }
    
    // return the results
    return $CurrencyPerformOperationResult;
}

//********************************************************************
// Sub CurrencyComputeSpecialField(
//                                SubRuleString As String,
//                                UserKeycode As String,
//                                DatabaseFieldName1 As String,
//                                DatabaseFieldName2 As String,
//                                IncludeSimTime as Boolean,
//                                IncludePCATDTime as Boolean,
//                                CurrentResult As String,
//                                ExpirationDate As String)
//
// Purpose:      Handle special currency fields.
//
// Inputs:
//   SubRuleString - A string containing the rule
//   WithinDate - date field for the within clause
//   UserKeycode - the keycode for the current user
//   DatabaseFieldName1 - first database field name for computing the
//                        results
//   DatabaseFieldName2 - second database field name for computing the
//                        results (may be "" if not needed).
//   IncludeSimTime - set TRUE to include time logged in a SIM aircraft type
//                    in the calculations
//   IncludePCATDTime - set TRUE to include time logged in a PCATD aircraft type
//                    in the calculations
//
// Outputs:
//   ExpirationDate - the computed expiration date of the rule
//   CurrentResult - the result of the operation
//
// Returns:
//
// Notes:
//   Special currency fields have the format:
//           Field_Name Operator Value
//   Where the Field_Name is the currency field to check, operator is
//   an algerbraic operator and value is the comparsion value.
//
//*********************************************************************
function CurrencyComputeSpecialField(
                                $SubRuleString,
                                $UserKeycode,
                                $DatabaseFieldName1,
                                $DatabaseFieldName2,
                                $IncludeSimTime,
                                $IncludePCATDTime,
                                &$CurrentResult,
                                &$ExpirationDate)
{        
    global $WithinField, $WithinDate;
    global $debug_flag;
    global $NullDate;

    // get the operator and value from the currency rule string
    $tmpSubRuleString = $SubRuleString;
    $CurrencyValue = GetNextToken($tmpSubRuleString, " ");
    $Operator = GetNextToken($tmpSubRuleString, " ");
    
    // build the query
    If (Len(Trim($DatabaseFieldName2)) == 0)
    {
        // only one database field to check
        $FlightQuery = "SELECT Date, $DatabaseFieldName1 FROM Flight WHERE (Keycode = '" . 
                    Trim($UserKeycode) . "'" . " AND ";
    }
    Else
    {
        // two database fields to check
        $FlightQuery = "SELECT Date, $DatabaseFieldName1, $DatabaseFieldName2 FROM Flight WHERE (Keycode = '" .
                    Trim($UserKeycode) . "'" . " AND ";
    }
    
    // exclude the simulator time if requested
    If (!$IncludeSimTime)
    {
        $FlightQuery = $FlightQuery . "model_id <> " . Str(LookupModelID("SIM")) .
                    " AND ";
    }
    
    // exclude the PCATD time if requested
    If (!$IncludePCATDTime)
    {
        $FlightQuery = $FlightQuery . "model_id <> " . Str(LookupModelID("PCATD")) .
                    " AND ";
    }
    
    // complete the query
    $FlightQuery = $FlightQuery . "Date >= '" . 
                FormatField(DateSerial(Year($WithinDate), Month($WithinDate), Day($WithinDate)), "DatabaseDate") . "'" .
                ")";
                          
    // create a query to get the desired information from the recent flight database
    $FlightQuery = $FlightQuery . " ORDER BY Date";
    
    // open a record set for the requested data from the database
	$SQLResult = sql_query($FlightQuery);
    if ($debug_flag)
        echo "DEBUG: CurrencyComputeSpecialField FlightQuery: $FlightQuery<BR>";
    
    // did we have any SQL errrors?
	if (!$SQLResult)
	{
	    // sql error, tell the user
	    fatal_error(0, sql_error());
    }
    Else
    {
        // no SQL errors, did we have any valid records?
    	if (mysqli_num_rows($SQLResult) > 0)
    	{
            // records found, add the totals
            
            // loop through the records and compute the toal of the special fields
            $FieldValue = 0;
            $ExpirationDate = DateValue($NullDate);
    		for (($i = mysqli_num_rows($SQLResult) - 1); $i >= 0; $i--) 
    		{
     		    $row = sql_row($SQLResult, $i);
                If (Len(Trim($DatabaseFieldName2)) == 0)
                {
                    // only one database field to check
                    $FieldValue = $FieldValue + $row[1];
                    If ($row[1] > 0)
                    {
                        $ExpirationDate = DateValue($row[0]);
                    }
                }
                Else
                {
                    // two database fields to check
                    $FieldValue = $FieldValue + Val($row[1]) + Val($row[2]);
                    If ((Val($row[1]) + Val($row[2])) > 0)
                    {
                        $ExpirationDate = DateValue($row[0]);
                    }
                }
                
                // if we have satisfied the rule, exit the loop so that the expiration date
                // will be the earliest expiration of the rule
                If (CurrencyPerformOperation(Str($FieldValue), $CurrencyValue, $Operator, False) == "1")
                    break;
            }
        
            // set the results
            $CurrentResult = Str($FieldValue);
            $ExpirationDate = CurrencyPerformOperation($ExpirationDate, $WithinField, "+", False);
        }
        Else
        {
            // no records found, set the values to zero
            $CurrentResult = Str(0);
            $ExpirationDate = "Now";
        }
    }
}

//********************************************************************
// CurrencyGetOperationResult(FirstOperand As String,
//                            SecondOperand As String,
//                            Operator As String,
//                            RuleExpiresEndOfMonth As Boolean,
//                            Result As String,
//                            ExpirationDate As String)
//
// Purpose:      Get the results of the currency operation.
//
// Inputs:
//   FirstOperand - first operand for the operation
//   SecondOperand - second operand for the operation
//   Operator - the operator for the operation
//   RuleExpiresEndOfMonth - true if the rule expires at the end of the month
//
// Outputs:
//   Result - the result of the operation
//   ExpirationDate - the expiration date of the operation
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function CurrencyGetOperationResult(
                            $FirstOperand,
                            $SecondOperand,
                            $Operator,
                            $RuleExpiresEndOfMonth,
                            &$Result,
                            &$ExpirationDate)
{
    global $debug_flag;

    // if the second operand is a date field, assume the first is the
    // expiration date
    $DateModifier = UCase(Right($SecondOperand, 1));
    If (UCase($SecondOperand) == "NOW")
    {
        $DateModifier = "N";
    }
    
    // perform the operation
    $Result = CurrencyPerformOperation($FirstOperand, $SecondOperand, $Operator, $RuleExpiresEndOfMonth);
    if ($debug_flag)
        echo "DEBUG: CurrencyGetOperationResult FirstOperand: $FirstOperand SecondOperand: $SecondOperand Operator: $Operator Result: $Result<BR>";
        
    // save the results as the expiration date if the second operand was a date
    If ($DateModifier == "Y" Or $DateModifier == "M" Or $DateModifier == "D" Or $DateModifier == "N")
    {
        $ExpirationDate = $FirstOperand;
    }
    
    // if it is a boolean operator, save the results of the test
    // we are inverting the results of the boolean operation so that
    // from the user perspective the value looks right, ie
    // "Manager's Grounding Action" displays as TRUE if the person is
    // grounded.
    If ($ExpirationDate == "BOOL")
    {
        If ($Result == 0)
            $ExpirationDate = "TRUE";
        Else
            $ExpirationDate = "FALSE";
    }
}

//********************************************************************
// CurrencyGetRuleResult(RuleString as String
//                          ExpirationDate As String,
//                          OperationResult As Boolean,
//                          UserKeycode As String,
//                          RuleExpiresEndOfMonth as Boolean,
//                          PilotType As String)
//
// Purpose:      Get the results of the currency rule.
//
// Inputs:
//   RuleString - A postfix string containing the currency rule to evaluate
//   UserKeycode - the keycode for the current user
//   RuleExpiresEndOfMonth - true if the rule expires at the end of the month
//   PilotType - Student, Private, Instrument or Instructor rating
//               for selecting currency information
//
// Outputs:
//   ExpirationDate - the computed expiration date of the rule
//   OperationResult - set TRUE if the operation evaluates to
//                   non-zero
//
// Returns:
//
// Notes:
//   The input rule is a postfix rule string.
//
//*********************************************************************
function CurrencyGetRuleResult(
                            $RuleString,
                            &$ExpirationDate,
                            &$OperationResult,
                            $UserKeycode,
                            $RuleExpiresEndOfMonth,
                            $PilotType)
{                                
    global $WithinField, $WithinDate;
    global $debug_flag;

    // initialize the stacks
    $OperatorStack = new Stack();
    $OperatorStack->Initialize();
    $ExpirationDateStack = new Stack();
    $ExpirationDateStack->Initialize();
    
    // set the sim and PCATD flags to log the time based on the level of the pilot
    // all pilots can log sim time for instrument time
    // only non-instrument pilots can log PCATD instrument time
    // no pilots can log takeoff, landings, or 60/90 day currency time in the sim or PCATD
    switch($PilotType)
    {
    Case "Student":
        $UseSim = True;
        $UsePCATD = True;
        break;
    Case "Private Under 200":
        $UseSim = True;
        $UsePCATD = True;
        break;
    Case "Private Over 200":
        $UseSim = True;
        $UsePCATD = True;
        break;
    Case "Instrument":
        $UseSim = True;
        $UsePCATD = False;
        break;
    Case "CFI":
        $UseSim = True;
        $UsePCATD = False;
        break;
    }

    // process the subrule
    While (Len($RuleString) > 0)
    {
        // get a token
        $FieldName = GetNextToken($RuleString, " ");
        
        // process the token
        switch (UCase($FieldName))
        {
        Case "WITHIN":          // the following rule applies within the past given time
            // get the date range for the Within field
            $WithinField = GetNextToken($RuleString, " ");
            $WithinDate = CurrencyPerformOperation(DateValue("now"), $WithinField, "-", False);
            break;
        Case "+":               // add the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            break;
        Case "-":               // subtract the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            break;
        Case "*":               // multiply the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            break;
        Case "/":               // divide by the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            break;
        Case "&":               // AND the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // adjust the expiration date for the AND operation
            $SecondOperandExpirationDate = $ExpirationDateStack->Pop();
            $FirstOperandExpirationDate = $ExpirationDateStack->Pop();
            
            // if the first operand expiration is invalid, use the second operand expiration date
            If (UCase($FirstOperandExpirationDate) == "FALSE" ||
                UCase($FirstOperandExpirationDate) == "TRUE" ||
                UCase($FirstOperandExpirationDate) == "N/A" ||
                Len($FirstOperandExpirationDate) == 0)
            {
                // first operand expiration date invalid, use the second
                $ExpirationDate = $SecondOperandExpirationDate;
            }
            // if the second operand expiration is invalid, use the first operand expiration date
            ElseIf (UCase($SecondOperandExpirationDate) == "FALSE" ||
                UCase($SecondOperandExpirationDate) == "TRUE" ||
                UCase($SecondOperandExpirationDate) == "N/A" ||
                Len($SecondOperandExpirationDate) == 0)
            {                
                // second operand expiration date invalid, use the first
                $ExpirationDate = $FirstOperandExpirationDate;
            }
            // both expiration dates are valid, compare and use the correct one
            Else
            {
                If (DateValue($FirstOperandExpirationDate) < DateValue($SecondOperandExpirationDate))
                    // in an AND operation, the earliest date will be the expiration date
                    $ExpirationDate = $FirstOperandExpirationDate;
                Else
                    // in an AND operation, the earliest date will be the expiration date
                    $ExpirationDate = $SecondOperandExpirationDate;
            }
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case "|":               // OR the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // adjust the expiration date for the OR operation
            $SecondOperandExpirationDate = $ExpirationDateStack->Pop();
            $FirstOperandExpirationDate = $ExpirationDateStack->Pop();
            
            // if the first operand expiration is invalid, use the second operand expiration date
            If (UCase($FirstOperandExpirationDate) == "FALSE" ||
                UCase($FirstOperandExpirationDate) == "TRUE" ||
                UCase($FirstOperandExpirationDate) == "N/A" ||
                Len($FirstOperandExpirationDate) == 0)
            {
                // first operand expiration date invalid, use the second
                $ExpirationDate = $SecondOperandExpirationDate;
            }
            // if the second operand expiration is invalid, use the first operand expiration date
            ElseIf (UCase($SecondOperandExpirationDate) == "FALSE" ||
                UCase($SecondOperandExpirationDate) == "TRUE" ||
                UCase($SecondOperandExpirationDate) == "N/A" ||
                Len($SecondOperandExpirationDate) == 0)
            {
                // second operand expiration date invalid, use the first
                $ExpirationDate = $FirstOperandExpirationDate;
            }
            // both expiration dates are valid, compare and use the correct one
            Else
            {
                If (DateValue($FirstOperandExpirationDate) > DateValue($SecondOperandExpirationDate))
                    // in an OR operation, the latest date will be the expiration date
                    $ExpirationDate = $FirstOperandExpirationDate;
                Else
                    // in an OR operation, the latest date will be the expiration date
                    $ExpirationDate = $SecondOperandExpirationDate;
            }
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case ">":               // compare greater than to the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case "<":               // compare less than to the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case ">=":              // compare greater than or equal to the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);

            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case "<=":              // compare less than or equal to the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case "=":               // compare equal to the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth,
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case "<>":              // compare not equal to the previous two fields
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // compute the result of the operation
            CurrencyGetOperationResult($FirstOperand,
                                        $SecondOperand,
                                        $FieldName,
                                        $RuleExpiresEndOfMonth, 
                                        $CurrentResult,
                                        $ExpirationDate);
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        Case "->":              // if operand1 is true, evaluate operand2
            // get the operands from the stack
            $SecondOperand = $OperatorStack->Pop();
            $FirstOperand = $OperatorStack->Pop();
            
            // get the expiration date values
            $SecondOperandExpirationDate = $ExpirationDateStack->Pop();
            $FirstOperandExpirationDate = $ExpirationDateStack->Pop();
            
            // use operand2 as the result if operand1 is true
            If ($FirstOperand == "1")
            {
                // operand1 is true, use the result of operand2
                $CurrentResult = $SecondOperand;
                $ExpirationDate = $SecondOperandExpirationDate;
            }
            Else
            {
                // force result to true since we assume that an if operator
                // is only used as part of an OR operation
                $CurrentResult = "0";
                $ExpirationDate = "N/A";
            }
            
            // push the result onto the stack
            $OperatorStack->Push($CurrentResult);
            
            // save the expiration date of this comparsion
            $ExpirationDateStack->Push($ExpirationDate);
            break;
        default:
            // it is a field name or a constant, look up the field name in the table
            If (Left($FieldName, 1) >= "0" && Left($FieldName, 1) <= "9")
            {
                // constants must begin with a number
                $CurrentResult = $FieldName;
            }
            Else
            {
                // check for special identifiers
                If (UCase($FieldName) == "NOW")
                {
                    $CurrentResult = "Now";
                    $ExpirationDate = "N/A";
                }
                ElseIf (UCase($FieldName) == "TRUE")
                {
                    $CurrentResult = "1";
                    $ExpirationDate = "BOOL";
                }
                ElseIf (UCase($FieldName) == "FALSE")
                {
                    $CurrentResult = "0";
                    $ExpirationDate = "BOOL";
                }
                ElseIf (UCase($FieldName) == "NIGHT_FLIGHT_HOURS")
                {
                    // special field - number of night flight hours
                    CurrencyComputeSpecialField(
                                $RuleString,
                                $UserKeycode,
                                "Night_Time",
                                "",
                                False,
                                False,
                                $CurrentResult,
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "DAY_FLIGHT_HOURS")
                {
                    // special field - number of day flight hours
                    CurrencyComputeSpecialField(
                                $RuleString,
                                $UserKeycode,
                                "Day_Time",
                                "",
                                False,
                                False,
                                $CurrentResult,
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "FLIGHT_HOURS")
                {
                    // special field - number of day and night flight hours
                    CurrencyComputeSpecialField(
                                $RuleString,
                                $UserKeycode,
                                "Day_Time",
                                "Night_Time",
                                False,
                                False,
                                $CurrentResult,
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "DUAL_TIME")
                {
                    // special field - number of dual flight hours
                    CurrencyComputeSpecialField(
                                $RuleString,
                                $UserKeycode,
                                "Dual_Time",
                                "",
                                False,
                                False,
                                $CurrentResult,
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "NIGHT_LANDINGS")
                {
                    // special field - number of night landings
                    CurrencyComputeSpecialField(
                                $RuleString,
                                $UserKeycode,
                                "Night_Landings",
                                "", 
                                False,
                                False, 
                                $CurrentResult, 
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "DAY_LANDINGS")
                {
                    // special field - number of day landings
                    CurrencyComputeSpecialField( 
                                $RuleString, 
                                $UserKeycode, 
                                "Day_Landings", 
                                "", 
                                False, 
                                False, 
                                $CurrentResult, 
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "LANDINGS")
                {
                    // special field - number of landings (day or night)
                    CurrencyComputeSpecialField( 
                                $RuleString, 
                                $UserKeycode, 
                                "Day_Landings", 
                                "Night_Landings", 
                                False, 
                                False, 
                                $CurrentResult, 
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "HOLDING_PROCEDURES")
                {
                    // special field - number of holding procedures
                    CurrencyComputeSpecialField( 
                                $RuleString, 
                                $UserKeycode, 
                                "Holding_Procedures", 
                                "", 
                                $UseSim, 
                                $UsePCATD, 
                                $CurrentResult, 
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "NAVIGATION_INTERCEPTS")
                {
                    // special field - number of navigation intercepts (instrument)
                    CurrencyComputeSpecialField( 
                                $RuleString, 
                                $UserKeycode, 
                                "Navigation_Intercepts", 
                                "", 
                                $UseSim, 
                                $UsePCATD, 
                                $CurrentResult, 
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "INSTRUMENT_APPROACHES")
                {
                    // special field - instrument approaches
                    CurrencyComputeSpecialField( 
                                $RuleString, 
                                $UserKeycode, 
                                "Instrument_Approach", 
                                "", 
                                $UseSim, 
                                $UsePCATD, 
                                $CurrentResult, 
                                $ExpirationDate);
                }
                ElseIf (UCase($FieldName) == "SAFETY_MEETING_DAYS")
                {
                    // special field - safety meeting expiration days
                    $CurrentResult = GetSafetyMeetingExpiration("Safety_Meeting_Expiration_Days");
                }
                ElseIf (UCase($FieldName) == "NEXT_SAFETY_MEETING")
                {
                    // special field - next safety meeting date
                    $CurrentResult = GetSafetyMeetingExpiration("Next_Safety_Meeting");
                    $ExpirationDate = $CurrentResult;
                }
                ElseIf (UCase($FieldName) == "LAST_SAFETY_MEETING")
                {
                    // special field - last safety meeting date
                    $CurrentResult = GetSafetyMeetingExpiration("Last_Safety_Meeting");
                    $ExpirationDate = $CurrentResult;
                }
                Else
                {
                    // field names, go lookup the value
                    $CurrentResult = LookupCurrencyFieldname($FieldName);
                }
            }
                
            // push the value onto the stack
            $OperatorStack->Push($CurrentResult);
            break;
        }
    }
        
    // convert the results of the operation to a boolean
    If ($CurrentResult != "0")
    {
        // operation resulted in non-zero, return TRUE
        $OperationResult = True;
    }
    Else
    {
        // operation resulted in zero, return FALSE
        $OperationResult = False;

        // adjust the expiration date if we are processing a
        // within time
        If (Len($WithinDate) != 0)
        {
            $ExpirationDate = CurrencyPerformOperation($ExpirationDate, $WithinField, "-", False);
        }
    }
}

//********************************************************************
// CurrencyGetNextSubRule(RuleString as String) As String
//
// Purpose:      Get a subrule from a rule string in the currency
//               field. A subrule is delimited by a logical operator.
//
// Inputs:
//   RuleString - A string containing the subrules
//
// Outputs:
//   RuleString - The updated string with the rule removed
//
// Returns:
//   The subrule from the RuleString
//
// Notes:
//   The RuleString is modified by this function.
//   This function does not work with parenthesis in an expression. It
//   is used to check aircraft type rules so this should not be a problem.
//
//*********************************************************************
function CurrencyGetNextSubRule(&$RuleString)
{
    // get tokens and build the SubRule string until we find a logical operator
    $SubRule = "";
    $TokenString = "";
    while ($TokenString != "&" && $TokenString != "|" && $TokenString != "->")
    {
        $TokenString = GetNextToken($RuleString, " ");
        if (Len($RuleString) == 0)
        {
            // last token, last subrule don't add blank to end of rule
            $SubRule = $SubRule . $TokenString;
            $TokenString = "";
            break;
        }
        else
        {
            if ($TokenString != "&" && $TokenString != "|" && $TokenString != "->")
            {
                $SubRule = $SubRule . $TokenString . " ";
            }
        }
    }
    
    // put back the last token as that is the next operator
    $RuleString = Trim($TokenString . " " . $RuleString);
    
    // pass back the SubRule
    $CurrencyGetNextSubRule = Trim($SubRule);
    
    // return the results
    return $CurrencyGetNextSubRule;
}

//********************************************************************
// DeleteOldTypeCurrencyRecords()
//
// Purpose:  Delete all the old aircraft type currency rules from
//           the database
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
function DeleteOldTypeCurrencyRecords()
{
    global $FlightTest, $WrittenTest, $InitialCheckout;
        
    // get the currency rules from the database
	$sql = "SELECT Pass_Criteria FROM CurrencyRules ORDER BY ID";		
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {    	        
        // loop through all the rules to get the aircraft type rules
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
            // get the rule from the record
            $CompleteRule = $row[0];
            
            // get the first subrule
            $SubRule = CurrencyGetNextSubRule($CompleteRule);
    
            // If this rule is a aircraft type rating rule there should be a matching
            // aircraft in the database. We assume that the first part of the subrule
            // (up to the "_") is the aircraft type
            $AircraftType = GetNextToken($SubRule, "_");
            
            // get the second token (flight or written)
            $TestType = GetNextToken($SubRule, "_");
        
            // see if the aircraft type is found in the database
            if (IsAircraftType($AircraftType))
            {
                // aircraft was found, must be a type rule.
                // delete the record
                DeleteDatabaseRecord(
                            "CurrencyRules",
                            "Pass_Criteria='" . $row[0] . "'");
                
                // delete the currency field from the database
                DeleteDatabaseRecord(
                            "CurrencyFields",
                            "Currency_Field_Name='" . $AircraftType . "_" . $TestType . "_Test" . "'");
                
                // delete the initial checkout currency field from the database
                DeleteDatabaseRecord(
                            "CurrencyFields",
                            "Currency_Field_Name='" . $AircraftType . "_" . $InitialCheckout . "'");
            }
        }
  	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("DeleteOldTypeCurrencyRecords", $sql);
    }
}

//********************************************************************
// DeleteCurrencyField(DeletedFieldName as String)
//
// Purpose:      Delete the given currency field.
//
// Inputs:
//   DeletedFieldName - the name of the field to delete
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function DeleteCurrencyField($DeletedFieldName)
{
    global $CurrencyFieldNames;
    global $CurrencyFieldValues;
    global $debug_flag;
    
    // look for the currency fields and delete it
    $NumberOfEntries = count($CurrencyFieldNames);
    for ($i = 0; $i < $NumberOfEntries; $i++)
    {
        If (UCase($CurrencyFieldNames[$i]) == UCase($DeletedFieldName))
        {
            // record found, delete it
            for ($j = $i; $j < $NumberOfEntries - 1; $j++)
            {
                $CurrencyFieldNames[$j] = $CurrencyFieldNames[$j + 1];
                $CurrencyFieldValues[$j] = $CurrencyFieldValues[$j + 1];
            }
            
            // remove the last element since it is not used now
            unset($CurrencyFieldNames[$NumberOfEntries - 1]);
            unset($CurrencyFieldValues[$NumberOfEntries - 1]);
            break;
        }
    }
}

//********************************************************************
// LoadDBCurrencyFields(UserName, $RulesField)
//
// Purpose:      Load the currency fields from the database. The fields
//               share a memo field and have the format:
//                   Field Name, Field Value;
//               We will load the fields into an array so that they may
//               be accessed for currency rule checking.
//
// Inputs:
//   UserName - member we are interested in
//   RulesField - optional parameter that specifies the rules. if it is
//                not supplied, the rules will be retrieved from the 
//                database
//
// Outputs:
//   none
//
// Returns:
//
// Notes:
//
//*********************************************************************
function LoadDBCurrencyFields($UserName, $RulesField="")
{
    global $CurrencyFieldNames;
    global $CurrencyFieldValues;
    global $debug_flag;
    
    // empty any existing values in the currency arrays
    $CurrencyFieldNames = array();
    $CurrencyFieldValues = array();
   
    // if the rules field is empty, get the rules from the database
    if (empty($RulesField))
    {
        // get the rules field from the database
    	$RulesField = sql_query1(
    								"SELECT Rules_Field " . 
    								"FROM AircraftScheduling_person " .
    								"WHERE username = '$UserName'");
    }
    
    // get the tokens from the fields
    $RulesArray = preg_split("/;/", $RulesField);
    $CurrencyRecordCounter = 0;
	foreach ($RulesArray as $Item)
	{
		$ItemArray = preg_split("/,/", $Item);
		$CurrencyFieldNames[$CurrencyRecordCounter] = $ItemArray[0];
		$CurrencyFieldValues[$CurrencyRecordCounter] = $ItemArray[1];
		$CurrencyRecordCounter++;
	}
}

//********************************************************************
// LookupCurrencyFieldname(FieldName as string) as string
//
// Purpose:      Lookup the value of the currency field name and
//               return the value
//
// Inputs:
//   FieldName - the name of the field to lookup
//
// Outputs:
//   none
//
// Returns:
//   The value from the table as a string
//
// Notes:
//
//*********************************************************************
function LookupCurrencyFieldname($FieldName)
{
    global $CurrencyFieldNames;
    global $CurrencyFieldValues;
    global $NullDate;
    global $debug_flag;

    // search the CurrencyFieldRecords for a match on FieldName
    $FieldValue = "";
    for ($i = 0; $i < count($CurrencyFieldNames); $i++)
    {
        if (RemoveCurrencyFieldQuotes($CurrencyFieldNames[$i]) == RemoveCurrencyFieldQuotes($FieldName))
        {
            // name found, handle special case of True and False
            if (UCase($CurrencyFieldValues[$i]) == "TRUE")
            {
                $FieldValue = "1";
            }
            else if (UCase($CurrencyFieldValues[$i]) == "FALSE")
            {
                $FieldValue = "0";
            }
            else
            {
                $FieldValue = Trim($CurrencyFieldValues[$i]);
            }
            break;
        }
    }
      
    // pass back default value if the field name was not found
    if (strlen($FieldValue) == 0) $FieldValue = $NullDate;

    // return the value
    return $FieldValue;
}

//********************************************************************
// SaveDBCurrencyFields(RuleFields As String)
//
// Purpose:      Save the currency fields into the database. The fields
//               share a memo field and have the format:
//                   Field Name, Field Value;
//               We will save the fields from an array that was
//               accessed for currency rule checking.
//
// Inputs:
//   none
//
// Outputs:
//   RuleFields - the updated currency fields ready to be saved in the
//                database
//
// Returns:
//
// Notes:
//
//*********************************************************************
function SaveDBCurrencyFields(&$RuleFields)
{
    global $CurrencyFieldNames;
    global $CurrencyFieldValues;

    // save the tokens to the rule string
    $RuleFields = "";
    for ($i = 0; $i < count($CurrencyFieldNames); $i++)
    {
        $RuleFields = $RuleFields . $CurrencyFieldNames[$i] . "," .
                        $CurrencyFieldValues[$i] . ";";
    }
    
    // remove the trailing ";" from the rule string
    if (Len($RuleFields) > 0)
    {
        $RuleFields = Left($RuleFields, Len($RuleFields) - 1);
    }
}

//********************************************************************
// PurgeCurrencyFields()
//
// Purpose:      Clear any existing currency fields so that new
//               values can be loaded.
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
// Notes:
//
//*********************************************************************
function PurgeCurrencyFields()
{
    global $CurrencyFieldNames;
    global $CurrencyFieldValues;

    // set the loaded record count to zero
    $CurrencyFieldNames = array();
    $CurrencyFieldValues = array();
}

//********************************************************************
// UpdateCurrencyFieldname(FieldName as string, FieldValue as string)
//
// Purpose:      Set the currency field to the given value.
//
// Inputs:
//   FieldName - the name of the field to update
//   FieldValue - the new value for the field
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function UpdateCurrencyFieldname($FieldName, $FieldValue)
{    
    global $CurrencyFieldNames;
    global $CurrencyFieldValues;
   
    // search the CurrencyFieldRecords for a match on FieldName
    for ($i = 0; $i < count($CurrencyFieldNames); $i++)
    {
        if ($CurrencyFieldNames[$i] == $FieldName)
        {
            // name found, set the new value
            $CurrencyFieldValues[$i] = $FieldValue;
            return;
        }
    }
    
    // value not found, add it to the table
    $CurrencyCounter = count($CurrencyFieldNames);
    $CurrencyFieldNames[$CurrencyCounter] = $FieldName;
    $CurrencyFieldValues[$CurrencyCounter] = $FieldValue;
}

//********************************************************************
// LoadRecentFlightExperience(
//                           UserName,
//                           PilotType,
//                           SummaryOnly,
//                           AllowSelection,
//                           NumberOfDays,
//                           JavascriptHandler)
//
// Purpose:      Load the recent flight information into the form
//
// Inputs:
//   UserName - member we are interested in
//   PilotType - Student, Private, Instrument or Instructor rating
//               for selecting currency information
//   SummaryOnly - set true to display only a summary of recent flight time
//   NumberOfDays - set to > 0 to override the default number of days
//               to display currency for. 
//   AllowSelection - set to tru to build a control to allow
//               the user to select the number of days.
//   JavascriptHandler - set to the name of the onchange function. only used
//               when AllowSelection is enabled.
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function LoadRecentFlightExperience(
                                    $UserName, 
                                    $PilotType, 
                                    $SummaryOnly,
                                    $AllowSelection,
                                    $NumberOfDays,
                                    $JavascriptHandler)
{
    global $debug_flag;
    global $goback, $GoBackParameters;

    // select the number of days for the recent flight experience
    // set the sim and PCATD flags to log the time based on the level of the pilot
    // all pilots can log sim time for instrument time
    // only non-instrument pilots can log PCATD instrument time
    // no pilots can log takeoff, landings, or 60/90 day currency time in the sim or PCATD
    switch ($PilotType)
    {
    Case "Student":
        $CurrencyString = "Last 60 days flight experience";
        $CurrencyDays = 60;
        $UseSim = True;
        $UsePCATD = True;
        break;
    Case "Private Under 200":
        $CurrencyString = "Last 60 days flight experience";
        $CurrencyDays = 60;
        $UseSim = True;
        $UsePCATD = True;
        break;
    Case "Private Over 200":
        $CurrencyString = "Last 90 days flight experience";
        $CurrencyDays = 90;
        $UseSim = True;
        $UsePCATD = True;
        break;
    Case "Instrument":
        $CurrencyString = "Last 6 months flight experience";
        $CurrencyDays = 182;
        $UseSim = True;
        $UsePCATD = False;
        break;
    Case "CFI":
        $CurrencyString = "Last 6 months flight experience";
        $CurrencyDays = 182;
        $UseSim = True;
        $UsePCATD = False;
        break;
    }
    
    // if the caller has requested, override the default CurrencyDays
    if ($NumberOfDays > 0)
    {
        // override the default currency days
        $CurrencyDays = $NumberOfDays;
    }
    
    // get the keycode from the database record
    $CurrentUserKeycode = $UserName;
    if ($debug_flag)
        echo "DEBUG: CurrentUserKeycode: $CurrentUserKeycode<BR>";
    
    // build the query
    $FlightQuery = "SELECT 
                            Date,
                            model_id,
                            Day_Time,
                            Night_Time,
                            Day_Landings,
                            Night_Landings,
                            Navigation_Intercepts,
                            Holding_Procedures,
                            Instrument_Approach,
                            Aircraft
                    FROM Flight WHERE (Keycode = '" . Trim($CurrentUserKeycode) . "'" . 
                " AND ";
    
    // exclude the simulator time if requested
    If (!$UseSim)
    {
        $FlightQuery = $FlightQuery . "model_id <> " . LookupModelID("SIM") .
                    " AND ";
    }
    
    // exclude the PCATD time if requested
    If (!$UsePCATD)
    {
        $FlightQuery = $FlightQuery . "model_id <> " . LookupModelID("PCATD") .
                    " AND ";
    }
    
    // complete the query
    $FlightQuery = $FlightQuery . "Date >= '" . 
                FormatField(DateSerial(Year("Now"), Month("Now"), Day("Now") - $CurrencyDays), "DatabaseDate") . "'" .
                ")";
    
    // add the totals of the recent flight experience
    $TotalDayTime = 0;
    $TotalNightTime = 0;
    $TotalDayLandings = 0;
    $TotalNightLandings = 0;
    $TotalNavInter = 0;
    $TotalHolds = 0;
    $TotalInstrumentApproaches = 0;

    // get the recent flight experience
	$SQLResult = sql_query($FlightQuery);
	
    
    // are we allowing the currency days to be selected
    if ($AllowSelection)
    {
        // build a control to allow the user to select the number of days
        // displayed
        echo "<b>Display flight experience for </b>";
		echo "<SELECT NAME='NumberOfDays' id='NumberOfDays' onChange='$JavascriptHandler()'>";
		
		// build the selection entries
    	echo "<OPTION " .
    			"VALUE='60'" . 
    			("60" == $CurrencyDays ? " SELECTED" : "") . 
    			">60 Days";
    	echo "<OPTION " .
    			"VALUE='90'" . 
    			("90" == $CurrencyDays ? " SELECTED" : "") . 
    			">90 Days";
    	echo "<OPTION " .
    			"VALUE='120'" . 
    			("120" == $CurrencyDays ? " SELECTED" : "") . 
    			">120 Days";
    	echo "<OPTION " .
    			"VALUE='182'" . 
    			("182" == $CurrencyDays ? " SELECTED" : "") . 
    			">6 Months";
    	echo "<OPTION " .
    			"VALUE='365'" . 
    			("365" == $CurrencyDays ? " SELECTED" : "") . 
    			">1 Year";
    	echo "<OPTION " .
    			"VALUE='36500'" . 
    			("36500" == $CurrencyDays ? " SELECTED" : "") . 
    			">All";       // not really all, but a 100 years is close enough
        
        // finish the control
		echo "</SELECT>";	  			
    }
    else
    {
    	// put in the title
    	echo "<H4>" . $CurrencyString . "</H4>";
	}
	
	// display the recent flight experience
	if ($SQLResult)
	{
	    // valid SQL, display the information
        echo "<TABLE bgcolor='#ffffed' border=1>";
        if ($SummaryOnly) 
        {
            echo "<TH> </TH>";
        }
        else
        {
            echo "<TH>Date</TH>";
            echo "<TH>Aircraft</TH>";
            echo "<TH>Type</TH>";
        }
        echo "<TH>Day<BR>Time</TH>";
        echo "<TH>Night<BR>Time</TH>";
        echo "<TH>Day<BR>Lnds</TH>";
        echo "<TH>Night<BR>Lnds</TH>";
        echo "<TH>Nav<BR>Inter</TH>";
        echo "<TH>Holds</TH>";
        echo "<TH>Instr<BR>Apps</TH>";
		for ($i = 0; ($row = sql_row($SQLResult, $i)); $i++) 
		{
		    // display the information if the user requested details
		    if (!$SummaryOnly)
		    {
    		    echo "<TR>";
    		    echo "<TD class=CR>" . strftime("%d-%b-%y", strtotime($row[0])) . "</TD>";
    		    echo "<TD class=CR>" . $row[9] . "</TD>";
    		    echo "<TD class=CR>" . LookupAircraftType($row[1]) . "</TD>";
    		    echo "<TD class=CR>" . number_format($row[2], 1) . "</TD>";
    		    echo "<TD class=CR>" . number_format($row[3], 1) . "</TD>";
    		    echo "<TD class=CR>" . number_format($row[4], 0) . "</TD>";
    		    echo "<TD class=CR>" . number_format($row[5], 0) . "</TD>";
    		    echo "<TD class=CR>" . number_format($row[6], 0) . "</TD>";
    		    echo "<TD class=CR>" . number_format($row[7], 0) . "</TD>";
    		    echo "<TD class=CR>" . number_format($row[8], 0) . "</TD>";
    		    echo "</TR>";
    		}
		    
		    // save the totals for completing the table
            $TotalDayTime = $TotalDayTime + $row[2];
            $TotalNightTime = $TotalNightTime + $row[3];
            $TotalDayLandings = $TotalDayLandings + $row[4];
            $TotalNightLandings = $TotalNightLandings + $row[5];
            $TotalNavInter = $TotalNavInter + $row[6];
            $TotalHolds = $TotalHolds + $row[7];
            $TotalInstrumentApproaches = $TotalInstrumentApproaches + $row[8];
		}
        
        // put the totals into the screen
	    echo "<TR>";
		if ($SummaryOnly)
	        echo "<TD colspan='1' class=CL>Totals</TD>";
	    else
	        echo "<TD colspan='3' class=CL>Totals</TD>";
	    echo "<TD class=CR>" . number_format($TotalDayTime, 1) . "</TD>";
	    echo "<TD class=CR>" . number_format($TotalNightTime, 1) . "</TD>";
	    echo "<TD class=CR>" . number_format($TotalDayLandings, 0) . "</TD>";
	    echo "<TD class=CR>" . number_format($TotalNightLandings, 0) . "</TD>";
	    echo "<TD class=CR>" . number_format($TotalNavInter, 0) . "</TD>";
	    echo "<TD class=CR>" . number_format($TotalHolds, 0) . "</TD>";
	    echo "<TD class=CR>" . number_format($TotalInstrumentApproaches, 0) . "</TD>";
	    echo "</TR>";

        echo "</TABLE>";
	}
	else
	{
	    // sql error, tell the user
	    fatal_error(0, sql_error());
	}
}

//********************************************************************
// LoadCurrencyValues(
//                    UserName As String,
//                    PilotType as String,
//                    CurrentlySelectedAircraftType as string,
//                    CurrentlySelectedAircraftID as string,
//                    FlightStatus,
//                    FlightStatusReason)
//
// Purpose:      Load the currency information into the form
//
// Inputs:
//   UserName - member we are interested in
//   PilotType - Student, Private, Instrument or Instructor rating
//               for selecting currency information
//   CurrentlySelectedAircraftType - the current aircraft type
//   CurrentlySelectedAircraftID - the tail number of the aircraft
//
// Outputs:
//   FlightStatus - the flight status value:
//                       DoesntApply - rule doesn't apply in this case
//                       ClearedToFly - rule passes
//                       ClearedToFlyDayOnly - night rule fails, allow VFR day flight
//                       ClearedToFlyNoInstruments - rule fails, allow VFR but not instruments
//                       NotClearedToFly - rule failed, no overrides allowed
//                       NotClearedToFlyOverride - rule failed, overrides allowed
//                       InformationOnly - rule is for information only
//   FlightStatusReason - reason not cleared for flight (if needed)
//
// Returns:
//   none
//*********************************************************************
function LoadCurrencyValues(
                            $UserName, 
                            $PilotType,
                            $CurrentlySelectedAircraftType,
                            $CurrentlySelectedAircraftID, 
                            &$FlightStatus, 
                            &$FlightStatusReason)

{                           
    global $DoesntApply, $ClearedToFly, $ClearedToFlyDayOnly;
    global $ClearedToFlyNoInstruments, $NotClearedToFly, $NotClearedToFlyOverride;
    global $InformationOnly;
    global $debug_flag;

    // initialize the arrays
    $CurrencyRuleString = array();
    $CurrencyStatusString = array();
    
    // get the keycode from the database record
    $CurrentUserKeycode = $UserName;
    
    // load the information from the database and compute the currency
    GetCurrencyValues(
                        $PilotType,
                        $CurrentUserKeycode,
                        $CurrencyRuleString,
                        $CurrencyStatusString,
                        $PilotIdentificationString,
                        $FlightStatus,
                        $FlightStatusReason,
                        $CurrentlySelectedAircraftType,
                        $CurrentlySelectedAircraftID);
    
    // put the pilot identification information on the screen
	echo "<H4>Expiration Dates:</H4>";
    echo "<B>$PilotIdentificationString</B><BR>";
    
    // get the currency field types from the database
    $CurrencyFieldFormat = array();
    for ($i = 0; $i < count($CurrencyRuleString); $i++)
    {
        // lookup the field type
        $CurrencyFieldFormat[$i] = sql_query1(
                        "SELECT Currency_Field_Type " .
                        "FROM CurrencyFields " .
                        "WHERE Currency_Field_Name = '" .
                        Replace($CurrencyRuleString[$i], " ", "_") .
                        "'");
        if ($CurrencyFieldFormat[$i] == -1) $CurrencyFieldFormat[$i] = "Date";                        
    }
    
    // put the pilot currency information on the screen
    echo "<TABLE bgcolor='#ffffed' border=0>";
    DisplayColumns(
                    count($CurrencyRuleString), 
                    2, 
                    $CurrencyRuleString, 
                    $CurrencyStatusString,
                    false,
                    $CurrencyFieldFormat); 
    echo "</TABLE>";
}

?>
