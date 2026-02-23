<?php
//-----------------------------------------------------------------------------
// 
// DisplayManagerMsg.php
// 
// PURPOSE: Displays the manager's message to the user.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model resources
//      InstructorResource = selected instructor resource
//      enablessl - set true to enable SSL
//      pview - set true to build a screen suitable for printing
//      ManagerMessage - message to display to the user
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
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
    
    // initialize variables
    $InstructorResource = "";
    $goback = "";
    $GoBackParameters = "";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    if(isset($rdata["enablessl"])) $enablessl = $rdata["enablessl"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["ManagerMessage"])) $ManagerMessage = $rdata["ManagerMessage"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    
    if($enablessl && $_SESSION["usessl"] && $HTTPS != "on") { header("Location: https://" . getenv('SERVER_NAME') . $_SERVER["PHP_SELF"]);  }
    
    #If we dont know the right date then make it up 
    if(!isset($day) or !isset($month) or !isset($year))
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
    
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    // build the form so that we return to the last screen
    echo "<FORM NAME='DisplayManagerMsg' ACTION='$goback' METHOD='POST'>";
    BuildHiddenInputs(CleanGoBackParameters($GoBackParameters));
    echo "<center>";
    echo "<H1>Administrator Message</H1>";
    echo "<TD CLASS=TL>";
    echo "<TEXTAREA NAME='ManagerMessage' ROWS=8 COLS=40 WRAP='virtual'>" . 
            htmlentities ( stripslashes($ManagerMessage) ) . 
            "</TEXTAREA></TD></TR>";
    echo "<BR>";
    echo "<INPUT TYPE='submit' VALUE='OK'>";
    echo "</center>";
    echo "</FORM>";
    
    include "trailer.inc";
?>
