<?php
//-----------------------------------------------------------------------------
// 
// help.php
// 
// PURPOSE: Displays the help system.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      pview - set true to build a screen suitable for printing
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
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    
    # if we dont know the right date then make it up
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
    
    if(empty($resource))
    	$resource = get_default_resource();
    
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else { $all=1; $makemodel = "&all=1"; }
    
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    echo "<H3>About AircraftScheduling</H3>\n";
    echo "<P>$AircraftScheduling_version\n";
    echo "<BR>Database: " . sql_version() . "\n";
    echo "<BR>" . php_uname() . "\n";
    echo "<BR>PHP: " . phpversion() . "\n";
    
    echo "<H3>Help</H3>\n";
    echo 'Please contact <a href="mailto:' . $AircraftScheduling_admin_email
    	. '">' . $AircraftScheduling_admin
    	. "</a> for any questions that aren't answered here.\n";
     
    // are we still using the old help system
    include "Help/TableOfContents.inc";        
    
    include "trailer.inc";
?>
