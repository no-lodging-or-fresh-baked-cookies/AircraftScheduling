<?php
//-----------------------------------------------------------------------------
// 
// PrintAircraftInformation.php
// 
// PURPOSE: Prints the aircraft reports.
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
//      PrintAircraftInformation - set to submit to print aircraft information
//      AircraftCancel - set to Cancel to cancel the printing.
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
    require_once("DatabaseFunctions.inc");

    // initialize variables
    $all = '';
    $TailNumber = 'All';
    
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
    if(isset($rdata["PrintAircraftInformation"])) $PrintAircraftInformation = $rdata["PrintAircraftInformation"];
    if(isset($rdata["AircraftCancel"])) $AircraftCancel = $rdata["AircraftCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
   
    //********************************************************************
    // PrintFullAircraftInformation(TailNumber)
    //
    // Purpose:  Print all aircraft information for a single aircraft
    //
    // Inputs:
    //   TailNumber - the tailnumber of the aircraft to be printed
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function PrintFullAircraftInformation($TailNumber)
    {
        global $RightJustify, $LeftJustify, $CenterJustify;
        global $AircraftScheduling_company;
        global $NotRepairedString, $RepairedString, $DeferredString, $ClosedString;
        global $WBAircraftWeight, $WBForeCG, $WBAftCG, $NumWBFieldRecords;
        
        // aircraft database table field offsets within the aircraft record
        
        include "DatabaseConstants.inc";
    
        $MaxLineLength = 80;
        $LeftMargin = 10;
        $PageLength = 60;
        $HeaderLength = 3;
        $ColumnWidth1 = 20;
        $ColumnWidth2 = 8;
        $ColumnWidth3 = 15;
        $ColumnWidth4 = 8;
        $ColumnWidth5 = 9;
        $ColumnWidth6 = 9;
        
        // get the aircraft record from the database
    	$sql = 
    			"SELECT " .
    			    "* " .
    			"FROM " .
    			    "AircraftScheduling_aircraft " .
        		"WHERE " .
        			"n_number='$TailNumber'";
    	$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // database enquiry successful, get the row data
            $AircraftRST = sql_row($res, 0);
            
            // load the weight and balance information
            LoadDBWBFields($AircraftRST[$WB_Fields_offset], false);
            
            // build the page header
            $PageHeader = "************ " . $AircraftScheduling_company . " AIRCRAFT ************";
                    
            // printer setup
            PrinterSetup(9);
            
            // build the underline string
            $UnderLine = "";
            for ($i = 0; $i < $MaxLineLength; $i++)
            {
                $UnderLine = $UnderLine . "_";
            }
            
            // skip some space at the top of the form
            for ($i = 0; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
             
            // print the page header
            PrintNonBreakingString(Space($LeftMargin) .
                           JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("Aircraft ID: " . UCase($AircraftRST[$n_number_offset]), 
                          $CenterJustify, $MaxLineLength) .
                          "<br>");
            
            // skip some space below the header
            for ($i = 1 ; $i < $HeaderLength; $i++)
            {
                PrintNonBreakingString(" " . "<br>");
            }
            
            // print the aircraft record header
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("STATUS", $CenterJustify, 11) .
                          JustifyField("TYPE", $CenterJustify, 12) .
                          JustifyField("COLOR", $CenterJustify, 10) .
                          JustifyField("TACH", $CenterJustify, 8) .
                          JustifyField("100 HR", $CenterJustify, 8) .
                          JustifyField("ANNUAL", $CenterJustify, 11) .
                          JustifyField("RATE", $CenterJustify, 10) .
                          JustifyField("HOBBS", $CenterJustify, 8) .
                          "<br>");
           
            // aircraft info
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField(LookupAircraftStatusString($AircraftRST[$status_offset]), $CenterJustify, 11) .
                          JustifyField(
                                LookupAircraftType($AircraftRST[$model_id_offset]) . "/" .
                                LookupAircraftEquipmentCodeString($AircraftRST[$code_id_offset]), $CenterJustify, 12) .
                          JustifyField($AircraftRST[$Aircraft_Color_offset], $CenterJustify, 10) .
                          JustifyField(FormatField($AircraftRST[$tach1_offset], "Float"), $CenterJustify, 8) .
                          JustifyField(FormatField($AircraftRST[$Hrs_Till_100_Hr_offset], "Float"), $CenterJustify, 8) .
                          JustifyField(FormatField($AircraftRST[$Annual_Due_offset], "Date"), $CenterJustify, 11) .
                          JustifyField(FormatField($AircraftRST[$hourly_cost_offset], "Currency"), $CenterJustify, 10) .
                          JustifyField(FormatField($AircraftRST[$Current_Hobbs_offset], "Float"), $CenterJustify, 8) .
                          "<br>");
            // skip space
            PrintNonBreakingString(" " . "<br>");
            
            // current user and keycode (if any)
            PrintNonBreakingString(Space($LeftMargin) .
                          "AIRCRAFT IS CURRENTLY CHECKED OUT TO:" . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("KEYCODE:", $LeftJustify, 8) .
                          JustifyField($AircraftRST[$CurrentKeycode_offset], $LeftJustify, 8) .
                          " " . JustifyField("USER:", $LeftJustify, 6) .
                          JustifyField($AircraftRST[$Current_User_offset], $LeftJustify, 50) . "<br>");
        
            // skip space
            PrintNonBreakingString(" " . "<br>");
                    
            // print the owner's name and address
            PrintNonBreakingString(Space($LeftMargin) .
                           "AIRCRAFT OWNER INFORMATION:" . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                           Space(5) . $AircraftRST[$Aircraft_Owner_Name_offset] . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                           Space(5) . $AircraftRST[$Aircraft_Owner_Address_offset] . "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                           Space(5) . Trim($AircraftRST[$Aircraft_Owner_City_offset]) . " " .
                                       Trim($AircraftRST[$Aircraft_Owner_State_offset]) . " " .
                                       Trim($AircraftRST[$Aircraft_Owner_Zip_offset]) . "<br>");
        
            // skip space
            PrintNonBreakingString(" " . "<br>");
        
            // aircraft weight and balance, CG envelope
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("AIRCRAFT ARMS", $CenterJustify, $MaxLineLength * 0.3333333) .
                          JustifyField("AIRCRAFT CG ENVELOPE", $CenterJustify, $MaxLineLength * 0.666666667) . 
                          "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("STATION", $LeftJustify, $ColumnWidth1) .
                          JustifyField("ARM", $RightJustify, $ColumnWidth2) .
                          Space($ColumnWidth3) .
                          JustifyField("WEIGHT", $RightJustify, $ColumnWidth4) .
                          JustifyField("FORE CG", $RightJustify, $ColumnWidth5) .
                          JustifyField("AFT CG", $RightJustify, $ColumnWidth6) .
                          "<br>");
			$WBLabels = [
				0 => "FUEL",
				1 => "FRONT SEAT",
				2 => "REAR SEAT 1",
				3 => "REAR SEAT 2",
				4 => "BAGGAGE AREA 1",
				5 => "BAGGAGE AREA 2"
			];
			$WBOffsets = [
				0 => $Fuel_Arm_offset,
				1 => $Front_Seat_Arm_offset,
				2 => $Rear_Seat_1_Arm_offset,
				3 => $Rear_Seat_2_Arm_offset,
				4 => $Baggage_Area_1_Arm_offset,
				5 => $Baggage_Area_2_Arm_offset
			];
			foreach($WBLabels as $WBKey => $WBValue)
			{
				if (array_key_exists($WBKey, $WBAircraftWeight))
				{
					// entry exists, print it
					PrintNonBreakingString(Space($LeftMargin) .
								  JustifyField($WBValue, $LeftJustify, $ColumnWidth1) .
								  JustifyField(FormatField($AircraftRST[$WBOffsets[$WBKey]], "Float"), $RightJustify, $ColumnWidth2) .
								  Space($ColumnWidth3) .
								  JustifyField(FormatField(Str($WBAircraftWeight[$WBKey]), "Float"), $RightJustify, $ColumnWidth4) .
								  JustifyField(FormatField(Str($WBForeCG[$WBKey]), "Float"), $RightJustify, $ColumnWidth5) .
								  JustifyField(FormatField(Str($WBAftCG[$WBKey]), "Float"), $RightJustify, $ColumnWidth6) . 
								  "<br>");
				}
				else
				{
					// entry doesn't exist, print defaults
					PrintNonBreakingString(Space($LeftMargin) .
								  JustifyField($WBValue, $LeftJustify, $ColumnWidth1) .
								  JustifyField(FormatField($AircraftRST[$WBOffsets[$WBKey]], "Float"), $RightJustify, $ColumnWidth2) .
								  Space($ColumnWidth3) .
								  JustifyField(FormatField(Str(0), "Float"), $RightJustify, $ColumnWidth4) .
								  JustifyField(FormatField(Str(0), "Float"), $RightJustify, $ColumnWidth5) .
								  JustifyField(FormatField(Str(0), "Float"), $RightJustify, $ColumnWidth6) . 
								  "<br>");
				}
			}
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("BAGGAGE AREA 3", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$Baggage_Area_3_Arm_offset], "Float"), $RightJustify, $ColumnWidth2) . 
                          "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("AIRCRAFT", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$Aircraft_Arm_offset], "Float"), $RightJustify, $ColumnWidth2) . 
                          "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("AUX FUEL ARM", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$Aux_Fuel_Arm_offset], "Float"), $RightJustify, $ColumnWidth2) . 
                          "<br>");
        
            // skip space
            PrintNonBreakingString(" " . "<br>");
            
            // aircraft weight and balance, CG envelope
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("AIRCRAFT WEIGHTS", $CenterJustify, $MaxLineLength * 0.3333333) .
                          JustifyField("AIRCRAFT FUEL", $CenterJustify, $MaxLineLength * 0.666666667) . 
                          "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("AIRCRAFT MAX WEIGHT:", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$max_gross_offset], "Float"), $RightJustify, $ColumnWidth2) .
                          Space($ColumnWidth3) .
                          JustifyField("MAX FUEL (gals):", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$Full_Fuel_Gallons_offset], "Float"), $RightJustify, $ColumnWidth5) . 
                          "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField("AIRCRAFT EMPTY WEIGHT:", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$empty_weight_offset], "Float"), $RightJustify, $ColumnWidth2) .
                          Space($ColumnWidth3) .
                          JustifyField("NORMAL FUEL (gals):", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$Default_Fuel_Gallons_offset], "Float"), $RightJustify, $ColumnWidth5) . 
                          "<br>");
            PrintNonBreakingString(Space($LeftMargin) .
                          JustifyField(" ", $LeftJustify, $ColumnWidth1) .
                          JustifyField(" ", $RightJustify, $ColumnWidth2) .
                          Space($ColumnWidth3) .
                          JustifyField("AUX FUEL (gals):", $LeftJustify, $ColumnWidth1) .
                          JustifyField(FormatField($AircraftRST[$Aux_Fuel_Gallons_offset], "Float"), $RightJustify, $ColumnWidth5) . 
                          "<br>");
            
            // skip space
            PrintNonBreakingString(" " . "<br>");
                                              
            // print the aircraft squawks
            $AircraftID = UCase($AircraftRST[$n_number_offset]);
            
            // create a query to retreive all squawks for this aircraft from the database
        	$SquawkSQL = 
                   "SELECT * FROM Squawks WHERE " .
                  "(Aircraft = '" . Trim($AircraftID) . "') AND " .
                  "(Repair_Date = ' ')";
        	$SquawkResult = sql_query($SquawkSQL);
             
            // if we didn't have any errors, process the results of the database inquiry
            if($SquawkResult) 
            {   
                // see if any squawks were found for this aircraft
                if (sql_count($SquawkResult) == 0)
                {
                    // no records found, print that on the sheet and exit
                    PrintNonBreakingString(Space($LeftMargin) .
                        "NO ACTIVE SQUAWKS FOR THIS AIRCRAFT" . "<br>");
                }
                else
                {
                    // set the line counter to number of lines already printed
                    $TextLines = 31;
                    
                    // put the title in the Squawk display
                    PrintNonBreakingString(Space($LeftMargin) .
                                "OPEN AIRCRAFT SQUAWKS FOR " . Trim($AircraftID) . "<br>");
                    PrintNonBreakingString(Space($LeftMargin) .
                        JustifyField("DATE", $CenterJustify, 12) .
                        JustifyField("REPAIR DATE", $CenterJustify, 13) .
                        JustifyField("GROUNDING", $CenterJustify, 11) .
                        JustifyField("DESCRIPTION", $LeftJustify, 60) . 
                        "<br>");
                                
                    // print the squawk text
                    for ($i = 0; $i < sql_count($SquawkResult); $i++)
                    {
                        // get the information from the database
                        $SquawkRST = sql_row($SquawkResult, $i);
                        
                        // if we have filled the page, send the page to the printer
                        if ($TextLines > $PageLength)
                        {
                            // at max page length, print the page
                            PrintNewPage();
                            $TextLines = 0;
                    
                            // skip some space at the top of the form
                            for ($i = 0; $i < $HeaderLength; $i++)
                            {
                                PrintNonBreakingString(" " . "<br>");
                            }
                
                            // skip some space at the top of the form
                            for ($i = 0; $i < $HeaderLength; $i++)
                            {
                                PrintNonBreakingString(" " . "<br>");
                            }
                             
                            // print the page header
                            PrintNonBreakingString(Space($LeftMargin) .
                                           JustifyField($PageHeader, $CenterJustify, $MaxLineLength) . 
                                           "<br>");
                            PrintNonBreakingString(Space($LeftMargin) .
                                          JustifyField("Aircraft ID: " . UCase($AircraftRST[$n_number_offset]), 
                                                    $CenterJustify, $MaxLineLength) . 
                                          "<br>");
                            
                            // skip some space below the header
                            for ($i = 0; $i < $HeaderLength; $i++)
                            {
                                PrintNonBreakingString(" " . "<br>");
                            }
                                                  
                            // print the aircraft squawks header
                            PrintNonBreakingString(Space($LeftMargin) .
                                        "OPEN AIRCRAFT SQUAWKS FOR " . Trim($AircraftID) . 
                                        "<br>");
                            PrintNonBreakingString(Space($LeftMargin) .
                                JustifyField("DATE", $CenterJustify, 12) .
                                JustifyField("REPAIR DATE", $CenterJustify, 13) .
                                JustifyField("GROUNDING", $CenterJustify, 11) .
                                JustifyField("DESCRIPTION", $LeftJustify, 60) . 
                                "<br>");
                        }
                        
                        // date of entry
                        $SquawkField =
                                JustifyField(FormatField($SquawkRST[$SquawkDate_offset], "Date"), $CenterJustify, 12);
                        
                        // if the repair date is blank, output a blank otherwise output the date
                        if (Len(Trim($SquawkRST[$SquawkRepair_Date_offset])) == 0 ||
                            UCase(Trim($SquawkRST[$SquawkRepair_Date_offset])) == $DeferredString ||
                            UCase(Trim($SquawkRST[$SquawkRepair_Date_offset])) == $ClosedString)
                        {
                            // invalid date, print the string
                            $SquawkField = $SquawkField .
                                JustifyField($SquawkRST[$SquawkRepair_Date_offset], $CenterJustify, 12);
                        }
                        else
                        {
                            // valid date, print it
                            $SquawkField = $SquawkField .
                                JustifyField(FormatField($SquawkRST[$SquawkRepair_Date_offset], "Date"), $CenterJustify, 13);
                        }
                        
                        // grounding status and description
                        $SquawkField = $SquawkField .
                            JustifyField(FormatField($SquawkRST[$SquawkGrounding_offset], "YesNo"), $CenterJustify, 11) .
                            JustifyField($SquawkRST[$SquawkDescription_offset], $LeftJustify, 60);
                    
                        // print the record
                        PrintNonBreakingString(Space($LeftMargin) .
                            $SquawkField . "<br>");
                        $TextLines = $TextLines + 1;
                    }
                }
            }
        	else 
            {
                // error processing database request, tell the user
                DisplayDatabaseError("PrintFullAircraftInformation Squawk", $SquawkSQL);
            }
        }
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("PrintFullAircraftInformation", $sql);
        }
    }
 
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }

    // this script will call itself whenever the submit or cancel button is pressed
    // we will check here for the checkout and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $PrintAircraftInformation == "Submit") 
    {
        // submit button was selected

        // updates to the charge are complete, take them back to the last screen
        // after the confirmation sheet is printed
        if(isset($goback))
        {
            // goback is set, take them back there
            if (!empty($GoBackParameters))
                // goback parameters set, use them
                $ReturnURL = $goback . CleanGoBackParameters($GoBackParameters);
            else
                // goback parameters not set, use the default
        	    $ReturnURL = $goback . "?" .
        	                "day=$day&month=$month&year=$year" .
        	                "&resource=$resource" .
        	                "&resource_id=$resource_id" .
        	                "&InstructorResource=$InstructorResource" .
        	                "$makemodel";
        }
        else
        {
            // goback is not set, use the default
        	$ReturnURL = "index.php?" .
                        "day=$day&month=$month&year=$year" .
                        "&resource=$resource" .
                        "&resource_id=$resource_id" .
                        "&InstructorResource=$InstructorResource" .
                        "$makemodel";
        }
        
        // include the print functions here so that the javascript won't
        // interfer with the header functions
        require_once("PrintFunctions.inc");
        
        // setup the print functions
        SetupPrintFunctions($ReturnURL);
        
        // if the user has selected all, print all aircraft
        if (UCase($TailNumber) == "ALL")
        {
            // print all aircraft
            // get the database information for the aircraft
            $sql = "SELECT n_number " . 
                   "FROM AircraftScheduling_aircraft  "  . 
                   "ORDER BY n_number";
            $res = sql_query($sql);
            
            // if we have anything to print
            if ($res)
            {
                // print all the tailnumbers
                for ($i = 0; ($row = sql_row($res, $i)); $i++)
                {
                    PrintFullAircraftInformation($row[0]);
                    if ($i != sql_count($res)) PrintNewPage();
                }
            }
            else
            {
                // error processing database request, tell the user
                DisplayDatabaseError("PrintAircraftInformation", $sql);
            }
        }
        else
        {
            // print a single aircraft
            PrintFullAircraftInformation($TailNumber);
        }
        
        // finish the print form
        CompletePrintFunctions();
                    
        // finished with this part of the script
        exit;
    }
    else if(count($_POST) > 0 && $AircraftCancel == "Cancel") 
    {
        // user canceled the Submit, take them back to the last screen
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

    // neither submit or cancel were selected display the main screen
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='PrintAircraftInformation.php' METHOD='POST'>";

    // start the table to display the aircraft report information
    echo "<center>";
    echo "<h2>Print Aircraft Information</h2>";
    echo "<table border=0>";

    // display the aircraft selection drop down box
    echo "<TR>";
    echo "<TD><CENTER><B>Print Information For Aircraft: </B>";
    $TailNumber = DisplayAircraftDropDown($TailNumber, false, true, "1=1");
    echo "</CENTER></TD></TR>";

    // finished with the table
    echo "</table>";
            
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='PrintAircraftInformation' type=submit value='Submit'></TD>";
    echo "<TD><input name='AircraftCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelPrint"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>
