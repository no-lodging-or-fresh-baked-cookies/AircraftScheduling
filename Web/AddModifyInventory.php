<?php
//-----------------------------------------------------------------------------
// 
// AddModifyInventory.php
// 
// PURPOSE: Displays the add or modify inventory item screen.
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
//      AddModifyInventory - set to modify to modify a inventory item
//      InventoryCancel - set to Cancel to cancel the update.
//      InventoryDelete - set the Delete to delete a flight
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
    $AddModify = "Add";
    $ErrorMessage = "";
	$debug_flag = 0;
    
    // inventory item information
    $NameOfUser = getName();
	$Inventoryday = date("d");
	$Inventorymonth = date("m");
	$Inventoryyear  = date("Y");
    $Part_Number = "";
    $PartDescription = "";
    $Quantity = 0;
    $Price = 0;
    $TotalPrice = 0;
    $UnitPrice = 0;
    $Category = "";

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
    if(isset($rdata["AddModifyInventory"])) $AddModifyInventory = $rdata["AddModifyInventory"];
    if(isset($rdata["AddModify"])) $AddModify = $rdata["AddModify"];
    if(isset($rdata["InventoryCancel"])) $InventoryCancel = $rdata["InventoryCancel"];
    if(isset($rdata["InventoryDelete"])) $InventoryDelete = $rdata["InventoryDelete"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"]; 
    
    // inventory item information   
    if(isset($rdata["Inventoryday"])) $Inventoryday = $rdata["Inventoryday"];
    if(isset($rdata["Inventorymonth"])) $Inventorymonth = $rdata["Inventorymonth"];
    if(isset($rdata["Inventoryyear"])) $Inventoryyear = $rdata["Inventoryyear"];
    if(isset($rdata["Part_Number"])) $Part_Number = $rdata["Part_Number"];
    if(isset($rdata["PartDescription"])) $PartDescription = $rdata["PartDescription"];
    if(isset($rdata["Unit_Price"])) $Unit_Price = $rdata["Unit_Price"];
    if(isset($rdata["Retail_Price"])) $Retail_Price = $rdata["Retail_Price"];
    if(isset($rdata["Quantity_In_Stock"])) $Quantity_In_Stock = $rdata["Quantity_In_Stock"];
    if(isset($rdata["Reorder_Quantity"])) $Reorder_Quantity = $rdata["Reorder_Quantity"];
    if(isset($rdata["Position"])) $Position = $rdata["Position"];
    if(isset($rdata["Category"])) $Category = $rdata["Category"];
    if(isset($rdata["Inventory_Type"])) $Inventory_Type = $rdata["Inventory_Type"];

    // old record information
    if(isset($rdata["OldInventoryday"])) $OldInventoryday = $rdata["OldInventoryday"];
    if(isset($rdata["OldInventorymonth"])) $OldInventorymonth = $rdata["OldInventorymonth"];
    if(isset($rdata["OldInventoryyear"])) $OldInventoryyear = $rdata["OldInventoryyear"];
    if(isset($rdata["OldPart_Number"])) $OldPart_Number = $rdata["OldPart_Number"];
    if(isset($rdata["OldPartDescription"])) $OldPartDescription = $rdata["OldPartDescription"];
    if(isset($rdata["OldUnit_Price"])) $OldUnit_Price = $rdata["OldUnit_Price"];
    if(isset($rdata["OldRetail_Price"])) $OldRetail_Price = $rdata["OldRetail_Price"];
    if(isset($rdata["OldQuantity_In_Stock"])) $OldQuantity_In_Stock = $rdata["OldQuantity_In_Stock"];
    if(isset($rdata["OldReorder_Quantity"])) $OldReorder_Quantity = $rdata["OldReorder_Quantity"];
    if(isset($rdata["OldPosition"])) $OldPosition = $rdata["OldPosition"];
    if(isset($rdata["OldCategory"])) $OldCategory = $rdata["OldCategory"];
    if(isset($rdata["OldInventory_Type"])) $OldInventory_Type = $rdata["OldInventory_Type"];
    
    // filter parameters (from display inventory screen)
    if(isset($rdata["FromPartNumber"])) $FromPartNumber = $rdata["FromPartNumber"];
    if(isset($rdata["ToPartNumber"])) $ToPartNumber = $rdata["ToPartNumber"];
    if(isset($rdata["FromPosition"])) $FromPosition = $rdata["FromPosition"];
    if(isset($rdata["ToPosition"])) $ToPosition = $rdata["ToPosition"];
    if(isset($rdata["FromCategory"])) $FromCategory = $rdata["FromCategory"];
    if(isset($rdata["ToCategory"])) $ToCategory = $rdata["ToCategory"];
    if(isset($rdata["InventoryStockLow"])) $InventoryStockLow = $rdata["InventoryStockLow"];
    if(isset($rdata["InventoryTypeSelection"])) $InventoryTypeSelection = $rdata["InventoryTypeSelection"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
            
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // LoadCategoryItems(Category)
    //
    // Purpose:  Load the inventory items into the part number control.
    //
    // Inputs:
    //   Category - currently selected category item
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function LoadCategoryItems($Category)
    {
        // build the select HTML	
		echo "<SELECT NAME='Category' id='Category'>";
        
        // load the part numbers into the combo box
        LoadCategories($Category);

  		// finished with the select
  		echo "</SELECT>";	
    }

    //********************************************************************
    // SaveDatabaseInformation()
    //
    // Purpose:  Save any updated information in the dialog to the
    //           database
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
    function SaveDatabaseInformation()
    {
        global $AddModify;
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        
        global $Inventoryday;
        global $Inventorymonth;
        global $Inventoryyear;
        global $Part_Number;
        global $PartDescription;
        global $Unit_Price;
        global $Retail_Price;
        global $Quantity_In_Stock;
        global $Reorder_Quantity;
        global $Position;
        global $Category;
        global $Inventory_Type;

        global $OldInventoryday;
        global $OldInventorymonth;
        global $OldInventoryyear;
        global $OldPart_Number;
        global $OldPartDescription;
        global $OldUnit_Price;
        global $OldRetail_Price;
        global $OldQuantity_In_Stock;
        global $OldReorder_Quantity;
        global $OldPosition;
        global $OldCategory;
        global $OldInventory_Type;
        
        $DatabaseFields = array();
        
        // Inventory items information
        SetDatabaseRecord("Date",
                          FormatField(BuildDate($Inventoryday, $Inventorymonth, $Inventoryyear), "DatabaseDate"), 
                          $RSConversionString, $DatabaseFields[0]);
        SetDatabaseRecord("Part_Number",
                                 $Part_Number, $RSConversionString, $DatabaseFields[1]);
        SetDatabaseRecord("Description",
                                 $PartDescription, $RSConversionString, $DatabaseFields[2]);
        SetDatabaseRecord("Unit_Price",
                                 GetNumber($Unit_Price), $RSConversionNumber, $DatabaseFields[3]);
        SetDatabaseRecord("Retail_Price",
                                 GetNumber($Retail_Price), $RSConversionNumber, $DatabaseFields[4]);
        SetDatabaseRecord("Quantity_In_Stock",
                                 Val($Quantity_In_Stock), $RSConversionNumber, $DatabaseFields[5]);
        SetDatabaseRecord("Reorder_Quantity",
                                 Val($Reorder_Quantity), $RSConversionNumber, $DatabaseFields[6]);
        SetDatabaseRecord("Position",
                                 $Position, $RSConversionString, $DatabaseFields[7]);
        SetDatabaseRecord("Category",
                                 $Category, $RSConversionString, $DatabaseFields[8]);
        SetDatabaseRecord("Inventory_Type",
                                 $Inventory_Type, $RSConversionString, $DatabaseFields[9]);
    
        // save the database record
        if (UCase($AddModify) == "MODIFY")
        {
            // update the current current
            UpdateDatabaseRecord(
                                "Inventory",
                                $DatabaseFields,
                                "(" .
                                    "Date=\"" . FormatField(BuildDate($OldInventoryday, $OldInventorymonth, $OldInventoryyear), "DatabaseDate") . "\" AND " .
                                    "Part_Number=\"" . AddEscapes($OldPart_Number) . "\" AND " .
                                    "Description=\"" . AddEscapes($OldPartDescription) . "\" AND " .
                                    "ROUND(Unit_Price, 2)=" . RoundToDecimalPlaces($OldUnit_Price, 2) . " AND " .
                                    "ROUND(Retail_Price, 2)=" . RoundToDecimalPlaces($OldRetail_Price, 2) . " AND " .
                                    "Quantity_In_Stock=" . $OldQuantity_In_Stock . " AND " .
                                    "Reorder_Quantity=" . $OldReorder_Quantity . " AND " .
                                    "Position=\"" . AddEscapes($OldPosition) . "\" AND " .
                                    "Inventory_Type=\"" . AddEscapes($OldInventory_Type) . "\"" .
                                ")");
        }
        else
        {
            // add a new record
            AddDatabaseRecord("Inventory", $DatabaseFields);
        }
            
        // log the change in the journal
        if (UCase($AddModify) == "MODIFY")
        {
        	$Description = 
                            "Updating inventory part number " . $OldPart_Number;
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
        else
        {
        	$Description = 
                            "Adding inventory part number " . $Part_Number;
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
    }
                
    //********************************************************************
    // DeleteInventory()
    //
    // Purpose:  Delete a inventory item from the database.
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
    function DeleteInventory()
    {
        global $OldPart_Number;
        
        DeleteDatabaseRecord("Inventory", "Part_Number='$OldPart_Number' LIMIT 1");
        
        // log the change in the journal
    	$Description = "Deleting inventory part number " . $OldPart_Number;
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
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

    // build the filter parameters
    $FilterParameter =  "&FromPartNumber=$FromPartNumber" .
                        "&ToPartNumber=$ToPartNumber" .
                        "&FromPosition=$FromPosition" .
                        "&ToPosition=$ToPosition" .
                        "&FromCategory=$FromCategory" .
                        "&ToCategory=$ToCategory" .
                        "&InventoryStockLow=$InventoryStockLow" .
                        "&InventoryTypeSelection=$InventoryTypeSelection" .
                        "&order_by=$order_by";

    // this script will call itself whenever the submit or Cancel button is pressed
    // we will check here for the update and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $AddModifyInventory == "Submit")
    {
        // acquire mutex to prevent concurrent inventory modifications
        if (!sql_mutex_lock('AircraftScheduling_inventory'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // if we are modifying an existing part number, don't check for existing part number
        if (UCase($AddModify) == "MODIFY")
        {
            // modifying an existing part number, don't worry if category is unique
    		$ExistingPartNumber = 0;
        }
        else
        {
            // adding a new part number, make sure that the part number is not already
            // in the database
    		$ExistingPartNumber = sql_query1(
    		                            "SELECT COUNT(*) " . 
    		                            "FROM Inventory " .
    		                            "WHERE Part_Number='$Part_Number'");
        }
        
        // if the part number already exists in the database, make them choose another
        if ($ExistingPartNumber > 0)
        {
            // part number already exists in the database
            $ErrorMessage = $ErrorMessage . "<b>Part number must be unique<br><br>";
        }
        else
        {
            // inventory item is being modified or added
    
            // save the inventory item information in the database
            SaveDatabaseInformation();
    
            // updates to the inventory item are complete, take them back to the last screen
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
            sql_mutex_unlock('AircraftScheduling_inventory');
            exit;
        }
        sql_mutex_unlock('AircraftScheduling_inventory');
    }
    else if(count($_POST) > 0 && $InventoryCancel == "Cancel")
    {
        // user canceled the inventory item changes, take them back to the last screen
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
    else if(count($_POST) > 0 && $InventoryDelete == "Delete") 
    {
        // user is deleting the inventory item
        DeleteInventory(); 
             
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
    	$OldInventoryday = $Inventoryday;
    	$OldInventorymonth = $Inventorymonth;
    	$OldInventoryyear  = $Inventoryyear;
        $OldPart_Number = $Part_Number;
        $OldPartDescription = $PartDescription;
        $OldUnit_Price = $Unit_Price;
        $OldRetail_Price = $Retail_Price;
        $OldQuantity_In_Stock = $Quantity_In_Stock;
        $OldReorder_Quantity = $Reorder_Quantity;
        $OldPosition = $Position;
        $OldCategory = $Category;
        $OldInventory_Type = $Inventory_Type;
    }
    else
    {
     	$OldInventoryday = date("d");
    	$OldInventorymonth = date("m");
    	$OldInventoryyear = date("Y");
        $OldPart_Number = "";
        $OldPartDescription = "";
        $OldUnit_Price = "";
        $OldRetail_Price = "";
        $OldQuantity_In_Stock = "";
        $OldReorder_Quantity = "";
        $OldPosition = "";
        $OldCategory = "";
        $OldInventory_Type = "";
    }
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='AddModifyInventory.php' METHOD='POST'>";

    // start the table to display the inventory item information
    echo "<center>";
    echo "<table border=0>";

    // tell the user what we are doing
    echo "<TR><TD colspan=2>";
    if (UCase($AddModify) == "ADD")
    {
        echo "<CENTER><H2>Add New Inventory Item</H2>";
        
        // did we have any errors processing the new inputs
        if (len($ErrorMessage) > 0)
        {
            // errors found, show them
            echo "<br>$ErrorMessage";
        }
    }
    else
    {
        echo "<CENTER><H2>Modify Inventory Item Information</H2>";
    }
    echo "</CENTER></TD></TR>";
    
    // display the date of the inventory item and the part number
    echo "<tr>";
    echo "<td>Date:";
    genDateSelector("Inventory", "main", $Inventoryday, $Inventorymonth, $Inventoryyear);
    echo "</td>";
    echo "<td align=right>Part Number:";
    echo "<input type=text name='Part_Number' ID='Part_Number' align=right size=9 " . 
            "Onchange='UpdateInventoryControl(\"Part_Number\")' " . 
            "value=\"" . stripslashes($Part_Number) . "\">";
    echo "</td>";
    echo "</tr>";
    
    // display the wholesale price and the retail price
    echo "<tr>";
    echo "<td align=right>Wholesale Price:";
    echo "<input type=text name='Unit_Price' ID='Unit_Price' align=right size=9 " . 
            "Onchange='UpdateInventoryControl(\"Unit_Price\")' " . 
            "value=\"" . FormatField($Unit_Price, "Currency") . "\">";
    echo "</td>";
    echo "<td align=right>Retail Price:";
    echo "<input type=text name='Retail_Price' ID='Retail_Price' align=right size=9 " . 
            "Onchange='UpdateInventoryControl(\"Retail_Price\")' " . 
            "value=\"" . FormatField($Retail_Price, "Currency") . "\">";
    echo "</td>";
    echo "</tr>";
    
    // current quantity and low quantity
    echo "<tr>";
    echo "<td align=right>Current Quantity:";
    echo "<input type=text name='Quantity_In_Stock' ID='Quantity_In_Stock' align=right size=9 " . 
            "Onchange='UpdateInventoryControl(\"Quantity_In_Stock\")' " . 
            "value=\"" . FormatField($Quantity_In_Stock, "Integer") . "\">";
    echo "</td>";
    echo "<td align=right>Low Quantity:";
    echo "<input type=text name='Reorder_Quantity' ID='Reorder_Quantity' align=right size=9 " . 
            "Onchange='UpdateInventoryControl(\"Reorder_Quantity\")' " . 
            "value=\"" . FormatField($Reorder_Quantity, "Integer") . "\">";
    echo "</td>";
    echo "</tr>";
    
    // description
    echo "<tr>";
    echo "<td colspan=2 align=left>Description:";
    echo "<input type=text name='PartDescription' ID='PartDescription' align=left size=50 " . 
            "Onchange='UpdateInventoryControl(\"PartDescription\")' " . 
            "value=\"" . stripslashes($PartDescription) . "\">";
    echo "</td>";
    echo "</tr>";
    
    // position
    echo "<tr>";
    echo "<td colspan=2 align=left>Position:&nbsp;&nbsp;&nbsp;&nbsp";
    echo "<input type=text name='Position' ID='Position' align=left size=50 " . 
            "Onchange='UpdateInventoryControl(\"Position\")' " . 
            "value=\"" . stripslashes($Position) . "\">";
    echo "</td>";
    echo "</tr>";
    
    // category
    echo "<tr>";
    echo "<td colspan=2 align=left>Category:&nbsp;&nbsp;&nbsp;";
    LoadCategoryItems($Category);
    echo "</td>";
    echo "</tr>";
    
    // inventory type (can't change)
    echo "<input name='Inventory_Type' type='hidden' value=\"$Inventory_Type\">\n";
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<input name='goback' type='hidden' value=\"$goback\">\n";
    if(isset($GoBackParameters)) echo "<input name='GoBackParameters' type='hidden' value=\"$GoBackParameters\">\n";
   
    // generate the update and cancel buttons
    echo "<center>";
    echo "<table>";
    echo "<tr>";
    echo "<td><input name='AddModifyInventory' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<td><input name='InventoryCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelInventory"] . "')\"></TD>";
    if (UCase($AddModify) == "MODIFY")
        echo "<td><input name='InventoryDelete' type=submit value='Delete' onClick=\"return confirm('" .  
                    $lang["DeleteInventory"] . "')\"></TD>";
    echo "</center></td></tr>";
    echo "</tr>";
    echo "</table>";
    
    echo "</center>";
    
    // save the input variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var AddModify = '$AddModify';";
    echo "var CurrencyPrefix = '$CurrencyPrefix';";
    echo "</SCRIPT>";
    
    // save the original values for submiting or deleting the form
    echo "<input name='OldInventoryday' type='hidden' value=\"$OldInventoryday\">\n";
    echo "<input name='OldInventorymonth' type='hidden' value=\"$OldInventorymonth\">\n";
    echo "<input name='OldInventoryyear' type='hidden' value=\"$OldInventoryyear\">\n";
    echo "<input name='OldPart_Number' type='hidden' value=\"$OldPart_Number\">\n";
    echo "<input name='OldPartDescription' type='hidden' value=\"$OldPartDescription\">\n";
    echo "<input name='OldUnit_Price' type='hidden' value=\"$OldUnit_Price\">\n";
    echo "<input name='OldRetail_Price' type='hidden' value=\"$OldRetail_Price\">\n";
    echo "<input name='OldQuantity_In_Stock' type='hidden' value=\"$OldQuantity_In_Stock\">\n";
    echo "<input name='OldReorder_Quantity' type='hidden' value=\"$OldReorder_Quantity\">\n";
    echo "<input name='OldPosition' type='hidden' value=\"$OldPosition\">\n";
    echo "<input name='OldCategory' type='hidden' value=\"$OldCategory\">\n";
    echo "<input name='OldInventory_Type' type='hidden' value=\"$OldInventory_Type\">\n";
    echo "<input name='AddModify' type='hidden' value=\"$AddModify\">\n";
    
    // save the filter information
    echo "<INPUT NAME='FromPartNumber' TYPE='HIDDEN' value=\"$FromPartNumber\">\n";
    echo "<INPUT NAME='ToPartNumber' TYPE='HIDDEN' value=\"$ToPartNumber\">\n";
    echo "<INPUT NAME='FromPosition' TYPE='HIDDEN' value=\"$FromPosition\">\n";
    echo "<INPUT NAME='ToPosition' TYPE='HIDDEN' value=\"$ToPosition\">\n";
    echo "<INPUT NAME='FromCategory' TYPE='HIDDEN' value=\"$FromCategory\">\n";
    echo "<INPUT NAME='ToCategory' TYPE='HIDDEN' value=\"$ToCategory\">\n";
    echo "<INPUT NAME='InventoryStockLow' TYPE='HIDDEN' value=\"$InventoryStockLow\">\n";
    echo "<INPUT NAME='InventoryTypeSelection' TYPE='HIDDEN' value=\"$InventoryTypeSelection\">\n";
    echo "<INPUT NAME='order_by' TYPE='HIDDEN' value=\"$order_by\">\n";
    
    // save the debug flag
    echo "<INPUT NAME='debug_flag' TYPE='HIDDEN' value=\"$debug_flag\">\n";
    
    // save the inventory information for the javascript code
    SaveInventoryJavaScriptArrays();
    
    echo "<br>";

    // generate return URL
    GenerateReturnURL(
                        $goback, 
                        CleanGoBackParameters($GoBackParameters));
    
    echo "<br>";
    
    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// UpdateInventoryControl(UpdatedControl)
//
// Purpose: Update the input entry given by UpdatedControl.
//
// Inputs:
//   UpdatedControl - the name of the control that contains the new
//                    entry.
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
function UpdateInventoryControl(UpdatedControl)
{ 
    // perform the formatting for the control
    switch (UpdatedControl)
    {    
    case "Part_Number":
        document.getElementById(UpdatedControl).value = 
                RemoveInvalidChars(document.getElementById(UpdatedControl).value);
        break;
    case "Unit_Price":
        Unit_Price = document.getElementById(UpdatedControl).value;
        if (Unit_Price.substr(0, 1) == CurrencyPrefix)
            Unit_Price = Unit_Price.substr(1);
        Unit_Price = parseFloat(Unit_Price);
        if (isNaN(Unit_Price)) Unit_Price = 0.0;
        document.getElementById(UpdatedControl).value = dollarize(Unit_Price);
        break;
    case "Retail_Price":
        Retail_Price = document.getElementById(UpdatedControl).value;
        if (Retail_Price.substr(0, 1) == CurrencyPrefix)
            Retail_Price = Retail_Price.substr(1);
        Retail_Price = parseFloat(Retail_Price);
        if (isNaN(Retail_Price)) Retail_Price = 0.0;
        document.getElementById(UpdatedControl).value = dollarize(Retail_Price);
        break;
    case "Quantity_In_Stock":
        Quantity_In_Stock = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(Quantity_In_Stock)) Quantity_In_Stock = 0.0;
        document.getElementById(UpdatedControl).value = format(Quantity_In_Stock, 0);
        break;
    case "Reorder_Quantity":
        Reorder_Quantity = parseFloat(document.getElementById(UpdatedControl).value);
        if (isNaN(Reorder_Quantity)) Reorder_Quantity = 0.0;
        document.getElementById(UpdatedControl).value = format(Reorder_Quantity, 0);
        break;
    case "PartDescription":
        document.getElementById(UpdatedControl).value = 
                RemoveInvalidChars(document.getElementById(UpdatedControl).value);
        break;
    case "Position":
        document.getElementById(UpdatedControl).value = 
                RemoveInvalidChars(document.getElementById(UpdatedControl).value);
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
    // make sure that the part number entered is unique if it has changed
    Part_Number = document.getElementById("Part_Number").value;
    OldPart_Number = document.getElementById("OldPart_Number").value;
    if (Part_Number.toUpperCase() != OldPart_Number.toUpperCase())
    {
        for (var i = 0; i < NumInventoryRecords; i++)
        {
            if (Part_Number.toUpperCase() == PartNumberList[i].toUpperCase())
            {
                alert("The part number " + Part_Number + " is already in the database.\n" +
                        "Please enter a new value.");
                document.getElementById('Part_Number').focus();
                document.getElementById('Part_Number').select();
                
                // error found, don't let them continue
                return false;
            }
        }
    }
    
    // if we are adding a new item, make sure the information was entered
    if (AddModify == "Add")
    {
        // adding a new item
        // make sure the part number was entered
        Part_Number = document.getElementById('Part_Number').value;
        if (Part_Number.length == 0)
        {
            alert("Part number for the new inventory item is required.\n" +
                    "Please enter a value.");
            document.getElementById('Part_Number').focus();
            document.getElementById('Part_Number').select();
            
            // error found, don't let them continue
            return false;
        }
        
        // make sure the description was entered
        PartDescription = document.getElementById('PartDescription').value;
        if (PartDescription.length == 0)
        {
            alert("The description for the new inventory item is required.\n" +
                    "Please enter a value.");
            document.getElementById('PartDescription').focus();
            document.getElementById('PartDescription').select();
            
            // error found, don't let them continue
            return false;
        }
    }
       
    // no errors found, return
	return true;
}

//-->
</script>
