<?php
//-----------------------------------------------------------------------------
// 
// AddModifyAircraft.php
// 
// PURPOSE: Displays the add or modify aircraft screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      update - set to Update Aircraft to update the given aircraft
//      delete - set to Delete Aircraft to delete the given aircraft
//      AddModify - set to modify to modify an aircraft or add to add an aircraft
//      aircraft_id - id number of the aircraft
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//
//      Database information is passed in in numerious controls.
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
    require_once("DatabaseFunctions.inc");
    require_once("DatabaseConstants.inc");

    // initialize variables
    $all = '';
    $AddModify = "Add";
    $hobbs = 0;
    $tach2 = 0;
    $min_pilot_cert = 0;
    $schedulable = "off";
    $ErrorMessage = "";

    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    $InstructorResource = "";
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    if(isset($rdata["AddModifyAircraft"])) $AddModifyAircraft = $rdata["AddModifyAircraft"];
    if(isset($rdata["AddModify"])) $AddModify = $rdata["AddModify"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["AircraftDelete"])) $AircraftDelete = $rdata["AircraftDelete"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    if(isset($rdata["schedulable"])) $schedulable = $rdata["schedulable"];
    
    // aircraft database table fields
    if(isset($rdata["aircraft_id"])) $aircraft_id = $rdata["aircraft_id"];
    if(isset($rdata["n_number"])) $n_number = $rdata["n_number"];
    if(isset($rdata["serial_number"])) $serial_number = $rdata["serial_number"];
    if(isset($rdata["panel_picture"])) $panel_picture = $rdata["panel_picture"];
    $delete_panel_picture = "";
    if(isset($rdata["delete_panel_picture"])) $delete_panel_picture = $rdata["delete_panel_picture"];
    if(isset($rdata["old_panel_picture"])) $old_panel_picture = $rdata["old_panel_picture"];
    if(isset($rdata["picture"])) $picture = $rdata["picture"];
    $delete_picture = "";
    if(isset($rdata["delete_picture"])) $delete_picture = $rdata["delete_picture"];
    if(isset($rdata["old_picture"])) $old_picture = $rdata["old_picture"];
    if(isset($rdata["hobbs"])) $hobbs = $rdata["hobbs"];
    if(isset($rdata["tach1"])) $tach1 = $rdata["tach1"];
    if(isset($rdata["tach2"])) $tach2 = $rdata["tach2"];
    if(isset($rdata["hourly_cost"])) $hourly_cost = $rdata["hourly_cost"];
    if(isset($rdata["rental_fee"])) $rental_fee = $rdata["rental_fee"];
    if(isset($rdata["empty_weight"])) $empty_weight = $rdata["empty_weight"];
    if(isset($rdata["max_gross"])) $max_gross = $rdata["max_gross"];
    if(isset($rdata["AircraftYear"])) $AircraftYear = $rdata["AircraftYear"];
    if(isset($rdata["ICAO_Equipment_Codes"])) $ICAO_Equipment_Codes = $rdata["ICAO_Equipment_Codes"];
    if(isset($rdata["ICAO_Wake_Turb"])) $ICAO_Wake_Turb = $rdata["ICAO_Wake_Turb"];
    if(isset($rdata["ICAO_Flight_Type"])) $ICAO_Flight_Type = $rdata["ICAO_Flight_Type"];
    if(isset($rdata["ICAO_ADSB_Type"])) $ICAO_ADSB_Type = $rdata["ICAO_ADSB_Type"];
    if(isset($rdata["ICAO_Transponder"])) $ICAO_Transponder = $rdata["ICAO_Transponder"];
    if(isset($rdata["ICAO_Number_Aircraft"])) $ICAO_Number_Aircraft = $rdata["ICAO_Number_Aircraft"];
    if(isset($rdata["make_id"])) $make_id = $rdata["make_id"];
    if(isset($rdata["model_id"])) $model_id = $rdata["model_id"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["description"])) $description = $rdata["description"];
    if(isset($rdata["ifr_cert"])) $ifr_cert = $rdata["ifr_cert"];
    if(isset($rdata["min_pilot_cert"])) $min_pilot_cert = $rdata["min_pilot_cert"];
    if(isset($rdata["status"])) $status = $rdata["status"];
    if(isset($rdata["Aircraft_Color"])) $Aircraft_Color = $rdata["Aircraft_Color"];
    if(isset($rdata["Hrs_Till_100_Hr"])) $Hrs_Till_100_Hr = $rdata["Hrs_Till_100_Hr"];
    if(isset($rdata["Hundred_Hr_Tach"])) $Hundred_Hr_Tach = $rdata["Hundred_Hr_Tach"];
    if(isset($rdata["Annualday"])) $Annualday = $rdata["Annualday"];
    if(isset($rdata["Annualmonth"])) $Annualmonth = $rdata["Annualmonth"];
    if(isset($rdata["Annualyear"])) $Annualyear = $rdata["Annualyear"];
    if(isset($rdata["Fuel_Arm"])) $Fuel_Arm = $rdata["Fuel_Arm"];
    if(isset($rdata["Default_Fuel_Gallons"])) $Default_Fuel_Gallons = $rdata["Default_Fuel_Gallons"];
    if(isset($rdata["Full_Fuel_Gallons"])) $Full_Fuel_Gallons = $rdata["Full_Fuel_Gallons"];
    if(isset($rdata["Front_Seat_Arm"])) $Front_Seat_Arm = $rdata["Front_Seat_Arm"];
    if(isset($rdata["Rear_Seat_1_Arm"])) $Rear_Seat_1_Arm = $rdata["Rear_Seat_1_Arm"];
    if(isset($rdata["Rear_Seat_2_Arm"])) $Rear_Seat_2_Arm = $rdata["Rear_Seat_2_Arm"];
    if(isset($rdata["Front_Seat_Weight"])) $Front_Seat_Weight = $rdata["Front_Seat_Weight"];
    if(isset($rdata["Rear_Seat_1_Weight"])) $Rear_Seat_1_Weight = $rdata["Rear_Seat_1_Weight"];
    if(isset($rdata["Rear_Seat_2_Weight"])) $Rear_Seat_2_Weight = $rdata["Rear_Seat_2_Weight"];
    if(isset($rdata["Baggage_Area_1_Arm"])) $Baggage_Area_1_Arm = $rdata["Baggage_Area_1_Arm"];
    if(isset($rdata["Baggage_Area_2_Arm"])) $Baggage_Area_2_Arm = $rdata["Baggage_Area_2_Arm"];
    if(isset($rdata["Baggage_Area_3_Arm"])) $Baggage_Area_3_Arm = $rdata["Baggage_Area_3_Arm"];
    if(isset($rdata["Baggage_Area_1_Weight"])) $Baggage_Area_1_Weight = $rdata["Baggage_Area_1_Weight"];
    if(isset($rdata["Baggage_Area_2_Weight"])) $Baggage_Area_2_Weight = $rdata["Baggage_Area_2_Weight"];
    if(isset($rdata["Baggage_Area_3_Weight"])) $Baggage_Area_3_Weight = $rdata["Baggage_Area_3_Weight"];
    if(isset($rdata["Aux_Fuel_Arm"])) $Aux_Fuel_Arm = $rdata["Aux_Fuel_Arm"];
    if(isset($rdata["Aux_Fuel_Gallons"])) $Aux_Fuel_Gallons = $rdata["Aux_Fuel_Gallons"];
    if(isset($rdata["Aircraft_Arm"])) $Aircraft_Arm = $rdata["Aircraft_Arm"];
    if(isset($rdata["Aircraft_Weight"])) $Aircraft_Weight = $rdata["Aircraft_Weight"];
    if(isset($rdata["Va_Max_Weight"])) $Va_Max_Weight = $rdata["Va_Max_Weight"];
    if(isset($rdata["Current_Hobbs"])) $Current_Hobbs = $rdata["Current_Hobbs"];
    if(isset($rdata["Current_User"])) $Current_User = $rdata["Current_User"];
    if(isset($rdata["CurrentKeycode"])) $CurrentKeycode = $rdata["CurrentKeycode"];
    if(isset($rdata["Aircraft_Owner_Name"])) $Aircraft_Owner_Name = $rdata["Aircraft_Owner_Name"];
    if(isset($rdata["Aircraft_Owner_Address"])) $Aircraft_Owner_Address = $rdata["Aircraft_Owner_Address"];
    if(isset($rdata["Aircraft_Owner_City"])) $Aircraft_Owner_City = $rdata["Aircraft_Owner_City"];
    if(isset($rdata["Aircraft_Owner_State"])) $Aircraft_Owner_State = $rdata["Aircraft_Owner_State"];
    if(isset($rdata["Aircraft_Owner_Zip"])) $Aircraft_Owner_Zip = $rdata["Aircraft_Owner_Zip"];
    if(isset($rdata["Aircraft_Owner_Contract"])) $Aircraft_Owner_Contract = $rdata["Aircraft_Owner_Contract"];
    if(isset($rdata["Aircraft_Owner_Phone1"])) $Aircraft_Owner_Phone1 = $rdata["Aircraft_Owner_Phone1"];
    if(isset($rdata["Aircraft_Owner_Phone2"])) $Aircraft_Owner_Phone2 = $rdata["Aircraft_Owner_Phone2"];
    if(isset($rdata["Aircraft_Remarks"])) $Aircraft_Remarks = $rdata["Aircraft_Remarks"];
    if(isset($rdata["Aircraft_Airspeed"])) $Aircraft_Airspeed = $rdata["Aircraft_Airspeed"];
    if(isset($rdata["Flight_ID"])) $Flight_ID = $rdata["Flight_ID"];
    if(isset($rdata["Oil_Type"])) $Oil_Type = $rdata["Oil_Type"];
    if(isset($rdata["Cleared_By"])) $Cleared_By = $rdata["Cleared_By"];
    if(isset($rdata["TrackSquawks"])) $TrackSquawks = $rdata["TrackSquawks"];

    // weight and balance fields
    if(isset($rdata["AircraftWeight0"])) $AircraftWeight0 = $rdata["AircraftWeight0"];
    if(isset($rdata["ForeCG0"])) $ForeCG0 = $rdata["ForeCG0"];
    if(isset($rdata["AftCG0"])) $AftCG0 = $rdata["AftCG0"];
    if(isset($rdata["AircraftWeight1"])) $AircraftWeight1 = $rdata["AircraftWeight1"];
    if(isset($rdata["ForeCG1"])) $ForeCG1 = $rdata["ForeCG1"];
    if(isset($rdata["AftCG1"])) $AftCG1 = $rdata["AftCG1"];
    if(isset($rdata["AircraftWeight2"])) $AircraftWeight2 = $rdata["AircraftWeight2"];
    if(isset($rdata["ForeCG2"])) $ForeCG2 = $rdata["ForeCG2"];
    if(isset($rdata["AftCG2"])) $AftCG2 = $rdata["AftCG2"];
    if(isset($rdata["AircraftWeight3"])) $AircraftWeight3 = $rdata["AircraftWeight3"];
    if(isset($rdata["ForeCG3"])) $ForeCG3 = $rdata["ForeCG3"];
    if(isset($rdata["AftCG3"])) $AftCG3 = $rdata["AftCG3"];
    if(isset($rdata["AircraftWeight4"])) $AircraftWeight4 = $rdata["AircraftWeight4"];
    if(isset($rdata["ForeCG4"])) $ForeCG4 = $rdata["ForeCG4"];
    if(isset($rdata["AftCG4"])) $AftCG4 = $rdata["AftCG4"];
    if(isset($rdata["AircraftWeight5"])) $AircraftWeight5 = $rdata["AircraftWeight5"];
    if(isset($rdata["ForeCG5"])) $ForeCG5 = $rdata["ForeCG5"];
    if(isset($rdata["AftCG5"])) $AftCG5 = $rdata["AftCG5"];
            
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // BuildICAOWakeTurbSelector($ICAOWakeTurb)
    //
    // Purpose: Display a selector for the list of ICAO wake turbulence codes.
    //
    // Inputs:
    //   ICAOWakeTurb - currently selected wake turbulence code
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildICAOWakeTurbSelector($ICAOWakeTurb)
    {
		 // build the select HTML	
		echo "<SELECT NAME='ICAO_Wake_Turb' id='ICAO_Wake_Turb'>";
		
		// build the selection entries
		echo "<OPTION VALUE='L'" . 
				("L" == $ICAOWakeTurb ? " SELECTED" : "") . 
				">L(up to 15,400)";
		echo "<OPTION VALUE='M'" . 
				("M" == $ICAOWakeTurb ? " SELECTED" : "") . 
				">M(up to 299,400)";
		echo "<OPTION VALUE='H'" . 
				("H" == $ICAOWakeTurb ? " SELECTED" : "") . 
				">H(over 299,400)";
		echo "</SELECT>";	
     }
    
    //********************************************************************
    // BuildICAOFlightTypeSelector($ICAOFlightType)
    //
    // Purpose: Display a selector for the list of ICAO flight type.
    //
    // Inputs:
    //   ICAOFlightType - currently selected flight type
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildICAOFlightTypeSelector($ICAOFlightType)
    {
		 // build the select HTML	
		echo "<SELECT NAME='ICAO_Flight_Type' id='ICAO_Flight_Type'>";
		
		// build the selection entries
		echo "<OPTION VALUE='G'" . 
				("G" == $ICAOFlightType ? " SELECTED" : "") . 
				">General Aviation";
		echo "<OPTION VALUE='S'" . 
				("S" == $ICAOFlightType ? " SELECTED" : "") . 
				">Scheduled ATS";
		echo "<OPTION VALUE='N'" . 
				("N" == $ICAOFlightType ? " SELECTED" : "") . 
				">Non-Scheduled ATS";
		echo "<OPTION VALUE='M'" . 
				("M" == $ICAOFlightType ? " SELECTED" : "") . 
				">Military";
		echo "<OPTION VALUE='X'" . 
				("X" == $ICAOFlightType ? " SELECTED" : "") . 
				">Other";
		echo "</SELECT>";	
     }
    
    //********************************************************************
    // BuildICAOADSBCodeSelector($ICAOADSBCode)
    //
    // Purpose: Display a selector for the list of ICAO ADS-B types.
    //
    // Inputs:
    //   ICAOADSBCode - currently selected ADS-B type
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildICAOADSBCodeSelector($ICAOADSBCode)
    {
		 // build the select HTML	
		echo "<SELECT NAME='ICAO_ADSB_Type' id='ICAO_ADSB_Type'>";
		
		// build the selection entries
		echo "<OPTION VALUE='N'" . 
				("N" == $ICAOADSBCode ? " SELECTED" : "") . 
				">None";
		echo "<OPTION VALUE='B1'" . 
				("B1" == $ICAOADSBCode ? " SELECTED" : "") . 
				">ADS-B, 1090 Out";
		echo "<OPTION VALUE='B2'" . 
				("B2" == $ICAOADSBCode ? " SELECTED" : "") . 
				">ADS-B, 1090 In/Out";
		echo "<OPTION VALUE='U1'" . 
				("U1" == $ICAOADSBCode ? " SELECTED" : "") . 
				">ADS-B, UAT Out";
		echo "<OPTION VALUE='U2'" . 
				("U2" == $ICAOADSBCode ? " SELECTED" : "") . 
				">ADS-B, UAT In/Out";
		echo "<OPTION VALUE='V1'" . 
				("V1" == $ICAOADSBCode ? " SELECTED" : "") . 
				">ADS-B VDL 4 Out";
		echo "<OPTION VALUE='V2'" . 
				("V2" == $ICAOADSBCode ? " SELECTED" : "") . 
				">ADS-B VDL 4 In/Out";
		echo "</SELECT>";	
     }
    
    //********************************************************************
    // BuildICAOTransponderSelector($ICAOTransponder)
    //
    // Purpose: Display a selector for the list of ICAO transponder types.
    //
    // Inputs:
    //   ICAOTransponder - currently selected transponder type
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildICAOTransponderSelector($ICAOTransponder)
    {
		 // build the select HTML	
		echo "<SELECT NAME='ICAO_Transponder' id='ICAO_Transponder'>";
		
		// build the selection entries
		echo "<OPTION VALUE='N'" . 
				("N" == $ICAOTransponder ? " SELECTED" : "") . 
				">None";
		echo "<OPTION VALUE='A'" . 
				("A" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode A";
		echo "<OPTION VALUE='C'" . 
				("C" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode C";
		echo "<OPTION VALUE='X'" . 
				("X" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode S, no Palt, no AC ID";
		echo "<OPTION VALUE='P'" . 
				("P" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode S with PAlt, no AC ID";
		echo "<OPTION VALUE='I'" . 
				("I" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode S, no PAlt with AC ID";
		echo "<OPTION VALUE='S'" . 
				("S" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode S, PAlt, AC ID";
		echo "<OPTION VALUE='E'" . 
				("E" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode S, PAlt, AC ID, Sq";
		echo "<OPTION VALUE='H'" . 
				("H" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode S, PAlt, AC ID, ENH";
		echo "<OPTION VALUE='L'" . 
				("L" == $ICAOTransponder ? " SELECTED" : "") . 
				">Mode S, PAlt, AC ID, Sq, EHH";
		echo "</SELECT>";	
     }
    
    //********************************************************************
    // BuildAircraftMakeSelector($AircraftMake)
    //
    // Purpose: Display a selector for the list of aircraft makes.
    //
    // Inputs:
    //   AircraftMake - current aircraft make
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildAircraftMakeSelector($AircraftMake)
    {
        // get the makes from the database
		$sql = 
				"SELECT make, make_id " .
				"FROM AircraftScheduling_make " .
                "ORDER BY make";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {    	
            // build the select HTML	
    		echo "<SELECT NAME='make_id' id='make_id'>";
    		
    		// build the selection entries
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
    			echo "<OPTION " .
    					"VALUE='" . $row[1] . "'" . 
    					($row[1] == $AircraftMake ? " SELECTED" : "") . 
    					">$row[0]";
    		}
    		echo "</SELECT>";	
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildAircraftMakeSelector", $sql);
        }
    }
	
    //********************************************************************
    // BuildAircraftModelSelector($AircraftModel)
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
    function BuildAircraftModelSelector($AircraftModel)
    {
        // get the models from the database
		$sql = 
				"SELECT model, model_id " .
				"FROM AircraftScheduling_model " .
                "ORDER by model";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {    	
            // build the select HTML	
    		echo "<SELECT NAME='model_id' id='model_id'>";
    		
    		// build the selection entries
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
    			echo "<OPTION " .
    					"VALUE='" . $row[1] . "'" . 
    					($row[1] == $AircraftModel ? " SELECTED" : "") . 
    					">$row[0]";
    		}
    		echo "</SELECT>";	
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildAircraftModelSelector", $sql);
        }
    }
    
    //********************************************************************
    // LoadOilTypes(OilType As ComboBox)
    //
    // Purpose:  Load the possible oil types into the OilType control.
    //
    // Inputs:
    //   OilType - currently selected oil type
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function LoadOilTypes($OilType)
    {
        // build the select HTML	
		echo "<SELECT NAME='Oil_Type' id='Oil_Type'>";
        
        // put "None" as a choice in case the user doesn't want to pick an oil type
		echo "<OPTION " .
				"VALUE='" . "None" . "'" . 
				("None" == $OilType ? " SELECTED" : "") . 
				">None";
        
        // load the part numbers into the combo box
        LoadPartNumbers($OilType);

  		// finished with the select
  		echo "</SELECT>";	
    }
    
    //********************************************************************
    // BuildAircraftTableEntry(
    //                   $ControlTitle, 
    //                   $WeightControlName,
    //                   $DefaultValue, 
    //                   $AllowChanges,
    //                   $TableName,
    //                   $ControlNameBoxSize,
    //                   $Column1Width,
    //                   $Column2Width)
    //
    // Purpose: Build a table row to display a control.
    //
    // Inputs:
    //   ControlTitle - the title for the control
    //   WeightControlName - the name of the item
    //   DefaultValue - default value for the input control
    //   AllowChanges - set to 1 to allow changes to the entries.
    //   TableName - name of the table that contains the input
    //   ControlNameBoxSize - size of the input box
    //   Column1Width - width of the first column in the table
    //   Column2Width - width of the second column in the table
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildAircraftTableEntry(
                                $ControlTitle, 
                                $WeightControlName,
                                $DefaultValue, 
                                $AllowChanges,
                                $TableName,
                                $ControlNameBoxSize,
                                $Column1Width,
                                $Column2Width)
     {
        // make sure we don't have any blanks in the control names
        $WeightControlName = Replace($WeightControlName, " ", "_");
        $TableName = Replace($TableName, " ", "_");

        // each entry is a table row
        echo "<TR>"; 
        
        // build the title column
        echo "<TD ALIGN=LEFT WIDTH=$Column1Width>$ControlTitle</TD>";        
    
        // build the column and let them change it if requested
        if ($AllowChanges)
        {
            // build the input column and let them change it.
            echo "<td align=left width=$Column2Width>" . 
                    "<input " .
                        "type=text " .
                        "name='$WeightControlName' " . 
                        "id='$WeightControlName' " .
                        "align=right " . 
                        "size=$ControlNameBoxSize " . 
                        "value='" . $DefaultValue . "' " . 
                        "Onchange='UpdateAircraftControl(\"$WeightControlName\", \"$TableName\")'>" . 
                    "</td>";
        }
        else
        {
            echo "<TD ALIGN=RIGHT WIDTH=$Column2Width>" . $DefaultValue . "</TD>";
        }
        
        // end the table row
        echo "</TR>";    	    
    }
    
    //********************************************************************
    // BuildArmWeightTableEntry(
    //                   $ControlTitle, 
    //                   $WeightControlName2,
    //                   $WeightControlName3,
    //                   $WeightControlName4,
    //                   $DefaultValue2, 
    //                   $DefaultValue3, 
    //                   $DefaultValue4, 
    //                   $TableName,
    //                   $ControlNameBoxSize,
    //                   $Column1Width,
    //                   $Column2Width,
    //                   $Column3Width,
    //                   $Column4Width)
    //
    // Purpose: Build a aircraft arm and weight table row to display a control.
    //
    // Inputs:
    //   ControlTitle - the title for the control
    //   WeightControlName2 - the name of the control for column 2
    //   WeightControlName3 - the name of the control for column 3
    //   WeightControlName4 - the name of the control for column 4
    //   DefaultValue2 - default value for the first input control
    //   DefaultValue3 - default value for the second input control
    //   DefaultValue4 - default value for the third input control
    //   TableName - name of the table that contains the input
    //   ControlNameBoxSize - size of the input box
    //   Column1Width - width of the first column in the table
    //   Column2Width - width of the second column in the table
    //   Column3Width - width of the third column in the table
    //   Column4Width - width of the fourth column in the table
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildArmWeightTableEntry(
                                $ControlTitle, 
                                $WeightControlName2,
                                $WeightControlName3,
                                $WeightControlName4,
                                $DefaultValue2, 
                                $DefaultValue3, 
                                $DefaultValue4, 
                                $TableName,
                                $ControlNameBoxSize,
                                $Column1Width,
                                $Column2Width,
                                $Column3Width,
                                $Column4Width)
     {
        // make sure we don't have any blanks in the control names
        $WeightControlName2 = Replace($WeightControlName2, " ", "_");
        $WeightControlName3 = Replace($WeightControlName3, " ", "_");
        $WeightControlName4 = Replace($WeightControlName4, " ", "_");
        $TableName = Replace($TableName, " ", "_");

        // each entry is a table row
        echo "<TR>"; 
        
        // build the title column
        echo "<TD ALIGN=LEFT WIDTH=$Column1Width>$ControlTitle</TD>";        
    
        // build the second column
        echo "<TD ALIGN=RIGHT WIDTH=$Column2Width>" . 
                "<INPUT " .
                    "TYPE=TEXT " .
                    "NAME='$WeightControlName2' " . 
                    "ID='$WeightControlName2' " .
                    "ALIGN=RIGHT " . 
                    "SIZE=$ControlNameBoxSize " . 
                    "VALUE='" . FormatField($DefaultValue2, "Float") . "' " . 
                    "Onchange='UpdateAircraftControl(\"$WeightControlName2\", \"$TableName\")'>" . 
                "</TD>";
    
        // if the fourth column is not specified, build the third column to span
        // column three and four
        if ($Column4Width == 0)
        {
            // build the second input column
            echo "<TD ALIGN=RIGHT colspan=2>" . 
                    "<INPUT " .
                        "TYPE=TEXT " .
                        "NAME='$WeightControlName3' " . 
                        "ID='$WeightControlName3' " .
                        "ALIGN=RIGHT " . 
                        "SIZE=$ControlNameBoxSize " . 
                        "VALUE='" . FormatField($DefaultValue3, "Float") . "' " . 
                        "Onchange='UpdateAircraftControl(\"$WeightControlName3\", \"$TableName\")'>" . 
                    "</TD>";
        }
        else
        {
            // fourth column is specifed, build the third column
            echo "<TD ALIGN=RIGHT WIDTH=$Column3Width>" . 
                    "<INPUT " .
                        "TYPE=TEXT " .
                        "NAME='$WeightControlName3' " . 
                        "ID='$WeightControlName3' " .
                        "ALIGN=RIGHT " . 
                        "SIZE=$ControlNameBoxSize " . 
                        "VALUE='" . FormatField($DefaultValue3, "Float") . "' " . 
                        "Onchange='UpdateAircraftControl(\"$WeightControlName3\", \"$TableName\")'>" . 
                    "</TD>";

            // fourth column is specifed, build the fourth column
            echo "<TD ALIGN=RIGHT WIDTH=$Column4Width>" . 
                    "<INPUT " .
                        "TYPE=TEXT " .
                        "NAME='$WeightControlName4' " . 
                        "ID='$WeightControlName4' " .
                        "ALIGN=RIGHT " . 
                        "SIZE=$ControlNameBoxSize " . 
                        "VALUE='" . FormatField($DefaultValue4, "Float") . "' " . 
                        "Onchange='UpdateAircraftControl(\"$WeightControlName4\", \"$TableName\")'>" . 
                    "</TD>";
        }
        
        // end the table row
        echo "</TR>";    	    
    }
    
    //********************************************************************
    // BuildCGTableEntry(
    //                   $WeightControlName,
    //                   $DefaultValue, 
    //                   $TableName,
    //                   $ControlNameBoxSize,
    //                   $ColumnWidth)
    //
    // Purpose: Build a aircraft CG table entry to display a control.
    //
    // Inputs:
    //   WeightControlName - the name of the item
    //   DefaultValue - default value for the first input control
    //   TableName - name of the table that contains the input
    //   ControlNameBoxSize - size of the input box
    //   ColumnWidth - width of the column in the table
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildCGTableEntry(
                                $WeightControlName,
                                $DefaultValue, 
                                $TableName,
                                $ControlNameBoxSize,
                                $ColumnWidth)
     {
        // make sure we don't have any blanks in the control names
        $WeightControlName = Replace($WeightControlName, " ", "_");
        $TableName = Replace($TableName, " ", "_");
    
        // build the second column
        echo "<td align=right width=$ColumnWidth>" . 
                "<input " .
                    "type=text " .
                    "name='$WeightControlName' " . 
                    "id='$WeightControlName' " .
                    "align=right " . 
                    "size=$ControlNameBoxSize " . 
                    "value='" . FormatField($DefaultValue, "Float") . "' " . 
                    "Onchange='UpdateAircraftControl(\"$WeightControlName\", \"$TableName\")'>" . 
                "</TD>";
    }

    //********************************************************************
    // DisplayAircraftInformation()
    //
    // Purpose:  Display the aircraft information fields for the user to
    //           enter the aircraft information.
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
    function DisplayAircraftInformation()
    {    	
    	global $AircraftScheduleType, $InstructorScheduleType;
        global $AllowAircraftCheckout;

        global $AircraftRST;
        
        include "DatabaseConstants.inc";

        // set the column sizes
        $Column1Width = "80%";
        $Column2Width = "20%";
        
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
         
        // set the table name  	
        $TableName = "AircraftInformationTable";        
    
        // start the table
        echo "<table id='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='2'>Aircraft Information</td>";
        echo "</tr>";
        
        // status
        echo "<tr>";
        echo " <td width=$Column1Width>Status</td>";
        echo " <td width=$Column2Width>";
        BuildStatusSelector(LookupAircraftStatusString($AircraftRST[$status_offset]));
        echo "</td>";
        echo "</tr>";
        
        // make
        echo "<tr>";
        echo " <td width=$Column1Width>Make</td>";
        echo " <td width=$Column2Width>";
        BuildAircraftMakeSelector($AircraftRST[$make_id_offset]);
        echo "</td>";
        echo "</tr>";
        
        // model
        echo "<tr>";
        echo " <td width=$Column1Width>Model</td>";
        echo " <td width=$Column2Width>";
        BuildAircraftModelSelector($AircraftRST[$model_id_offset]);
        echo "</td>";
        echo "</tr>";
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // current tach
            BuildAircraftTableEntry(
                                    "Current Tach", 
                                    "tach1", 
                                    FormatField($AircraftRST[$tach1_offset], "Float"), 
                                    $AllowChanges, 
                                    $TableName,
                                    15,
                                    $Column1Width,
                                    $Column2Width);
           
            // 100 hour tach
            BuildAircraftTableEntry(
                                    "100 hour tach", 
                                    "Hundred_Hr_Tach", 
                                    FormatField($AircraftRST[$Hundred_Hr_Tach_offset], "Float"), 
                                    $AllowChanges, 
                                    $TableName,
                                    15,
                                    $Column1Width,
                                    $Column2Width);
           
            // annual due
        	$Annualday   = Day($AircraftRST[$Annual_Due_offset]);
        	$Annualmonth = Month($AircraftRST[$Annual_Due_offset]);
        	$Annualyear  = Year($AircraftRST[$Annual_Due_offset]);
            echo "<tr>";
            echo "<td align=left width=$Column1Width>Annual Due";
            echo "</td>";
            echo "<td align=left>";
			genDateSelector("Annual", "main", $Annualday, $Annualmonth, $Annualyear);
            echo "</td>";
            echo "</tr>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            // current tach
            echo "<INPUT NAME='tach1' TYPE='HIDDEN' VALUE='$AircraftRST[$tach1_offset]'>\n";
           
            // 100 hour tach
            echo "<INPUT NAME='Hundred_Hr_Tach' TYPE='HIDDEN' VALUE='$AircraftRST[$Hundred_Hr_Tach_offset]'>\n";
           
            // annual due
        	$Annualday   = Day($AircraftRST[$Annual_Due_offset]);
        	$Annualmonth = Month($AircraftRST[$Annual_Due_offset]);
        	$Annualyear  = Year($AircraftRST[$Annual_Due_offset]);
            echo "<INPUT NAME='Annualday' TYPE='HIDDEN' VALUE='$Annualday'>\n";
            echo "<INPUT NAME='Annualmonth' TYPE='HIDDEN' VALUE='$Annualmonth'>\n";
            echo "<INPUT NAME='Annualyear' TYPE='HIDDEN' VALUE='$Annualyear'>\n";
        }
       
        // Rental Hour Rate
        BuildAircraftTableEntry(
                                "Rental Hour Rate", 
                                "hourly_cost", 
                                FormatField($AircraftRST[$hourly_cost_offset], "Currency"), 
                                $AllowChanges, 
                                $TableName,
                                15,
                                $Column1Width,
                                $Column2Width);
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // Owner Credit Rate
            BuildAircraftTableEntry(
                                    "Owner Credit Rate", 
                                    "rental_fee", 
                                    FormatField($AircraftRST[$rental_fee_offset], "Currency"), 
                                    $AllowChanges, 
                                    $TableName,
                                    15,
                                    $Column1Width,
                                    $Column2Width);
           
            // current hobbs
            BuildAircraftTableEntry(
                                    "Current Hobbs", 
                                    "Current_Hobbs", 
                                    FormatField($AircraftRST[$Current_Hobbs_offset], "Float"), 
                                    $AllowChanges, 
                                    $TableName,
                                    15,
                                    $Column1Width,
                                    $Column2Width);
           
            // current user
            BuildAircraftTableEntry(
                                    "Current User", 
                                    "Current_User", 
                                    $AircraftRST[$Current_User_offset], 
                                    $AllowChanges, 
                                    $TableName,
                                    15,
                                    $Column1Width,
                                    $Column2Width);
           
            // current keycode
            BuildAircraftTableEntry(
                                    "Current Username", 
                                    "CurrentKeycode", 
                                    $AircraftRST[$CurrentKeycode_offset], 
                                    $AllowChanges, 
                                    $TableName,
                                    15,
                                    $Column1Width,
                                    $Column2Width);
           
            // last flight cleared by
            echo "<tr>";
            echo "<td align=left>Clearing Authority:";
            echo "</td>";
            echo "<td align=left>";
            BuildClearingAuthoritySelector(
                                GetNameFromUsername($AircraftRST[$AircraftCleared_By_offset]), 
                                true, 
                                "Cleared_By",
                                20,
                                true,
                                false,
                                "",
                                true);
            echo "</td>";
            echo "</tr>";
                    
            // oil type
            echo "<tr>";
            echo " <td align=left width=$Column1Width>Oil Type</td>";
            echo " <td align=left width=$Column2Width>";
            LoadOilTypes($AircraftRST[$Oil_Type_offset]);
            echo "</td>";
            echo "</tr>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            // Owner Credit Rate
            echo "<INPUT NAME='rental_fee' TYPE='HIDDEN' VALUE='$AircraftRST[$rental_fee_offset]'>\n";
           
            // current hobbs
            echo "<INPUT NAME='hobbs' TYPE='HIDDEN' VALUE='$AircraftRST[$Current_Hobbs_offset]'>\n";
           
            // current user
            echo "<INPUT NAME='Current_User' TYPE='HIDDEN' VALUE='$AircraftRST[$Current_User_offset]'>\n";
           
            // current keycode
            echo "<INPUT NAME='CurrentKeycode' TYPE='HIDDEN' VALUE='$AircraftRST[$CurrentKeycode_offset]'>\n";
           
            // last flight cleared by
            echo "<INPUT NAME='Cleared_By' TYPE='HIDDEN' VALUE='$AircraftRST[$AircraftCleared_By_offset]'>\n";
            // oil type
            echo "<INPUT NAME='Oil_Type' TYPE='HIDDEN' VALUE='$AircraftRST[$Oil_Type_offset]'>\n";
        }
       
        // serial number
        BuildAircraftTableEntry(
                                "Serial Number", 
                                "serial_number", 
                                $AircraftRST[$serial_number_offset], 
                                $AllowChanges, 
                                $TableName,
                                15,
                                $Column1Width,
                                $Column2Width);
       
        // year
        BuildAircraftTableEntry(
                                "Year", 
                                "AircraftYear", 
                                $AircraftRST[$year_offset], 
                                $AllowChanges, 
                                $TableName,
                                15,
                                $Column1Width,
                                $Column2Width);
        
        
        // ICAO equipment codes
		BuildAircraftTableEntry(
								"ICAO Equip Codes", 
								"ICAO_Equipment_Codes", 
								$AircraftRST[$ICAO_Equipment_Codes_offset], 
								$AllowChanges, 
								$TableName,
								12,
								$Column1Width,
								$Column2Width);
        
        // ICAO wake turbulence
        echo "<tr>";
        echo " <td width=$Column1Width>ICAO Wake Turb</td>";
        echo " <td width=$Column2Width>";
		BuildICAOWakeTurbSelector($AircraftRST[$ICAO_Wake_Turb_offset]);
        echo "</td>";
        echo "</tr>";
        
        // ICAO flight type
        echo "<tr>";
        echo " <td width=$Column1Width>ICAO Flight Type</td>";
        echo " <td width=$Column2Width>";
		BuildICAOFlightTypeSelector($AircraftRST[$ICAO_Flight_Type_offset]);
        echo "</td>";
        echo "</tr>";
        
        // ICAO ADS-B codes
        echo "<tr>";
        echo " <td width=$Column1Width>ICAO ADS-B Code</td>";
        echo " <td width=$Column2Width>";
		BuildICAOADSBCodeSelector($AircraftRST[$ICAO_ADSB_Type_offset]);
        echo "</td>";
        echo "</tr>";
        
        // ICAO Transponder
        echo "<tr>";
        echo " <td width=$Column1Width>ICAO Transponder</td>";
        echo " <td width=$Column2Width>";
		BuildICAOTransponderSelector($AircraftRST[$ICAO_Transponder_offset]);
        echo "</td>";
        echo "</tr>";
        
        // ICAO number aircraft
		BuildAircraftTableEntry(
								"ICAO # Aircraft", 
								"ICAO_Number_Aircraft", 
								$AircraftRST[$ICAO_Number_Aircraft_offset], 
								$AllowChanges, 
								$TableName,
								12,
								$Column1Width,
								$Column2Width);
       
        // IFR Certified
        echo "<tr>";
        echo " <td colspan=2>";
        echo "<INPUT NAME='ifr_cert' TYPE='checkbox'";
        if ($AircraftRST[$ifr_cert_offset] == 1) echo "CHECKED";
        echo "> IFR Certified";
        echo "</td>";
        echo "</tr>";
        
        // schedulable
        echo "<tr>";
        echo " <td colspan=2>";
        echo "<input type=checkbox name='schedulable'";
        $Schedulable = sql_query1(
                    "SELECT COUNT(*) " . 
                    "FROM AircraftScheduling_resource " . 
                    "WHERE item_id=$AircraftRST[$aircraft_id_offset] " .
                    "  AND schedulable_id=$AircraftScheduleType");
        if ($Schedulable == 1) echo "CHECKED";
        echo ">Schedulable";
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayOwnerInformation()
    //
    // Purpose:  Display the aircraft owner information fields for the user to
    //           enter the aircraft information.
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
    function DisplayOwnerInformation()
    {    	
        global $AllowAircraftCheckout;

        global $AircraftRST;
        
        include "DatabaseConstants.inc";

        // set the column sizes
        $Column1Width = "50%";
        $Column2Width = "20%";
        $Column3Width = "30%";
       
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
        $LargeBoxSize = 33;
         
        // set the table name  	
        $TableName = "AircraftOwnerTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Owner Information</td>";
        echo "</tr>";
        
        // owner's name
        echo "<tr>";
        echo "<td colspan=3>Owner's Name</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=3>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_Name' " . 
                    "ID='Aircraft_Owner_Name' " .
                    "align=left " . 
                    "SIZE=$LargeBoxSize " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_Name_offset] . "' " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // owner's address
        echo "<tr>";
        echo "<td colspan=3>Address</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=3>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_Address' " . 
                    "ID='Aircraft_Owner_Address' " .
                    "align=left " . 
                    "SIZE=$LargeBoxSize " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_Address_offset] . "' " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // owner's city, state, zip
        echo "<tr>";
        echo "<td width=$Column1Width>City</td>";
        echo "<td width=$Column2Width>State</td>";
        echo "<td width=$Column3Width>Zip Code</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left width=$Column1Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_City' " . 
                    "ID='Aircraft_Owner_City' " .
                    "align=left " . 
                    "SIZE=14 " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_City_offset] . "' " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column1Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_State' " . 
                    "ID='Aircraft_Owner_State' " .
                    "align=left " . 
                    "SIZE=3 " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_State_offset] . "' " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column1Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_Zip' " . 
                    "ID='Aircraft_Owner_Zip' " .
                    "align=left " . 
                    "SIZE=6 " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_Zip_offset] . "' " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // contract number
        echo "<tr>";
        echo "<td colspan=3>Contract Number</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=3>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_Contract' " . 
                    "ID='Aircraft_Owner_Contract' " .
                    "align=left " . 
                    "SIZE=$LargeBoxSize " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_Contract_offset] . "' " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // owner's city, state, zip
        echo "<tr>";
        echo "<td width=$Column1Width>Phone</td>";
        echo "<td colspan=2>Phone</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left width=$Column1Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_Phone1' " . 
                    "ID='Aircraft_Owner_Phone1' " .
                    "align=left " . 
                    "SIZE=14 " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_Phone1_offset] . "' " . 
                    ">" . 
                "</td>";
        echo "<td align=left colspan=2>" . 
                "<input " .
                    "type=text " .
                    "NAME='Aircraft_Owner_Phone2' " . 
                    "ID='Aircraft_Owner_Phone2' " .
                    "align=left " . 
                    "SIZE=14 " . 
                    "VALUE='" . $AircraftRST[$Aircraft_Owner_Phone2_offset] . "' " . 
                    ">" . 
                "</td>";
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayFlightPlanInformation()
    //
    // Purpose:  Display the aircraft flight plan information fields for 
    //           the user to enter the aircraft information.
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
    function DisplayFlightPlanInformation()
    {    	
        global $AllowAircraftCheckout;

        global $AircraftRST;
        
        include "DatabaseConstants.inc";

        // set the column sizes
        $Column1Width = "80%";
        $Column2Width = "20%";
        
        // constants
        $AllowChanges = 1;
        $DontAllowChanges = 0;
         
        // set the table name  	
        $TableName = "AircraftFlightPlanTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='2'>Flight Plan Information</td>";
        echo "</tr>";
            
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // aircraft remarks
            BuildAircraftTableEntry(
                                    "Aircraft Remarks", 
                                    "Aircraft_Remarks", 
                                    $AircraftRST[$Aircraft_Remarks_offset], 
                                    $AllowChanges, 
                                    $TableName,
                                    12,
                                    $Column1Width,
                                    $Column2Width);
           
            // aircraft airspeed
            BuildAircraftTableEntry(
                                    "Aircraft Airspeed (kts)", 
                                    "Aircraft_Airspeed", 
                                    $AircraftRST[$Aircraft_Airspeed_offset], 
                                    $AllowChanges, 
                                    $TableName,
                                    12,
                                    $Column1Width,
                                    $Column2Width);
           
            // Va at gross
            BuildAircraftTableEntry(
                                    "Va (kts) at Max Wt", 
                                    "Va_Max_Weight", 
                                    $AircraftRST[$Va_Max_Weight_offset], 
                                    $AllowChanges, 
                                    $TableName,
                                    12,
                                    $Column1Width,
                                    $Column2Width);
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            // aircraft remarks
            echo "<INPUT NAME='Aircraft_Remarks' TYPE='HIDDEN' VALUE='$AircraftRST[$Aircraft_Remarks_offset]'>\n";
           
            // aircraft airspeed
            echo "<INPUT NAME='Aircraft_Airspeed' TYPE='HIDDEN' VALUE='$AircraftRST[$Aircraft_Airspeed_offset]'>\n";
           
            // Va at gross
            echo "<INPUT NAME='Va_Max_Weight' TYPE='HIDDEN' VALUE='$AircraftRST[$Va_Max_Weight_offset]'>\n";
        }

        // Aircraft Color
        BuildAircraftTableEntry(
                                "Aircraft Color", 
                                "Aircraft_Color", 
                                $AircraftRST[$Aircraft_Color_offset], 
                                $AllowChanges, 
                                $TableName,
                                12,
                                $Column1Width,
                                $Column2Width);
       
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // Flight ID
            BuildAircraftTableEntry(
                                    "Flight ID", 
                                    "Flight_ID", 
                                    $AircraftRST[$Flight_ID_offset], 
                                    $AllowChanges, 
                                    $TableName,
                                    12,
                                    $Column1Width,
                                    $Column2Width);
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<INPUT NAME='Flight_ID' TYPE='HIDDEN' VALUE='$AircraftRST[$Flight_ID_offset]'>\n";
        }
    
        // finished with the table
        echo "</table>";
    }
    
    //********************************************************************
    // DisplayArmsWeightsInformation()
    //
    // Purpose:  Display the aircraft arms and weight information fields for 
    //           the user to enter the aircraft information.
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
    function DisplayArmsWeightsInformation()
    {    	
        global $AllowAircraftCheckout;

        global $AircraftRST;
        
        include "DatabaseConstants.inc";

        // set the column sizes
        $Column1Width = "40%";
        $Column2Width = "20%";
        $Column3Width = "20%";
        $Column4Width = "20%";
        
        // set the size of the input text boxes
        $InputTextboxSize = 5;
         
        // set the table name  	
        $TableName = "AircraftArmsWeightsTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='4'>Aircraft Arms and Weights</td>";
        echo "</tr>";
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{        
            // seat and baggage area sub title
            echo "<tr>";
            echo " <td width=$Column1Width><center><b>Station</b></center></td>";
            echo " <td width=$Column2Width><center><b>Arm</b></center></td>";
            echo " <td colspan=2><center><b>Default Wt</b></center></td>";
            echo "</tr>";
           
            // front seat
            BuildArmWeightTableEntry(
                                    "Front Seat", 
                                    "Front_Seat_Arm", 
                                    "Front_Seat_Weight", 
                                    "", 
                                    $AircraftRST[$Front_Seat_Arm_offset], 
                                    $AircraftRST[$Front_Seat_Weight_offset], 
                                    "",
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    0);
           
            // back seat 1
            BuildArmWeightTableEntry(
                                    "Back Seat 1", 
                                    "Rear_Seat_1_Arm", 
                                    "Rear_Seat_1_Weight", 
                                    "", 
                                    $AircraftRST[$Rear_Seat_1_Arm_offset], 
                                    $AircraftRST[$Rear_Seat_1_Weight_offset], 
                                    "",
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    0);
           
            // back seat 2
            BuildArmWeightTableEntry(
                                    "Back Seat 2", 
                                    "Rear_Seat_2_Arm", 
                                    "Rear_Seat_2_Weight", 
                                    "", 
                                    $AircraftRST[$Rear_Seat_2_Arm_offset], 
                                    $AircraftRST[$Rear_Seat_2_Weight_offset], 
                                    "",
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    0);
           
            // baggage area 1
            BuildArmWeightTableEntry(
                                    "Baggage Area 1", 
                                    "Baggage_Area_1_Arm", 
                                    "Baggage_Area_1_Weight", 
                                    "", 
                                    $AircraftRST[$Baggage_Area_1_Arm_offset], 
                                    $AircraftRST[$Baggage_Area_1_Weight_offset], 
                                    "",
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    0);
           
            // baggage area 2
            BuildArmWeightTableEntry(
                                    "Baggage Area 2", 
                                    "Baggage_Area_2_Arm", 
                                    "Baggage_Area_2_Weight", 
                                    "", 
                                    $AircraftRST[$Baggage_Area_2_Arm_offset], 
                                    $AircraftRST[$Baggage_Area_2_Weight_offset], 
                                    "",
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    0);
           
            // baggage area 3
            BuildArmWeightTableEntry(
                                    "Baggage Area 3", 
                                    "Baggage_Area_3_Arm", 
                                    "Baggage_Area_3_Weight", 
                                    "", 
                                    $AircraftRST[$Baggage_Area_3_Arm_offset], 
                                    $AircraftRST[$Baggage_Area_3_Weight_offset], 
                                    "",
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    0);
            
            // fuel arms and weights sub title
            echo "<tr>";
            echo " <td width=$Column1Width><b><center>Station</b></center></td>";
            echo " <td width=$Column2Width><b><center>Arm</b></center></td>";
            echo " <td width=$Column3Width><b><center>Normal</b></center></td>";
            echo " <td width=$Column4Width><b><center>Max</b></center></td>";
            echo "</tr>";
           
            // fuel
            BuildArmWeightTableEntry(
                                    "Fuel (gals)", 
                                    "Fuel_Arm", 
                                    "Default_Fuel_Gallons", 
                                    "Full_Fuel_Gallons", 
                                    $AircraftRST[$Fuel_Arm_offset], 
                                    $AircraftRST[$Default_Fuel_Gallons_offset], 
                                    $AircraftRST[$Full_Fuel_Gallons_offset],
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    $Column4Width);
           
            // aux fuel
            BuildArmWeightTableEntry(
                                    "Aux Fuel (gals)", 
                                    "Aux_Fuel_Arm", 
                                    "Aux_Fuel_Gallons", 
                                    "", 
                                    $AircraftRST[$Aux_Fuel_Arm_offset], 
                                    $AircraftRST[$Aux_Fuel_Gallons_offset], 
                                    "",
                                    $TableName,
                                    $InputTextboxSize, 
                                    $Column1Width,
                                    $Column2Width,
                                    $Column3Width,
                                    0);
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
           
            // front seat
            echo "<INPUT NAME='Front_Seat_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Front_Seat_Arm_offset]'>\n";
            echo "<INPUT NAME='Front_Seat_Weight' TYPE='HIDDEN' VALUE='$AircraftRST[$Front_Seat_Weight_offset]'>\n";
           
            // back seat 1
            echo "<INPUT NAME='Rear_Seat_1_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Rear_Seat_1_Arm_offset]'>\n"; 
            echo "<INPUT NAME='Rear_Seat_1_Weight' TYPE='HIDDEN' VALUE='$AircraftRST[$Rear_Seat_1_Weight_offset]'>\n";
           
            // back seat 2
            echo "<INPUT NAME='Rear_Seat_2_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Rear_Seat_2_Arm_offset]'>\n"; 
            echo "<INPUT NAME='Rear_Seat_2_Weight' TYPE='HIDDEN' VALUE='$AircraftRST[$Rear_Seat_2_Weight_offset]'>\n";
           
            // baggage area 1
            echo "<INPUT NAME='Baggage_Area_1_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Baggage_Area_1_Arm_offset]'>\n"; 
            echo "<INPUT NAME='Baggage_Area_1_Weight' TYPE='HIDDEN' VALUE='$AircraftRST[$Baggage_Area_1_Weight_offset]'>\n";
           
            // baggage area 2
            echo "<INPUT NAME='Baggage_Area_2_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Baggage_Area_2_Arm_offset]'>\n"; 
            echo "<INPUT NAME='Baggage_Area_2_Weight' TYPE='HIDDEN' VALUE='$AircraftRST[$Baggage_Area_2_Weight_offset]'>\n";
           
            // baggage area 3
            echo "<INPUT NAME='Baggage_Area_3_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Baggage_Area_3_Arm_offset]'>\n"; 
            echo "<INPUT NAME='Baggage_Area_3_Weight' TYPE='HIDDEN' VALUE='$AircraftRST[$Baggage_Area_3_Weight_offset]'>\n";
           
            // fuel
            echo "<INPUT NAME='Fuel_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Fuel_Arm_offset]'>\n"; 
            echo "<INPUT NAME='Default_Fuel_Gallons' TYPE='HIDDEN' VALUE='$AircraftRST[$Default_Fuel_Gallons_offset]'>\n";
           
            // aux fuel
            echo "<INPUT NAME='Aux_Fuel_Arm' TYPE='HIDDEN' VALUE='$AircraftRST[$Aux_Fuel_Arm_offset]'>\n";
            echo "<INPUT NAME='Aux_Fuel_Gallons' TYPE='HIDDEN' VALUE='$AircraftRST[$Aux_Fuel_Gallons_offset]'>\n";
        }
        
        // aircraft sub title
        echo "<tr>";
        echo " <td width=$Column1Width><center><b>Station</b></center></td>";
        echo " <td width=$Column2Width><center><b>Arm</b></center></td>";
        echo " <td width=$Column3Width><center><b>Empty</b></center></td>";
        echo " <td width=$Column4Width><center><b>Max</b></center></td>";
        echo "</tr>";
       
        // aircraft
        BuildArmWeightTableEntry(
                                "Aircraft", 
                                "Aircraft_Arm", 
                                "empty_weight", 
                                "max_gross", 
                                $AircraftRST[$Aircraft_Arm_offset], 
                                $AircraftRST[$empty_weight_offset], 
                                $AircraftRST[$max_gross_offset],
                                $TableName,
                                $InputTextboxSize, 
                                $Column1Width,
                                $Column2Width,
                                $Column3Width,
                                $Column4Width);
    
        // finished with the table
        echo "</table>";
    }
    
    //********************************************************************
    // DisplayCGEnvelopeInformation()
    //
    // Purpose:  Display the aircraft CG envelope information fields for 
    //           the user to enter the aircraft information.
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
    function DisplayCGEnvelopeInformation()
    {    	
        global $AllowAircraftCheckout;

        global $AircraftRST;
        
        include "DatabaseConstants.inc";

        // set the column sizes
        $Column1Width = "16%";
        $Column2Width = "16%";
        $Column3Width = "16%";
        $Column4Width = "20%";
        $Column5Width = "16%";
        $Column6Width = "16%";

        // get the weight and balance field from the database record
        $WBFields = $AircraftRST[$WB_Fields_offset];
        if (strlen($WBFields) > 0)
        {
            // initialize the weight and balance arrays
            for ($i = 0; $i < 6; $i++)
            {
                $WBAircraftWeight[$i] = 0.0;
                $WBForeCG[$i] = 0.0;
                $WBAftCG[$i] = 0.0;
            }
            
            // get the tokens from the fields
            $NumWBFieldRecords = 0;
            while (strlen($WBFields) > 0)
            {
                $WBAircraftWeight[$NumWBFieldRecords] = GetNextToken($WBFields, ",");
                $WBForeCG[$NumWBFieldRecords] = GetNextToken($WBFields, ",");
                $WBAftCG[$NumWBFieldRecords] = GetNextToken($WBFields, ";");
                $NumWBFieldRecords++;
            }
        }
		else
		{
			// initialize the weight and balance arrays
            for ($i = 0; $i < 6; $i++)
            {
                $WBAircraftWeight[$i] = 0.0;
                $WBForeCG[$i] = 0.0;
                $WBAftCG[$i] = 0.0;
            }
		}
         
        // set the table name  	
        $TableName = "AircraftCGEnvelopeTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
              
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // title
            echo "<tr>";
            echo " <th colspan=6>Aircraft CG Envelope</td>";
            echo "</tr>";
            
            // sub titles
            echo "<tr>";
            echo " <td width=$Column1Width><center><b>Weight</b></center></td>";
            echo " <td width=$Column2Width><center><b>Fore</b></center></td>";
            echo " <td width=$Column3Width><center><b>Aft</b></center></td>";
            echo " <td width=$Column4Width><center><b>Weight</b></center></td>";
            echo " <td width=$Column5Width><center><b>Fore</b></center></td>";
            echo " <td width=$Column6Width><center><b>Aft</b></center></td>";
            echo "</tr>";
                    
            // output the weight fore and aft CG lines
            for ($i = 0; $i < 3; $i++)
            {
                echo "<tr>";
                BuildCGTableEntry(
                                "AircraftWeight" . $i,
                                $WBAircraftWeight[$i], 
                                $TableName,
                                3,
                                $Column1Width);
                BuildCGTableEntry(
                                "ForeCG" . $i,
                                $WBForeCG[$i], 
                                $TableName,
                                2,
                                $Column2Width);
                BuildCGTableEntry(
                                "AftCG" . $i,
                                $WBAftCG[$i], 
                                $TableName,
                                2,
                                $Column3Width);
                BuildCGTableEntry(
                                "AircraftWeight" . ($i + 3),
                                $WBAircraftWeight[$i + 3], 
                                $TableName,
                                3,
                                $Column4Width);
                BuildCGTableEntry(
                                "ForeCG" . ($i + 3),
                                $WBForeCG[$i + 3], 
                                $TableName,
                                2,
                                $Column5Width);
                BuildCGTableEntry(
                                "AftCG" . ($i + 3),
                                $WBAftCG[$i + 3], 
                                $TableName,
                                2,
                                $Column6Width);
                echo "</tr>";
            }
                     
            // instruction line
            echo "<tr>";
            echo " <td colspan=6><b>Enter fore and aft CG limits for each weight.</b></td>";
            echo "</tr>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
                    
            // output the weigth fore and aft CG lines
            for ($i = 0; $i < 3; $i++)
            {
                echo "<INPUT NAME='AircraftWeight" . $i . "' TYPE='HIDDEN' VALUE='$WBAircraftWeight[$i]'>\n";
                echo "<INPUT NAME='ForeCG" . $i . "' TYPE='HIDDEN' VALUE='$WBForeCG[$i]'>\n";
                echo "<INPUT NAME='AftCG" . $i . "' TYPE='HIDDEN' VALUE='$WBAftCG[$i]'>\n";
                echo "<INPUT NAME='AircraftWeight" . ($i + 3) . "' TYPE='HIDDEN' VALUE='" . $WBAircraftWeight[$i + 3] . "'>\n";
                echo "<INPUT NAME='ForeCG" . ($i + 3) . "' TYPE='HIDDEN' VALUE='" . $WBForeCG[$i + 3] . "'>\n";
                echo "<INPUT NAME='AftCG" . ($i + 3) . "' TYPE='HIDDEN' VALUE='" . $WBAftCG[$i + 3] . "'>\n";
            }
        }
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayAircraftFields()
    //
    // Purpose:  Display the aircraft fields for the user to
    //           add or modify the information.
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
        global $AllowAircraftCheckout;

        // set the column sizes
        $Column1Width = "38%";
        $Column2Width = "26%";
        $Column3Width = "36%";
        
        // start the table to display the aircraft flight fields
        echo "<table border=0>";
        
        // start the row (all columns are on one row)
        echo "<tr>";
         
        // fill in the left column information
        echo "<td width=$Column1Width>";
        DisplayAircraftInformation();
        echo "</td>";
         
        // fill in the middle column information
        echo "<td width=$Column2Width>";
        DisplayOwnerInformation();
        DisplayFlightPlanInformation();
        echo "</td>";
        
        // fill in the right column information
        echo "<td width=$Column3Width>";
        DisplayArmsWeightsInformation();
        DisplayCGEnvelopeInformation();
        echo "</td>";
        
        // end the row (all columns are on one row)
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
    }
    
    //********************************************************************
    // LoadNewAircraftValues()
    //
    // Purpose: Load new aircraft's information into the form
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
    function LoadNewAircraftValues()
    {
        global $AircraftRST;
        global $CheckedOutString, $CheckInProgressString;
        global $CheckOutProgressString, $OnLineString, $OffLineString;
        
        include "DatabaseConstants.inc";
                
        // clear the aircraft information
        $AircraftRST[$aircraft_id_offset] = 0;
        $AircraftRST[$n_number_offset] = "";
        $AircraftRST[$serial_number_offset] = 0;
        $AircraftRST[$panel_picture_offset] = "";
        $AircraftRST[$picture_offset] = "";
        $AircraftRST[$hobbs_offset] = 0;
        $AircraftRST[$tach1_offset] = 0;
        $AircraftRST[$tach2_offset] = 0;
        $AircraftRST[$hourly_cost_offset] = 0;
        $AircraftRST[$rental_fee_offset] = 0;
        $AircraftRST[$empty_weight_offset] = 0;
        $AircraftRST[$year_offset] = 0;
        $AircraftRST[$code_id_offset] = 0;
        $AircraftRST[$make_id_offset] = 0;
        $AircraftRST[$model_id_offset] = 0;
        $AircraftRST[$resource_id_offset] = 0;
        $AircraftRST[$description_offset] = "";
        $AircraftRST[$ifr_cert_offset] = 0;
        $AircraftRST[$min_pilot_cert_offset] = "";
        $AircraftRST[$Aircraft_Color_offset] = "";
        $AircraftRST[$Hrs_Till_100_Hr_offset] = 0;
        $AircraftRST[$Hundred_Hr_Tach_offset] = 0;
        $AircraftRST[$Annual_Due_offset] = "now";
        $AircraftRST[$Va_Max_Weight_offset] = 0;
        $AircraftRST[$Current_Hobbs_offset] = 0;
        $AircraftRST[$Current_User_offset] = "";
        $AircraftRST[$CurrentKeycode_offset] = "";
        $AircraftRST[$Aircraft_Owner_Name_offset] = "";
        $AircraftRST[$Aircraft_Owner_Address_offset] = "";
        $AircraftRST[$Aircraft_Owner_City_offset] = "";
        $AircraftRST[$Aircraft_Owner_State_offset] = "";
        $AircraftRST[$Aircraft_Owner_Zip_offset] = "";
        $AircraftRST[$Aircraft_Owner_Contract_offset] = "";
        $AircraftRST[$Aircraft_Owner_Phone1_offset] = "";
        $AircraftRST[$Aircraft_Owner_Phone2_offset] = "";
        $AircraftRST[$Aircraft_Remarks_offset] = "";
        $AircraftRST[$Aircraft_Airspeed_offset] = 0;
        $AircraftRST[$Flight_ID_offset] = "";
        $AircraftRST[$AircraftCleared_By_offset] = "";
        $AircraftRST[$TrackSquawks_offset] = 1;
       
        // for the oil type, select "None"
        $AircraftRST[$Oil_Type_offset] = "None";
        
        // set the default ICAO aircraft information
        $AircraftRST[$ICAO_Equipment_Codes_offset] = "SBG";
        $AircraftRST[$ICAO_Wake_Turb_offset] = "L";
        $AircraftRST[$ICAO_Number_Aircraft_offset] = 1;
        $AircraftRST[$ICAO_Flight_Type_offset] = "G";
        $AircraftRST[$ICAO_ADSB_Type_offset] = "N";
        $AircraftRST[$ICAO_Transponder_offset] = "C";
            
        // set the default  aircraft status
        $AircraftRST[$status_offset] = LookupAircraftStatus($OffLineString);
    
        // set the default weight and balance arm information
        $AircraftRST[$Fuel_Arm_offset] = 0;
        $AircraftRST[$Aux_Fuel_Arm_offset] = 0;
        $AircraftRST[$Front_Seat_Arm_offset] = 0;
        $AircraftRST[$Rear_Seat_1_Arm_offset] = 0;
        $AircraftRST[$Rear_Seat_2_Arm_offset] = 0;
        $AircraftRST[$Baggage_Area_1_Arm_offset] = 0;
        $AircraftRST[$Baggage_Area_2_Arm_offset] = 0;
        $AircraftRST[$Baggage_Area_3_Arm_offset] = 0;
        $AircraftRST[$Aircraft_Arm_offset] = 0;
        
        // clear the aircraft weight information
        $AircraftRST[$max_gross_offset] = 0;
        $AircraftRST[$Aircraft_Weight_offset] = 0;
        $AircraftRST[$Full_Fuel_Gallons_offset] = 0;
        $AircraftRST[$Default_Fuel_Gallons_offset] = 0;
        $AircraftRST[$Aux_Fuel_Gallons_offset] = 0;
        $AircraftRST[$Front_Seat_Weight_offset] = 0;
        $AircraftRST[$Rear_Seat_1_Weight_offset] = 0;
        $AircraftRST[$Rear_Seat_2_Weight_offset] = 0;
        $AircraftRST[$Baggage_Area_1_Weight_offset] = 0;
        $AircraftRST[$Baggage_Area_2_Weight_offset] = 0;
        $AircraftRST[$Baggage_Area_3_Weight_offset] = 0;
        
        // clear the weight, fore CG and aft CG information
        $AircraftRST[$WB_Fields_offset] = "";
    }
                
    //********************************************************************
    // DeleteAircraft($TailNumber)
    //
    // Purpose:  Delete an aircraft from the database.
    //
    // Inputs:
    //   TailNumber - tailnumber of the aircraft to delete
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DeleteAircraft($TailNumber)
    {
    	global $AircraftScheduleType, $InstructorScheduleType;
    	global $day, $month, $year, $resource, $resource_id, $makemodel;
    	global $ImageRootPath;
    	global $old_picture, $old_panel_picture;
        
    	// get the resource id of the aircraft to delete
    	$resource_id = sql_query1("SELECT resource_id FROM AircraftScheduling_aircraft WHERE n_number='$TailNumber'");
    	
    	// get the aircraft id of the aircraft to delete
    	$aircraft_id = sql_query1("SELECT aircraft_id FROM AircraftScheduling_aircraft WHERE n_number='$TailNumber'");
    	
    	// delete any ratings for this aircraft
        DeleteDatabaseRecord(
                            "AircraftScheduling_required_ratings",
                            "aircraft_id=$aircraft_id");  
    	
    	// delete the resource for this aircraft
        DeleteDatabaseRecord(
                            "AircraftScheduling_resource",
                            "item_id=$aircraft_id AND schedulable_id=$AircraftScheduleType");  
    	
    	// delete any entries scheduled for this aircraft
    	if($resource_id > 0) 
    	{
            DeleteDatabaseRecord(
                                "AircraftScheduling_entry",
                                "resource_id=$resource_id");  
            DeleteDatabaseRecord(
                                "AircraftScheduling_repeat",
                                "resource_id=$resource_id");  
    	}
    	
    	// delete the aircraft from the database
    	$sql = "DELETE FROM AircraftScheduling_aircraft WHERE n_number='$TailNumber'";
        DeleteDatabaseRecord(
                            "AircraftScheduling_aircraft",
                            "n_number='$TailNumber'");
                            
        // delete the picture file if it exists
        if (isset($old_picture))
        { 
            @unlink($ImageRootPath . "/" . "$old_picture");
        }
        
        // delete the panel picture file if it exists
        if (isset($old_panel_picture))
        {
            @unlink($ImageRootPath . "/" . "$old_panel_picture");
        }
    	
		// log the delete in to the journal
		$Description = 
					"Deleting aircraft information for aircraft " . $TailNumber;
		CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    }

    //********************************************************************
    // SaveDBWBFields(AircraftWeight, ForeCG, AftCG)
    //
    // Purpose: Save the weight and balance fields to the database.
    //          The fields share a memo field and have the format:
    //            Aircraft Weight, Fore CG Limit, Aft CG Limit;
    //          We will load the fields into an array so that they may
    //          be accessed for weight and balance calculations.
    //
    // Inputs:
    //   AircraftWeight - array of the aircraft weights
    //   ForeCG - array of the aircraft forward CG limit
    //   AftCG - array of the aircraft aft CG limit
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   RuleFields - the updated currency fields ready to be saved in the
    //                database
    // Notes:
    //
    //*********************************************************************
    function SaveDBWBFields($AircraftWeight, $ForeCG, $AftCG)
    {
        // save the tokens to the rule string
        $RuleFields = "";
        for ($i = 0; $i < count($AircraftWeight); $i++)
        {
            $RuleFields = $RuleFields .
                            Str($AircraftWeight[$i]) . "," .
                            Str($ForeCG[$i]) . "," .
                            Str($AftCG[$i]) . ";";
        }
        
        // remove the trailing ";" from the rule string
        if (Len($RuleFields) > 0)
        {
            $RuleFields = Left($RuleFields, Len($RuleFields) - 1);
        }
        
        // return the results
        return $RuleFields;
    }

    //********************************************************************
    // SaveCGEnvelop(CGString as String)
    //
    // Purpose:  Save the CG envelop to the database after verifing it is
    //           ordered correctly
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   CGEnvelop - the value of the CG envelop to save to the database.
    //*********************************************************************
    function SaveCGEnvelop()
    {         
        global $AircraftWeight0;
        global $ForeCG0;
        global $AftCG0;
        global $AircraftWeight1;
        global $ForeCG1;
        global $AftCG1;
        global $AircraftWeight2;
        global $ForeCG2;
        global $AftCG2;
        global $AircraftWeight3;
        global $ForeCG3;
        global $AftCG3;
        global $AircraftWeight4;
        global $ForeCG4;
        global $AftCG4;
        global $AircraftWeight5;
        global $ForeCG5;
        global $AftCG5;
        
        // put the discrete input values into an array so we can build the
        // WB information
        $WBWeightLimit = array();
        $WBWeightLimit[0] = $AircraftWeight0;
        $WBWeightLimit[1] = $AircraftWeight1;
        $WBWeightLimit[2] = $AircraftWeight2;
        $WBWeightLimit[3] = $AircraftWeight3;
        $WBWeightLimit[4] = $AircraftWeight4;
        $WBWeightLimit[5] = $AircraftWeight5;
        $WBForeCGLimit = array();
        $WBForeCGLimit[0] = $ForeCG0;
        $WBForeCGLimit[1] = $ForeCG1;
        $WBForeCGLimit[2] = $ForeCG2;
        $WBForeCGLimit[3] = $ForeCG3;
        $WBForeCGLimit[4] = $ForeCG4;
        $WBForeCGLimit[5] = $ForeCG5;
        $WBAftCGLimit = array();
        $WBAftCGLimit[0] = $AftCG0;
        $WBAftCGLimit[1] = $AftCG1;
        $WBAftCGLimit[2] = $AftCG2;
        $WBAftCGLimit[3] = $AftCG3;
        $WBAftCGLimit[4] = $AftCG4;
        $WBAftCGLimit[5] = $AftCG5;

        // copy the information from the inputs to the WB array in increasing
        // weight order
        $NumWBFieldRecords = 0;
        $AircraftWeight = array();
        $ForeCG = array();
        $AftCG = array();
        while (1)
        {
            $EntryFound = false;
        
            // find the lowest weight in the table (other than zero)
            // we need to do this because the CG calculations require low to
            // high ordering of the fields
            $PreviousWeight = 9999999999;
            for ($i = 0; $i < count($WBWeightLimit); $i++)
            {
                if (Val($WBWeightLimit[$i]) < $PreviousWeight &&
                        Val($WBWeightLimit[$i]) != 0)
                {
                    $PreviousWeight = Val($WBWeightLimit[$i]);
                    $LowestEntry = $i;
                    $EntryFound = true;
                }
            }
                    
            if ($EntryFound)
            {
                // weight entry found, copy it to the table and zero the
                // entry in the screen so we won't find it again
                $AircraftWeight[$NumWBFieldRecords] = Val($WBWeightLimit[$LowestEntry]);
                $ForeCG[$NumWBFieldRecords] = Val($WBForeCGLimit[$LowestEntry]);
                $AftCG[$NumWBFieldRecords] = Val($WBAftCGLimit[$LowestEntry]);
                $WBWeightLimit[$LowestEntry] = "0.0";
                $NumWBFieldRecords = $NumWBFieldRecords + 1;
            }
            else
            {
                // if we didn't find a valid entry, we're done
                break;
            }
        }
        
        // build the rules string
        $CGEnvelop = SaveDBWBFields($AircraftWeight, $ForeCG, $AftCG);
        
        // return the results
        return $CGEnvelop;
    }
        
    //********************************************************************
    // UpdateAircraftInformation($TailNumber)
    //
    // Purpose: Update the aircraft information database from the information
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
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $AddModify;
        
        global $aircraft_id;
        global $serial_number;
        global $panel_picture;
        global $picture;
        global $hobbs;
        global $tach1;
        global $tach2;
        global $hourly_cost;
        global $rental_fee;
        global $empty_weight;
        global $max_gross;
        global $AircraftYear;
        global $code_id;
        global $make_id;
        global $model_id;
        global $resource_id;
        global $description;
        global $ifr_cert;
        global $min_pilot_cert;
        global $status;
        global $Aircraft_Color;
        global $Hrs_Till_100_Hr;
        global $Hundred_Hr_Tach;
        global $Annualday;
        global $Annualmonth;
        global $Annualyear; 
        global $Fuel_Arm;
        global $Default_Fuel_Gallons;
        global $Full_Fuel_Gallons;
        global $Front_Seat_Arm;
        global $Rear_Seat_1_Arm;
        global $Rear_Seat_2_Arm;
        global $Front_Seat_Weight;
        global $Rear_Seat_1_Weight;
        global $Rear_Seat_2_Weight;
        global $Baggage_Area_1_Arm;
        global $Baggage_Area_2_Arm;
        global $Baggage_Area_3_Arm;
        global $Baggage_Area_1_Weight;
        global $Baggage_Area_2_Weight;
        global $Baggage_Area_3_Weight;
        global $Aux_Fuel_Arm;
        global $Aux_Fuel_Gallons;
        global $Aircraft_Arm;
        global $Aircraft_Weight;
        global $Va_Max_Weight;
        global $Current_Hobbs;
        global $Current_User;
        global $CurrentKeycode;
        global $Aircraft_Owner_Name;
        global $Aircraft_Owner_Address;
        global $Aircraft_Owner_City;
        global $Aircraft_Owner_State;
        global $Aircraft_Owner_Zip;
        global $Aircraft_Owner_Contract;
        global $Aircraft_Owner_Phone1;
        global $Aircraft_Owner_Phone2;
        global $Aircraft_Remarks;
        global $Aircraft_Airspeed;
        global $Flight_ID;
        global $Oil_Type;
        global $WB_Fields;
        global $Cleared_By;
        global $schedulable;
        global $TrackSquawks;
        global $ICAO_Equipment_Codes;
        global $ICAO_Wake_Turb;
        global $ICAO_Number_Aircraft;
        global $ICAO_Flight_Type;
        global $ICAO_ADSB_Type;
        global $ICAO_Transponder;
   
        // save the aircraft information
        $DatabaseFields = array();
        SetDatabaseRecord("n_number",
                        UCase(Trim($TailNumber)), $RSConversionString, $DatabaseFields[0]);
        SetDatabaseRecord("model_id",
                        $model_id,
                        $RSConversionNumber, $DatabaseFields[1]);
        SetDatabaseRecord("Oil_Type",
                        UCase($Oil_Type), $RSConversionString, $DatabaseFields[2]);
        SetDatabaseRecord("Aircraft_Owner_Name",
                        $Aircraft_Owner_Name, $RSConversionString, $DatabaseFields[3]);
        SetDatabaseRecord("Aircraft_Owner_Address",
                        $Aircraft_Owner_Address, $RSConversionString, $DatabaseFields[4]);
        SetDatabaseRecord("Aircraft_Owner_City",
                        $Aircraft_Owner_City, $RSConversionString, $DatabaseFields[5]);
        SetDatabaseRecord("Aircraft_Owner_State",
                        $Aircraft_Owner_State, $RSConversionString, $DatabaseFields[6]);
        SetDatabaseRecord("Aircraft_Owner_Zip",
                        $Aircraft_Owner_Zip, $RSConversionString, $DatabaseFields[7]);
        SetDatabaseRecord("Aircraft_Owner_Contract",
                        $Aircraft_Owner_Contract, $RSConversionString, $DatabaseFields[8]);
        SetDatabaseRecord("Aircraft_Owner_Phone1",
                        $Aircraft_Owner_Phone1, $RSConversionString, $DatabaseFields[9]);
        SetDatabaseRecord("Aircraft_Owner_Phone2",
                        $Aircraft_Owner_Phone2, $RSConversionString, $DatabaseFields[10]);
        SetDatabaseRecord("Aircraft_Color",
                        UCase($Aircraft_Color), $RSConversionString, $DatabaseFields[11]);
        SetDatabaseRecord("Flight_ID",
                        UCase($Flight_ID), $RSConversionString, $DatabaseFields[12]);
        SetDatabaseRecord("Va_Max_Weight",
                        $Va_Max_Weight, $RSConversionNumber, $DatabaseFields[13]);
        SetDatabaseRecord("code_id",
                        1, $RSConversionNumber, $DatabaseFields[14]);	// not used - hard code to default code
        SetDatabaseRecord("tach1",
                        $tach1, $RSConversionNumber, $DatabaseFields[15]);
        SetDatabaseRecord("100_Hr_Tach",
                        $Hundred_Hr_Tach, $RSConversionNumber, $DatabaseFields[16]);
        SetDatabaseRecord("Annual_Due", 
                            FormatField(BuildDate($Annualday, $Annualmonth, $Annualyear), "DatabaseDate"), 
                            $RSConversionString, $DatabaseFields[17]);
        SetDatabaseRecord("hourly_cost",
                        GetNumber($hourly_cost), $RSConversionNumber, $DatabaseFields[18]);
        SetDatabaseRecord("rental_fee",
                        GetNumber($rental_fee), $RSConversionNumber, $DatabaseFields[19]);
        SetDatabaseRecord("Current_Hobbs",
                        $Current_Hobbs, $RSConversionNumber, $DatabaseFields[20]);
        SetDatabaseRecord("Current_User",
                        UCase($Current_User), $RSConversionString, $DatabaseFields[21]);
        SetDatabaseRecord("CurrentKeycode",
                        $CurrentKeycode, $RSConversionString, $DatabaseFields[22]);
        SetDatabaseRecord("Cleared_By",
                        $Cleared_By, $RSConversionString, $DatabaseFields[23]);
        SetDatabaseRecord("Aircraft_Remarks",
                        $Aircraft_Remarks, $RSConversionString, $DatabaseFields[24]);
        SetDatabaseRecord("Aircraft_Airspeed",
                        $Aircraft_Airspeed, $RSConversionNumber, $DatabaseFields[25]);
        
        // compute the hours remaining until the 100 hr is due
        SetDatabaseRecord("Hrs_till_100_Hr",
                        (Val($Hundred_Hr_Tach) -
                            Val($tach1)), $RSConversionNumber, $DatabaseFields[26]);
        
        // save aircraft status information
        SetDatabaseRecord("status",
                LookupAircraftStatus($status),
                $RSConversionNumber, $DatabaseFields[27]);
        
        // save the weight and balance arm information
        SetDatabaseRecord("Fuel_Arm",
                        $Fuel_Arm, $RSConversionNumber, $DatabaseFields[28]);
        SetDatabaseRecord("Aux_Fuel_Arm",
                        $Aux_Fuel_Arm, $RSConversionNumber, $DatabaseFields[29]);
        SetDatabaseRecord("Front_Seat_Arm",
                        $Front_Seat_Arm, $RSConversionNumber, $DatabaseFields[30]);
        SetDatabaseRecord("Rear_Seat_1_Arm",
                        $Rear_Seat_1_Arm, $RSConversionNumber, $DatabaseFields[31]);
        SetDatabaseRecord("Rear_Seat_2_Arm",
                        $Rear_Seat_2_Arm, $RSConversionNumber, $DatabaseFields[32]);
        SetDatabaseRecord("Baggage_Area_1_Arm",
                        $Baggage_Area_1_Arm, $RSConversionNumber, $DatabaseFields[33]);
        SetDatabaseRecord("Baggage_Area_2_Arm",
                        $Baggage_Area_2_Arm, $RSConversionNumber, $DatabaseFields[34]);
        SetDatabaseRecord("Baggage_Area_3_Arm",
                        $Baggage_Area_3_Arm, $RSConversionNumber, $DatabaseFields[35]);
        SetDatabaseRecord("Aircraft_Arm",
                        $Aircraft_Arm, $RSConversionNumber, $DatabaseFields[36]);
        
        // save the aircraft weight information
        SetDatabaseRecord("max_gross",
                        $max_gross, $RSConversionNumber, $DatabaseFields[37]);
        SetDatabaseRecord("empty_weight",
                        $empty_weight, $RSConversionNumber, $DatabaseFields[38]);
        SetDatabaseRecord("Full_Fuel_Gallons",
                        $Full_Fuel_Gallons, $RSConversionNumber, $DatabaseFields[39]);
        SetDatabaseRecord("Default_Fuel_Gallons",
                        $Default_Fuel_Gallons, $RSConversionNumber, $DatabaseFields[40]);
        SetDatabaseRecord("Aux_Fuel_Gallons",
                        $Aux_Fuel_Gallons, $RSConversionNumber, $DatabaseFields[41]);
        SetDatabaseRecord("Front_Seat_Weight",
                        $Front_Seat_Weight, $RSConversionNumber, $DatabaseFields[42]);
        SetDatabaseRecord("Rear_Seat_1_Weight",
                        $Rear_Seat_1_Weight, $RSConversionNumber, $DatabaseFields[43]);
        SetDatabaseRecord("Rear_Seat_2_Weight",
                        $Rear_Seat_2_Weight, $RSConversionNumber, $DatabaseFields[44]);
        SetDatabaseRecord("Baggage_Area_1_Weight",
                        $Baggage_Area_1_Weight, $RSConversionNumber, $DatabaseFields[45]);
        SetDatabaseRecord("Baggage_Area_2_Weight",
                        $Baggage_Area_2_Weight, $RSConversionNumber, $DatabaseFields[46]);
        SetDatabaseRecord("Baggage_Area_3_Weight",
                        $Baggage_Area_3_Weight, $RSConversionNumber, $DatabaseFields[47]);
        
        // save the weight, fore CG and aft CG information
        $CGEnvelop = SaveCGEnvelop();
        SetDatabaseRecord("WB_Fields", $CGEnvelop, $RSConversionString, $DatabaseFields[48]);
        
        // save the model information
        SetDatabaseRecord("make_id",
                        $make_id,
                        $RSConversionNumber, $DatabaseFields[49]);
        
        // save the online scheduling information
        SetDatabaseRecord("serial_number",
                        $serial_number, $RSConversionString, $DatabaseFields[50]);
        SetDatabaseRecord("year",
                        $AircraftYear, $RSConversionString, $DatabaseFields[51]);
        if ($panel_picture == "on")
            SetDatabaseRecord("panel_picture", UCase(Trim($TailNumber)) . "_panel.jpg", $RSConversionString, $DatabaseFields[52]);
        else
            SetDatabaseRecord("panel_picture", " ", $RSConversionString, $DatabaseFields[52]);
        if ($picture == "on")
            SetDatabaseRecord("picture", UCase(Trim($TailNumber)) . ".jpg", $RSConversionString, $DatabaseFields[53]);
        else
            SetDatabaseRecord("picture", " ", $RSConversionString, $DatabaseFields[53]);
        if ($ifr_cert == "on")
            SetDatabaseRecord("ifr_cert", 1, $RSConversionNumber, $DatabaseFields[54]);
        else
            SetDatabaseRecord("ifr_cert", 0, $RSConversionNumber, $DatabaseFields[54]);
        
        // save the aircraft description
        SetDatabaseRecord("description", $description, $RSConversionString, $DatabaseFields[55]);
        
        // save the aircraft squawk tracking status
        if ($TrackSquawks == "on")
            SetDatabaseRecord("TrackSquawks", 1, $RSConversionNumber, $DatabaseFields[56]);
        else
            SetDatabaseRecord("TrackSquawks", 0, $RSConversionNumber, $DatabaseFields[56]);
         
        // save the ICAO flight plan information
        SetDatabaseRecord("ICAO_Equipment_Codes", $ICAO_Equipment_Codes, $RSConversionString, $DatabaseFields[57]);
        SetDatabaseRecord("ICAO_Wake_Turb", $ICAO_Wake_Turb, $RSConversionString, $DatabaseFields[58]);
        SetDatabaseRecord("ICAO_Number_Aircraft", $ICAO_Number_Aircraft, $RSConversionNumber, $DatabaseFields[59]);
        SetDatabaseRecord("ICAO_Flight_Type", $ICAO_Flight_Type, $RSConversionString, $DatabaseFields[60]);
        SetDatabaseRecord("ICAO_ADSB_Type", $ICAO_ADSB_Type, $RSConversionString, $DatabaseFields[61]);
        SetDatabaseRecord("ICAO_Transponder", $ICAO_Transponder, $RSConversionString, $DatabaseFields[62]);
     
        // save the database record
        if (UCase($AddModify) == "MODIFY")
        {
            // update the current record
            UpdateDatabaseRecord(
                                "AircraftScheduling_aircraft",
                                $DatabaseFields,
                                "n_number='" . UCase(Trim($TailNumber)) . "'");
        }
        else
        {
            // add a new record
            AddDatabaseRecord(
                                "AircraftScheduling_aircraft",
                                $DatabaseFields);
        }
        
        // set the scheduling status for the aircraft
        if ($schedulable == "on")
            // aircraft is schedulable by the online schedule
            EnableScheduling(UCase(Trim($TailNumber)));
        else
            // aircraft is not schedulable by the online schedule
            DisableScheduling(UCase(Trim($TailNumber)));
            
        // log the change in the journal
        if (UCase($AddModify) == "MODIFY")
            CreateJournalEntry(
                                    strtotime("now"), getUserName(),
                                    "Updating aircraft information for aircraft " .
                                        UCase(Trim($TailNumber)));
        else
            CreateJournalEntry(
                                    strtotime("now"), getUserName(),
                                    "Adding aircraft " .
                                        UCase(Trim($TailNumber)));
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }

    // this script will call itself whenever the submit or Cancel button is pressed
    // we will check here for the update and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $AddModifyAircraft == "Submit")
    {
        // acquire mutex to prevent concurrent aircraft modifications
        if (!sql_mutex_lock('AircraftScheduling_aircraft'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // if we are modifying an existing aircraft, don't check for existing tailnumber
        if (UCase($AddModify) == "MODIFY")
        {
            // modifying an existing aircraft, don't worry if tailnumber is unique
    		$ExistingTailnumber = 0;
        }
        else
        {
            // adding a new aircraft, make sure that the tailnumber is not already
            // in the database
    		$ExistingTailnumber = sql_query1(
    		                            "SELECT COUNT(*) " . 
    		                            "FROM AircraftScheduling_aircraft " .
    		                            "WHERE n_number='$n_number'");
        }
        
        // if the tailnumber already exists in the database, make them choose another
        if ($ExistingTailnumber > 0)
        {
            // username already exists in the database
            $ErrorMessage = $ErrorMessage . "<b>Aircraft tailnumber must be unique<br><br>";
        }
        else
        {   
            // aircraft is being modified or added

            // handle the uploading of a aircraft picture file
			$tmp = $_FILES['picture']['tmp_name'];
            if (is_uploaded_file($tmp)) 
            {
                if(isset($old_picture) && !@unlink($ImageRootPath . "/" . "$old_picture"))
                {
                    // error, unable to delete old picture
                    $ErrorMessage .= "Unable to delete " . $ImageRootPath . "/" . "$old_picture";
                    $picture = "";
                }
                else if(!move_picture_into_position($tmp, $n_number . ".jpg"))
                {
                    // error, unable to copy uploaded file
                    $ErrorMessage .= "Unable to move new picture: " . 
                                        $_FILES['picture']['name'] . 
                                        " to img/$n_number.jpg";
                    $picture = "";
                }
                else 
                {
                    // good upload
                    $picture = "on";
                }
            } 
            else if($delete_picture == "on")
            {
                // don't use the picture file
                // delete the picture file if it exists
                if (isset($old_picture))
                { 
                    @unlink($ImageRootPath . "/" . "$old_picture");
                }
                $picture = "";
            }
            else
            {
                // no changes, leave the picture setting alone
                if (!empty($old_picture))
                {
                    // picture is not being changed
                    $picture = "on";
                }
                else
                {
                    // no picture for this aircraft
                    $picture = "";
                }
            }
            
            // handle the uploading of a aircraft panel picture file
			$tmp = $_FILES['panel_picture']['tmp_name'];
            if(is_uploaded_file($tmp)) 
            {
                if(isset($old_panel_picture) && !@unlink($ImageRootPath . "/" . "$old_panel_picture"))
                {
                    // error, unable to delete old picture
                    $ErrorMessage .= "Unable to delete " . $ImageRootPath . "/" . "$old_panel_picture";
                    $panel_picture = "";
                }
                else if(!move_picture_into_position($tmp, $n_number."_panel.jpg"))
                {
                    // error, unable to copy uploaded file
                    $ErrorMessage .= "Unable to move new picture: " . 
                                        $_FILES['panel_picture']['name'] . 
                                        " to img/$n_number.jpg";
                    $panel_picture = "";
                }
                else 
                {
                    // good upload
                    $panel_picture = "on";
                }
            } 
            else if($delete_panel_picture == "on")
            {
                // don't use the panel picture file
                // delete the panel picture file if it exists
                if (isset($old_panel_picture))
                {
                    @unlink($ImageRootPath . "/" . "$old_panel_picture");
                }
                $panel_picture = "";
            }
            else
            {
                // no changes, leave the panel picture setting alone
                if (!empty($old_panel_picture))
                {
                    // picture is not being changed
                    $panel_picture = "on";
                }
                else
                {
                    // no picture for this aircraft
                    $panel_picture = "";
                }
            }
   
            // save the aircraft information in the database
            UpdateAircraftInformation($n_number);
    
            // updates to the aircraft are complete, take them back to the last screen
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
                        
            // finished with this part of the script
            sql_mutex_unlock('AircraftScheduling_aircraft');
            exit;
        }
        sql_mutex_unlock('AircraftScheduling_aircraft');
    }
    else if(count($_POST) > 0 && $AircraftCancel == "Cancel")
    {
        // user canceled the aircraft changes, take them back to the last screen
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
    else if(count($_POST) > 0 && $AircraftDelete == "Delete")
    {
        // acquire mutex to prevent concurrent aircraft modifications
        if (!sql_mutex_lock('AircraftScheduling_aircraft'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // user is deleting the aircraft — wrap in transaction for atomicity
        sql_begin();
        DeleteAircraft($n_number);
        sql_commit();
        sql_mutex_unlock('AircraftScheduling_aircraft');
        
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

    // neither Submit, Delete or Cancel were selected, display the main screen
    
    // make sure that the TrackSquawks field exists in the database. if the 
    // database is an older version, it may not be there
    $sql = "SELECT TrackSquawks FROM AircraftScheduling_aircraft LIMIT 1";
    if (sql_query1($sql) == -1)
    {
        // value doesn't exist, add it to the database
        $sql = "ALTER TABLE AircraftScheduling_aircraft " .
                "ADD TrackSquawks INT DEFAULT '1' NOT NULL";
		sql_command($sql);
    }
    
    // are we modifying or adding a new aircraft
    if (UCase($AddModify) == "MODIFY")
    {
        // modifying an existing aircraft, get the information from the database
    	$sql = 
    			"SELECT " .
    			    "* " .
    			"FROM " .
    			    "AircraftScheduling_aircraft " .
        		"WHERE " .
        			"n_number='$n_number'";
    	$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // database enquiry successful, get the row data
            $AircraftRST = sql_row($res, 0);
        }
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("AddModifyAircraft", $sql);
        }
    }
    else
    {
        // adding a new aircraft, fill in the default information
        $AircraftRST = array();
        LoadNewAircraftValues();
    }
        
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");

    // start the form
	echo "<FORM NAME='main' enctype='multipart/form-data' ACTION='AddModifyAircraft.php' METHOD='POST'>";

    // start the table to display the aircraft information
    echo "<center>";
    echo "<table border=0>";

    // tell the user what we are doing
    echo "<TR><TD colspan=2>";
    if (UCase($AddModify) == "ADD")
    {
        // adding an aircraft
        echo "<CENTER><H2>Add New Aircraft</H2>";
        echo "Tailnumber: <INPUT NAME='n_number' ID='n_number' TYPE='TEXT' VALUE='' SIZE=8>\n";
        
        // did we have any errors processing the new inputs
        if (len($ErrorMessage) > 0)
        {
            // errors found, show them
            echo "<br>$ErrorMessage";
        }
    }
    else
    {
        echo "<CENTER><H2>Modify Aircraft $n_number Information</H2>";
        echo "<br>";
        echo "<table border=0>";
        echo "</table>";
    }
    echo "</CENTER></TD></TR>";

    // finished with the table
    echo "</table>";
        
    // aircraft information
    echo "<table border=0>";
    echo "<tr>";
    echo "<TD>";
    DisplayAircraftFields();
    echo "</TD></TR>";
    echo "</table>";
    
    // aircraft description
    echo "<br>";
    echo "<table border=1>";
    echo "<tr>";
    echo "<th>Description</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<TD CLASS=TL><TEXTAREA NAME='description' ROWS=8 COLS=50 WRAP='virtual'>";
    echo htmlentities($AircraftRST[$description_offset]); 
    echo "</TEXTAREA></TD>";
    echo "</tr>";
    echo "</table>";
    echo "<br>";
    
    // display the current aircraft squawks and allow modifications if enabled
    if ($AllowSquawkControl)
    {        
        // aircraft squawk tracking
        echo "<br>";
        echo "<INPUT NAME='TrackSquawks' TYPE='checkbox'";
        if ($AircraftRST[$TrackSquawks_offset] == 1) echo "CHECKED";
        echo "> Track Aircraft Squawks";
        echo "<br>";

        // if we are modifying an aircraft, show the current squawks
        if (UCase($AddModify) == "MODIFY")
        {
            require_once("SquawkFunctions.inc");
            $TextRows = 8;
            $TextColumns = 70;
            DisplayAircraftSquawks(
                                    $AircraftRST[$n_number_offset], 
                                    getUserName(), 
                                    $AircraftRST[$tach1_offset],
                                    $TextRows, 
                                    $TextColumns, 
                                    1,
                                    1);
        }
    }
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
    
    // generate the picture upload information
    echo "<br>";
    echo "<table border=0>";
    if(!empty($AircraftRST[$picture_offset]) || !empty($AircraftRST[$panel_picture_offset])) 
    {
        echo "<caption>" . 
             "Note: Picture scale automatically adjusted to 300 pixels in width for " .
             "display on this page. Click on the image to view the full size. " . 
             "It is not necessary to upload the image again if you are satisfied with the following pictures:" .
             "</caption>";
    }
    echo "<tr>";
    echo "<td>Picture:</td>";
    echo "<td>";
    echo "<input type='file' name='picture'>";
    echo "<br>";
    if(!empty($AircraftRST[$picture_offset])) 
    {
        echo "Delete Picture: <input type='checkbox' name='delete_picture'>";
        echo "</td>";
        echo "<td>";
        echo "<a href=\"image.php?src=" . $AircraftRST[$picture_offset] . "\">";
        echo "<img src=\"image.php?src=" . $AircraftRST[$picture_offset] . "&width=300\"></a>";
        echo "<input type=\"hidden\" name=\"old_picture\" value=\"". $AircraftRST[$picture_offset] . "\">";
    }
    echo "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td>Panel:</td>";
    echo "<td>";
    echo "<input type='file' name='panel_picture'>";
    echo "<br>";
    if(!empty($AircraftRST[$panel_picture_offset])) 
    {
        echo "Delete Picture: <input type='checkbox' name='delete_panel_picture'>";
        echo "</td>";
        echo "<td>";
        echo "<a href=\"image.php?src=" . $AircraftRST[$panel_picture_offset] . "\">";
        echo "<img src=\"image.php?src=" . $AircraftRST[$panel_picture_offset] . "&width=300\"></a>";
        echo "<input type=\"hidden\" name=\"old_panel_picture\" value=\"" . $AircraftRST[$panel_picture_offset] . "\">";
    } 
    echo "</td>";
    echo "</tr>";
    echo "</table>";
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='AddModifyAircraft' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelAircraft"] . "')\"></TD>";
    if (UCase($AddModify) == "MODIFY")
        echo "<TD><input name='AircraftDelete' type=submit value='Delete' onClick=\"return confirm('" .  
                    $lang["DeleteAircraft"] . "')\"></TD>";
    echo "</CENTER></TD></TR>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";
    
    // save the input variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var AddModify = '$AddModify';";
    echo "var CurrencyPrefix = '$CurrencyPrefix';";
    echo "</SCRIPT>";
    
    // save the original values for submiting or deleting the form
    if (UCase($AddModify) == "MODIFY")
    {
        echo "<INPUT NAME='n_number' TYPE='HIDDEN' VALUE='$n_number'>\n";
    }
    echo "<INPUT NAME='resource_id' TYPE='HIDDEN' VALUE='" . $AircraftRST[$resource_id_offset] ."'>\n";
    echo "<INPUT NAME='min_pilot_cert' TYPE='HIDDEN' VALUE='" . $AircraftRST[$min_pilot_cert_offset] ."'>\n";
    echo "<INPUT NAME='AddModify' TYPE='HIDDEN' VALUE='" . $AddModify ."'>\n";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
    
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// UpdateAircraftControl(UpdatedControl, TableName)
//
// Purpose: Update the input entry given by UpdatedControl.
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
function UpdateAircraftControl(UpdatedControl, TableName)
{ 
    // get the current value of the updated control
    UpdatedControlValue = parseFloat(document.getElementById(UpdatedControl).value);
    if (isNaN(UpdatedControlValue)) UpdatedControlValue = 0.0;

    // perform the formatting for the control
    switch (UpdatedControl)
    {    
    case "serial_number":
        break;
    case "panel_picture":
        break;
    case "picture":
        break;
    case "hobbs":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "tach1":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "tach2":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "hourly_cost":
        HourlyCost = document.getElementById(UpdatedControl).value;
        if (HourlyCost.substr(0, 1) == CurrencyPrefix)
            HourlyCost = HourlyCost.substr(1);
        HourlyCost = parseFloat(HourlyCost);
        if (isNaN(HourlyCost)) HourlyCost = 0.0;
        document.getElementById(UpdatedControl).value = dollarize(HourlyCost);
        break;
    case "rental_fee":
        RentalFee = document.getElementById(UpdatedControl).value;
        if (RentalFee.substr(0, 1) == CurrencyPrefix)
            RentalFee = RentalFee.substr(1);
        RentalFee = parseFloat(RentalFee);
        if (isNaN(RentalFee)) RentalFee = 0.0;
        document.getElementById(UpdatedControl).value = dollarize(RentalFee);
        break;
    case "empty_weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "max_gross":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AircraftYear":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 0);
        break;
    case "ICAO_Equipment_Codes":
		document.getElementById(UpdatedControl).value = 
                document.getElementById(UpdatedControl).value.toUpperCase();
        break;
   case "ICAO_Wake_Turb":
        break;
	case "ICAO_Flight_Type":
        break;
	case "ICAO_ADSB_Type":
        break;
	case "ICAO_Transponder":
        break;
	case "ICAO_Number_Aircraft":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 0);
         break;
    case "make_id":
        break;
    case "model_id":
        break;
    case "resource_id":
        break;
    case "description":
        break;
    case "ifr_cert":
        break;
    case "min_pilot_cert":
        break;
    case "status":
        break;
    case "Aircraft_Color":
        break;
    case "Hrs_Till_100_Hr":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Hundred_Hr_Tach":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Fuel_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Default_Fuel_Gallons":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Full_Fuel_Gallons":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Front_Seat_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Rear_Seat_1_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Rear_Seat_2_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Front_Seat_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Rear_Seat_1_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Rear_Seat_2_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Baggage_Area_1_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Baggage_Area_2_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Baggage_Area_3_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Baggage_Area_1_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Baggage_Area_2_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Baggage_Area_3_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Aux_Fuel_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Aux_Fuel_Gallons":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Aircraft_Arm":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Aircraft_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Va_Max_Weight":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Current_Hobbs":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "Current_User":
        break;
    case "CurrentKeycode":
        break;
    case "Aircraft_Owner_Name":
        break;
    case "Aircraft_Owner_Address":
        break;
    case "Aircraft_Owner_City":
        break;
    case "Aircraft_Owner_State":
        break;
    case "Aircraft_Owner_Zip":
        break;
    case "Aircraft_Owner_Contract":
        break;
    case "Aircraft_Owner_Phone1":
        break;
    case "Aircraft_Owner_Phone2":
        break;
    case "Aircraft_Remarks":
        break;
    case "Aircraft_Airspeed":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 0);
        break;
    case "Flight_ID":
        break;
    case "Oil_Type":
        break;
    case "WB_Fields":
        break;
    case "Cleared_By":
        break;
    case "AircraftWeight0":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "ForeCG0":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AftCG0":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AircraftWeight1":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "ForeCG1":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AftCG1":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AircraftWeight2":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "ForeCG2":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AftCG2":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AircraftWeight3":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "ForeCG3":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AftCG3":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AircraftWeight4":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "ForeCG4":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AftCG4":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AircraftWeight5":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "ForeCG5":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "AftCG5":
        document.getElementById(UpdatedControl).value = 
                format(UpdatedControlValue, 1);
        break;
    case "TrackSquawks":
        break;
    }
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
    // if we are adding an aircraft, make sure the tailnumber was entered
	
    if (AddModify == "Add")
    {
        // adding an aircraft
        // make sure the tailnumber was entered
        TailNumber = document.getElementById('n_number').value;
        if (TailNumber.length == 0)
        {
            alert("Tailnumber for the new aircraft is required.\n" +
                    "Please enter a value.");
            document.getElementById('n_number').focus();
            
            // error found, don't let them continue
            return false;
        }
    }
	
    // no errors found, return
	return true;
}

//-->
</script>
