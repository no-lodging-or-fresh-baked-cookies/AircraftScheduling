<?php
//-----------------------------------------------------------------------------
// 
// DisplayFlights.php
// 
// PURPOSE: Displays the flights on file to allow selection for modification.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      order_by - parameter to sort the display by
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      makemodel - selected make and model resources
//      n_number - selected aircraft tailnumber
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//    
//      filter parameters
//          FilterName - name to filter the flights on
//          FilterAircraft - aircraft tailnumber to filter the flights on
//          FromDay - start day to filter the flights on
//          FromMonth - start month to filter the flights on
//          FromYear - start year to filter the flights on
//          ToDay - end day to filter the flights on
//          ToMonth - end day to filter the flights on
//          ToYear - end day to filter the flights on
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
    require_once("CurrencyFunctions.inc");
    
    // initialize variables
    $default_flight_days = 30;
    $AllString = "All";
    $FilterName = $AllString;
    $FilterAircraft = $AllString;
	$FromTime = mktime(0, 0, 0, date("m"), date("d") - $default_flight_days, date("Y"));
	$FromDay = date("d", $FromTime);
	$FromMonth = date("m", $FromTime);
	$FromYear = date("Y", $FromTime);
	$ToDay   = date("d");
	$ToMonth = date("m");
	$ToYear  = date("Y");
    $order_by = "Date";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["makemodel"])) $makemodel = $rdata["makemodel"];
    if(isset($rdata["n_number"])) $n_number = $rdata["n_number"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    
    // filter parameters
    if(isset($rdata["FilterName"])) $FilterName = $rdata["FilterName"];
    if(isset($rdata["FilterAircraft"])) $FilterAircraft = $rdata["FilterAircraft"];
    if(isset($rdata["FromDay"])) $FromDay = $rdata["FromDay"];
    if(isset($rdata["FromMonth"])) $FromMonth = $rdata["FromMonth"];
    if(isset($rdata["FromYear"])) $FromYear = $rdata["FromYear"];
    if(isset($rdata["ToDay"])) $ToDay = $rdata["ToDay"];
    if(isset($rdata["ToMonth"])) $ToMonth = $rdata["ToMonth"];
    if(isset($rdata["ToYear"])) $ToYear = $rdata["ToYear"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // BuildFilterOption(
    //                   CurrentlySelected, 
    //                   FilterControlName, 
    //                   SQLResult,
    //                   AddPrivateOption,
    //                   UseSecondEntry)
    //
    // Purpose: Build a drop down box to display the filter parameters.
    //
    // Inputs:
    //   CurrentlySelected - currently selected filter value
    //   FilterControlName - name of the filter control
    //   SQLResult - result of the SQL query that contains the list of
    //              options in row[0]
    //   AddPrivateOption - optional. true to add private as an aircraft
    //              tailnumber selection
    //   UseSecondEntry - optional. true to user row[1] as the display
    //              name in the selection list.
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildFilterOption(
                                $CurrentlySelected, 
                                $FilterControlName, 
                                $SQLResult,
                                $AddPrivateOption = false,
                                $UseSecondEntry = false)
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	global $SpecialAircraftTailnumber;
    
        // filter parameters
        global $AllString;
        global $FilterName;
        global $FilterAircraft;
    	
        // build the select HTML	
		echo "<SELECT NAME='$FilterControlName' id='$FilterControlName' onChange=LoadNewFlights()>";
		
		// put the all parameter in the table
		echo "<OPTION " .
				"VALUE=$AllString" . 
				($CurrentlySelected == $AllString ? " SELECTED" : "") . 
				">$AllString";
		
		// build the selection entries
		for($i=0; $row = sql_row($SQLResult, $i); $i++) 
		{
			echo "<OPTION " .
					"VALUE='" . $row[0] . "'" . 
					(Ucase($row[0]) == UCase($CurrentlySelected) ? " SELECTED" : ""); 
			if ($UseSecondEntry) echo ">$row[1]";
			else echo ">$row[0]";
		}
		
		// should we add the PRIVATE tailnumber?
		if ($AddPrivateOption)
		{
			echo "<OPTION " .
					"VALUE='" . $SpecialAircraftTailnumber . "'" . 
					(Ucase($SpecialAircraftTailnumber) == UCase($CurrentlySelected) ? " SELECTED" : "") . 
					">$SpecialAircraftTailnumber";
		}
		
		// end the selector
		echo "</SELECT>";	
    }
    
    //********************************************************************
    // BuildFlightFilter()
    //
    // Purpose: Display the filter controls for the flights.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   the filter string based on the current filter selections
    //*********************************************************************
    function BuildFlightFilter()
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    
        // filter parameters
        global $AllString;
        global $FilterName;
        global $FilterAircraft;
        global $FromDay;
        global $FromMonth;
        global $FromYear;
        global $ToDay;
        global $ToMonth;
        global $ToYear;
        global $DatabaseNameFormat;
        global $DatabaseNameFormat;
        global $DisplayNameFormat;
    	
        // initialize the filter               
        $BuildFlightFilter = "";

    	// put the filters in a table so we have some control over the selection
        echo "<table>";
   		echo "<TR>";
        
        // build the user name filter
		$sql = 
				"SELECT $DatabaseNameFormat, $DisplayNameFormat " .
				"FROM AircraftScheduling_person " .
				"WHERE user_level != $UserLevelDisabled " .
                "ORDER by last_name";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
    		echo "<TD CLASS=CR><B>Filter flights for </B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($FilterName, "FilterName", $res, false, true);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($FilterName != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildFlightFilter = $BuildFlightFilter .
  			            "AND UPPER($DatabaseNameFormat) = '" . UCase($FilterName) . "' ";
  			}
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildFlightFilter", $sql);
        }
        
        // build the aircraft tailnumber filter
		$sql = 
				"SELECT n_number " .
				"FROM AircraftScheduling_aircraft " .
                "ORDER by n_number";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
    		echo "<TD CLASS=CR><B>for aircraft</B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($FilterAircraft, "FilterAircraft", $res, true);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($FilterAircraft != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildFlightFilter = $BuildFlightFilter .
  			            "AND UPPER(Aircraft) = '" . UCase($FilterAircraft) . "' ";
  			}
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildFlightFilter", $sql);
        }

        // date selectors on a new row
   		echo "</TR>";
   		echo "<TR>";
        
        // generate date selector for from date
		echo "<TD CLASS=CR><B>from </B></TD>";
		echo "<TD CLASS=CL>";
        genDateSelector("From", "main", $FromDay, $FromMonth, $FromYear, "LoadNewFlights", "LoadNewFlights");
		echo "</TD>";
		$StartDate = date("Y-m-d", mktime(0, 0, 0, $FromMonth, $FromDay, $FromYear));
	    $BuildFlightFilter = $BuildFlightFilter .
	            "AND Date >= '" . FormatField($StartDate, "DatabaseDate") . "' ";
        
        // generate date selector for to date
		echo "<TD CLASS=CR><B>to </B></TD>";
		echo "<TD CLASS=CL>";
        genDateSelector("To", "main", $ToDay, $ToMonth, $ToYear, "LoadNewFlights", "LoadNewFlights");
		echo "</TD>";
		$EndDate = date("Y-m-d", mktime(0, 0, 0 , $ToMonth, $ToDay, $ToYear));
	    $BuildFlightFilter = $BuildFlightFilter .
	            "AND Date <= '" . FormatField($EndDate, "DatabaseDate") . "' ";
        
        // complete the table
		echo "</TR>";
        echo "</table>";
        
        return $BuildFlightFilter;
    }

    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
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
	$FromTime = mktime(0, 0, 0, date("m"), date("d") - $default_flight_days, date("Y"));
    if (empty($FilterName)) $FilterName = $AllString;
    if (empty($FilterAircraft)) $FilterAircraft = $AllString;
    if (empty($FromDay)) $FromDay = date("d", $FromTime);
    if (empty($FromMonth)) $FromMonth = date("m", $FromTime);
    if (empty($FromYear)) $FromYear = date("Y", $FromTime);
    if (empty($ToDay)) $ToDay = date("d");
    if (empty($ToMonth)) $ToMonth = date("m");
    if (empty($ToYear)) $ToYear = date("Y");
        
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else { $all=1; $makemodel = "&all=1"; }
    
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
    
    // are we authorized to perform this function?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelSuper))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // start the page
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    // start the form
	echo "<FORM NAME='main'>";
    
    // display the title
    echo "<H2>Flight Management</H2>";

    // if we are not generating a view for print, show the headers
    if ($pview == 0)
    {
        // get the name of the user from the login name if the filter
        // name is all
        if ($FilterName == $AllString)
            $FlightName = getName();
        else
            $FlightName = $FilterName;

        echo "<UL>";
        echo " <LI><b><a href='AddModifyFlights.php" . 
                                "?AddModify=Add" . 
                                "&NameOfUser=$FlightName" . 
                                "&FilterName=$FilterName" .
                                "&FilterAircraft=$FilterAircraft" .
                                "&FromDay=$FromDay" .
                                "&FromMonth=$FromMonth" .
                                "&FromYear=$FromYear" .
                                "&ToDay=$ToDay" .
                                "&ToMonth=$ToMonth" .
                                "&ToYear=$ToYear" .
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                                "'" .
                                ">Add Flight</a></b>";
        echo " <LI><b>Click on a heading to sort</b>";
        echo " <LI><b>Click on a name to edit that flight</b>";
        echo "</UL>";
    }
    
    // build the filter for the flights
    $FlightFilter = BuildFlightFilter();
    
    // buid the SQL for the flights
    $sql = "SELECT " .
    			"$DatabaseNameFormat, " .       // 0
    			"Date, " .                      // 1
    			"Aircraft, " .                  // 2
    			"model, " .                     // 3
    			"ROUND(Begin_Hobbs, 1), " .     // 4
    			"ROUND(End_Hobbs, 1), " .       // 5
    			"ROUND(Begin_Tach, 1), " .      // 6
    			"ROUND(End_Tach, 1), " .        // 7
    			"ROUND(Day_Time, 1), " .        // 8
    			"ROUND(Night_Time, 1), " .      // 9
    			"Day_Landings, " .              // 10
    			"Night_Landings, " .            // 11
    			"Cleared_By, " .                // 12
    			"ROUND(Dual_Time, 1), " .       // 13
    			"ROUND(Dual_PP_Time, 1), " .    // 14
    			"Instructor_Keycode, " .        // 15
    			"Instruction_Type , " .         // 16
    			"Holding_Procedures, " .        // 17
    			"Navigation_Intercepts , " .    // 18
    			"Instrument_Approach , " .      // 19
    			"Local_Fuel, " .                // 20
    			"Cross_Country_Fuel , " .       // 21
    			"Cross_Country_Fuel_Credit, " . // 22
    			"Oil  " .                       // 23
    		"FROM " .
    			"Flight a, " .
    			"AircraftScheduling_person b, " .
    			"AircraftScheduling_model c " .
    		"WHERE " .
    			"a.model_id=c.model_id AND " .
    			"a.keycode=b.username AND " .
                "a.Instruction_Type <> '$InstructorInstruction' " . 
                "$FlightFilter " .  
            "ORDER BY $order_by ";
    $res = sql_query($sql);
        
    // set the column sizes
    $Column1Width = "20%";
    $Column2Width = "12%";
    $Column3Width = "10%";
    $Column4Width = "10%";
    $Column5Width = "7%";
    $Column6Width = "7%";
    $Column7Width = "7%";
    $Column8Width = "7%";
    $Column9Width = "5%";
    $Column10Width = "5%";
    $Column11Width = "5%";
    $Column12Width = "5%";

    // build the script name and link back parameters
    $ScriptName = "DisplayFlights.php" . 
                    "?goback=$goback" . 
                    "&GoBackParameters=$GoBackParameters" .
                    "&FilterName=$FilterName" .
                    "&FilterAircraft=$FilterAircraft" .
                    "&FromDay=$FromDay" .
                    "&FromMonth=$FromMonth" .
                    "&FromYear=$FromYear" .
                    "&ToDay=$ToDay" .
                    "&ToMonth=$ToMonth" .
                    "&ToYear=$ToYear";
       
    // save the script parameters for the javascript procedures
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var order_by = '$order_by';";
    echo "var goback = '$goback';";
    echo "var GoBackParameters = '$GoBackParameters';";
    echo "</SCRIPT>";
    
    // put up the table headers with the links to sort the columns
    echo "<table border=1>";
    echo "<tr>";
    echo " <td align=center width=$Column1Width><a href='$ScriptName&order_by=last_name'>Name</a></td>";
    echo " <td align=center width=$Column2Width><a href='$ScriptName&order_by=Date'>Date</a></td>";
    echo " <td align=center width=$Column3Width><a href='$ScriptName&order_by=Aircraft'>Aircraft</a></td>";
    echo " <td align=center width=$Column4Width><a href='$ScriptName&order_by=model'>Type</a></td>";
    echo " <td align=center width=$Column5Width><a href='$ScriptName&order_by=Begin_Hobbs'>Begin<br>Hobbs</a></td>";
    echo " <td align=center width=$Column6Width><a href='$ScriptName&order_by=End_Hobbs'>End<br>Hobbs</a></td>";
    echo " <td align=center width=$Column7Width><a href='$ScriptName&order_by=Begin_Tach'>Begin<br>Tach</a></td>";
    echo " <td align=center width=$Column8Width><a href='$ScriptName&order_by=End_Tach'>End<br>Tach</a></td>";
    echo " <td align=center width=$Column9Width><a href='$ScriptName&order_by=Day_Time'>Day<br>Time</a></td>";
    echo " <td align=center width=$Column10Width><a href='$ScriptName&order_by=Night_Time'>Night<br>Time</a></td>";
    echo " <td align=center width=$Column11Width><a href='$ScriptName&order_by=Day_Landings'>Day<br>Lnds</a></td>";
    echo " <td align=center width=$Column12Width><a href='$ScriptName&order_by=Night_Landings'>Night<br>Lnds</a></td>";
    echo "</tr>";
         
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {
        // process the results of the database inquiry
		for ($i = 0; ($row = sql_row($res, $i)); $i++) 
		{
			echo "<tr>";
			
			// for all the records that were found in the database
			for($c = 0; $c < 12; $c++) 
			{
			    // format the columns as needed
				switch ($c)
				{
				case 0:
    			    // username column
    			    
    			    // build the parameter list for modifying the flight
                 	$Flightday   = date("d", strtotime($row[1]));
                	$Flightmonth = date("m", strtotime($row[1]));
                	$Flightyear  = date("Y", strtotime($row[1]));
   			        $ModifyParameterList = 
                                "?AddModify=Modify" .
                                "&NameOfUser=" . urlencode($row[0]) .
                                "&TailNumber=$row[2]" .
                                "&AircraftModel=$row[3]" .
                                "&Flightday=$Flightday" .
                                "&Flightmonth=$Flightmonth" .
                                "&Flightyear=$Flightyear" .
                                "&ClearingAuthority=$row[12]" .
                                "&BeginningHobbs=" . RoundToDecimalPlaces($row[4], 1) .
                                "&EndingHobbs=" . RoundToDecimalPlaces($row[5], 1) .
                                "&BeginningTach=" . RoundToDecimalPlaces($row[6], 1) .
                                "&EndingTach=" . RoundToDecimalPlaces($row[7], 1) .
                                "&InstDualTime=" . RoundToDecimalPlaces($row[13], 1) .
                                "&InstPAndP=" . RoundToDecimalPlaces($row[14], 1) .
                                "&InstKeycode=$row[15]" .
                                "&InstType=$row[16]" .
                                "&FlightTimeDay=$row[8]" .
                                "&FlightTimeNight=$row[9]" .
                                "&FlightTimeHolds=$row[17]" .
                                "&FlightTimeNav=$row[18]" .
                                "&FlightTimeInstApp=$row[19]" .
                                "&FlightTimeDayLndg=$row[10]" .
                                "&FlightTimeNightLndg=$row[11]" .
                                "&AircraftLocalFuel=$row[20]" .
                                "&AircraftXCntryFuel=$row[21]" .
                                "&AircraftXCntryFuelCost=$row[22]" .
                                "&AircraftOil=$row[23]" .
                                "&FilterName=$FilterName" .
                                "&FilterAircraft=$FilterAircraft" .
                                "&FromDay=$FromDay" .
                                "&FromMonth=$FromMonth" .
                                "&FromYear=$FromYear" .
                                "&ToDay=$ToDay" .
                                "&ToMonth=$ToMonth" .
                                "&ToYear=$ToYear" .
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);

    			    // display a link to modify the flight
					echo "<td align=left width=$Column1Width>";
					echo "<a href='AddModifyFlights.php$ModifyParameterList'>" .
					            stripslashes($row[$c]) . "</a></td>";
                    break;
				case 1:
    			    // date column
				    echo "<td align=center width=$Column2Width>" . FormatField($row[$c], "Date") . "</td>";
                    break;
				case 2:
    			    // aircraft column
					echo "<td align=center width=$Column3Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
                    break;
				case 3:
    			    // type column
					echo "<td align=center width=$Column4Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
                    break;
				case 4:
    			    // begin hobbs column
					echo "<td align=right width=$Column5Width>" . FormatField($row[$c], "Float") . "</td>";
                    break;
				case 5:
    			    // end hobbs column
					echo "<td align=right width=$Column6Width>" . FormatField($row[$c], "Float") . "</td>";
                    break;
				case 6:
    			    // begin tach column
					echo "<td align=right width=$Column7Width>" . FormatField($row[$c], "Float") . "</td>";
                    break;
				case 7:
    			    // end tach column
					echo "<td align=right width=$Column8Width>" . FormatField($row[$c], "Float") . "</td>";
                    break;
				case 8:
    			    // day time column
					echo "<td align=right width=$Column9Width>" . FormatField($row[$c], "Float") . "</td>";
                    break;
				case 9:
    			    // night time column
					echo "<td align=right width=$Column10Width>" . FormatField($row[$c], "Float") . "</td>";
                    break;
				case 10:
    			    // day landings column
					echo "<td align=right width=$Column11Width>" . FormatField($row[$c], "Integer") . "</td>";
                    break;
				case 11:
    			    // night landings column
					echo "<td align=right width=$Column12Width>" . FormatField($row[$c], "Integer") . "</td>";
                    break;
				default:
                    break;
				}
			}
			echo "</tr>\n";
		}
	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("DisplayFlights.php", $sql);
    }
    
    echo "</table>";
    
    echo "<BR>";


    // if we are not generating a view for print, show the headers
    if ($pview == 0)
    {
        // generate return URL
        GenerateReturnURL(
                            $goback, 
                            CleanGoBackParameters($GoBackParameters));
        
        echo "<br>";
    }

    // end the form
    echo "</FORM>";

    include "trailer.inc" 

?>
<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// LoadNewFlights()
//
// Purpose: Load the flights selected by the filters.
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
function LoadNewFlights()
{        
    // get the current control values
    var FilterName = document.getElementById('FilterName').value;
    var FilterAircraft = document.getElementById('FilterAircraft').value;
    var FromDay = document.getElementById('Fromday').value;
    var FromMonth = document.getElementById('Frommonth').value;
    var FromYear = document.getElementById('Fromyear').value;
    var ToDay = document.getElementById('Today').value;
    var ToMonth = document.getElementById('Tomonth').value;
    var ToYear = document.getElementById('Toyear').value;
    
    // reload the page with the new filter values
    window.location.href = 
                        "DisplayFlights.php" + 
                            "?FilterName=" + FilterName +
                            "&FilterAircraft=" + FilterAircraft +
                            "&FromDay=" + FromDay +
                            "&FromMonth=" + FromMonth +
                            "&FromYear=" + FromYear +
                            "&ToDay=" + ToDay +
                            "&ToMonth=" + ToMonth +
                            "&ToYear=" + ToYear +
                            "&goback=" + goback + 
                            "&GoBackParameters=" + GoBackParameters;
}

//-->
</script>
