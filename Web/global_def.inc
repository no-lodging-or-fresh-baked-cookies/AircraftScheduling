<?php
//-----------------------------------------------------------------------------
// 
// global_def.inc
// 
// PURPOSE: Contains global definitions for variables.
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

# site configuration information (usernames, passwords, etc.)
include 'SiteSpecific.inc';

// initialize the session
if ($SaveSessionCookiesLocally) 
{
	// only needed on servers that don't have session cookies in a writable
	// directory	
	session_save_path("../tmp");
}
session_start();

// if the REQUEST_URI variable is not available
if (!isset($_SERVER['REQUEST_URI'])) 
       $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];


##########################################
# Set the server timezone to Eastern since
# PHP < 7.0 used that as a default
##########################################
date_default_timezone_set("America/New_York");

// initialize variables
if(!isset($_SESSION["usessl"])) $_SESSION["usessl"] = false;
if(!isset($_SESSION["counter"])) $_SESSION["counter"] = 0;
if(!isset($_SESSION["ActiveUserLevel"])) $_SESSION["ActiveUserLevel"] = 0;
if(!isset($_SESSION["ActiveFirstName"])) $_SESSION["ActiveFirstName"] = '';
if(!isset($_SESSION["ActiveLastName"])) $_SESSION["ActiveLastName"] = '';
if(!isset($_SESSION["ActivePhoneNumber"])) $_SESSION["ActivePhoneNumber"] = '';
if(!isset($_SESSION["authenticated"])) $_SESSION["authenticated"] = false;
if(!isset($_SESSION["ActiveUsername"])) $_SESSION["ActiveUsername"] = '';
if(!isset($_SESSION["ActivePassword"])) $_SESSION["ActivePassword"] = '';
if(!isset($_SESSION["LastActivityTime"])) $_SESSION["LastActivityTime"] = strtotime("now");
if(!isset($make)) $make = '';
if(!isset($model)) $model = '';
if(!isset($add_certificate)) $add_certificate = '';
if(!isset($add_rating)) $add_rating = '';
if(!isset($add_endorsement)) $add_endorsement = '';
if(!isset($add_make)) $add_make = '';
if(!isset($add_model)) $add_model = '';
if(!isset($certificates)) $certificates = '';
if(!isset($ratings)) $ratings = '';
if(!isset($endorsements)) $endorsements = '';
if(!isset($makes)) $makes = '';
if(!isset($models)) $models = '';
if(!isset($AircraftScheduling_company)) $AircraftScheduling_company = '';
if(!isset($pilot_certificate_id)) $pilot_certificate_id = '';
if(!isset($resource)) $resource = '';
if(!isset($name)) $name = '';
if(!isset($create_by)) $create_by = '';
if(!isset($certificate)) $certificate = '';
if(!isset($resource_id)) $resource_id = '';
if(!isset($pview)) $pview = '';
if(!isset($Signin_Attempts)) $Signin_Attempts = 5;
if(!isset($Activity_Timeout)) $Activity_Timeout = 300;
if(!isset($Logon_Weather_Link)) $Logon_Weather_Link = ""; // "http://www.metvuw.com/radar/radar.php?location=nz";
if(!isset($Logon_Weather_Map_Link)) $Logon_Weather_Map_Link = ""; //"http://www.metvuw.com/forecast/2010052818/rain-nzni-2010052818-06.gif";
if(!isset($Logon_Weather_Credit)) $Logon_Weather_Credit = ""; // "Provided by MetVUW";
if(!isset($Timezone_Server)) $Timezone_Server = +12;	// server timezone hours from GMT
if(!isset($Timezone_Local)) $Timezone_Local = +12;	// local timezone hours from GMT
if(!isset($Number_Repeat_Days)) $Number_Repeat_Days = 90;	// maximum days allowed in a repeat schedule
if(!isset($MaxScheduleDaysAllowed)) $MaxScheduleDaysAllowed = 0;	// maximum days in the future to allow scheduling
if(!isset($User_Must_Login)) $User_Must_Login = 1;	// Set to 1 to force user to login to view schedule

#If we dont know the right date then make it up 
if (!isset($day) or !isset($month) or !isset($year))
{
	$day   = date("d");
	$month = date("m");
	$year  = date("Y");
} 
else
{
    // make sure the date values are valid
    if (empty($day))
    	$day   = date("d");
    if (empty($month))
    	$month = date("m");
    if (empty($year))
	    $year  = date("Y");

    # Make the date valid if day is more then number of days in month
	while (!checkdate($month, $day, $year))
		$day--;
}

# Constants for user levels
# UserLevelSuper and UserLevelAdmin must be the highest levels. UserLevelSuper
# can modify schedules of other users. UserLevelAdmin can modify schedules of other 
# users and change configuration settings.
global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
global $UserLevelMaintenance, $UserLevelAdmin;
global $UserLevelDisabledName, $UserLevelNormalName, $UserLevelSuperName, $UserLevelOfficeName; 
global $UserLevelMaintenanceName, $UserLevelAdminName;
$UserLevelDisabled = 0;			// user is disabled
$UserLevelDisabledName = "Disabled";
$UserLevelNormal = 1;			// normal user
$UserLevelNormalName = "Normal";
$UserLevelSuper = 2;			// super worker can schedule for other users
$UserLevelSuperName = "Super";
$UserLevelOffice = 3;			// office user
$UserLevelOfficeName = "Office";
$UserLevelMaintenance = 4;			// aircraft maintenance user
$UserLevelMaintenanceName = "Maintenance";
$UserLevelAdmin = 5;			// administrator
$UserLevelAdminName = "Administrator";

# Constants for schedulable_id in AircraftScheduling_schedulable
# Note that if these constants are changed, then the entries in the
# database must also be changed
global $AircraftScheduleType, $InstructorScheduleType;
$AircraftScheduleType = 1;			// aircraft type
$InstructorScheduleType = 2;		// instructor type

# Set the default hourly rate for the instructors
$Default_Instructor_Rate = 23.00;		// hourly rate in dollars

# Constants used for AircraftScheduling_entry entry_type
global $EntryTypeStandby, $EntryTypeNormal, $EntryTypeRepeating, $EntryTypeModified;
$EntryTypeStandby = -1;			// standby request entry
$EntryTypeNormal = 0;			// normal, non-repeating entry
$EntryTypeRepeating = 1;		// repeating entry
$EntryTypeModified = 2;			// modified repeating entry

# used to adjust the modification, created and journal times for the
# local time zone. values are hours from GMT
global $Timezone_Server, $Timezone_Local;

# maximum number of repeat days allowed to schedule into the future
global $Number_Repeat_Days;

# if set to 1, the user must login to view the schedule
# if set to 0, user may view the schedule,must login to change anything
global $User_Must_Login;

# relative directory path for the image files (set in config.inc)
global $ImageRootPath;

# email authentication information (set in SiteSpecific.inc)
global $email_host, $email_username, $email_password, $email_return_address;
global $email_type;

# if set to true, the aircraft checkout, currency functions and billing are enabled.
global $AllowAircraftCheckout;

# password timeout (days) - may make a config item at a future date
global $PasswordTimeoutDays;
$PasswordTimeoutDays = 10000;

# aircraft type names
global $SimulatorAircraftType, $PCATDAircraftType;
$SimulatorAircraftType = "SIM";      // simulator aircraft type
$PCATDAircraftType = "PCATD";        // PC training device aircraft type
$SpecialAircraftTailnumber = "PRIVATE";  // special tailnumber for selling fuel

# SQL statement to format names
global $DatabaseNameFormat;
global $BuildNameFormat;
global $DisplayNameFormat;
$DatabaseNameFormat = "CONCAT(first_name, ' ', last_name)";
$BuildNameFormat = '$first $last';
$DisplayNameFormat = "CONCAT(last_name, ', ', first_name)";

# squawk fault types
global $OpenFault, $Repaired, $Deferred, $ClosedFault;
$OpenFault = 1;             // fault is still open (not fixed)
$Repaired = 2;              // fault is corrected
$Deferred = 3;              // fault is deferred
$ClosedFault = 4;           // fault was not a problem

# squawk repair status
global $NotRepairedString, $RepairedString, $DeferredString, $ClosedString;
$NotRepairedString = "Not Repaired"; // squawk has not been repaired
$RepairedString = "Repaired";        // squawk has been repaired
$DeferredString = "Deferred";        // squawk has been deferred
$ClosedString = "Closed";            // squawk has been closed (no problem)

# inventory types
global $WholesaleInventory, $RetailInventory, $MaintenanceInventory;
$WholesaleInventory = "Wholesale";     // wholesale inventory type
$RetailInventory = "Retail";           // retail inventory type
$MaintenanceInventory = "Maintenance"; // maintenance inventory type

# default amount of time to retain journal entries
$MaxJournalDays = "120 days";

# default amount of time to add for instructor P&P time
$DefaultPPTime = "0.5";