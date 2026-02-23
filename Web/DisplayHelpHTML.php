<?php
//-----------------------------------------------------------------------------
// 
// DisplayHelpHTML.php
// 
// PURPOSE: A PHP wrapper around the help HTML files so that we get
//          the header, trailer and CSS information.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      HelpHTMLFile - name of the HTML help file to display.
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
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    if(isset($rdata["HelpHTMLFile"])) $HelpHTMLFile = $rdata["HelpHTMLFile"];
    
    // if we don't know the right date then make it up
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
    
    // include the HTML file
    include $HelpHTMLFile;

    // generate return URL if we are not displaying this file
    if ($HelpHTMLFile != "Help/TableOfContents.inc")
    {
        echo "<p>";
        echo "<a href='DisplayHelpHTML.php?HelpHTMLFile=Help/TableOfContents.inc'>" . 
                "Back to the Help Table of Contents</a>";
        echo "</p>";            
    }
    echo "<br>";
    
    // display the trailer
    include "trailer.inc";
?>
