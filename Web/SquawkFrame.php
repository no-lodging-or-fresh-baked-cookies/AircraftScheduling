<?php
//-----------------------------------------------------------------------------
// 
// SquawkFrame
// 
// PURPOSE: Generates the html used within the iframe for displaying squawks.
// 
// PARAMETERS:
//      TailNumber - selected aircraft tailnumber
//      AllowModifications - set to 1 to allow modifications of the squawks
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
    require_once("SquawkFunctions.inc");
	
	global $makemodel;

    // initialize variables
    $all = '';
    $TailNumber = '';

    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    if(isset($rdata["AllowModifications"])) $AllowModifications = $rdata["AllowModifications"];
    
    // setup the background color and information
    $pview = 1;
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    // get the squawk list
    $SquawkText = BuildAircraftSquawkList($TailNumber, $AllowModifications);
            
    // display the squawks
    echo "<table id='AircraftSquawks' rules='groups'>";
    echo $SquawkText;
    echo "</table>";

?>
