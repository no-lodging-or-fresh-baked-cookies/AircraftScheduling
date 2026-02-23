<?php
//-----------------------------------------------------------------------------
// 
// DisplayInventory.php
// 
// PURPOSE: Displays the inventoryto allow selection for modification.
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
//          FromPartNumber - starting partnumber to filter the flights on
//          ToPartNumber - ending partnumber to filter the flights on
//          FromPosition - starting position to filter the flights on
//          ToPosition - ending position to filter the flights on
//          FromCategory - starting category to filter the flights on
//          ToCategory - ending category to filter the flights on
//          InventoryStockLow - set to one to display only the low stock items
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
    $default_inventory_days = 12000;
    $InventoryTypeSelection = "";
    $AllString = "All";
    $FromPartNumber = $AllString;
    $ToPartNumber = $AllString;
    $FromPosition = $AllString;
    $ToPosition = $AllString;
    $FromCategory = $AllString;
    $ToCategory = $AllString;
    $InventoryStockLow = 0;
    
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
    if(isset($rdata["InventoryTypeSelection"])) $InventoryTypeSelection = $rdata["InventoryTypeSelection"];
    if(isset($rdata["FromPartNumber"])) $FromPartNumber = $rdata["FromPartNumber"];
    if(isset($rdata["ToPartNumber"])) $ToPartNumber = $rdata["ToPartNumber"];
    if(isset($rdata["FromPosition"])) $FromPosition = $rdata["FromPosition"];
    if(isset($rdata["ToPosition"])) $ToPosition = $rdata["ToPosition"];
    if(isset($rdata["FromCategory"])) $FromCategory = $rdata["FromCategory"];
    if(isset($rdata["ToCategory"])) $ToCategory = $rdata["ToCategory"];
    if(isset($rdata["InventoryStockLow"])) $InventoryStockLow = $rdata["InventoryStockLow"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // BuildFilterOption(CurrentlySelected, FilterControlName, SQLResult)
    //
    // Purpose: Build a drop down box to display the filter parameters.
    //
    // Inputs:
    //   CurrentlySelected - currently selected filter value
    //   FilterControlName - name of the filter control
    //   SQLResult - result of the SQL query that contains the list of
    //              options in row[0]
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildFilterOption($CurrentlySelected, $FilterControlName, $SQLResult)
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    
        // filter parameters
        global $AllString;
    	
        // build the select HTML	
		echo "<SELECT NAME='$FilterControlName' id='$FilterControlName' onChange=LoadNewInventory()>";
		
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
					(Ucase($row[0]) == UCase($CurrentlySelected) ? " SELECTED" : "") . 
					">$row[0]";
		}
		echo "</SELECT>";	
    }
    
    //********************************************************************
    // BuildInventoryFilter()
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
    function BuildInventoryFilter()
    {
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	global $InventoryTypeSelectionSelection;
    
        // filter parameters
        global $AllString;
        global $FromPartNumber;
        global $ToPartNumber;
        global $FromPosition;
        global $ToPosition;
        global $FromCategory;
        global $ToCategory;
        global $InventoryStockLow;
    	
        // initialize the filter               
        $BuildInventoryFilter = "";

    	// put the filters in a table so we have some control over the selection
        echo "<table>";
        
        // build the part number filters
   		echo "<TR>";
		$sql = 
				"SELECT DISTINCT Part_Number " .
				"FROM Inventory " .
				"WHERE $InventoryTypeSelectionSelection " .
                "ORDER by Part_Number";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // from part number selection
    		echo "<TD CLASS=CR><B>Filter part numbers from</B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($FromPartNumber, "FromPartNumber", $res);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($FromPartNumber != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildInventoryFilter = $BuildInventoryFilter .
  			            "AND Part_Number >= '" . $FromPartNumber . "' ";
  			}
  			
            // to part number selection
       		$res = sql_query($sql);
    		echo "<TD CLASS=CR><B>To</B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($ToPartNumber, "ToPartNumber", $res);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($ToPartNumber != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildInventoryFilter = $BuildInventoryFilter .
  			            "AND Part_Number <= '" . $ToPartNumber . "' ";
  			}
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildInventoryFilter", $sql);
        }
        
        // build the position filters
   		echo "<TR>";
		$sql = 
				"SELECT DISTINCT Position " .
				"FROM Inventory " .
				"WHERE $InventoryTypeSelectionSelection " .
                "ORDER by Position";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // from position selection
    		echo "<TD CLASS=CR><B>Filter position from</B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($FromPosition, "FromPosition", $res);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($FromPosition != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildInventoryFilter = $BuildInventoryFilter .
  			            "AND Position >= '" . $FromPosition . "' ";
  			}
  			
            // to position selection
       		$res = sql_query($sql);
    		echo "<TD CLASS=CR><B>To</B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($ToPosition, "ToPosition", $res);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($ToPosition != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildInventoryFilter = $BuildInventoryFilter .
  			            "AND Position <= '" . $ToPosition . "' ";
  			}
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildInventoryFilter", $sql);
        }
        
        // build the category filters
   		echo "<TR>";
		$sql = 
				"SELECT DISTINCT Category  " .
				"FROM Inventory " .
				"WHERE $InventoryTypeSelectionSelection " .
                "ORDER by Category ";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // from category selection
    		echo "<TD CLASS=CR><B>Filter category from</B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($FromCategory, "FromCategory", $res);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($FromCategory != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildInventoryFilter = $BuildInventoryFilter .
  			            "AND Category >= '" . $FromCategory . "' ";
  			}
  			
            // to category selection
       		$res = sql_query($sql);
    		echo "<TD CLASS=CR><B>To</B></TD>";
    
    		echo "<TD CLASS=CL>";
    		BuildFilterOption($ToCategory, "ToCategory", $res);
  			echo "</TD>";
  			
  			// build the SQL to select the option
  			if ($ToCategory != $AllString)
  			{
  			    // all is not selected, build the filter value
  			    $BuildInventoryFilter = $BuildInventoryFilter .
  			            "AND Category <= '" . $ToCategory . "' ";
  			}
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("BuildInventoryFilter", $sql);
        }
    
        // add in the low inventory field
   		echo "<TR>";
  		echo "<td>&nbsp</td>";
  		if ($InventoryStockLow)
  		{
            // low inventory entered add to the SQL statement
		    $BuildInventoryFilter = $BuildInventoryFilter .
		            "AND Quantity_In_Stock < Reorder_Quantity ";
  			            
  			// build the input control
      		echo "<td>" .
      		        "<input name='InventoryStockLow' " . 
						"id='InventoryStockLow' " .
      		            "type='checkbox' " .
      		            "value=1 " .
      		            "checked " .
      		            "onclick=LoadNewInventory()>" .
      		            "Show Low Quantity Items" . 
      		     "</td>";
      	}
      	else
      	{
            // low inventory not entered
      		echo "<td>" .
      		        "<input name='InventoryStockLow' " . 
						"id='InventoryStockLow' " .
      		            "type='checkbox' " .
      		            "value=1 " .
      		            "onclick=LoadNewInventory()>" .
      		            "Show Low Quantity Items" . 
      		     "</td>";
        }
        
        // complete the table
		echo "</TR>";
        echo "</table>";
        
        return $BuildInventoryFilter;
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
    
    // set the inventory type
    switch (UCase($InventoryTypeSelection))
    {
    case "MAINTENANCE":
        $InventoryTypeSelectionSelection = "Inventory_Type='Maintenance'";
        $InventoryTitle = "Maintenance";
        $Inventory_Type = "Maintenance";
        break;
    case "WHOLESALE":
        $InventoryTypeSelectionSelection = "Inventory_Type='Wholesale'";
        $InventoryTitle = "Wholesale";
        $Inventory_Type = "Wholesale";
        break;
    case "RETAIL":
        $InventoryTypeSelectionSelection = "Inventory_Type='Retail'";
        $InventoryTitle = "Retail";
        $Inventory_Type = "Retail";
        break;
    default:
        $InventoryTypeSelectionSelection = "Inventory_Type='Retail'";
        $InventoryTitle = "Retail";
        $Inventory_Type = "Retail";
        break;
    }
    
    // start the form
	echo "<FORM NAME='main'>";
    
    // display the title
    echo "<H2>$InventoryTitle Inventory Management</H2>";

    // if we are not generating a view for print, show the headers
    if ($pview == 0)
    {
        echo "<UL>";
        echo " <LI><b><a href='AddModifyInventory.php" . 
                                "?AddModify=Add" . 
                                "&InventoryTypeSelection=$InventoryTypeSelection" . 
                                "&Inventory_Type=$Inventory_Type" .
                                "&FromPartNumber=$FromPartNumber" .
                                "&ToPartNumber=$ToPartNumber" .
                                "&FromPosition=$FromPosition" .
                                "&ToPosition=$ToPosition" .
                                "&FromCategory=" . urlencode($FromCategory) .
                                "&ToCategory=" . urlencode($ToCategory) .
                                "&InventoryStockLow=$InventoryStockLow" .
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                                "'" .
                                ">Add Inventory Item</a></b>";
        echo " <LI><b>Click on a heading to sort</b>";
        echo " <LI><b>Click on a date to edit that inventory item</b>";
        echo "</UL>";
    }
    
    // build the filter for the flights
    $InventoryFilter = BuildInventoryFilter();
    
    // buid the SQL for the flights
    if(! isset($order_by)) $order_by = "Part_Number";
    $sql = "SELECT " .
    			"Date, " .                // 0
    			"Part_Number, " .         // 1
    			"Description, " .         // 2
    			"Unit_Price, " .          // 3
    			"Retail_Price, " .        // 4
    			"Quantity_In_Stock, " .   // 5
    			"Reorder_Quantity, " .    // 6
    			"Position, " .            // 7
    			"Category, " .            // 8
    			"Inventory_Type " .       // 9
    		"FROM " .
    			"Inventory " .
    		"WHERE " .
    			"$InventoryTypeSelectionSelection " .
                "$InventoryFilter " .  
            "ORDER BY $order_by ";
    $res = sql_query($sql);
        
    // set the column sizes
    $Column1Width = "15%";
    $Column2Width = "8%";
    $Column3Width = "25%";
    $Column4Width = "5%";
    $Column5Width = "5%";
    $Column6Width = "5%";
    $Column7Width = "5%";
    $Column8Width = "12%";
    $Column9Width = "20%";

    // build the script name and link back parameters
    $ScriptName = "DisplayInventory.php" . 
                    "?InventoryTypeSelection=$InventoryTypeSelection" .
                    "&FromPartNumber=$FromPartNumber" .
                    "&ToPartNumber=$ToPartNumber" .
                    "&FromPosition=$FromPosition" .
                    "&ToPosition=$ToPosition" .
                    "&FromCategory=" . urlencode($FromCategory) .
                    "&ToCategory=" . urlencode($ToCategory) .
                    "&InventoryStockLow=$InventoryStockLow";
       
    // save the script parameters for the javascript procedures
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var order_by = '$order_by';";
    echo "var goback = '$goback';";
    echo "var GoBackParameters = '$GoBackParameters';";
    echo "var InventoryTypeSelection = '$InventoryTypeSelection';";
    echo "</SCRIPT>";
    
    // put up the table headers with the links to sort the columns
    echo "<table border=1>";
    echo "<tr>";
    echo " <td align=center width=$Column1Width><a href='$ScriptName&order_by=Date&goback=$goback&GoBackParameters=$GoBackParameters'>Date</a></td>";
    echo " <td align=center width=$Column2Width><a href='$ScriptName&order_by=Part_Number&goback=$goback&GoBackParameters=$GoBackParameters'>Part Number</a></td>";
    echo " <td align=center width=$Column3Width><a href='$ScriptName&order_by=Description&goback=$goback&GoBackParameters=$GoBackParameters'>Description</a></td>";
    echo " <td align=center width=$Column4Width><a href='$ScriptName&order_by=Unit_Price&goback=$goback&GoBackParameters=$GoBackParameters'>Unit<br>Price</a></td>";
    echo " <td align=center width=$Column5Width><a href='$ScriptName&order_by=Retail_Price&goback=$goback&GoBackParameters=$GoBackParameters'>Retail<br>Price</a></td>";
    echo " <td align=center width=$Column6Width><a href='$ScriptName&order_by=Quantity_In_Stock&goback=$goback&GoBackParameters=$GoBackParameters'>Quantity<br>In Stock</a></td>";
    echo " <td align=center width=$Column7Width><a href='$ScriptName&order_by=Reorder_Quantity&goback=$goback&GoBackParameters=$GoBackParameters'>Reorder<br>Quantity</a></td>";
    echo " <td align=center width=$Column8Width><a href='$ScriptName&order_by=Position&goback=$goback&GoBackParameters=$GoBackParameters'>Position</a></td>";
    echo " <td align=center width=$Column9Width><a href='$ScriptName&order_by=Category&goback=$goback&GoBackParameters=$GoBackParameters'>Category</a></td>";
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
    			    // date column
    			    
    			    // build the parameter list for modifying the inventory item
                 	$Inventoryday   = date("d", strtotime($row[0]));
                	$Inventorymonth = date("m", strtotime($row[0]));
                	$Inventoryyear  = date("Y", strtotime($row[0]));
   			        $ModifyParameterList = 
                                "?AddModify=Modify" .
                                "&Inventoryday=$Inventoryday" .
                                "&Inventorymonth=$Inventorymonth" .
                                "&Inventoryyear=$Inventoryyear" .
                                "&Part_Number=" . urlencode(stripslashes($row[1])) .
                                "&PartDescription=" . urlencode(stripslashes($row[2])) .
                                "&Unit_Price=$row[3]" .
                                "&Retail_Price=$row[4]" .
                                "&Quantity_In_Stock=$row[5]" .
                                "&Reorder_Quantity=$row[6]" .
                                "&Position=" . urlencode(stripslashes($row[7])) .
                                "&Category=" . urlencode(stripslashes($row[8])) .
                                "&Inventory_Type=$row[9]" .
                                "&FromPartNumber=$FromPartNumber" .
                                "&ToPartNumber=$ToPartNumber" .
                                "&FromPosition=$FromPosition" .
                                "&ToPosition=$ToPosition" .
                                "&FromCategory=" . urlencode($FromCategory) .
                                "&ToCategory=" . urlencode($ToCategory) .
                                "&InventoryStockLow=$InventoryStockLow" .
                                "&InventoryTypeSelection=$InventoryTypeSelection" .
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);

    			    // display a link to modify the inventory item
					echo "<td align=left width=$Column1Width>";
					echo "<a href='AddModifyInventory.php$ModifyParameterList'>" .
					            FormatField($row[$c], "Date") . "</a></td>";
                    break;
				case 1:
    			    // Part Number
					echo "<td align=center width=$Column2Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "&nbsp") . "</td>";
                    break;
				case 2:
    			    // Description
					echo "<td align=center width=$Column3Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "&nbsp") . "</td>";
                    break;
				case 3:
    			    // Unit Price
					echo "<td align=right width=$Column4Width>" . FormatField($row[$c], "Currency") . "</td>";
                    break;
				case 4:
    			    // Retail Price
					echo "<td align=right width=$Column5Width>" . FormatField($row[$c], "Currency") . "</td>";
                    break;
				case 5:
    			    // Quantity in stock
					echo "<td align=right width=$Column6Width>" . FormatField($row[$c], "Integer") . "</td>";
                    break;
				case 6:
    			    // Reorder Quantity
					echo "<td align=right width=$Column7Width>" . FormatField($row[$c], "Integer") . "</td>";
                    break;
				case 7:
    			    // Position
					echo "<td align=left width=$Column8Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "&nbsp") . "</td>";
                    break;
				case 8:
    			    // Category
					echo "<td align=left width=$Column9Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "&nbsp") . "</td>";
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
        DisplayDatabaseError("DisplayInventory.php", $sql);
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
// LoadNewInventory()
//
// Purpose: Load the inventory items selected by the filters.
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
function LoadNewInventory()
{        
    // get the current control values
    var FromPartNumber = document.getElementById('FromPartNumber').value;
    var ToPartNumber = document.getElementById('ToPartNumber').value;
    var FromPosition = document.getElementById('FromPosition').value;
    var ToPosition = document.getElementById('ToPosition').value;
    var FromCategory = document.getElementById('FromCategory').value;
    var ToCategory = document.getElementById('ToCategory').value;
    var InventoryStockLow = document.getElementById('InventoryStockLow').checked;
    
    // reload the page with the new filter values
    if (InventoryStockLow) 
        InventoryStockLow = 1;
    else
        InventoryStockLow = 0;
    window.location.href = 
                        "DisplayInventory.php" + 
                            "?InventoryTypeSelection=" + InventoryTypeSelection +
                            "&FromPartNumber=" + FromPartNumber +
                            "&ToPartNumber=" + ToPartNumber +
                            "&FromPosition=" + FromPosition +
                            "&ToPosition=" + ToPosition +
                            "&FromCategory=" + encodeURIComponent(FromCategory) +
                            "&ToCategory=" + encodeURIComponent(ToCategory) +
                            "&InventoryStockLow=" + InventoryStockLow +
                            "&goback=" + goback + 
                            "&GoBackParameters=" + GoBackParameters;
}

//-->
</script>
