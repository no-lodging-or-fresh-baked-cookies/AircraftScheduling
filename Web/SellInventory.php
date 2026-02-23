<?php
//-----------------------------------------------------------------------------
// 
// SellInventory.php
// 
// PURPOSE: Displays the sell inventory screen.
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
//      SellInventory - set to modify to modify a charge
//      SellInventoryCancel - set to Cancel to cancel the update.
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
    $MaxSellItems = 15;
    
    // charge information
    $NameOfUser = "None";
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
    if(isset($rdata["SellInventory"])) $SellInventory = $rdata["SellInventory"];
    if(isset($rdata["SellInventoryCancel"])) $SellInventoryCancel = $rdata["SellInventoryCancel"];
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
    // we will check here for the submit and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $SellInventory == "Submit") 
    {
        // submit button was selected
        
        // get the sell inventory functions here to avoid
        // header problems from the java code
        require_once("SellInventoryFunctions.inc");
                
        // get the control variables
        SetControlVariables();

        // save the charge information in the database
        SaveChargeInformation($NameOfUser);;
            
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
        
        // print the confirmation sheet
        PrintConfirmation($NameOfUser);
        
        // finish the print form
        CompletePrintFunctions();
                    
        // finished with this part of the script
        exit;
    }
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

    // neither Submit or Cancel were selected, display the main screen
    // get the sell inventory functions here to avoid
    // header problems from the java code
    require_once("SellInventoryFunctions.inc");
        
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='SellInventory.php' METHOD='POST'>";

    // start the table to display the charge information
    echo "<center>";

    // tell the user what we are doing
    echo "<CENTER><H2>Sell items</H2>";
    
    // member information
    echo "User:";
    BuildMemberSelector($NameOfUser, true, "", 30, true);
    echo "</CENTER>";

    // skip some space
    echo "<br>";
    echo "<br>";

    // display the items to sell
    BuildSellItemTable($MaxSellItems);
        
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<input name='goback' type='hidden' value='$goback'>\n";
    if(isset($GoBackParameters)) echo "<input name='GoBackParameters' type='hidden' value='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<center>";
    echo "<table>";
    echo "<tr>";
    echo "<td><input name='SellInventory' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<td><input name='SellInventoryCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelCharge"] . "')\"></TD>";
    echo "</center></td></tr>";
    echo "</tr>";
    echo "</table>";
    
    echo "</center>";
    
    // save the input variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var MaxSellItems = $MaxSellItems;";   
    echo "</SCRIPT>";
     
    // save the inventory information for the javascript code
    SaveInventoryJavaScriptArrays();
    
    echo "<br>";
    
    // end the form
    echo "</FORM>";
    
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
    // make sure a user was selected
    UserName = document.getElementById("NameOfUser").value;
    
    // if none is selected don't let them continue
    if (UserName.toUpperCase() == "NONE")
    {
        // no user selected, don't let them continue
        alert("Please select the user to sell the items to.");
        document.getElementById('NameOfUser').focus();
        
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
