<?php
//-----------------------------------------------------------------------------
// 
// edit_instructor.php
// 
// PURPOSE: Displays a screen to allow the modification of instructor information.
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
//      pview - set true to build a screen suitable for printing
//      update - set to 1 if updating an instructor's information
//      delete - set to 1 if deleting an instructor's information
//      instructor_id - database instructor_id
//      description - instructor description
//      hourly_cost - hourly cost for the instructor
//      schedulable - set to 1 to allow scheduling of this instructor
//      FirstnameLastName - name of .jpg file for instructor picture
//      picture - upload information for the picture file
//      delete_picture - set to "on" to delete the instructor picture
//      old_picture - name of the old picture (if any)
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
    $update = '';
    $delete = '';
    $sql = '';
    $schedulable = '';
    
    // set default data for input parameters
    $serial_number = 0;
    $equipment = "U";
    $hourly_cost = 0;
    $description = "none";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["update"])) $update = $rdata["update"];
    if(isset($rdata["delete"])) $delete = $rdata["delete"];
    if(isset($rdata["instructor_id"])) $instructor_id= $rdata["instructor_id"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    
    if(isset($rdata["description"])) $description = $rdata["description"];
    if(isset($rdata["hourly_cost"])) $hourly_cost = $rdata["hourly_cost"];
    if(isset($rdata["schedulable"])) $schedulable = $rdata["schedulable"];
    
    if(isset($rdata["FirstnameLastName"])) $FirstnameLastName = $rdata["FirstnameLastName"];    
    if(isset($rdata["picture"])) $picture = $rdata["picture"];
    if(isset($rdata["delete_picture"])) $delete_picture = $rdata["delete_picture"];
    if(isset($rdata["old_picture"])) $old_picture = $rdata["old_picture"];
    
    if(empty($instructor_id))
    {
      	session_write_close();
    	header("Location: DisplayInstructor.php");
    }
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice)) {
    
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // should we update the instructor's information?
    if(count($_POST) > 0 && $update == "Update" && isset($instructor_id)) 
    {
    	// Catch null variables and enter them into the database as such
    	// The database can't handle single quotes around 'null', so each value must be quoted here
		foreach ($_POST as $name => $value)
    	{
    		if(preg_match("/instructor_id|picture|FirstnameLastName/", $name)) continue;
    		$$name = "'" . addslashes($value) . "'";
    		if($$name == "''") $$name="null";
    	}
    	
    	$hourly_cost = preg_replace("/\\$/", "//", $hourly_cost);
    	$sql1 = "UPDATE AircraftScheduling_instructors SET hourly_cost=$hourly_cost";
    	$sql1 .= ", description=$description";
            
        // handle the uploading of an instructor picture file
		$tmp = $_FILES['picture']['tmp_name'];
        if(is_uploaded_file($tmp)) 
        {
            if(isset($old_picture) && !@unlink($ImageRootPath . "/" . "$old_picture"))
            {
                // error, unable to delete old picture
                $ErrorMessage .= "Unable to delete " . $ImageRootPath . "/" . "$old_picture";
                $InstructorPicture = "";
            }
            else if(!move_picture_into_position($tmp, $FirstnameLastName . ".jpg"))
            {
                // error, unable to copy uploaded file
                $ErrorMessage .= "Unable to move new picture: " . 
                                    $_FILES['picture']['name'] . 
                                    " to img/$FirstnameLastName.jpg";
                $InstructorPicture = "";
            }
            else 
            {
                // good upload
                $InstructorPicture = "on";
            }
        } 
        else if($delete_picture == "on")
        {
            // don't use the picture file
            // delete the picture file if it exists
            if (isset($old_picture))
            {
                @unlink($ImageRootPath . "$old_picture");
            }
            $InstructorPicture = "";
        }
        else
        {
            // no changes, leave the picture setting alone
            if (!empty($old_picture))
            {
                // picture is not being changed
                $InstructorPicture = "on";
            }
            else
            {
                // no picture for this aircraft
                $InstructorPicture = "";
            }
        }
    	
    	if($InstructorPicture == "on") 
    		$sql1 .= ", picture='" . $FirstnameLastName . ".jpg'";
    	else
    		$sql1 .= ", picture=null";
    	
    	// execute the sql statement to update the instructors information
    	$sql1 .= " WHERE instructor_id=$instructor_id ";
    	$sql1_result = sql_command($sql1);			
    	// check the results and save if an error occurs
    	if ($sql1_result == -1) 
    	{
    		echo $sql1 . sql_error();
    	}
    	
    	// Have to add single quotes for the match because of the while(list....) loop above
    	$resource_count = sql_query1("SELECT count(*) FROM AircraftScheduling_resource WHERE item_id=$instructor_id AND schedulable_id=$InstructorScheduleType");
    	$sql4_result = 0;
    	
    	// if schedulabe is disabled, remove the entries from the schedule
    	if($schedulable != "'on'" &&  $resource_count == 1) 
    	{
    		// remove any scheduled entries for this instructor since it is no longer on the schedule
    		$resource_id = sql_query1("SELECT resource_id FROM AircraftScheduling_instructors WHERE instructor_id=$instructor_id");
    		if($resource_id > 0) 
    		{
    			$sql_result = sql_command("DELETE FROM AircraftScheduling_entry WHERE resource_id=$resource_id");
    			$sql_result = sql_command("DELETE FROM AircraftScheduling_repeat WHERE resource_id=$resource_id");
    		}
    
    		$sql = " DELETE FROM AircraftScheduling_resource WHERE item_id=$instructor_id AND schedulable_id=$InstructorScheduleType ";
    		$sql_result = sql_command($sql);
    		
    		// check the results and save if an error occurs
    		if ($sql_result == -1) 
    		{
    			$sql4_result = -1;
    			echo $sql . sql_error();
    		}
    		$sql = " UPDATE AircraftScheduling_instructors SET resource_id=0 WHERE instructor_id=$instructor_id ";
    		$sql_result = sql_command($sql);
    		
    		// check the results and save if an error occurs
    		if ($sql_result == -1) 
    		{
    			$sql4_result = -1;
    			echo $sql . sql_error();
    		}
    	}
    	else if($schedulable == "'on'" && $resource_count == 0) 
    	{
    		$sql = " INSERT INTO 
    					AircraftScheduling_resource (item_id, schedulable_id, resource_name, resource_make, resource_model) 
    					VALUES ($instructor_id, $InstructorScheduleType, $resource_name, '', '') ";
    		$sql_result = sql_command($sql);
    		$ResourceIDSeq = sql_insert_id("AircraftScheduling_resource", "resource_id");
    		
    		// check the results and save if an error occurs
    		if ($sql_result == -1) 
    		{
    			$sql4_result = -1;
    			echo $sql . sql_error();
    		}
    		$sql = " UPDATE AircraftScheduling_instructors SET resource_id = $ResourceIDSeq WHERE instructor_id=$instructor_id ";
    		$sql_result = sql_command($sql);
    		
    		// check the results and save if an error occurs
    		if ($sql_result == -1) 
    		{
    			$sql4_result = -1;
    			echo $sql . sql_error();
    		}
    	}
    	
    	if(-1 == $sql1_result || -1 == $sql4_result || isset($error)) 
    	{
    		print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    		echo "Error " . $sql1_result . $sql4_result;
    		if (isset($error)) echo "<br>$sql<br>$error";
    			include "trailer.inc";
    		exit;
    	} 
    	else
    	{
    		// log the change in to the journal
    		$InstructorName = sql_query1(
    								"SELECT $DatabaseNameFormat 
    								FROM AircraftScheduling_person LEFT JOIN AircraftScheduling_instructors USING (person_id) 
    								WHERE instructor_id=" . $instructor_id);
    		$Description = 
    					"Changing information for instructor " . $InstructorName;
    		CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    		
    		// back to the instructor modification screen
    		session_write_close();
    		header("Location: DisplayInstructor.php");
    		exit;
    	}
    } 
    else if(count($_POST) > 0 && $delete == "Remove" && isset($instructor_id)) 
    {
    	// save the instructor name for the journal entry
    	$InstructorName = sql_query1(
    							"SELECT $DatabaseNameFormat 
    							FROM AircraftScheduling_person LEFT JOIN AircraftScheduling_instructors USING (person_id) 
    							WHERE instructor_id=" . $instructor_id);
    	$res = sql_query("SELECT resource_id, picture FROM AircraftScheduling_instructors WHERE instructor_id=$instructor_id");
    	$row = sql_row($res, 0);
    	$resource_id = $row[0];
    	$sql = "DELETE FROM AircraftScheduling_resource WHERE item_id=$instructor_id AND schedulable_id=$InstructorScheduleType ";
    	$sql_result = sql_command($sql);
    	if($resource_id > 0) 
    	{
    		$sql = "DELETE FROM AircraftScheduling_entry WHERE resource_id=$resource_id ";
    		$sql_result = $sql_result + sql_command($sql);
    		$sql = "DELETE FROM AircraftScheduling_repeat WHERE resource_id=$resource_id ";
    		$sql_result = $sql_result + sql_command($sql);
    	}
    	$sql = "DELETE FROM AircraftScheduling_instructors WHERE instructor_id=$instructor_id ";
    	$sql_result = $sql_result + sql_command($sql);
                            
        // delete the picture file if it exists
        if (isset($old_picture))
        { 
            @unlink($ImageRootPath . "$old_picture");
        }

        // delete the database information
    	if(sql_command($sql) != 0) 
    	{
    		print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    		echo sql_error() . "<br>$sql";
    		include "trailer.inc";
    		exit;
    	} 
    	else 
    	{
    		// log the removal in to the journal
    		$Description = 
    					"Removing " . $InstructorName . " from the instructor list";
    		CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    		
    		// back to the instructor modification screen
    		session_write_close();
    		header("Location: DisplayInstructor.php");
    		exit;
    	}
    }
    
    // start the main form
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    $sql = "SELECT " .
            "     b.instructor_id, " .
            "     a.first_name, " .
            "     a.last_name, " .
            "     hourly_cost, " .
            "     email, " .
            "     description, " .
            "     picture  " .
            "FROM  " .
            "	  AircraftScheduling_person a,  " .
            "	  AircraftScheduling_instructors b  " .
            "WHERE  " .
            "	a.person_id=b.person_id AND " .
            "	instructor_id = '$instructor_id'";
    
    $res = sql_query($sql);
    if ($res) 
    {
        $row = sql_row($res, 0);

        // display the instructor table
        echo "<center><H2>Modify Instructor " . buildName($row[1], $row[2]) ." Information</H2>";
        echo "<form name='edit_instructor' ENCTYPE='multipart/form-data' action='edit_instructor.php' method='POST'>";
        echo "<table border=1>";
        echo "<tr>";
        echo " <th>id</td>";
        echo " <th>First Name</td>";
        echo " <th>Last Name</td>";
        echo " <th>Hourly Cost</td>";
        echo " <th>Email</td>";
        echo " <th>Description</td>";
        echo " <th>Schedulable</th>";
        echo "</tr>";
        
        echo "<tr>";
        echo "  <td><input type='hidden' name='instructor_id' value='" . stripslashes($row[0]) . "'>" . stripslashes($row[0]) . "</td>";
        echo "  <td><input type='hidden' name='first_name' size='10' value='" . stripslashes($row[1]) . "'>" . stripslashes($row[1]) . "</td>";
        echo "  <td><input type='hidden' name='last_name' size='16' value='" . stripslashes($row[2]) . "'>" . stripslashes($row[2]) . "</td>";
        echo "  <td><input name='hourly_cost' size='10' value='" . stripslashes($row[3]) . "'></td>";
        echo "  <td><input type='hidden' name='email' size='18' value='" . stripslashes($row[4]) . "'>" . stripslashes($row[4]) . "</td>";
        echo "  <td><input name='description' size='56' value='" . stripslashes($row[5]) . "'></td>";
        echo "  <td><center><input type=checkbox name='schedulable'";
        if (1 == sql_query1("SELECT count(*) FROM AircraftScheduling_resource WHERE item_id=$row[0] AND schedulable_id=$InstructorScheduleType")) echo "CHECKED";
        echo "></center></td>";
        echo "</tr>";
        echo "</table>";
        echo "<br>";
    }
    else 
    {
        echo sql_error();
    }
    
    // generate the picture upload information
    echo "<br>";
    echo "<table border=0>";
    if(!empty($row[6])) 
    {
        echo "<caption>" . 
             "Note: Picture scale automatically adjusted to 300 pixels in width for " .
             "display on this page. Click on the image to view the full size. " . 
             "It is not necessary to upload the image again if you are satisfied with the following picture:" .
             "</caption>";
    }
    echo "<tr>";
    echo "<td>Picture:</td>";
    echo "<td>";
    echo "<input type='file' name='picture'>";
    echo "<br>";
    if(!empty($row[6])) 
    {
        echo "Delete Picture: <input type='checkbox' name='delete_picture'>";
        echo "</td>";
        echo "<td>";
        echo "<a href=\"image.php?src=" . $row[6] . "\">";
        echo "<img src=\"image.php?src=" . $row[6] . "&width=300\"></a>";
        echo "<input type=\"hidden\" name=\"old_picture\" value=\"". $row[6] . "\">";
    }
    echo "</td>";
    echo "</tr>";
    echo "</table>";
	echo "<input type='hidden' name='FirstnameLastName' value='" . $row[1] . $row[2] . "'>";
	echo "<input type='hidden' name='resource_name' value='" . buildName($row[1], $row[2]) . "'>";
   
    // generate the update button
	echo "<input name='update' type=submit value='Update' ONCLICK='return ValidateAndSubmit()'>";

    // if the aircraft checkout functions are enabled, adding and removing
    // instructors is accomblished through the user maintenance
    if (!$AllowAircraftCheckout)
    {
        echo "<input name='delete' type=submit value='Remove' onClick='return confirm(\"" . $lang["confirmremove"] . "\")'>";
    }
    
	echo "</form>";
	echo "</center>";
	echo "<br>";
    include "trailer.inc" 
?>

<!-- ############################### javascript procedures ######################### -->
<SCRIPT LANGUAGE="JavaScript">
// do a little form verifying

//********************************************************************
// ValidateAndSubmit()
//
// Purpose: Verify the data the user entered before submitting the form.
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
function ValidateAndSubmit()
{
	if(document.forms["edit_instructor"].hourly_cost.value == "")
	{
		alert ( "You must enter a hourly cost for this instructor");
		return false;
	}
	
    // no errors found, return
	return true;
}
</SCRIPT>
