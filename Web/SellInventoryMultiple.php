<?php
//-----------------------------------------------------------------------------
// 
// SellInventoryMultiple.php
// 
// PURPOSE: Sell a single inventory item to multiple users.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//      SellInventory - set to modify to modify a charge
//      SellInventoryCancel - set to Cancel to cancel the update.
//      Lastday - day of last safety meeting
//      Lastmonth - month of last safety meeting
//      Lastyear - year of last safety meeting
//      Nextday - day of next safety meeting
//      Nextmonth - month of next safety meeting
//      Nextyear - year of next safety meeting
//      order_by - parameter to sort the display by
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
    include "DatabaseConstants.inc";
    require_once("CurrencyFunctions.inc");

    // initialize variables
    $order_by = "last_name";
    $MaxSellItems = 1;
    
    // charge information
	$Chargeday = date("d");
	$Chargemonth = date("m");
	$Chargeyear  = date("Y");
    $PartNumber = "";
    $PartDescription = "";
    $Quantity = 0;
    $Price = 0;
    $TotalPrice = 0;
    $UnitPrice = 0;
    $Category = "";
	$InstructorResource = "";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["SellInventory"])) $SellInventory = $rdata["SellInventory"];
    if(isset($rdata["SellInventoryCancel"])) $SellInventoryCancel = $rdata["SellInventoryCancel"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["Lastday"])) $Lastday = $rdata["Lastday"];
    if(isset($rdata["Lastmonth"])) $Lastmonth = $rdata["Lastmonth"];
    if(isset($rdata["Lastyear"])) $Lastyear = $rdata["Lastyear"];
    if(isset($rdata["Nextday"])) $Nextday = $rdata["Nextday"];
    if(isset($rdata["Nextmonth"])) $Nextmonth = $rdata["Nextmonth"];
    if(isset($rdata["Nextyear"])) $Nextyear = $rdata["Nextyear"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    
    // charge information   
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

    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // UpdateUsersCharges()
    //
    // Purpose: Update the selected members charge. The input
    //          parameters are processed to determine the users that
    //          should be updated.
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
    function UpdateUsersCharges()
    {
        global $rdata;
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        
        // get the control variables
        SetControlVariables();
        
        // process the seleted users. the checkboxes are the usernames for 
        // the members so any valid usernames within the script input
        // parameters will be set
        $InputNames = array_keys($rdata);
        $InputParameters = array_values($rdata);
        $FirstTime = true;
    	for ($i = 0; $i < count($InputNames); $i++)
    	{
    		// is this a checkbox parameter?
    		if ($InputParameters[$i] == "on")
    		{
    		    // yes, set the charge for the username
                // save the charge information in the database
                SaveChargeInformation(GetNameFromUsername($InputNames[$i]));
                
                // print a new page unless it is the first page
                if (!$FirstTime) 
                {
                    PrintNewPage();
                }
                $FirstTime = false;
        
                // print the confirmation sheet
                PrintConfirmation(GetNameFromUsername($InputNames[$i]));
    		}
    	}
    }
    
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################

    # if we dont know the right date then make it up 
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
    
    // if the make and model is not set, set the default
    if($make) $makemodel = "&make=$make";
    else if($model) $makemodel = "&model=$model";
    else { $all=1; $makemodel = "&all=1"; }
    
    // if the resource is not set, get the default
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
    
    // are we authorized for this operation?
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // if we are updating the data, save it and return to previous screen
    if(count($_POST) > 0 && $SellInventory == "Submit") 
    {        
        // get the sell inventory functions here to avoid
        // header problems from the java code
        require_once("SellInventoryFunctions.inc");
        
        // updates to the charge information are complete, take them back to the last screen
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

        // update the charge for the selected users
        UpdateUsersCharges();
        
        // finish the print form
        CompletePrintFunctions();
                    
        // finished with this part of the script
        exit;
    }
    
    // if we are updating the data, save it and return to previous screen
    else if(count($_POST) > 0 && $SellInventoryCancel == "Cancel") 
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
    else
    {
        // get the sell inventory functions here to avoid
        // header problems from the java code
        require_once("SellInventoryFunctions.inc");
        
        // display the sell inventory information
    	print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");

	    // display the sell inventory information
		echo "<center>";
        echo "<h2>Sell Inventory Item to Multiple Users</h2>";
		echo "<form name='SellInventoryMultiple' action='" . getenv('SCRIPT_NAME') . "' method='post'>";
		echo "<table border=0>\n";

        // display the items to sell
        BuildSellItemTable($MaxSellItems);
		
		echo "</table>";
		
		// get the member information from the database
        $MembersResult =
                SQLOpenRecordset(
                                 "SELECT * FROM AircraftScheduling_person " . 
                                 "WHERE " .
                                    "INSTR(Rules_Field, 'Member_Status,$MemberStatusInActive') OR " . 
                                    "INSTR(Rules_Field, 'Member_Status,$MemberStatusAircraft') OR " . 
                                    "INSTR(Rules_Field, 'Member_Status,$MemberStatusActive') " . 
                                 "ORDER BY $order_by");
		        
        echo "<H2>Check the users to sell the selected item to</H2>";

        // process the results of the database inquiry
        $UserNamesArray = array();
        $NamesArray = array();
		for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++) 
		{
		    // username column
			$UserNamesArray[$MembersCnt] = $MembersRST[$username_offset];
			
			// name column
			$NamesArray[$MembersCnt] = BuildName($MembersRST[$first_name_offset], $MembersRST[$last_name_offset]);
		}
    
        // start the table
        $NumberOfColumns = 3;
        echo "<table border=0>";
        
        // display the columns and rows
        DisplayNameColumns(
                            count($UserNamesArray), 
                            $NumberOfColumns, 
                            $UserNamesArray, 
                            $NamesArray);

        // complete the table
        echo "</table>";
        
        // save the goback parameters as hidden inputs so that they will
        // be retained after the submit
        if(isset($goback)) echo "<input name='goback' type='hidden' value='$goback'>\n";
        if(isset($GoBackParameters)) echo "<input name='GoBackParameters' type='hidden' value='$GoBackParameters'>\n";

		// submit and cancel buttons
		echo "<br>";
        echo "<td><input name='SellInventory' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
        echo "<td><input name='SellInventoryCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                        $lang["CancelCharge"] . "')\"></TD>";
		echo "</form>";
		echo "</center>";
        
        // save the input variables for the javascript code
        echo "<SCRIPT LANGUAGE=\"JavaScript\">";
        echo "var MaxSellItems = $MaxSellItems;";   
        echo "</SCRIPT>";
         
        // save the inventory information for the javascript code
        SaveInventoryJavaScriptArrays();
    }
    
    include "trailer.inc";

?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

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
    // make sure at least one user was selected
    var form = window.document.forms["SellInventoryMultiple"];
    TotalNames = 0;
    FirstCheckbox = 0;
    for (var i = 0; i < form.elements.length; i++)
    {
        if (form.elements[i].type == "checkbox")
        {
            // checkbox found, is it selected
            if (form.elements[i].checked)
            {
                // checkbox is selected, count it
                TotalNames++;
            }
            
            // save a pointer to the first checkbox so we
            // can select it if none are selected
            FirstCheckbox = i;
        }
    }
    
    // if no names are selected, don't let them continue
    if (TotalNames == 0)
    {
        // no users selected, don't let them continue
        alert("Please select at least one user to sell the items to.");
        form.elements[FirstCheckbox].focus();
        
        // error found, don't let them continue
        return false;
    }

    // make sure that at least one item was entered
    TotalItems = 0;
    for (i = 0; i < MaxSellItems; i++)
    {
        PartNumber = document.getElementById("PartNumber" + i).value;
        
        // if none is not selected, count the item
        if (PartNumber.toUpperCase() != "NONE") TotalItems++;
    }
    if (TotalItems ==0)
    {
        // no item sold, don't let them continue
        alert("At least one item must be sold before submitting the form.\n" +
                "Please enter a value.");
        document.getElementById('PartNumber0').focus();
        
        // error found, don't let them continue
        return false;
    }

    // no errors found, return
	return true;
}

//-->
</script>
