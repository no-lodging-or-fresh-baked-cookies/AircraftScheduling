<?php
//-----------------------------------------------------------------------------
// 
// CheckCurrency.php
// 
// PURPOSE: Displays the currency fields for the user.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      InstructorResource = selected instructor resource
//      CurrencyName - name of the user to display currency for
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      debug_flag - set to 1 to display debug information
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------
    include "global_def.inc";
    include "config.inc";
    include "$dbsys.inc";
    include "AircraftScheduling_auth.inc";
    include "functions.inc";
    require_once("CurrencyFunctions.inc");
    require_once("StringFunctions.inc");
    
    global $pview;
    global $debug_flag;
    
    // initialize variables
    $sql = '';
    $InstructorResource = "";
    $make = "";
    $model = "";
    $goback = "";
    $GoBackParameters = "";
    $CurrencyName = "";
    $debug_flag = 0;
    $NumberOfDays = 0;
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    if(isset($rdata["CurrencyName"])) $CurrencyName = $rdata["CurrencyName"];
    if(isset($rdata["NumberOfDays"])) $NumberOfDays = $rdata["NumberOfDays"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
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
    
    if(empty($resource))
    	$resource = get_default_resource();
    if(!isset($edit_type))
    	$edit_type = "";
    
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
    
    // can we modify this item?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
        showAccessDenied(
                         $day, $month, $year, 
                         $resource, 
                         $resource_id, 
                         $makemodel, 
                         $goback, 
                         "", 
                         $InstructorResource);
    	exit;
    }
    
    # This page will display the currency of the pilot based on the type of pilot
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    // get the name of the user from the login name if the requested user
    // was not passed in
    if (empty($CurrencyName))
        $NameOfUser = getName();
    else
        $NameOfUser = $CurrencyName;
    
    echo "<form name='main'>";
    
    echo "<table border=0>";
    
    // if the user is a normal user, put in the user name for the currency fields
    if (authGetUserLevel(getUserName(), $auth["admin"]) <= $UserLevelNormal)
    {
    	echo "<tr><td class=CR><b>" . $lang["CurrencyName"] . "</b></td>";
    	echo "<td class=CL>" . htmlentities($NameOfUser) . "</td></tr>";
    	echo "<input type=hidden name='CurrencyName' value='" . $NameOfUser . "'>";
    	
    	// setup script to display currency when the number request days changes
    	echo "<script language='JavaScript'>";
    	echo "function UpdateCurrencyDays()";
    	echo "{";
    	echo "	window.location.href = \"" . $_SERVER["PHP_SELF"] . 
    			"?CurrencyName=$NameOfUser" .
    			"&NumberOfDays=\"" . 
    			" + document.getElementById('NumberOfDays').value + " . 
    			"\"&goback=$goback&GoBackParameters=$GoBackParameters\";";
    	echo "}";
    	echo "</script>";   
    }
    else
    {
    	// admin or super user
    	echo "<tr><td class=CR><b>" . $lang["CurrencyName"] . "</b></td>";
    	    
		// build the selection entries
    	echo "<td class=CL>";
        BuildMemberSelector(
                            $NameOfUser, 
                            false, 
                            "",
                            20,
                            true,
                            false,
                            "DisplayCurrency");
        echo "</td></tr>";
    
    	// setup script to display currency when the name changes
    	echo "<script language='JavaScript'>";
    	echo "function DisplayCurrency()";
    	echo "{";
    	echo "	window.location.href = \"" . $_SERVER["PHP_SELF"] . 
    			"?CurrencyName=\"" . 
    			" + document.getElementById('NameOfUser').value + \"" .
    			"&NumberOfDays=0&goback=$goback&GoBackParameters=$GoBackParameters\";";
    	echo "}";
    	echo "</script>";   
    
    	// setup script to display currency when the number request days changes
    	echo "<script language='JavaScript'>";
    	echo "function UpdateCurrencyDays()";
    	echo "{";
    	echo "	window.location.href = \"" . $_SERVER["PHP_SELF"] . 
    			"?CurrencyName=\"" . 
    			" + document.getElementById('NameOfUser').value + \"" .
    			"&NumberOfDays=\"" . 
    			" + document.getElementById('NumberOfDays').value + " . 
    			"\"&goback=$goback&GoBackParameters=$GoBackParameters\";";
    	echo "}";
    	echo "</script>";   
    }
    
    echo "</table>";
    echo "</form>";
    
    // load the currency fields from the database
    LoadDBCurrencyFields(LookupUserName($NameOfUser));
    
    // get the pilot type
    $PilotType = LookupCurrencyFieldname("Rating");
    
    // put the recent flight experience in the form
    LoadRecentFlightExperience(
                                LookupUserName($NameOfUser), 
                                $PilotType, 
                                0, 
                                true, 
                                $NumberOfDays, 
                                "UpdateCurrencyDays");
    
    // show the currency information
    LoadCurrencyValues(
                        LookupUserName($NameOfUser), 
                        $PilotType, 
                        "ALL", 
                        "ALL", 
                        $FlightStatus, 
                        $FlightStatusReason);
    
    // show the currency status of each aircraft model
	$sql = 
			"SELECT model " .
			"FROM AircraftScheduling_model " .
            "ORDER by model";		
	$res = sql_query($sql);
     
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {
        echo "<br>";    	
        echo "<b>Flight Status for Rental Aircraft Types</b>";    	
        echo "<table bgcolor='#ffffed' border=1>";
        echo "<th>Type</th>";
        echo "<th>Status</th>";
        echo "<th>Reason</th>";

		// build the currency for each rental aircraft model
		for($i=0; $row = sql_row($res, $i); $i++) 
		{
		    if (IsRentalAircraftType($row[0]))
            {
                // initialize the arrays
                $CurrencyRuleString = array();
                $CurrencyStatusString = array();
                
                // load the information from the database and compute the currency
                GetCurrencyValues(
                                    $PilotType,
                                    LookupUserName($NameOfUser),
                                    $CurrencyRuleString,
                                    $CurrencyStatusString,
                                    $PilotIdentificationString,
                                    $FlightStatus,
                                    $FlightStatusReason,
                                    $row[0],
                                    "ALL");
                    
                // show the aircraft type
    		    echo "<tr>";
    		    echo "<td class=CL>$row[0]</td>";
    
                // put the flight status information on the screen
                switch ($FlightStatus)
                {
                Case $ClearedToFly:
                    // cleared to fly
                    if (LookupCurrencyFieldname("Rating") == $StudentPilot)
                    {
        		        echo "<td class=CL>Cleared For Flight - Student</td>";
        		        echo "<td class=CL>&nbsp</td>";
                    }
                    else
                    {
        		        echo "<td class=CL>Cleared For Flight</td>";
        		        echo "<td class=CL>&nbsp</td>";
        		    }
                    break;
                Case $ClearedToFlyDayOnly:
                    // not cleared for night flight, put the information in the form
    		        echo "<td class=CL>Cleared For Flight - Day Only</td>";
    		        if (strlen($FlightStatusReason) > 0)
    		            echo "<td class=CL>$FlightStatusReason</td>";
    		        else
    		            echo "<td class=CL>&nbsp</td>";
                    break;
                Case $ClearedToFlyNoInstruments:
                    // not cleared for instruments, put the information in the form
    		        echo "<td class=CL>Cleared For Flight - VFR Only</td>";
    		        if (strlen($FlightStatusReason) > 0)
    		            echo "<td class=CL>$FlightStatusReason</td>";
    		        else
    		            echo "<td class=CL>&nbsp</td>";
                    break;
                Case $NotClearedToFly:
                    // not cleared no overrides allowed, put the information in the form
    		        echo "<td class=CL>Not Cleared For Flight - Grounded</td>";
    		        if (strlen($FlightStatusReason) > 0)
    		            echo "<td class=CL>$FlightStatusReason</td>";
    		        else
    		            echo "<td class=CL>&nbsp</td>";
                    break;
                Case $NotClearedToFlyOverride:
                    // not cleared overrides allowed, put the information in the form
     		        echo "<td class=CL>Not Cleared For Flight</td>";
    		        if (strlen($FlightStatusReason) > 0)
    		            echo "<td class=CL>$FlightStatusReason</td>";
    		        else
    		            echo "<td class=CL>&nbsp</td>";
                   break;
                }
                echo "</b></h4>";
    	    }
        }

        // end the table
        echo "</table>";
        echo "<br>";
  	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("CheckCurrency", $sql);
    }
    
    // generate return URL
    GenerateReturnURL(
                        $goback, 
                        CleanGoBackParameters($GoBackParameters));
    
    include "trailer.inc" 
?>
