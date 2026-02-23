<?php
//-----------------------------------------------------------------------------
// 
// AircraftScheduling_config.php
// 
// PURPOSE: Display and update the configuration information.
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelAdmin))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // if the maximum number of days to allow the user to schedule is not set in the
    // database, add it now. this may occur if the user has an older version of the database
    $sql = "SELECT COUNT(*) FROM AircraftScheduling_config WHERE name = 'Max_Days_Allowed'";
    if (sql_query1($sql) == 0)
    {
        // value doesn't exist, add it to the database
        $sql = "INSERT INTO AircraftScheduling_config (name, value, description) " . 
                "VALUES (" .
                    "'MaxScheduleDaysAllowed', " .
                    "'0', "  .
                    "'Maximum number of days in the future that a schedule may be created (0 for unlimited)'" . 
                ")";
		sql_command($sql);
    }
    
    // reset the variable array back to the start
    reset($_POST);
    
    // if we are updating the data, save it and return to previous screen
    if ($UpdateData == "Update")
    {
        // get the updated parameters and update them in the database
		foreach ($_POST as $name => $value)
    	{
    		sql_command(
    		            "UPDATE AircraftScheduling_config " .
    		            "SET value='" . addslashes($value) . "' " .
    		            "WHERE name='$name'");
    	}
    	
    	// log the change in to the journal
    	$Description = 
    				"Updating configuration information";
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    		
    	// updates complete, return to admin pages
    	session_write_close();
    	header("Location: admin.php");
    	exit;
    }
    
    // if we are updating the data, save it and return to previous screen
    else if ($CancelData == "Cancel")
    {
    	// updates canceled, return to admin pages
    	session_write_close();
    	header("Location: admin.php");
    	exit;
    }
    else
    {
        // display the config information
        $sql = "SELECT name, value, description FROM AircraftScheduling_config ORDER BY description";
    	$res = sql_query($sql);
    	
    	// start the form
    	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    	        
        // if we didn't have any errors, process the results of the database inquiry
    	if($res) 
    	{
    	    // display the config information
    		echo "<center>";
            echo "<h2>Update Configuration Information</h2>";
    		echo "<form name='AircraftScheduling_config' action='" . getenv('SCRIPT_NAME') . "' method='post'>";
    		echo "<table border=0>\n";
    		for($i=0; ($row = sql_row($res, $i)); $i++) 
    		{
    			echo "<tr>";
    			
    			// description
    			echo "<td>" . stripslashes($row[2]) . "</td>";

    			// if the text is not too long, show it in a input box
    			echo "<td>";
    			if (strlen(stripslashes($row[1])) <= 32)
    			{
    			    // short text area
    			    echo "<input name='$row[0]' size=32 value='" . stripslashes($row[1]) . "'>";
    			}
    			else
    			{
    			    // long text area
                    echo "<textarea name='$row[0]' rows=4 cols=25 wrap='virtual'>" . 
                            htmlentities ( stripslashes($row[1]) ) . 
                         "</textarea>";
    			}
    			echo "</td>";
    			
    			echo "</tr>";
    		}
    		echo "</table>";
    		echo "<input type=submit value='Update' name='UpdateData'>";
    		echo "<input type=submit value='Cancel' name='CancelData' onClick=\"return confirm('" .  
                    $lang["CancelConfig"] . "')\">";
    		echo "</form>";
    		echo "</center>";
    	} 
    	else
    	{
            // error processing database request, tell the user
            DisplayDatabaseError("AircraftScheduling_config", $sql);
    	}
    }
    
    include "trailer.inc";

?>