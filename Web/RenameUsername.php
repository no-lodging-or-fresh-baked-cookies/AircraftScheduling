<?php
//-----------------------------------------------------------------------------
// 
// RenameUsername.php
// 
// PURPOSE: Change a username.
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
//      UpdateData - set to Update to update the config information
//      CancelData - set to Cancel to cancel the updates
//      CurrentUsername - current username
//      NewUsername - new username
//      order_by - parameter to sort the display by
//
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
    include "DatabaseConstants.inc";

    // initialize variables
    $order_by = "last_name";
	$UpdateData = "none";
	$CancelData = "none";
	$NewUsername = "";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
	if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["UpdateData"])) $UpdateData = $rdata["UpdateData"];
    if(isset($rdata["CancelData"])) $CancelData = $rdata["CancelData"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["CurrentUsername"])) $CurrentUsername = $rdata["CurrentUsername"];
    if(isset($rdata["NewUsername"])) $NewUsername = $rdata["NewUsername"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    
    //********************************************************************
    // BuildUsernameOption(
    //                   CurrentlySelected, 
    //                   FilterControlName, 
    //                   SQLResult,
    //                   UseSecondEntry)
    //
    // Purpose: Build a drop down box to display the current usernames.
    //
    // Inputs:
    //   CurrentlySelected - currently selected filter value
    //   FilterControlName - name of the filter control
    //   SQLResult - result of the SQL query that contains the list of
    //              options in row[0]
    //   UseSecondEntry - optional. true to user row[1] as the display
    //              name in the selection list.
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildUsernameOption(
                                $CurrentlySelected, 
                                $FilterControlName, 
                                $SQLResult,
                                $UseSecondEntry = false)
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    
        // filter parameters
        global $AllString;
        global $FilterName;
    	
        // build the select HTML	
		echo "<SELECT NAME='$FilterControlName' id='$FilterControlName' onChange=LoadNewCharges()>";
		
		// build the selection entries
		for($i=0; $row = sql_row($SQLResult, $i); $i++) 
		{
			echo "<OPTION " .
					"VALUE='" . $row[0] . "'" . 
					(Ucase($row[0]) == UCase($CurrentlySelected) ? " SELECTED" : ""); 
			echo ">$row[0] ($row[1])";
		}
		echo "</SELECT>";	
    }
           
    //********************************************************************
    // SaveUsernameJavaScriptArrays()
    //
    // Purpose:  Save the username items that the javascript code needs 
    //           to use when a Username is changed.
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
    function SaveUsernameJavaScriptArrays()
    {   
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    
        // open a recordset for the inventory items
    	$sql = 
    			"SELECT " .
    			    "Username " .               // 0
    			"FROM " .
    			    "AircraftScheduling_person " .
    			"WHERE user_level != $UserLevelDisabled " .
    			"ORDER BY username";
    	$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            $NumUsernameRecords = sql_count($res);
    
            // build the global arrays for the javascript processing
            echo "<SCRIPT LANGUAGE=\"JavaScript\">";
            echo "var NumUsernameRecords = $NumUsernameRecords;";
        
            // build the array lists from the database information
            $UsernameList = "";
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
                if ($i != $NumUsernameRecords - 1)
                {
                    $UsernameList = $UsernameList . "'" . $row[0] . "',";
                }
                else
                {
                    $UsernameList = $UsernameList . "'" . $row[0] . "'";
                }
            }
            echo "var UsernameList = new Array($UsernameList);";   
            
            // end of username arrays
            echo "</SCRIPT>";            
        }
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("SaveUsernameJavaScriptArrays", $sql);
        }  
    }
    
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################

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
    
    // if the make and model is not set, set the default
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else { $all=1; $makemodel = "&all=1"; }
    
    // if the resource is not set, get the default
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
    
    // are we authorized for this operation?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // if we are updating the data, save it and return to previous screen
    if ($UpdateData == "Update")
    {
        // acquire mutex to prevent concurrent username renames
        if (!sql_mutex_lock('AircraftScheduling_rename'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // update the username in all the tables — wrap in transaction for atomicity
        sql_begin();

        // make sure the username is upper case
        $NewUsername = UCase($NewUsername);

        // AircraftScheduling_entry table
		sql_command(
		            "UPDATE AircraftScheduling_entry " .
		            "SET create_by = '$NewUsername' " .
		            "WHERE UPPER(create_by) = '" . UCase($CurrentUsername) . "' ");   
        
        // AircraftScheduling_journal table
		sql_command(
		            "UPDATE AircraftScheduling_journal " .
		            "SET username ='$NewUsername' " .
		            "WHERE UPPER(username) = '" . UCase($CurrentUsername) . "' ");     
        
        // AircraftScheduling_person table
		sql_command(
		            "UPDATE AircraftScheduling_person " .
		            "SET username ='$NewUsername' " .
		            "WHERE UPPER(username) = '" . UCase($CurrentUsername) . "' ");      
        
        // Charges table
		sql_command(
		            "UPDATE Charges " .
		            "SET KeyCode ='$NewUsername' " .
		            "WHERE UPPER(KeyCode) = '" . UCase($CurrentUsername) . "' ");   
        
        // Flight table
		sql_command(
		            "UPDATE Flight " .
		            "SET KeyCode ='$NewUsername' " .
		            "WHERE UPPER(KeyCode) = '" . UCase($CurrentUsername) . "' ");     
		sql_command(
		            "UPDATE Flight " .
		            "SET Instructor_Keycode ='$NewUsername' " .
		            "WHERE UPPER(Instructor_Keycode) = '" . UCase($CurrentUsername) . "' ");  
		sql_command(
		            "UPDATE Flight " .
		            "SET Student_Keycode ='$NewUsername' " .
		            "WHERE UPPER(Student_Keycode) = '" . UCase($CurrentUsername) . "' ");       
		sql_command(
		            "UPDATE Flight " .
		            "SET Cleared_By ='$NewUsername' " .
		            "WHERE UPPER(Cleared_By) = '" . UCase($CurrentUsername) . "' ");      
        
        // Squawks table
		sql_command(
		            "UPDATE Squawks " .
		            "SET KeyCode = '$NewUsername' " .
		            "WHERE UPPER(KeyCode) = '" . UCase($CurrentUsername) . "' ");        
        
        sql_commit();
        sql_mutex_unlock('AircraftScheduling_rename');

        // log the change in the journal
        $Description =
                        "Old username " .
                                $CurrentUsername .
                        "  set to " .
                                $NewUsername;
     	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
   		
    	// updates complete, return to admin pages
    	session_write_close();
    	header("Location: admin.php");
    	exit;
    }
    
    // if we are canceling return to previous screen
    else if ($CancelData == "Cancel")
    {
    	// updates canceled, return to admin pages
    	session_write_close();
    	header("Location: admin.php");
    	exit;
    }
    else
    {
        // display the username and let the user set a new one
    	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");

	    // display the title information
		echo "<center>";
        echo "<h2>Rename Username</h2>";
		echo "<form name='RenameUsername' action='" . getenv('SCRIPT_NAME') . "' method='post'>";
		echo "<table border=0>\n";
		
        // build the user name filter
		$sql = 
				"SELECT username, $DatabaseNameFormat " .
				"FROM AircraftScheduling_person " .
				"WHERE user_level != $UserLevelDisabled " .
                "ORDER by username";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {  			
            // get the first name in the list for selection
            $row = sql_row($res, 0);
            $FirstUsername = $row[0];
            
    		// current username        
    		echo "<tr>";
    		echo "<td>Select Username to Change:</td>";
    		echo "<td>";			
    		BuildUsernameOption($FirstUsername, "CurrentUsername", $res);
    		echo "</td>";			
    		echo "</tr>";
    		
    		// new username
            echo "<tr>";
            echo "<td class=CL>";
            echo "New Username (must be unique):";
            echo "</td>";
            echo "<td class=CL>";
            echo  "<input " .
                        "type=text " .
                        "NAME='NewUsername' " . 
                        "ID='NewUsername' " .
                        "align=left " . 
                        "SIZE=10 " . 
                        "VALUE='$NewUsername' " . 
                        ">";
            echo "</td>";
            echo "</tr>";
    		
    		echo "</table>";
    
    		// submit and cancel buttons
    		echo "<br>";
            echo "<input name='UpdateData' type=submit value='Update' ONCLICK='return ValidateAndSubmit()'>";
    		echo "<input type=submit value='Cancel' name='CancelData' onClick=\"return confirm('" .  
                    $lang["CancelSafety"] . "')\">";
    		echo "</form>";
    		echo "</center>";
    
            // save the inventory information for the javascript code
            SaveUsernameJavaScriptArrays();
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("RenameUsername", $sql);
        }
    }
    
    include "trailer.inc";

?>


<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

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
    // make sure that the username entered is unique if it has changed
    CurrentUsername = document.getElementById("CurrentUsername").value;
    NewUsername = document.getElementById("NewUsername").value;
    if (CurrentUsername.toUpperCase() != NewUsername.toUpperCase())
    {
        for (var i = 0; i < NumUsernameRecords; i++)
        {
            if (NewUsername.toUpperCase() == UsernameList[i].toUpperCase())
            {
                alert("The Username " + NewUsername + " is already in the database.\n" +
                        "Please enter a new value.");
                document.getElementById('NewUsername').focus();
                document.getElementById('NewUsername').select();
                
                // error found, don't let them continue
                return false;
            }
        }
    }
    else
    {
        // username hasn't changed, tell the user
        alert("The new Username " + CurrentUsername + " is the same as the old.\n" +
                "Please enter a new Username.");
        document.getElementById('NewUsername').focus();
        document.getElementById('NewUsername').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // make sure that the new username is entered
    if (NewUsername.length == 0)
    {
        alert("The new Username cannot be blank.\n" +
                "Please enter a value.");
        document.getElementById('NewUsername').focus();
        document.getElementById('NewUsername').select();
        
        // error found, don't let them continue
        return false;
    }

       
    // no errors found, return
	return true;
}

//-->
</script>
