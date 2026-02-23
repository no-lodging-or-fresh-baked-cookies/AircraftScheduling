<?php
//-----------------------------------------------------------------------------
// 
// DisplayMemberCharges.php
// 
// PURPOSE: Displays the member charges for the given month.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model resources
//      InstructorResource = $rdata["InstructorResource"];
//      ChargesName - member name to display charges for
//      MonthName - name of the month to display charges for
//      AllowModifications - set to true to allow modifications
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      debug_flag - set to 1 to enable debug information
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
    global $DatabaseNameFormat;
    
    // initialize variables
    $sql = '';
    $InstructorResource = "";
    $make = "";
    $model = "";
    $goback = "";
    $GoBackParameters = "";
    $ChargesName = "";
    $MonthName = "";
    $debug_flag = 0;
    $AllowModifications = false;
    
    // build the query
    $FlightQuery = "SELECT " .
            			"$DatabaseNameFormat, " .       // 0
            			"Date, " .                      // 1
            			"Aircraft, " .                  // 2
            			"model, " .                     // 3
            			"Begin_Hobbs, " .               // 4
            			"End_Hobbs, " .                 // 5
            			"Begin_Tach, " .                // 6
            			"End_Tach, " .                  // 7
            			"Day_Time, " .                  // 8
            			"Night_Time, " .                // 9
            			"Day_Landings, " .              // 10
            			"Night_Landings, " .            // 11
            			"Cleared_By, " .                // 12
            			"Dual_Time, " .                 // 13
            			"Dual_PP_Time, " .              // 14
            			"Instructor_Keycode, " .        // 15
            			"Instruction_Type , " .         // 16
            			"Holding_Procedures, " .        // 17
            			"Navigation_Intercepts , " .    // 18
            			"Instrument_Approach , " .      // 19
            			"Local_Fuel, " .                // 20
            			"Cross_Country_Fuel , " .       // 21
            			"Cross_Country_Fuel_Credit, " . // 22
            			"Oil, " .                       // 23
                        "Hobbs_Elapsed,  " .            // 24
                        "Aircraft_Rate,   " .           // 25
                        "Aircraft_Cost,  " .            // 26
                        "Instruction_Rate,  " .         // 27
                        "Instructor_Charge,  " .        // 28
                        "Fuel_Cost,  " .                // 29
                        "Local_Fuel_Cost,  " .          // 30
                        "Oil_Rate,  " .                 // 31
                        "Oil_Cost, " .                  // 32
                        "a.model_id, " .                // 33
                        "Student_Keycode, " .           // 34
                        "Keycode ";                     // 35
        
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
    if(isset($rdata["ChargesName"])) $ChargesName = $rdata["ChargesName"];
    if(isset($rdata["MonthName"])) $MonthName = $rdata["MonthName"];
    if(isset($rdata["AllowModifications"])) $AllowModifications = $rdata["AllowModifications"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    //********************************************************************
    // BuildFlightParameters($FlightRST)
    //
    // Purpose: Build the parameters to modify the given flight. If the
    //          flight is an instruction flight, the information for 
    //          the students flight will be loaded.
    //
    // Inputs:
    //   FlightRST - flight information from the database
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   The parameters for modifying the flight.
    //*********************************************************************
    function BuildFlightParameters($FlightRST)
    {
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        global $FlightQuery;
	    global $_SERVER;

		// offset into the flight database record
		$NameOffset = 0;
		$DateOffset = 1;
		$AircraftOffset = 2;
		$modelOffset = 3;
		$Begin_HobbsOffset = 4;
		$End_HobbsOffset = 5;
		$Begin_TachOffset = 6;
		$End_TachOffset = 7;
		$Day_TimeOffset = 8;
		$Night_TimeOffset = 9;
		$Day_LandingsOffset = 10;
		$Night_LandingsOffset = 11;
		$Cleared_ByOffset = 12;
		$Dual_TimeOffset = 13;
		$Dual_PP_TimeOffset = 14;
		$Instructor_KeycodeOffset = 15;
		$Instruction_TypeOffset = 16;
		$Holding_ProceduresOffset = 17;
		$Navigation_InterceptsOffset = 18;
		$Instrument_ApproachOffset = 19;
		$Local_FuelOffset = 20;
		$Cross_Country_FuelOffset = 21;
		$Cross_Country_Fuel_CreditOffset = 22;
		$OilOffset = 23;
        $Hobbs_ElapsedOffset = 24;
        $Aircraft_RateOffset = 25;
        $Aircraft_CostOffset = 26;
        $Instruction_RateOffset = 27;
        $Instructor_ChargeOffset = 28;
        $Fuel_CostOffset = 29;
        $Local_Fuel_CostOffset = 30;
        $Oil_RateOffset = 31;
        $Oil_CostOffset = 32;
        $model_idOffset = 33;
        $Student_KeycodeOffset = 34;
        $KeycodeOffset = 35;
        
        // is this an instructional flight?
        if ($FlightRST[$Instruction_TypeOffset] == $InstructorInstruction)
        {
            // instructional flight, load the student flight information
            $sql = $FlightQuery . 
                                    "FROM " .
                            			"Flight a, " .
                            			"AircraftScheduling_person b, " .
                            			"AircraftScheduling_model c " .
                            		"WHERE " .
                            			"a.model_id=c.model_id AND " .
                            			"a.keycode=b.username AND " .
                                        "Keycode = '$FlightRST[$Student_KeycodeOffset]' AND " .
                                        "Instructor_Keycode = '$FlightRST[$KeycodeOffset]' AND " .
                                        "Date = '$FlightRST[$DateOffset]' AND " .
                                        "Aircraft = '$FlightRST[$AircraftOffset]' AND " .
                                        "a.model_id = $FlightRST[$model_idOffset] AND " .
                                        "ROUND(Dual_Time, 1) = " . 
                                                    RoundToDecimalPlaces($FlightRST[$Dual_TimeOffset], 1) . " AND " .
                                        "ROUND(Dual_PP_Time, 1) = " . 
                                                    RoundToDecimalPlaces($FlightRST[$Dual_PP_TimeOffset], 1) . " AND " .
                                        "ROUND(Hobbs_Elapsed, 1) = " . 
                                                    RoundToDecimalPlaces($FlightRST[$Hobbs_ElapsedOffset], 1);

        	$SQLResult = sql_query($sql);
    	
        	// display the recent flight experience
        	if ($SQLResult)
        	{
             	$StudentFlightRST = sql_row($SQLResult, 0);
             	$Flightday = date("d", strtotime($StudentFlightRST[$DateOffset]));
            	$Flightmonth = date("m", strtotime($StudentFlightRST[$DateOffset]));
            	$Flightyear  = date("Y", strtotime($StudentFlightRST[$DateOffset]));
                $ModifyParameterList = 
                            "?AddModify=Modify" .
                            "&NameOfUser=$StudentFlightRST[$NameOffset]" .
                            "&TailNumber=$StudentFlightRST[$AircraftOffset]" .
                            "&AircraftModel=$StudentFlightRST[$modelOffset]" .
                            "&Flightday=$Flightday" .
                            "&Flightmonth=$Flightmonth" .
                            "&Flightyear=$Flightyear" .
                            "&ClearingAuthority=$StudentFlightRST[$Cleared_ByOffset]" .
                            "&BeginningHobbs=$StudentFlightRST[$Begin_HobbsOffset]" .
                            "&EndingHobbs=$StudentFlightRST[$End_HobbsOffset]" .
                            "&BeginningTach=$StudentFlightRST[$Begin_TachOffset]" .
                            "&EndingTach=$StudentFlightRST[$End_TachOffset]" .
                            "&InstDualTime=$StudentFlightRST[$Dual_TimeOffset]" .
                            "&InstPAndP=$StudentFlightRST[$Dual_PP_TimeOffset]" .
                            "&InstKeycode=$StudentFlightRST[$Instructor_KeycodeOffset]" .
                            "&InstType=$StudentFlightRST[$Instruction_TypeOffset]" .
                            "&FlightTimeDay=$StudentFlightRST[$Day_TimeOffset]" .
                            "&FlightTimeNight=$StudentFlightRST[$Night_TimeOffset]" .
                            "&FlightTimeHolds=$StudentFlightRST[$Holding_ProceduresOffset]" .
                            "&FlightTimeNav=$StudentFlightRST[$Navigation_InterceptsOffset]" .
                            "&FlightTimeInstApp=$StudentFlightRST[$Instrument_ApproachOffset]" .
                            "&FlightTimeDayLndg=$StudentFlightRST[$Day_LandingsOffset]" .
                            "&FlightTimeNightLndg=$StudentFlightRST[$Night_LandingsOffset]" .
                            "&AircraftLocalFuel=$StudentFlightRST[$Local_FuelOffset]" .
                            "&AircraftXCntryFuel=$StudentFlightRST[$Cross_Country_FuelOffset]" .
                            "&AircraftXCntryFuelCost=$StudentFlightRST[$Cross_Country_Fuel_CreditOffset]" .
                            "&AircraftOil=$StudentFlightRST[$OilOffset]" .
                            "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
        	}
        	else
        	{
                // error processing database request, tell the user
                DisplayDatabaseError("BuildFlightParameters", $sql);
        	}
        }
        else
        {
            // student flight or standalone flight
         	$Flightday = date("d", strtotime($FlightRST[$DateOffset]));
        	$Flightmonth = date("m", strtotime($FlightRST[$DateOffset]));
        	$Flightyear  = date("Y", strtotime($FlightRST[$DateOffset]));
            $ModifyParameterList = 
                        "?AddModify=Modify" .
                        "&NameOfUser=$FlightRST[$NameOffset]" .
                        "&TailNumber=$FlightRST[$AircraftOffset]" .
                        "&AircraftModel=$FlightRST[$modelOffset]" .
                        "&Flightday=$Flightday" .
                        "&Flightmonth=$Flightmonth" .
                        "&Flightyear=$Flightyear" .
                        "&ClearingAuthority=$FlightRST[$Cleared_ByOffset]" .
                        "&BeginningHobbs=$FlightRST[$Begin_HobbsOffset]" .
                        "&EndingHobbs=$FlightRST[$End_HobbsOffset]" .
                        "&BeginningTach=$FlightRST[$Begin_TachOffset]" .
                        "&EndingTach=$FlightRST[$End_TachOffset]" .
                        "&InstDualTime=$FlightRST[$Dual_TimeOffset]" .
                        "&InstPAndP=$FlightRST[$Dual_PP_TimeOffset]" .
                        "&InstKeycode=$FlightRST[$Instructor_KeycodeOffset]" .
                        "&InstType=$FlightRST[$Instruction_TypeOffset]" .
                        "&FlightTimeDay=$FlightRST[$Day_TimeOffset]" .
                        "&FlightTimeNight=$FlightRST[$Night_TimeOffset]" .
                        "&FlightTimeHolds=$FlightRST[$Holding_ProceduresOffset]" .
                        "&FlightTimeNav=$FlightRST[$Navigation_InterceptsOffset]" .
                        "&FlightTimeInstApp=$FlightRST[$Instrument_ApproachOffset]" .
                        "&FlightTimeDayLndg=$FlightRST[$Day_LandingsOffset]" .
                        "&FlightTimeNightLndg=$FlightRST[$Night_LandingsOffset]" .
                        "&AircraftLocalFuel=$FlightRST[$Local_FuelOffset]" .
                        "&AircraftXCntryFuel=$FlightRST[$Cross_Country_FuelOffset]" .
                        "&AircraftXCntryFuelCost=$FlightRST[$Cross_Country_Fuel_CreditOffset]" .
                        "&AircraftOil=$FlightRST[$OilOffset]" .
                        "&goback=" . GetScriptName() .
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
        }
        
        // returnt the result
        return $ModifyParameterList;
    }                                
    
    //********************************************************************
    // LoadFlightCharges(
    //                           $PilotName As String,
    //                           $MonthName as String)
    //
    // Purpose:      Load the recent flight charge info into the form
    //
    // Inputs:
    //   PilotName - member we are interested in
    //   MonthName - month (full name) that we will display charges for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   The total of the flight charges.
    //*********************************************************************
    function LoadFlightCharges($PilotName, $MonthName)
    {
        global $debug_flag;
        global $AllowModifications;
        global $FlightQuery;
        global $ChargesName;
	    global $_SERVER;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        
        // get the keycode from the database record
        $CurrentUserKeycode = LookupUserName($PilotName);
        if ($debug_flag)
            echo "DEBUG: CurrentUserKeycode: $CurrentUserKeycode<BR>";
        
        // get the number of the month
        $UserMonth = date("m", strtotime("$MonthName 1, 2000")) - 1;
        
        // we want to filter from the first day of the month to the last
        // determine the next month so we can get the last day of this month
        $NextMonth = $UserMonth + 1;
        
        // if the billing month is greater than the current month assume they mean
        // last year
        If ($UserMonth + 1 > Month("Now"))
            $BillingYear = Year("Now") - 1;
        Else
            $BillingYear = Year("Now");
        
        // compute the start and end dates
        $StartDate = FormatField(DateSerial($BillingYear, $UserMonth + 1, 1), "DatabaseDate");
        $EndDate = FormatField(DateSerial($BillingYear, $NextMonth + 1, 0), "DatabaseDate");
        if ($debug_flag)
            echo "DEBUG: StartDate: $StartDate EndDate: $EndDate<BR>";
        
        // add the totals of the recent flight experience
        $TotalTime = 0;
        $TotalAircraftCost = 0;
        $TotalDualTime = 0;
        $TotalPPTime = 0;
        $TotalInstructorCost = 0;
        $TotalLocalFuelCost = 0;
        $TotalCrossCountryFuelCredit = 0;
        $TotalOilCost = 0;
    
        // get the recent flight experience
		$SQLQuery = $FlightQuery . 
                                "FROM " .
                        			"Flight a, " .
                        			"AircraftScheduling_person b, " .
                        			"AircraftScheduling_model c " .
                        		"WHERE " .
                        			"a.model_id=c.model_id AND " .
                        			"a.keycode=b.username AND " .
                                    "(a.Date >= '$StartDate') AND " .
                                    "(a.Date <= '$EndDate') AND  " .
                                    "(a.Keycode = '$CurrentUserKeycode')  " .
                                "ORDER BY Date";
    	$SQLResult = sql_query($SQLQuery);
		if ($debug_flag)
			echo "DEBUG: SQL: " . $SQLQuery . "<BR>";
    	
    	// put in the title
    	echo "<H4>Flight Charges</H4>";
    	
    	// if modifications are allowed
        if ($AllowModifications)
        {
    	    // give them a link to add a new flight
            echo " <LI><b><a href='AddModifyFlights.php" . 
                                    "?AddModify=Add" . 
                                    "&NameOfUser=" . urlencode($ChargesName) .
                                    "&goback=" . GetScriptName() .
                                    "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                                    "'" .
                                    ">Add Flight</a></b>";
    	}
    	
    	// display the recent flight experience
    	if ($SQLResult)
    	{
    	    // valid SQL, display the information
            echo "<TABLE bgcolor='#ffffed' border=1>";
            echo "<TH>Date</TH>";
            echo "<TH>Aircraft</TH>";
            echo "<TH>Student</TH>";
            echo "<TH>Time</TH>";
            echo "<TH>Aircraft<BR>Cost</TH>";
            echo "<TH>Dual<BR>Time</TH>";
            echo "<TH>Prep<BR>Time</TH>";
            echo "<TH>Inst<BR>Cost</TH>";
            echo "<TH>Local<BR>Fuel</TH>";
            echo "<TH>Fuel<BR>Cost</TH>";
            echo "<TH>Cross<BR>Cntry</TH>";
            echo "<TH>X-Cntry<BR>Credit</TH>";
            echo "<TH>Oil</TH>";
            echo "<TH>Oil<BR>Cost</TH>";
    		for ($i = 0; ($row = sql_row($SQLResult, $i)); $i++) 
    		{

    		    // display the information
    		    echo "<TR>";
    		    
                // if we are allowing modifications
                if ($AllowModifications)
                {
    			    // build the parameter list for modifying the flight
    			    $ModifyParameterList = BuildFlightParameters($row);
    
    			    // display a link to modify the flight
    				echo "<td class=CL>";
    				echo "<a href='AddModifyFlights.php$ModifyParameterList'>" .
    				            FormatField($row[1], "Date") . "</a></td>";
				}
				else
				{
				    // not modifying the flight
        		    echo "<td class=CL>" . FormatField($row[1], "Date") . "</TD>";   // date
				}
				
				// display remainder of the fields
    		    echo "<td class=CL>" . $row[2] . "</TD>";                            // aircraft
    		    $FlightChargeName = GetNameFromUsername($row[34]);
                if ($debug_flag)
                    echo "DEBUG: Username: " . $row[34] . " FlightChargeName: $FlightChargeName " . 
                            "ChargesName: $ChargesName<BR>";
    		    if (strtoupper($ChargesName) == strtoupper($FlightChargeName) ||
    		        $FlightChargeName == -1)       // student
        		    echo "<td class=CL>none</TD>";
    		    else
        		    echo "<td class=CL>" . $FlightChargeName . "</TD>";
    		    echo "<td class=CR>" . FormatField($row[24], "Float") . "</TD>";     // time
    		    echo "<td class=CR>" . FormatField($row[26], "Currency") . "</TD>";  // aircraft cost
    		    echo "<td class=CR>" . FormatField($row[13], "Float") . "</TD>";     // dual time
    		    echo "<td class=CR>" . FormatField($row[14], "Float") . "</TD>";     // PP time
    		    echo "<td class=CR>" . FormatField($row[28], "Currency") . "</TD>";  // inst cost
    		    echo "<td class=CR>" . FormatField($row[20], "Float") . "</TD>";     // local fuel
    		    echo "<td class=CR>" . FormatField($row[30], "Currency") . "</TD>";  // cost
    		    echo "<td class=CR>" . FormatField($row[21], "Float") . "</TD>";     // cross country
    		    echo "<td class=CR>" . FormatField($row[22], "Currency") . "</TD>";  // credit
    		    echo "<td class=CR>" . FormatField($row[23], "Integer") . "</TD>";   // oil
    		    echo "<td class=CR>" . FormatField($row[32], "Currency") . "</TD>";  // oil cost
    		    echo "</TR>";
    		    
    		    // save the totals for completing the table
                $TotalTime = $TotalTime + $row[24];
                $TotalAircraftCost = $TotalAircraftCost + $row[26];
                $TotalDualTime = $TotalDualTime + $row[13];
                $TotalPPTime = $TotalPPTime + $row[14];
                $TotalInstructorCost = $TotalInstructorCost + $row[28];
                $TotalLocalFuelCost = $TotalLocalFuelCost + $row[30];
                $TotalCrossCountryFuelCredit = $TotalCrossCountryFuelCredit + $row[22];
                $TotalOilCost = $TotalOilCost + $row[32];
    		}
            
            // put the totals into the screen
    	    echo "<TR>";
    	    echo "<TD colspan='3' class=CL>Totals</TD>";
    	    echo "<TD class=CR>" . FormatField($TotalTime, "Float") . "</TD>";      // time
    	    echo "<TD class=CR>" . FormatField($TotalAircraftCost, "Currency") . "</TD>";  // aircraft cost
    	    echo "<TD class=CR>" . FormatField($TotalDualTime, "Float") . "</TD>";    // dual time
    	    echo "<TD class=CR>" . FormatField($TotalPPTime, "Float") . "</TD>";   // PP time
    	    echo "<TD class=CR>" . FormatField($TotalInstructorCost, "Currency") . "</TD>"; // inst cost
    	    echo "<TD class=CR><BR></TD>";                                      // local fuel
    	    echo "<TD class=CR>" . FormatField($TotalLocalFuelCost, "Currency") . "</TD>"; // fuel cost
    	    echo "<TD class=CR><BR></TD>";                                      // cross country
    	    echo "<TD class=CR>" . FormatField($TotalCrossCountryFuelCredit, "Currency") . "</TD>"; // credit
    	    echo "<TD class=CR><BR></TD>";                                      // oil
    	    echo "<TD class=CR>" . FormatField($TotalOilCost, "Currency") . "</TD>";    // oil cost
    	    echo "</TR>";
    
            echo "</TABLE>";
            
            // compute the total of the charges
            $TotalFlightCharges = 
                                $TotalAircraftCost +
                                $TotalInstructorCost +
                                $TotalLocalFuelCost +
                                $TotalCrossCountryFuelCredit +
                                $TotalOilCost;
    	}
    	else
    	{
    	    // sql error, tell the user
    	    fatal_error(0, sql_error());
    	    $TotalFlightCharges = 0;
    	}
    	
    	// return the total of the charges
    	return $TotalFlightCharges;
    }
    
    //********************************************************************
    // LoadCharges(
    //                           $PilotName As String,
    //                           $MonthName as String)
    //
    // Purpose:      Load the recent charge info into the form
    //
    // Inputs:
    //   PilotName - member we are interested in
    //   MonthName - month (full name) that we will display charges for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   The total of the flight charges.
    //*********************************************************************
    function LoadCharges($PilotName, $MonthName)
    {
        global $debug_flag;
        global $AllowModifications;
        global $ChargesName;
	    global $_SERVER;
        
        // get the keycode from the database record
        $CurrentUserKeycode = LookupUserName($PilotName);
        if ($debug_flag)
            echo "DEBUG: CurrentUserKeycode: $CurrentUserKeycode<BR>";
        
        // get the number of the month
        $UserMonth = date("m", strtotime("$MonthName 1, 2000")) - 1;
        
        // we want to filter from the first day of the month to the last
        // determine the next month so we can get the last day of this month
        $NextMonth = $UserMonth + 1;
        
        // if the billing month is greater than the current month assume they mean
        // last year
        If ($UserMonth + 1 > Month("Now"))
            $BillingYear = Year("Now") - 1;
        Else
            $BillingYear = Year("Now");
        
        // compute the start and end dates
        $StartDate = FormatField(DateSerial($BillingYear, $UserMonth + 1, 1), "DatabaseDate");
        $EndDate = FormatField(DateSerial($BillingYear, $NextMonth + 1, 0), "DatabaseDate");
        if ($debug_flag)
            echo "DEBUG: StartDate: $StartDate EndDate: $EndDate<BR>";
    
        // build the query
        $ChargeQuery = 
                        "SELECT " .
                        "    Date, " .              // 0
                        "    Part_Number, " .       // 1
                        "    Part_Description, " .  // 2
                        "    Quantity, " .          // 3
                        "    Price, " .             // 4
                        "    Total_Price, " .       // 5 
                        "    Unit_Price, " .        // 6 
                        "    Category " .           // 7 
                        "FROM Charges  " .
                        "WHERE (Charges.Date >= '$StartDate') AND  " .
                        "      (Charges.Date <= '$EndDate') AND  " .
                        "      (Charges.Keycode = '$CurrentUserKeycode')  " .
                        "ORDER BY Date";
        
        // add the totals of the recent flight experience
        $TotalPrice = 0;
    
        // get the recent charge information
    	$SQLResult = sql_query($ChargeQuery);
    	
    	// put in the title
    	echo "<H4>Charges</H4>";
    	
    	// if we are modifications are allowed
        if ($AllowModifications)
        {
    	    // give them a link to add a new charge
            echo " <LI><b><a href='AddModifyCharges.php" . 
                                    "?AddModify=Add" . 
                                    "&NameOfUser=" . urlencode($ChargesName) .
                                    "&goback=" . GetScriptName() .
                                    "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                                    "'" .
                                    ">Add Charge</a></b>";
    	}
    	
    	// display the recent charges
    	if ($SQLResult)
    	{
    	    // valid SQL, display the information
            echo "<TABLE bgcolor='#ffffed' border=1>";
            echo "<TH>Date</TH>";
            echo "<TH>Part<BR>Number</TH>";
            echo "<TH>Part Description</TH>";
            echo "<TH>Quantity</TH>";
            echo "<TH>Price</TH>";
            echo "<TH>Total<BR>Price</TH>";
    		for ($i = 0; ($row = sql_row($SQLResult, $i)); $i++) 
    		{
    		    // display the information
    		    echo "<TR>";
    		    
                // if we are allowing modifications
                if ($AllowModifications)
                {
        			// build the parameter list for modifying the charge
                 	$Chargeday = date("d", strtotime($row[0]));
                	$Chargemonth = date("m", strtotime($row[0]));
                	$Chargeyear  = date("Y", strtotime($row[0]));
                    $ModifyParameterList = 
                                "?AddModify=Modify" .
                                "&NameOfUser=" . urlencode($ChargesName) .
                                "&Chargeday=$Chargeday" .
                                "&Chargemonth=$Chargemonth" .
                                "&Chargeyear=$Chargeyear" .
                                "&PartNumber=" . urlencode($row[1]) .
                                "&PartDescription=" . urlencode($row[2]) . 
                                "&Quantity=$row[3]" .
                                "&Price=$row[4]" .
                                "&TotalPrice=$row[5]" .
                                "&UnitPrice=$row[6]" .
                                "&Category=" . urlencode($row[7]) .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
    
    			    // display a link to modify the charge
    				echo "<td class=CL>";
    				echo "<a href='AddModifyCharges.php$ModifyParameterList'>" .
    				            FormatField($row[0], "Date") . "</a></td>";
				}
				else
				{
				    // not modifying the flight
        		    echo "<td class=CL>" . FormatField($row[0], "Date") . "</TD>";   // date
				}

    		    echo "<TD class=CL>" . $row[1] . "</TD>";                          // part number
    		    echo "<TD class=CL>" . $row[2] . "</TD>";                          // part description
    		    echo "<TD class=CR>" . FormatField($row[3], "Integer") . "</TD>";  // quantity
    		    echo "<TD class=CR>" . FormatField($row[4], "Currency") . "</TD>"; // price
    		    echo "<TD class=CR>" . FormatField($row[5], "Currency") . "</TD>"; // total price
    		    echo "</TR>";
    		    
    		    // save the totals for completing the table
                $TotalPrice = $TotalPrice + $row[5];
    		}
            
            // put the totals into the screen
    	    echo "<TR>";
    	    echo "<TD colspan='5' class=CL>Totals</TD>";
    	    echo "<TD class=CR>" . FormatField($TotalPrice, "Currency") . "</TD>";    // total price
    	    echo "</TR>";
    
            echo "</TABLE>";
            
            // compute the total of the charges
            $TotalCharges = $TotalPrice;
    	}
    	else
    	{
    	    // sql error, tell the user
    	    fatal_error(0, sql_error());
    	    $TotalCharges = 0;
    	}
    	
    	// return the total of the charges
    	return $TotalCharges;
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
    
    // if the month name was not set, set it
    if (empty($MonthName))
    {
        // set the month name
        $MonthName = date("F");
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
    
    # This page will display the charges
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
        
    // get the name of the user from the login name if the requested user
    // was not passed in
    if (empty($ChargesName))
    {
        $NameOfUser = getName();
        $ChargesName = $NameOfUser;
    }
    else
    {
        $NameOfUser = $ChargesName;
    }
    
    if ($debug_flag)
        echo "DEBUG: ChargesName: $ChargesName NameOfUser: $NameOfUser<br>";
    
    echo "<FORM NAME='main'>";
    
    echo "<TABLE BORDER=0>";
    
    // if the user is a normal user, put in the user name for the charges fields
    if (authGetUserLevel(getUserName(), $auth["admin"]) <= $UserLevelNormal)
    {
    	echo "<TR><TD CLASS=CR><B>" . $lang["ChargesName"] . "</B></TD>";
    	echo "<TD CLASS=CL>" . htmlentities($NameOfUser) . "</TD></TR>";
    	echo "<INPUT TYPE=HIDDEN NAME='NameOfUser' VALUE='" . $NameOfUser . "'>";
    }
    else
    {
    	// admin or super user let them pick the person
    	echo "<TR><TD CLASS=CR><B>" . $lang["ChargesName"] . "</B></TD>";
    	
  		// build the selection entries
    	echo "<TD CLASS=CL>";
        BuildMemberSelector(
                            $NameOfUser, 
                            false, 
                            "",
                            20,
                            true,
                            false,
                            "DisplayCharges");    	
    	echo "<TD CLASS=CL>";
    }
    
    // put up the list of months to pick for charges
    echo "<TR><TD CLASS=CR><B>" . $lang["ChargesMonths"] . "</B></TD>";
    
    echo "<TD CLASS=CL>";
    $MonthList = array(
                        "January", 
                        "February", 
                        "March", 
                        "April", 
                        "May", 
                        "June", 
                        "July", 
                        "August", 
                        "September", 
                        "October", 
                        "November", 
                        "December");
    echo "<SELECT NAME='MonthName' onChange=DisplayCharges()>";
    
    // build the selection entries
    foreach ($MonthList as $Item)
    {
    	echo "<OPTION " .
    			"VALUE='" . $Item . "'" . 
    			($Item == $MonthName ? " SELECTED" : "") . 
    			">$Item";
    }
    echo "</SELECT>";	
    echo "</TD></TR>";
    echo "<TD CLASS=CL>";
    
    // setup script to display charges when the name changes
    echo "<SCRIPT LANGUAGE='JavaScript'>";
    echo "function DisplayCharges()";
    echo "{";
    	
    // reload this scipt with the new name selected
    echo "	window.location.href = \"" . $_SERVER["PHP_SELF"] . 
    		"?ChargesName=\" + document.forms['main'].NameOfUser.value + " . 
    		"\"&MonthName=\" + document.forms['main'].MonthName.value + " . 
    		"\"&AllowModifications=$AllowModifications\" + " . 
    		"\"&goback=$goback&GoBackParameters=$GoBackParameters\";";
    echo "}";
    echo "</SCRIPT>";
    
    echo "</TABLE>";
    echo "</FORM>";
    
    // display the flight charge information
    $TotalFlightCharges = LoadFlightCharges($NameOfUser, $MonthName);
    
    // display the charge information
    $TotalCharges = LoadCharges($NameOfUser, $MonthName);
    
    // display the total charges for the month
    echo "<BR>";
    
    echo "<TABLE bgcolor='#ffffed' border=0>";
    echo "<TR>";
    echo "<TD class=CR><FONT size='+1'>Total Flight Charges:</FONT></TD>";
    echo "<TD class=CR><FONT size='+1'>" . FormatField($TotalFlightCharges, "Currency") . "</FONT></TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD class=CR><FONT size='+1'>Total Misc Charges:</FONT></TD>";
    echo "<TD class=CR><FONT size='+1'>" . FormatField($TotalCharges, "Currency") . "</FONT></TD>";
    echo "</TR>";
    echo "<TR>";
    echo "<TD class=CR><FONT size='+1'>Total for Month:</FONT></TD>";
    echo "<TD class=CR><FONT size='+1'>" . FormatField($TotalCharges + $TotalFlightCharges, "Currency") . "</FONT></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    // generate return URL
    GenerateReturnURL(
                        $goback, 
                        CleanGoBackParameters($GoBackParameters));
    
    include "trailer.inc" 
?>
