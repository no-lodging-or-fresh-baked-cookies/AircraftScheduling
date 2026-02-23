<?php
//-----------------------------------------------------------------------------
// 
// CombinationLock.php
// 
// PURPOSE: Displays the combination to the aircraft lock box.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
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
    $TailNumber = '';
    $CurrentlySelectedAircraftType = '';
    $CurrentlySelectedTailnumber = '';
    $ClearingAuthorityKeyCodeTextBox = '';
    $WBFieldString = '';
    
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
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    # if we dont know the right date then make it up 
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal) && $User_Must_Login)
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "WaitScreenTimeout");
	
	// get the combination from the database
	$KeyLockCombinationText = GetServerPreferenceValue("Lock_Combination");
	
	// get the time to display the combination from the database and 
	// save it for the javascript
	$PauseTime = GetServerPreferenceValue("CombinationLock_Display_Seconds");
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var PauseTime = $PauseTime;";
    echo "</SCRIPT>";

	echo "<center>";

	// display the lock box combination
	echo "<br>";
	echo "<table border=6>";
	echo "<tr><td><h1>Aircraft Key Lock Combination Is:</h1><br>";
	echo "<center><h1>$KeyLockCombinationText</h1></td></tr>";
	echo "</table>";

    
    // space between combination and OK button
  	echo "<br>";

    // generate the Ok button to move to the next screen
    echo "<input name='AircraftCheckout' type=submit value='OK' ONCLICK='GotoNextScreen()'>";

    // if the administrator wants us to logoff after checking out
    if ($LogoffAfterCheckInOut)
    {     
        // save the logoff URL for the javascript code
        echo "<SCRIPT LANGUAGE=\"JavaScript\">";
        echo "var NextURL = 'logoff.php';";
        echo "</SCRIPT>";
    }
    else
    {
        // save the goback URL for the javascript code
        echo "<SCRIPT LANGUAGE=\"JavaScript\">";
        echo "var NextURL = '" . $goback . CleanGoBackParameters($GoBackParameters) . "';";
        echo "</SCRIPT>";
    }
    
	echo "</center>";
    
    include "trailer.inc";
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// WaitScreenTimeout()
//
// Purpose: Show the screen for the number of seconds requested.
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
function WaitScreenTimeout()
{
    var EndTime = null;
    
    // compute the endtime in miliseconds
    EndTime = 1000 * PauseTime;
    
    // wait for the timer to expire and call the logoff handler
    setTimeout("GotoNextScreen()", EndTime);
	return true;
}

//********************************************************************
// GotoNextScreen()
//
// Purpose: If the user has requested, logoff or return to the previous
//          screen.
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
function GotoNextScreen()
{
    window.location.href = NextURL;
    return true;
}

//-->
</script>
