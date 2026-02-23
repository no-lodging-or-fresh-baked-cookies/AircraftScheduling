<?PHP

//-----------------------------------------------------------------------------
// 
// add_instructor_handler.php
// 
// PURPOSE: Update the database information for the add_instructor screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      edit_user - user number for the user we are updating
//      hourly_cost - hourly cost for the instructor
//      description - instructor description
//      add_to_schedule - set to "on" to add allow the instructor to be scheduled
//      FirstnameLastName - name of .jpg file for instructor picture
//      resource_name - name of the resource for the database
//      picture - upload information for the picture file
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

    // set default data for input parameters
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
    if(isset($rdata["edit_user"])) $edit_user= $rdata["edit_user"];
    if(isset($rdata["hourly_cost"])) $hourly_cost = $rdata["hourly_cost"];
    if(isset($rdata["description"])) $description = $rdata["description"];
    if(isset($rdata["add_to_schedule"])) $add_to_schedule = $rdata["add_to_schedule"];
    if(isset($rdata["FirstnameLastname"])) $FirstnameLastname = $rdata["FirstnameLastname"];
    if(isset($rdata["resource_name"])) $resource_name = $rdata["resource_name"];
    if(isset($rdata["picture"])) $picture = $rdata["picture"];
    
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
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // update the database with the items
    $sql1 = "INSERT INTO AircraftScheduling_instructors (person_id";
    $values = "('$edit_user'";
    if($hourly_cost) 
    {
      $sql1 .= ", hourly_cost";
      $values .= ",'" . addslashes($hourly_cost) . "'";
    }
    
    if($description) 
    {
      $sql1 .= ", description";
      $values .= ",'" . addslashes($description) . "'";
    }
                
    // handle the uploading of an instructor picture file
    $tmp = $_FILES['picture']['tmp_name'];
    if(is_uploaded_file($tmp)) 
    {
        if(!move_picture_into_position($tmp, $FirstnameLastname . ".jpg"))
        {
            // error, unable to copy uploaded file
            $ErrorMessage .= "Unable to move new picture: " . 
                                $_FILES['picture']['name'] . 
                                " to img/$FirstnameLastname.jpg";
            $InstructorPicture = "";
        }
        else 
        {
            // good upload
            $InstructorPicture = "on";
        }
    } 
    else
    {
        // no picture for this instructor
        $InstructorPicture = "";
    }
    
    if($InstructorPicture == "on")
    {
    	$sql1 .= ", picture";
    	$values .= ", '".$FirstnameLastname.".jpg'";
    }
    
    $sql1 .= ", lesson_fee";
    $values .= ", '0'";
    
    // update the instructor table and save the results
    $sql1 = "$sql1) VALUES $values ) ";
    $sql1_result = sql_command($sql1);
    $InstructorIDSeq = sql_insert_id("AircraftScheduling_instructors", "instructor_id");
    
    $err = 0;
    $sql2_result = 0;
    $sql3_result = 0;
    if($add_to_schedule == "on") 
    {
    	// add a link in the resource table to the new instructor
    	$sql2 = "INSERT INTO 
    				AircraftScheduling_resource (item_id, schedulable_id, resource_name, resource_make, resource_model) 
    				VALUES ($InstructorIDSeq, $InstructorScheduleType, '$resource_name', '', '')";
    	$sql2_result = sql_command($sql2);	
    	$ResourceIDSeq = sql_insert_id("AircraftScheduling_resource", "resource_id");
    	
    	// update the link back to the instructor
    	$sql3 = "UPDATE AircraftScheduling_instructors SET resource_id = $ResourceIDSeq WHERE person_id = '$edit_user' ";
    	$sql3_result = sql_command($sql3);
    }
    
    // test the results of the SQL statements
    if(-1 == $sql1_result || -1 == $sql2_result || -1 == $sql3_result) 
    {
    	echo "SQL error occurred";
    	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    	echo sql_error();
    	include "trailer.inc";
    } 
    else 
    {
    	// log the change in to the journal
    	$InstructorName = sql_query1(
    							"SELECT $DatabaseNameFormat 
    							FROM AircraftScheduling_person LEFT JOIN AircraftScheduling_instructors USING (person_id) 
    							WHERE instructor_id=" . $InstructorIDSeq);
    	$Description = 
    				"Adding " . $InstructorName . " to instructor list";
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    	
    	// back to the instructor modification screen
    	session_write_close();
    	header("Location: DisplayInstructor.php");
    	exit;
    }

?>