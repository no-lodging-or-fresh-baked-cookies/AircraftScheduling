<?php
//-----------------------------------------------------------------------------
// 
// DisplayInstrutorInfo.php
// 
// PURPOSE: Displays the aircraft information screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      TailNumber - selected aircraft tailnumber
//      all - select all resources
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      This file based on Meeting Room Booking System http://mrbs.sourceforge.net
//      It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
//      12/19/2001 and released under the terms of the GNU Public License
//
//      AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
//      a lot more than AircraftScheduling so I renamed it to keep from causing confusion.
// 
// -----------------------------------------------------------------------------

    include "global_def.inc";
    include "config.inc";
    include "AircraftScheduling_auth.inc";
    include "$dbsys.inc";
    include "functions.inc";
    
    // initialize variables
    $all = '';
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["name"])) $name = $rdata["name"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal) && $User_Must_Login)
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
	
    $name = preg_replace("/_/", " ", $name);
    $name = preg_replace("/\//", "", $name);

    $sql = "SELECT $DatabaseNameFormat, hourly_cost, email, description, picture, phone_number, Home_Phone 
            FROM AircraftScheduling_person LEFT JOIN AircraftScheduling_instructors USING (person_id) 
            WHERE $DatabaseNameFormat='$name'";
    
    $res = sql_query($sql); 
    
    if($res) 
    {
        // we have good database information, display the instructor status
        $row = sql_row($res, 0);
        
        echo "<center>";
        echo "<H2>Instructor - $row[0]</H2>";
        echo "<table border=0>";
        echo "<tr><td>";
        if($row[4]) echo "<a href=\"image.php?src=$row[4]\"><img src=\"image.php?src=$row[4]&width=400\"></a>";
        echo "</td>";
        echo "</table>";
        echo "<table border=0>";

        if($row[1]) echo "<tr><td>Instruction Rate:</td><td><B>$CurrencyPrefix$row[1]/hour</B></td></tr>";
        if($row[2]) echo "<tr><td>Email:</td><td><a href=\"mailto:$row[2]\"><B>$row[2]</B></a></td></tr>";
        if($row[5]) echo "<tr><td>Phone number:</td><td><B>" . 
                            FormatPhoneNumber($row[5], $row[6])  . 
                            "</B></td></tr>";
        if($row[3]) echo "<tr><td>Information:</td><td><B>$row[3]</B></td></tr>";
        echo "</table>";
        echo "<BR>";
        // generate return URL
        GenerateReturnURL(
                            $goback, 
                            "?day=$day&month=$month&year=$year$makemodel" . 
                            "&resource=$resource" .
                            "&resource_id=$resource_id"
                            );
        
        echo "</center>";
    }
    else
    {
        // error getting database information
        DisplayDatabaseError("DisplayInstructorInfo.php", $sql);
    }
    
    include "trailer.inc";
?>