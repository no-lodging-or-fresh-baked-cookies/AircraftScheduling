<?php
//-----------------------------------------------------------------------------
// 
// DisplayInstructor.php
// 
// PURPOSE: Displays the instructors on file to allow selection for modification.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      order_by - parameter to sort the display by
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model resources
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
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["makemodel"])) $makemodel = $rdata["makemodel"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    
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
    
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else { $all=1; $makemodel = "&all=1"; }
    
    if(empty($resource))
    	$resource = get_default_resource();
    		
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $makemodel, " ", " ", " ");
    	exit();
    }
    
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    if(! isset($order_by)) $order_by = "last_name";
    $sql = "SELECT 
    			instructor_id, 
    			first_name,
    			last_name, 
    			hourly_cost, 
    			email, 
    			description 
    		FROM 
    			AircraftScheduling_person a, 
    			AircraftScheduling_instructors b 
    		WHERE 
    			a.person_id=b.person_id 
    		ORDER BY $order_by";
    
    $res = sql_query($sql);
     
 	if ($res) 
 	{
 	    // display the title and header information
        echo "<H2>Instructor Management</H2>";
        echo "<UL>";
		
		// if the aircraft checkout functions are enabled, adding and removing
		// instructors is accomblished through the user maintenance
     	if (!$AllowAircraftCheckout)
    	{
            echo " <LI><b><a href='add_instructor.php'>Add Instructor</a></b>";
        }
        echo " <LI><b>Click on a heading to sort</b>";
        echo " <LI><b>Click on a id to edit that instructor</b>";
        echo "</UL>";
        echo "<table border=1>";
        echo "<tr>";
        
        // output the instructor row headers
        echo "	<td><a href='DisplayInstructor.php?order_by=instructor_id'>id</a></td>";
        echo "	<td><A href='DisplayInstructor.php?order_by=first_name'>First Name</A></td>";
        echo "	<td><A href='DisplayInstructor.php?order_by=last_name'>Last Name</A></td>";
        echo "	<td><a href='DisplayInstructor.php?order_by=hourly_cost'>Hourly Cost</a></td>";
        echo "	<td><a href='DisplayInstructor.php?order_by=email'>Email</a></td>";
        echo "	<td><a href='DisplayInstructor.php?order_by=description'>Description</a></td>";
        echo "</tr>";

        // output the instructor information
		for ($i = 0; ($row = sql_row($res, $i)); $i++) 
		{
			echo "<tr>";
			for($c = 0; $c < count($row); $c++) 
			{
				if(0 == $c)
					echo "<td><a href='edit_instructor.php?instructor_id=" . 
					        stripslashes($row[$c]) . "'>" . 
					        stripslashes($row[$c]) . "</a></td>";
				else
					echo "<td>" . stripslashes($row[$c]?$row[$c]:"none") . "</td>";
			}
			echo "</tr>\n";
		}
	}
	else
    {
        // error processing database request, tell the user
        DisplayDatabaseError("DisplayInstructor", $sql);
    }
    
    echo "</table>";
    
    echo "<BR>";
    echo "<A HREF='admin.php'>Return to administrator page</A>";
    
    echo "<br>";
    
    include "trailer.inc" 
?>
