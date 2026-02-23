<?php
//-----------------------------------------------------------------------------
// 
// DateFunctions
// 
// PURPOSE: Provides the functions for manipulating dates.
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
require_once("adodb-time.inc.php");

// constants
$SecondsInDay = (24 * 60 * 60); // number of seconds in a day

//********************************************************************
// GetDayMonthYear($Date)
//
// Purpose: Returns the day, month and year of the input date. The 
//          format of the input field may be YYYY-MM-DD or DD-MMM-YYYY.
//
// Inputs:
//   Date - day, month and year in the format YYYY-MM-DD ,DD-MMM-YYYY,
//          MM/DD/YY or MM/DD/YYYY
//
// Outputs:
//   none
//
// Returns:
//   An array of day, month and year.
//*********************************************************************
function GetDayMonthYear($Date)
{ 
    global $SecondsInDay;
    global $NullDate;
       
    $DayMonthYearArray[] = array(0 => " ", 1 => " ", 2 => " ");
	$First = " ";
	$Second = " ";
	$Third = " ";
    
    // split the date field into date and time if needed
    if (InStr(1, $Date, ":") > 0)
    {
        // date has time in it, get just the year month and date
        list($YearMonthDay, $HourMinuteSeconds) = preg_split("/ /", $Date);
    }
    else
    {
        // date is just the year month and date
        $YearMonthDay = $Date;
    }
    
    // is the format YYYY-MM-DD or DD-MMM-YYYY?
    if (InStr(1, $YearMonthDay, "-") > 0)
    {
        // format is YYYY-MM-DD or DD-MMM-YYYY
        // split the date fields
        list($First, $Second, $Third) = preg_split("/-/", $YearMonthDay);
        
        // determine the format (YYYY-MM-DD or DD-MMM-YYYY)
        if (strlen($First) == 4)
        {
            // format is YYYY-MM-DD
            $DayMonthYearArray[0] = $Third;
            $DayMonthYearArray[1] = $Second;
            $DayMonthYearArray[2] = $First;
        }
        else
        {
            // format is DD-MMM-YYYY
            $DayMonthYearArray[0] = $First;
            $DayMonthYearArray[1] = $Second;
            $DayMonthYearArray[2] = $Third;
        }
    }
    else if (InStr(1, $YearMonthDay, " ") > 0)
    {
        // format is YYYY MM DD or DD MMM YYYY
        // split the date fields
        list($First, $Second, $Third) = preg_split("/ /", $YearMonthDay);
        
        // determine the format (YYYY MM DD or DD MMM YYYY)
        if (strlen($First) == 4)
        {
            // format is YYYY MM DD
            $DayMonthYearArray[0] = $Third;
            $DayMonthYearArray[1] = $Second;
            $DayMonthYearArray[2] = $First;
        }
        else
        {
            // format is DD MMM YYYY
            $DayMonthYearArray[0] = $First;
            $DayMonthYearArray[1] = $Second;
            $DayMonthYearArray[2] = $Third;
        }
    }
    else if (InStr(1, $YearMonthDay, "/") > 0)
    {
        // format is MM/DD/YY
        // split the date fields
       list($First, $Second, $Third) = preg_split("/\//", $YearMonthDay);
        $DayMonthYearArray[0] = $Second;
        $DayMonthYearArray[1] = $First;
       $DayMonthYearArray[2] = $Third;
    }
    else
    {
        // invalid date, give them the default
        list($First, $Second, $Third) = preg_split("/-/", $NullDate);
        $DayMonthYearArray[0] = $First;
        $DayMonthYearArray[1] = $Second;
        $DayMonthYearArray[2] = $Third;
    }
    
    // return the values
    return $DayMonthYearArray;
}

//********************************************************************
// DateSerial(year, month, day)
//
// Purpose: Returns a date for a specified year, month, and day.
//
// Inputs:
//   year - Number between 100 and 9999, inclusive, or anumeric expression. 
//   month - Any numeric expression. 
//   day - Any numeric expression. 
//
// Outputs:
//   none
//
// Returns:
//   The requested characters
//*********************************************************************
function DateSerial($Year, $Month, $Day)
{    
    global $debug_flag;
    global $SecondsInDay;
       
    $ReturnValue = adodb_mktime(0, 0, 0, $Month, $Day, $Year) / $SecondsInDay;
    return $ReturnValue;
}

//********************************************************************
// DateValue(Date)
//
// Purpose: The required date argument is normally a string expression 
//          representing a date from January 1, 100 through 
//          December 31, 9999. However, date can also be any expression
//          that can represent a date, a time, or both a date and time, 
//          in that range.
//
// Inputs:
//   Date - the input date to convert 
//
// Outputs:
//   none
//
// Returns:
//   The converted date.
//*********************************************************************
function DateValue($Date)
{
    global $debug_flag;
    global $SecondsInDay;
           
    // if we have string value, convert it to a date
    if (is_string($Date))
    {
        // string date, convert it to a serial date
        if (Ucase($Date) == "NOW")
        {
            // get the current date
            $ReturnValue = adodb_mktime(0, 0, 0, adodb_date("m"), adodb_date("d"), adodb_date("Y")) /
                                            $SecondsInDay;
        }
        else
        {
            // get the day, month and year
            list($Day, $Month, $Year) = GetDayMonthYear($Date);
            
            // if it is a numeric month, don't convert it
            if ($Month >= 1 && $Month <= 12)
            {
                $Month = $Month;
            }
            else
            {
                // convert the month to a number (Jan - > 1, Feb ->2, etc.)
                $Month = date("m", strtotime("$Month 1, 2000"));
            }
            $ReturnValue = adodb_mktime(0, 0, 0, $Month, $Day, $Year) / $SecondsInDay;
        }
    }
    else
    {
        // not a string date, must be a serial date
        $ReturnValue = $Date;
    }

    // return the results
    return $ReturnValue;
}

//********************************************************************
// GetShortMonth(MonthNumber)
//
// Purpose: Returns the short name of the month given the month number.
//
// Inputs:
//   MonthNumber - the input date to convert (in days)
//
// Outputs:
//   The short name (three characters) of the month
//
// Returns:
//   The numerical value for the month.
//*********************************************************************
function GetShortMonth($MonthNumber)
{
    global $debug_flag;
    $ShortMonths = array("Jan", "Feb", "Mar", "Apr",
    						"May", "Jun", "Jul", "Aug", "Sept",
    						"Oct", "Nov", "Dec");
           
    // return the short name of the month
    return $ShortMonths[$MonthNumber - 1];
}

//********************************************************************
// Day(Date)
//
// Purpose: Returns an integer containing a whole number representing
//          the day of the month.
//
// Inputs:
//   Date - the input date to convert (in days)
//
// Outputs:
//   none
//
// Returns:
//   The numerical value for the day.
//*********************************************************************
function Day($Date)
{
    global $debug_flag;
    global $SecondsInDay;
           
    // if we have string value, convert it to a date
    if (is_string($Date))
    {
         if (Ucase($Date) == "NOW")
        {
            // get the current date
            $ReturnValue = adodb_date("d");
        }
        else
        {
            // string date, convert it to a serial date
            list($Day, $Month, $Year) = GetDayMonthYear($Date);
            $ReturnValue = $Day;
        }
    }
    else
    {
        // not a string date, must be a serial date
        $ReturnValue = adodb_date("d", $Date * $SecondsInDay);
    }
    
    // return the result
    return $ReturnValue;
}

//********************************************************************
// Month(Date)
//
// Purpose: Returns an integer containing a whole number representing
//          the month.
//
// Inputs:
//   Date - the input date to convert (in days)
//
// Outputs:
//   none
//
// Returns:
//   The numerical value for the month.
//*********************************************************************
function Month($Date)
{
    global $debug_flag;
    global $SecondsInDay;
           
    // if we have string value, convert it to a date
    if (is_string($Date))
    {
        if (Ucase($Date) == "NOW")
        {
            // get the current date
            $ReturnValue = adodb_date("m");
        }
        else
        {
            // string date, convert it to a serial date
            list($Day, $Month, $Year) = GetDayMonthYear($Date);
            
            // if it is a numeric month, don't convert it
            if ($Month >= 1 && $Month <= 12)
            {
                $ReturnValue = $Month;
            }
            else
            {
                // don't care about anything except the month so use the
                // internal convertor (Jan - > 1, Feb ->2, etc.)
                $ReturnValue = adodb_date("m", strtotime("$Month 1, 2000"));
            }
        }
    }
    else
    {
        // not a string date, must be a serial date
        $ReturnValue = adodb_date("m", $Date * $SecondsInDay);
    }
    
    // return the result
    return $ReturnValue;
}

//********************************************************************
// Year(Date)
//
// Purpose: Returns an integer containing a whole number representing
//          the year
//
// Inputs:
//   Date - the input date to convert (in days)
//
// Outputs:
//   none
//
// Returns:
//   The numerical value for the year.
//*********************************************************************
function Year($Date)
{
    global $debug_flag;
    global $SecondsInDay;
       
    // if we have string value, convert it to a date
    if (is_string($Date))
    {
        if (Ucase($Date) == "NOW")
        {
            // get the current date
            $ReturnValue = adodb_date("Y");
        }
        else
        {
            // string date, convert it to a serial date
            list($Day, $Month, $Year) = GetDayMonthYear($Date);
            $ReturnValue = $Year;
        }
    }
    else
    {
        // not a string date, must be a serial date
        $ReturnValue = adodb_date("Y", $Date * $SecondsInDay);
    }
    
    // return the result
    return $ReturnValue;
}

//********************************************************************
// $FormatDate(Date)
//
// Purpose: Returns a string in the format dd-MMM-yyyy for the given 
//          date.
//
// Inputs:
//   Date - the input date to convert 
//
// Outputs:
//   none
//
// Returns:
//   The converted date.
//*********************************************************************
function FormatDate($Date)
{
    global $SecondsInDay;
       
    return
            adodb_date("d", $Date * $SecondsInDay) . "-" .
            adodb_date("M", $Date * $SecondsInDay) . "-" . 
            adodb_date("Y", $Date * $SecondsInDay); 
}

//********************************************************************
// $IsValidDate(Date)
//
// Purpose: Returns TRUE if the input date is a valid date, FALSE 
//          otherwise.
//
// Inputs:
//   Date - the input date to test 
//
// Outputs:
//   none
//
// Returns:
//   TRUE if the input date is a valid date, FALSE otherwise.
//*********************************************************************
function IsValidDate($Date)
{
    global $SecondsInDay;
       
    // if we have string value, convert it to a date
    if (is_string($Date))
    {
        // string date
        if (Ucase($Date) == "NOW") 
            $ValidDate = True;
        elseif (strlen($Date) < 10) 
            $ValidDate = False;
        else
        {
            // attempt to convert the date
            $SerialDate = adodb_mktime(0, 0, 0, Month($Date), Day($Date), Year($Date));
            
            // if we had a bad return value, it is not a date
            if ($SerialDate === -1)
                $ValidDate = False;
            else
                $ValidDate = True;
        }
    }
    else
    {
        // serial date
        if ($Date < adodb_mktime(0, 0, 0, 1, 1, 1900))
            $ValidDate = False;
        else
            $ValidDate = True;
    }
    
    // return the results
    return $ValidDate;
}

//********************************************************************
// BuildDate($FlightDay, $FlightMonth, $FlightYear)
//
// Purpose: Return the date in the format DD-MMM-YYYY.
//
// Inputs:
//   FlightDay - number of the day of the month
//   FlightMonth - number of the month of the year
//   FlightYear - four digit year
//
// Outputs:
//   none
//
// Returns:
//   The converted date.
//*********************************************************************
function BuildDate($FlightDay, $FlightMonth, $FlightYear)
{
    return strftime("%d-%b-%Y", mktime(0, 0, 0, $FlightMonth, $FlightDay, $FlightYear));
}

?>
