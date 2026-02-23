<?php

//-----------------------------------------------------------------------------
// 
// add_instructor.php
// 
// PURPOSE: Display a screen so that a new instructor can be added to the database.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      edit_user - user number for the user we are updating
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model resources
//      order_by - parameter to sort the display by
//      pview - set true to build a screen suitable for printing
//      hourly_cost - hourly cost for the instructor
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
    $order_by = '';
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["edit_user"])) $edit_user = $rdata["edit_user"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["makemodel"])) $makemodel = $rdata["makemodel"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["hourly_cost"])) $hourly_cost = $rdata["hourly_cost"];
    
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
    if (empty($resource))
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
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    $sql = "SELECT 
    			username, first_name, last_name, email, person_id, user_level, 
    			counter, middle_name,	password, title, address1,
    			address2, city, state, zip, phone_number
    		FROM AircraftScheduling_person ";
    if(isset($edit_user))   $sql .= "where person_id = '$edit_user' ";
    $sql .= $order_by ? "order by $order_by" : "order by last_name";
    
    $res = sql_query($sql);
    if(-1 == $res) 
    {
    	echo "SQL error occurred";
    	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    	echo sql_error();
    	include "trailer.inc";
    	exit();
    }
    if(($row = sql_row_keyed($res, 0)) > 0) 
    {
    	if(!isset($edit_user)) 
    	{
    		// display the list of users
    		echo "<h2>Add a user to the instructor list.</h2>";
    		echo "<br>Click on a username to add that user as an instructor<br>";
    		echo "Click on a column header to sort the user list<br>";  
    		echo "Note: To add someone as an instructor, they must already be a user.";
    		echo "</h2>";  
    		echo "<table border=2>";
    		echo "<tr>";
    		echo   "<td><A href=\"" . $_SERVER["PHP_SELF"] . "?order_by=username\">Username</A></td>
    				<td><A href=\"" . $_SERVER["PHP_SELF"] . "?order_by=first_name\">First Name</A></td>
    				<td><A href=\"" . $_SERVER["PHP_SELF"] . "?order_by=last_name\">Last Name</A></td>
    				<td><A href=\"" . $_SERVER["PHP_SELF"] . "?order_by=email\">Email</A></td>
    				<td><A href=\"" . $_SERVER["PHP_SELF"] . "?order_by=person_id\">Person ID</A></td>
    				<td><A href=\"" . $_SERVER["PHP_SELF"] . "?order_by=user_level\">User Level</A></td>
    				<td><A href=\"" . $_SERVER["PHP_SELF"] . "?order_by=counter\">Counter</A></td>";
    		echo "</tr>";
    
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
    			// if this person is already in the instructor list, don't display them
    			$InstructorID = sql_query1("SELECT person_id FROM AircraftScheduling_instructors WHERE person_id = '$row[4]'");
    
    			if ($InstructorID < 0)
    			{
    				// not already an instructor, display the user
    				// set the user level
    				if ($row[5] == $UserLevelDisabled)
    					$UserLevel = $UserLevelDisabledName;
    				else if($row[5] == $UserLevelNormal)
    					$UserLevel = $UserLevelNormalName;
    				else if($row[5] == $UserLevelSuper)
    					$UserLevel = $UserLevelSuperName;
    				else if($row[5] == $UserLevelOffice)
    					$UserLevel = $UserLevelOfficeName;
    				else if($row[5] == $UserLevelMaintenance)
    					$UserLevel = $UserLevelMaintenanceName;
    				else if($row[5] == $UserLevelAdmin)
    					$UserLevel = $UserLevelAdminName;
    				
    				// display the user values
    				echo "<tr>\n";
    				echo "<td><a href='" . getenv("SCRIPT_NAME") . "?edit_user=$row[4]'>$row[0]</a></td>\n";
    				echo "<td> $row[1]</td>"; 
    				echo "<td> $row[2]</td>";
    				echo strlen($row[3]) ? "<td> $row[3]</td>" : "<td> none</td>";
    				echo "<td> $row[4]</td>";
    				echo "<td> $UserLevel</td>";
    				echo "<td> $row[6]</td>";
    				echo "</tr>";	
    			}
    		}
    
    		echo "</table>";
    		
    		// add a link back to admin pages
    		echo "<BR>";
    	    echo "<A HREF=\"admin.php\">" . "Return to administrator page" . "</A>";
    	}
    	else 
    	{ 
    		// add a user to the instructor list
    		$row = sql_row($res, 0);
     		echo "<h2>Add the following user to the instructor list</h2>";  
    
    		// put up the information form with the user information filled in
    		echo "<form name='add_instructor' enctype='multipart/form-data' action='add_instructor_handler.php' method='POST'>";
    		echo "<center>";
    		echo "<td width=\"70%\">";
    		echo "<table border=0>";
    		echo "<tr>";
    		echo "<td><B>First Name</B></td>";
    		echo "<td><B>Middle Name</B></td>";
    		echo "<td><B>Last Name</B></td>";
    		echo "</tr>";
    		echo "<tr>";
    		echo "<td>$row[1]</td>";
    		echo "<td>$row[7]</td>";
    		echo "<td>$row[2]</td>";
    		echo "</tr>";
    		echo "<tr>";
    		echo "<td><B>Email</B></td>";
    		echo "<td><B>Phone Number</B></td>";
    		echo "<td><B>User Level</B></td>";
    		echo "</tr>";
    		echo "<tr>";
    		echo "<td>$row[3]</td>";
    		echo "<td>". stripslashes($row[15]) . "</td>";
    
    		// display the user level
    		if ($row[5] == $UserLevelDisabled)
    			$UserLevel = $UserLevelDisabledName;
    		else if($row[5] == $UserLevelNormal)
    			$UserLevel = $UserLevelNormalName;
    		else if($row[5] == $UserLevelSuper)
    			$UserLevel = $UserLevelSuperName;
    		else if($row[5] == $UserLevelOffice)
    			$UserLevel = $UserLevelOfficeName;
    		else if($row[5] == $UserLevelMaintenance)
    			$UserLevel = $UserLevelMaintenanceName;
    		else if($row[5] == $UserLevelAdmin)
    			$UserLevel = $UserLevelAdminName;
    		echo "<td>$UserLevel</td>";
    		echo "</tr>";
    		echo "<tr>";
            echo "</table>";
            echo "<table border=0>";
    		echo "<td><b>Hourly Cost</b></td>";
    		echo "</tr>";
    		echo "<tr>";
    		echo "<td><INPUT NAME=\"hourly_cost\" SIZE=16 MAXLENGTH=10 VALUE=\"$Default_Instructor_Rate\"></td>";
            echo "</tr>";
        
            // generate the picture upload information
            echo "<tr>";
            echo "<td><b>Picture:</b></td>";
            echo "<td>";
            echo "<input type='file' name='picture'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
     		
    		echo "<td width=\"70%\">";
    		echo "<table border=0>";
    		echo "<tr>";
    		echo "<td><B>Description</B></td>";
    		echo "</tr>";
    		echo "<tr>";
    		echo "<td><INPUT NAME=\"description\" SIZE=56 MAXLENGTH=56 VALUE=\"\"></td>";
     		echo "</tr>";
    		echo "</table>";
    		echo "</CENTER>";
    
    		if($enablessl && !isset($HTTPS)) 
    		{
    			echo "<H2><INPUT NAME=\"usessl\" TYPE=\"checkbox\"> Use SSL (Select this option to protect your personal information)</H2>";
    		}
    		
        	echo "<INPUT TYPE='hidden' NAME='edit_user' value='$edit_user'>";
        	echo "<INPUT TYPE='hidden' NAME='FirstnameLastname' value='" . $row[1] . $row[2] . "'>";
        	echo "<INPUT TYPE='hidden' NAME='resource_name' value='" . buildName($row[1], $row[2]) . "'>";
        	echo "<INPUT TYPE='hidden' NAME='add_to_schedule' value='on'>";
    		?>
    
    		<SCRIPT LANGUAGE="JavaScript">
    		// do a little form verifying
    		function do_submit ()
    		{
    		  if(document.forms["add_instructor"].hourly_cost.value == "")
    		  {
    		    alert ( "You must enter an hourly cost for the instructor's time");
    		    return false;
    		  }
    		  
    		  document.forms["add_instructor"].submit();
    		  
    		  return true;
    		}
    		</SCRIPT>
    		
    		<CENTER>		
    		<SCRIPT LANGUAGE="JavaScript">
    		       document.writeln ( '<INPUT type="button" value="Add" ONCLICK="do_submit()">' );
    		</SCRIPT>
    		<NOSCRIPT>
    		   <INPUT TYPE="submit" VALUE="Submit">
    		</NOSCRIPT>
    		</CENTER>
    
    		<?php
    		echo "</FORM>";
    	} 
    }
    
    include "trailer.inc"

?>