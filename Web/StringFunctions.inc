<?php
//-----------------------------------------------------------------------------
// 
// StringFunctions
// 
// PURPOSE: Contains general functions to work with strings. Generally follows
//          the visual basic model of using strings.
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

require_once("DatabaseFunctions.inc");

$RightJustify = 1;      // right justify within the field
$LeftJustify  = 2;      // left justify within the field
$CenterJustify = 3;     // center the value within the field
$EncryptionKey = "jefadmo1";    // encryption key

//********************************************************************
// Str($InputNumber)
//
// Purpose: Return a string from the input number.
//
// Inputs:
//   InputString - the number to convert
//
// Outputs:
//   none
//
// Returns:
//   The converted string.
//*********************************************************************
function Str($InputNumber)
{
    return (string) $InputNumber;
}

//********************************************************************
// Val($InputString)
//
// Purpose: Return the numeric value of the string.
//
// Inputs:
//   InputString - the string to convert
//
// Outputs:
//   none
//
// Returns:
//   The converted integer.
//*********************************************************************
function Val($InputString)
{
    return (float) $InputString;
}

//********************************************************************
// Left($InputString, $Length)
//
// Purpose: Return the left most character of the input string.
//
// Inputs:
//   InputString - the string to extract the character from
//   Length - the number of characters to return
//
// Outputs:
//   none
//
// Returns:
//   The requested characters
//*********************************************************************
function Left($InputString, $Length)
{
    return substr($InputString, 0, $Length);
}

//********************************************************************
// Right($InputString, $Length)
//
// Purpose: Return the right most characters of the input string.
//
// Inputs:
//   InputString - the string to extract the character from
//   Count - the number of characters to return
//
// Outputs:
//   none
//
// Returns:
//   The requested characters
//*********************************************************************
function Right($InputString, $Length)
{
    return substr($InputString, strlen($InputString) - $Length, $Length);
}

//********************************************************************
// Mid($InputString, $Start, $Length)
//
// Purpose: Return the middle characters of the input string.
//
// Inputs:
//   InputString - the string to extract the character from
//   Start - the position within the string to start (1 is 1st char)
//   Length - the number of characters to return
//
// Outputs:
//   none
//
// Returns:
//   The requested characters
//*********************************************************************
function Mid($InputString, $Start, $Length)
{
    return substr($InputString, $Start - 1, $Length);
}

//********************************************************************
// InStr($Start, $String1, $String2, $Compare)
//
// Purpose: Returns the position of the first occurrence of one 
//          string within another
//
// Inputs:
//          Start - numeric expression that sets the starting position 
//                  for each search.
//          String1 - string expression being searched. 
//          String2 - string expression sought. 
//          Compare - not used. for compatibility
//
// Outputs:
//   none
//
// Returns:
//   The requested characters
//*********************************************************************
function InStr($Start, $String1, $String2, $Compare=0)
{
    $StringPosition = strpos($String1, $String2, $Start - 1);
    
    // check the return value, if it is false (instead of 0), the
    // string was not found
    if ($StringPosition === false)
    {
        // String2 was not found in String1
        return 0;
    } 
    else
    {
        // String2 was found in String1, return the position
        return ($StringPosition + 1);
    }
}

//********************************************************************
// Len($String)
//
// Purpose: Return the length of the string.
//
// Inputs:
//          String - string expression to test. 
//
// Outputs:
//   none
//
// Returns:
//   The length of the string.
//*********************************************************************
function Len($String)
{
    return strlen($String);
}

//********************************************************************
// UCase($String)
//
// Purpose: Convert the string to upper case and return it.
//
// Inputs:
//          String - string expression to convert. 
//
// Outputs:
//   none
//
// Returns:
//   The updated string.
//*********************************************************************
function UCase($String)
{
    return strtoupper($String);
}

//********************************************************************
// Replace(Expression, Find, ReplaceWith)
//
// Purpose: Replace the string Find with ReplaceWith in the string
//          Expression.
//
// Inputs:
//   Expression - String expression containing substring to replace. 
//   Find - Substring being searched for. 
//   ReplaceWith - Replacement substring. 
//
// Outputs:
//   none
//
// Returns:
//   The requested characters
//*********************************************************************
function Replace($Expression, $Find, $ReplaceWith)
{
    return str_replace($Find, $ReplaceWith, $Expression);
}

//********************************************************************
// Space($NumberSpaces)
//
// Purpose: Returns a string consisting of the specified number of spaces
//
// Inputs:
//   NumberSpaces - number of spaces to generate
//
// Outputs:
//   none
//
// Returns:
//   The requested size string
//*********************************************************************
function Space($NumberSpaces)
{
    $Space = "";
    for ($i = 0; $i < $NumberSpaces; $i++)
    {
        $Space = $Space . " ";
    }
    
    // return the results
    return $Space;
}

//********************************************************************
// JustifyField(Field As String,
//                   Justify As JustifyType,
//                   FieldSize as integer)
//                   As String
//
// Purpose: Justify the string within the size of the field
//
// Inputs:
//   Field - the field to justify
//   Justify - type of justification (right, left or center)
//   FieldSize - the size of the field to justify within
//
// Outputs:
//   none
//
// Returns:
//   The justified string
//*********************************************************************
function JustifyField(
                        $Field,
                        $Justify,
                        $FieldSize)
{
    global $RightJustify, $LeftJustify, $CenterJustify;
    
    // perform the requested justify operation
    $InputString = trim($Field);
    switch ($Justify)
    {
    case $RightJustify:
        // right justify the field
        if (Len($InputString) < $FieldSize)
        {
            // field length is less than requested size, pad on the left
            $OutputString = Space($FieldSize - Len($InputString)) . $InputString;
        }
        elseif (Len($InputString) > $FieldSize)
        {
            // field length is greater than requested size, truncate the field
            $OutputString = Left($InputString, $FieldSize);
        }
        else
        {
            // field length is equal to the requested size, pass back the field
            $OutputString = $InputString;
        }
        break;
    case $CenterJustify:
        // center the field
        if (Len($InputString) < $FieldSize)
        {
            // field length is less than requested size, center the field
            $OutputString = Space(($FieldSize - Len($InputString)) / 2) . $InputString .
                                    Space(($FieldSize - Len($InputString)) / 2);
            
            // if we had an odd number of spaces caused by rounding, add one more
            // space on the right
            if (Len($OutputString) < $FieldSize)
            {
                $OutputString = $OutputString . Space(1);
            }
        }
        elseif (Len($InputString) > $FieldSize)
        {
            // field length is greater than requested size, truncate the field
            $OutputString = Left($InputString, $FieldSize);
        }
        else
        {
            // field length is equal to the requested size, pass back the field
            $OutputString = $InputString;
        }
        break;
   case $LeftJustify:
        // left justify the field
        if (Len($InputString) < $FieldSize)
        {
            // field length is less than requested size, pad on the right
            $OutputString = $InputString . Space($FieldSize - Len($InputString));
        }
        elseif (Len($InputString) > $FieldSize)
        {
            // field length is greater than requested size, truncate the field
            $OutputString = Left($InputString, $FieldSize);
        }
        else
        {
            // field length is equal to the requested size, pass back the field
            $OutputString = $InputString;
        }
        break;
    }
    
    // pass back the justified string
    $JustifyField = Left($OutputString, $FieldSize);
    
    // return the results
    return $JustifyField;
}

//********************************************************************
// GetNextToken(TokenString as String, Delimiter as String) As String
//
// Purpose:      Get a token from the string TokenString. A token is
//               delimited by the Delimiter value.
//
// Inputs:
//   TokenString - A string containing the tokens
//   Delimiter - The character that seperates the fields
//
// Outputs:
//   TokenString - The updated string with the token removed
//
// Returns:
//   The token from the TokenString
//
// Notes:
//   The TokenString is modified by this function
//
//*********************************************************************
function GetNextToken(&$TokenString, $Delimiter)
{    
    global $vbCancel, $vbOK, $vbTextCompare;

    // see if we have a field break
    $FieldBreakPos = InStr(1, $TokenString, $Delimiter, $vbTextCompare);
     
    // if the token was not found, but the TokenString length > 0 then
    // assume the remainder of the string is the token
    if ($FieldBreakPos == 0)
    {
        // token not found, return the remainder of the string
        $Token = $TokenString;
        $TokenString = "";
    }
    Else
    {
        // token found, get the token
        $Token = Mid($TokenString, 1, $FieldBreakPos - 1);
        
        // remove the token from the string
        $TokenString = Mid($TokenString, $FieldBreakPos + 1, Len($TokenString) - $FieldBreakPos);
    }
    
    // return the token
    return $Token;
}

//********************************************************************
// FormatField(InputField As String, FieldType as String) As String
//
// Purpose:      Format the input field as specified by the FieldType.
//
// Inputs:
//   InputField - the existing field
//   FieldType - the type of field
//
// Outputs:
//   none
//
// Returns:
//   The InputField formated
//*********************************************************************
function FormatField(
                    $InputField, 
                    $FieldType) 
{
    global $CurrencyPrefix;
    
    // format the field
    $FormatField = "";
    switch (UCase($FieldType))
    {
    case "BOOLEAN":
        // boolean field
        if ($InputField == "1" || UCase($InputField) == "TRUE" || UCase(Left($InputField, 1)) == "T")
            $FormatField = "TRUE";
        else
            $FormatField = "FALSE";
        break;
    case "YESNO":
        // yes/no field
        if ($InputField == "1" ||
            UCase($InputField) == "TRUE" ||
            UCase($InputField) == "YES" ||
            UCase($InputField) == "Y" ||
            UCase(Left($InputField, 1)) == "T")
            $FormatField = "YES";
        else
            $FormatField = "NO";
        break;
    case "DATE":
        // date field
        // ignore null dates
        if (Len(Trim($InputField)) != 0 && $InputField != "0000-00-00 00:00:00")
        {
            $FormatField = 
                        Day(DateValue($InputField)) . "-" . 
                        GetShortMonth(Month(DateValue($InputField))) . "-" . 
                        Year(DateValue($InputField));
        }
        break;
    case "DATABASEDATE":
        // date field
        // ignore null dates
        if (Len(Trim($InputField)) != 0 && $InputField != "0000-00-00 00:00:00")
        {
            $FormatField = 
                            Year(DateValue($InputField)) . "-" .
                            Month(DateValue($InputField)) . "-" . 
                            Day(DateValue($InputField)); 
         }
        break;
    case "INTEGER":
        // integer field
        $FormatField = number_format(GetNumber($InputField), 0, ".", "");
        break;
    case "FLOAT":
        // floating point field
        $FormatField = number_format(GetNumber($InputField), 1, ".", "");
        break;
    case "STRING":
        $FormatField = $InputField;
        break;
    case "CURRENCY":
        // money field
        if ($InputField >= 0)
        {
            // postive number
            $FormatField = $CurrencyPrefix . number_format(GetNumber($InputField), 2);
        }
        else
        {
            // negative number
            $FormatField = "(" . $CurrencyPrefix . number_format(abs(GetNumber($InputField)), 2) . ")";
        }
        break;
    default:
        $FormatField = $InputField;
        break;
    }
    
    // return the results
    return $FormatField;
}

//********************************************************************
// GetNumber(InputField As String) As Double
//
// Purpose:      Return the numeric value of the input field
//
// Inputs:
//   InputField - the existing field
//
// Outputs:
//   none
//
// Returns:
//   The InputField converted to a numeric
//*********************************************************************
function GetNumber($InputField)
{
    global $CurrencyPrefix;
    
    // clear extra characters from the input field
    $tmpInputField = "";
    for ($i = 1; $i <= Len($InputField); $i++)
    {
        $InputCharacter = Mid($InputField, $i, 1);
        if ($InputCharacter == " ")
        {
            // ignore this character
        }
        elseif ($InputCharacter == $CurrencyPrefix)
        {
            // ignore this character
        }
        elseif ($InputCharacter == ",")
        {
            // ignore this character
        }
        else
        {
            // add character to the output
            $tmpInputField = $tmpInputField . $InputCharacter;
        }
    }
    
    // return the numeric value
    return Val($tmpInputField);
}
    
//********************************************************************
// AddEscapes(InputString As String) As Variant
//
// Purpose:  Add an escape ("\") to any single quotes (') contained
//           within a string.
//
// Inputs:
//   InputString - value to check
//
// Outputs:
//   none
//
// Returns:
//   The string with escapes added.
//*********************************************************************
function AddEscapes($InputString)
{
    // escape any single quotes within the string
    $InputStringPointer = 1;
    $AddEscapes = "";
    $NextCharacter = "";
    while ($InputStringPointer <= Len($InputString))
    {
        // get a character from the input string
        $InputCharacter = Mid($InputString, $InputStringPointer, 1);
        $NextCharacter = Mid($InputString, $InputStringPointer + 1, 1);
        
        // if this is a quote, escape the quote
        if ($InputCharacter == "'")
        {
            // ' character, add an escape
            $AddEscapes = $AddEscapes . "\\" . $InputCharacter;
        }
        elseif ($InputCharacter == "\"")
        {
            // " character, add an escape
            $AddEscapes = $AddEscapes . "\\" . $InputCharacter;
        }
        elseif ($InputCharacter == "\\")
        {
            // \ character, add an escape if we don't already have one
            if ($NextCharacter != "\\")
            {
                $AddEscapes = $AddEscapes . "\\" . $InputCharacter;
            }
        }
        elseif ($InputCharacter == "`")
        {
            // ` character, add an escape
            $AddEscapes = $AddEscapes . "\\" . $InputCharacter;
        }
        else
        {
            $AddEscapes = $AddEscapes . $InputCharacter;
        }
        
        $InputStringPointer = $InputStringPointer + 1;
    }
    
    // return the new string
    return $AddEscapes;
}
    
//********************************************************************
// StripSpecialChars(InputString As String) As Variant
//
// Purpose:  Stip any special characters from the given string.
//
// Inputs:
//   InputString - value to check
//
// Outputs:
//   none
//
// Returns:
//   The string with the special characters stripped.
//*********************************************************************
function StripSpecialChars($InputString)
{
    $InputStringPointer = 1;
    $StripSpecialChars = "";
    while ($InputStringPointer <= Len($InputString))
    {
        // get a character from the input string
        $InputCharacter = Mid($InputString, $InputStringPointer, 1);
        
        // if this is a quote, remove it
        if ($InputCharacter == "'")
        {
            // ' character, ignore it
        }
        elseif ($InputCharacter == "\"")
        {
            // " character, ignore it
        }
        elseif ($InputCharacter == "\\")
        {
            // \ character, ignore it
        }
        elseif ($InputCharacter == "`")
        {
            // ` character, ignore it
        }
        elseif ($InputCharacter == "&")
        {
            // & character, ignore it
        }
        elseif ($InputCharacter == "#")
        {
            // # character, ignore it
        }
        elseif ($InputCharacter == "+")
        {
            // + character, ignore it
        }
        elseif ($InputCharacter == "\n")
        {
            // new line character, replace it with a blank
            $StripSpecialChars = $StripSpecialChars . " ";
        }
        elseif ($InputCharacter == "\r")
        {
            // carriage return character, ignore it
        }
        else
        {
            $StripSpecialChars = $StripSpecialChars . $InputCharacter;
        }
        
        $InputStringPointer = $InputStringPointer + 1;
    }
    
    // return the new string
    return $StripSpecialChars;
}

//********************************************************************
// EncryptString(InputString as String)
//
// Purpose:  Encrypt the input string and pass it back.
//
// Inputs:
//   InputString - the string to encrypt
//
// Outputs:
//   none
//
// Returns:
//   The encrypted string
//*********************************************************************
function EncryptString($InputString)
{
    global $EncryptionKey;
    
    // do we need to encrypt the string
    if (!empty($InputString))
    {
        // remove any leading and trailing blanks
        $TrimmedInputString = Trim($InputString);
        
        // loop through the string and apply the encryption code
        $CodeCount = 1;
        $OutputString = "";
        for ($i = 1; $i <= Len($TrimmedInputString); $i++)
        {
            // get an encryption code byte
            $CodeCount = $CodeCount + 1;
            if ($CodeCount > Len($EncryptionKey)) $CodeCount = 1;
            $EncryptionCode = ord(Mid($EncryptionKey, $CodeCount, 1));
            
            // apply the encryption key to the input string
            $OutputByte = (ord(Mid($TrimmedInputString, $i, 1)) + $EncryptionCode) % 256;
            $OutputString = $OutputString . Chr($OutputByte);
        }
        
        // pass back the encrypted string (the "0" is used
        // to flag the string as encrypted)
        $EncryptString = "0" . $OutputString;
    }
    else
    {
        // null value
        $EncryptString = "";
    }
    
    // return the result
    return $EncryptString;
}

//********************************************************************
// DecryptString(InputString as String)
//
// Purpose:  Decrypt the input string and pass it back.
//
// Inputs:
//   InputString - the string to encrypt
//
// Outputs:
//   none
//
// Returns:
//   The decrypted string
//*********************************************************************
function DecryptString($InputString)
{
    global $EncryptionKey;
    
    // do we need to decrypt the string
    if (!empty($InputString))
    {
        if (Left($InputString, 1) == "0")
        {
            // decrypt the string
            // remove any leading and trailing blanks
            $TrimmedInputString = Trim(Right($InputString, Len($InputString) - 1));
            
            // loop through the string and apply the decryption code
            $CodeCount = 1;
            $OutputString = "";
            for ($i = 1; $i <= Len($TrimmedInputString); $i++)
            {
                // get an encryption code byte
                $CodeCount = $CodeCount + 1;
                if ($CodeCount > Len($EncryptionKey)) $CodeCount = 1;
                $EncryptionCode = ord(Mid($EncryptionKey, $CodeCount, 1));
                
                // apply the encryption key to the input string
                $OutputByte = (ord(Mid($TrimmedInputString, $i, 1)) - $EncryptionCode) % 256;
                $OutputString = $OutputString . Chr($OutputByte);
            }
            
            // pass back the decrypted string
            $DecryptString = $OutputString;
        }
        else
        {
            // no decryption needed
            $DecryptString = Trim($InputString);
        }
    }
    else
    {
        // null value
        $DecryptString = "";
    }
    
    // return the result
    return $DecryptString;
}

//********************************************************************
// FormatSSNString(InputString as String)
//
// Purpose:  Format a SSN where only the last four digits are displayed.
//
// Inputs:
//   InputString - the input SSN
//
// Outputs:
//   none
//
// Returns:
//   The formated SSN string
//*********************************************************************
function FormatSSNString($InputString)
{
    // only display the last four digits of the SSN
    $FormatSSNString = "XXX-XX-" . Right(Trim($InputString), 4);
    
    // return the result
    return $FormatSSNString;
}

//********************************************************************
// GetUserLevel(PilotType As String)
//
// Purpose:  Map the pilot rating to a userlevel. Pilot rating is used
//           by this program, userlevel is used by the on-line
//           scheduling program.
//
// Inputs:
//   PilotType - the rating of the pilot
//
// Outputs:
//   none
//
// Returns:
//   The userlevel for the given pilot rating.
//*********************************************************************
function GetUserLevel($PilotType)
{

    //set the user level based on the pilot type
    //   (0) Disabled User may not sign in.
    //   (1) Normal User may sign in and modify any items that the user creates.
    //   (2) Super User may sign in, modify any items that the user creates and
    //              modify any items that other users create.
    //   (3) Office User may sign in, modify any items that the user creates,
    //               modify any items that other users create and modify user
    //               information (add instructor, add users, etc).
    //   (4) Maintenance User may sign in, modify any items that the user creates,
    //               modify any items that other users create and modify aircraft
    //               information (add aircraft, delete aircraft,
    //               update aircraft status).
    //   (5) Administrator User may sign in and modify any items.
    switch ($PilotType)
    {
    case "Student":
        $GetUserLevel = 1;
        break;
    case "Private Under 200":
        $GetUserLevel = 1;
        break;
    case "Private Over 200":
        $GetUserLevel = 1;
        break;
    case "Instrument":
        $GetUserLevel = 1;
        break;
    case "CFI":
        $GetUserLevel = 2;
        break;
    default:
        $GetUserLevel = 0;
        break;
    }
    
    // return the results
    return $GetUserLevel;
}
 
//********************************************************************
// BuildMemberSelector(
//                      NameOfUser, 
//                      AddNoneOption, 
//                      ControlPrefix, 
//                      NameSize,
//                      OnlyActive,
//                      IncludeUsername,
//                      JavascriptHandler)
//
// Purpose: Display a selector for the list of members.
//
// Inputs:
//   NameOfUser - selected user in the list
//   AddNoneOption - optional. set true to add "None" as a selection
//                   option.
//   ControlPrefix - optional. prefix for the control name so that
//                   the name becomes $ControlPrefix . NameOfUser.
//   NameSize - optional. number of characters to display in the name
//   OnlyActive - optional. set true to only include the active members
//   IncludeUsername - optional. set true to include the username in the
//                   drop down list (display only).
//   JavascriptHandler - optional. set to the name of the onchange function
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildMemberSelector(
                            $NameOfUser, 
                            $AddNoneOption = false, 
                            $ControlPrefix = "",
                            $NameSize = 20,
                            $OnlyActive = true,
                            $IncludeUsername = false,
                            $JavascriptHandler = "")
{
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
    global $DatabaseNameFormat;
    global $DisplayNameFormat;
    
    // build the user name list
	$sql = 
			"SELECT $DatabaseNameFormat, username, $DisplayNameFormat " .
			"FROM AircraftScheduling_person ";
    if ($OnlyActive)
    {			
        $sql = $sql .			
    			"WHERE user_level != $UserLevelDisabled ";
    }
    $sql = $sql .			
            "ORDER by last_name";		
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {    	
        // build the select HTML	
		echo "<SELECT NAME='" . $ControlPrefix . "NameOfUser' " . 
		            "id='" . $ControlPrefix . "NameOfUser' ";
    	if (strlen($JavascriptHandler) > 0)
    	    echo "onChange='$JavascriptHandler()'>";
    	else
		    echo ">";
		
		// do we need the "None" option?
		if ($AddNoneOption)
		{
            // put "None" as a choice
    		echo "<OPTION " .
    				"VALUE='" . "None" . "'" . 
    				("None" == UCase($NameOfUser) ? " SELECTED" : "") . 
    				">None";
		}
		
		// build the selection entries
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
			echo "<OPTION " .
					"VALUE='" . $row[0] . "'" . 
					(Ucase($row[0]) == UCase($NameOfUser) ? " SELECTED" : "");
		    if ($IncludeUsername)
		    {
                echo ">" . Left("(" . $row[1] . ") " . $row[2], $NameSize);
            }
		    else
		    {
                echo ">" . Left($row[2], $NameSize);
            }
		}
		echo "</SELECT>";	  			
  	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("BuildMemberSelector", $sql);
    }
}
 
//********************************************************************
// BuildInstructorSelector(
//                      InstructorName, 
//                      AddNoneOption, 
//                      ControlPrefix, 
//                      NameSize,
//                      OnlyActive,
//                      IncludeUsername,
//                      JavascriptHandler,
//                      UseUsername)
//
// Purpose: Display a selector for the list of instructors.
//
// Inputs:
//   InstructorName - selected user in the list
//   AddNoneOption - optional. set true to add "None" as a selection
//                   option.
//   ControlName - optional. name of the control. if it is not set,
//                   the default is InstructorName.
//   NameSize - optional. number of characters to display in the name
//   OnlyActive - optional. set true to only include the active members
//   IncludeUsername - optional. set true to include the username in the
//                   drop down list (display only).
//   JavascriptHandler - optional. set to the name of the onchange function
//   UseUsername - optional. set to true to produce the username instead of the
//                 instructor name when a new item is selected.
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildInstructorSelector(
                            $InstructorName, 
                            $AddNoneOption = false, 
                            $ControlName = "InstructorName",
                            $NameSize = 20,
                            $OnlyActive = true,
                            $IncludeUsername = false,
                            $JavascriptHandler = "",
                            $UseUsername = false)
{
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
    global $DatabaseNameFormat;
    global $AircraftScheduleType, $InstructorScheduleType;
    
    // build the user name list
	$sql = 
			"SELECT $DatabaseNameFormat, username, first_name, last_name " .
			"FROM AircraftScheduling_resource a, " .
			"     AircraftScheduling_instructors b, " .
			"     AircraftScheduling_person c " . 
			"WHERE a.schedulable_id=$InstructorScheduleType AND " .
			"      a.item_id=b.instructor_id AND " .
			"      b.person_id=c.person_id ";
    if ($OnlyActive)
    {			
        $sql = $sql .			
    			"AND user_level != $UserLevelDisabled ";
    }
    $sql = $sql .			
            "ORDER by last_name";		
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {    	
        // build the select HTML	
		echo "<SELECT NAME='" . $ControlName . "' " . 
		            "id='" . $ControlName . "' ";
    	if (strlen($JavascriptHandler) > 0)
    	    echo "onChange='$JavascriptHandler()'>";
    	else
		    echo ">";
		
		// do we need the "None" option?
		if ($AddNoneOption)
		{
            // put "None" as a choice
    		echo "<OPTION " .
    				"VALUE='" . "None" . "'" . 
    				("None" == UCase($InstructorName) ? " SELECTED" : "") . 
    				">None";
		}
		
		// build the selection entries
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
			echo "<OPTION ";
			
			// should we output the username when selected?
			if ($UseUsername)
			    echo "VALUE='" . $row[1] . "'"; 
			else
			    echo "VALUE='" . $row[0] . "'"; 
			echo (Ucase($row[0]) == UCase($InstructorName) ? " SELECTED" : "");
		    if ($IncludeUsername)
		    {
                echo ">" . Left("(" . $row[1] . ") " . $row[3] . ", " . $row[2], $NameSize);
            }
		    else
		    {
                echo ">" . Left($row[3] . ", " . $row[2], $NameSize);
            }
		}
		echo "</SELECT>";	  			
  	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("BuildMemberSelector", $sql);
    }
}
 
//********************************************************************
// BuildClearingAuthoritySelector(
//                      InstructorName, 
//                      AddNoneOption, 
//                      ControlPrefix, 
//                      NameSize,
//                      OnlyActive,
//                      IncludeUsername,
//                      JavascriptHandler,
//                      UseUsername)
//
// Purpose: Display a selector for the list of clearing authorities.
//
// Inputs:
//   InstructorName - selected user in the list
//   AddNoneOption - optional. set true to add "None" as a selection
//                   option.
//   ControlName - optional. name of the control. if it is not set,
//                   the default is InstructorName.
//   NameSize - optional. number of characters to display in the name
//   OnlyActive - optional. set true to only include the active members
//   IncludeUsername - optional. set true to include the username in the
//                   drop down list (display only).
//   JavascriptHandler - optional. set to the name of the onchange function
//   UseUsername - optional. set to true to produce the username instead of the
//                 instructor name when a new item is selected.
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildClearingAuthoritySelector(
                            $InstructorName, 
                            $AddNoneOption = false, 
                            $ControlName = "InstructorName",
                            $NameSize = 20,
                            $OnlyActive = true,
                            $IncludeUsername = false,
                            $JavascriptHandler = "",
                            $UseUsername = false)
{
	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
	global $UserLevelMaintenance, $UserLevelAdmin;
    global $DatabaseNameFormat;
    global $AircraftScheduleType, $InstructorScheduleType;
    
    // build the user name list
    $sql = "SELECT $DatabaseNameFormat, username, first_name, last_name " . 
           "FROM AircraftScheduling_person  " . 
           "WHERE ((Clearing_Authority=1 OR " .
                    "INSTR(Rules_Field, 'Rating,CFI') OR " .
                    "INSTR(Rules_Field, 'Rating,CFII')) AND " .
                    "INSTR(Rules_Field, 'Member_Status,Active')) ";
    if ($OnlyActive)
    {			
        $sql = $sql .			
    			"AND user_level != $UserLevelDisabled ";
    }
    $sql = $sql .			
            "ORDER by last_name";		
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {    	
        // build the select HTML	
		echo "<SELECT NAME='" . $ControlName . "' " . 
		            "id='" . $ControlName . "' ";
    	if (strlen($JavascriptHandler) > 0)
    	    echo "onChange='$JavascriptHandler()'>";
    	else
		    echo ">";
		
		// do we need the "None" option?
		if ($AddNoneOption)
		{
            // put "None" as a choice
    		echo "<OPTION " .
    				"VALUE='" . "None" . "'" . 
    				("None" == UCase($InstructorName) ? " SELECTED" : "") . 
    				">None";
		}
		
		// build the selection entries
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
			echo "<OPTION ";
			
			// should we output the username when selected?
			if ($UseUsername)
			    echo "VALUE='" . $row[1] . "'"; 
			else
			    echo "VALUE='" . $row[0] . "'"; 
			echo (Ucase($row[0]) == UCase($InstructorName) ? " SELECTED" : "");
		    if ($IncludeUsername)
		    {
                echo ">" . Left("(" . $row[1] . ") " . $row[3] . ", " . $row[2], $NameSize);
            }
		    else
		    {
                echo ">" . Left($row[3] . ", " . $row[2], $NameSize);
            }
		}
		echo "</SELECT>";	  			
  	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("BuildMemberSelector", $sql);
    }
}

//********************************************************************
// BuildPilotIdentificationSelector(Rating, $GenerateJavascript, $AddAllOption)
//
// Purpose: Display a selector for the list of pilot types.
//
// Inputs:
//   Rating - current pilot type 
//   GenerateJavascript - set true to generate the onchange function
//   AddAllOption - optional. set true to add an all option to the list
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildPilotIdentificationSelector(
                                          $Rating, 
                                          $GenerateJavascript = true,
                                          $AddAllOption = false)
{
    global $NoCurrencyPilot, $StudentPilot, $PrivatePilotUnder200;
    global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;

    // build the select HTML	
	echo "<SELECT NAME='Rating' id='Rating' ";
	if ($GenerateJavascript)
	    echo "onChange='document.forms[\"main\"].submit()'>";
	else
	    echo ">";
	
	// build the selection entries
	if ($AddAllOption)
	{
    	echo "<OPTION " .
    			"VALUE='All'" . 
    			("All" == $Rating ? " SELECTED" : "") . 
    			">All";
	}
	echo "<OPTION " .
			"VALUE='" . $NoCurrencyPilot . "'" . 
			($NoCurrencyPilot == $Rating ? " SELECTED" : "") . 
			">" . $NoCurrencyPilot;
	echo "<OPTION " .
			"VALUE='" . $StudentPilot . "'" . 
			($StudentPilot == $Rating ? " SELECTED" : "") . 
			">" . $StudentPilot;
	echo "<OPTION " .
			"VALUE='" . $PrivatePilotUnder200 . "'" . 
			($PrivatePilotUnder200 == $Rating ? " SELECTED" : "") . 
			">" . $PrivatePilotUnder200;
	echo "<OPTION " .
			"VALUE='" . $PrivatePilotOver200 . "'" . 
			($PrivatePilotOver200 == $Rating ? " SELECTED" : "") . 
			">" . $PrivatePilotOver200;
	echo "<OPTION " .
			"VALUE='" . $InstrumentPilot . "'" . 
			($InstrumentPilot == $Rating ? " SELECTED" : "") . 
			">" . $InstrumentPilot;
	echo "<OPTION " .
			"VALUE='" . $CFIPilot . "'" . 
			($CFIPilot == $Rating ? " SELECTED" : "") . 
			">" . $CFIPilot;
    
    // end the select entry
	echo "</SELECT>";	
}

//********************************************************************
// BuildMemberStatusSelector(MemberStatus, AddAllOption)
//
// Purpose: Display a selector for the list of member status.
//
// Inputs:
//   MemberStatus - current member status
//   AddAllOption - optional. set true to add an all option to the list
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildMemberStatusSelector($MemberStatus, $AddAllOption = false)
{
    global $MemberStatusActive, $MemberStatusInActive, $MemberStatusAircraft, $MemberStatusResigned;

    // build the select HTML	
	echo "<SELECT NAME='Member_Status' id='Member_Status'>";
	
	// build the selection entries
	if ($AddAllOption)
	{
    	echo "<OPTION " .
    			"VALUE='All'" . 
    			("All" == $Rating ? " SELECTED" : "") . 
    			">All";
	}
	echo "<OPTION " .
			"VALUE='" . $MemberStatusActive . "'" . 
			($MemberStatusActive == $MemberStatus ? " SELECTED" : "") . 
			">" . $MemberStatusActive;
	echo "<OPTION " .
			"VALUE='" . $MemberStatusInActive . "'" . 
			($MemberStatusInActive == $MemberStatus ? " SELECTED" : "") . 
			">" . $MemberStatusInActive;
	echo "<OPTION " .
			"VALUE='" . $MemberStatusAircraft . "'" . 
			($MemberStatusAircraft == $MemberStatus ? " SELECTED" : "") . 
			">" . $MemberStatusAircraft;
	echo "<OPTION " .
			"VALUE='" . $MemberStatusResigned . "'" . 
			($MemberStatusResigned == $MemberStatus ? " SELECTED" : "") . 
			">" . $MemberStatusResigned;
    
    // end the select entry
	echo "</SELECT>";	
}

//********************************************************************
// BuildMemberSortSelector($SortSelection)
//
// Purpose: Display a selector for member report sorting.
//
// Inputs:
//   SortSelection - current report sorting to select
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildMemberSortSelector($SortSelection)
{
    // build the select HTML	
	echo "<SELECT NAME='UserSortSelection' id='UserSortSelection'>";
			
	// user name
	echo "<OPTION " .
			"VALUE='username'" . 
			(Ucase($SortSelection) == UCase("username") ? " SELECTED" : "") . 
			">KeyCode";

	// First Name
	echo "<OPTION " .
			"VALUE='first_name'" . 
			(Ucase($SortSelection) == UCase("first_name") ? " SELECTED" : "") . 
			">First Name";
	
	// Middle Initial
	echo "<OPTION " .
			"VALUE='middle_name'" . 
			(Ucase($SortSelection) == UCase("middle_name") ? " SELECTED" : "") . 
			">Middle Initial";
	
	// Last Name
	echo "<OPTION " .
			"VALUE='last_name'" . 
			(Ucase($SortSelection) == UCase("last_name") ? " SELECTED" : "") . 
			">Last Name";
	
	// Address
	echo "<OPTION " .
			"VALUE='address1'" . 
			(Ucase($SortSelection) == UCase("address1") ? " SELECTED" : "") . 
			">Address";
	
	// City
	echo "<OPTION " .
			"VALUE='city'" . 
			(Ucase($SortSelection) == UCase("city") ? " SELECTED" : "") . 
			">City";
	
	// State
	echo "<OPTION " .
			"VALUE='state'" . 
			(Ucase($SortSelection) == UCase("state") ? " SELECTED" : "") . 
			">State";
	
	// Zip
	echo "<OPTION " .
			"VALUE='zip'" . 
			(Ucase($SortSelection) == UCase("zip") ? " SELECTED" : "") . 
			">Zip";
	
	// Phone 2
	echo "<OPTION " .
			"VALUE='phone_number'" . 
			(Ucase($SortSelection) == UCase("phone_number") ? " SELECTED" : "") . 
			">Phone 1";
	
	// Phone 1
	echo "<OPTION " .
			"VALUE='Home_Phone'" . 
			(Ucase($SortSelection) == UCase("Home_Phone") ? " SELECTED" : "") . 
			">Phone 2";

    // end the select box
	echo "</SELECT>";	
}
        
//********************************************************************
// BuildAircraftSelector(
//                       TailNumber, 
//                       AddAllOption,
//                       ControlPrefix)
//
// Purpose: Display a selector for the list of aircraft.
//
// Inputs:
//   TailNumber - current tailnumber to select
//   AddAllOption - optional. set true to add an all option to the list
//   ControlPrefix - optional. prefix for the control name so that
//                   the name becomes $ControlPrefix . TailNumber.
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildAircraftSelector(
                                $TailNumber,
                                $AddAllOption = false,
                                $ControlPrefix = "")

{
    // build the aircraft tailnumber filter
	$sql = 
			"SELECT " .
			    "n_number " .
			"FROM " .
			    "AircraftScheduling_aircraft " .
            "ORDER by n_number";		
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {    	
        // build the select HTML	
		echo "<SELECT NAME='" . $ControlPrefix . "TailNumber' " . 
		            "id='" . $ControlPrefix . "TailNumber'>";
		
		// add an option for all
    	if ($AddAllOption)
    	{
    		echo "<OPTION " .
    				"VALUE='All'" . 
    				(Ucase($TailNumber) == UCase("All") ? " SELECTED" : "") . 
    				">All";
    	}
		
		// build the selection entries
		for($i=0; $row = sql_row($res, $i); $i++) 
		{    		    
			// build the option value
			echo "<OPTION " .
					"VALUE='" . $row[0] . "'" . 
					(Ucase($row[0]) == UCase($TailNumber) ? " SELECTED" : "") . 
					">$row[0]";
		}

        // end the select box
		echo "</SELECT>";	
  	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("BuildAircraftSelector", $sql);
    }
}

//********************************************************************
// BuildAircraftSortSelector($SortSelection)
//
// Purpose: Display a selector for aircraft report sorting.
//
// Inputs:
//   SortSelection - current report sorting to select
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildAircraftSortSelector($SortSelection)
{
    // build the select HTML	
	echo "<SELECT NAME='AircraftSortSelection' id='AircraftSortSelection'>";
			
	// tail number
	echo "<OPTION " .
			"VALUE='n_number'" . 
			(Ucase($SortSelection) == UCase("n_number") ? " SELECTED" : "") . 
			">Tail Number";
	echo "<OPTION " .
			"VALUE='code_id'" . 
			(Ucase($SortSelection) == UCase("code_id") ? " SELECTED" : "") . 
			">Code ID";
	echo "<OPTION " .
			"VALUE='make_id'" . 
			(Ucase($SortSelection) == UCase("make_id") ? " SELECTED" : "") . 
			">Make";
	echo "<OPTION " .
			"VALUE='model_id'" . 
			(Ucase($SortSelection) == UCase("model_id") ? " SELECTED" : "") . 
			">Model";
	echo "<OPTION " .
			"VALUE='Aircraft_Owner_Name'" . 
			(Ucase($SortSelection) == UCase("Aircraft_Owner_Name") ? " SELECTED" : "") . 
			">Aircraft Owner Name";

    // end the select box
	echo "</SELECT>";	
}

//********************************************************************
// BuildFlightSortSelector($SortSelection)
//
// Purpose: Display a selector for flight report sorting.
//
// Inputs:
//   SortSelection - current report sorting to select
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildFlightSortSelector($SortSelection)
{
    // build the select HTML	
	echo "<SELECT NAME='FlightSortSelection' id='FlightSortSelection'>";
			
	// tail number
	echo "<OPTION " .
			"VALUE='Keycode'" . 
			(Ucase($SortSelection) == UCase("Keycode") ? " SELECTED" : "") . 
			">Username";
	echo "<OPTION " .
			"VALUE='Date'" . 
			(Ucase($SortSelection) == UCase("Date") ? " SELECTED" : "") . 
			">Date";
	echo "<OPTION " .
			"VALUE='Aircraft'" . 
			(Ucase($SortSelection) == UCase("Aircraft") ? " SELECTED" : "") . 
			">Aircraft";
	echo "<OPTION " .
			"VALUE='model_id'" . 
			(Ucase($SortSelection) == UCase("model_id") ? " SELECTED" : "") . 
			">Model";

    // end the select box
	echo "</SELECT>";	
}

//********************************************************************
// BuildChargeSortSelector($SortSelection)
//
// Purpose: Display a selector for charge report sorting.
//
// Inputs:
//   SortSelection - current report sorting to select
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildChargeSortSelector($SortSelection)
{
    // build the select HTML	
	echo "<SELECT NAME='ChargeSortSelection' id='ChargeSortSelection'>";
			
	// tail number
	echo "<OPTION " .
			"VALUE='Keycode'" . 
			(Ucase($SortSelection) == UCase("Keycode") ? " SELECTED" : "") . 
			">Username";
	echo "<OPTION " .
			"VALUE='Date'" . 
			(Ucase($SortSelection) == UCase("Date") ? " SELECTED" : "") . 
			">Date";

    // end the select box
	echo "</SELECT>";	
}

//********************************************************************
// BuildInventorySortSelector($SortSelection)
//
// Purpose: Display a selector for inventory report sorting.
//
// Inputs:
//   SortSelection - current report sorting to select
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildInventorySortSelector($SortSelection)
{
    // build the select HTML	
	echo "<SELECT NAME='InventorySortSelection' id='InventorySortSelection'>";
			
	// tail number
	echo "<OPTION " .
			"VALUE='Date'" . 
			(Ucase($SortSelection) == UCase("Date") ? " SELECTED" : "") . 
			">Date";
	echo "<OPTION " .
			"VALUE='Part_Number'" . 
			(Ucase($SortSelection) == UCase("Part_Number") ? " SELECTED" : "") . 
			">Part Number";
	echo "<OPTION " .
			"VALUE='Description'" . 
			(Ucase($SortSelection) == UCase("Description") ? " SELECTED" : "") . 
			">Description";
	echo "<OPTION " .
			"VALUE='Unit_Price'" . 
			(Ucase($SortSelection) == UCase("Unit_Price") ? " SELECTED" : "") . 
			">Unit Price";
	echo "<OPTION " .
			"VALUE='Retail_Price'" . 
			(Ucase($SortSelection) == UCase("Retail_Price") ? " SELECTED" : "") . 
			">Retail Price";
	echo "<OPTION " .
			"VALUE='Quantity_In_Stock'" . 
			(Ucase($SortSelection) == UCase("Quantity_In_Stock") ? " SELECTED" : "") . 
			">Quantity In Stock";
	echo "<OPTION " .
			"VALUE='Reorder_Quantity'" . 
			(Ucase($SortSelection) == UCase("Reorder_Quantity") ? " SELECTED" : "") . 
			">Reorder Quantity";
	echo "<OPTION " .
			"VALUE='Position'" . 
			(Ucase($SortSelection) == UCase("Position") ? " SELECTED" : "") . 
			">Position";
	echo "<OPTION " .
			"VALUE='Category'" . 
			(Ucase($SortSelection) == UCase("Category") ? " SELECTED" : "") . 
			">Category";
	echo "<OPTION " .
			"VALUE='Inventory_Type'" . 
			(Ucase($SortSelection) == UCase("Inventory_Type") ? " SELECTED" : "") . 
			">Inventory Type";

    // end the select box
	echo "</SELECT>";	
}

//********************************************************************
// BuildAircraftRentalSelector($RentalSelection)
//
// Purpose: Display a selector for aircraft rental status.
//
// Inputs:
//   RentalSelection - current report rental status to select
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildAircraftRentalSelector($RentalSelection)
{
    // build the select HTML	
	echo "<SELECT NAME='AircraftRentalSelection' id='AircraftRentalSelection'>";
			
	// tail number
	echo "<OPTION " .
			"VALUE='All'" . 
			(Ucase($RentalSelection) == UCase("All") ? " SELECTED" : "") . 
			">All";
	echo "<OPTION " .
			"VALUE='Rental'" . 
			(Ucase($RentalSelection) == UCase("Rental") ? " SELECTED" : "") . 
			">Rental";
	echo "<OPTION " .
			"VALUE='NonRental'" . 
			(Ucase($RentalSelection) == UCase("NonRental") ? " SELECTED" : "") . 
			">Non Rental";

    // end the select box
	echo "</SELECT>";	
}
    
//********************************************************************
// BuildStatusSelector(
//                      Status,
//                      AddAllOption,
//                      ControlPrefix)
//
// Purpose: Display a selector for the list of aircraft status.
//
// Inputs:
//   Status - currently selected status
//   AddAllOption - optional. set true to add an all option to the list
//   ControlPrefix - optional. prefix for the control name so that
//                   the name becomes $ControlPrefix . status.
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function BuildStatusSelector(
                             $Status,
                             $AddAllOption = false,
                             $ControlPrefix = "")                             
{
    // build the select HTML
	echo "<SELECT NAME='" . $ControlPrefix . "status' " . 
	            "id='" . $ControlPrefix . "status'>";
		
	// add an option for all
	if ($AddAllOption)
	{
		echo "<OPTION " .
				"VALUE='All'" . 
				(Ucase($Status) == UCase("All") ? " SELECTED" : "") . 
				">All";
	}
	
	// build the selection entries
	for($i=0; $i < 5; $i++) 
	{
	    $StatusString = LookupAircraftStatusString($i);
		echo "<OPTION " .
				"VALUE='" . $StatusString . "'" . 
				($StatusString == $Status ? " SELECTED" : "") . 
				">" . $StatusString;
	}
	echo "</SELECT>";	
}

?>
