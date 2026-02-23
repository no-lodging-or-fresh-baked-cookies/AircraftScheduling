<?php
//-----------------------------------------------------------------------------
// 
// AddModifyCharges.php
// 
// PURPOSE: Displays the add or modify charges screen.
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
//      AddModifyCharges - set to modify to modify a charge
//      ChargeCancel - set to Cancel to cancel the update.
//      ChargeDelete - set the Delete to delete a flight
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
    
    // charge information
    $NameOfUser = getName();
	$Chargeday = date("d");
	$Chargemonth = date("m");
	$Chargeyear  = date("Y");
    $PartNumber = "";
    $PartDescription = "";
    $Quantity = 1;
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
    if(isset($rdata["AddModifyCharges"])) $AddModifyCharges = $rdata["AddModifyCharges"];
    if(isset($rdata["AddModify"])) $AddModify = $rdata["AddModify"];
    if(isset($rdata["ChargeCancel"])) $ChargeCancel = $rdata["ChargeCancel"];
    if(isset($rdata["ChargeDelete"])) $ChargeDelete = $rdata["ChargeDelete"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"]; 
    
    // charge information   
    if(isset($rdata["NameOfUser"])) $NameOfUser = $rdata["NameOfUser"];
    if(isset($rdata["Chargeday"])) $Chargeday = $rdata["Chargeday"]; 
    if(isset($rdata["Chargemonth"])) $Chargemonth = $rdata["Chargemonth"]; 
    if(isset($rdata["Chargeyear"])) $Chargeyear = $rdata["Chargeyear"]; 
    if(isset($rdata["PartNumber"])) $PartNumber = $rdata["PartNumber"]; 
    if(isset($rdata["PartDescription"])) $PartDescription = $rdata["PartDescription"]; 
    if(isset($rdata["Quantity"])) $Quantity = $rdata["Quantity"]; 
    if(isset($rdata["Price"])) $Price = $rdata["Price"]; 
    if(isset($rdata["TotalPrice"])) $TotalPrice = $rdata["TotalPrice"]; 
    if(isset($rdata["UnitPrice"])) $UnitPrice = $rdata["UnitPrice"]; 
    if(isset($rdata["Category"])) $Category = $rdata["Category"]; 

    // old record information
    if(isset($rdata["OldMemberKeyCode"])) $OldMemberKeyCode = $rdata["OldMemberKeyCode"]; 
    if(isset($rdata["OldChargeDate"])) $OldChargeDate = $rdata["OldChargeDate"]; 
    if(isset($rdata["OldPartNumber"])) $OldPartNumber = $rdata["OldPartNumber"]; 
    if(isset($rdata["OldPartDescription"])) $OldPartDescription = $rdata["OldPartDescription"]; 
    if(isset($rdata["OldQuantity"])) $OldQuantity = $rdata["OldQuantity"]; 
    if(isset($rdata["OldPrice"])) $OldPrice = $rdata["OldPrice"]; 
    if(isset($rdata["OldTotalPrice"])) $OldTotalPrice = $rdata["OldTotalPrice"]; 
    if(isset($rdata["OldUnitPrice"])) $OldUnitPrice = $rdata["OldUnitPrice"]; 
    if(isset($rdata["OldCategory"])) $OldCategory = $rdata["OldCategory"]; 
    
    // filter parameters (from display charges screen)
    if(isset($rdata["FilterName"])) $FilterName = $rdata["FilterName"];
    if(isset($rdata["FromDay"])) $FromDay = $rdata["FromDay"];
    if(isset($rdata["FromMonth"])) $FromMonth = $rdata["FromMonth"];
    if(isset($rdata["FromYear"])) $FromYear = $rdata["FromYear"];
    if(isset($rdata["ToDay"])) $ToDay = $rdata["ToDay"];
    if(isset($rdata["ToMonth"])) $ToMonth = $rdata["ToMonth"];
    if(isset($rdata["ToYear"])) $ToYear = $rdata["ToYear"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
            
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // LoadInventoryItems(PartNumber)
    //
    // Purpose:  Load the inventory items into the part number control.
    //
    // Inputs:
    //   PartNumber - currently selected inventory item
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function LoadInventoryItems($PartNumber)
    {
        // build the select HTML	
		echo "<SELECT NAME='PartNumber' id='PartNumber' Onchange='UpdateInventoryControl()'>";
        
        // load the part numbers into the combo box
        LoadPartNumbers($PartNumber, 15);

  		// finished with the select
  		echo "</SELECT>";	
    }
    
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
        LoadCategories($Category, 50);

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
        global $OldMemberKeyCode;
        global $OldChargeDate; 
        global $OldPartNumber; 
        global $OldPartDescription; 
        global $OldQuantity; 
        global $OldPrice; 
        global $OldTotalPrice; 
        global $OldUnitPrice; 
        global $OldCategory; 
        global $NameOfUser;
        global $Chargeday; 
        global $Chargemonth; 
        global $Chargeyear; 
        global $PartNumber; 
        global $PartDescription; 
        global $Quantity; 
        global $Price; 
        global $TotalPrice; 
        global $UnitPrice; 
        global $Category; 
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        
        $DatabaseFields = array();
        
        // Charges information
        SetDatabaseRecord("Keycode",
                           GetUsernameFromName($NameOfUser), $RSConversionString, $DatabaseFields[0]);
        SetDatabaseRecord("Date",
                          FormatField(BuildDate($Chargeday, $Chargemonth, $Chargeyear), "DatabaseDate"), 
                          $RSConversionString, $DatabaseFields[1]);
        SetDatabaseRecord("Part_Number",
                          $PartNumber, $RSConversionString, $DatabaseFields[2]);
        SetDatabaseRecord("Part_Description",
                          $PartDescription, $RSConversionString, $DatabaseFields[3]);
        SetDatabaseRecord("Price",
                          GetNumber($Price), $RSConversionNumber, $DatabaseFields[4]);
        SetDatabaseRecord("Unit_Price",
                          GetNumber($UnitPrice), $RSConversionNumber, $DatabaseFields[5]);
        SetDatabaseRecord("Quantity",
                          Val($Quantity), $RSConversionNumber, $DatabaseFields[6]);
        SetDatabaseRecord("Total_Price",
                          GetNumber($Price) * Val($Quantity), 
                          $RSConversionNumber, $DatabaseFields[7]);
        SetDatabaseRecord("Category",
                          $Category, $RSConversionString, $DatabaseFields[8]);
    
        // save the database record
        if (UCase($AddModify) == "MODIFY")
        {
            // update the current current
            UpdateDatabaseRecord(
                        "Charges",
                        $DatabaseFields,
                        "(" .
                         "Keycode='" . $OldMemberKeyCode . "' AND " .
                         "Date='" . FormatField($OldChargeDate, "DatabaseDate") . "' AND " .
                         "Part_Number='" . $OldPartNumber . "' AND " .
                         "Part_Description='" . $OldPartDescription . "' AND " .
                         "Quantity=" . $OldQuantity . " AND " .
                         "Category='" . $OldCategory . "'" .
                        ")");
        }
        else
        {
            // add a new record
            AddDatabaseRecord("Charges", $DatabaseFields);
        }
            
        // log the change in the journal
        if (UCase($AddModify) == "MODIFY")
        {
        	$Description = 
                            "Changing charge for part number " . $PartNumber .
                            " on date " . FormatField(BuildDate($Chargeday, $Chargemonth, $Chargeyear), "Date") .
                            " quantity " . $Quantity .
                            " for " . GetUsernameFromName($NameOfUser) .
                            " (" . $NameOfUser . ")";
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
        else
        {
        	$Description = 
                            "Adding charge for part number " . $PartNumber .
                            " on date " . FormatField(BuildDate($Chargeday, $Chargemonth, $Chargeyear), "Date") .
                            " quantity " . $Quantity .
                            " for " . GetUsernameFromName($NameOfUser) .
                            " (" . $NameOfUser . ")";
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
    }
                
    //********************************************************************
    // DeleteCharge()
    //
    // Purpose:  Delete a charge from the database.
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
    function DeleteCharge()
    {
        global $OldMemberKeyCode;
    	global $OldChargeDate;
        global $OldPartNumber;
        global $OldPartDescription;
        global $OldQuantity;
        global $OldPrice;
        global $OldTotalPrice;
        global $OldUnitPrice;
        global $OldCategory;
        
        // user responded yes, delete the record
        DeleteDatabaseRecord("Charges",
            "(" .
                "Keycode='" . $OldMemberKeyCode . "' AND " .
                "Date='" . FormatField($OldChargeDate, "DatabaseDate") . "' AND " .
                "Part_Number='$OldPartNumber' AND " .
                "Part_Description='$OldPartDescription' AND " .
                "Quantity='$OldQuantity' AND " .
                "ROUND(Price,2)=" . RoundToDecimalPlaces($OldPrice, 2) . " AND " .
                "ROUND(Total_Price,2)=" . RoundToDecimalPlaces($OldTotalPrice, 2) . " AND " .
                "ROUND(Unit_Price,2)=" . RoundToDecimalPlaces($OldUnitPrice, 2) . " AND " .
                "Category='$OldCategory'" .
            ") LIMIT 1");

        // log the change in the journal
    	$Description = 
                        "Deleting charge for part number " . $OldPartNumber . 
                        " for " . $OldMemberKeyCode .
                        " (" . GetNameFromUsername($OldMemberKeyCode) . ")" .
                        " on date " . FormatField($OldChargeDate, "Date");
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
    $FilterParameter = "&FilterName=$FilterName" . 
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
    if(count($_POST) > 0 && $AddModifyCharges == "Submit")
    {
        // acquire mutex to prevent concurrent charge modifications
        if (!sql_mutex_lock('AircraftScheduling_charges'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // charge is being modified or added

        // save the charge information in the database
        SaveDatabaseInformation();
        sql_mutex_unlock('AircraftScheduling_charges');

        // updates to the charge are complete, take them back to the last screen
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
    else if(count($_POST) > 0 && $ChargeCancel == "Cancel") 
    {
        // user canceled the charge changes, take them back to the last screen
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
    else if(count($_POST) > 0 && $ChargeDelete == "Delete") 
    {
        // user is deleting the charge
        DeleteCharge(); 
             
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
        $OldChargeDate = BuildDate($Chargeday, $Chargemonth, $Chargeyear);
        $OldPartNumber = $PartNumber;
        $OldPartDescription = $PartDescription;
        $OldQuantity = $Quantity;
        $OldPrice = $Price;
        $OldTotalPrice = $TotalPrice;
        $OldUnitPrice = $UnitPrice;
        $OldCategory = $Category;
    }
    else
    {
        $OldMemberKeyCode = " ";
    	$OldChargeDate = "Now";
        $OldPartNumber = " ";
        $OldPartDescription = " ";
        $OldQuantity = " ";
        $OldPrice = " ";
        $OldTotalPrice = " ";
        $OldUnitPrice = " ";
        $OldCategory = " ";
    }
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='AddModifyCharges.php' METHOD='POST'>";

    // start the table to display the charge information
    echo "<center>";
    echo "<table border=0>";

    // tell the user what we are doing
    echo "<TR><TD colspan=2>";
    if (UCase($AddModify) == "ADD")
    {
        echo "<CENTER><H2>Add New Charge</H2>";
        
        // member information
        echo "<tr>";
        echo "<td colspan=2>Charge for user:";
        BuildMemberSelector($NameOfUser);
        echo "<br>";
        echo "</TD></TR>";
    }
    else
    {
        echo "<CENTER><H2>Modify Charge Information</H2>";
        
        // member information
        echo "<tr>";
        echo "<td colspan=2><center><b>Charge for user: $NameOfUser</b></center>";
        echo "<br>";
        echo "</TD></TR>";
        echo "<input name='NameOfUser' type='hidden' value='$NameOfUser'>\n";
    }
    echo "</CENTER></TD></TR>";
    
    // display the date of the charge and the part number
    echo "<tr>";
    echo "<td>Date:";
    genDateSelector("Charge", "main", $Chargeday, $Chargemonth, $Chargeyear);
    echo "</td>";
    echo "<td>Part Number:";
    LoadInventoryItems($PartNumber);
    echo "</td>";
    echo "</tr>";
    
    // display the quantity and the retail price
    echo "<tr>";
    echo "<td align=right>Quantity:";
    echo "<INPUT TYPE=TEXT NAME='Quantity' ID='Quantity' ALIGN=RIGHT SIZE=9 VALUE='" . 
            FormatField($Quantity, "Integer") . "' " .
            "Onchange='FormatControl()'>";
    echo "</td>";
    echo "<td align=right>Retail Price:";
    echo "<INPUT TYPE=TEXT NAME='Price' ID='Price' ALIGN=RIGHT SIZE=9 VALUE='" . 
            FormatField($Price, "Currency") . "' " .
            "Onchange='FormatControl()'>";
    echo "</td>";
    echo "</tr>";
    
    // wholesale price
    echo "<tr>";
    echo "<td colspan=2 align=right>Wholesale Price:";
    echo "<INPUT TYPE=TEXT NAME='UnitPrice' ID='UnitPrice' ALIGN=RIGHT SIZE=9 VALUE='" . 
            FormatField($UnitPrice, "Currency") . "' " .
            "Onchange='FormatControl()'>";
    echo "</td>";
    echo "</tr>";
    
    // description
    echo "<tr>";
    echo "<td colspan=2 align=left>Description:";
    echo "<input type=text name='PartDescription' ID='PartDescription' align=left size=50 value='" . 
            $PartDescription . "'>";
    echo "</td>";
    echo "</tr>";
    
    // category
    echo "<tr>";
    echo "<td colspan=2 align=left>Category:&nbsp;&nbsp;&nbsp;";
    LoadCategoryItems($Category);
    echo "</td>";
    echo "</tr>";
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<input name='goback' type='hidden' value='$goback'>\n";
    if(isset($GoBackParameters)) echo "<input name='GoBackParameters' type='hidden' value='$GoBackParameters'>\n";
    
    // save the debug flag
    echo "<INPUT NAME='debug_flag' TYPE='HIDDEN' value=\"$debug_flag\">\n";
   
    // generate the update and cancel buttons
    echo "<center>";
    echo "<table>";
    echo "<tr>";
    echo "<td><input name='AddModifyCharges' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<td><input name='ChargeCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelCharge"] . "')\"></TD>";
    if (UCase($AddModify) == "MODIFY")
        echo "<td><input name='ChargeDelete' type=submit value='Delete' onClick=\"return confirm('" .  
                    $lang["DeleteCharge"] . "')\"></TD>";
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
    echo "<INPUT NAME='OldMemberKeyCode' TYPE='HIDDEN' VALUE='$OldMemberKeyCode'>\n";
    echo "<INPUT NAME='OldChargeDate' TYPE='HIDDEN' VALUE='$OldChargeDate'>\n";
    echo "<INPUT NAME='OldPartNumber' TYPE='HIDDEN' VALUE='$OldPartNumber'>\n";
    echo "<INPUT NAME='OldPartDescription' TYPE='HIDDEN' VALUE='$OldPartDescription'>\n";
    echo "<INPUT NAME='OldQuantity' TYPE='HIDDEN' VALUE='$OldQuantity'>\n";
    echo "<INPUT NAME='OldPrice' TYPE='HIDDEN' VALUE='$OldPrice'>\n";
    echo "<INPUT NAME='OldTotalPrice' TYPE='HIDDEN' VALUE='$OldTotalPrice'>\n";
    echo "<INPUT NAME='OldUnitPrice' TYPE='HIDDEN' VALUE='$OldUnitPrice'>\n";
    echo "<INPUT NAME='OldCategory' TYPE='HIDDEN' VALUE='$OldCategory'>\n";
    echo "<INPUT NAME='AddModify' TYPE='HIDDEN' VALUE='$AddModify'>\n";
    
    // save the filter information
    echo "<INPUT NAME='FilterName' TYPE='HIDDEN' VALUE='$FilterName'>\n";
    echo "<INPUT NAME='FromDay' TYPE='HIDDEN' VALUE='$FromDay'>\n";
    echo "<INPUT NAME='FromMonth' TYPE='HIDDEN' VALUE='$FromMonth'>\n";
    echo "<INPUT NAME='FromYear' TYPE='HIDDEN' VALUE='$FromYear'>\n";
    echo "<INPUT NAME='ToDay' TYPE='HIDDEN' VALUE='$ToDay'>\n";
    echo "<INPUT NAME='ToMonth' TYPE='HIDDEN' VALUE='$ToMonth'>\n";
    echo "<INPUT NAME='ToYear' TYPE='HIDDEN' VALUE='$ToYear'>\n";
    echo "<INPUT NAME='order_by' TYPE='HIDDEN' VALUE='$order_by'>\n";
    
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
// UpdateInventoryControl()
//
// Purpose: Update the inventory information when a new part number
//          is selected by the user.
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
function UpdateInventoryControl()
{
    // new part number selected, update the controls
    PartNumberIndex = document.getElementById("PartNumber").selectedIndex;
    document.getElementById("Price").value = dollarize(RetailPriceList[PartNumberIndex]);
    document.getElementById("UnitPrice").value = dollarize(UnitPriceList[PartNumberIndex]);
    document.getElementById("PartDescription").value = DescriptionList[PartNumberIndex];
    
    // select the category from the list
    CategoryControlList = document.getElementById('Category');
    ListLength = CategoryControlList.length;
    for (var i = 0; i < ListLength; i++)
    {
        var CompareString = CategoryList[PartNumberIndex].substr(0, CategorySizeLimit);
        if (CategoryControlList.options[i].text.toUpperCase() == CompareString.toUpperCase())
        {
            CategoryControlList.selectedIndex = i;
            break;
        }
    }
}

//********************************************************************
// FormatControl()
//
// Purpose: Format the controls when a change is made.
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
function FormatControl()
{
    // format the quantity controls
    Quantity = parseFloat(document.getElementById("Quantity").value);
    if (isNaN(Quantity)) Quantity = 0.0;
    document.getElementById("Quantity").value = format(Quantity, 0);

    // format the price controls
    Price = document.getElementById("Price").value;
    if (Price.substr(0, 1) == CurrencyPrefix)
        Price = Price.substr(1);
    Price = parseFloat(Price);
    if (isNaN(Price)) Price = 0.0;
    document.getElementById("Price").value = dollarize(Price);
    
    // format the unit price control
    UnitPrice = document.getElementById("UnitPrice").value;
    if (UnitPrice.substr(0, 1) == CurrencyPrefix)
        UnitPrice = UnitPrice.substr(1);
    UnitPrice = parseFloat(UnitPrice);
    if (isNaN(UnitPrice)) UnitPrice = 0.0;
    document.getElementById("UnitPrice").value = dollarize(UnitPrice);
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
    // no errors found, return
	return true;
}

//-->
</script>
