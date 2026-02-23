<?php
//-----------------------------------------------------------------------------
// 
// DisplayCharges.php
// 
// PURPOSE: Displays the charges on file to allow selection for modification.
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
//          FilterName - name to filter the charges on
//          FromDay - start day to filter the charges on
//          FromMonth - start month to filter the charges on
//          FromYear - start year to filter the charges on
//          ToDay - end day to filter the charges on
//          ToMonth - end day to filter the charges on
//          ToYear - end day to filter the charges on
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
    $default_charge_days = 30;
    $AllString = "All";
    $FilterName = $AllString;
	$FromTime = mktime(0, 0, 0, date("m"), date("d") - $default_charge_days, date("Y"));
	$FromDay = date("d", $FromTime);
	$FromMonth = date("m", $FromTime);
	$FromYear = date("Y", $FromTime);
	$ToDay   = date("d");
	$ToMonth = date("m");
	$ToYear  = date("Y");
    
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
    //                   UseSecondEntry)
    //
    // Purpose: Build a drop down box to display the filter parameters.
    //
    // Inputs:
    //   CurrentlySelected - currently selected filter value
    //   FilterControlName - name of the filter control
    //   SQLResult - result of the SQL query that contains the list of
    //              options in row[0]
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
                                $UseSecondEntry = false)
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    
        // filter parameters
        global $AllString;
        global $FilterName;
    	
        // build the select HTML	
		echo "<SELECT NAME='$FilterControlName' id='$FilterControlName' onChange=LoadNewCharges()>";
		
		// put the all paramter in the table
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
		echo "</SELECT>";	
    }
    
    //********************************************************************
    // BuildChargeFilter()
    //
    // Purpose: Display the filter controls for the charges.
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
    function BuildChargeFilter()
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
        global $DatabaseNameFormat;
    
        // filter parameters
        global $AllString;
        global $FilterName;
        global $FromDay;
        global $FromMonth;
        global $FromYear;
        global $ToDay;
        global $ToMonth;
        global $ToYear;
        global $DatabaseNameFormat;
        global $DisplayNameFormat;
    	
        // initialize the filter               
        $BuildChargeFilter = "";

    	// put the filters in a table so we have some control over the selection
        echo "<table>";
   		echo "<TR>";
        
        // build the user name filter
		$sql = 
				"SELECT $DatabaseNameFormat, $DisplayNameFormat  " .
				"FROM AircraftScheduling_person " .
				"WHERE user_level != $UserLevelDisabled " .
                "ORDER by last_name";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
    		echo "<TD CLASS=CR><B>Filter charges for </B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($FilterName, "FilterName", $res, true);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($FilterName != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildChargeFilter = $BuildChargeFilter .
  			            "AND UPPER($DatabaseNameFormat) = '" . UCase($FilterName) . "' ";
  			}
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildChargeFilter", $sql);
        }

        // date selectors on a new row
   		echo "</TR>";
   		echo "<TR>";
        
        // generate date selector for from date
		echo "<TD CLASS=CR><B>from </B></TD>";
		echo "<TD CLASS=CL>";
        genDateSelector("From", "main", $FromDay, $FromMonth, $FromYear, "LoadNewCharges", "LoadNewCharges");
		echo "</TD>";
		$StartDate = date("Y-m-d", mktime(0, 0, 0, $FromMonth, $FromDay, $FromYear));
	    $BuildChargeFilter = $BuildChargeFilter .
	            "AND Date >= '" . FormatField($StartDate, "DatabaseDate") . "' ";
        
        // generate date selector for to date
		echo "<TD CLASS=CR><B>to </B></TD>";
		echo "<TD CLASS=CL>";
        genDateSelector("To", "main", $ToDay, $ToMonth, $ToYear, "LoadNewCharges", "LoadNewCharges");
		echo "</TD>";
		$EndDate = date("Y-m-d", mktime(0, 0, 0 , $ToMonth, $ToDay, $ToYear));
	    $BuildChargeFilter = $BuildChargeFilter .
	            "AND Date <= '" . FormatField($EndDate, "DatabaseDate") . "' ";
        
        // complete the table
		echo "</TR>";
        echo "</table>";
        
        return $BuildChargeFilter;
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
    
    // make sure the filter information is set
	$FromTime = mktime(0, 0, 0, date("m"), date("d") - $default_charge_days, date("Y"));
    if (empty($FilterName)) $FilterName = $AllString;
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
    echo "<H2>Charges Management</H2>";

    // if we are not generating a view for print, show the headers
    if ($pview == 0)
    {   
        // get the name of the user from the login name if the filter
        // name is all
        if ($FilterName == $AllString)
            $ChargesName = getName();
        else
            $ChargesName = $FilterName;

        echo "<UL>";
        echo " <LI><b><a href='AddModifyCharges.php" . 
                                "?AddModify=Add" . 
                                "&NameOfUser=$ChargesName" .
                                "&FilterName=$FilterName" .
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
                                ">Add Charge</a></b>";
        echo " <LI><b>Click on a heading to sort</b>";
        echo " <LI><b>Click on a name to edit that charge</b>";
        echo "</UL>";
    }
    
    // build the filter for the charges
    $ChargeFilter = BuildChargeFilter();
    
    // build the SQL for the charges
    if(! isset($order_by)) $order_by = "Date";
    $sql = "SELECT " .
    			"$DatabaseNameFormat, " .                // 0
    			"Date, " .                               // 1
    			"Part_Number, " .                        // 2
    			"Part_Description , " .                  // 3
    			"Quantity, " .                           // 4
    			"Price, " .                              // 5
    			"Total_Price, " .                        // 6
    			"Category, " .                           // 7
    			"Unit_Price  " .                         // 8
    		"FROM " .
    			"Charges a, " .
    			"AircraftScheduling_person b " .
    		"WHERE " .
    			"a.keycode=b.username " .
                "$ChargeFilter " .  
            "ORDER BY $order_by ";
    $res = sql_query($sql);
        
    // set the column sizes
    $Column1Width = "19%";
    $Column2Width = "12%";
    $Column3Width = "10%";
    $Column4Width = "19%";
    $Column5Width = "7%";
    $Column6Width = "7%";
    $Column7Width = "7%";
    $Column8Width = "19%";

    // build the script name and link back parameters
    $ScriptName = "DisplayCharges.php" . 
                    "?goback=$goback" . 
                    "&GoBackParameters=$GoBackParameters" .
                    "&FilterName=$FilterName" .
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
    echo " <td align=center width=$Column2Width><a href='$ScriptName&order_by=Date'>Purchase Date</a></td>";
    echo " <td align=center width=$Column3Width><a href='$ScriptName&order_by=Part_Number'>Part Number</a></td>";
    echo " <td align=center width=$Column4Width><a href='$ScriptName&order_by=Part_Description'>Description</a></td>";
    echo " <td align=center width=$Column5Width><a href='$ScriptName&order_by=Quantity'>Quantity</a></td>";
    echo " <td align=center width=$Column6Width><a href='$ScriptName&order_by=Price'>Retail<br>Price</a></td>";
    echo " <td align=center width=$Column7Width><a href='$ScriptName&order_by=Total_Price'>Total<br>Price</a></td>";
    echo " <td align=center width=$Column8Width><a href='$ScriptName&order_by=Category'>Category</a></td>";
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
    			    
    			    // build the parameter list for modifying the charge
                 	$Chargeday   = date("d", strtotime($row[1]));
                	$Chargemonth = date("m", strtotime($row[1]));
                	$Chargeyear  = date("Y", strtotime($row[1]));
                    $ModifyParameterList = 
                                "?AddModify=Modify" .
                                "&NameOfUser=" . urlencode($row[0]) .
                                "&Chargeday=$Chargeday" .
                                "&Chargemonth=$Chargemonth" .
                                "&Chargeyear=$Chargeyear" .
                                "&PartNumber=" . urlencode($row[2]) .
                                "&PartDescription=" . urlencode($row[3]) . 
                                "&Quantity=$row[4]" .
                                "&Price=$row[5]" .
                                "&TotalPrice=$row[6]" .
                                "&UnitPrice=$row[8]" .
                                "&Category=" . urlencode($row[7]) .
                                "&FilterName=$FilterName" .
                                "&FromDay=$FromDay" .
                                "&FromMonth=$FromMonth" .
                                "&FromYear=$FromYear" .
                                "&ToDay=$ToDay" .
                                "&ToMonth=$ToMonth" .
                                "&ToYear=$ToYear" .
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);

    			    // display a link to modify the charge
					echo "<td align=left width=$Column1Width>";
					echo "<a href='AddModifyCharges.php$ModifyParameterList'>" .
					            stripslashes($row[$c]) . "</a></td>";
                    break;
				case 1:
    			    // purchase date column
				    echo "<td align=center width=$Column2Width>" . FormatField($row[$c], "Date") . "</td>";
                    break;
				case 2:
    			    // part number column
					echo "<td align=center width=$Column3Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
                    break;
				case 3:
    			    // description column
					echo "<td align=center width=$Column4Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
                    break;
				case 4:
    			    // quantity column
					echo "<td align=right width=$Column5Width>" . FormatField($row[$c], "Integer") . "</td>";
                    break;
				case 5:
    			    // retail price column
					echo "<td align=right width=$Column6Width>" . FormatField($row[$c], "Currency") . "</td>";
                    break;
				case 6:
    			    // total price column
					echo "<td align=right width=$Column7Width>" . FormatField($row[$c], "Currency") . "</td>";
                    break;
				case 7:
    			    // category column
					echo "<td align=right width=$Column8Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
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
        DisplayDatabaseError("DisplayCharges.php", $sql);
    }
    
    echo "</table>";
    
    echo "<BR>";

    // if we are not generating a view for print, show the return link
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
// LoadNewCharges()
//
// Purpose: Load the charges selected by the filters.
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
function LoadNewCharges()
{        
    // get the current control values
    var FilterName = document.getElementById('FilterName').value;
    var FromDay = document.getElementById('Fromday').value;
    var FromMonth = document.getElementById('Frommonth').value;
    var FromYear = document.getElementById('Fromyear').value;
    var ToDay = document.getElementById('Today').value;
    var ToMonth = document.getElementById('Tomonth').value;
    var ToYear = document.getElementById('Toyear').value;
    
    // reload the page with the new filter values
    window.location.href = 
                        "DisplayCharges.php" + 
                            "?FilterName=" + FilterName +
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
