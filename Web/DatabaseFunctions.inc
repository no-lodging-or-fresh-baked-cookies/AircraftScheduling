<?php
//-----------------------------------------------------------------------------
// 
// DatabaseFunctions
// 
// PURPOSE: Contains general functions to interface with the database.
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
require_once("DateFunctions.inc");

// structure to hold the information used to update a database record
class DatabaseFieldType
{
    var $FieldName;         // field name in the database record
    var $Value;             // value of the type to assign
    var $DataType;          // data type of field
}

// type of recordset conversions
$RSConversionNone   = 1;    // no conversion of the data
$RSConversionString = 2;    // data is a string
$RSConversionNumber = 3;    // data is a number
$RSConversionDate   = 4;    // data is a date

// aircraft status strings
$CheckedOutString = "In Use";
$CheckInProgressString = "Checking In";
$CheckOutProgressString = "Checking Out";
$OnLineString = "On Line";
$OffLineString = "Grounded";

// type of flight instruction
$FreeInstruction = "Free";             // free instruction description string
$NoInstruction = "None";               // no instruction description string
$GroundInstruction = "Ground" ;        // ground instruction description string
$PrivateInstruction = "Private";       // private instruction description string
$InstrumentInstruction = "Instrument"; // instrument instruction description string
$CommercialInstruction = "Commercial"; // commercial instruction description string
$CFIInstruction = "CFI";               // CFI instruction description string
$CFIIInstruction = "CFII";             // CFII instruction description string

// number of retry counts if the server doesn't respond
$MaxServerRetryCount = 3;

// array to hold inventory information so we don't have to reload it
// everytime we need it
$PartNumberString = "";
$CurrentSelectedPartNumber = "";
$DescriptionString = "";
$CurrentSelectedDescription = "";
    
//********************************************************************
// CheckForNullBoolean(Value As Variant) As String
//
// Purpose:  Check the input value for a null and return " " if so
//           otherwise return the string value of the boolean.
//
// Inputs:
//   Value - value to check
//
// Outputs:
//   none
//
// Returns:
//   The string if not null, " " otherwise.
//*********************************************************************
function CheckForNullBoolean($Value)
{
    // check for a null value
    if (is_null($Value))
    {
        $CheckForNullBoolean = " ";
    }
    else
    {
        if ($Value)
            $CheckForNullBoolean = "Yes";
        else
           $CheckForNullBoolean = "No";
    }
    return $CheckForNullBoolean;
}
    
//********************************************************************
// CheckForNullNumber(Value As Variant) As String
//
// Purpose:  Check the input value for a null and return " " if so
//           otherwise return the string value of the number.
//
// Inputs:
//   Value - value to check
//
// Outputs:
//   none
//
// Returns:
//   The string if not null, " " otherwise.
//*********************************************************************
function CheckForNullNumber($Value)
{
    // check for a null value
    if (is_null($Value))
        $CheckForNullNumber = "0";
    else
        $CheckForNullNumber = (string) $Value;
    return $CheckForNullNumber;
}
    
//********************************************************************
// CheckForNullStr(Value As Variant) As String
//
// Purpose:  Check the input value for a null and return " " if so
//           otherwise return the string value.
//
// Inputs:
//   Value - value to check
//
// Outputs:
//   none
//
// Returns:
//   The string if not null, " " otherwise.
//*********************************************************************
function CheckForNullStr($Value)
{
    // check for a null value
    if (is_null($Value))
        $CheckForNullStr = " ";
    Else
        $CheckForNullStr = trim($Value);
    return $CheckForNullStr;
}

//********************************************************************
// MySQLExecuteCommand(QueryString As String)
//
// Purpose:  Open a recordset from the MySQL database.
//
// Inputs:
//   QueryString - the query string for the recordset
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function MySQLExecuteCommand($QueryString)
{            
    global $debug_flag;
	global $MaxServerRetryCount;
    
    // if debug is enabled, show the query without executing it
    if ($debug_flag == 1)
    {
        // debug enabled
	    echo "<BR>DEBUG QueryString: $QueryString<BR>";
        exit();
    }

    // try the requested number of times to execute the command
    $ServerRetryCount = 0;
    while ($ServerRetryCount <= $MaxServerRetryCount)
    {
        // execute the requested query
        $affected = sql_command($QueryString);
	    if($affected < 0)
	    {
            // command failed to execute, increment the counter
            $ServerRetryCount = $ServerRetryCount + 1;
	    }
	    else
	    {
            // command executed, return affected rows to the caller
            return $affected;
	    }
    }
    
    // if we are here, it is because the server failed to respond
	print_header(0, 0, 0, 0, 0, 0, "");
	echo "<H2>Error! Connection to MySQL server lost. Unable to recover, exiting.</H2>";
	echo "<BR> sql error: " . sql_error();
	echo "<BR>DEBUG QueryString: $QueryString<BR>";
	include "trailer.inc";
	exit();
}

//********************************************************************
// UpdateDatabaseRecord(
//                    DatabaseTable As String,
//                    DatabaseFields() As DatabaseFieldType,
//                    Condition As String)
//
// Purpose:  Build the SQL statement to update the given data in the
//           database and update the record.
//
// Inputs:
//   DatabaseTable - name of the table within the database to update
//   DatabaseFields - fieldnames and values to update within the record.
//   Condition - selector for the database records to update
//
// Outputs:
//   none
//
// Returns:
//   number of rows affected by the update
//*********************************************************************
function UpdateDatabaseRecord(
                    $DatabaseTable, 
                    $DatabaseFields, 
                    $Condition)
{                    
    global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;

    // put UPDATE command in the SQL statement
    $SQLStatement = "UPDATE " . $DatabaseTable;
    
    // build the fields to update
    $SQLStatement = $SQLStatement . " SET ";
    
    for ($FieldCounter = 0; $FieldCounter < count($DatabaseFields); $FieldCounter++)
    {
        // put the fieldname in the SQL statement
        $SQLStatement = $SQLStatement . $DatabaseFields[$FieldCounter]->FieldName . "=";
        
        // put the value in the SQL statement
        switch ($DatabaseFields[$FieldCounter]->DataType)
        {
            case $RSConversionNone:           // no conversion of the data
                $SQLStatement = $SQLStatement . $DatabaseFields[$FieldCounter]->Value;
                break;
            case $RSConversionString:         // data is a string
                $SQLStatement = $SQLStatement . "'" . CheckForNullStr($DatabaseFields[$FieldCounter]->Value) . "'";
                break;
            case $RSConversionNumber:         // data is a number
                $SQLStatement = $SQLStatement . CheckForNullNumber($DatabaseFields[$FieldCounter]->Value);
                break;
            case $RSConversionDate:           // data is a date
                $SQLStatement = $SQLStatement . "'" . 
                        FormatField($DatabaseFields[$FieldCounter]->Value, "DatabaseDate") . 
                        "'";
                break;
            default:
                $SQLStatement = $SQLStatement . Str($DatabaseFields[FieldCounter]->Value);
                break;
        }
        
        // if it is not the last field, add a comma
        if ($FieldCounter != count($DatabaseFields) - 1) $SQLStatement = $SQLStatement . ", ";
    }

    // add the WHERE condition
    $SQLStatement = $SQLStatement . " WHERE " . $Condition;
    
    // update the record and return affected rows count
    return MySQLExecuteCommand($SQLStatement);
}

//********************************************************************
// AddDatabaseRecord(
//                    DatabaseTable As String,
//                    DatabaseFields() As DatabaseFieldType)
//
// Purpose:  Build the SQL statement to add the given data to the
//           database and update the record.
//
// Inputs:
//   DatabaseTable - name of the table within the database to update
//   DatabaseFields - fieldnames and values to update within the record.
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function AddDatabaseRecord(
                    $DatabaseTable, 
                    $DatabaseFields)
{                    
    global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;

    // put INSERT command in the SQL statement
    $SQLStatement = "INSERT INTO " . $DatabaseTable;

    // build the fields to update
    $SQLStatement = $SQLStatement . " SET ";
    for ($FieldCounter = 0; $FieldCounter < count($DatabaseFields); $FieldCounter++)
    {
        // put the fieldname in the SQL statement
        $SQLStatement = $SQLStatement . $DatabaseFields[$FieldCounter]->FieldName . "=";
        
        // put the value in the SQL statement
        switch ($DatabaseFields[$FieldCounter]->DataType)
        {
            case $RSConversionNone:           // no conversion of the data
                $SQLStatement = $SQLStatement . $DatabaseFields[$FieldCounter]->Value;
                break;
            case $RSConversionString:         // data is a string
                $SQLStatement = $SQLStatement . "'" . CheckForNullStr($DatabaseFields[$FieldCounter]->Value) . "'";
                break;
            case $RSConversionNumber:         // data is a number
                $SQLStatement = $SQLStatement . CheckForNullNumber($DatabaseFields[$FieldCounter]->Value);
                break;
            case $RSConversionDate:           // data is a date
                $SQLStatement = $SQLStatement . "'" . 
                        FormatField($DatabaseFields[$FieldCounter]->Value, "DatabaseDate") .
                        "'";
                break;
            default:
                $SQLStatement = $SQLStatement . Str($DatabaseFields[$FieldCounter]->Value);
                break;
        }
        
        // if it is not the last field, add a comma
        if ($FieldCounter != count($DatabaseFields) - 1) $SQLStatement = $SQLStatement . ", ";
    }
    
    // add the record
    MySQLExecuteCommand($SQLStatement);
}

//********************************************************************
// DeleteDatabaseRecord(
//                    DatabaseTable As String,
//                    Condition As String)
//
// Purpose:  Build the SQL statement to delete the given data in the
//           database and delete the record.
//
// Inputs:
//   DatabaseTable - name of the table within the database to update
//   Condition - selector for the database records to update
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function DeleteDatabaseRecord(
                    $DatabaseTable,
                    $Condition)
{                    
    // put DELETE command in the SQL statement
    $SQLStatement = "DELETE FROM " . $DatabaseTable;

    // add the WHERE condition
    $SQLStatement = $SQLStatement . " WHERE " . $Condition;
    
    // delete the record
    MySQLExecuteCommand($SQLStatement);
}
 
//********************************************************************
// SetDatabaseRecord(
//                    FieldName As String,
//                    Value As Variant,
//                    DataType As RSConversionType,
//                    DatabaseFields As DatabaseFieldType)
//
// Purpose:  Build the DatabaseFieldType record that will be used
//           for updating the database by calling UpdateDatabaseRecord.
//
// Inputs:
//    FieldName - field name in the database record
//    Value - value of the type to assign
//    DataType - data type of field
//
// Outputs:
//   DatabaseFields - updated entry.
//
// Returns:
//   none
//*********************************************************************
function SetDatabaseRecord(
                    $FieldName,
                    $Value,
                    $DataType,
                    &$DatabaseFields)
{
    global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
                    
    $DatabaseFields = new StdClass();
	$DatabaseFields->FieldName = "`$FieldName`";
    $DatabaseFields->DataType = $DataType;
    switch ($DataType)
    {
        case $RSConversionNone:            // no conversion of the data
            $DatabaseFields->Value = $Value;
            break;
        case $RSConversionString:          // data is a string
            $DatabaseFields->Value = AddEscapes($Value);
            break;
        case $RSConversionNumber:          // data is a number
            $DatabaseFields->Value = Val(CheckForNullNumber($Value));
            break;
        case $RSConversionDate:            // data is a date
            $DatabaseFields->Value = $Value;
            break;
        default:
            $DatabaseFields->Value = $Value;
            break;
    }
}

//********************************************************************
// LookupAircraftType(ModelID As integer) As String
//
// Purpose:  Convert the aircraft model id into a aircraft type
//
// Inputs:
//   ModelID - the aircraft model id to look up
//
// Outputs:
//   none
//
// Returns:
//   The aircraft type string
//
// Notes:
//
//*********************************************************************
function LookupAircraftType($ModelID)
{
    global $debug_flag;

    // lookup the aircraft type (model)
	$AircraftType = sql_query1("
						SELECT model 
						FROM AircraftScheduling_model
						WHERE model_id = '$ModelID'
						");
    return $AircraftType;								
}

//********************************************************************
// LookupModelID(AircraftType As String) As Integer
//
// Purpose:  Convert the aircraft type into a aircraft model id
//
// Inputs:
//   AircraftType - the aircraft type string id to look up
//
// Outputs:
//   none
//
// Returns:
//   The aircraft model id
//
// Notes:
//
//*********************************************************************
Function LookupModelID($AircraftType)
{   
    global $debug_flag;

    // lookup the model id
	$ModelID = sql_query1("
						SELECT model_id 
						FROM AircraftScheduling_model
						WHERE model = '$AircraftType'
						");
    return $ModelID;								
}


//********************************************************************
// LookupAircraftMake(MakeID As integer) As String
//
// Purpose:  Convert the aircraft make id into a aircraft type
//
// Inputs:
//   MakeID - the aircraft make id to look up
//
// Outputs:
//   none
//
// Returns:
//   The aircraft type string
//
// Notes:
//
//*********************************************************************
function LookupAircraftMake($MakeID)
{
    global $debug_flag;

    // lookup the make
	$Make = sql_query1(
						"SELECT make " .
						"FROM AircraftScheduling_make " .
						"WHERE make_id = $MakeID");
    return $Make;								
}

//********************************************************************
// LookupMakeID(AircraftMake As String) As Integer
//
// Purpose:  Convert the aircraft type into a aircraft make id
//
// Inputs:
//   AircraftMake - the aircraft type string id to look up
//
// Outputs:
//   none
//
// Returns:
//   The aircraft Make id
//
// Notes:
//
//*********************************************************************
Function LookupMakeID($AircraftMake)
{
    // define a record set for getting aircraft make information
	$LookupMakeID = sql_query1(
	                    "SELECT make_id FROM AircraftScheduling_make " .
                        "WHERE make='" . $AircraftMake . "'");
    
    // see if we found a valid aircraft type
    if ($LookupMakeID < 0)
    {
        // invalid aircraft
        $LookupMakeID = 0;
    }
    
    // return the results
    return $LookupMakeID;
}

//********************************************************************
// LookupAircraftEquipmentCodeString(EquipmentCode As integer) As String
//
// Purpose:  Convert the aircraft equipment code into a string
//
// Inputs:
//   EquipmentCode - the equipment code to look up
//
// Outputs:
//   none
//
// Returns:
//   The aircraft equipment code string
//
// Notes:
//
//*********************************************************************
Function LookupAircraftEquipmentCodeString($EquipmentCode)
{                            
    // define a record set for getting aircraft equipment code information
	$LookupAircraftEquipmentCodeString = sql_query1(
	                    "SELECT code FROM AircraftScheduling_equipment_codes WHERE code_id=" .
                        Str($EquipmentCode));
    
    // see if we found a valid aircraft model ID
    if ($LookupAircraftEquipmentCodeString < 0)
    {
        // invalid equipment code
        $LookupAircraftEquipmentCodeString = "Unknown";
    }
    
    // return the results
    return $LookupAircraftEquipmentCodeString;
}

//********************************************************************
// LookupAircraftEquipmentCode(EquipmentCode As String) As Integer
//
// Purpose:  Convert the aircraft equipment code string into a number
//
// Inputs:
//   EquipmentCode - the equipment code to look up
//
// Outputs:
//   none
//
// Returns:
//   The aircraft equipment code number
//
// Notes:
//
//*********************************************************************
function LookupAircraftEquipmentCode($EquipmentCode)
{
	$LookupAircraftEquipmentCode = sql_query1(
	                    "SELECT code_id FROM AircraftScheduling_equipment_codes WHERE code='" .
                        $EquipmentCode . "'");
    
    // see if we found a valid aircraft model ID
    if ($LookupAircraftEquipmentCode < 0)
    {
        // invalid equipment code
        $LookupAircraftEquipmentCode = 1;
    }
    
    // return the results
    return $LookupAircraftEquipmentCode;
}

//********************************************************************
// LookupAircraftStatusString(StatusID As integer) As String
//
// Purpose:  Convert the aircraft status into a status string
//
// Inputs:
//   StatusID - the status id to look up
//
// Outputs:
//   none
//
// Returns:
//   The aircraft status string
//
// Notes:
//
//*********************************************************************
function LookupAircraftStatusString($StatusID)
{
    global $CheckedOutString, $CheckInProgressString;
    global $CheckOutProgressString, $OnLineString, $OffLineString;

    switch ($StatusID)
    {
    case 0:
        // aircraft is offline
        $LookupAircraftStatusString = $OffLineString;
        break;
    case 1:
        // aircraft is online
        $LookupAircraftStatusString = $OnLineString;
        break;
    case 2:
        // aircraft is checked out
        $LookupAircraftStatusString = $CheckedOutString;
        break;
    case 3:
        // aircraft is checking in
        $LookupAircraftStatusString = $CheckInProgressString;
        break;
    case 4:
        // aircraft is checking out
        $LookupAircraftStatusString = $CheckOutProgressString;
        break;
    default:
        // aircraft status is unknown
        $LookupAircraftStatusString = "Unknown";
        break;
    }
    return $LookupAircraftStatusString;
}

//********************************************************************
// LookupAircraftStatus(StatusString As String) As Integer
//
// Purpose:  Convert the aircraft status into a status id
//
// Inputs:
//   StatusString - The aircraft status string to look up.
//
// Outputs:
//   none
//
// Returns:
//   The aircraft status ID
//
// Notes:
//
//*********************************************************************
function LookupAircraftStatus($StatusString)
{
    global $CheckedOutString, $CheckInProgressString;
    global $CheckOutProgressString, $OnLineString, $OffLineString;

    switch ($StatusString)
    {
    case $OffLineString:
        // aircraft is offline
        $LookupAircraftStatus = 0;
        break;
    case $OnLineString:
        // aircraft is online
        $LookupAircraftStatus = 1;
        break;
    case $CheckedOutString:
        // aircraft is checked out
        $LookupAircraftStatus = 2;
        break;
    case $CheckInProgressString:
        // aircraft is checking in
        $LookupAircraftStatus = 3;
        break;
    case $CheckOutProgressString:
        // aircraft is checking out
        $LookupAircraftStatus = 4;
        break;
    default:
        // aircraft status is unknown
        $LookupAircraftStatus = 0;
        break;
    }
    return $LookupAircraftStatus;
}

//********************************************************************
// GetGeneralPreferenceValue(PreferenceFieldName As String) As Variant
//
// Purpose:  Return the value for the given preference field.
//
// Inputs:
//   PreferenceFieldName - field name of the preference value to retreive
//
// Outputs:
//   none
//
// Returns:
//   The value of the preference field
//*********************************************************************
function GetGeneralPreferenceValue($PreferenceFieldName)
{
    // retreive the value from the database
	$GetGeneralPreferenceValue = sql_query1(
	    "SELECT value FROM AircraftScheduling_config WHERE name='$PreferenceFieldName'"); 

    // return the results
    return $GetGeneralPreferenceValue;
}

//********************************************************************
// GetServerPreferenceValue(PreferenceFieldName As String) As Variant
//
// Purpose:  Return the value for the given preference field.
//
// Inputs:
//   PreferenceFieldName - field name of the preference value to retreive
//
// Outputs:
//   none
//
// Returns:
//   The value of the preference field
//*********************************************************************
function GetServerPreferenceValue($PreferenceFieldName)
{
    // retreive the value from the database
	$GetServerPreferenceValue = sql_query1(
	    "SELECT value FROM AircraftScheduling_config WHERE name='$PreferenceFieldName'"); 

    // return the results
    return $GetServerPreferenceValue;
}

//********************************************************************
// GetInstructionRate(
//                   InstructionType As String)
//                   As Double
//
// Purpose:  Determine the instruction charge for the type of instruction
//           given and return the value
//
// Inputs:
//   InstructionType - the type of instuction
//
// Outputs:
//   none
//
// Returns:
//   The rate of instruction
//
// Notes:
//
//*********************************************************************
Function GetInstructionRate($InstructionType)
{
    global $FreeInstruction, $NoInstruction, $GroundInstruction;
    global $PrivateInstruction, $InstrumentInstruction;
    global $CommercialInstruction, $CFIInstruction, $CFIIInstruction;

    // determine the type of instruction given and return the
    // cost of that instruction
    if ($InstructionType == $FreeInstruction)
        // free, no cost
        $GetInstructionRate = 0;
    elseif ($InstructionType == $NoInstruction)
        // no instruction, no cost
        $GetInstructionRate = 0;
    elseif ($InstructionType == $GroundInstruction)
        // ground instruction, get the rate from the preferences
        $GetInstructionRate = GetGeneralPreferenceValue("Ground_Instruction_Amount");
    elseif ($InstructionType == $PrivateInstruction)
        // Private instruction, get the rate from the preferences
        $GetInstructionRate = GetGeneralPreferenceValue("Private_Instruction_Amount");
    elseif ($InstructionType == $InstrumentInstruction)
        // Instrument instruction, get the rate from the preferences
        $GetInstructionRate = GetGeneralPreferenceValue("Instrument_Instruction_Amount");
    elseif ($InstructionType == $CommercialInstruction)
        // commercial instruction, get the rate from the preferences
        $GetInstructionRate = GetGeneralPreferenceValue("Commercial_Instruction_Amount");
    elseif ($InstructionType == $CFIInstruction)
        // CFI instruction, get the rate from the preferences
        $GetInstructionRate = GetGeneralPreferenceValue("CFI_Instruction_Amount");
    elseif ($InstructionType == $CFIIInstruction)
        // CFII instruction, get the rate from the preferences
        $GetInstructionRate = GetGeneralPreferenceValue("CFII_Instruction_Amount");
    else
        // invalid case, shouldn't be here
        $GetInstructionRate = 0;
    
    // return the results
    return $GetInstructionRate;
}

//********************************************************************
// GetInstructorCreditRate(
//                   InstructorKeycode as string,
//                   InstructionType As String)
//                   As Double
//
// Purpose:  Determine the instructor rate for the type of instruction
//           given and return the value
//
// Inputs:
//   InstructorKeycode - keycode for the instructor
//   InstructionType - the type of instruction to look for the rate
//
// Outputs:
//   none
//
// Returns:
//   The credit rate for this instructor
//
// Notes:
//
//*********************************************************************
function GetInstructorCreditRate($InstructorKeyCode, $InstructionType)
{
    global $FreeInstruction, $NoInstruction, $GroundInstruction;
    global $PrivateInstruction, $InstrumentInstruction;
    global $CommercialInstruction, $CFIInstruction, $CFIIInstruction;

    
    // get the instructor credit rates from the database
    $sql = "SELECT " .
           "    Ground_Instruction_Amount, " .
           "    Private_Instruction_Amount, " .
           "    Instrument_Instruction_Amount, " .
           "    Commercial_Instruction_Amount, " .
           "     CFI_Instruction_Amount, " .
           "     CFII_Instruction_Amount " .
    	   "FROM AircraftScheduling_person " . 
    	   "WHERE username='" . UCase(Trim($InstructorKeyCode)) . "'";    
    $res = sql_query($sql);
    
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    { 
        // if it is not found, there is an error in the database (only if someone
        // else changed it, the program verifies the keycode before the write)
        // so we will do the best we can
        if (sql_count($res) > 0)
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);
    
            // record found, get the credit rates
            if ($InstructionType == $FreeInstruction)
                $GetInstructorCreditRate = 0;
            elseif ($InstructionType == $NoInstruction)
                $GetInstructorCreditRate = 0;
            elseif ($InstructionType == $GroundInstruction)
                $GetInstructorCreditRate = Val(CheckForNullNumber($row[0]));
            elseif ($InstructionType == $PrivateInstruction)
                $GetInstructorCreditRate = Val(CheckForNullNumber($row[1]));
            elseif ($InstructionType == $InstrumentInstruction)
                $GetInstructorCreditRate = Val(CheckForNullNumber($row[2]));
            elseif ($InstructionType == $CommercialInstruction)
                $GetInstructorCreditRate = Val(CheckForNullNumber($row[3]));
            elseif ($InstructionType == $CFIInstruction)
                $GetInstructorCreditRate = Val(CheckForNullNumber($row[4]));
            elseif ($InstructionType == $CFIIInstruction)
                $GetInstructorCreditRate = Val(CheckForNullNumber($row[5]));
            Else
                // invalid case, shouldn't be here
                $GetInstructorCreditRate = 0;
        }
        else
        {
            // error case, shouldn't be here
            $GetInstructorCreditRate = 0;
        }
    }
    else
    {
        // error processing database request, tell the user
        DisplayDatabaseError("GetInstructorCreditRate", $sql);
        $GetInstructorCreditRate = 0;
    }
    
    // return the results
    return $GetInstructorCreditRate;
}

//********************************************************************
// AdjustInventoryItem(InventoryItem as String, AdjustQuantity as Integer)
//
// Purpose:  Adjust the value for the inventory item in the database.
//
// Inputs:
//   InventoryItem - the inventory item to change the quantity for
//   AdjustQuantity - the number of items to add (postive) or
//               subtract (negative) from the inventory
//
// Outputs:
//   none
//
// Returns:
//   The total charges for the month
//*********************************************************************
function AdjustInventoryItem($InventoryItem, $AdjustQuantity)
{
    // Use atomic SQL UPDATE to avoid read-then-write race condition.
    // The arithmetic is done server-side in a single statement.
    $AdjustQuantity = (int)$AdjustQuantity;
    $sql = "UPDATE Inventory SET Quantity_In_Stock = Quantity_In_Stock + ($AdjustQuantity) " .
           "WHERE Part_Number='" . addescapes($InventoryItem) . "'";
    if (sql_command($sql) < 0)
    {
        // error processing database request, tell the user
        DisplayDatabaseError("AdjustInventoryItem", $sql);
    }
}

//********************************************************************
// GetInventoryItem(
//                   PartNumber As String,
//                   Description as String,
//                   RetailPrice as Double,
//                   WholesalePrice as Double,
//                   Category as String,
//                   UnitPrice As Double,
//                   Position As String,
//                   QuantityInStock As Integer)
//
// Purpose:  Get the inventory information from the database given
//           the part number.
//
// Inputs:
//   PartNumber - the part number to look up.
//
// Outputs:
//   Description - the description that corresponds to the part number.
//   RetailPrice - the retail price that corresponds to the part number.
//   WholesalePrice - the wholesale price that corresponds to the part number.
//   Category - the category that corresponds to the part number
//   UnitPrice - the unit price for the part number
//   Position - the inventory position of the part number
//   QuantityInStock - current quantity of the item in stock
//
// Returns:
//   none
//*********************************************************************
function GetInventoryItem( 
                   $PartNumber, 
                   &$Description, 
                   &$RetailPrice, 
                   &$WholesalePrice, 
                   &$Category, 
                   &$UnitPrice, 
                   &$Position, 
                   &$QuantityInStock)
{                   
    // open a recordset for the inventory items
    $sql = "SELECT
                Description,
                Retail_Price,
                Unit_Price,
                Category,
                Position,
                Quantity_In_Stock 
    		FROM Inventory   
    		WHERE Part_Number='" . $PartNumber . "'";    
    $res = sql_query($sql);
    
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {             
        // if the part number field record is found, pass back the information
        if (sql_count($res) > 0)
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);

            // record found, pass back the information
            $Description = $row[0];
            $RetailPrice = $row[1];
            $WholesalePrice = $row[2];
            $Category = $row[3];
            $UnitPrice = $row[2];
            $Position = $row[4];
            $QuantityInStock = $row[5];
        }
        else
        {
            // record not found, pass back default information
            $Description = "UNKNOWN";
            $RetailPrice = 0;
            $WholesalePrice = 0;
            $Category = "None";
            $UnitPrice = 0;
            $Position = "UNKNOWN";
            $QuantityInStock = 0;
        }
    }
    else
    {
        // error processing database request, tell the user
        DisplayDatabaseError("GetInventoryItem", $sql);
    }
}

//********************************************************************
// AircraftShouldBeGrounded(AircraftID As String)
//
// Purpose:  Check to see if the current aircraft should be grounded by
//           scanning the squawks. If a grounding squawk is found, return
//           true
//
// Inputs:
//   AircraftID - tail number for the aircraft to check for grounding
//                squawks.
//
// Outputs:
//   nome
//
// Returns:
//   True if a grounding squawk is found
//*********************************************************************
function AircraftShouldBeGrounded($AircraftID)
{    
    // create a query for the current aircraft squawks
    $sql = "SELECT * FROM Squawks " .   
    		"WHERE (Aircraft = '" . UCase(Trim($AircraftID)) . "') AND " . 
    		"       Grounding = 1 AND " . 
    		"       Length(Trim(Repair_Date)) = 0";    
    $res = sql_query($sql);
    
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    { 
        // did we find any squawks?
        if (sql_count($res) > 0)
        {
            // yes, aircraft should be grounded
            $AircraftShouldBeGrounded = true;
        }
        else
        {
            // no grounding squawks
            $AircraftShouldBeGrounded = False;
        }
    }
    else
    {
        // error processing database request, tell the user
        DisplayDatabaseError("AircraftShouldBeGrounded", $sql);
        $AircraftShouldBeGrounded = false;
    }
    
    // return the results
    return $AircraftShouldBeGrounded;
}

//********************************************************************
// DeleteFlightInstructorRecord(
//                                InstructorKeyCode As String,
//                                StudentKeycode as String,
//                                OldDualTime As Double,
//                                OldPPTime As Double,
//                                InstructionDate As String,
//                                AircraftID As String,
//                                ModelID As Integer)
//
// Purpose:      Search for a flight instruction record that matches the
//               input parameters and delete it.
//
// Inputs:
//   InstructorKeyCode - keycode for the instructor
//   StudentKeycode - keycode for the student
//   OldDualTime - dual time
//   OldPPTime - P & P time
//   InstructionDate - flight date
//   AircraftID - aircraft tail number
//   ModelID - type of the aircraft
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
function DeleteFlightInstructorRecord( 
                                $InstructorKeyCode, 
                                $StudentKeycode, 
                                $OldDualTime, 
                                $OldPPTime, 
                                $InstructionDate, 
                                $AircraftID, 
                                $ModelID)
{                                        
    // delete the flight instructor record if needed
    if (strlen($InstructorKeyCode) > 0 ||
        strlen($StudentKeycode) > 0 ||
        strlen($AircraftID) > 0)
    {
        DeleteDatabaseRecord("Flight", 
           "(" . 
               "Keycode='" . Trim($InstructorKeyCode) . "' AND " . 
               "Student_Keycode='" . Trim($StudentKeycode) . "' AND " . 
               "Date='" . FormatField($InstructionDate, "DatabaseDate") . "' AND " . 
               "Aircraft='" . $AircraftID . "' AND " . 
               "model_id=" . Str($ModelID) . " AND " . 
               "ROUND(Dual_Time, 1)=" . RoundToDecimalPlaces($OldDualTime, 1) . " AND " . 
               "ROUND(Dual_PP_Time, 1)=" . RoundToDecimalPlaces($OldPPTime, 1) . 
           ")");
    }
}

//********************************************************************
// LoadPartNumbers($InventoryType, $SizeLimit=10)
//
// Purpose:  Load the possible inventory part numbers into the
//           combo box.
//
// Inputs:
//   InventoryType - currently selected inventory item
//   SizeLimit - optional. Limit of number of characters in each element
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function LoadPartNumbers($InventoryType, $SizeLimit=10)
{  
    global $PartNumberString;
    global $CurrentSelectedPartNumber;
          
    // if the part number string is not loaded, load it or the
    // selected item has changed
    if (len($PartNumberString) == 0 || 
            $InventoryType != $CurrentSelectedPartNumber)
    {
        // open a recordset for the inventory items
    	$sql = 
    			"SELECT DISTINCT " .
    			    "Part_Number " .
    			"FROM " .
    			    "Inventory " .
    			"ORDER BY Part_Number";
    	$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // see if any inventory items are found in the database
            $PartNumberString = "";
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
                // get the inventory part number
        		$PartNumberString = $PartNumberString .  "<OPTION " .
        				"VALUE='" . UCase($row[0]) . "'" . 
        				(UCase($row[0]) == $InventoryType ? " SELECTED" : "") . 
        				">" . Left(UCase($row[0]), $SizeLimit);
            }
        }
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("LoadPartNumbers", $sql);
        }  
    }
    
    // save the size limit variable for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var PartNumberSizeLimit = '$SizeLimit';";
    echo "</SCRIPT>";

    // build the part number selection list
    echo $PartNumberString;
}

//********************************************************************
// LoadDescriptions($InventoryType, $SizeLimit=10)
//
// Purpose:  Load the possible inventory descriptions into the
//           combo box.
//
// Inputs:
//   InventoryType - currently selected inventory item
//   SizeLimit - optional. Limit of number of characters in each element
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function LoadDescriptions($InventoryType, $SizeLimit=10)
{    
    global $DescriptionString;
    global $CurrentSelectedDescription;
       
    // if the description string is not loaded, load it or the
    // selected item has changed
    if (len($DescriptionString) == 0 || 
            $InventoryType != $CurrentSelectedDescription)
    {
        // open a recordset for the inventory items
    	$sql = 
    			"SELECT DISTINCT " .
    			    "Description " .
    			"FROM " .
    			    "Inventory " .
    			"ORDER BY Description";
    	$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // see if any inventory items are found in the database
            $DescriptionString = "";
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
                // get the inventory description
        		$DescriptionString = $DescriptionString .  "<OPTION " .
        				"VALUE='" . UCase($row[0]) . "'" . 
        				(UCase($row[0]) == $InventoryType ? " SELECTED" : "") . 
        				">" . Left(UCase($row[0]), $SizeLimit);
            }
            
            // save the current selection
            $CurrentSelectedDescription = $InventoryType;
        }
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("LoadDescriptions", $sql);
        }  
    }
    
    // save the size limit variable for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var DescriptionSizeLimit = '$SizeLimit';";
    echo "</SCRIPT>";

    // build the description selection list
    echo $DescriptionString;
}
 
//********************************************************************
// LoadCategories($SelectedCategory)
//
// Purpose:  Load the possible categories into the
//           combo box.
//
// Inputs:
//   SelectedCategory - currently selected category
//   SizeLimit - optional. Limit of number of characters in each element
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function LoadCategories($SelectedCategory, $SizeLimit=20)
{    
    // open a recordset for the category items
	$sql = 
			"SELECT DISTINCT " .
			    "Name " .
			"FROM " .
			    "Categories ";
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {
        // see if any category items are found in the database
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
            // get the category name
            $CategoryString = UCase($row[0]);
        
            // load the category items into the combo box
    		echo "<OPTION " .
    				"VALUE='" . $CategoryString . "'" . 
    				($CategoryString == $SelectedCategory ? " SELECTED" : "") . 
    				">" . Left($CategoryString, $SizeLimit);
        }
    }
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("LoadCategories", $sql);
    }  
    
    // save the size limit variable for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var CategorySizeLimit = '$SizeLimit';";
    echo "</SCRIPT>";
}
           
//********************************************************************
// SaveInventoryJavaScriptArrays()
//
// Purpose:  Save the inventory items that the javascript code needs 
//           to use when a part number is changed.
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
function SaveInventoryJavaScriptArrays()
{   
    // open a recordset for the inventory items
	$sql = 
			"SELECT " .
			    "Part_Number, " .           // 0
			    "Description, " .           // 1
			    "Unit_Price, " .            // 2
			    "Retail_Price, " .          // 3
			    "Quantity_In_Stock, " .     // 4
			    "Reorder_Quantity, " .      // 5
			    "Position, " .              // 6
			    "Category, " .              // 7
			    "Inventory_Type, " .        // 8
			    "Part_Number " .            // 9
			"FROM " .
			    "Inventory " .
			"ORDER BY Part_Number";
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {
        $NumInventoryRecords = sql_count($res);

        // build the global arrays for the javascript processing
        echo "<SCRIPT LANGUAGE=\"JavaScript\">";
        echo "var NumInventoryRecords = $NumInventoryRecords;";
    
        // build the array lists from the database information
        $DescriptionList = "";
        $UnitPriceList = "";
        $RetailPriceList = "";
        $QuantityInStockList = "";
        $ReorderQuantityList = "";
        $PositionList = "";
        $CategoryList = "";
        $InventoryTypeList = "";
        $PartNumberList = "";
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
            if ($i != $NumInventoryRecords - 1)
            {
                $DescriptionList = $DescriptionList . "'" . $row[1] . "',";
                $UnitPriceList = $UnitPriceList . "'" . $row[2] . "',";
                $RetailPriceList = $RetailPriceList . "'" . $row[3] . "',";
                $QuantityInStockList = $QuantityInStockList . "'" . $row[4] . "',";
                $ReorderQuantityList = $ReorderQuantityList . "'" . $row[5] . "',";
                $PositionList = $PositionList . "'" . $row[6] . "',";
                $CategoryList = $CategoryList . "'" . $row[7] . "',";
                $InventoryTypeList = $InventoryTypeList . "'" . $row[8] . "',";
                $PartNumberList = $PartNumberList . "'" . $row[9] . "',";
            }
            else
            {
                $DescriptionList = $DescriptionList . "'" . $row[1] . "'";
                $UnitPriceList = $UnitPriceList . "'" . $row[2] . "'";
                $RetailPriceList = $RetailPriceList . "'" . $row[3] . "'";
                $QuantityInStockList = $QuantityInStockList . "'" . $row[4] . "'";
                $ReorderQuantityList = $ReorderQuantityList . "'" . $row[5] . "'";
                $PositionList = $PositionList . "'" . $row[6] . "'";
                $CategoryList = $CategoryList . "'" . $row[7] . "'";
                $InventoryTypeList = $InventoryTypeList . "'" . $row[8] . "'";
                $PartNumberList = $PartNumberList . "'" . $row[9] . "'";
            }
        }
        echo "var DescriptionList = new Array($DescriptionList);";   
        echo "var UnitPriceList = new Array($UnitPriceList);";   
        echo "var RetailPriceList = new Array($RetailPriceList);";   
        echo "var QuantityInStockList = new Array($QuantityInStockList);";   
        echo "var ReorderQuantityList = new Array($ReorderQuantityList);";   
        echo "var PositionList = new Array($PositionList);";   
        echo "var CategoryList = new Array($CategoryList);";   
        echo "var InventoryTypeList = new Array($InventoryTypeList);";   
        echo "var PartNumberList = new Array($PartNumberList);";   
        
        // end of inventory arrays
        echo "</SCRIPT>";            
    }
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("SaveInventoryJavaScriptArrays", $sql);
    }  
}
    
//********************************************************************
// IsSchedulable(ResourceName As String) As Boolean
//
// Purpose:  Check to see if an aircraft is schedulable in the online
//           scheduling.
//
// Inputs:
//   ResourceName - name of the resource to check
//
// Outputs:
//   none
//
// Returns:
//   True if the aircraft is schedulable, false otherwise.
//*********************************************************************
function IsSchedulable($ResourceName)
{
    // get a recordset for the resource
    $SchedulableID = sql_query1(
                "SELECT schedulable_id FROM AircraftScheduling_resource WHERE resource_name='" .
                $ResourceName . "'");
    
    // see if this is a schedulable resource
    if ($SchedulableID != -1)
    {
        // record found, resource is schedulable
        $IsSchedulable = true;
    }
    else
    {
        // record not found, resource is not schedulable
        $IsSchedulable = false;
    }
    
    // return the results
    return $IsSchedulable;
}
    
//********************************************************************
// EnableScheduling(ResourceName As String)
//
// Purpose:  Enable scheduling of the resource in the online scheduling
//           program.
//
// Inputs:
//   ResourceName - name of the resource to enable
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function EnableScheduling($ResourceName)
{
    global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
    global $PrivateInstruction, $InstrumentInstruction;
    global $CommercialInstruction, $CFIInstruction, $CFIIInstruction;
    global $AircraftScheduleType, $InstructorScheduleType;
    global $DatabaseNameFormat;

    // if the resource is not schedulable, enable scheduling
    if (!IsSchedulable($ResourceName))
    {
        // wrap multi-step enable in a transaction for atomicity
        sql_begin();

        // resource is not already schedulable, enable online scheduling
        // of the resource
        if (IsAircraftID($ResourceName))
        {
            // resouce is an aircraft, enable scheduling
            // get the information for this aircraft
        	$MakeID = sql_query1(
        						"SELECT make_id " .
        						"FROM AircraftScheduling_aircraft " .
        						"WHERE n_number = '$ResourceName'");
        	$ModelID = sql_query1(
        						"SELECT model_id " .
        						"FROM AircraftScheduling_aircraft " .
        						"WHERE n_number = '$ResourceName'");
        	$AircraftID = sql_query1(
        						"SELECT aircraft_id " .
        						"FROM AircraftScheduling_aircraft " .
        						"WHERE n_number = '$ResourceName'");

            // get the aircraft's make
            $ResourceMake = LookupAircraftMake($MakeID);
            
            // get the aircraft's model
            $ResourceModel = LookupAircraftType($ModelID);
            
            // add a link in the resource table to the aircraft
            $DatabaseFields = array();
            SetDatabaseRecord("item_id",
                            $AircraftID, $RSConversionNumber, $DatabaseFields[0]);
            SetDatabaseRecord("schedulable_id",
                            $AircraftScheduleType, $RSConversionNumber, $DatabaseFields[1]);
            SetDatabaseRecord("resource_name",
                            $ResourceName, $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("resource_make",
                            $ResourceMake, $RSConversionString, $DatabaseFields[3]);
            SetDatabaseRecord("resource_model",
                            $ResourceModel, $RSConversionString, $DatabaseFields[4]);
            AddDatabaseRecord(
                                "AircraftScheduling_resource",
                                $DatabaseFields);
            
            // get the resource ID of the record we just inserted
        	$ResourceID = sql_query1(
        						"SELECT resource_id " .
        						"FROM AircraftScheduling_resource " .
        						"WHERE resource_name = '$ResourceName'");
            
            // set the resource id into the aircraft record
            $DatabaseFields = array();
            SetDatabaseRecord("resource_id",
                            $ResourceID, $RSConversionNumber, $DatabaseFields[0]);
            UpdateDatabaseRecord(
                                "AircraftScheduling_aircraft",
                                $DatabaseFields,
                                "n_number='" . UCase(Trim($ResourceName)) . "'");
        }
        else
        {
            // resource is an instructor, enable scheduling
            // get the information for this instructor
        	$UserName = sql_query1(
        						"SELECT username " .
        						"FROM AircraftScheduling_person " .
        						"WHERE $DatabaseNameFormat='" .
                                $ResourceName . "'");
        	$PersonID = sql_query1(
        						"SELECT person_id " .
        						"FROM AircraftScheduling_person " .
        						"WHERE $DatabaseNameFormat='" .
                                $ResourceName . "'");
            
            // add a link in the instructor table to the person
            $DatabaseFields = array();
            SetDatabaseRecord("hourly_cost",
                            GetInstructorCreditRate($UserName, $PrivateInstruction),
                            $RSConversionNumber, $DatabaseFields[0]);
            SetDatabaseRecord("lesson_fee", 0, $RSConversionNumber, $DatabaseFields[1]);
            SetDatabaseRecord("description", "", $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("picture", "", $RSConversionString, $DatabaseFields[3]);
            SetDatabaseRecord("resource_id", "NULL", $RSConversionNone, $DatabaseFields[4]);
            SetDatabaseRecord("person_id",
                            $PersonID, $RSConversionNumber, $DatabaseFields[5]);
            AddDatabaseRecord(
                                "AircraftScheduling_instructors",
                                $DatabaseFields);
            
            // get the instructor ID of the record we just inserted
        	$InstructorID = sql_query1(
        						"SELECT instructor_id " .
        						"FROM AircraftScheduling_instructors " .
        						"WHERE person_id=" . $PersonID);
            
            // add a link in the resource table to the instructor
            $DatabaseFields = array();
            SetDatabaseRecord("item_id",
                            $InstructorID, $RSConversionNumber, $DatabaseFields[0]);
            SetDatabaseRecord("schedulable_id",
                            $InstructorScheduleType, $RSConversionNumber, $DatabaseFields[1]);
            SetDatabaseRecord("resource_name",
                            $ResourceName, $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("resource_make",
                            "", $RSConversionString, $DatabaseFields[3]);
            SetDatabaseRecord("resource_model",
                            "", $RSConversionString, $DatabaseFields[4]);
            AddDatabaseRecord(
                                "AircraftScheduling_resource",
                                $DatabaseFields);
            
            // get the resource ID of the record we just inserted
        	$ResourceID = sql_query1(
        						"SELECT resource_id " .
        						"FROM AircraftScheduling_resource " .
        						"WHERE resource_name = '$ResourceName'");
            
            // set the resource id into the instructor record
            $DatabaseFields = array();
            SetDatabaseRecord("resource_id",
                            $ResourceID, $RSConversionNumber, $DatabaseFields[0]);
            UpdateDatabaseRecord(
                                "AircraftScheduling_instructors",
                                $DatabaseFields,
                                "person_id=" . $PersonID);
        }

        sql_commit();
    }
}

//********************************************************************
// DisableScheduling(ResourceName As String)
//
// Purpose:  Disable scheduling of the resource in the online scheduling
//           program.
//
// Inputs:
//   ResourceName - name of the resource to enable
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function DisableScheduling($ResourceName)
{
    global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
    global $DatabaseNameFormat;

    // if the resource is schedulable, disable scheduling
    if (IsSchedulable($ResourceName))
    {
        // wrap multi-step disable in a transaction for atomicity
        sql_begin();

        // resource is schedulable, disable online scheduling
        // of the resource
        if (IsAircraftID($ResourceName))
        {
            // resouce is an aircraft, disable scheduling
            // get the information for this aircraft
        	$ResourceID = sql_query1(
        						"SELECT resource_id " .
        						"FROM AircraftScheduling_aircraft " .
        						"WHERE n_number = '$ResourceName'");
            
            // delete the link in the resource table to the aircraft
            DeleteDatabaseRecord(
                        "AircraftScheduling_resource",
                        "resource_id='" . $ResourceID . "'");
            
            // clear the resource id in the aircraft record
            $DatabaseFields = array();
            SetDatabaseRecord("resource_id", "NULL", $RSConversionNone, $DatabaseFields[0]);
            UpdateDatabaseRecord(
                                "AircraftScheduling_aircraft",
                                $DatabaseFields,
                                "n_number='" . UCase(Trim($ResourceName)) . "'");
        }
        else
        {
            // resource is an instructor, disable scheduling
            // get the information for this instructor
        	$PersonID = sql_query1(
        						"SELECT person_id " .
        						"FROM AircraftScheduling_person " .
        						"WHERE $DatabaseNameFormat='" .
                                $ResourceName . "'");
            
            // get the instructor ID of the record we want to delete
        	$ResourceID = sql_query1(
        						"SELECT resource_id " .
        						"FROM AircraftScheduling_instructors " .
        						"WHERE person_id=" . $PersonID);
            
            // delete the link in the resource table to the instructor
            DeleteDatabaseRecord(
                        "AircraftScheduling_resource",
                        "resource_id='" . $ResourceID . "'");
            
            // delete the link in the instructor table to the person
            DeleteDatabaseRecord(
                        "AircraftScheduling_instructors",
                        "person_id='" . $PersonID . "'");
        }

        sql_commit();
    }
}

//********************************************************************
// DisplayDatabaseError($CallingProcedure, $sql)
//
// Purpose: Display a database error to the user.
//
// Inputs:
//   CallingProcedure - the name of the procedure with the database
//                      error.
//   sql - the sql that failed
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function DisplayDatabaseError($CallingProcedure, $sql)
{
    global $year, $month, $day, $resource, $makemodel, $resource_id;

    echo "<H2>Unable to query the database in $CallingProcedure</H2>";
    echo "SQL: $sql<BR>";
    echo sql_error() . "<BR>";
    
    // generate return URL
    GenerateReturnURL(
                        $goback, 
                        "?day=$day&month=$month&year=$year$makemodel" . 
                        "&resource=$resource" .
                        "&resource_id=$resource_id"
                        );
}
    
//********************************************************************
// DisplayAircraftDropDown(
//                          $TailNumber, 
//                          $LoadJavaScript, 
//                          $GenerateAll,
//                          $AircraftStatus)
//
// Purpose: Generate the HTML code to display the aircraft dropdown
//          selection box and the Javascript code to process the selection.
//
// Inputs:
//   TailNumber - tailnumber of the currently selected aircraft
//   LoadJavaScript - optional. set true to save the WB information
//                    for the javascript code.
//   GenerateAll - optional. set true to generate "All" selection.
//   AircraftStatus - SQL selection string for the aircraft status
//
// Outputs:
//   none
//
// Returns:
//   the tailnumber of the selected aircraft
//*********************************************************************
function DisplayAircraftDropDown(
                                    $TailNumber, 
                                    $LoadJavaScript,
                                    $GenerateAll,
                                    $AircraftStatus)
{
    global $OnLineString;
    global $year, $month, $day, $resource, $makemodel, $resource_id;
    global $pview;
     
    // if we are not generating a printable version, generate the dropdown
    if ($pview != 1)
    {
        // get the schedule information for the aircraft
        $sql = "SELECT n_number, status " . 
               "FROM AircraftScheduling_aircraft  " . 
               "WHERE $AircraftStatus "  . 
               "ORDER BY n_number";
        $res = sql_query($sql);
        
        // if we have anything to display
        if ($res)
        {
            // start the drop down box
            echo "<SELECT NAME='TailNumber' onChange=SelectDisplayEntity()>";
        
            // should we generate the "All" selection
            if ($GenerateAll)
            {
                // put "All" as a choice
        		echo "<OPTION " .
        				"VALUE='" . "All" . "'" . 
        				("All" == $TailNumber ? " SELECTED" : "") . 
        				">All";
        				
        	    // are we generating the JavaScript code?
                if ($LoadJavaScript)
                {        	    
                    $LinkEntriesCount = 0;
                    $LinkArray[$LinkEntriesCount] = 
                        GetScriptName() . 
                        "?year=$year&month=$month&day=$day&resource=$resource$makemodel&TailNumber='All'";
                    $LinkEntriesCount = $LinkEntriesCount + 1;
                }
            }  
            else
            {        				
                // don't generate "All" selection
        	    // are we generating the JavaScript code?
                if ($LoadJavaScript)
                {        	    
                    $LinkEntriesCount = 0;
                }
            }      				
    
            // add the results of the query to the drop down box
            for ($i = 0; ($row = sql_row($res, $i)); $i++)
            {
                // if the TailNumber is not already set, use the first entry
                if(strlen($TailNumber) == 0)
                {
                    // only use the TailNumber if we have a valid tail number
                    if (!empty($row[0])) $TailNumber = $row[0];
                }
        
                if ($row[0] == $TailNumber)
                {
                    echo "<OPTION SELECTED" . ">$row[0]";
                }
                else 
                    echo "<OPTION" . ">$row[0]";
                    
                // save the link entry for building the java script later
                if ($LoadJavaScript)
                {
                    $LinkArray[$LinkEntriesCount] = 
                        GetScriptName() . 
                        "?year=$year&month=$month&day=$day&resource=$resource$makemodel&TailNumber=$row[0]";
                    $LinkEntriesCount = $LinkEntriesCount + 1;
                }
           }
    
            // end the drop down box
            echo "</SELECT>";
            
            // should we generate the javascript code?
            if ($LoadJavaScript)
            {
                // setup script to select link when the aircraft selection changes
                echo "<SCRIPT LANGUAGE=\"JavaScript\">";
                echo "function SelectDisplayEntity()";
                echo "{";
                echo "  var LinkEntries = new Array;";
            
                // build the link entry array
                $LinkEntriesCount = 0;
                foreach($LinkArray as $Item)
                {
                    echo "  LinkEntries[" . $LinkEntriesCount . "] = \"" . $Item . "\";";
                    $LinkEntriesCount = $LinkEntriesCount + 1;
                }
                    
            	echo "	window.location.href = LinkEntries[document.forms['main'].TailNumber.selectedIndex];";
                echo "}";
                echo "</SCRIPT>";
            }
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("DisplayAircraftDropDown", $sql);
        }
    }
    
    // return the currently selected tailnumber
    return $TailNumber;
}
    
//********************************************************************
// SQLOpenRecordset($SQL)
//
// Purpose: Open a recordset with the given SQL statement.
//
// Inputs:
//   SQL - SQL for selecting the records
//
// Outputs:
//   none
//
// Returns:
//   the result of the open. an error is displayed if one occurs
//*********************************************************************
function SQLOpenRecordset($SQL)
{
    // perform the query
    $Result = sql_query($SQL);
     
    // if we didn't have any errors
    if(!$Result) 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("SQLOpenRecordset", $SQL);
    }
    
    // return the result
    return $Result;
}
?>
