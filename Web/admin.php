<?php
//-----------------------------------------------------------------------------
// 
// admin.php
// 
// PURPOSE: Displays the admin screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      add_make - set to non-zero to add a new make
//      add_model - set to non-zero to add a new model
//      makes - selected make
//      models - selected model
//      pview - set true to build a screen suitable for printing
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
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["add_make"])) $add_make = $rdata["add_make"];
    if(isset($rdata["add_model"])) $add_model = $rdata["add_model"];
    if(isset($rdata["makes"])) $makes = $rdata["makes"];
    if(isset($rdata["models"])) $models = $rdata["models"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    
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
    
    // if the user is not authorized
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // make sure that the phone_number field is large enough. if the 
    // database is an older version, it may not be correct
    // we are going to put this command here since it needs to be executed
    // (multiple executions won't hurt), but we don't want to be executed
    // in a high usage procedure.
    $sql = "ALTER TABLE AircraftScheduling_entry " .
            "CHANGE phone_number phone_number " . 
            "VARCHAR(60) DEFAULT NULL";
	sql_command($sql);
    
    // make sure that the Instructor Time field exists in the database. if the 
    // database is an older version, it may not be there
    $sql = "SELECT Name FROM Categories WHERE Name='INSTRUCTOR TIME' LIMIT 1";
    if (sql_query1($sql) == -1)
    {
        // value doesn't exist, add it to the database
        $sql = "INSERT INTO `Categories` " .
                "( `Name` , `GLAC` , `Can_Be_Changed` , `Record_ID` ) " .
                " VALUES ('INSTRUCTOR TIME', 'C 504-63', '0', '')";
		sql_command($sql);
    }
    
    // user is authorized, show the admin screen
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    // show the page title
    echo "<h2>Administration</h2>";
    
    // put the selection items in a list
    echo "<UL>";
	    
    // show the aircraft management selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
	{
		echo "<li><b>Aircraft</b>"; 

		echo "<ul>";
		
		// show the make and model selection
	    echo "<li>";
		echo "<b><a href='AddModifyMakeModel.php'>Aircraft Make and Model Management</a></b>";

        // aircraft management
		echo "<li>";
		echo "<b><a href='DisplayAircraft.php'>Aircraft Management</a></b>";
		
		// show the print aircraft reports selection if the aircraft checkout 
		// functions are enabled
     	if ($AllowAircraftCheckout)
    	{
		    echo "<li>";
    		echo "<b><a href='PrintAircraftInformation.php" . 
                        "?day=$day&month=$month&year=$year" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    		            "'>Print Aircraft Information</a></b>";
		    echo "<li>";
    		echo "<b><a href='PrintAircraftFaultInformation.php" . 
                        "?day=$day&month=$month&year=$year" .
                        "&goback=" . GetScriptName() .  
			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    		            "'>Print Aircraft Fault Records</a></b>";
    	}
		
    	// show the export selection
    	echo "<li>";
    	echo "<b><a href='ExportUserData.php" . 
                            "?SelectedTable=Aircraft" .
                            "&day=$day&month=$month&year=$year" .
                            "&goback=" . GetScriptName() .  
    			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    			            "'>Export Aircraft Information</a></b>";

        // end the lists
		echo "</li>";
		echo "</ul>";
		echo "<br>";
	}
	
	// display the config section
	echo "<li><b>Configuration</b>"; 
    
    // show the notices selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
	{
    	echo "<ul>";
    	echo "<li>";
		echo "<b><a href='edit_notices.php'>Change Login Notice Message</a></b>";
	}
    
    // show the program configuration selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin) 
	{
		echo "<li>";
		echo "<b><a href='AircraftScheduling_config.php'>Program Configuration</a></b>";
	}
    
    // show the journal selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin) 
	{
    	echo "<li>";
		echo "<b><a href='Journal_Report.php'>View Journal Entries</a></b>";
	}
	
	// complete the admin section
	echo "</li>";
	echo "</ul>";
	echo "<br>";
    
    // show the flight selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
	{
		// show the flight selection if the aircraft checkout functions are
		// enabled
     	if ($AllowAircraftCheckout)
    	{
    		echo "<li><b>Flights</b>"; 
    
    		echo "<ul>";
    
    		echo "<li>";
    		echo "<b><a href='DisplayFlights.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Modify Flight Information</a></b>";
		
        	// show the export selection
        	echo "<li>";
        	echo "<b><a href='ExportUserData.php" . 
                                "?SelectedTable=Flight" .
                                "&day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
        			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
        			            "'>Export Flight Information</a></b>";

    		echo "</li>";
    		echo "</ul>";
    		echo "<br>";
    	}
	}
    
    // show the inventory selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
	{
		// show the inventory selection if the aircraft checkout functions are
		// enabled
     	if ($AllowAircraftCheckout)
    	{
    		echo "<li><b>Inventory</b>"; 
      		echo "<ul>";    
    
            // display category modification for the office user
        	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
        		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice) 
        	{
        		echo "<li>";
        		echo "<b><a href='DisplayCategories.php" . 
                                    "?day=$day&month=$month&year=$year" .
                                    "&goback=" . GetScriptName() .  
        				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
        				            "'>Modify Category Information</a></b>";
            }
    
            // display maintenance inventory for the maintenance user
        	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
        		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
        	{
        		echo "<li>";
        		echo "<b><a href='DisplayInventory.php" . 
        		                    "?InventoryTypeSelection=Maintenance" .
                                    "&day=$day&month=$month&year=$year" .
                                    "&goback=" . GetScriptName() .  
        				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
        				            "'>Modify Maintenance Inventory Information</a></b>";
            }
    
            // display retail inventory for the office user
        	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
        		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice) 
        	{
        		echo "<li>";
        		echo "<b><a href='DisplayInventory.php" . 
        		                    "?InventoryTypeSelection=Retail" .
                                    "&day=$day&month=$month&year=$year" .
                                    "&goback=" . GetScriptName() .  
        				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
        				            "'>Modify Retail Inventory Information</a></b>";
            }
    
            // display wholesale inventory for the office user
        	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
        		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice) 
        	{
        		echo "<li>";
        		echo "<b><a href='DisplayInventory.php" . 
        		                    "?InventoryTypeSelection=Wholesale" .
                                    "&day=$day&month=$month&year=$year" .
                                    "&goback=" . GetScriptName() .  
        				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
        				            "'>Modify Wholesale Inventory Information</a></b>";
            }
            
    		// display the inventory sales items
    		echo "<li>";
    		echo "<b><a href='SellInventory.php" . 
    		                    "?InventoryTypeSelection=All" .
                                "&day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Sell Inventory Items</a></b>";
    		echo "<li>";
    		echo "<b><a href='SellInventoryMultiple.php" . 
    		                    "?InventoryTypeSelection=All" .
                                "&day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Sell Inventory Item to Multiple Users</a></b>";
    
            // print the inventory
    		echo "<li>";
    		echo "<b><a href='PrintInventoryInformation.php" . 
    		                    "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Print Inventory Information</a></b>";

		
        	// show the export selection
        	echo "<li>";
        	echo "<b><a href='ExportUserData.php" . 
                                "?SelectedTable=Inventory" .
                                "&day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
        			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
        			            "'>Export Inventory Information</a></b>";

    		echo "</li>";
    		echo "</ul>";
    		echo "<br>";
    	}
	}
    
    // show the billing selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice) 
	{
		// show the charges selection if the aircraft checkout functions are
		// enabled
     	if ($AllowAircraftCheckout)
    	{
    		echo "<li><b>Billing</b>"; 
    
    		echo "<ul>";
    		echo "<li>";
    		echo "<b><a href='DisplayMemberCharges.php" . 
    		                    "?AllowModifications=1" . 
                                "&day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    		                    "'>Display User Charges</a></b>";
    
    		echo "<li>";
    		echo "<b><a href='DisplayCharges.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Modify Charge Information</a></b>";
    
    		echo "<li>";
    		echo "<b><a href='PrintMonthlySanityCheckInformation.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Print Billing Sanity Check Information</a></b>";
    
    		echo "<li>";
    		echo "<b><a href='PrintDailyBillingInformation.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Print Daily Billing Information</a></b>";
    
    		echo "<li>";
    		echo "<b><a href='PrintDailyDARInformation.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Print Daily DAR Information</a></b>";
    
    		echo "<li>";
    		echo "<b><a href='PrintMonthlyBillingInformation.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Print Monthly Billing Information</a></b>";
		
        	// show the export selection
        	echo "<li>";
        	echo "<b><a href='ExportUserData.php" . 
                                "?SelectedTable=Charge" .
                                "&day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
        			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
        			            "'>Export Charge Information</a></b>";

    		echo "</li>";
    		echo "</ul>";
    		echo "<br>";
    	}
	}
    
    // show the user management selection if the user has access
	if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
		authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelOffice) 
	{
		echo "<li><b>Users</b>"; 

		echo "<ul>";

		// instructor management selection
		echo "<li>";
		echo "<b><a href='DisplayInstructor.php'>Instructor Management</a></b>";
        		
		// show the print member selection if the aircraft checkout functions are
		// enabled
     	if ($AllowAircraftCheckout)
    	{
    		echo "<li>";
    		echo "<b><a href='PrintMemberInformation.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Print User Information</a></b>";
        }
        		
		// show the print member statistics selection if the aircraft checkout functions are
		// enabled
     	if ($AllowAircraftCheckout)
    	{
    		echo "<li>";
    		echo "<b><a href='PrintMemberStatistics.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Print User Statistics</a></b>";
        }
        		
		// show the safety meeting selection if the aircraft checkout functions are
		// enabled
     	if ($AllowAircraftCheckout)
    	{
        	echo "<li>";
    		echo "<b><a href='GetSafetyMeetingDates.php" . 
                                "?day=$day&month=$month&year=$year" .
                                "&goback=" . GetScriptName() .  
    				            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    				            "'>Set Safety Meeting Dates</a></b>";
    	}
		
		// rename username selection
		echo "<li>";
		echo "<b><A href='RenameUsername.php'>Rename Username</a></b>"; 
		
		// user management selection
		echo "<li>";
		echo "<b><A href='DisplayMembers.php'>User Management</a></b>"; 
		
    	// show the export selection
    	echo "<li>";
    	echo "<b><a href='ExportUserData.php" . 
                            "?SelectedTable=User" .
                            "&day=$day&month=$month&year=$year" .
                            "&goback=" . GetScriptName() .  
    			            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
    			            "'>Export User Information</a></b>";
		
		echo "</li>";
		echo "</ul>";
		echo "<br>";
	}
	// let the maintenance login have read only access to the user information
	else if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelMaintenance) 
	{
		echo "<li><b>Users</b>"; 

		echo "<ul>";
		
		// user management selection
		echo "<li>";
		echo "<b><A href='DisplayMembers.php'>User Management</a></b>"; 
		
		echo "</li>";
		echo "</ul>";
		echo "<br>";
	}

    // end the list of items
    echo "</ul>";
    
    echo "<br>";

    include "trailer.inc"
?>
