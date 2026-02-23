<?php
//-----------------------------------------------------------------------------
// 
// AircraftCheckout.php
// 
// PURPOSE: Displays the aircraft checkout screen.
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
//      ClearingKeyCode - username of clearing authority
//      AircraftCheckout - set to Checkout to checkout an aircraft or
//      AircraftCancel - set to Cancel to cancel the checkout.
//      debug_flag - set to non-zero to enable debug output information
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
    $ClearingKeyCode = '';
    $WBFieldString = '';
    
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
    if(isset($rdata["AircraftCheckout"])) $AircraftCheckout = $rdata["AircraftCheckout"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["ClearingKeyCode"])) $ClearingKeyCode = $rdata["ClearingKeyCode"];
    if(isset($rdata["WBFieldString"])) $WBFieldString = $rdata["WBFieldString"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // GetDefaultTailNumber($TailNumber)
    //
    // Purpose:  Look for the next aircraft that the user is checking out
    //           and return that.
    //
    // Inputs:
    //   TailNumber - tailnumber of the currently selected aircraft (if any)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   The default tailnumber to checkout
    //*********************************************************************
    function GetDefaultTailNumber($TailNumber)
    {
    	global $AircraftScheduleType, $InstructorScheduleType;
    
        // if the tailnumber is already set, use that
        if (strlen($TailNumber) > 0)
        {
            // tailnumber is set, use it
            $GetDefaultTailNumber = $TailNumber;
        }
        else
        {
            // tailnumber is not set, look for the next scheduled aircraft
            // look back 1 hour (1 * 60 * 60 seconds) since someone may be
            // checking out after the start time they scheduled 
            $sql = "SELECT b.resource_name " .  
                    		"FROM AircraftScheduling_entry a," . 
                    		"     AircraftScheduling_resource b " . 
                    		"WHERE a.resource_id=b.resource_id AND " . 
                    		"      b.schedulable_id=$AircraftScheduleType AND " . 
                    		"      a.start_time >=" . (strtotime("now") - (1 * 60 * 60) - TimeZoneAdjustment()) . " AND " .
                    		"      a.name='" . getname() . "' " .
                    		"LIMIT 1";
            $NextTailNumber = sql_query1($sql);
            
            // if we found a valid tailnumber, use it as the default
            if ($NextTailNumber != -1)
            {
                if (strlen($NextTailNumber) > 0)
                {
                    // valid tailnumber found, use it
                    $GetDefaultTailNumber = $NextTailNumber;
                }
                else
                {
                    // no valid tailnumber found, don't specify one
                    $GetDefaultTailNumber = "";
                }
            }
            else
            {
                // no valid tailnumber found, don't specify one
                $GetDefaultTailNumber = "";
            }
        }
        
        // return the results
        return $GetDefaultTailNumber;
    }

    //********************************************************************
    // PrintAircraftCheckoutForm($TailNumber)
    //
    // Purpose: Print the sign-out sheet for the aircraft
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
    function PrintAircraftCheckoutForm($TailNumber)
    {
        // get the information from the database about the selected aircraft
        $sql = "SELECT * FROM AircraftScheduling_aircraft WHERE n_number='$TailNumber'";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);

            // print the first page of the check-out form
            PrintAircraftCheckoutFormPage($TailNumber, $row, "FILE");
            
            // put a page break between pages
            PrintNewPage();
            
            // print the second page of the check-out form
            PrintAircraftCheckoutFormPage($TailNumber, $row, "PILOT");
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintAircraftCheckoutForm", $sql);
        }
    }

    //********************************************************************
    // PrintAircraftCheckoutFormPage($TailNumber, $row, $FormType)
    //
    // Purpose: Print the sign-out sheet (pilot or file copy) for 
    //          the aircraft.
    //
    // Inputs:
    //   TailNumber - tailnumber of the currently selected aircraft
    //   row - rows of data read from the database
    //   FormType - type of form (pilot copy or file copy)
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintAircraftCheckoutFormPage($TailNumber, $row, $FormType)
    {
        global $ClearingKeyCode;
        global $SimulatorAircraftType, $PCATDAircraftType;
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $WBFieldString;
        global $AllowSquawkControl;
         
        include "DatabaseConstants.inc";
               
        $LeftMargin = 0;
        $MaxLineLength = 76;
         
        // printer setup
        PrinterSetup(10);
        
        // header
        PrintNonBreakingString(Space($LeftMargin) . 
                      JustifyField("*****    $FormType COPY    *****", $CenterJustify, $MaxLineLength) . "<BR>");
        
        PrintNonBreakingString(Space($LeftMargin) . 
                      JustifyField("AIRCRAFT", $CenterJustify, 10) . 
                      JustifyField("TYPE", $CenterJustify, 10) . 
                      JustifyField("STATUS", $CenterJustify, 10) . 
                      JustifyField("100 HR", $CenterJustify, 8) . 
                      JustifyField("TACH", $CenterJustify, 8) . 
                      JustifyField("ANNUAL", $CenterJustify, 11) . "<BR>");
       
        // aircraft info
        PrintNonBreakingString(Space($LeftMargin) . 
                      JustifyField($TailNumber, $CenterJustify, 10) . 
                      JustifyField(LookupAircraftType($row[$model_id_offset]), $CenterJustify, 10) . 
                      JustifyField("On Line", $CenterJustify, 10) . 
                      JustifyField(number_format($row[$Hrs_Till_100_Hr_offset], 1), $CenterJustify, 8) . 
                      JustifyField(number_format($row[$tach1_offset], 1), $CenterJustify, 8) . 
                      JustifyField(date("d-M-y", strtotime($row[$Annual_Due_offset])), $CenterJustify, 11) . "<BR>");
        
        // skip space
        PrintNonBreakingString("<BR>");
          
        // date and time
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("TIME: " . strftime("%H:%M", time() - TimeZoneAdjustment()) . " LOCAL", $LeftJustify, 34) . 
                    JustifyField("DATE: " . strftime("%d %b %y", time() - TimeZoneAdjustment()), $LeftJustify, 34) . "<BR>");
    
        // skip space
        PrintNonBreakingString("<BR>");
        
        // clearing authority if required
        if (strlen($ClearingKeyCode) > 0)
	    {
            // a clearing authority was required for clearing, add that to the sheet
            PrintNonBreakingString(Space($LeftMargin) . 
                        " ***  FLIGHT PLAN CLEARED BY " . $ClearingKeyCode . "  ***" . "<BR>");
        }
    
        // aircraft tail number and destination
        if (Len(Trim($row[$Flight_ID_offset])) == 0)
	    {
            // no value in the Flight ID field, use the aircraft tail number for Aircraft ID field
            PrintNonBreakingString(Space($LeftMargin) . 
                        JustifyField("AIRCRAFT: " . $TailNumber, $LeftJustify, 34) . 
                        JustifyField("DESTINATION:", $LeftJustify, 34) . "<BR>");
        }
        else
        {
            // Flight ID field contains a value, use the flight ID for Aircraft ID field
            PrintNonBreakingString(Space($LeftMargin) . 
                        JustifyField("AIRCRAFT: " . $row[$Flight_ID_offset], $LeftJustify, 34) . 
                        JustifyField("DESTINATION:", $LeftJustify, 34) . "<BR>");
        }
        
        // flight rules and total ETE
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("FLIGHT RULES:  IFR / VFR", $LeftJustify, 34) . 
                    JustifyField("TOTAL ETE:", $LeftJustify, 34) . "<BR>");
    
        // type of flight and alternate
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("TYPE OF FLIGHT: " . $row[$ICAO_Flight_Type_offset], $LeftJustify, 34) .  
                    JustifyField("ALTERNATE: ", $LeftJustify, 32) . "<BR>");
    
        // number of aircraft and other information
		PrintNonBreakingString(Space($LeftMargin) . 
					JustifyField("NUMBER OF AIRCRAFT: " . 
					FormatField(
						($row[$ICAO_Number_Aircraft_offset] == 0 ? 1 : $row[$ICAO_Number_Aircraft_offset]), 
						"Integer"), 
					$LeftJustify, 34) . 
					JustifyField("OTHER INFORMATION: ",
					$LeftJustify, 34) . "<BR>");
		
        // aircraft type and endurance
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("Type of Aircraft: " . LookupAircraftType($row[$model_id_offset]), 
						$LeftJustify, 34) . 
                    JustifyField("ENDURANCE:", $LeftJustify, 34) . "<BR>");
		
        // wake turbulence cat and persons on board
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("WAKE TURB CAT: " . 
						(Len(Trim($row[$ICAO_Wake_Turb_offset])) == 0 ? "L" : $row[$ICAO_Wake_Turb_offset]), 
						$LeftJustify, 34) . 
                    JustifyField("PERSONS ON BOARD: ", $LeftJustify, 34) . "<BR>");
    
        // equipment/transponder code and aircraft color
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("EQUIPMENT: " . 
						(Len(Trim($row[$ICAO_Equipment_Codes_offset])) == 0 ? "S" : $row[$ICAO_Equipment_Codes_offset]) .
						"/" . 
						(Len(Trim($row[$ICAO_Transponder_offset])) == 0 ? "C" : $row[$ICAO_Transponder_offset]) .
						(Len(Trim($row[$ICAO_ADSB_Type_offset])) == 0 ? "" : $row[$ICAO_ADSB_Type_offset]),
						$LeftJustify, 34) . 
                    JustifyField("AIRCRAFT COLOR: " . $row[$Aircraft_Color_offset], $LeftJustify, 34) . "<BR>");
    
        // departure and remarks
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("DEPARTURE: ", $LeftJustify, 34) . 
                    JustifyField("REMARKS:", $LeftJustify, 34) . "<BR>");
    
        // time of departure and pilot's name, phone number
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("TIME OF DEPARTURE: ", $LeftJustify, 34) . 
					JustifyField("PILOT'S NAME:", $LeftJustify, 14) . 
                    JustifyField(getname() . " ". getPhoneNumber(), $LeftJustify, 34) . "<BR>");
         
        // cruising speed and blank
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("CRUISING SPEED: " . 
					FormatField($row[$Aircraft_Airspeed_offset], "Integer") . 
					" knots",
					$LeftJustify, 34) . 
                    JustifyField(" ", $LeftJustify, 34) . "<BR>");
    
        // altitude and blank
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("ALTITUDE: ", $LeftJustify, 34) . 
                    JustifyField(" ", $LeftJustify, 34) . "<BR>");
    
        // route and blank
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("ROUTE:", $LeftJustify, 34) . 
                    JustifyField(" ", $LeftJustify, 34) . "<BR>");
    
        // skip space
        PrintNonBreakingString("<BR>");
        
        // hobbs meter information
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("BEGINNING HOBBS: ", $LeftJustify, 17) . 
                    JustifyField(number_format($row[$Current_Hobbs_offset], 1), $RightJustify, 8) . 
                    Space(1) . 
                    JustifyField("ENDING HOBBS: ", $LeftJustify, 14) . 
                    JustifyField("__________", $LeftJustify, 11) . "<BR>");
        
        // skip space
        PrintNonBreakingString("<BR>");
        
        // tach information
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("BEGINNING TACH: ", $LeftJustify, 17) . 
                    JustifyField(number_format($row[$tach1_offset], 1), $RightJustify, 8) . 
                    Space(1) . 
                    JustifyField("ENDING TACH: ", $LeftJustify, 14) . 
                    JustifyField("__________", $LeftJustify, 11) . "<BR>");
        
        // skip space
        PrintNonBreakingString("<BR>");
        
        // local fuel information
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("LOCAL FUEL PUMPED: __________", $RightJustify, 50) . "<BR>");
        
        // skip space
        PrintNonBreakingString("<BR>");
        
        // local fuel information
        PrintNonBreakingString(Space($LeftMargin) . 
                    JustifyField("OIL USED:          __________", $RightJustify, 50) . "<BR>");
        
        // skip space
        PrintNonBreakingString("<BR>");
            
        // if this is not a simulator or a PCATD, print the weight and balance information
        if (LookupAircraftType($row[$code_id_offset]) != $SimulatorAircraftType And 
            LookupAircraftType($row[$code_id_offset]) != $PCATDAircraftType)
	    {
            // not a simulator, print the weight and balance information
            // note: this can take from 6 lines to 9 lines depending on aircraft configuration
            // header
            PrintNonBreakingString(Space($LeftMargin) . 
                        JustifyField("STATION", $LeftJustify, 24) . 
                        Space(1) . 
                        JustifyField("WEIGHT", $CenterJustify, 9) . 
                        Space(1) . 
                        JustifyField("ARM", $CenterJustify, 6) . 
                        Space(1) . 
                        JustifyField("MOMENT", $CenterJustify, 10) . "<BR>");
            
            //read the weight and balance information 
            $locWBFieldString = trim($WBFieldString);
            if (strlen($locWBFieldString) > 0)
            {
                // get the tokens from the fields
                $NumWBFieldRecords = 0;
                while (strlen($locWBFieldString) > 0)
                {
                    $WBStation = GetNextToken($locWBFieldString, ",");
                    $WBWeight = GetNextToken($locWBFieldString, ",");
                    $WBArm = GetNextToken($locWBFieldString, ",");
                    $WBMoment = GetNextToken($locWBFieldString, ",");
                    $WBGallons = GetNextToken($locWBFieldString, ";");
                                            
                    // print the fields
                    if (Left($WBStation, 6) == "Totals")
	                {
                        // total line 
                        PrintNonBreakingString(Space($LeftMargin) . 
                                    JustifyField($WBStation, $LeftJustify, 24) . 
                                    Space(1) . 
                                    JustifyField($WBWeight, $RightJustify, 9) . 
                                    Space(1) . 
                                    JustifyField($WBArm, $RightJustify, 6) . 
                                    Space(1) . 
                                    JustifyField($WBMoment, $RightJustify, 10) . 
                                    "<BR>");
                        
                        // save the aircraft total weight
                        $AircraftTotalWeight = Val($WBWeight);
                        $AircraftGrossWeight = $row[$max_gross_offset];
                        $WBCenterOfGravity = Val($WBMoment) / $AircraftTotalWeight;
	                }
	                elseif (Left($WBStation, 4) == "Fuel")
	                {
                        // fuel line, print the number of gallons on the line
                        PrintNonBreakingString(Space($LeftMargin) . 
                                    JustifyField($WBStation, $LeftJustify, 24) . 
                                    Space(1) . 
                                    JustifyField($WBWeight, $RightJustify, 9) . 
                                    Space(1) . 
                                    JustifyField($WBArm, $RightJustify, 6) . 
                                    Space(1) . 
                                    JustifyField($WBMoment, $RightJustify, 10) . 
                                    Space(5) . 
                                    JustifyField($WBGallons . " GALLONS", $LeftJustify, 14) . 
                                    "<BR>");
                    }
                    else
                    {
                        // normal line
                        PrintNonBreakingString(Space($LeftMargin) . 
                                    JustifyField($WBStation, $LeftJustify, 24) . 
                                    Space(1) . 
                                    JustifyField($WBWeight, $RightJustify, 9) . 
                                    Space(1) . 
                                    JustifyField($WBArm, $RightJustify, 6) . 
                                    Space(1) . 
                                    JustifyField($WBMoment, $RightJustify, 10) . 
                                    "<BR>");
                    }
                }
            }
            
            // skip space
            PrintNonBreakingString("<BR>");

            // CG line
            PrintNonBreakingString(Space($LeftMargin) . 
                        Space(13) . 
                        "Center of Gravity = " . 
                        number_format($WBCenterOfGravity, 3) . "<BR>");
            
            // maneuvering speed line
                    if($AircraftGrossWeight>0)
		    {
			$Va = $row[$Va_Max_Weight_offset] * sqrt($AircraftTotalWeight / $AircraftGrossWeight);
		    }
            PrintNonBreakingString(Space($LeftMargin) . 
                        Space(13) . 
                        "Maneuvering speed (Va) at " . 
                        FormatField(Str($AircraftTotalWeight), "Float") . 
                        "(lbs) is " . 
                        FormatField(Str($Va), "Integer") . 
                        " knots" . "<BR>");
        }
        else
        {
            // simulator, skip the weight and balance space
            PrintNonBreakingString("<BR>");
            PrintNonBreakingString("<BR>");
            PrintNonBreakingString("<BR>");
            PrintNonBreakingString("<BR>");
            PrintNonBreakingString("<BR>");
            PrintNonBreakingString("<BR>");
        }
    
        // skip space
        PrintNonBreakingString("<BR>");

        // check the type of form we are printing
        if ($FormType == "FILE")
        {        
            // printing the file copy
            // waiver information. since this may be more than one line, parse the line
            $WaiverLine = GetGeneralPreferenceValue("Checkout_Waiver_Text");
            $PrintLine = "";
            while (Len($WaiverLine) > 0)
            {
                if (Len($PrintLine) > $MaxLineLength)
    	        {
                    // at max line length, print the line
                    PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<BR>");
                    $PrintLine = "";
                }
                
                // get the next token and add to the printline
                $PrintLine = $PrintLine . GetNextToken($WaiverLine, " ") . " ";
            }
            
            // print the last line (if needed)
            if (Len($PrintLine) > 0)
    	    {
                PrintNonBreakingString(Space($LeftMargin) . $PrintLine . "<BR>");
            }
        }
        else
        {
            // printing the pilot copy
            // print the squawks if we are controlling squawks
            if ($AllowSquawkControl)
            {
                // if squawk tracking is enabled for this aircraft tail number
                $sql = "SELECT TrackSquawks FROM AircraftScheduling_aircraft WHERE n_number='$TailNumber'";
                if (sql_query1($sql) != 0)
                {
                    // print the open aircraft squawks
                    // get the information from the database about the selected aircraft
                    $sql = "SELECT date, description FROM Squawks WHERE " .
                                "(Aircraft = '" . Trim($TailNumber) . "') AND " .
                                "(Repair_Date = ' ')";    
                    $res = sql_query($sql);
                    
                    // if we didn't have any errors, process the results of the database inquiry
                    if($res) 
                    {                            
                        //see if any squawks were found for this aircraft
                        if (sql_count($res) == 0)
                        {
                            // no records found, print that on the sheet and exit
                            PrintNonBreakingString(Space($LeftMargin) .
                                "NO ACTIVE SQUAWKS FOR THIS AIRCRAFT" . "<BR>");
                        }
                        else
                        {
                            // put the title in the Squawk display
                            PrintNonBreakingString(Space($LeftMargin) .
                                        "OPEN AIRCRAFT SQUAWKS FOR " . Trim($TailNumber) . "<BR>");
                            PrintNonBreakingString(Space($LeftMargin) .
                                JustifyField("DATE", $CenterJustify, 12) . Space(1) . 
                                JustifyField("DESCRIPTION", $LeftJustify, 60) . "<BR>");
                                        
                            // print the squawk text
                    		for($i=0; $SquawkRow = sql_row($res, $i); $i++) 
                            {
                                // date of entry
                                $SquawkField = 
                                        JustifyField(FormatField($SquawkRow[0], "Date"), $CenterJustify, 12);
                                
                                // grounding status and description
                                $SquawkField = $SquawkField . Space(1) . 
                                    JustifyField($SquawkRow[1], $LeftJustify, 60);
                            
                                // print the record
                                PrintNonBreakingString(Space($LeftMargin) .
                                    $SquawkField . "<BR>");
                            }                
                        }
                    }
                    else
                    {
                        // error processing database request, tell the user
                        DisplayDatabaseError("PrintAircraftCheckoutFormPage", $sql);
                    }
                }
                else
                {
                    // squawks are not tracked for this aircraft, print that on the sheet so
                    // the pilot knows
                    PrintNonBreakingString(Space($LeftMargin) .
                            "SQUAWKS ARE NOT TRACKED FOR THIS AIRCRAFT" . "<BR>");
                }

            }
        }
        
        // skip space
        PrintNonBreakingString("<BR>");
        
        // flight manifest
        PrintNonBreakingString(Space($LeftMargin) . 
                    "*** FLIGHT MANIFEST ***" . "<BR>");
        // skip space
        PrintNonBreakingString("<BR>");
        
        // flight manifest line 1 (front seat)
        PrintNonBreakingString(Space($LeftMargin) . 
                    "1._________________________ 2. _________________________" . "<BR>");
        
        // flight manifest line 2 (back seat 1 if we have it)
        if ($row[$Rear_Seat_1_Arm_offset] != 0)
	    {
            PrintNonBreakingString("<BR>");
            PrintNonBreakingString(Space($LeftMargin) . 
                    "3._________________________ 4. _________________________" . "<BR>");
        }
        
        // flight manifest line 3 (back seat 2 if we have it)
        if ($row[$Rear_Seat_2_Arm_offset] != 0)
	    {
            PrintNonBreakingString("<BR>");
            PrintNonBreakingString(Space($LeftMargin) . 
                    "5._________________________ 6. _________________________" . "<BR>");
        }
        
        // skip space
        PrintNonBreakingString("<BR>");
        
        // check the type of form we are printing
        if ($FormType == "FILE")
        {        
            // printing the file copy
            // pilot signature
            PrintNonBreakingString(Space($LeftMargin) . 
                        "PILOT'S SIGNATURE (" . 
                        UCase(trim(getUserName())) . 
                        "):_________________________" . "<BR>");
            
            // skip space
            PrintNonBreakingString("<BR>");
            
            // clearing authority signature
            PrintNonBreakingString(Space($LeftMargin) . 
                        "CLEARING AUTHORITY (________):_________________________" . "<BR>");
        }
    }

    //********************************************************************
    // DisplayPilotInformation($TailNumber, $FlightStatus, $FlightStatusReason)
    //
    // Purpose: Display the currency information for the selected pilot.
    //
    // Inputs:
    //   TailNumber - the tail number of the aircraft to checkedout
    //
    // Outputs:
    //   FlightStatus - the flight status value:
    //                       DoesntApply - rule doesn't apply in this case
    //                       ClearedToFly - rule passes
    //                       ClearedToFlyDayOnly - night rule fails, allow VFR day flight
    //                       ClearedToFlyNoInstruments - rule fails, allow VFR but not instruments
    //                       NotClearedToFly - rule failed, no overrides allowed
    //                       NotClearedToFlyOverride - rule failed, overrides allowed
    //   FlightStatusReason - reason not cleared for flight (if needed)
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayPilotInformation($TailNumber, &$FlightStatus, &$FlightStatusReason)
    {        
        // get the current user
        $UserName = getUserName();
        
        // load the currency fields from the database
        LoadDBCurrencyFields($UserName);
        
        // get the pilot type
        $PilotType = LookupCurrencyFieldname("Rating");
        
        // put a summary of the recent flight experience in the form
        LoadRecentFlightExperience($UserName, $PilotType, 1, false, 0, "");
        
        // get the aircraft type from the database for the selected tailnumber
        $AircraftType = sql_query1("SELECT 
                                        model  
                            		FROM AircraftScheduling_aircraft a, 
                            			AircraftScheduling_model c 
                            		WHERE a.n_number='$TailNumber' AND 
                            			a.model_id=c.model_id");
        
        // show the currency information
        LoadCurrencyValues(
                            $UserName, 
                            $PilotType, 
                            $AircraftType, 
                            $TailNumber, 
                            $FlightStatus, 
                            $FlightStatusReason);
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
            echo "<TABLE>";        
    	    echo "<TR><TD COLSPAN=2><H4>Aircraft Information:</H4></TD></TR>";
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
    // BuildCGTable($TotalWeight, $TotalMoment, $AircraftGrossWeight, $WBFields)
    //
    // Purpose: Build a table display the CG balance information.
    //
    // Inputs:
    //   TotalWeight - total weight of the aircraft
    //   TotalMoment - the total moment of the aircraft
    //   AircraftGrossWeight - maximum weight of the aircraft
    //   WBFields - weight and balance fields read from the database
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildCGTable($TotalWeight, $TotalMoment, $AircraftGrossWeight, $WBFields)
    {
        // load the weight and balance CG tables from the database field
        LoadDBWBFields($WBFields);
        
        // skip some space	
        echo "<BR>"; 
        
        // start the CG table	
        echo "<TABLE ID='CGTable'>"; 
        
        // nothing computed yet, the javascript code will do the calculations
        $WBForwardLimitTextCaption = "Fore CG Not Computed";
        $WBCGStatusTextCaption = "CG Status Not Computed";
        $WBCenterOfGravityTextCaption = "CG Not Computed";
        $WBWeightStatusTextCaption = "Weight Status Not Computed";
        $WBAftLimitTextCaption = "Aft limit Not Computed";
        
        // CG line 1
        echo "<TR><TD> </TD><TD ALIGN=RIGHT>$WBForwardLimitTextCaption</TD><TD> </TD><TD>$WBCGStatusTextCaption</TD></TR>";
        
        // CG line 2
        echo "<TR><TD> </TD><TD ALIGN=RIGHT>$WBCenterOfGravityTextCaption</TD><TD> </TD><TD>$WBWeightStatusTextCaption</TD></TR>";
        
        // CG line 3
        echo "<TR><TD> </TD><TD ALIGN=RIGHT>$WBAftLimitTextCaption</TD><TD> </TD><TD> </TD></TR>";

        // finish the table
        echo "</TABLE>";
    }
    
    //********************************************************************
    // BuildWBTableEntry(
    //                   $RowNumber, 
    //                   $WBName, 
    //                   $Gallons, 
    //                   $Weight, 
    //                   $Arm, 
    //                   $Moment,
    //                   $AllowChanges)
    //
    // Purpose: Build a table row to display the weight and balance
    //          information.
    //
    // Inputs:
    //   RowNumber - the number of the row for this item
    //   WBName - the name of the weight and balance item
    //   Gallons - gallons for this item (ignored if 0)
    //   Weight - the weight of the item
    //   Arm - the arm for the item
    //   Moment - the moment for the item
    //   AllowChanges - set to 1 to allow changes to the gallons or weight
    //                  entries.
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildWBTableEntry(
                                $RowNumber, 
                                $WBName, 
                                $Gallons, 
                                $Weight, 
                                $Arm, 
                                $Moment,
                                $AllowChanges)
    {	
    	// set the size of the input boxes
    	$GallonsBoxSize = 2;
    	$WeightBoxSize = 3;

        // build the control name
        $WBControlName = Replace($WBName, " ", "_");

        // each entry is a table row
        echo "<TR>"; 
        
        // build the station column
        echo "<TD ALIGN=LEFT>$WBName</TD>";
        
        // build the gallons column
        if ($Gallons != 0)
        {        
            // entry has a gallon entry, build the control name
            
            // build the gallons entry
            if ($AllowChanges)
            {
                echo "<TD ALIGN=RIGHT>" . 
                        "<INPUT " .
                            "TYPE=TEXT " .
                            "NAME='" . $WBControlName . "Gallons' " . 
                            "ID='" . $WBControlName . "Gallons' " .
                            "ALIGN=RIGHT " . 
                            "SIZE=$GallonsBoxSize " . 
                            "MAXLENGTH=5 " . 
                            "VALUE='" . sprintf("%1.1f", $Gallons) . "' " . 
                            "Onchange='UpdateWBTable(0, $RowNumber, \"" . $WBControlName . "Gallons" . "\")'>" . 
                        "</TD>";
            }
            else
            {
                echo "<TD ALIGN=RIGHT>" . sprintf("%5.1f", $Gallons) . "</TD>";
            }
            
            // build the weight column, but don't let them change it directly
            // since updating gallons will update the weight
            echo "<TD ALIGN=RIGHT>" . sprintf("%5.1f", $Weight) . "</TD>";
        }
        else
        {
            // entry does not have gallons entry
            echo "<TD ALIGN=RIGHT> </TD>";
        
            // build the weight column and let them change it if requested
            if ($AllowChanges)
            {
                echo "<TD ALIGN=RIGHT>" . 
                        "<INPUT " .
                            "TYPE=TEXT " .
                            "NAME='" . $WBControlName . "Weight' " . 
                            "ID='" . $WBControlName . "Weight' " .
                            "ALIGN=RIGHT " . 
                            "SIZE=$WeightBoxSize " . 
                            "MAXLENGTH=5 " . 
                            "VALUE='" . sprintf("%1.1f", $Weight) . "' " . 
                            "Onchange='UpdateWBTable(1, $RowNumber, \"" . $WBControlName . "Weight" . "\")'>" . 
                        "</TD>";
            }
            else
            {
                echo "<TD ALIGN=RIGHT>" . sprintf("%5.1f", $Weight) . "</TD>";
            }
        }
        
        // build the arm column
        echo "<TD ALIGN=RIGHT>" . sprintf("%5.1f", $Arm) . "</TD>";
        
        // build the moment column
        echo "<TD ALIGN=RIGHT>" . sprintf("%5.1f", $Moment) . "</TD>";
        
        // end the table row
        echo "</TR>";    	    
    }
    
    //********************************************************************
    // DisplayWeightBalance($TailNumber)
    //
    // Purpose: Display the weight and balance information for the selected
    //          aircraft.
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
    function DisplayWeightBalance($TailNumber)
    {
        global $SimulatorAircraftType, $PCATDAircraftType;
        
        // set the column sizes
        $Column1Width = "44%";
        $Column2Width = "16%";
        $Column3Width = "16%";
        $Column4Width = "16%";
        $Column5Width = "16%";
        
        // init local data
        $TotalWeights = 0;
        $TotalArms = 0;
   
        // get the information from the database about the selected aircraft
        $sql = "SELECT " .  
                        "model_id, " .               // 0
                        "Fuel_Arm, " .               // 1
                        "Default_Fuel_Gallons, " .   // 2
                        "Aux_Fuel_Arm, " .           // 3
                        "Aux_Fuel_Gallons, " .       // 4
                        "Front_Seat_Arm, " .         // 5
                        "Front_Seat_Weight, " .      // 6
                        "Rear_Seat_1_Arm, " .        // 7
                        "Rear_Seat_1_Weight, " .     // 8
                        "Rear_Seat_2_Arm, " .        // 9
                        "Rear_Seat_2_Weight, " .     // 10
                        "Baggage_Area_1_Arm, " .     // 11
                        "Baggage_Area_1_Weight, " .  // 12
                        "Baggage_Area_2_Arm, " .     // 13
                        "Baggage_Area_2_Weight, " .  // 14
                        "Baggage_Area_3_Arm, " .     // 15
                        "Baggage_Area_3_Weight, " .  // 16
                        "Aircraft_Arm, " .           // 17
                        "empty_weight, " .           // 18
                        "max_gross, " .              // 19
                        "WB_Fields " .               // 20
        		"FROM AircraftScheduling_aircraft " . 
        		"WHERE n_number='$TailNumber'";    
        $res = sql_query($sql);
        
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // process the results of the database inquiry
            $row = sql_row($res, 0);

            // if this is not a simulator or PCATD
            if (LookupAircraftType($row[0]) != $SimulatorAircraftType &&
                LookupAircraftType($row[0]) != $PCATDAircraftType)
            {                
                // load the station information into the form
        	    echo "<H4>Weight & Balance:</H4>";

                // start the W & B table
                echo "<TABLE ID='WBTable'>";        
        	    
        	    // put in the headers
                echo "<TR>" . 
                        "<TD WIDTH=$Column1Width ALIGN=CENTER><B>Station</B></TD>" .
                        "<TD WIDTH=$Column2Width ALIGN=CENTER><B>Gallons</B></TD>" .
                        "<TD WIDTH=$Column3Width ALIGN=CENTER><B>Weight</B></TD>" .
                        "<TD WIDTH=$Column4Width ALIGN=CENTER><B>Arm</B></TD>" .
                        "<TD WIDTH=$Column5Width ALIGN=CENTER><B>Moment</B></TD>" .
                      "</TR>";    	    

                // if the arm is zero, assume that that item is not used
                $RowNumber = 0;
                       
                // fuel
                if ($row[1] != 0)
                {
                    $RowNumber++;
                    $Gallons = $row[2];
                    $Weight = $row[2] * 6;
                    $Arm = $row[1];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Fuel", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }

                // aux fuel
                if ($row[3] != 0)
                {
                    $RowNumber++;
                    $Gallons = $row[4];
                    $Weight = $row[4] * 6;
                    $Arm = $row[3];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Aux Fuel", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // front seat
                if ($row[5] != 0)
                {
                    $RowNumber++;
                    $Gallons = 0;
                    $Weight = $row[6];
                    $Arm = $row[5];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Front Seat", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // rear seat 1
                if ($row[7] != 0)
                {
                    $RowNumber++;
                    $Gallons = 0;
                    $Weight = $row[8];
                    $Arm = $row[7];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Back Seat", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // rear seat 2
                if ($row[9] != 0)
                {
                    $RowNumber++;
                    $Gallons = 0;
                    $Weight = $row[10];
                    $Arm = $row[9];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Back Seat 2", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // baggage area 1
                if ($row[11] != 0)
                {
                    $RowNumber++;
                    $Gallons = 0;
                    $Weight = $row[12];
                    $Arm = $row[11];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Baggage Area 1", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // baggage area 2
                if ($row[13] != 0)
                {
                    $RowNumber++;
                    $Gallons = 0;
                    $Weight = $row[14];
                    $Arm = $row[13];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Baggage Area 2", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // baggage area 3
                if ($row[15] != 0)
                {
                    $RowNumber++;
                    $Gallons = 0;
                    $Weight = $row[16];
                    $Arm = $row[15];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Baggage Area 3", $Gallons, $Weight, $Arm, $Moment, 1);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // aircraft
                if ($row[17] != 0)
                {
                    $RowNumber++;
                    $Gallons = 0;
                    $Weight = $row[18];
                    $Arm = $row[17];
                    $Moment = $Weight * $Arm;
                    BuildWBTableEntry($RowNumber, "Aircraft", $Gallons, $Weight, $Arm, $Moment, 0);
                    $TotalWeights = $TotalWeights + $Weight;
                    $TotalArms = $TotalArms + $Moment;
                }
                
                // total line
                $AircraftGrossWeight = $row[19];
                echo "<TR>" . 
                    "<TD ALIGN=LEFT COLSPAN=2>Totals (Max Wt = " . sprintf("%5.1f", $AircraftGrossWeight) . ")</TD>" .
                    "<TD ALIGN=RIGHT>" . sprintf("%5.1f", $TotalWeights) . "</TD>" .
                    "<TD ALIGN=RIGHT> </TD>" .
                    "<TD ALIGN=RIGHT>" . sprintf("%5.1f", $TotalArms) . "</TD>" .
                  "</TR>";    	    
                
                // finish the table
                echo "</TABLE>";
                
                // compute and display the CG positions
                $WBFields = $row[20];
                BuildCGTable($TotalWeights, $TotalArms, $AircraftGrossWeight, $WBFields);       
            }
            Else
            {
                // simulator, don't display weight and balance entries
            }
        }
        else
        {
            // error processing database request, tell the user
            DisplayDatabaseError("DisplayWeightBalance", $sql);
        }
    }
    
    //********************************************************************
    // DisplayAircraftInformation($TailNumber)
    //
    // Purpose: Display the information for the selected aircraft.
    //
    // Inputs:
    //   TailNumber- tail number of the selected aircraft
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayAircraftInformation($TailNumber)
    {
        // display the aircraft status
        DisplayAircraftStatus($TailNumber);
        
        // skip some space
        echo "<BR>";
        
        // display the aircraft weight and balance
        DisplayWeightBalance($TailNumber);
    }
    
    //********************************************************************
    // CheckClearingAuthorityCurrency(ClearingAuthorityKeyCode As String,
    //                         ClearingAuthorityFlightStatus As CurrencyCheckType,
    //                         ClearingAuthorityFlightStatusReason As String)
    //
    // Purpose: Check a clearing authority's currency and return the results.
    //
    // Inputs:
    //   ClearingAuthorityKeyCode - keycode for the ClearingAuthority
    //
    // Outputs:
    //   ClearingAuthorityFlightStatus - results of the currency check
    //   ClearingAuthorityFlightStatusReason - reason for any currency failure
    //
    // Returns:
    //   none
    //
    // Notes:
    //
    //*********************************************************************
    function CheckClearingAuthorityCurrency( 
                                        $ClearingAuthorityKeyCode, 
                                        &$ClearingAuthorityFlightStatus,
                                        &$ClearingAuthorityFlightStatusReason)
    {                                                
        global $CurrentlySelectedAircraftType; $CurrentlySelectedTailnumber;
        global $DoesntApply, $ClearedToFly, $ClearedToFlyDayOnly;
        global $ClearedToFlyNoInstruments, $NotClearedToFly, $NotClearedToFlyOverride;
        
        // load the currency fields from the database
        LoadDBCurrencyFields($ClearingAuthorityKeyCode);
        
        // get the pilot type
        $PilotType = LookupCurrencyFieldname("Rating");
        
        // check the clearing authority's currency
        GetCurrencyValues(
                            $PilotType,
                            $ClearingAuthorityKeyCode,
                            $CurrencyRuleString,
                            $CurrencyStatusString,
                            $PilotIdentificationString,
                            $ClearingAuthorityFlightStatus,
                            $ClearingAuthorityFlightStatusReason,
                            $CurrentlySelectedAircraftType,
                            "ALL");          // check all aircraft IDs

        // verify the clearing authority flight status
        switch ($ClearingAuthorityFlightStatus)
        {
        Case $ClearedToFly:
            // cleared, allow him to clear
            $ClearingAuthorityFlightStatus = $ClearedToFly;
            $ClearingAuthorityFlightStatusReason = "";
            break;
        Case $ClearedToFlyNoInstruments:
            // not cleared for instruments but cleared for VFR, allow him to clear
            $ClearingAuthorityFlightStatus = $ClearedToFly;
            $ClearingAuthorityFlightStatusReason = "";
            break;
        Case $ClearedToFlyDayOnly:
            // cleared for day flight only, allow him to clear
            $ClearingAuthorityFlightStatus = $ClearedToFly;
            $ClearingAuthorityFlightStatusReason = "";
            break;
        Case $NotClearedToFly:
            // not cleared no overrides allowed, don't allow him to clear
            break;
        Case $NotClearedToFlyOverride:
            // not cleared overrides allowed, don't allow him to clear
            break;
        }
    }
   
    //********************************************************************
    // BuildClearingAuthorityList()
    //
    // Purpose: Build the clearing authority list for the javascript code.
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
    function BuildClearingAuthorityList()
    {
        global $DoesntApply, $ClearedToFly, $ClearedToFlyDayOnly;
        global $ClearedToFlyNoInstruments, $NotClearedToFly, $NotClearedToFlyOverride;

        // counter for the number of clearing authorities found
        $NumClearingAuthority = 0;
        
        // build the global arrays for the javascript processing
        echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    
        // generate the code to load the javascipt arrays
        echo "var ClearingAuthorityList = new Array(";
        
        // get a list of the clearing authorities from the database
        $sql = "SELECT username  " . 
               "FROM AircraftScheduling_person  " . 
               "WHERE ((Clearing_Authority=1 OR " .
                        "INSTR(Rules_Field, 'Rating,CFI') OR " .
                        "INSTR(Rules_Field, 'Rating,CFII')) AND " .
                        "INSTR(Rules_Field, 'Member_Status,Active'))" .
               "ORDER BY username";
        $res = sql_query($sql);
        
        // if we have any clearing authorities
        if ($res)
        {
            // process the clearing authorities
            $NumClearingAuthority = 0;
            for ($i = 0; $i < sql_count($res); $i++)
            {
                // get the user information from the database
                $row = sql_row($res, $i);
                $NumClearingAuthority++;   
                if ($NumClearingAuthority == 1)
                    echo "'$row[0]'";
                else
                    echo ", '$row[0]'";
            }
        }
        
        // close the array
        echo ");";

        // save the number of clearing authorities found
        echo "var NumClearingAuthority = $NumClearingAuthority;";
        echo "</SCRIPT>";            
    }

    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
    $ClearingAuthorityError = '';

	#If we dont know the right date then make it up 
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

    // this script will call itself whenever the Checkout or Cancel button is pressed
    // we will check here for the checkout and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $AircraftCheckout == "Checkout")
    {
        // acquire mutex to prevent concurrent check-in/check-out
        if (!sql_mutex_lock('AircraftScheduling_aircraft'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // aircraft is being checked out
                
        // if a clearing authority was specified, make sure that they are cleared to fly
        // the javascript code won't let us get here unless a username is entered or
        // it is not required
        if (strlen($ClearingKeyCode) > 0)
        {
            // clearing authority was entered, make sure the user is current    
            CheckClearingAuthorityCurrency($ClearingKeyCode, $FlightStatus, $FlightStatusReason);
            if ($FlightStatus == $ClearedToFly)
            {
                // valid clearing authority
                $ClearingAuthorityError = '';
            }
            else
            {
                // user is not current to fly, can't be a clearing authority
                $ClearingAuthorityError = "<H4><CENTER>User " . strtoupper($ClearingKeyCode) . 
                                            " is not cleared to fly<BR>" .
                                        "$FlightStatusReason<BR>" .
                                        "Pick another clearing authority</CENTER></H4>";
            }
        }
        
        // if we didn't have any clearing authority errors, checkout the aircraft
        if (strlen($ClearingAuthorityError) == 0)
        {
            // no errors, checkout the aircraft — wrap in transaction for atomicity
            sql_begin();
            $DatabaseFields = array();

            // mark the aircraft as checked out in the database
            SetDatabaseRecord("status",
                        LookupAircraftStatus($CheckedOutString), $RSConversionString, $DatabaseFields[0]);
            SetDatabaseRecord("Current_User",
                        getName(), $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("CurrentKeyCode",
                        getUserName(), $RSConversionString, $DatabaseFields[2]);

            // if we had a clearing authority, put that in the database
            if (strlen($ClearingKeyCode) > 0)
            {
                // a clearing authority was required for clearing, save it in the database
                SetDatabaseRecord("Cleared_By",
                                $ClearingKeyCode, $RSConversionString, $DatabaseFields[3]);
            }
            else
            {
                // no clearing authority was requrired
                SetDatabaseRecord("Cleared_By", "", $RSConversionString, $DatabaseFields[3]);
            }

            // complete the database updates — only if aircraft is still Online (status=1)
            // to prevent double-checkout race condition
            $affected = UpdateDatabaseRecord(
                                "AircraftScheduling_aircraft",
                                $DatabaseFields,
                                "n_number='" . UCase(Trim($TailNumber)) . "' AND status=1");

            if ($affected == 0)
            {
                // aircraft was not in "On Line" state — another user may have
                // already checked it out. Roll back and notify.
                sql_rollback();
                sql_mutex_unlock('AircraftScheduling_aircraft');
                print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
                echo "<H4><CENTER>Aircraft " . htmlspecialchars($TailNumber) .
                     " is no longer available for checkout.<BR>" .
                     "It may have been checked out by another user or taken offline.</CENTER></H4>";
                include "trailer.inc";
                exit;
            }

            sql_commit();
            sql_mutex_unlock('AircraftScheduling_aircraft');

            // log the aircraft checkout in the journal
        	$Description = "Aircraft " . $TailNumber . " checked out by " . getName();
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
            
            // print the aircraft checkout form copies
            for ($i = 0; $i < GetServerPreferenceValue("Number_of_Check_out_Copies"); $i++)
            {
                PrintAircraftCheckoutForm($TailNumber);
            }
               
            // finish the print form
            CompletePrintFunctions();
                            
            // finished with this part of the script
            exit;
        }
        else
        {
            // clearing authority error — release the mutex before continuing
            sql_mutex_unlock('AircraftScheduling_aircraft');
        }
    }
    else if(count($_POST) > 0 && $AircraftCancel == "Cancel") 
    {
        // user canceled the checkout, take them back to the last screen
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

    // neither Update or Cancel were selected or we had a problem with checkout, display
    // the main screen
    
    // if the tailnumber is not set, see if we can guess the aircraft that the user
    // is about to checkout
    $TailNumber = GetDefaultTailNumber($TailNumber);
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "UpdateCGTable");

    // save the user name for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var CurrentUserName = '" . getUserName() . "';";
    echo "</SCRIPT>";
    
    // start the form
	echo "<FORM NAME='main' ACTION='AircraftCheckout.php' METHOD='POST'>";

    // start the table to display the aircraft checkout information
    echo "<center>";
    echo "<table border=0>";

    // display the aircraft selection drop down box
    echo "<TR><TD COLSPAN=4>";
    echo "<CENTER><B>Aircraft To Checkout: </B>";
    $TailNumber = DisplayAircraftDropDown(
                                        $TailNumber, 
                                        true, 
                                        false, 
                                        "status=" . LookupAircraftStatus($OnLineString));
    echo "</CENTER></TD></TR>";
     
    // fill the left column of the table with the pilot information
    echo "<TR><TD>";
    DisplayPilotInformation($TailNumber, $FlightStatus, $FlightStatusReason);
    echo "</TD>";

    // skip some space between columns
    echo "<TD> </TD>";
    echo "<TD> </TD>";
    
    // fill the right column of the table with the aircraft information
    echo "<TD>";
    DisplayAircraftInformation($TailNumber);
    echo "</TD></TR>";

    // finished with the table
    echo "</table>";
        
    // display the aircraft squawks if we are managing squawks
    if ($AllowSquawkControl)
    {
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
        
    // put the flight status information on the screen
    switch ($FlightStatus)
    {
    case $ClearedToFly:
        // cleared to fly
        // if this is a student, show that they are cleared as a student
        If (LookupCurrencyFieldname("Rating") == $StudentPilot)
        {
            echo "<H4>";
            echo "Cleared For Flight - Student";
            echo "</H4>";
        }
        Else
        {
            echo "<H4>";
            echo "Cleared For Flight";
            echo "</H4>";
        }
        break;
    case $ClearedToFlyDayOnly:
        // not cleared for night flight, put the information in the form
        echo "<H4>";
        echo "Cleared For Flight - Day Only";
        echo "<BR>" . $FlightStatusReason;
        echo "</H4>";
        break;
    case $ClearedToFlyNoInstruments:
        // not cleared for instruments, put the information in the form
        echo "<H4>";
        echo "Cleared For Flight - VFR Only";
        echo "<BR>" . $FlightStatusReason;
        echo "</H4>";
        break;
    case $NotClearedToFly:
        // not cleared no overrides allowed, put the information in the form
        echo "<H4>";
        echo "Not Cleared For Flight - Grounded";
        echo "<BR>" . $FlightStatusReason;
        echo "</H4>";
        break;
    case $NotClearedToFlyOverride:
        // not cleared overrides allowed, put the information in the form
        echo "<H4>";
        echo "Not Cleared For Flight";
        echo "<BR>" . $FlightStatusReason;
        echo "</H4>";
        
        // show the clearing authority information so that a clearing authority can override
        echo "<TABLE>";
        echo "<TR>";
        echo "<TD>Enter Clearing Authority Key Code For Flight:</TD>";
        echo "<TD><input " . 
                        "type=text " .
                        "name='ClearingKeyCode' " . 
                        "id='ClearingKeyCode' " . 
                        "size=10 " . 
                        "maxlength=10 " . 
                 "></TD>";
        echo "</TR>";
        // if the user entered an invalid clearing authority, show the error
        if (strlen($ClearingAuthorityError) > 0)
        {
            // no errors, checkout the aircraft
            echo "<TR>";
            echo "<TD COLSPAN=2>$ClearingAuthorityError</TD>";
            echo "</TR>";
        }
        echo "</TABLE>";
        
        // build the clearing authority list for the javascript code
        BuildClearingAuthorityList();
        break;
    }

    // generate the code to load the pilot status javascipt information variables
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var FlightStatus = $FlightStatus;";
    echo "var DoesntApply = $DoesntApply;";
    echo "var ClearedToFly = $ClearedToFly;";
    echo "var ClearedToFlyDayOnly = $ClearedToFlyDayOnly;";
    echo "var ClearedToFlyNoInstruments = $ClearedToFlyNoInstruments;";
    echo "var NotClearedToFly = $NotClearedToFly;";
    echo "var NotClearedToFlyOverride = $NotClearedToFlyOverride;";
    echo "</SCRIPT>";
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
    
    // put the placeholder control for the weight and balance fields
    // the javascript code will fill these in as the weight and balance is calculated
    echo "<input name='WBFieldString' id='WBFieldString' type='hidden' value='$WBFieldString'";    
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='AircraftCheckout' type=submit value='Checkout' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelCheckout"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

// javascript global data
var WBCGOutOfRange;         // amount that the aircraft is out of balance
var WBWeightOutOfRange;     // amount that the aircraft is overweight (lbs)
var MaxWeightCol = 0;       // column number for max weight field
var TotalWeightCol = 1;     // column number for total weight field
var TotalMomentCol = 3;     // column number for total moment field

//********************************************************************
// UpdateWBTable(UpdatingWeight, RowNumber, UpdatedControl)
//
// Purpose: Update the weight and balance entry given by RowNumber
//          and update the totals line.
//
// Inputs:
//   UpdatingWeight - set true if updating a weight field, false if
//                    updating a gallons field.
//   RowNumber - the row number within the table that we are updating
//   UpdatedControl - the name of the control that contains the new
//                    weight or gallons entry.
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
function UpdateWBTable(UpdatingWeight, RowNumber, UpdatedControl)
{
    var x = document.getElementById('WBTable').rows;
    var y = x[RowNumber].cells;
    var StationCol = 0;     // column number for station field
    var GallonsCol = 1;     // column number for gallons field
    var WeightCol = 2;      // column number for weight field
    var ArmCol = 3;         // column number for arm field
    var MomentCol = 4;      // column number for moment field
    var TotalWeightCol = 1; // column number for total weight field
    var TotalMomentCol = 3; // column number for total moment field

    // if we are updating the weight field instead of the gallons field
    if (UpdatingWeight)
    {
        // updating weight field
        StationWeight = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(StationWeight)) StationWeight = 0.0;
        
        // compute and save the old weight
        OldStationWeight = parseFloat(y[MomentCol].innerHTML / y[ArmCol].innerHTML);
        
        // save the old moment
        OldStationMoment = parseFloat(y[MomentCol].innerHTML);
        
        // update the weight and moment of the station
        document.getElementById(UpdatedControl).value = format(StationWeight, 1);
        StationMoment = StationWeight * y[ArmCol].innerHTML;
        y[MomentCol].innerHTML = format(StationMoment, 1);
    }
    else
    {
        // updating gallons field
        Gallons = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(Gallons)) Gallons = 0.0;
        document.getElementById(UpdatedControl).value = format(Gallons, 1);
        
        // compute and save the old weight
        OldStationWeight = parseFloat(y[MomentCol].innerHTML / y[ArmCol].innerHTML);
        
        // save the old moment
        OldStationMoment = parseFloat(y[MomentCol].innerHTML);
        
        // update the weight and moment of the fuel
        StationWeight = Gallons * 6;
        y[WeightCol].innerHTML = format(StationWeight, 1);
        StationMoment = StationWeight * y[ArmCol].innerHTML;
        y[MomentCol].innerHTML = format(StationMoment, 1);
    }
    
    // get the location of the totals line within the weight & balance table
    var NumberRows = x.length;
    var TotalWeight;
    var TotalMoment;
    y = x[NumberRows - 1].cells;
    
    // compute the totals
    TotalWeight = parseFloat(y[TotalWeightCol].innerHTML) - OldStationWeight + StationWeight;
    TotalMoment = parseFloat(y[TotalMomentCol].innerHTML) - OldStationMoment + StationMoment;
    
    // update the totals line
    y[TotalWeightCol].innerHTML = format(TotalWeight, 1);
    y[TotalMomentCol].innerHTML = format(TotalMoment, 1);
    
    // update the CG table
    UpdateCGTable();
}
 
//********************************************************************
// WBAdjustFuelWeight(OverLoadedWeight as Integer)
//
// Purpose:  Adjust the fuel to get the aircraft within max weight
//
// Inputs:
//   OverLoadedWeight - Number of extra pounds of weight of the aircraft
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function WBAdjustFuelWeight(OverLoadedFuel)
{
    // compute the amount of fuel we need to lose and
    // adjust the fuel quantity of the weight and balance
    // information.
    ExistingFuel = document.getElementById('FuelGallons').value;
    ExistingFuel = ExistingFuel - ((OverLoadedFuel / 6) + 1);
    
    // don't let the fuel go below zero
    if (ExistingFuel < 0) ExistingFuel = 0;
    
    // update the fuel weight
    document.getElementById('FuelGallons').value = format(ExistingFuel, 1);
    UpdateWBTable(0, 1, "FuelGallons");
    
    // adjust the weight and balance information
    UpdateCGTable();
}
    
//********************************************************************
// LookupWBInformation(AircraftWeight as single,
//                     FieldSelection as string) as single
//
// Purpose: Lookup the value of the aircraft weight and return
//          the forward CG limit or the aft CG limit depending
//          on the FieldSelection
//
// Inputs:
//   AircraftWeight - the weight of the aircraft
//   FieldSelection - the selection of the field to output
//                    ForeCG or AftCG
//
// Outputs:
//   none
//
// Returns:
//   ForeCG - the forward CG limit or
//   AftCG - the aft CG limit depending on FieldSelection
//
// Notes:
//
//*********************************************************************
function LookupWBInformation(AircraftWeight, FieldSelection)
{
    var i;
    var ScaleFactor;
    var ForeCG;
    var AftCG;
    
    if (AircraftWeight <= WBAircraftWeight[0])
    {
        // check for less than the first, use the first if so
        ForeCG = WBForeCG[0];
        AftCG = WBAftCG[0];
    }
    else if (AircraftWeight >= WBAircraftWeight[NumWBFieldRecords - 1])
    {
        // check for greater than the last, use the last if so
        ForeCG = WBForeCG[NumWBFieldRecords - 1];
        AftCG = WBAftCG[NumWBFieldRecords - 1];
    }
    else
    {
        //set the default values if the weight is not found
        ForeCG = 0;
        AftCG = 0;

        // search the WBFieldRecords for a match on AircraftWeight
        for(i = 0; i < NumWBFieldRecords - 1; i++)
        {
            if (AircraftWeight >= WBAircraftWeight[i] &&
                AircraftWeight <= WBAircraftWeight[i + 1])
            {
                // assume that the difference between the weights is linear
                // and scale the CGs
                ScaleFactor = (AircraftWeight - WBAircraftWeight[i]) / 
                                (WBAircraftWeight[i + 1] - WBAircraftWeight[i]);
                                
                // weight found get the CG limits
                ForeCG = WBForeCG[i] + 
                    (WBForeCG[i + 1] - WBForeCG[i]) * ScaleFactor;
                AftCG = WBAftCG[i] +
                    (WBAftCG[i + 1] - WBAftCG[i]) * ScaleFactor;
                break;
            }
        }
    }
    
    // pass back the value requested
    if (FieldSelection.toUpperCase() == "FORECG")
    {
        // return the forward CG limit
        return ForeCG;
    }
    else
    {
        // return the Aft CG limit
        return AftCG;
    }
}

//********************************************************************
// IsFieldUsed(FieldName)
//
// Purpose: Return true if the object exists within the form.
//
// Inputs:
//   FieldName - name of the field to test
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function IsFieldUsed(FieldName)
{
    // see if the object exists on the form
    NumberElements = document.forms['main'].elements.length;
    for (var i = 0; i < NumberElements; i++)
    {
        if (document.forms['main'].elements[i].name == FieldName) 
        {
            // object found, return true
            return true;
        }
    }
    
    // object not found, return false
    return false;
}

//********************************************************************
// UpdateWBFieldControl()
//
// Purpose: Update the WBFieldString control so that the WB information
//          is saved for printing.
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
function UpdateWBFieldControl()
{
    var WBFieldString = '';
    var x = document.getElementById('WBTable').rows;
    var RowNumber = 1;
    
    // fuel WB fields
    if (IsFieldUsed('FuelGallons'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
        
        // weight
        WBFieldString = WBFieldString + y[2].innerHTML + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gallons
        WBFieldString = WBFieldString + document.getElementById('FuelGallons').value + ';';     
        RowNumber++;
    }
    
    // aux fuel WB fields
    if (IsFieldUsed('Aux_FuelGallons'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
        
        // weight
        WBFieldString = WBFieldString + y[2].innerHTML + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gallons
        WBFieldString = WBFieldString + document.getElementById('Aux_FuelGallons').value + ';';     
        RowNumber++;
    }
    
    // front seat WB fields
    if (IsFieldUsed('Front_SeatWeight'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
                
        // weight
        WBFieldString = WBFieldString + document.getElementById('Front_SeatWeight').value + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gas field not used
        WBFieldString = WBFieldString + ' ' + ';';     
        RowNumber++;
    }
    
    // rear seat 1 WB fields
    if (IsFieldUsed('Back_SeatWeight'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
                
        // weight
        WBFieldString = WBFieldString + document.getElementById('Back_SeatWeight').value + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gas field not used
        WBFieldString = WBFieldString + ' ' + ';';      
        RowNumber++;
    }
    
    // rear seat 2 WB fields
    if (IsFieldUsed('Back_Seat_2Weight'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
                
        // weight
        WBFieldString = WBFieldString + document.getElementById('Back_Seat_2Weight').value + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gas field not used
        WBFieldString = WBFieldString + ' ' + ';';      
        RowNumber++;
    }
    
    // baggage area 1 WB fields
    if (IsFieldUsed('Baggage_Area_1Weight'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
                
        // weight
        WBFieldString = WBFieldString + document.getElementById('Baggage_Area_1Weight').value + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gas field not used
        WBFieldString = WBFieldString + ' ' + ';';      
        RowNumber++;
    }
    
    // baggage area 2 WB fields
    if (IsFieldUsed('Baggage_Area_2Weight'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
                
        // weight
        WBFieldString = WBFieldString + document.getElementById('Baggage_Area_2Weight').value + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gas field not used
        WBFieldString = WBFieldString + ' ' + ';';      
        RowNumber++;
    }
        
    // baggage area 3 WB fields
    if (IsFieldUsed('Baggage_Area_3Weight'))
    {
        // field is used, save the values
        y = x[RowNumber].cells;
        
        // field name
        WBFieldString = WBFieldString + y[0].innerHTML + ','; 
                
        // weight
        WBFieldString = WBFieldString + document.getElementById('Baggage_Area_3Weight').value + ',';     
        
        // arm
        WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
        // moment
        WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
        // gas field not used
        WBFieldString = WBFieldString + ' ' + ';';    
        RowNumber++;
    }
    
    // aircraft WB fields
    y = x[RowNumber].cells;
    
    // aircraft field name
    WBFieldString = WBFieldString + y[0].innerHTML + ','; 
            
    // aircraft weight
    WBFieldString = WBFieldString + y[2].innerHTML + ',';     
    
    // aircraft arm
    WBFieldString = WBFieldString + y[3].innerHTML + ',';     
    
    // aircraft moment
    WBFieldString = WBFieldString + y[4].innerHTML + ',';     
        
    // gas field not used
    WBFieldString = WBFieldString + ' ' + ';';      
    RowNumber++;
    
    // total WB fields
    y = x[RowNumber].cells;
    
    // total field name
    WBFieldString = WBFieldString + y[0].innerHTML + ','; 
            
    // total weight
    WBFieldString = WBFieldString + y[1].innerHTML + ',';     
    
    // total arm
    WBFieldString = WBFieldString + ' ' + ',';     
    
    // total moment
    WBFieldString = WBFieldString + y[3].innerHTML + ',';     
        
    // gas field not used
    WBFieldString = WBFieldString + ' ' + ';';      
    RowNumber++;
    
    // save the updated WB fields
    document.getElementById('WBFieldString').value = WBFieldString;
}

//********************************************************************
// UpdateCGTable()
//
// Purpose: Update the table that displays the CG balance information.
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
function UpdateCGTable()
{
    var WBCenterOfGravityTextCaption;
    var WBForwardLimitTextCaption;
    var WBAftLimitTextCaption;
    var WBCGStatusTextCaption;
    var WBCGStatusTextForeColor;
    var WBCenterOfGravityTextForeColor;
    var x = document.getElementById('WBTable').rows;
    var NumberRows = document.getElementById('WBTable').rows.length;
    var y = x[NumberRows - 1].cells;
    
    // get the aircraft information from the weight and balance table
    var TotalWeight = y[TotalWeightCol].innerHTML;
    var TotalMoment = y[TotalMomentCol].innerHTML;
    var AircraftGrossWeight = parseFloat(y[MaxWeightCol].innerHTML.substr(17, 6));
    
    // center of gravity calculation
    if (TotalWeight > 0)
    {
        WBCenterOfGravityTextCaption = "Center of Gravity = " + format((TotalMoment / TotalWeight), 3);
        WBForwardLimitTextCaption = "Forward CG Limit = " + format(LookupWBInformation(TotalWeight, "ForeCG"), 3);
        WBAftLimitTextCaption = "Aft CG Limit = " + format(LookupWBInformation(TotalWeight, "AftCG"), 3);
        
        // check for CG out of limits conditions
        if ((TotalMoment / TotalWeight) < LookupWBInformation(TotalWeight, "ForeCG"))
        {
            // CG is forward
            WBCGStatusTextCaption = "CG is Forward by " +
                format((LookupWBInformation(TotalWeight, "ForeCG") - (TotalMoment / TotalWeight)), 3);
            WBCGStatusTextForeColor = "RED";
            WBCenterOfGravityTextForeColor = "RED";
            WBCGOutOfRange = (-1) * (LookupWBInformation(TotalWeight, "ForeCG") - (TotalMoment / TotalWeight));
        }
        else if ((TotalMoment / TotalWeight) > LookupWBInformation(TotalWeight, "AftCG"))
        {
            // CG is aft
            WBCGStatusTextCaption = "CG is Aft by " +
                format((TotalMoment / TotalWeight) - (LookupWBInformation(TotalWeight, "AftCG")), 3);
            WBCGStatusTextForeColor = "RED";
            WBCenterOfGravityTextForeColor = "RED";
            WBCGOutOfRange = (TotalMoment / TotalWeight) - (LookupWBInformation(TotalWeight, "AftCG"));
        }
        else
        {
            // CG is within limits
            WBCGStatusTextCaption = "CG Is Within Limits ";
            WBCGStatusTextForeColor = "BLACK";
            WBCenterOfGravityTextForeColor = "BLACK";
            WBCGOutOfRange = 0;
        }
    }
    else
    {
        // CG couldn't be calculated
        // CG is within limits
        WBCGStatusTextCaption = "CG Not Calculated ";
        WBCGStatusTextForeColor = "BLACK";
        WBCenterOfGravityTextForeColor = "BLACK";
        WBCGOutOfRange = 0;
    }

    // check for weight out of limits
    if (TotalWeight > AircraftGrossWeight)
    {
        // weight is too high
        WBWeightStatusTextCaption = "Wt high by " +
            format((TotalWeight - AircraftGrossWeight), 1) +
            " lbs";
        WBWeightStatusTextForeColor = "RED";
        WBWeightOutOfRange = TotalWeight - AircraftGrossWeight;
    }
    else
    {
        // weight is within limits
        WBWeightStatusTextCaption = "Weight Is Within Limits ";
        WBWeightStatusTextForeColor = "BLACK";
        WBWeightOutOfRange = 0;
    }
    
    // update the CG table
    x = document.getElementById('CGTable').rows;
        
    // CG line 1
    y = x[0].cells;
    y[1].innerHTML = WBForwardLimitTextCaption;
    y[3].innerHTML = "<font color='" + WBCGStatusTextForeColor + "'>" + 
                        WBCGStatusTextCaption + 
                     "</font>";
    
    // CG line 2
    y = x[1].cells;
    y[1].innerHTML = "<font color='" + WBCenterOfGravityTextForeColor + "'>" + 
                        WBCenterOfGravityTextCaption + 
                     "</font>";
    y[3].innerHTML = "<font color='" + WBWeightStatusTextForeColor + "'>" + 
                        WBWeightStatusTextCaption + 
                     "</font>";
    
    // CG line 3
    y = x[2].cells;
    y[1].innerHTML = WBAftLimitTextCaption;
    
    // save the WB fields for the print HTML
    UpdateWBFieldControl();
}

//********************************************************************
// IsClearingAuthorityKeyCode(ClearingAuthorityUsername)
//
// Purpose: Verity that the given username is a valid clearing 
//          authority. Note that the list that is checked is provided
//          by the php code since we can't read from the database.
//
// Inputs:
//   ClearingAuthorityUsername - the username to check
//
// Outputs:
//   none
//
// Returns:
//   true if the user is a valid clearing authority, false otherwise.
//*********************************************************************
function IsClearingAuthorityKeyCode(ClearingAuthorityUsername)
{    
    var i;
    
    // see if this is a clearing authority
    for (i = 0; i < NumClearingAuthority; i++)
    {
        if (ClearingAuthorityList[i].toUpperCase() == 
                ClearingAuthorityUsername.toUpperCase())
        {
            // clearing authority found, return true
            return true;
        }
    }

    // clearing authority not found, return false
    return false;
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
    var ClearingAuthority;
    
    // if this is not a simulator type or a PCATD, verify the weight and balance fields
    if (AircraftType != SimulatorAircraftType && AircraftType != PCATDAircraftType)
    {
        // verify the weight fields
        if (WBWeightOutOfRange != 0)
        {
            Response = confirm("The aircraft is too heavy by " + 
                format(WBWeightOutOfRange, 1) + " lbs." + "\n" +
                "The fuel load will be adjusted to compensate.");
            if (Response)
            {
                WBAdjustFuelWeight(WBWeightOutOfRange);
                if (WBWeightOutOfRange != 0)
                {
                    // unable to adjust the fuel load, tell the user
                    alert("Unable to remove enough fuel. The aircraft is too heavy by " +
                        format(WBWeightOutOfRange, 1) + " lbs.\n" + 
                        "Adjust the weight in the weight and balance and try again.");
		            return false;
                }
                else
                {
                    // fuel load adjusted, tell the user how much he can have
                    alert("Fuel adjusted to  " + 
                        document.getElementById('FuelGallons').value + " gals." + "\n" + 
                        "Aircraft weight is now less than gross weight.");
                }
            }
            else
            {
                // don't let the aircraft be checked out with too much weight
		        return false;
            }
        }

        // verify the balance fields
        if (WBCGOutOfRange < 0)
        {
            alert("The aircraft CG is too far forward by " + 
                format(Math.abs(WBCGOutOfRange), 3) + " inches.\n" + 
                "You must correct the weight and balance before continuing.");
		    return false;
        }
        else if (WBCGOutOfRange > 0)
        {
            alert("The aircraft CG is too far aft by " +
                format(Math.abs(WBCGOutOfRange), 3) + " inches.\n" +
                "You must correct the weight and balance before continuing.");
		    return false;
        }
        
        // check to be sure that they entered at least the pilots weight in the
        // weight and balance
        if (document.getElementById('Front_SeatWeight').value == 0)
        {
            // no weight entered in the weight and balance, give them another chance
            alert("You must enter at least the pilot's weight in the weight and balance sheet.");
                
            // set the focus to front seat weight entry of the weight and balance
            document.getElementById('Front_SeatWeight').focus();
            document.getElementById('Front_SeatWeight').select();
		    return false;
        }

    }
    
    // get the clearing authority if one is entered
    if (document.getElementById('ClearingKeyCode'))
    {
        // clearing authority is required, get the value
        ClearingAuthority = document.getElementById('ClearingKeyCode').value;
    }
    else
    {
        // clearing authority is not required
        ClearingAuthority = "";
    }
    
    // verify the user flight status
    switch (FlightStatus)
    {
    case ClearedToFly:
        // cleared, continue
        break;
    case ClearedToFlyNoInstruments:
        // not cleared for instruments but cleared for VFR, continue
        break;
    case ClearedToFlyDayOnly:
        // cleared for day flight only, continue
        break;
    case NotClearedToFly:
        // not cleared no overrides allowed, tell the user and exit
        alert("You are not cleared for flight (no overrides are allowed).\n" + 
                    "See the manager to clear the grounding condition.");
		return false;
        break;
    case NotClearedToFlyOverride:
        // not cleared, overrides allowed, if we have a valid clearing authority keycode, continue
        // otherwise tell the user and exit
        if (ClearingAuthority.length == 0)
        {
            // keycode was not entered, but is required
            alert("A clearing authority username is required to clear the flight.");
            document.getElementById('ClearingKeyCode').focus();
            document.getElementById('ClearingKeyCode').select();
		    return false;
		}
        else
        {
            // a username was entered, be sure that it is a valid clearing authority
            
            // is this a valid clearing authority?
            if (!IsClearingAuthorityKeyCode(ClearingAuthority))
            {
                // invalid user entered, tell the user
                alert(ClearingAuthority.toUpperCase() + 
                            " is not a valid clearing authority username.");
                document.getElementById('ClearingKeyCode').focus();
                document.getElementById('ClearingKeyCode').select();
		        return false;
            }
            
            // make sure that a clearing authority is not attempting to clear for themselves
            if (CurrentUserName.toUpperCase() == ClearingAuthority.toUpperCase())
            {
                // invalid user entered, tell the user
                alert(ClearingAuthority.toUpperCase() + " is your username.\n" + 
                                    "Your cannot clear a flight for yourself.");
                document.getElementById('ClearingKeyCode').focus();
                document.getElementById('ClearingKeyCode').select();
		        return false;
            }
        }
        break;
    }

    // no errors found, return
	return true;
}

//-->
</script>
