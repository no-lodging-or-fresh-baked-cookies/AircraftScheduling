<?php
//-----------------------------------------------------------------------------
// 
// AddModifyFlights.php
// 
// PURPOSE: Displays the add or modify flight screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      all - select all resources
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      AddModify - set to modify to modify a flight or add to add a flight
//      AddModifyFlights - set to modify to modify a flight
//      FlightCancel - set to Cancel to cancel the update.
//      FlightDelete - set the Delete to delete a flight
//      debug_flag - set to non-zero to enable debug output information
//
//      User information
//          NameOfUser - first and last name of user
//          TailNumber - selected aircraft tailnumber
//          AircraftModel - aircraft model of this flight 
//          Flightday - day (number) for the flight
//          Flightmonth - month (number) for the flight
//          Flightyear - year (number) for the flight
//          ClearingAuthority - clearing authority username
//
//      Hobbs fields
//          BeginningHobbs - beginning hobbs value that the user entered 
//          EndingHobbs - ending hobbs value that the user entered 
//    
//      tach fields
//          BeginningTach - beginning tach value that the user entered 
//          EndingTach - ending tach value that the user entered
//    
//      flight instruction fields
//          InstDualTime - instructor dual time that the user entered
//          InstPAndP - instructor P & P time that the user entered
//          InstKeycode - instructor username that the user entered
//          InstType - type of instruction that the user entered
//    
//      flight time fields
//          FlightTimeDay - hours of day flight time that the user entered
//          FlightTimeNight - hours of night flight time that the user entered
//          FlightTimeHolds - number of holds that the user entered
//          FlightTimeNav - number of navigation intercepts that the user entered
//          FlightTimeInstApp - number of instrument approaches that the user entered
//          FlightTimeDayLndg - number of day landings that the user entered
//          FlightTimeNightLndg - number of night landings that the user entered
//    
//      aircraft fields
//          AircraftLocalFuel - amount of local fuel that the user entered
//          AircraftXCntryFuel - amount of cross-country fuel that the user entered
//          AircraftXCntryFuelCost - cost of the cross-country fuel that the user entered
//          AircraftOil - number of quarts of oil that the user entered
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
    require_once("DatabaseFunctions.inc");

    // initialize variables
    $all = '';
    $CurrentlySelectedAircraftType = '';
    $CurrentlySelectedTailnumber = '';
    $AddModify = "Add";
    
    // User information
    $NameOfUser = getName();
    $TailNumber = '';
    $AircraftModel = ''; 
	$Flightday   = date("d");
	$Flightmonth = date("m");
	$Flightyear  = date("Y");
    $ClearingAuthority = '';

    // initialize Hobbs fields
    $BeginningHobbs = '0'; 
    $EndingHobbs = '0';

    // initialize tach fields
    $BeginningTach = '0';
    $EndingTach = '0';

    // initialize flight instruction fields
    $InstDualTime = '';
    $InstPAndP = '';
    $InstKeycode = '';
    $InstType = $PrivateInstruction;

    // initialize flight time fields
    $FlightTimeDay = '';
    $FlightTimeNight = '';
    $FlightTimeHolds = '';
    $FlightTimeNav = '';
    $FlightTimeInstApp = '';
    $FlightTimeDayLndg = '';
    $FlightTimeNightLndg = '';

    // initialize aircraft fields
    $AircraftLocalFuel = '';
    $AircraftXCntryFuel = '';
    $AircraftXCntryFuelCost = '';
    $AircraftOil = '';

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
    if(isset($rdata["AddModifyFlights"])) $AddModifyFlights = $rdata["AddModifyFlights"];
    if(isset($rdata["AddModify"])) $AddModify = $rdata["AddModify"];
    if(isset($rdata["FlightCancel"])) $FlightCancel = $rdata["FlightCancel"];
    if(isset($rdata["FlightDelete"])) $FlightDelete = $rdata["FlightDelete"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    // User information
    if(isset($rdata["NameOfUser"])) $NameOfUser = $rdata["NameOfUser"];
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    if(isset($rdata["AircraftModel"])) $AircraftModel = $rdata["AircraftModel"];
    if(isset($rdata["Flightday"])) $Flightday = $rdata["Flightday"];
    if(isset($rdata["Flightmonth"])) $Flightmonth = $rdata["Flightmonth"];
    if(isset($rdata["Flightyear"])) $Flightyear = $rdata["Flightyear"];
    if(isset($rdata["ClearingAuthority"])) $ClearingAuthority = $rdata["ClearingAuthority"];
    
    // hobbs fields
    if(isset($rdata["BeginningHobbs"])) $BeginningHobbs = $rdata["BeginningHobbs"];
    if(isset($rdata["EndingHobbs"])) $EndingHobbs = $rdata["EndingHobbs"];
    
    // tach fields
    if(isset($rdata["BeginningTach"])) $BeginningTach = $rdata["BeginningTach"];
    if(isset($rdata["EndingTach"])) $EndingTach = $rdata["EndingTach"];
    
    // flight instruction fields
    if(isset($rdata["InstDualTime"])) $InstDualTime = $rdata["InstDualTime"];
    if(isset($rdata["InstPAndP"])) $InstPAndP = $rdata["InstPAndP"];
    if(isset($rdata["InstKeycode"])) $InstKeycode = $rdata["InstKeycode"];
    if(isset($rdata["InstType"])) $InstType = $rdata["InstType"];
    
    // flight time fields
    if(isset($rdata["FlightTimeDay"])) $FlightTimeDay = $rdata["FlightTimeDay"];
    if(isset($rdata["FlightTimeNight"])) $FlightTimeNight = $rdata["FlightTimeNight"];
    if(isset($rdata["FlightTimeHolds"])) $FlightTimeHolds = $rdata["FlightTimeHolds"];
    if(isset($rdata["FlightTimeNav"])) $FlightTimeNav = $rdata["FlightTimeNav"];
    if(isset($rdata["FlightTimeInstApp"])) $FlightTimeInstApp = $rdata["FlightTimeInstApp"];
    if(isset($rdata["FlightTimeDayLndg"])) $FlightTimeDayLndg = $rdata["FlightTimeDayLndg"];
    if(isset($rdata["FlightTimeNightLndg"])) $FlightTimeNightLndg = $rdata["FlightTimeNightLndg"];
    
    // aircraft fields
    if(isset($rdata["AircraftLocalFuel"])) $AircraftLocalFuel = $rdata["AircraftLocalFuel"];
    if(isset($rdata["AircraftXCntryFuel"])) $AircraftXCntryFuel = $rdata["AircraftXCntryFuel"];
    if(isset($rdata["AircraftXCntryFuelCost"])) $AircraftXCntryFuelCost = $rdata["AircraftXCntryFuelCost"];
    if(isset($rdata["AircraftOil"])) $AircraftOil = $rdata["AircraftOil"];

    // original fields
    if(isset($rdata["OldMemberKeyCode"])) $OldMemberKeyCode = $rdata["OldMemberKeyCode"];
    if(isset($rdata["OldInstructorKeyCode"])) $OldInstructorKeyCode = $rdata["OldInstructorKeyCode"];
    if(isset($rdata["OldDualTime"])) $OldDualTime = $rdata["OldDualTime"];
    if(isset($rdata["OldPPTime"])) $OldPPTime = $rdata["OldPPTime"];
    if(isset($rdata["OldFlightDate"])) $OldFlightDate = $rdata["OldFlightDate"];
    if(isset($rdata["OldBeginHobbs"])) $OldBeginHobbs = $rdata["OldBeginHobbs"];
    if(isset($rdata["OldEndHobbs"])) $OldEndHobbs = $rdata["OldEndHobbs"];
    if(isset($rdata["OldAircraftID"])) $OldAircraftID = $rdata["OldAircraftID"];
    if(isset($rdata["OldAircraftType"])) $OldAircraftType = $rdata["OldAircraftType"];
    if(isset($rdata["OldOilUsed"])) $OldOilUsed = $rdata["OldOilUsed"];
    if(isset($rdata["OldInstType"])) $OldInstType = $rdata["OldInstType"];
        
    // filter parameters (from display flights screen)
    if(isset($rdata["FilterName"])) $FilterName = $rdata["FilterName"];
    if(isset($rdata["FilterAircraft"])) $FilterAircraft = $rdata["FilterAircraft"];
    if(isset($rdata["FromDay"])) $FromDay = $rdata["FromDay"];
    if(isset($rdata["FromMonth"])) $FromMonth = $rdata["FromMonth"];
    if(isset($rdata["FromYear"])) $FromYear = $rdata["FromYear"];
    if(isset($rdata["ToDay"])) $ToDay = $rdata["ToDay"];
    if(isset($rdata["ToMonth"])) $ToMonth = $rdata["ToMonth"];
    if(isset($rdata["ToYear"])) $ToYear = $rdata["ToYear"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
        
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
        
    //********************************************************************
    // BuildAircraftSelectorList()
    //
    // Purpose: Display a selector for the list of aircraft.
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
    function BuildAircraftSelectorList()
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	
    	global $TailNumber;	
    	global $AircraftModel;
    	global $SpecialAircraftTailnumber;
    
        // hobbs fields
        global $BeginningHobbs;
        global $EndingHobbs;
        
        // tach fields
        global $BeginningTach;
        global $EndingTach;
        
        // build the aircraft tailnumber filter
		$sql = 
				"SELECT " .
				    "a.n_number, " .
				    "a.Current_Hobbs, " .
				    "a.tach1, " .
				    "c.model " .
				"FROM " .
				    "AircraftScheduling_aircraft a, " .
    			    "AircraftScheduling_model c " .
        		"WHERE " .
        			"a.model_id=c.model_id " .
                "ORDER by n_number";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {    	
            // init the javascript array values
            $HobbsArray = "";
            $TachArray = "";
            $ModelArray = "";

            // build the select HTML	
    		echo "<SELECT NAME='TailNumber' id='TailNumber'onChange=SelectAircraft()>";
    		
    		// build the selection entries
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
    		    // if the tailnumber is empty, set it to the first valid aircraft
    		    if (strlen($TailNumber) == 0)
    		    {
    		        // set tailnumber and model to valid values
    		        $TailNumber = $row[0];
    		        $AircraftModel = $row[3];
    		        $BeginningHobbs = $row[1];
    		        $EndingHobbs = $row[1];
    		        $BeginningTach = $row[2];
    		        $EndingTach = $row[2];
    		    }
    		    
    			// build the option value
    			echo "<OPTION " .
    					"VALUE='" . $row[0] . "'" . 
    					(Ucase($row[0]) == UCase($TailNumber) ? " SELECTED" : "") . 
    					">$row[0]";
    					
                // save the aircraft parameters for building the javascript arrays
                if ($i < (sql_count($res) - 1))
                {
                    // not the last element, add a comma
                    $HobbsArray = $HobbsArray . "$row[1],";
                    $TachArray = $TachArray . "$row[2],";
                    $ModelArray = $ModelArray . "'$row[3]',";
                }
                else
                {
                    // last row, no comma
                    $HobbsArray = $HobbsArray . "$row[1]";
                    $TachArray = $TachArray . "$row[2]";
                    $ModelArray = $ModelArray . "'$row[3]'";
                }
    		}
    		
    		// add the special tailnumber (no charges)
			echo "<OPTION " .
					"VALUE='" . $SpecialAircraftTailnumber . "'" . 
					(Ucase($SpecialAircraftTailnumber) == UCase($TailNumber) ? " SELECTED" : "") . 
					">$SpecialAircraftTailnumber";

            // end the select box
    		echo "</SELECT>";
            
            // save the aircraft parameters for the javascript procedures
            echo "<SCRIPT LANGUAGE=\"JavaScript\">";
            echo "var HobbsArray = new Array($HobbsArray);";
            echo "var TachArray = new Array($TachArray);";
            echo "var ModelArray = new Array($ModelArray);";
            echo "</SCRIPT>";
	
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildAircraftSelectorList", $sql);
        }
    }
    
    //********************************************************************
    // BuildAircraftTypeSelector()
    //
    // Purpose: Display a selector for the list of aircraft types.
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
    function BuildAircraftTypeSelector()
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	
    	global $AircraftModel;
        
        // build the aircraft tailnumber filter
		$sql = 
				"SELECT model " .
				"FROM AircraftScheduling_model " .
                "ORDER by model";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {    	
            // build the select HTML	
    		echo "<SELECT NAME='AircraftModel' id='AircraftModel'>";
    		
    		// build the selection entries
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
    			echo "<OPTION " .
    					"VALUE='" . $row[0] . "'" . 
    					(Ucase($row[0]) == UCase($AircraftModel) ? " SELECTED" : "") . 
    					">$row[0]";
    		}
    		echo "</SELECT>";	
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildAircraftTypeSelector", $sql);
        }
    }
    
    //********************************************************************
    // BuildFlightTableEntry(
    //                   $ControlTitle, 
    //                   $FlightControlName,
    //                   $DefaultValue, 
    //                   $AllowChanges,
    //                   $TableName)
    //
    // Purpose: Build a table row to display a control.
    //
    // Inputs:
    //   ControlTitle - the title for the control
    //   FlightControlName - the name of the item
    //   DefaultValue - default value for the input control
    //   AllowChanges - set to 1 to allow changes to the entries.
    //   TableName - name of the table that contains the input
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildFlightTableEntry(
                                $ControlTitle, 
                                $FlightControlName,
                                $DefaultValue, 
                                $AllowChanges,
                                $TableName)
     {	
        global $Column1Width, $Column2Width;

    	// set the size of the input boxes
    	$ControlNameBoxSize = 4;

        // make sure we don't have any blanks in the control names
        $FlightControlName = Replace($FlightControlName, " ", "_");
        $TableName = Replace($TableName, " ", "_");

        // each entry is a table row
        echo "<TR>"; 
        
        // build the title column
        echo "<TD ALIGN=LEFT WIDTH=$Column1Width>$ControlTitle</TD>";        
    
        // build the column and let them change it if requested
        if ($AllowChanges)
        {
            // build the input column and let them change it.
            echo "<td align=right width=$Column2Width>" . 
                    "<input " .
                        "type=text " .
                        "name='$FlightControlName' " . 
                        "id='$FlightControlName' " .
                        "align=right " . 
                        "size=$ControlNameBoxSize " . 
                        "value='" . $DefaultValue . "' " . 
                        "Onchange='UpdateFlightControl(\"$FlightControlName\", \"$TableName\")'>" . 
                    "</td>";
        }
        else
        {
            echo "<td align=right width=$Column2Width>" . $DefaultValue . "</TD>";
        }
        
        // end the table row
        echo "</TR>";    	    
    }

    //********************************************************************
    // DisplayHobbsFields()
    //
    // Purpose:  Display the aircraft flight hobbs fields for the user to
    //           enter the flight information.
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
    function DisplayHobbsFields()
    {    	
        // Hobbs fields
        global $BeginningHobbs; 
        global $EndingHobbs;
        
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
         
        // set the table name  	
        $TableName = "HobbsTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Hobbs</td>";
        echo "</tr>";
       
        // beginning hobbs
        BuildFlightTableEntry(
                                "Beginning", 
                                "BeginningHobbs", 
                                sprintf("%1.1f", $BeginningHobbs), 
                                $AllowChanges, 
                                $TableName);
       
        // ending hobbs
        BuildFlightTableEntry(
                                "Ending", 
                                "EndingHobbs", 
                                sprintf("%1.1f", $EndingHobbs), 
                                $AllowChanges, 
                                $TableName);
       
        // elapsed hobbs
        BuildFlightTableEntry(
                                "Elapsed", 
                                "", 
                                sprintf("%1.1f", $EndingHobbs - $BeginningHobbs), 
                                $DontAllowChanges, 
                                $TableName);
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayTachFields()
    //
    // Purpose:  Display the aircraft flight tach fields for the user to
    //           enter the flight information.
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
    function DisplayTachFields()
    {    	        
        // tach fields
        global $BeginningTach;
        global $EndingTach;
        
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
         
        // set the table name  	
        $TableName = "TachTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Tach</td>";
        echo "</tr>";
       
        // beginning tach
        BuildFlightTableEntry(
                                "Beginning", 
                                "BeginningTach", 
                                sprintf("%1.1f", $BeginningTach), 
                                $AllowChanges, 
                                $TableName);
       
        // beginning tach
        BuildFlightTableEntry(
                                "Ending", 
                                "EndingTach", 
                                sprintf("%1.1f", $EndingTach), 
                                $AllowChanges, 
                                $TableName);
       
        // elapsed tach
        BuildFlightTableEntry(
                                "Elapsed", 
                                "", 
                                sprintf("%1.1f", $EndingTach - $BeginningTach), 
                                $DontAllowChanges, 
                                $TableName);
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayFlightInstFields()
    //
    // Purpose:  Display the aircraft flight flight instructor fields for 
    //           the user to enter the flight information.
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
    function DisplayFlightInstFields()
    {    	
        global $Column1Width, $Column2Width;
        global $FreeInstruction, $NoInstruction, $GroundInstruction;
        global $PrivateInstruction, $InstrumentInstruction;
        global $CommercialInstruction, $CFIInstruction, $CFIIInstruction;

        // flight instruction fields
        global $InstDualTime;
        global $InstPAndP;
        global $InstKeycode;
        global $InstType;
        
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
         
        // set the table name  	
        $TableName = "FlightInstTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Flight Instructor</td>";
        echo "</tr>";
       
        // keycode
        echo "<TR><TD ALIGN=LEFT COLSPAN=2>";        
        BuildInstructorSelector(
                            GetNameFromUsername($InstKeycode), 
                            true, 
                            "InstKeycode",
                            20,
                            true,
                            false,
                            "UpdateFlightInstructorInfo",
                            true);
        echo "</TR>";    	    

        // dual time
        BuildFlightTableEntry(
                                "Dual Time", 
                                "InstDualTime", 
                                $InstDualTime, 
                                $AllowChanges, 
                                $TableName);
       
        // P & P
        BuildFlightTableEntry(
                                "P & P", 
                                "InstPAndP", 
                                $InstPAndP, 
                                $AllowChanges, 
                                $TableName);
       
       
        // flight instruction type
        echo "<TR><TD ALIGN=LEFT COLSPAN=2>";        
    
        // build the drop down box for selecting flight instruction type
        echo "<SELECT NAME='InstType'>";
        echo "<OPTION" . ($InstType == $NoInstruction ? " SELECTED" : "") . ">$NoInstruction";
        echo "<OPTION" . ($InstType == $FreeInstruction ? " SELECTED" : "") . ">$FreeInstruction";
        echo "<OPTION" . ($InstType == $GroundInstruction ? " SELECTED" : "") . ">$GroundInstruction";
        echo "<OPTION" . ($InstType == $PrivateInstruction ? " SELECTED" : "") . ">$PrivateInstruction";
        echo "<OPTION" . ($InstType == $InstrumentInstruction ? " SELECTED" : "") . ">$InstrumentInstruction";
        echo "<OPTION" . ($InstType == $CommercialInstruction ? " SELECTED" : "") . ">$CommercialInstruction";
        echo "<OPTION" . ($InstType == $CFIInstruction ? " SELECTED" : "") . ">$CFIInstruction";
        echo "<OPTION" . ($InstType == $CFIIInstruction ? " SELECTED" : "") . ">$CFIIInstruction";
        echo "</SELECT>";
        
        // end the table row
        echo "</TR>";    	    
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayFlightTimeFields()
    //
    // Purpose:  Display the aircraft flight flight time fields for 
    //           the user to enter the flight information.
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
    function DisplayFlightTimeFields()
    {    	
        // flight time fields
        global $FlightTimeDay;
        global $FlightTimeNight;
        global $FlightTimeHolds;
        global $FlightTimeNav;
        global $FlightTimeInstApp;
        global $FlightTimeDayLndg;
        global $FlightTimeNightLndg;

        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
         
        // set the table name  	
        $TableName = "FlightTimeTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Flight Time</td>";
        echo "</tr>";
       
        // day
        BuildFlightTableEntry(
                                "Day", 
                                "FlightTimeDay", 
                                $FlightTimeDay, 
                                $AllowChanges, 
                                $TableName);
       
        // night
        BuildFlightTableEntry(
                                "Night", 
                                "FlightTimeNight", 
                                $FlightTimeNight, 
                                $AllowChanges, 
                                $TableName);
       
        // holding procedures
        BuildFlightTableEntry(
                                "Holding Procedures", 
                                "FlightTimeHolds", 
                                $FlightTimeHolds, 
                                $AllowChanges, 
                                $TableName);
       
        // navigation intercepts
        BuildFlightTableEntry(
                                "Navigation Intercepts", 
                                "FlightTimeNav", 
                                $FlightTimeNav, 
                                $AllowChanges, 
                                $TableName);
       
        // instrument approaches
        BuildFlightTableEntry(
                                "Instrument Approaches", 
                                "FlightTimeInstApp", 
                                $FlightTimeInstApp, 
                                $AllowChanges, 
                                $TableName);
       
        // day landings
        BuildFlightTableEntry(
                                "Day Landings", 
                                "FlightTimeDayLndg", 
                                $FlightTimeDayLndg, 
                                $AllowChanges, 
                                $TableName);
       
        // night landings
        BuildFlightTableEntry(
                                "Night Landings", 
                                "FlightTimeNightLndg", 
                                $FlightTimeNightLndg, 
                                $AllowChanges, 
                                $TableName);

        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayAircraftFields()
    //
    // Purpose:  Display the aircraft flight aircraft fields for 
    //           the user to enter the flight information.
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
    function DisplayAircraftFields()
    {    	
        // aircraft fields
        global $AircraftLocalFuel;
        global $AircraftXCntryFuel;
        global $AircraftXCntryFuelCost;
        global $AircraftOil;

        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
         
        // set the table name  	
        $TableName = "AircraftTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Aircraft</td>";
        echo "</tr>";
       
        // local fuel
        BuildFlightTableEntry(
                                "Local Fuel (gals)", 
                                "AircraftLocalFuel", 
                                $AircraftLocalFuel, 
                                $AllowChanges, 
                                $TableName);
       
        // cross country fuel
        BuildFlightTableEntry(
                                "Cross Country Fuel (gals)", 
                                "AircraftXCntryFuel", 
                                $AircraftXCntryFuel, 
                                $AllowChanges, 
                                $TableName);
       
        // cross country fuel cost
        BuildFlightTableEntry(
                                "Cross Country Fuel Cost", 
                                "AircraftXCntryFuelCost", 
                                -1 * GetNumber($AircraftXCntryFuelCost), 
                                $AllowChanges, 
                                $TableName);
       
        // oil
        BuildFlightTableEntry(
                                "Oil (qts)", 
                                "AircraftOil", 
                                $AircraftOil, 
                                $AllowChanges, 
                                $TableName);

        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayFlightFields($TailNumber)
    //
    // Purpose:  Display the aircraft flight fields for the user to
    //           enter the flight information.
    //
    // Inputs:
    //   TailNumber - tailnumber of the currently selected aircraft
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayFlightFields($TailNumber)
    { 
        global $Column1Width, $Column2Width;
    
        // hobbs fields
        global $BeginningHobbs;
        global $EndingHobbs;
        
        // tach fields
        global $BeginningTach;
        global $EndingTach;
        
        // flight instruction fields
        global $InstDualTime;
        global $InstPAndP;
        global $InstKeycode;
        global $InstType;
        
        // flight time fields
        global $FlightTimeDay;
        global $FlightTimeNight;
        global $FlightTimeHolds;
        global $FlightTimeNav;
        global $FlightTimeInstApp;
        global $FlightTimeDayLndg;
        global $FlightTimeNightLndg;
        
        // aircraft fields
        global $AircraftLocalFuel;
        global $AircraftXCntryFuel;
        global $AircraftXCntryFuelCost;
        global $AircraftOil;

        // set the column sizes
        $Column1Width = "80%";
        $Column2Width = "20%";

        // start the table to display the aircraft flight fields
        echo "<table border=0>";
    
        // display the title
        echo "<TR><TD COLSPAN=4>";
        echo "<CENTER><H4>Check-in Information</H4>";
        echo "</CENTER></TD></TR>";
         
        // fill in the left column information
        echo "<TR><TD>";
        DisplayHobbsFields();
        echo "<BR>";
        DisplayTachFields($BeginningTach, $EndingTach);
        echo "<BR>";
        DisplayFlightInstFields();
        echo "</TD>";
    
        // skip some space between columns
        echo "<TD> </TD>";
        echo "<TD> </TD>";
        
        // fill in the right column information
        echo "<TD>";
        DisplayFlightTimeFields();
        DisplayAircraftFields();
        echo "</TD></TR>";
    
        // finished with the table
        echo "</table>";
    }
                
    //********************************************************************
    //   DeleteFlight()
    //
    // Purpose:  Delete a flight from the database.
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
    function DeleteFlight()
    {
        global $OldMemberKeyCode;
        global $OldInstructorKeyCode;
        global $OldDualTime;
        global $OldPPTime;
        global $OldFlightDate;
        global $OldBeginHobbs;
        global $OldEndHobbs;
        global $OldAircraftID;
        global $OldAircraftType;
        global $OldOilUsed;
        global $OldInstType;

        // save the instructor information so we can delete the instructor's record
        $InstructorKeyCode = $OldInstructorKeyCode;
        $StudentKeyCode = $OldMemberKeyCode;
        $InstructionDate = $OldFlightDate;
        $AircraftID = $OldAircraftID;
        $ModelID = LookupModelID($OldAircraftType);
        $HobbsElapsed = RoundToDecimalPlaces($OldEndHobbs - $OldBeginHobbs, 1);
        
        // user responded yes, delete the record
        DeleteDatabaseRecord("Flight",
            "(" .
                "Keycode='" . $OldMemberKeyCode . "' AND " .
                "Date='" . FormatField($OldFlightDate, "DatabaseDate") . "' AND " .
                "Aircraft='" . $OldAircraftID . "' AND " .
                "model_id=" . $ModelID . " AND " .
                "Instruction_Type='" . $OldInstType . "' AND " .
                "ROUND(Hobbs_Elapsed, 1)=" . RoundToDecimalPlaces($HobbsElapsed, 1) .
            ") LIMIT 1");
    
        // if we had instruction time for this flight, look for the instructor's
        // record and delete that record
        if (Len($InstructorKeyCode) > 0)
        {
            DeleteFlightInstructorRecord(
                                $InstructorKeyCode,
                                $StudentKeyCode,
                                $OldDualTime,
                                $OldPPTime,
                                $InstructionDate,
                                $AircraftID,
                                $ModelID);
        }
        
        // log the change in the journal
    	$Description = 
                        "Deleting flight for " . $OldMemberKeyCode .
                        " (" . GetNameFromUsername($OldMemberKeyCode) . ")" .
                        " on date " . FormatField($OldFlightDate, "Date") .
                        " for aircraft " . $OldAircraftID;
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    }
    
    //********************************************************************
    //   UpdateFlightInstructorCharges(
    //                                 OldStudentKeyCode As String,
    //                                 OldInstructorKeyCode As String,
    //                                 OldDualTime As Double,
    //                                 OldPPTime As Double,
    //                                 OldFlightDate As String,
    //                                 OldBeginHobbs As Double,
    //                                 OldEndHobbs As Double,
    //                                 OldAircraftID As String,
    //                                 OldAircraftType As integer,
    //                                 NewStudentKeyCode as String,
    //                                 NewInstructorKeyCode As String,
    //                                 NewInstructionType As String)
    //
    // Purpose:  Update flight instructor charges. This procedure is called
    //           when a flight is changed if the dual or PP time is changed
    //           for the flight so that the instructor's charges can be
    //           updated.
    //
    // Inputs:
    //   OldStudentKeycode - the keycode for the student
    //   OldInstructorKeyCode - old instructor keycode
    //   OldDualTime - old dual time
    //   OldPPTime - old P & P time
    //   OldFlightDate - old date of the flight
    //   OldBeginHobbs - old beginning hobbs
    //   OldEndHobbs - old ending hobbs
    //   OldAircraftID - old aircraft tail number
    //   OldAircraftType - old aircraft type
    //   NewStudentKeyCode - new keycode for the student
    //   NewInstructorKeyCode - new instructor keycode
    //   NewInstructionType - type of instruction
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function UpdateFlightInstructorCharges(
                                            $OldStudentKeyCode,
                                            $OldInstructorKeyCode,
                                            $OldDualTime,
                                            $OldPPTime,
                                            $OldFlightDate,
                                            $OldBeginHobbs,
                                            $OldEndHobbs,
                                            $OldAircraftID,
                                            $OldAircraftType,
                                            $NewStudentKeyCode,
                                            $NewInstructorKeyCode,
                                            $NewInstructionType)
    {                                                    
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        
        // User information
        global $NameOfUser;
        global $TailNumber;
        global $AircraftModel;
        global $Flightday;
        global $Flightmonth;
        global $Flightyear;
        global $ClearingAuthority;
    
        // hobbs fields
        global $BeginningHobbs;
        global $EndingHobbs;
        
        // tach fields
        global $BeginningTach;
        global $EndingTach;
        
        // flight instruction fields
        global $InstDualTime;
        global $InstPAndP;
        global $InstKeycode;
        global $InstType;
        
        // flight time fields
        global $FlightTimeDay;
        global $FlightTimeNight;
        global $FlightTimeHolds;
        global $FlightTimeNav;
        global $FlightTimeInstApp;
        global $FlightTimeDayLndg;
        global $FlightTimeNightLndg;
        
        // aircraft fields
        global $AircraftLocalFuel;
        global $AircraftXCntryFuel;
        global $AircraftXCntryFuelCost;
        global $AircraftOil;

        // delete the old instructor record and add a new one
        DeleteFlightInstructorRecord(
                                    $OldInstructorKeyCode,
                                    $OldStudentKeyCode,
                                    $OldDualTime,
                                    $OldPPTime,
                                    $OldFlightDate,
                                    $OldAircraftID,
                                    LookupModelID($OldAircraftType));
        
        // set the fields
        $DatabaseFields = array();
        SetDatabaseRecord("Keycode",
                            UCase(Trim($NewInstructorKeyCode)), $RSConversionString, $DatabaseFields[0]);
        SetDatabaseRecord("Date", 
                            FormatField(BuildDate($Flightday, $Flightmonth, $Flightyear), "DatabaseDate"), 
                            $RSConversionString, $DatabaseFields[1]);
        SetDatabaseRecord("Aircraft",
                        $TailNumber, $RSConversionString, $DatabaseFields[2]);
        SetDatabaseRecord("model_id",
                            LookupModelID($AircraftModel), $RSConversionNumber, $DatabaseFields[3]);
        SetDatabaseRecord("Begin_Hobbs",
                            $BeginningHobbs, $RSConversionNumber, $DatabaseFields[4]);
        SetDatabaseRecord("End_Hobbs", $EndingHobbs, $RSConversionNumber, $DatabaseFields[5]);
        SetDatabaseRecord("Begin_Tach", $BeginningTach, $RSConversionNumber, $DatabaseFields[6]);
        SetDatabaseRecord("End_Tach", $EndingTach, $RSConversionNumber, $DatabaseFields[7]);
        SetDatabaseRecord("Day_Time", $FlightTimeDay, $RSConversionNumber, $DatabaseFields[8]);
        SetDatabaseRecord("Night_Time", $FlightTimeNight, $RSConversionNumber, $DatabaseFields[9]);
        SetDatabaseRecord("Instruction_Type", $InstructorInstruction, $RSConversionString, $DatabaseFields[10]);
        SetDatabaseRecord("Student_Keycode", $NewStudentKeyCode, $RSConversionString, $DatabaseFields[11]);
        SetDatabaseRecord("Dual_Time", $InstDualTime, $RSConversionNumber, $DatabaseFields[12]);
        SetDatabaseRecord("Dual_PP_Time", $InstPAndP, $RSConversionNumber, $DatabaseFields[13]);
        SetDatabaseRecord("Instructor_Keycode", " ", $RSConversionString, $DatabaseFields[14]);
        SetDatabaseRecord("Day_Landings", $FlightTimeDayLndg, $RSConversionNumber, $DatabaseFields[15]);
        SetDatabaseRecord("Night_Landings", $FlightTimeNightLndg, $RSConversionNumber, $DatabaseFields[16]);
        SetDatabaseRecord("Navigation_Intercepts", $FlightTimeNav, $RSConversionNumber, $DatabaseFields[17]);
        SetDatabaseRecord("Holding_Procedures", $FlightTimeHolds, $RSConversionNumber, $DatabaseFields[18]);
        SetDatabaseRecord("Instrument_Approach", $FlightTimeInstApp, $RSConversionNumber, $DatabaseFields[19]);
        
        //save the fuel information
        SetDatabaseRecord("Local_Fuel", 0, $RSConversionNumber, $DatabaseFields[20]);
        SetDatabaseRecord("Fuel_Cost", 0, $RSConversionNumber, $DatabaseFields[21]);
        SetDatabaseRecord("Local_Fuel_Cost", 0, $RSConversionNumber, $DatabaseFields[22]);
        SetDatabaseRecord("Cross_Country_Fuel", 0, $RSConversionNumber, $DatabaseFields[23]);
        SetDatabaseRecord("Cross_Country_Fuel_Credit", 0, $RSConversionNumber, $DatabaseFields[24]);
        
        // save the oil used
        SetDatabaseRecord("Oil", 0, $RSConversionNumber, $DatabaseFields[25]);
        SetDatabaseRecord("Oil_Rate", 0, $RSConversionNumber, $DatabaseFields[26]);
        SetDatabaseRecord("Oil_Cost", 0, $RSConversionNumber, $DatabaseFields[27]);
    
        // update the aircraft costs (no cost for the instructor)
        $HobbsElasped =
                RoundToDecimalPlaces($EndingHobbs -
                $BeginningHobbs, 1);
        SetDatabaseRecord("Hobbs_Elapsed", $HobbsElasped, $RSConversionNumber, $DatabaseFields[28]);
        SetDatabaseRecord("Aircraft_Rate", 0, $RSConversionNumber, $DatabaseFields[29]);
        SetDatabaseRecord("Aircraft_Cost", 0, $RSConversionNumber, $DatabaseFields[30]);
        SetDatabaseRecord("Owner_Rate", 0, $RSConversionNumber, $DatabaseFields[31]);
        SetDatabaseRecord("Owner_Reimbursement", 0, $RSConversionNumber, $DatabaseFields[32]);
        
        // save the instructor times
        $InstructionRate = -1 * GetInstructorCreditRate(Trim($NewInstructorKeyCode), $NewInstructionType);
        SetDatabaseRecord("Instruction_Rate", $InstructionRate, $RSConversionNumber, $DatabaseFields[33]);
        SetDatabaseRecord("Instructor_Charge",
                $InstructionRate *
                (Val($InstDualTime) +
                Val($InstPAndP)), $RSConversionNumber, $DatabaseFields[34]);
        SetDatabaseRecord("Cleared_By", $ClearingAuthority, $RSConversionString, $DatabaseFields[35]);
                            
        // save the fields to the database
        AddDatabaseRecord("Flight", $DatabaseFields);
    }
         
    //********************************************************************
    // UpdateFlightInformation($TailNumber)
    //
    // Purpose: Update the flight information database from the information
    //          entered by the user on the screen
    //
    // Inputs:
    //   TailNumber - aircraft tailnumber we are updating a flight for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function UpdateFlightInformation($TailNumber)
    {        
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        global $AddModify;
    	global $SpecialAircraftTailnumber;
        
        // User information
        global $NameOfUser;
        global $TailNumber;
        global $AircraftModel;
        global $Flightday;
        global $Flightmonth;
        global $Flightyear;
        global $ClearingAuthority;

        // flight time fields
        global $FlightTimeDay;
        global $FlightTimeNight;
        global $FlightTimeHolds;
        global $FlightTimeNav;
        global $FlightTimeInstApp;
        global $FlightTimeDayLndg;
        global $FlightTimeNightLndg;
    
        // hobbs fields
        global $BeginningHobbs;
        global $EndingHobbs;
        
        // tach fields
        global $BeginningTach;
        global $EndingTach;
        
        // flight instruction fields
        global $InstDualTime;
        global $InstPAndP;
        global $InstKeycode;
        global $InstType;
        
        // flight time fields
        global $FlightTimeDay;
        global $FlightTimeNight;
        global $FlightTimeHolds;
        global $FlightTimeNav;
        global $FlightTimeInstApp;
        global $FlightTimeDayLndg;
        global $FlightTimeNightLndg;
        
        // aircraft fields
        global $AircraftLocalFuel;
        global $AircraftXCntryFuel;
        global $AircraftXCntryFuelCost;
        global $AircraftOil;
        
        global $OldMemberKeyCode;
        global $OldInstructorKeyCode;
        global $OldDualTime;
        global $OldPPTime;
        global $OldFlightDate;
        global $OldBeginHobbs;
        global $OldEndHobbs;
        global $OldAircraftID;
        global $OldAircraftType;
        global $OldOilUsed;
        global $OldInstType;
        
        global $OldInstType;

        // find the current aircraft if it is not the special tail number
        if (UCase(Trim($TailNumber)) != UCase($SpecialAircraftTailnumber))
        {
            // get the information from the database about the selected aircraft
            $sql = "SELECT
                        hourly_cost,
                        rental_fee,
                        Oil_Type  
            		FROM AircraftScheduling_aircraft  
            		WHERE n_number='$TailNumber'";    
            $res = sql_query($sql);
            
            // if we didn't have any errors, process the results of the database inquiry
            if($res) 
            {
                // process the results of the database inquiry
                $row = sql_row($res, 0);
            }
            else
            {
                // error processing database request, tell the user
                DisplayDatabaseError("UpdateFlightInformation", $sql);
            }
        }
        
        // make sure that the non-zero length text fields have blanks in them
        if (Len($InstKeycode) == 0) $InstKeycode = " ";
        
        // save the values from the screen
        $DatabaseFields = array();
		SetDatabaseRecord("Keycode", GetUsernameFromName($NameOfUser), $RSConversionString, $DatabaseFields[0]);
        
        // flight information
        SetDatabaseRecord("Aircraft", $TailNumber, $RSConversionString, $DatabaseFields[1]);
        SetDatabaseRecord("Cleared_By", $ClearingAuthority, $RSConversionString, $DatabaseFields[2]);
        SetDatabaseRecord("Date", FormatField(BuildDate($Flightday, $Flightmonth, $Flightyear), "DatabaseDate"), $RSConversionString, $DatabaseFields[3]);
        SetDatabaseRecord("model_id", LookupModelID($AircraftModel), $RSConversionNumber, $DatabaseFields[4]);
        
        // check-in information
        // hobbs
        SetDatabaseRecord("Begin_Hobbs", Val($BeginningHobbs), $RSConversionNumber, $DatabaseFields[5]);
        SetDatabaseRecord("End_Hobbs", Val($EndingHobbs), $RSConversionNumber, $DatabaseFields[6]);
        
        // tach
        SetDatabaseRecord("Begin_Tach", Val($BeginningTach), $RSConversionNumber, $DatabaseFields[7]);
        SetDatabaseRecord("End_Tach", Val($EndingTach), $RSConversionNumber, $DatabaseFields[8]);
        
        // flight instruction
        SetDatabaseRecord("Dual_Time", RoundToDecimalPlaces(Val($InstDualTime), 1), $RSConversionNumber, $DatabaseFields[9]);
        SetDatabaseRecord("Dual_PP_Time", RoundToDecimalPlaces(Val($InstPAndP), 1), $RSConversionNumber, $DatabaseFields[10]);
        SetDatabaseRecord("Student_Keycode", " ", $RSConversionString, $DatabaseFields[11]);
        SetDatabaseRecord("Instruction_Type", $InstType, $RSConversionString, $DatabaseFields[12]);
        SetDatabaseRecord("Instructor_Keycode", Trim($InstKeycode), $RSConversionString, $DatabaseFields[13]);
        
        // flight time
        SetDatabaseRecord("Day_Time", $FlightTimeDay, $RSConversionNumber, $DatabaseFields[14]);
        SetDatabaseRecord("Night_Time", $FlightTimeNight, $RSConversionNumber, $DatabaseFields[15]);
        SetDatabaseRecord("Holding_Procedures", $FlightTimeHolds, $RSConversionNumber, $DatabaseFields[16]);
        SetDatabaseRecord("Navigation_Intercepts", $FlightTimeNav, $RSConversionNumber, $DatabaseFields[17]);
        SetDatabaseRecord("Instrument_Approach", $FlightTimeInstApp, $RSConversionNumber, $DatabaseFields[18]);
        SetDatabaseRecord("Day_Landings", $FlightTimeDayLndg, $RSConversionNumber, $DatabaseFields[19]);
        SetDatabaseRecord("Night_Landings", $FlightTimeNightLndg, $RSConversionNumber, $DatabaseFields[20]);
                
        //save the fuel information
        SetDatabaseRecord("Local_Fuel", Val($AircraftLocalFuel), $RSConversionNumber, $DatabaseFields[21]);
        // if this aircraft is a rental aircraft, don't charge for fuel
        if (UCase(Trim($TailNumber)) == UCase($SpecialAircraftTailnumber) ||
            sql_count($res) == 0)
        {
            // special tailnumber or non-club aircraft, charge for fuel
            SetDatabaseRecord("Fuel_Cost", GetGeneralPreferenceValue("Fuel_Charge"), $RSConversionNumber, $DatabaseFields[22]);
            
            // don't reimburse for cross country fuel
            SetDatabaseRecord("Cross_Country_Fuel", 0, $RSConversionNumber, $DatabaseFields[23]);
            SetDatabaseRecord("Cross_Country_Fuel_Credit", 0, $RSConversionNumber, $DatabaseFields[24]);
            SetDatabaseRecord("Local_Fuel_Cost",
                            Val($AircraftLocalFuel) *
                            GetGeneralPreferenceValue("Fuel_Charge"),
                            $RSConversionNumber,
                            $DatabaseFields[25]);
        }
        else
        {
            //club aircraft
            if ($row[0] > 0)
            {
                // rental aircraft, don't charge for fuel
                SetDatabaseRecord("Fuel_Cost", 0, $RSConversionNumber, $DatabaseFields[22]);
                
                // reimburse for cross country fuel
                SetDatabaseRecord("Cross_Country_Fuel", Val($AircraftXCntryFuel), $RSConversionNumber, $DatabaseFields[23]);
                
                // if we are not reimbursing the actual cost, compute the credit
                if (GetGeneralPreferenceValue("Fuel_Reimbursement") != 0)
                {
                    // not reimbursing the full cost, compute the credit
                    SetDatabaseRecord("Cross_Country_Fuel_Credit",
                                    RoundToDecimalPlaces(-1 * $AircraftXCntryFuel *
                                                    GetGeneralPreferenceValue("Fuel_Reimbursement")),
                                    $RSConversionNumber, $DatabaseFields[24]);
                }
                else
                {
                    // reimbursing the full cost
                    SetDatabaseRecord("Cross_Country_Fuel_Credit",
                                    RoundToDecimalPlaces(-1 * GetNumber($AircraftXCntryFuelCost)),
                                    $RSConversionNumber, $DatabaseFields[24]);
                }
                SetDatabaseRecord("Local_Fuel_Cost", 0, $RSConversionNumber, $DatabaseFields[25]);
            }
            else
            {
                // non-rental aircraft, charge for fuel
                SetDatabaseRecord("Fuel_Cost", GetGeneralPreferenceValue("Fuel_Charge"), $RSConversionNumber, $DatabaseFields[22]);
                
                // don't reimburse for cross country fuel
                SetDatabaseRecord("Cross_Country_Fuel", 0, $RSConversionNumber, $DatabaseFields[23]);
                SetDatabaseRecord("Cross_Country_Fuel_Credit", 0, $RSConversionNumber, $DatabaseFields[24]);
                SetDatabaseRecord("Local_Fuel_Cost",
                                Val($AircraftLocalFuel) *
                                GetGeneralPreferenceValue("Fuel_Charge"),
                                $RSConversionNumber,
                                $DatabaseFields[25]);
            }
        }
        
        // save the oil used
        $OldOilUsed = $OldOilUsed;
        SetDatabaseRecord("Oil", Val($AircraftOil), $RSConversionNumber, $DatabaseFields[26]);
        // if this aircraft is a rental aircraft, don't charge for oil
        if (UCase(Trim($TailNumber)) == UCase($SpecialAircraftTailnumber) ||
            sql_count($res) == 0)
        {
            // special tailnumber, charge for oil
            $OilRate = GetGeneralPreferenceValue("Oil_Charge");
        }
        else
        {
            // special tailnumber or non-club aircraft, charge for oil
            if ($row[0] > 0)
            {
                // rental aircraft, don't charge for oil
                $OilRate = 0;
            }
            else
            {
                // non-rental aircraft, charge for oil
                $OilRate = GetGeneralPreferenceValue("Oil_Charge");
            }
        }
        SetDatabaseRecord("Oil_Rate", $OilRate, $RSConversionNumber, $DatabaseFields[27]);
        SetDatabaseRecord("Oil_Cost",
                        Val($AircraftOil) *
                        $OilRate, $RSConversionNumber, $DatabaseFields[28]);
        
        // update the inventory for the oil used
        if (($OldOilUsed - Val($AircraftOil)) != 0)
        {
            AdjustInventoryItem(
                                $row[2],
                                ($OldOilUsed - Val($AircraftOil)));
        }
        
        // update the aircraft costs
        $HobbsElapsed = RoundToDecimalPlaces(Val($EndingHobbs) -
                Val($BeginningHobbs), 1);
        SetDatabaseRecord("Hobbs_Elapsed", $HobbsElapsed, $RSConversionNumber, $DatabaseFields[29]);
        if (UCase(Trim($TailNumber)) == UCase($SpecialAircraftTailnumber) ||
            sql_count($res) == 0)
        {
            $AircraftRate = 0;
            $OwnerRate = 0;
        }
        else
        {
            $AircraftRate = $row[0];
            
            // if this is a privately owned aircraft, set the owner reimbursement rate
            if ($row[1] != $row[0])
            {
                // lease back aircraft, save the rate we reimburse the owner
                $OwnerRate = $row[1];
            }
            else
            {
                // club owned aircraft so we don't owe anyone any reimbursements
                $OwnerRate = 0;
            }
        }
        SetDatabaseRecord("Aircraft_Rate", $AircraftRate, $RSConversionNumber, $DatabaseFields[30]);
        SetDatabaseRecord("Owner_Rate", $OwnerRate, $RSConversionNumber, $DatabaseFields[31]);
        SetDatabaseRecord("Aircraft_Cost",
            RoundToDecimalPlaces($AircraftRate * $HobbsElapsed), $RSConversionNumber, $DatabaseFields[32]);
        SetDatabaseRecord("Owner_Reimbursement",
            RoundToDecimalPlaces($OwnerRate * $HobbsElapsed), $RSConversionNumber, $DatabaseFields[33]);
        
        // update the instructor charges if any dual time was entered
        if (RoundToDecimalPlaces(Val($InstDualTime), 1) > 0 ||
            RoundToDecimalPlaces(Val($InstPAndP), 1) > 0)
        {
            $InstructionRate = GetInstructionRate($InstType);
            SetDatabaseRecord("Instruction_Rate",
                    $InstructionRate, $RSConversionNumber, $DatabaseFields[34]);
            SetDatabaseRecord("Instructor_Charge",
                    $InstructionRate *
                    (RoundToDecimalPlaces(Val($InstDualTime), 1) +
                    RoundToDecimalPlaces(Val($InstPAndP), 1)), $RSConversionNumber, $DatabaseFields[35]);
        }
        else
        {
            // no dual time
            SetDatabaseRecord("Instruction_Rate", 0, $RSConversionNumber, $DatabaseFields[34]);
            SetDatabaseRecord("Instructor_Charge", 0, $RSConversionNumber, $DatabaseFields[35]);
        }
    
        // save the changes
        if (UCase($AddModify) == "MODIFY")
        {
            UpdateDatabaseRecord(
                                "Flight",
                                $DatabaseFields,
                                "(" .
                                    "Keycode='" . $OldMemberKeyCode . "' AND " .
                                    "Date='" . FormatField($OldFlightDate, "DatabaseDate") . "' AND " .
                                    "Aircraft='" . $OldAircraftID . "' AND " .
                                    "model_id=" . LookupModelID($OldAircraftType) . " AND " .
                                    "Instruction_Type='" . $OldInstType . "' AND " .
                                    "ROUND(Begin_Hobbs, 1)=" . RoundToDecimalPlaces($OldBeginHobbs, 1) . " AND " .
                                    "ROUND(End_Hobbs, 1)=" . RoundToDecimalPlaces($OldEndHobbs, 1) .
                                ")");
        }
        else
        {
            AddDatabaseRecord(
                                "Flight",
                                $DatabaseFields);
        }
        
        // update the instructor charges if any dual time was entered
        if (RoundToDecimalPlaces(Val($InstDualTime), 1) > 0 ||
            RoundToDecimalPlaces(Val($InstPAndP), 1) > 0)
        {
            // update the flight instructor record with any changes
            UpdateFlightInstructorCharges(
                                            $OldMemberKeyCode,
                                            $OldInstructorKeyCode,
                                            $OldDualTime,
                                            $OldPPTime,
                                            $OldFlightDate,
                                            $OldBeginHobbs,
                                            $OldEndHobbs,
                                            $OldAircraftID,
                                            $OldAircraftType,
                                            GetUsernameFromName($NameOfUser),
                                            Trim($InstKeycode),
                                            $InstType);
        }
        else
        {
            // delete the old instructor record if there was one
            if (strlen($OldInstructorKeyCode) > 0)
            {
                DeleteFlightInstructorRecord(
                                                $OldInstructorKeyCode,
                                                Trim(GetUsernameFromName($NameOfUser)),
                                                $OldDualTime,
                                                $OldPPTime,
                                                $OldFlightDate,
                                                $OldAircraftID,
                                                LookupModelID($OldAircraftType));
            }
        }     
        
        // update the aircraft information if it is a club aircraft
        if (UCase(Trim($TailNumber)) != UCase($SpecialAircraftTailnumber) &&
            sql_count($res) > 0)
        {
            UpdateAircraftInformation($TailNumber);
        }
        
        // log the change in the journal
        if (UCase($AddModify) != "ADD")
        {
        	$Description = 
                            "Updating flight for " . Trim(GetUsernameFromName($NameOfUser)) .
                            " (" . $NameOfUser . ")" .
                            " on date " . BuildDate($Flightday, $Flightmonth, $Flightyear) .
                            " for aircraft " . $TailNumber;
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
        else
        {
        	$Description = 
                                    "Adding flight for " . Trim(GetUsernameFromName($NameOfUser)) .
                                    " (" . $NameOfUser . ")" .
                                    " on date " . BuildDate($Flightday, $Flightmonth, $Flightyear) .
                                    " for aircraft " . $TailNumber;
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
    }
    
    //********************************************************************
    // UpdateAircraftInformation($TailNumber)
    //
    // Purpose: Update the aircraft information from the information
    //          entered by the user on the screen
    //
    // Inputs:
    //   TailNumber - aircraft tailnumber we are updating a flight for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function UpdateAircraftInformation($TailNumber)
    {        
        global $SimulatorAircraftType, $PCATDAircraftType;
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $CheckedOutString, $CheckInProgressString;
        global $CheckOutProgressString, $OnLineString, $OffLineString;
     
        // hobbs fields
        global $BeginningHobbs;
        global $EndingHobbs;
       
        // tach fields
        global $BeginningTach;
        global $EndingTach;
        
        global $OldAircraftID;
        global $AddModify;
    
        // get the information from the database about the selected aircraft
        $sql = "SELECT " .
                    "hourly_cost, " .       // 0
                    "rental_fee, " .        // 1
                    "Current_Hobbs, " .     // 2
                    "Annual_Due, " .        // 3
                    "tach1, " .             // 4
                    "100_Hr_Tach, " .       // 5
                    "model_id " .           // 6
        		"FROM AircraftScheduling_aircraft " .
        		"WHERE n_number='$TailNumber'";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);

            // if we are adding a flight or the aircraft tailnumber didn't change,
            // see if we need to update the hobbs and tach value. the idea is to 
            // try to catch new or modified flights that may have increased the
            // hobbs or tach. we don't want to change the values if the aircraft
            // tailnumber changes since this may apply the old aircraft's tach
            // and hobbs to the new.
            if (UCase($AddModify) == "ADD" || $TailNumber == $OldAircraftID)
            {
                // update the hobbs if the hobbs entered is greater
                // than the ones in the database
                if (Val($EndingHobbs) > Val($row[2]))
                {
                    $DatabaseFields = array();
                    SetDatabaseRecord(
                                        "Current_Hobbs",
                                        $EndingHobbs,
                                        $RSConversionNumber,
                                        $DatabaseFields[0]);
                                            
                    // update the record in the database
                    UpdateDatabaseRecord(
                                        "AircraftScheduling_aircraft",
                                        $DatabaseFields,
                                        "n_number='" . $TailNumber . "'");
                }
                
                // update the tach if the tach entered is greater
                // than the one in the database
                if (Val($EndingTach) > Val($row[4]))
                {
                    $DatabaseFields = array();
                    SetDatabaseRecord(
                                        "tach1",
                                        $EndingTach,
                                        $RSConversionNumber,
                                        $DatabaseFields[0]);

                    // if the aircraft is a rental aircraft, adjust the hours 
                    // until 100 hr value
                    if ($row[0] > 0)
                    {
                        // rentail aircraft
                        SetDatabaseRecord(
                                            "Hrs_till_100_Hr",
                                            $row[5] - $EndingTach,
                                            $RSConversionNumber,
                                            $DatabaseFields[1]);
                    }
                        
                    // update the record in the database
                    UpdateDatabaseRecord(
                                        "AircraftScheduling_aircraft",
                                        $DatabaseFields,
                                        "n_number='" . $TailNumber . "'");
                }
                    
                // if this aircraft is not a simulator or a PCATD
                if (LookupAircraftType($row[6]) != $SimulatorAircraftType && 
                    LookupAircraftType($row[6]) != $PCATDAircraftType)
                {                   
                    // if the aircraft is a rental aircraft
                    if ($row[0] > 0)
                    {
                        // is the 100 hour due?
                        if ($row[5] - $EndingTach <= 0)
                        {
                            $DatabaseFields = array();
                            SetDatabaseRecord(
                                            "status",
                                            LookupAircraftStatus($OffLineString),
                                            $RSConversionNumber,
                                            $DatabaseFields[0]);
                        
                            // update the record in the database
                            UpdateDatabaseRecord(
                                                "AircraftScheduling_aircraft",
                                                $DatabaseFields,
                                                "n_number='" . $TailNumber . "'");
                
                            // log the grounding in the journal
                        	$Description = "Grounding aircraft $TailNumber because 100 hr is due. " .
                        	                "100 hr tach: " . $row[5] . " current tach: " . $EndingTach;
                        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
                        }
                        
                        // see if the annual has expired, if so, ground the aircraft
                        if (DateValue("Now") > DateValue($row[3]))
                        {
                            $DatabaseFields = array();
                            SetDatabaseRecord(
                                                "status",
                                                LookupAircraftStatus($OffLineString),
                                                $RSConversionNumber,
                                                $DatabaseFields[0]);
            
                            // update the record in the database
                            UpdateDatabaseRecord(
                                                "AircraftScheduling_aircraft",
                                                $DatabaseFields,
                                                "n_number='" . $TailNumber . "'");
                        
                            // log the grounding in the journal
                        	$Description = "Grounding aircraft $TailNumber because annual is due.";
                        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
                        }
                    }                    
                }
            }
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("UpdateAircraftInformation", $sql);
        }
    }

    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
    # if we dont know the right date then make it up 
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
    
    // are we authorized to perform this function?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelSuper))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }

    // build the filter parameters
    $FilterParameter = "&FilterName=$FilterName" .
                       "&FilterAircraft=$FilterAircraft" . 
                       "&FromDay=$FromDay" .
                       "&FromMonth=$FromMonth" .
                       "&FromYear=$FromYear" .
                       "&ToDay=$ToDay" .
                       "&ToMonth=$ToMonth" .
                       "&ToYear=$ToYear" .
                       "&order_by=$order_by";

    // this script will call itself whenever the submit or Cancel button is pressed
    // we will check here for the update and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $AddModifyFlights == "Submit")
    {
        // acquire mutex to prevent concurrent flight modifications
        if (!sql_mutex_lock('AircraftScheduling_flight'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // flight is being modified or added — wrap in transaction for atomicity
        sql_begin();

        // save the flight information in the database
        UpdateFlightInformation($TailNumber);

        sql_commit();
        sql_mutex_unlock('AircraftScheduling_flight');

        // updates to the flight are complete, take them back to the last screen
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                header("Location: $goback" . CleanGoBackParameters($GoBackParameters));
            else
                // goback parameters not set, use the default
        	    header("Location: " . $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$FilterParameter" . 
        	                "$makemodel");
        }
        else
        {
            // goback is not set, use the default
        	Header("Location: index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
        	            "$FilterParameter" . 
                        "$makemodel");
        }
                    
        // finished with this part of the script
        exit;
    }
    else if(count($_POST) > 0 && $FlightCancel == "Cancel") 
    {
        // user canceled the flight changes, take them back to the last screen
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                header("Location: $goback" . CleanGoBackParameters($GoBackParameters));
            else
                // goback parameters not set, use the default
        	    header("Location: " . $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$FilterParameter" . 
        	                "$makemodel");
        }
        else
        {
            // goback is not set, use the default
        	Header("Location: index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
        	            "$FilterParameter" . 
                        "$makemodel");
        }
		exit();
    }
    else if(count($_POST) > 0 && $FlightDelete == "Delete")
    {
        // acquire mutex to prevent concurrent flight modifications
        if (!sql_mutex_lock('AircraftScheduling_flight'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // user is deleting the flight — wrap in transaction for atomicity
        sql_begin();
        DeleteFlight();
        sql_commit();
        sql_mutex_unlock('AircraftScheduling_flight');
        
        // take them back to the last screen
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                header("Location: $goback" . CleanGoBackParameters($GoBackParameters));
            else
                // goback parameters not set, use the default
        	    header("Location: " . $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$FilterParameter" . 
        	                "$makemodel");
        }
        else
        {
            // goback is not set, use the default
        	Header("Location: index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
        	            "$FilterParameter" . 
                        "$makemodel");
        }
		exit();
    }

    // neither Submit, Delete or Cancel were selected, display the main screen
            
    // save the old values for updating the flight record
    if (UCase($AddModify) == "MODIFY")
    {
        $OldMemberKeyCode = GetUsernameFromName($NameOfUser);
        $OldInstructorKeyCode = $InstKeycode;
        $OldDualTime = RoundToDecimalPlaces($InstDualTime, 1);
        $OldPPTime = RoundToDecimalPlaces($InstPAndP, 1);
        $OldFlightDate = BuildDate($Flightday, $Flightmonth, $Flightyear);
        $OldBeginHobbs = $BeginningHobbs;
        $OldEndHobbs = $EndingHobbs;
        $OldAircraftID = $TailNumber;
        $OldAircraftType = $AircraftModel;
        $OldOilUsed = $AircraftOil;
        $OldInstType = $InstType;
    }
    else
    {
        $OldMemberKeyCode = " ";
        $OldInstructorKeyCode = " ";
        $OldDualTime = 0;
        $OldPPTime = 0;
        $OldFlightDate = "Now";
        $OldBeginHobbs = 0;
        $OldEndHobbs = 0;
        $OldAircraftID = " ";
        $OldAircraftType = 0;
        $OldOilUsed = 0;
        $OldInstType = "";
    }
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "UpdateControlValues");
    
    // start the form
	echo "<FORM NAME='main' ACTION='AddModifyFlights.php' METHOD='POST'>";

    // start the table to display the aircraft flight information
    echo "<center>";
    echo "<table border=0>";

    // tell the user what we are doing
    echo "<TR><TD colspan=2>";
    if (UCase($AddModify) == "ADD")
        echo "<CENTER><H2>Add New Flight</H2>";
    else
        echo "<CENTER><H2>Modify Flight Information</H2>";
    echo "</CENTER></TD></TR>";
    
    // member information
    echo "<tr>";
    echo "<td colspan=2>Flight for user:";
    BuildMemberSelector($NameOfUser, false, "", 20, false);
    echo "</TD></TR>";
    
    // aircraft and aircraft type
    echo "<tr>";
    echo "<td>Aircraft:";
    BuildAircraftSelectorList();
    echo "</TD>";
    echo "<td>Type:";
    BuildAircraftTypeSelector();
    echo "</TD>";
    echo "</TR>";
    
    // date information
    echo "<tr>";
    echo "<td colspan=2>Date:";
    genDateSelector("Flight", "main", $Flightday, $Flightmonth, $Flightyear);
    echo "</TD>";
    echo "</TR>";
    
    // clearing authority information
    echo "<tr>";
    echo "<td colspan=3>Flight Clearing Authority:";
    BuildClearingAuthoritySelector(
                        GetNameFromUsername($ClearingAuthority), 
                        true, 
                        "ClearingAuthority",
                        20,
                        true,
                        false,
                        "",
                        true);
    echo "</td>";
    echo "</tr>";

    // finished with the table
    echo "</table>";
    
    // flight information
    echo "<table border=0>";
    echo "<tr>";
    echo "<TD>";
    DisplayFlightFields($TailNumber);
    echo "</TD></TR>";
    echo "</table>";
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='AddModifyFlights' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<TD><input name='FlightCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelFlight"] . "')\"></TD>";
    if (UCase($AddModify) == "MODIFY")
        echo "<TD><input name='FlightDelete' type=submit value='Delete' onClick=\"return confirm('" .  
                    $lang["DeleteFlight"] . "')\"></TD>";
    echo "</CENTER></TD></TR>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";
    
    // save the input variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var AddModify = '$AddModify';";
    echo "var DefaultPPTime = '$DefaultPPTime';";
    echo "var CurrencyPrefix = '$CurrencyPrefix';";
    echo "</SCRIPT>";
    
    // save the original values for submiting or deleting the form
    echo "<INPUT NAME='OldMemberKeyCode' TYPE='HIDDEN' VALUE='$OldMemberKeyCode'>\n";
    echo "<INPUT NAME='OldInstructorKeyCode' TYPE='HIDDEN' VALUE='$OldInstructorKeyCode'>\n";
    echo "<INPUT NAME='OldDualTime' TYPE='HIDDEN' VALUE='$OldDualTime'>\n";
    echo "<INPUT NAME='OldPPTime' TYPE='HIDDEN' VALUE='$OldPPTime'>\n";
    echo "<INPUT NAME='OldFlightDate' TYPE='HIDDEN' VALUE='$OldFlightDate'>\n";
    echo "<INPUT NAME='OldBeginHobbs' TYPE='HIDDEN' VALUE='$OldBeginHobbs'>\n";
    echo "<INPUT NAME='OldEndHobbs' TYPE='HIDDEN' VALUE='$OldEndHobbs'>\n";
    echo "<INPUT NAME='OldAircraftID' TYPE='HIDDEN' VALUE='$OldAircraftID'>\n";
    echo "<INPUT NAME='OldAircraftType' TYPE='HIDDEN' VALUE='$OldAircraftType'>\n";
    echo "<INPUT NAME='OldOilUsed' TYPE='HIDDEN' VALUE='$OldOilUsed'>\n";
    echo "<INPUT NAME='OldInstType' TYPE='HIDDEN' VALUE='$OldInstType'>\n";
    echo "<INPUT NAME='AddModify' TYPE='HIDDEN' VALUE='$AddModify'>\n";
       
    // save the filter information
    echo "<INPUT NAME='FilterName' TYPE='HIDDEN' VALUE='$FilterName'>\n";
    echo "<INPUT NAME='FilterAircraft' TYPE='HIDDEN' VALUE='$FilterAircraft'>\n";
    echo "<INPUT NAME='FromDay' TYPE='HIDDEN' VALUE='$FromDay'>\n";
    echo "<INPUT NAME='FromMonth' TYPE='HIDDEN' VALUE='$FromMonth'>\n";
    echo "<INPUT NAME='FromYear' TYPE='HIDDEN' VALUE='$FromYear'>\n";
    echo "<INPUT NAME='ToDay' TYPE='HIDDEN' VALUE='$ToDay'>\n";
    echo "<INPUT NAME='ToMonth' TYPE='HIDDEN' VALUE='$ToMonth'>\n";
    echo "<INPUT NAME='ToYear' TYPE='HIDDEN' VALUE='$ToYear'>\n";
    echo "<INPUT NAME='order_by' TYPE='HIDDEN' VALUE='$order_by'>\n";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// UpdateFlightControl(UpdatedControl, TableName)
//
// Purpose: Update the input entry given by UpdatedControl and update
//          the totals line.
//
// Inputs:
//   UpdatedControl - the name of the control that contains the new
//                    entry.
//   TableName - name of the table that contains the input
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function UpdateFlightControl(UpdatedControl, TableName)
{
    var BeginningRow = 1;   // row number for beginning field
    var ElapsedRow = 3;     // row number for elapsed field
    var ValuesCol = 1;      // column number for value fields
    
    // perform the processing for the control
    switch (UpdatedControl)
    {    
    // hobbs fields
    case "BeginningHobbs":        
    case "EndingHobbs":        
        var x = document.getElementById(TableName).rows;
        var y = null;
        var Ending;
        var Elapsed;

        // get the hobbs beginning value
        Beginning = parseFloat(document.getElementById('BeginningHobbs').value);
        if (isNaN(Beginning)) Beginning = 0.0;
        document.getElementById('BeginningHobbs').value = format(Beginning, 1);
        
        // get the the hobbs ending value
        Ending = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(Ending)) Ending = 0.0;
        document.getElementById(UpdatedControl).value = format(Ending, 1);

        // compute the hobbs elapsed
        Elapsed = Ending - Beginning;
        
        // update the elapsed line
        y = x[ElapsedRow].cells;
        y[ValuesCol].innerHTML = format(Elapsed, 1);
        
        // update the day flight time if it hasn't been set
        FlightTimeDay = document.getElementById('FlightTimeDay').value;
        FlightTimeNight = document.getElementById('FlightTimeNight').value;
        if ((isNaN(parseFloat(FlightTimeDay)) || parseFloat(FlightTimeDay) == 0.0) &&
             (isNaN(parseFloat(FlightTimeNight)) || parseFloat(FlightTimeNight) == 0.0))
        {
            document.getElementById('FlightTimeDay').value = format(Elapsed, 1);
            UpdateFlightControl('FlightTimeDay', 'FlightTimeTable');
        }
        break;
        
    // tach fields
    case "BeginningTach":        
    case "EndingTach":        
        var x = document.getElementById(TableName).rows;
        var y = null;
        var Ending;
        var Elapsed;

        // get the tach beginning value
        Beginning = parseFloat(document.getElementById('BeginningTach').value);
        if (isNaN(Beginning)) Beginning = 0.0;
        document.getElementById('BeginningTach').value = format(Beginning, 1);
        
        // get the the tach ending value
        Ending = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(Ending)) Ending = 0.0;
        document.getElementById(UpdatedControl).value = format(Ending, 1);

        // compute the elapsed tach time
        Elapsed = Ending - Beginning;
        
        // update the elapsed line
        y = x[ElapsedRow].cells;
        y[ValuesCol].innerHTML = format(Elapsed, 1);
        break;
    
    // flight instruction fields
    case "InstDualTime":
        DualTime = document.getElementById(UpdatedControl).value;
        if (isNaN(DualTime)) DualTime = 0.0;
        document.getElementById(UpdatedControl).value = 
                format(DualTime, 1);
        break;
    case "InstPAndP":
        PPTime = parseFloat(document.getElementById('InstPAndP').value);
        if (isNaN(PPTime)) PPTime = 0.0;
        document.getElementById(UpdatedControl).value = format(PPTime, 1);
        break;
    case "InstKeycode":
        break;
    case "InstType":
        break;
    
    // flight time fields
    case "FlightTimeDay":
        FlightTimeDay = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(FlightTimeDay)) FlightTimeDay = 0.0;
        document.getElementById(UpdatedControl).value = format(FlightTimeDay, 1);
        break;
    case "FlightTimeNight":
        FlightTimeNight = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(FlightTimeNight)) FlightTimeNight = 0.0;
        document.getElementById(UpdatedControl).value = format(FlightTimeNight, 1);
        break;
    case "FlightTimeHolds":
        FlightTimeHolds = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(FlightTimeHolds)) FlightTimeHolds = 0.0;
        document.getElementById(UpdatedControl).value = format(FlightTimeHolds, 0);
        break;
    case "FlightTimeNav":
        FlightTimeNav = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(FlightTimeNav)) FlightTimeNav = 0.0;
        document.getElementById(UpdatedControl).value = format(FlightTimeNav, 0);
        break;
    case "FlightTimeInstApp":
        FlightTimeInstApp = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(FlightTimeInstApp)) FlightTimeInstApp = 0.0;
        document.getElementById(UpdatedControl).value = format(FlightTimeInstApp, 0);
        break;
    case "FlightTimeDayLndg":
        FlightTimeDayLndg = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(FlightTimeDayLndg)) FlightTimeDayLndg = 0.0;
        document.getElementById(UpdatedControl).value = format(FlightTimeDayLndg, 0);
        break;
    case "FlightTimeNightLndg":
        FlightTimeNightLndg = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(FlightTimeNightLndg)) FlightTimeNightLndg = 0.0;
        document.getElementById(UpdatedControl).value = format(FlightTimeNightLndg, 0);
        break;
    
    // aircraft fields
    case "AircraftLocalFuel":
        AircraftLocalFuel = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(AircraftLocalFuel)) AircraftLocalFuel = 0.0;
        document.getElementById(UpdatedControl).value = format(AircraftLocalFuel, 1);
        break;
    case "AircraftXCntryFuel":
        AircraftXCntryFuel = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(AircraftXCntryFuel)) AircraftXCntryFuel = 0.0;
        document.getElementById(UpdatedControl).value = format(AircraftXCntryFuel, 1);
        break;
    case "AircraftXCntryFuelCost":
        var XCntryFuelCost = document.getElementById(UpdatedControl).value;
        
        // remove any dollar signs
        if (XCntryFuelCost.substring(0, 1) == CurrencyPrefix)
            XCntryFuelCost = XCntryFuelCost.substring(1);
        
        // format the control value
        if (XCntryFuelCost.length > 0)
        {
            XCntryFuelCost = parseFloat(XCntryFuelCost);
            if (isNaN(XCntryFuelCost)) XCntryFuelCost = 0.0;
            document.getElementById(UpdatedControl).value = dollarize(XCntryFuelCost);
        }
        break;
    case "AircraftOil":
        AircraftOil = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(AircraftOil)) AircraftOil = 0.0;
        document.getElementById(UpdatedControl).value = 
                format(AircraftOil, 0)
        break;
    }
}

//********************************************************************
// UpdateControlValues()
//
// Purpose: Update all the control on the form .
//
// Inputs:
//   none
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function UpdateControlValues()
{
     // hobbs fields
    UpdateFlightControl("BeginningHobbs", "HobbsTable");        
    UpdateFlightControl("EndingHobbs", "HobbsTable");        
        
    // tach fields
    UpdateFlightControl("BeginningTach", "TachTable");        
    UpdateFlightControl("EndingTach", "TachTable");        
     
    // flight instruction fields
    UpdateFlightControl("InstDualTime", "FlightInstTable");
    UpdateFlightControl("InstPAndP", "FlightInstTable");
    UpdateFlightControl("InstKeycode", "FlightInstTable");
    UpdateFlightControl("InstType", "FlightInstTable");
    
    // flight time fields
    UpdateFlightControl("FlightTimeNight", "FlightTimeTable");
    UpdateFlightControl("FlightTimeDay", "FlightTimeTable");
    UpdateFlightControl("FlightTimeHolds", "FlightTimeTable");
    UpdateFlightControl("FlightTimeNav", "FlightTimeTable");
    UpdateFlightControl("FlightTimeInstApp", "FlightTimeTable");
    UpdateFlightControl("FlightTimeDayLndg", "FlightTimeTable");
    UpdateFlightControl("FlightTimeNightLndg", "FlightTimeTable");
    
    // aircraft fields
    UpdateFlightControl("AircraftLocalFuel", "AircraftTable");
    UpdateFlightControl("AircraftXCntryFuel", "AircraftTable");
    UpdateFlightControl("AircraftXCntryFuelCost", "AircraftTable");
    UpdateFlightControl("AircraftOil", "AircraftTable");
}

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
    var BeginningRow = 1;   // row number for beginning field
    var ElapsedRow = 3;     // row number for elapsed field
    var ValuesCol = 1;      // column number for value fields

    // verify that the ending hobbs is greater than beginning hobbs
    var x = document.getElementById('HobbsTable').rows;
    var EndingHobbs;

    // get the hobbs beginning value
    var y = x[BeginningRow].cells;
    
    // get the the hobbs ending value
    EndingHobbs = parseFloat(document.getElementById('EndingHobbs').value);
    BeginningHobbs = parseFloat(document.getElementById('BeginningHobbs').value);
    if (BeginningHobbs > EndingHobbs)
	{
        alert("Ending hobbs less than beginning hobbs.\n" +
                "Please correct the ending hobbs value.");
        document.getElementById('EndingHobbs').focus();
        document.getElementById('EndingHobbs').select();
        
        // error found, don't let them continue
        return false;
    }
 
    // compute the elasped time (round to one decimal point)
    ElaspedHobbs = parseFloat(format((EndingHobbs - BeginningHobbs), 1));

    // verify that the elapsed hobbs is not too large
    if (ElaspedHobbs > 10)
	{
        // tell the user they tried to log a large amount of hobbs time
        Response = confirm("You entered a large amount of hobbs time.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('EndingHobbs').focus();
            document.getElementById('EndingHobbs').select();
            
            // error found, don't let them continue
            return false;
        }
    }
   
    // verify that the ending tach is greater than beginning tach
    var x = document.getElementById('TachTable').rows;
    var Endingtach;

    // get the tach beginning value
    var y = x[BeginningRow].cells;
    
    // get the the tach ending value
    EndingTach = parseFloat(document.getElementById('EndingTach').value);
    BeginningTach = parseFloat(document.getElementById('BeginningTach').value);
    if (BeginningTach > EndingTach)
	{
        alert("Ending tach less than beginning tach.\n" +
                "Please correct the ending tach value.");
        document.getElementById('EndingTach').focus();
        document.getElementById('EndingTach').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // verify that the elapsed tach is not too large
    if ((EndingTach - BeginningTach) > 10)
	{
        // tell the user they tried to log a large amount of tach time
        Response = confirm("You entered a large amount of tach time.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('EndingTach').focus();
            document.getElementById('EndingTach').select();
            
            // error found, don't let them continue
            return false;
        }
    }

    // no errors found, return
	return true;
}

//********************************************************************
// SelectAircraft()
//
// Purpose: Update the aircraft information when a new aircraft is 
//          selected.
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
function SelectAircraft()
{
    // get the selected index of the aircraft control
    ArrayPointer = document.getElementById('TailNumber').selectedIndex;

    // get the values from the array
    BeginHobbs = HobbsArray[ArrayPointer];
    BeginTach = TachArray[ArrayPointer];
    Model = ModelArray[ArrayPointer];
    
    // if we are adding a new flight, set the aircraft parameters
    // based on the aircraft selection
    if (AddModify.toUpperCase() == "ADD")
    {
        // update the aircraft fields    
        // hobbs beginning and ending values
        document.getElementById('BeginningHobbs').value = format(BeginHobbs, 1);
        document.getElementById('EndingHobbs').value = format(BeginHobbs, 1);
    
        // tach beginning and ending values
        document.getElementById('BeginningTach').value = format(BeginTach, 1);
        document.getElementById('EndingTach').value = format(BeginTach, 1);
        
        // format all the controls
        UpdateControlValues();
    }
    
    // select the aircraft make from the list
    ModelList = document.getElementById('AircraftModel');
    ListLength = ModelList.length;
    for (var i = 0; i < ListLength; i++)
    {
        if (ModelList.options[i].text == Model)
        {
            ModelList.selectedIndex = i;
            break;
        }
    }
}

//********************************************************************
// UpdateFlightInstructorInfo()
//
// Purpose: Update the instructor information when a new instructor is 
//          selected.
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
function UpdateFlightInstructorInfo()
{
    // get the selected instructor
    InstructorKeycode = document.getElementById('InstKeycode').value;
    
    // if we have not selected an instructor, clear the instructor time values
    if (InstructorKeycode == "None")
    {
        // no instructor selected, clear the time values
        document.getElementById('InstDualTime').value = "0.0";
        document.getElementById('InstPAndP').value = "0.0";
    }
    else
    {
        // instructor selected, set the dual time to the elasped hobbs value
        // if something hasn't already been entered
        DualTime = document.getElementById('InstDualTime').value;
        if (DualTime.length == 0 || parseFloat(DualTime) == 0.0 || isNaN(parseFloat(DualTime)))
        {
            // nothing entered, use the elasped hobbs time
            EndingHobbs = parseFloat(document.getElementById('EndingHobbs').value);
            BeginningHobbs = parseFloat(document.getElementById('BeginningHobbs').value);
            ChargedTime = parseFloat(format((EndingHobbs - BeginningHobbs), 1));
            if (isNaN(ChargedTime)) ChargedTime = 0.0;
            document.getElementById('InstDualTime').value = format(ChargedTime, 1);
            document.getElementById('InstPAndP').value = DefaultPPTime;
        }
        else
        { 
            // something already entered, use it  
            ChargedTime = parseFloat(format(document.getElementById('InstDualTime').value, 1));
        }    
        
        UpdateFlightControl('InstDualTime', 'FlightInstTable');
    }
}

//-->
</script>
