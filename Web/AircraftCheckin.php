<?php
//-----------------------------------------------------------------------------
// 
// AircraftCheckin.php
// 
// PURPOSE: Displays the aircraft checkin screen.
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
//      GoBackParameters - parameters to return to previous page
//      AircraftCheckin - set to Checkin to checkin an aircraft or
//      AircraftCancel - set to Cancel to cancel the checkin.
//      debug_flag - set to non-zero to enable debug output information
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
//          FlightTimeNightLndg" - number of night landings that the user entered
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
    $TailNumber = '';
    $CurrentlySelectedAircraftType = '';
    $CurrentlySelectedTailnumber = '';

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
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    if(isset($rdata["AircraftCheckin"])) $AircraftCheckin = $rdata["AircraftCheckin"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
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
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // CheckAircraftStatus()
    //
    // Purpose:  Check the aircraft status and if the aircraft is not checked out,
    //           bail out of the script. This is to prevent a double check
    //           in in case an error occurred or the user had two screens up. 
    //           Normally, the user won't get a check in option unless the 
    //           aircraft is checked out, but since we are dealing with
    //           browsers that can have multiple copies, we need to make sure.
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
    function CheckAircraftStatus()
    {  
        global $TailNumber;
        global $CheckedOutString, $CheckInProgressString;
        global $CheckOutProgressString, $OnLineString, $OffLineString;
        global $day, $month, $year;
        global $resource;
        global $resource_id;
        global $InstructorResource;
        global $makemodel;
        global $goback;
        global $GoBackParameters;
         
        // if the tailnumber is not set, get the default tailnumber
        if (strlen($TailNumber) > 0)
        {
            // tailnumber is set, use it
            $tmpTailNumber = $TailNumber;
        }
        else
        {
            // tailnumber is not set, get the default
            $tmpTailNumber = GetDefaultCheckinTailNumber(getUserName());
        }
        
        // get the aircraft status from the database
        $AircraftStatusID = sql_query1(
                                    "SELECT status " . 
                            		"FROM AircraftScheduling_aircraft " .
                            		"WHERE n_number='$tmpTailNumber'");    
        $AircraftStatusName = LookupAircraftStatusString($AircraftStatusID);
        
        // if the aircraft is not checked out, exit the script
        if ($AircraftStatusName != $CheckedOutString &&
            $AircraftStatusName != $OffLineString)
        {
            // aircraft is not checked out, don't let them check it in again
    
            // log the problem in the journal
        	$Description = "Attempt to check in Aircraft " . $tmpTailNumber . " twice blocked.";
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
            	    
            // return to the last screen
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
                            "$makemodel");
            }
    		exit();
        }
    } 	

    //********************************************************************
    // GetDefaultTailNumber($UserName)
    //
    // Purpose:  Look for the aircraft that the user has checked out
    //           and return that.
    //
    // Inputs:
    //   UserName - username to look up aircraft for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   The aircraft that the user has checked out
    //*********************************************************************
    function GetDefaultCheckinTailNumber($UserName)
    {    	
        $AircraftTailNumber = sql_query1(
                    "SELECT n_number  
                     FROM AircraftScheduling_aircraft 
                     WHERE CurrentKeycode = '$UserName'
                     ");
        
        // return the results
        return $AircraftTailNumber;
    }
    
    //********************************************************************
    // BuildCheckinTableEntry(
    //                   $ControlTitle, 
    //                   $CheckinControlName,
    //                   $DefaultValue, 
    //                   $AllowChanges,
    //                   $TableName)
    //
    // Purpose: Build a table row to display a control.
    //
    // Inputs:
    //   ControlTitle - the title for the control
    //   CheckinControlName - the name of the item
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
    function BuildCheckinTableEntry(
                                $ControlTitle, 
                                $CheckinControlName,
                                $DefaultValue, 
                                $AllowChanges,
                                $TableName)
     {	
        global $Column1Width, $Column2Width;

    	// set the size of the input boxes
    	$ControlNameBoxSize = 4;

        // make sure we don't have any blanks in the control names
        $CheckinControlName = Replace($CheckinControlName, " ", "_");
        $TableName = Replace($TableName, " ", "_");

        // each entry is a table row
        echo "<TR>"; 
        
        // build the title column
        echo "<TD ALIGN=LEFT WIDTH=$Column1Width>$ControlTitle</TD>";        
    
        // build the column and let them change it if requested
        if ($AllowChanges)
        {
            // build the input column and let them change it.
            echo "<TD ALIGN=RIGHT WIDTH=$Column2Width>" . 
                    "<INPUT " .
                        "TYPE=TEXT " .
                        "NAME='$CheckinControlName' " . 
                        "ID='$CheckinControlName' " .
                        "ALIGN=RIGHT " . 
                        "SIZE=$ControlNameBoxSize " . 
                        "VALUE='" . $DefaultValue . "' " . 
                        "Onchange='UpdateCheckinControl(\"$CheckinControlName\", \"$TableName\")'>" . 
                    "</TD>";
        }
        else
        {
            echo "<TD ALIGN=RIGHT WIDTH=$Column2Width>" . $DefaultValue . "</TD>";
        }
        
        // end the table row
        echo "</TR>";    	    
    }

    //********************************************************************
    // DisplayHobbsFields()
    //
    // Purpose:  Display the aircraft checkin hobbs fields for the user to
    //           enter the checkin information.
    //
    // Inputs:
    //   BeginningHobbs - beginning hobbs for display
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayHobbsFields($BeginningHobbs)
    {    	
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
        
        // save the beginning hobbs value
        echo "<input name='BeginningHobbs' type='hidden' value=$BeginningHobbs>";
         
        // set the table name  	
        $TableName = "HobbsTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Hobbs</td>";
        echo "</tr>";
       
        // beginning hobbs
        BuildCheckinTableEntry(
                                "Beginning", 
                                "", 
                                sprintf("%1.1f", $BeginningHobbs), 
                                $DontAllowChanges, 
                                $TableName);
       
        // beginning hobbs
        BuildCheckinTableEntry(
                                "Ending", 
                                "EndingHobbs", 
                                sprintf("%1.1f", $BeginningHobbs), 
                                $AllowChanges, 
                                $TableName);
       
        // elapsed hobbs
        BuildCheckinTableEntry(
                                "Elapsed", 
                                "", 
                                "0.0", 
                                $DontAllowChanges, 
                                $TableName);
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayTachFields()
    //
    // Purpose:  Display the aircraft checkin tach fields for the user to
    //           enter the checkin information.
    //
    // Inputs:
    //   BeginningTach - beginning tach for display
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayTachFields($BeginningTach)
    {    	        
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
        
        // save the beginning tach value
        echo "<input name='BeginningTach' type='hidden' value=$BeginningTach>";
         
        // set the table name  	
        $TableName = "TachTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Tach</td>";
        echo "</tr>";
       
        // beginning tach
        BuildCheckinTableEntry(
                                "Beginning", 
                                "", 
                                sprintf("%1.1f", $BeginningTach), 
                                $DontAllowChanges, 
                                $TableName);
       
        // beginning tach
        BuildCheckinTableEntry(
                                "Ending", 
                                "EndingTach", 
                                sprintf("%1.1f", $BeginningTach), 
                                $AllowChanges, 
                                $TableName);
       
        // elapsed tach
        BuildCheckinTableEntry(
                                "Elapsed", 
                                "", 
                                "0.0", 
                                $DontAllowChanges, 
                                $TableName);
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayFlightInstFields()
    //
    // Purpose:  Display the aircraft checkin flight instructor fields for 
    //           the user to enter the checkin information.
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
		global $InstKeycode;
        
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
        BuildCheckinTableEntry(
                                "Dual Time", 
                                "InstDualTime", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // P & P
        BuildCheckinTableEntry(
                                "P & P", 
                                "InstPAndP", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // flight instruction type
        echo "<TR><TD ALIGN=LEFT COLSPAN=2>";        
    
        // build the drop down box for selecting flight instruction type
        echo "<SELECT NAME='InstType'>";
        echo "<OPTION>$NoInstruction";
        echo "<OPTION>$FreeInstruction";
        echo "<OPTION>$GroundInstruction";
        echo "<OPTION SELECTED>$PrivateInstruction";
        echo "<OPTION>$InstrumentInstruction";
        echo "<OPTION>$CommercialInstruction";
        echo "<OPTION>$CFIInstruction";
        echo "<OPTION>$CFIIInstruction";
        echo "</SELECT>";
        
        // end the table row
        echo "</TR>";    	    
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayFlightTimeFields()
    //
    // Purpose:  Display the aircraft checkin flight time fields for 
    //           the user to enter the checkin information.
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
        BuildCheckinTableEntry(
                                "Day", 
                                "FlightTimeDay", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // night
        BuildCheckinTableEntry(
                                "Night", 
                                "FlightTimeNight", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // holding procedures
        BuildCheckinTableEntry(
                                "Holding Procedures", 
                                "FlightTimeHolds", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // navigation intercepts
        BuildCheckinTableEntry(
                                "Navigation Intercepts", 
                                "FlightTimeNav", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // instrument approaches
        BuildCheckinTableEntry(
                                "Instrument Approaches", 
                                "FlightTimeInstApp", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // day landings
        BuildCheckinTableEntry(
                                "Day Landings", 
                                "FlightTimeDayLndg", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // night landings
        BuildCheckinTableEntry(
                                "Night Landings", 
                                "FlightTimeNightLndg", 
                                "", 
                                $AllowChanges, 
                                $TableName);

        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayAircraftFields()
    //
    // Purpose:  Display the aircraft checkin aircraft fields for 
    //           the user to enter the checkin information.
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
        BuildCheckinTableEntry(
                                "Local Fuel (gals)", 
                                "AircraftLocalFuel", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // cross country fuel
        BuildCheckinTableEntry(
                                "Cross Country Fuel (gals)", 
                                "AircraftXCntryFuel", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // cross country fuel cost
        BuildCheckinTableEntry(
                                "Cross Country Fuel Cost", 
                                "AircraftXCntryFuelCost", 
                                "", 
                                $AllowChanges, 
                                $TableName);
       
        // oil
        BuildCheckinTableEntry(
                                "Oil (qts)", 
                                "AircraftOil", 
                                "", 
                                $AllowChanges, 
                                $TableName);

        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayCheckinFields($TailNumber)
    //
    // Purpose:  Display the aircraft checkin fields for the user to
    //           enter the checkin information.
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
    function DisplayCheckinFields($TailNumber)
    { 
        global $Column1Width, $Column2Width;

        // set the column sizes
        $Column1Width = "80%";
        $Column2Width = "20%";

        // get the information from the database about the selected aircraft
        $sql = "SELECT " .  
                        "Current_Hobbs, " .          // 0
                        "tach1 " .                   // 1
        		"FROM AircraftScheduling_aircraft " . 
        		"WHERE n_number='$TailNumber'";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);
            
            // start the table to display the aircraft checkin fields
            echo "<table border=0>";
        
            // display the title
            echo "<TR><TD COLSPAN=4>";
            echo "<CENTER><b>Check-in Information</b>";
            echo "</CENTER></TD></TR>";
             
            // fill in the left column information
            echo "<TR><TD>";
            DisplayHobbsFields($row[0]);
            echo "<BR>";
            DisplayTachFields($row[1]);
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
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("DisplayCheckinFields", $sql);
        }
    }

    //********************************************************************
    // PrintAircraftCheckinForm($TailNumber)
    //
    // Purpose: Print the sign-in sheet for the aircraft
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
    function PrintAircraftCheckinForm($TailNumber)
    {
        global $SimulatorAircraftType, $PCATDAircraftType;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $WBFieldString;
	    global $AircraftScheduling_company;
    
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

        // get the information from the database about the selected aircraft
        $sql = "SELECT
        				Cleared_By,
        				hourly_cost 
        		FROM AircraftScheduling_aircraft  
        		WHERE n_number='$TailNumber'";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);

            $LeftMargin = 5;
            $MaxLineLength = 60;
            $DescriptionFieldLeftMargin = 20;   // left column indent
            $DescriptionFieldLength = 30;       // left column width
            $ValueFieldLength = 10;             // right column width
            $ChargeDescriptionFieldLength = 35; // charges left column width
            $ChargeValueFieldLength = 10;       // charges right column width
             
            // printer setup
            PrinterSetup(11);
            
            // remove any dollar signs from the front of AircraftXCntryFuelCost
            $AircraftXCntryFuelCost = GetNumber($AircraftXCntryFuelCost);
            
            // skip some space at the top of the form
            PrintNonBreakingString(" " . "<BR>");
            PrintNonBreakingString(" " . "<BR>");
            
            // header
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField(
                            "******************** " .
                            $AircraftScheduling_company .
                            "********************",
                            $CenterJustify, $MaxLineLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("FLIGHT IN " . Trim($TailNumber), $CenterJustify, $MaxLineLength) . "<BR>");
            if (Len(Trim($row[0])) > 0)
            {
                // clearing authority entered
                PrintNonBreakingString(Space($LeftMargin) .
                              JustifyField("FLIGHT CLEARED BY " . Trim($row[0]), $CenterJustify, $MaxLineLength) . "<BR>");
            }
            PrintNonBreakingString(Space($LeftMargin) .
                         JustifyField("CHECK-IN DATE " . strftime("%d %b %y", time() - TimeZoneAdjustment()), $CenterJustify, $MaxLineLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                         JustifyField("CHECK-IN TIME (Local) " . strftime("%H:%M",  time() - TimeZoneAdjustment()), $CenterJustify, $MaxLineLength) . "<BR>");
            
            // skip space
            PrintNonBreakingString(" " . "<BR>");
              
            // beginning hobbs time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("BEGINNING HOBBS", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($BeginningHobbs, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
              
            // ending hobbs time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("ENDING HOBBS", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($EndingHobbs, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
              
            // Elapsed hobbs time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("ELAPSED HOBBS TIME", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField(($EndingHobbs - $BeginningHobbs), "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
              
            // beginning tach time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("BEGINNING TACH", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($BeginningTach, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
              
            // ending tach time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("ENDING TACH", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($EndingTach, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
              
            // Elapsed tach time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("ELAPSED TACH TIME", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField(($EndingTach - $BeginningTach), "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // day flight time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("DAY FLIGHT TIME", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($FlightTimeDay, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // day landings
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("DAY LANDINGS", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($FlightTimeDayLndg, "INTEGER"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // night flight time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("NIGHT FLIGHT TIME", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($FlightTimeNight, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // night landings
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("NIGHT LANDINGS", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($FlightTimeNightLndg, "INTEGER"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // Holding Procedures
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("HOLDING PROCEDURES", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($FlightTimeHolds, "INTEGER"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // navigation intercepts
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("NAVIGATION INTERCEPTS", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($FlightTimeNav, "INTEGER"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // instrument approaches
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("INSTRUMENT APPROACHES", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($FlightTimeInstApp, "INTEGER"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // dual flight time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("DUAL TIME", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($InstDualTime, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // P & P time
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("P&P TIME", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($InstPAndP, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // local fuel
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("LOCAL FUEL (GALS)", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($AircraftLocalFuel, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // cross country fuel
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("CROSS COUNTRY FUEL (GALS)", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($AircraftXCntryFuel, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("CROSS COUNTRY FUEL COST", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($AircraftXCntryFuelCost, "FLOAT"), $RightJustify, $ValueFieldLength) . "<BR>");
        
            // oil
            PrintNonBreakingString(Space($LeftMargin) . Space($DescriptionFieldLeftMargin) .
                        JustifyField("OIL (QTS)", $LeftJustify, $DescriptionFieldLength) .
                        JustifyField(FormatField($AircraftOil, "INTEGER"), $RightJustify, $ValueFieldLength) . "<BR>");
            
            // skip space
            PrintNonBreakingString(" " . "<BR>");
            
            // print out the account charge information
            PrintNonBreakingString(Space($LeftMargin) .
                        "THE FOLLOWING CHARGES WILL BE MADE TO YOUR ACCOUNT: " . "<BR>");
            $InstructorRate = RoundToDecimalPlaces(GetInstructionRate(Trim($InstType)));
            $FlightTimeCharge = RoundToDecimalPlaces(Val(($EndingHobbs - $BeginningHobbs)) * $row[1]);
            $InstructorTimeCharge = RoundToDecimalPlaces(Val($InstDualTime) * $InstructorRate);
            $PPTimeCharge = RoundToDecimalPlaces(Val($InstPAndP) * $InstructorRate);
            // if this aircraft is a rental aircraft, don't charge for fuel or oil and reimburse for
            // cross country fuel
            if ($row[1] > 0)
            {
                // rental aircraft, don't charge for fuel or oil, reimburse for cross country fuel
                $FuelCharge = 0;
                
                // if we are not reimbursing the actual cost, compute the credit
                if (GetGeneralPreferenceValue("Fuel_Reimbursement") != 0)
                {
                    // not reimbursing the full cost, compute the credit
                    $CrossCountryFuelCharge = -1 * RoundToDecimalPlaces(Val($AircraftXCntryFuel) *
                                                    GetGeneralPreferenceValue("Fuel_Reimbursement"));
                }
                else
                {
                    // reimbursing the full cost
                    $CrossCountryFuelCharge = -1 * RoundToDecimalPlaces(Val($AircraftXCntryFuelCost));
                }
                $OilCharge = 0;
            }
            else
            {
                // non-rental aircraft, charge for fuel and oil, don't reimburse for cross country fuel
                $FuelCharge = RoundToDecimalPlaces(
                                        Val($AircraftLocalFuel) * GetGeneralPreferenceValue("Fuel_Charge"));
                $CrossCountryFuelCharge = 0;
                $OilCharge = RoundToDecimalPlaces(
                                        Val($AircraftOil) * GetGeneralPreferenceValue("Oil_Charge"));
            }
            $TotalCharges =
                            $FlightTimeCharge +
                            $InstructorTimeCharge +
                            $PPTimeCharge +
                            $FuelCharge +
                            $OilCharge +
                            $CrossCountryFuelCharge;
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("FLIGHT TIME (" .
                        FormatField(($EndingHobbs - $BeginningHobbs), "Float") .
                        " hours) ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField(FormatField(Str($FlightTimeCharge), "Currency"), $RightJustify, $ChargeValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("DUAL TIME (" .
                        FormatField($InstDualTime, "Float") .
                        " hours) ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField(FormatField(Str($InstructorTimeCharge), "Currency"), $RightJustify, $ChargeValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("P&P TIME (" .
                        FormatField($InstPAndP, "Float") .
                        " hours) ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField(FormatField(Str($PPTimeCharge), "Currency"), $RightJustify, $ChargeValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("LOCAL FUEL (" .
                        FormatField($AircraftLocalFuel, "FLOAT") .
                        " gals) ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField(FormatField(Str($FuelCharge), "Currency"), $RightJustify, $ChargeValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("LOCAL OIL (" .
                        FormatField($AircraftOil, "INTEGER") .
                        " qts) ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField(FormatField(Str($OilCharge), "Currency"), $RightJustify, $ChargeValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("X-COUNTRY FUEL (" .
                        FormatField($AircraftXCntryFuel, "FLOAT") .
                        " gals) ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField(FormatField(Str($CrossCountryFuelCharge), "Currency"), $RightJustify, $ChargeValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField(" ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField("_________________________", $RightJustify, $ChargeValueFieldLength) . "<BR>");
            PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("TOTAL CHARGES ", $LeftJustify, $ChargeDescriptionFieldLength) .
                        JustifyField(FormatField(Str($TotalCharges), "Currency"), $RightJustify, $ChargeValueFieldLength) . "<BR>");
            
            // skip space
            PrintNonBreakingString(" " . "<BR>");
            PrintNonBreakingString(" " . "<BR>");
            
            // pilot signature
            PrintNonBreakingString(Space($LeftMargin) .
                        "PILOT'S SIGNATURE (" .
                        UCase(Trim(getUserName())) .
                        "):_________________________" . "<BR>");
            
            // instructor's signature if required
            if (Val($InstDualTime) > 0 || Val($InstPAndP) > 0)
            {
                // an instructor was on this flight, add that to the sheet
                PrintNonBreakingString(Space($LeftMargin) . " " . "<BR>");
                PrintNonBreakingString(Space($LeftMargin) .
                        "INSTRUCTOR'S SIGNATURE:_________________________" . "<BR>");
            }
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintAircraftCheckinForm", $sql);
        }
    }

    //********************************************************************
    // DisplayAircraftStatus($TailNumber)
    //
    // Purpose: Display the status information for the selected aircraft.
    //
    // Inputs:
    //   TailNumber - tail number of the selected aircraft
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayAircraftStatus($TailNumber)
    {
        global $CurrentlySelectedAircraftType; $CurrentlySelectedTailnumber;
        global $CurrencyPrefix;

        // get the information from the database about the selected aircraft
        $sql = "SELECT 
                        ROUND(a.hourly_cost, 2), 
        				model, 
        				status,
        				ROUND(Current_Hobbs, 1),
        				ROUND(tach1, 1),
                        ROUND(Hrs_Till_100_Hr, 1),                
                        Annual_Due  
        		FROM AircraftScheduling_aircraft a, 
        			AircraftScheduling_make b, 
        			AircraftScheduling_model c, 
        			AircraftScheduling_equipment_codes d 
        		WHERE a.n_number='$TailNumber' AND 
        			a.make_id=b.make_id AND 
        			a.model_id=c.model_id AND 
        			a.code_id=d.code_id";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);
            
            // determine aircraft status
            $AircraftStatusName = LookupAircraftStatusString($row[2]);
            	
            // display the aircraft status information
            echo "<TABLE BORDER=0>";        
    	    echo "<TR><TD COLSPAN=2><b>Aircraft Information</b></TD></TR>";
            echo "<TR><TD>Type</TD><TD><B>$row[1]</B></TD></TR>";        
            echo "<TR><TD>Status</TD><TD><B>$AircraftStatusName</B></TD></TR>";
            echo "<TR><TD>Hours until 100 hr</TD><TD><B>$row[5]</B></TD></TR>";
            echo "<TR><TD>Current Tach</TD><TD><B>$row[4]</B></TD></TR>";
            echo "<TR><TD>Annual Due</TD><TD><B>" . date("d-M-y", strtotime($row[6])) . "</B></TD></TR>";
            echo "<TR><TD>Rate/Hr</TD><TD><B>$CurrencyPrefix$row[0]</B></TD></TR>";
            echo "<TR><TD>Current Hobbs:</TD><TD><B>$row[3]</B></TD></TR>";
            echo "</TABLE>";
            
            // save the currently selected aircraft information
            $CurrentlySelectedAircraftType = $row[1];
            $CurrentlySelectedTailnumber = $TailNumber;
       
            // generate the code to load the aircraft javascipt information variables
            echo "<SCRIPT LANGUAGE=\"JavaScript\">";
            echo "var SimulatorAircraftType = 'SIM';";
            echo "var PCATDAircraftType = 'PCATD';";            
            echo "var AircraftType = '$row[1]';";
            echo "</SCRIPT>";
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("DisplayAircraftStatus", $sql);
        }
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

        // get the information from the database about the selected aircraft
        $sql = "SELECT
                    model_id,
                    hourly_cost,
                    rental_fee,
                    Oil_Type,
                    Cleared_By 
        		FROM AircraftScheduling_aircraft  
        		WHERE n_number='$TailNumber'";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);
            
            // make sure that the non-zero length text fields have blanks in them
            if (strlen($InstKeycode) == 0) $InstKeycode = " ";
            
            // add the flight record
            $DatabaseFields = array();
            $StudentKeyCode = getUserName();
            SetDatabaseRecord("KeyCode", $StudentKeyCode, $RSConversionString, $DatabaseFields[0]);
            SetDatabaseRecord("Date", FormatField("now", "DatabaseDate"), $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Aircraft", $TailNumber, $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("model_id", trim($row[0]), $RSConversionNumber, $DatabaseFields[3]);
            SetDatabaseRecord("Begin_Hobbs", RoundToDecimalPlaces($BeginningHobbs, 1), $RSConversionNumber, $DatabaseFields[4]);
            SetDatabaseRecord("End_Hobbs", RoundToDecimalPlaces($EndingHobbs, 1), $RSConversionNumber, $DatabaseFields[5]);
            SetDatabaseRecord("Begin_Tach", RoundToDecimalPlaces($BeginningTach, 1), $RSConversionNumber, $DatabaseFields[6]);
            SetDatabaseRecord("End_Tach", RoundToDecimalPlaces($EndingTach, 1), $RSConversionNumber, $DatabaseFields[7]);
            SetDatabaseRecord("Day_Time", RoundToDecimalPlaces($FlightTimeDay, 1), $RSConversionNumber, $DatabaseFields[8]);
            SetDatabaseRecord("Night_Time", RoundToDecimalPlaces($FlightTimeNight, 1), $RSConversionNumber, $DatabaseFields[9]);
            SetDatabaseRecord("Instruction_Type", $InstType, $RSConversionString, $DatabaseFields[10]);
            SetDatabaseRecord("Student_Keycode", $StudentKeyCode, $RSConversionString, $DatabaseFields[11]);
            SetDatabaseRecord("Dual_Time", RoundToDecimalPlaces(Val($InstDualTime), 1), $RSConversionNumber, $DatabaseFields[12]);
            SetDatabaseRecord("Dual_PP_Time", RoundToDecimalPlaces(Val($InstPAndP), 1), $RSConversionNumber, $DatabaseFields[13]);
            SetDatabaseRecord("Instructor_Keycode", $InstKeycode, $RSConversionString, $DatabaseFields[14]);
            SetDatabaseRecord("Day_Landings", Val($FlightTimeDayLndg), $RSConversionNumber, $DatabaseFields[15]);
            SetDatabaseRecord("Night_Landings", Val($FlightTimeNightLndg), $RSConversionNumber, $DatabaseFields[16]);
            SetDatabaseRecord("Navigation_Intercepts", Val($FlightTimeNav), $RSConversionNumber, $DatabaseFields[17]);
            SetDatabaseRecord("Holding_Procedures", Val($FlightTimeHolds), $RSConversionNumber, $DatabaseFields[18]);
            SetDatabaseRecord("Instrument_Approach", Val($FlightTimeInstApp), $RSConversionNumber, $DatabaseFields[19]);
            
            //save the fuel information
            SetDatabaseRecord("Local_Fuel", Val($AircraftLocalFuel), $RSConversionNumber, $DatabaseFields[20]);
            // if this aircraft is a rental aircraft, don't charge for fuel
            if ($row[1] > 0)
            {
                // rental aircraft, don't charge for fuel
				$FuelCost = 0;
                SetDatabaseRecord("Fuel_Cost", $FuelCost, $RSConversionNumber, $DatabaseFields[21]);
            }
            else
            {
                // non-rental aircraft, charge for fuel
                $FuelCost = GetGeneralPreferenceValue("Fuel_Charge");
                SetDatabaseRecord("Fuel_Cost", $FuelCost, $RSConversionNumber, $DatabaseFields[21]);
            }
            SetDatabaseRecord("Local_Fuel_Cost",
                            Val($AircraftLocalFuel) * $FuelCost,
                            $RSConversionNumber, $DatabaseFields[22]);
            SetDatabaseRecord("Cross_Country_Fuel", Val($AircraftXCntryFuel), $RSConversionNumber, $DatabaseFields[23]);
                
            // if we are not reimbursing the actual cost, compute the credit
            if (GetGeneralPreferenceValue("Fuel_Reimbursement") != 0)
            {
                // not reimbursing the full cost, compute the credit
                SetDatabaseRecord("Cross_Country_Fuel_Credit",
                                RoundToDecimalPlaces(-1 * Val($AircraftXCntryFuel) *
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
            
            // save the oil used
            SetDatabaseRecord("Oil", Val($AircraftOil), $RSConversionNumber, $DatabaseFields[25]);
            // if this aircraft is a rental aircraft, don't charge for oil
            if ($row[1] > 0)
            {
                // rental aircraft, don't charge for oil
                $OilRate = 0;
            }
            else
            {
                // non-rental aircraft, charge for oil
                $OilRate = GetGeneralPreferenceValue("Oil_Charge");
            }
            SetDatabaseRecord("Oil_Rate", $OilRate, $RSConversionNumber, $DatabaseFields[26]);
            SetDatabaseRecord("Oil_Cost",
                            Val($AircraftOil) * $OilRate,
                            $RSConversionNumber, $DatabaseFields[27]);
            
            // update the inventory for the oil used
            if (Val($AircraftOil) > 0)
            {
                AdjustInventoryItem($row[3], -Val($AircraftOil));
            }
            
            // update the aircraft costs
            $HobbsElapsed = RoundToDecimalPlaces($EndingHobbs - $BeginningHobbs, 1);
            SetDatabaseRecord("Hobbs_Elapsed", $HobbsElapsed, $RSConversionNumber, $DatabaseFields[28]);
            SetDatabaseRecord("Aircraft_Rate", $row[1], $RSConversionNumber, $DatabaseFields[29]);
            SetDatabaseRecord("Aircraft_Cost", RoundToDecimalPlaces($row[1] * $HobbsElapsed), 
                                $RSConversionNumber, $DatabaseFields[30]);
                
            // if this is a privately owned aircraft, set the owner reimbursement rate
            if ($row[2] != $row[1])
            {
                // lease back aircraft, save the rate we reimburse the owner
                $ClubRate = $row[2];
            }
            else
            {
                // club owned aircraft so we don't owe anyone any reimbursements
                $ClubRate = 0;
            }
            SetDatabaseRecord("Owner_Rate", $ClubRate, $RSConversionNumber, $DatabaseFields[31]);
            SetDatabaseRecord("Owner_Reimbursement",
                RoundToDecimalPlaces($ClubRate * $HobbsElapsed), $RSConversionNumber, $DatabaseFields[32]);
            
            // update the instructor charges if any dual time was entered
            if (RoundToDecimalPlaces(Val($InstDualTime), 1) > 0 || 
                    RoundToDecimalPlaces(Val($InstPAndP), 1) > 0)
            {
                $InstructionRate = GetInstructionRate($InstType);
                SetDatabaseRecord("Instruction_Rate", $InstructionRate, $RSConversionNumber, $DatabaseFields[33]);
                SetDatabaseRecord("Instructor_Charge",
                        $InstructionRate *
                        (RoundToDecimalPlaces(Val($InstDualTime), 1) +
                        RoundToDecimalPlaces(Val($InstPAndP), 1)),
                        $RSConversionNumber, $DatabaseFields[34]);
            }
            else
            {
                SetDatabaseRecord("Instruction_Rate", 0, $RSConversionNumber, $DatabaseFields[33]);
                SetDatabaseRecord("Instructor_Charge", 0, $RSConversionNumber, $DatabaseFields[34]);
            }
                        
            // save the instruction type for updating the instructor record
            $StudentInstructionType = $InstType;
            
            // save the clearing authority if there was one
            if (strlen($row[4]))
                $ClearingAuthority = $row[4];
            Else
                $ClearingAuthority = "";
            SetDatabaseRecord("Cleared_By", $ClearingAuthority, $RSConversionString, $DatabaseFields[35]);
            
            // save the fields to the database
            AddDatabaseRecord("Flight", $DatabaseFields);
            
            // if this was a dual lesson, add a flight record for the instructor
            if (Val($InstDualTime) > 0 || Val($InstPAndP) > 0)
            {
                // store the fields
                SetDatabaseRecord("KeyCode", UCase(trim($InstKeycode)), $RSConversionString, $DatabaseFields[0]);
                SetDatabaseRecord("Date", FormatField("now", "DatabaseDate"), $RSConversionString, $DatabaseFields[1]);
                SetDatabaseRecord("Aircraft", trim($TailNumber), $RSConversionString, $DatabaseFields[2]);
                SetDatabaseRecord("model_id", trim($row[0]), $RSConversionNumber, $DatabaseFields[3]);
                SetDatabaseRecord("Begin_Hobbs", RoundToDecimalPlaces($BeginningHobbs, 1), $RSConversionNumber, $DatabaseFields[4]);
                SetDatabaseRecord("End_Hobbs", RoundToDecimalPlaces($EndingHobbs, 1), $RSConversionNumber, $DatabaseFields[5]);
                SetDatabaseRecord("Begin_Tach", RoundToDecimalPlaces($BeginningTach, 1), $RSConversionNumber, $DatabaseFields[6]);
                SetDatabaseRecord("End_Tach", RoundToDecimalPlaces($EndingTach, 1), $RSConversionNumber, $DatabaseFields[7]);
                SetDatabaseRecord("Day_Time", RoundToDecimalPlaces($FlightTimeDay, 1), $RSConversionNumber, $DatabaseFields[8]);
                SetDatabaseRecord("Night_Time", RoundToDecimalPlaces($FlightTimeNight, 1), $RSConversionNumber, $DatabaseFields[9]);
                SetDatabaseRecord("Instruction_Type", $InstructorInstruction, $RSConversionString, $DatabaseFields[10]);
                SetDatabaseRecord("Student_Keycode", $StudentKeyCode, $RSConversionString, $DatabaseFields[11]);
                SetDatabaseRecord("Dual_Time", RoundToDecimalPlaces(Val($InstDualTime), 1), $RSConversionNumber, $DatabaseFields[12]);
                SetDatabaseRecord("Dual_PP_Time", RoundToDecimalPlaces(Val($InstPAndP), 1), $RSConversionNumber, $DatabaseFields[13]);
                SetDatabaseRecord("Instructor_Keycode", " ", $RSConversionString, $DatabaseFields[14]);
                SetDatabaseRecord("Day_Landings", Val($FlightTimeDayLndg), $RSConversionNumber, $DatabaseFields[15]);
                SetDatabaseRecord("Night_Landings", Val($FlightTimeNightLndg), $RSConversionNumber, $DatabaseFields[16]);
                SetDatabaseRecord("Navigation_Intercepts", Val($FlightTimeNav), $RSConversionNumber, $DatabaseFields[17]);
                SetDatabaseRecord("Holding_Procedures", Val($FlightTimeHolds), $RSConversionNumber, $DatabaseFields[18]);
                SetDatabaseRecord("Instrument_Approach", Val($FlightTimeInstApp), $RSConversionNumber, $DatabaseFields[19]);
                
                //save the fuel information
                SetDatabaseRecord("Local_Fuel", 0, $RSConversionNumber, $DatabaseFields[20]);
                SetDatabaseRecord("Fuel_Cost", 0, $RSConversionNumber, $DatabaseFields[21]);
                SetDatabaseRecord("Local_Fuel_Cost", 0, $RSConversionNumber, $DatabaseFields[22]);
                SetDatabaseRecord("Cross_Country_Fuel", 0, $RSConversionNumber, $DatabaseFields[23]);
                SetDatabaseRecord("Cross_Country_Fuel_Credit", 0, $RSConversionNumber, $DatabaseFields[24]);
                
                // save the oil used
                SetDatabaseRecord("Oil", 0, $RSConversionNumber, $DatabaseFields[25]);
                SetDatabaseRecord("Oil_Rate", 0, $RSConversionNumber, $DatabaseFields[26]);
                SetDatabaseRecord("Oil_Cost",
                                Val($AircraftOil) * $OilRate, $RSConversionNumber, $DatabaseFields[27]);
                
                // update the aircraft costs (no cost for the instructor)
                SetDatabaseRecord("Hobbs_Elapsed", $HobbsElapsed, $RSConversionNumber, $DatabaseFields[28]);
                SetDatabaseRecord("Aircraft_Rate", 0, $RSConversionNumber, $DatabaseFields[29]);
                SetDatabaseRecord("Aircraft_Cost", 0, $RSConversionNumber, $DatabaseFields[30]);
                SetDatabaseRecord("Owner_Rate", 0, $RSConversionNumber, $DatabaseFields[31]);
                SetDatabaseRecord("Owner_Reimbursement", 0, $RSConversionNumber, $DatabaseFields[32]);
                
                // update the instructor charges if any dual time was entered
                $InstructionRate = -1 * GetInstructorCreditRate(trim($InstKeycode), $StudentInstructionType);
                SetDatabaseRecord("Instruction_Rate", $InstructionRate, $RSConversionNumber, $DatabaseFields[33]);
                SetDatabaseRecord("Instructor_Charge",
                        $InstructionRate *
                        (RoundToDecimalPlaces(Val($InstDualTime), 1) +
                        RoundToDecimalPlaces(Val($InstPAndP), 1)), $RSConversionNumber, $DatabaseFields[34]);
                
                SetDatabaseRecord("Cleared_By", $ClearingAuthority, $RSConversionString, $DatabaseFields[35]);
                
                // save the fields to the database
                AddDatabaseRecord("Flight", $DatabaseFields);
            }
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("UpdateFlightInformation", $sql);
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
        global $AllowSquawkControl;
     
        // hobbs fields
        global $BeginningHobbs;
        global $EndingHobbs;
       
        // tach fields
        global $BeginningTach;
        global $EndingTach;

        // not grounded unless something fails
        $AircraftStatus = $OnLineString;

        // get the information from the database about the selected aircraft
        $sql = "SELECT " .
                    "model_id, " .
                    "hourly_cost, " .
                    "Hrs_till_100_Hr, " .
                    "Annual_Due, " .
                    "100_Hr_Tach " .
        		"FROM AircraftScheduling_aircraft " .
        		"WHERE n_number='$TailNumber'";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);

            // if this aircraft is not a simulator or a PCATD
            if (LookupAircraftType($row[0]) != $SimulatorAircraftType && 
                LookupAircraftType($row[0]) != $PCATDAircraftType)
            {
                // if the aircraft is a rental aircraft, see if the 100 hour is due,
                // if so, ground the aircraft
                if ($row[1] > 0)
                {
                    if (($row[4] - Val($EndingTach)) <= 0)
                    {
                        $AircraftStatus = $OffLineString;
                
                        // log the grounding in the journal
                    	$Description = "Grounding aircraft $TailNumber because 100 hr is due. " .
                    	                "100 hr tach: " . $row[4] . " current tach: " . $EndingTach;
                    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
                    }
                }
                
                // see if the annual is due, if so, ground the aircraft
                if (DateValue("now") > DateValue($row[3]))
                {
                    $AircraftStatus = $OffLineString;
                
                    // log the grounding in the journal
                	$Description = "Grounding aircraft $TailNumber because annual is due.";
                	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
                }
                
                // are we controlling squawks?
                if ($AllowSquawkControl)
                {
                    // see if the aircraft has any grounding squawks. ground it if so
                    if (AircraftShouldBeGrounded($TailNumber))
                    {
                        $AircraftStatus = $OffLineString;
                    
                        // log the grounding in the journal
                    	$Description = "Grounding aircraft $TailNumber because it has grounding squawks.";
                    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
                    }
                }
            }
                
            // update the record in the database
            $DatabaseFields = array();
            SetDatabaseRecord("status", LookupAircraftStatus($AircraftStatus), $RSConversionNumber, $DatabaseFields[0]);
            SetDatabaseRecord("Current_User", "", $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("CurrentKeyCode", "", $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("tach1", RoundToDecimalPlaces($EndingTach, 1), $RSConversionNumber, $DatabaseFields[3]);
            SetDatabaseRecord("Hrs_till_100_Hr", 
                                    RoundToDecimalPlaces($row[4] - Val($EndingTach), 1), $RSConversionNumber, 
                                    $DatabaseFields[4]);
            SetDatabaseRecord("Current_Hobbs", RoundToDecimalPlaces($EndingHobbs, 1), $RSConversionNumber, $DatabaseFields[5]);
            SetDatabaseRecord("Cleared_By", "", $RSConversionString, $DatabaseFields[6]);
            // only update if aircraft is currently In Use (status=2)
            // to prevent check-in of an aircraft that was already checked in
            $affected = UpdateDatabaseRecord(
                                 "AircraftScheduling_aircraft",
                                 $DatabaseFields,
                                 "n_number='" . $TailNumber . "' AND status=2");
            return $affected;
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("UpdateAircraftInformation", $sql);
            return 0;
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal) && $User_Must_Login)
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // if the aircraft is not checked out, bail out
    CheckAircraftStatus();

    // this script will call itself whenever the Checkin or Cancel button is pressed
    // we will check here for the checkin and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $AircraftCheckin == "Checkin")
    {
        // acquire mutex to prevent concurrent check-in/check-out
        if (!sql_mutex_lock('AircraftScheduling_aircraft'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // aircraft is being checked in — wrap in transaction for atomicity
        sql_begin();

        // save the flight information in the database
        UpdateFlightInformation($TailNumber);

        // update the aircraft information in the aircraft database
        // returns affected rows — 0 means aircraft was not in expected state
        $affected = UpdateAircraftInformation($TailNumber);

        if ($affected == 0)
        {
            // aircraft was not in "In Use" state — another user may have
            // already checked it in. Roll back and notify.
            sql_rollback();
            sql_mutex_unlock('AircraftScheduling_aircraft');
            print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
            echo "<H4><CENTER>Aircraft " . htmlspecialchars($TailNumber) .
                 " is no longer checked out.<BR>" .
                 "It may have been checked in by another user.</CENTER></H4>";
            include "trailer.inc";
            exit;
        }

        sql_commit();
        sql_mutex_unlock('AircraftScheduling_aircraft');

        // log the aircraft checkin in the journal
    	$Description = "Aircraft " . $TailNumber . " checked in by " . getName();
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        	    
        // set the URL for the combination lock screen
        if(isset($goback))
        {
            // goback is set, take them back there after the combination lock screen
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                $CombinationLockURL = "CombinationLock.php?" . 
                                "goback=$goback&GoBackParameters=$GoBackParameters";
            else
                // goback parameters not set, use the default
        	    $CombinationLockURL = "CombinationLock.php?goback=" . $goback . 
        	                "&GoBackParameters=" . 
        	                    BuildGoBackParameters(
                	                "?day=$day&month=$month&year=$year" .
                	                "&resource=$resource" .
                	                "&resource_id=$resource_id" .
                	                "&InstructorResource=$InstructorResource" .
                	                "$makemodel");
        }
        else
        {
            // goback is not set, use the default
        	$CombinationLockURL = "CombinationLock.php?goback=index.php" . 
        	                "&GoBackParameters=" . 
        	                    BuildGoBackParameters(
                	                "?day=$day&month=$month&year=$year" .
                	                "&resource=$resource" .
                	                "&resource_id=$resource_id" .
                	                "&InstructorResource=$InstructorResource" .
                	                "$makemodel");
        }
                
        // include the print functions here so that the javascript won't
        // interfer with the header functions
        require_once("PrintFunctions.inc");

        // setup the print functions
        SetupPrintFunctions($CombinationLockURL);
        
        // print the aircraft checkin form copies
        $NumberOfCopies = GetServerPreferenceValue("Number_of_Check_in_Copies");
        for ($i = 0; $i < $NumberOfCopies; $i++)
        {
            PrintAircraftCheckinForm($TailNumber);
                        
            // if this is not the last page, print a page break
            if ($i < ($NumberOfCopies - 1))
            {
                // put a page break between pages
                PrintNewPage();
            }
        }
        
        // finish the print form
        CompletePrintFunctions();
                    
        // finished with this part of the script
        exit;
    }
    else if(count($_POST) > 0 && $AircraftCancel == "Cancel") 
    {
        // user canceled the checkin, take them back to the last screen
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
                        "$makemodel");
        }
		exit();
    }

    // neither Update or Cancel were selected, display the main screen
    
    // get the tailnumber of the aircraft that this user has checked out
    $TailNumber = GetDefaultCheckinTailNumber(getUserName());
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "UpdateControlValues");
    
    // get the current user
    $UserName = getUserName();
    
    // load the currency fields from the database
    LoadDBCurrencyFields($UserName);
    
    // get the pilot type
    $PilotType = LookupCurrencyFieldname("Rating");

    // save the user name and pilot type for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var CurrentUserName = '$UserName';";
    echo "var PilotType = '$PilotType';";
    echo "var TailNumber = '$TailNumber';";
    echo "</SCRIPT>";
    
    // start the form
	echo "<FORM NAME='main' ACTION='AircraftCheckin.php' METHOD='POST'>";

    // start the table to display the aircraft checkin information
    echo "<center>";
    echo "<table border=0>";

    // display the aircraft that we are checking in
    echo "<TR><TD COLSPAN=4>";
    echo "<CENTER><H2>Aircraft To Checkin: $TailNumber</H2>";
    echo "</CENTER></TD></TR>";
     
    // fill the left column of the table with the aircraft information
    echo "<TR><TD>";
    DisplayAircraftStatus($TailNumber);
    echo "</TD>";

    // skip some space between columns
    echo "<TD> </TD>";
    echo "<TD> </TD>";
    
    // fill the right column of the table with the checkin information
    echo "<TD>";
    DisplayCheckinFields($TailNumber);
    echo "</TD></TR>";

    // finished with the table
    echo "</table>";
        
    // if we are controlling squawks
    if ($AllowSquawkControl)
    {
        // display the aircraft squawks
        require_once("SquawkFunctions.inc");
        $CurrentTach = sql_query1(
                                "SELECT ROUND(tach1, 1) " . 
                                "FROM AircraftScheduling_aircraft " . 
                                "WHERE n_number='$TailNumber'");
        $TextRows = 4;
        $TextColumns = 70;
        DisplayAircraftSquawks(
                                $TailNumber, 
                                getUserName(), 
                                $CurrentTach, 
                                $TextRows, 
                                $TextColumns, 
                                1, 
                                0);
    }
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='AircraftCheckin' type=submit value='Checkin' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelCheckin"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";
    
    // save the input variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var DefaultPPTime = '$DefaultPPTime';";
    echo "var CurrencyPrefix = '$CurrencyPrefix';";
    echo "</SCRIPT>";
    
    // save the tailnumber for submitting the form
    echo "<INPUT NAME='TailNumber' TYPE='HIDDEN' VALUE='$TailNumber'>\n";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// UpdateCheckinControl(UpdatedControl, TableName)
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
function UpdateCheckinControl(UpdatedControl, TableName)
{
    var BeginningRow = 1;   // row number for beginning field
    var ElapsedRow = 3;     // row number for elapsed field
    var ValuesCol = 1;      // column number for value fields
    
    // perform the processing for the control
    switch (UpdatedControl)
    {    
    // hobbs fields
    case "EndingHobbs":        
        var x = document.getElementById(TableName).rows;
        var y = null;
        var Ending;
        var Elapsed;

        // get the hobbs beginning value
        y = x[BeginningRow].cells;
        
        // get the the hobbs ending value
        Ending = document.getElementById(UpdatedControl).value;
        document.getElementById(UpdatedControl).value = format(Ending, 1);

        // compute the hobbs elapsed
        Elapsed = Ending - parseFloat(y[ValuesCol].innerHTML);
        
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
            UpdateCheckinControl('FlightTimeDay', 'FlightTimeTable');
        }
        break;
        
    // tach fields
    case "EndingTach":        
        var x = document.getElementById(TableName).rows;
        var y = null;
        var Ending;
        var Elapsed;

        // get the tach beginning value
        y = x[BeginningRow].cells;
        
        // get the the tach ending value
        Ending = document.getElementById(UpdatedControl).value;
        document.getElementById(UpdatedControl).value = format(Ending, 1);

        // compute the elapsed tach time
        Elapsed = Ending - parseFloat(y[ValuesCol].innerHTML);
        
        // update the elapsed line
        y = x[ElapsedRow].cells;
        y[ValuesCol].innerHTML = format(Elapsed, 1);
        break;
    
    // flight instruction fields
    case "InstDualTime":
        DualTime = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(DualTime)) DualTime = 0.0;
        document.getElementById(UpdatedControl).value = format(DualTime, 1);
        
        // add P&P time if the user had dual time and
        // nothing has been entered in the PP time field
        if (DualTime > 0.0)
        {
            PPTime = document.getElementById('InstPAndP').value;
            if (PPTime.length == 0 || parseFloat(PPTime) == 0.0 || isNaN(parseFloat(PPTime)))
            {
                document.getElementById('InstPAndP').value = DefaultPPTime;
                UpdateCheckinControl('InstPAndP', 'FlightInstTable');
            }
        }
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
        // format the day flight time
        FlightTimeDay = document.getElementById(UpdatedControl).value; 
        
        // get the elapsed hobbs time
        var x = document.getElementById('HobbsTable').rows;
        var y = x[ElapsedRow].cells;
        Elapsed = parseFloat(y[ValuesCol].innerHTML);
        if (isNaN(Elapsed)) Elapsed = 0.0;

        // don't allow the day flight time to exceed the total hobbs time
        if (FlightTimeDay > Elapsed)
        {
            FlightTimeDay = Elapsed;
        }
            
        // the night flight time is the remainder
        FlightTimeNight = Elapsed - FlightTimeDay;

        // update the control values
        document.getElementById('FlightTimeDay').value = format(FlightTimeDay, 1);
        document.getElementById('FlightTimeNight').value = format(FlightTimeNight, 1);
        break;
    case "FlightTimeNight":
        // format the night flight time
        FlightTimeNight = document.getElementById(UpdatedControl).value; 
        
        // get the elapsed hobbs time
        var x = document.getElementById('HobbsTable').rows;
        var y = x[ElapsedRow].cells;
        Elapsed = parseFloat(y[ValuesCol].innerHTML);
        if (isNaN(Elapsed)) Elapsed = 0.0;

        // don't allow the night flight time to exceed the total hobbs time
        if (FlightTimeNight > Elapsed)
        {
            FlightTimeNight = Elapsed;
        }
            
        // the day flight time is the remainder
        FlightTimeDay = Elapsed - FlightTimeNight;

        // update the control values
        document.getElementById('FlightTimeDay').value = format(FlightTimeDay, 1);
        document.getElementById('FlightTimeNight').value = format(FlightTimeNight, 1);
        break;
    case "FlightTimeHolds":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 0);
        break;
    case "FlightTimeNav":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 0);
        break;
    case "FlightTimeInstApp":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 0);
        break;
    case "FlightTimeDayLndg":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 0);
        break;
    case "FlightTimeNightLndg":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 0);
        break;
    
    // aircraft fields
    case "AircraftLocalFuel":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 1);
        break;
    case "AircraftXCntryFuel":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 1);
        break;
    case "AircraftXCntryFuelCost":
        var XCntryFuelCost = document.getElementById(UpdatedControl).value;
        
        // remove any dollar signs
        if (XCntryFuelCost.substring(0, 1) == CurrencyPrefix)
            XCntryFuelCost = XCntryFuelCost.substring(1);
        
        // format the control value
        if (XCntryFuelCost.length > 0)
        {
            document.getElementById(UpdatedControl).value = 
                dollarize(XCntryFuelCost);
        }
        break;
    case "AircraftOil":
        document.getElementById(UpdatedControl).value = 
                format(document.getElementById(UpdatedControl).value, 0)
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
    UpdateCheckinControl("EndingHobbs", "HobbsTable");        
        
    // tach fields
    UpdateCheckinControl("EndingTach", "TachTable");        
     
    // flight instruction fields
    UpdateCheckinControl("InstDualTime", "FlightInstTable");
    UpdateCheckinControl("InstPAndP", "FlightInstTable");
    UpdateCheckinControl("InstKeycode", "FlightInstTable");
    UpdateCheckinControl("InstType", "FlightInstTable");
    
    // flight time fields
    UpdateCheckinControl("FlightTimeNight", "FlightTimeTable");
    UpdateCheckinControl("FlightTimeDay", "FlightTimeTable");
    UpdateCheckinControl("FlightTimeHolds", "FlightTimeTable");
    UpdateCheckinControl("FlightTimeNav", "FlightTimeTable");
    UpdateCheckinControl("FlightTimeInstApp", "FlightTimeTable");
    UpdateCheckinControl("FlightTimeDayLndg", "FlightTimeTable");
    UpdateCheckinControl("FlightTimeNightLndg", "FlightTimeTable");
    
    // aircraft fields
    UpdateCheckinControl("AircraftLocalFuel", "AircraftTable");
    UpdateCheckinControl("AircraftXCntryFuel", "AircraftTable");
    UpdateCheckinControl("AircraftXCntryFuelCost", "AircraftTable");
    UpdateCheckinControl("AircraftOil", "AircraftTable");
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
    BeginningHobbs = parseFloat(y[ValuesCol].innerHTML);
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
    BeginningTach = parseFloat(y[ValuesCol].innerHTML);
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
            
    // validate the dual time
    DualTime = parseFloat(document.getElementById('InstDualTime').value);
    if (isNaN(DualTime)) DualTime = 0.0;
    if (DualTime > ElaspedHobbs)
	{
        // tell the user they tried to log too much time
        alert("The dual flight time is greater than the Elapsed hobbs time.\n" +
            "Adjust the ending hobbs value or the dual flight time.");
        
        document.getElementById('InstDualTime').focus();
        document.getElementById('InstDualTime').select();
        
        // error found, don't let them continue
        return false;
    }

    // if the pilot is a student pilot and no dual time was entered, confirm that
    // it is correct
    if (DualTime == 0.0 && PilotType == "Student")
	{
        // ask the user if they should log dual time
        Response = confirm("No dual time logged. Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('InstDualTime').focus();
            document.getElementById('InstDualTime').select();
            
            // error found, don't let them continue
            return false;
        }
    }
            
    // validate the PP time
    PPTime = parseFloat(document.getElementById('InstPAndP').value);
    if (isNaN(PPTime)) PPTime = 0.0;
    if (PPTime > 10)
	{
        // tell the user they tried to log a large amount of time time
        Response = confirm("You entered a large amount of PP time.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('InstPAndP').focus();
            document.getElementById('InstPAndP').select();
            
            // error found, don't let them continue
            return false;
        }
    }
    
    // if dual or pp time was logged, but no instructor keycode was entered
    // flag the error
    InstructorKeycode = document.getElementById('InstKeycode').value;
    if ((DualTime != 0 || PPTime != 0) && InstructorKeycode.toUpperCase() == "NONE")
	{
        // tell the user they tried to log dual time without an instructor keycode
        alert("Dual time was entered without selecting an instructor.\n" + 
            "Please select the instructor.");
        
        document.getElementById('InstKeycode').focus();
        
        // error found, don't let them continue
        return false;
    }

    // validate the day flight time
    FlightTimeDay = parseFloat(document.getElementById('FlightTimeDay').value);
    if (isNaN(FlightTimeDay)) FlightTimeDay = 0.0;
    if (FlightTimeDay > ElaspedHobbs)
	{
        // tell the user they tried to log too much time
        alert("The day flight time is greater than the Elapsed hobbs time.\n" +
            "Adjust the ending hobbs value or the day flight time.");
        document.getElementById('FlightTimeDay').focus();
        document.getElementById('FlightTimeDay').select();
        
        // error found, don't let them continue
        return false;
    }
            
    // validate the night flight time
    FlightTimeNight = parseFloat(document.getElementById('FlightTimeNight').value);
    if (isNaN(FlightTimeNight)) FlightTimeNight = 0.0;
    if (FlightTimeNight > ElaspedHobbs)
	{
        // tell the user they tried to log too much time
        alert("The night flight time is greater than the Elapsed hobbs time.\n" + 
            "Adjust the ending hobbs value or the night flight time.");
        
        document.getElementById('FlightTimeNight').focus();
        document.getElementById('FlightTimeNight').select();
        
        // error found, don't let them continue
        return false;
    }

    // validate the holding procedures
    FlightTimeHolds = parseFloat(document.getElementById('FlightTimeHolds').value);
    if (isNaN(FlightTimeHolds)) FlightTimeHolds = 0.0;
    if (FlightTimeHolds > 10)
	{
        // tell the user they tried to log a large amount of holding procedures
        Response = confirm("You entered a large amount of holding procedures.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('FlightTimeHolds').focus();
            document.getElementById('FlightTimeHolds').select();
            
            // error found, don't let them continue
            return false;
        }
    }
            
    // validate the navigation intercepts
    FlightTimeNav = parseFloat(document.getElementById('FlightTimeNav').value);
    if (isNaN(FlightTimeNav)) FlightTimeNav = 0.0;
    if (FlightTimeNav > 10)
	{
        // tell the user they tried to log a large amount navigation intercepts
        Response = confirm("You entered a large amount navigation intercepts.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('FlightTimeNav').focus();
            document.getElementById('FlightTimeNav').select();
            
            // error found, don't let them continue
            return false;
        }
    }
            
    // validate the instrument approaches
    FlightTimeInstApp = parseFloat(document.getElementById('FlightTimeInstApp').value);
    if (isNaN(FlightTimeInstApp)) FlightTimeInstApp = 0.0;
    if (FlightTimeInstApp > 10)
	{
        // tell the user they tried to log a large amount of instrument approaches
        Response = confirm("You entered a large amount of instrument approaches.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('FlightTimeInstApp').focus();
            document.getElementById('FlightTimeInstApp').select();
            
            // error found, don't let them continue
            return false;
        }
    }
            
    // validate the day landings
    FlightTimeDayLndg = parseFloat(document.getElementById('FlightTimeDayLndg').value);
    if (isNaN(FlightTimeDayLndg)) FlightTimeDayLndg = 0.0;
    if (FlightTimeDayLndg > 10)
	{
        // tell the user they tried to log a large amount of day landings
        Response = confirm("You entered a large amount of day landings.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('FlightTimeDayLndg').focus();
            document.getElementById('FlightTimeDayLndg').select();
            
            // error found, don't let them continue
            return false;
        }
    }
    
    // validate the night landings
    FlightTimeNightLndg = parseFloat(document.getElementById('FlightTimeNightLndg').value);
    if (isNaN(FlightTimeNightLndg)) FlightTimeNightLndg = 0.0;
    if (FlightTimeNightLndg > 10)
	{
        // tell the user they tried to log a large amount of night landings
        Response = confirm("You entered a large amount of night landings.\n" +
            "Are you sure?");
        if (!Response)
	    {
            // user said this was an invalid entry, let them correct it
            document.getElementById('FlightTimeNightLndg').focus();
            document.getElementById('FlightTimeNightLndg').select();
            
            // error found, don't let them continue
            return false;
        }
    }
            
    // validate the local fuel if this is not a simulator or a PCATD
    if (AircraftType != SimulatorAircraftType && AircraftType != PCATDAircraftType)
	{
        AircraftLocalFuel = parseFloat(document.getElementById('AircraftLocalFuel').value);
        if (isNaN(AircraftLocalFuel)) AircraftLocalFuel = 0.0;
        if (AircraftLocalFuel > 40)
	    {
            // tell the user they tried to log a large amount of Local fuel
            Response = confirm("You entered a large amount of local fuel.\n" +
                "Are you sure?");
            if (!Response)
	        {
                // user said this was an invalid entry, let them correct it
                document.getElementById('AircraftLocalFuel').focus();
                document.getElementById('AircraftLocalFuel').select();
                
                // error found, don't let them continue
                return false;
            }
        }
        if (AircraftLocalFuel == 0)
	    {
            // tell the user they failed to log Local fuel
            Response = confirm("You didn't enter any local fuel.\n" +
                "Are you sure?");
            if (!Response)
	        {
                // user said this was an invalid entry, let them correct it
                document.getElementById('AircraftLocalFuel').focus();
                document.getElementById('AircraftLocalFuel').select();
                
                // error found, don't let them continue
                return false;
            }
        }
                
        // validate the cross country fuel
        AircraftXCntryFuel = parseFloat(document.getElementById('AircraftXCntryFuel').value);
        if (isNaN(AircraftXCntryFuel)) AircraftXCntryFuel = 0.0;
        if (AircraftXCntryFuel > 100)
	    {
            // tell the user they tried to log a large amount of cross country fuel
            Response = confirm("You entered a large amount of cross country fuel.\n" +
                "Are you sure?");
            if (!Response)
	        {
                // user said this was an invalid entry, let them correct it
                document.getElementById('AircraftXCntryFuel').focus();
                document.getElementById('AircraftXCntryFuel').select();
                
                // error found, don't let them continue
                return false;
            }
        }
                
        // validate the cross country fuel cost
        AircraftXCntryFuelCost = document.getElementById('AircraftXCntryFuelCost').value;
        if (AircraftXCntryFuelCost.substring(0, 1) == CurrencyPrefix)
            AircraftXCntryFuelCost = AircraftXCntryFuelCost.substring(1);
        AircraftXCntryFuelCost = parseFloat(AircraftXCntryFuelCost);
        if (isNaN(AircraftXCntryFuelCost)) AircraftXCntryFuelCost = 0.0;
        if (AircraftXCntryFuel > 0.0 && AircraftXCntryFuelCost == 0)
	    {
            // tell the user they entered cross-country fuel without a cost
            alert("You entered cross country fuel without entering the cost of the fuel.\n" +
                "Please enter the cost of the cross-country fuel.");
            document.getElementById('AircraftXCntryFuelCost').focus();
            document.getElementById('AircraftXCntryFuelCost').select();
            
            // error found, don't let them continue
            return false;
        }
                
        // validate the oil
        AircraftOil = parseFloat(document.getElementById('AircraftOil').value);
        if (isNaN(AircraftOil)) AircraftOil = 0.0;
        if (AircraftOil > 10)
	    {
            // tell the user they tried to log a large amount of oil
            Response = confirm("You entered a large amount of oil.\n" +
                "Are you sure?");
            if (!Response)
	        {
                // user said this was an invalid entry, let them correct it
                document.getElementById('AircraftOil').focus();
                document.getElementById('AircraftOil').select();
                
                // error found, don't let them continue
                return false;
            }
        }
    }
    else
	{
        // simulator make sure that the fuel and oil entries are zero
        document.getElementById('AircraftLocalFuel').value = "";
        document.getElementById('AircraftXCntryFuel').value = "";
        document.getElementById('AircraftXCntryFuelCost').value = "";
        document.getElementById('AircraftOil').value = "";
    }
    
    // no errors found, return
	return true;
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
    var BeginningRow = 1;   // row number for beginning field
    var ElapsedRow = 3;     // row number for elapsed field
    var ValuesCol = 1;      // column number for value fields

    // verify that the ending hobbs is greater than beginning hobbs

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
            var x = document.getElementById('HobbsTable').rows;
            var EndingHobbs;
        
            // get the hobbs beginning value
            var y = x[BeginningRow].cells;
            
            // get the the hobbs ending value
            EndingHobbs = parseFloat(document.getElementById('EndingHobbs').value);
            BeginningHobbs = parseFloat(y[ValuesCol].innerHTML);
            ChargedTime = parseFloat(format((EndingHobbs - BeginningHobbs), 1));
            if (isNaN(ChargedTime)) ChargedTime = 0.0;
            document.getElementById('InstDualTime').value = format(ChargedTime, 1);
        }
        else
        { 
            // something already entered, use it  
            ChargedTime = parseFloat(format(document.getElementById('InstDualTime').value, 1));
        }    
        
        UpdateCheckinControl('InstDualTime', 'FlightInstTable');
    }
}

//-->
</script>
