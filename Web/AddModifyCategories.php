<?php
//-----------------------------------------------------------------------------
// 
// AddModifyCategories.php
// 
// PURPOSE: Displays the add or modify categories screen.
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
//      AddModify - set to modify to modify a category or add to add a category
//      AddModifyCategories - set to modify to modify a category
//      CategoryCancel - set to Cancel to cancel the update.
//      CategoryDelete - set the Delete to delete a flight
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
    $CategoryCancel = "";
    $CategoryDelete = "";
    $ErrorMessage = "";
    
    // category information
    $CategoryName = "";
    $GLAC = "";
    $CanBeChanged = 1;

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
    if(isset($rdata["AddModifyCategories"])) $AddModifyCategories = $rdata["AddModifyCategories"];
    if(isset($rdata["AddModify"])) $AddModify = $rdata["AddModify"];
    if(isset($rdata["CategoryCancel"])) $CategoryCancel = $rdata["CategoryCancel"];
    if(isset($rdata["CategoryDelete"])) $CategoryDelete = $rdata["CategoryDelete"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"]; 
    
    // category information   
    if(isset($rdata["CategoryName"])) $CategoryName = $rdata["CategoryName"]; 
    if(isset($rdata["GLAC"])) $GLAC = $rdata["GLAC"]; 
    if(isset($rdata["CanBeChanged"])) $CanBeChanged = $rdata["CanBeChanged"];

    // old record information
    if(isset($rdata["OldCategoryName"])) $OldCategoryName = $rdata["OldCategoryName"]; 
    if(isset($rdata["OldGLAC"])) $OldGLAC = $rdata["OldGLAC"]; 
 
    // filter inforamtion
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
        global $OldCategoryName; 
        global $OldGLAC; 
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        
        global $CategoryName; 
        global $GLAC;
        global $CanBeChanged;
        
        $DatabaseFields = array();
        
        // Categories information
    
        // if the name is null, don't save anything
        if (Len(Trim($CategoryName)) > 0)
        {
            // category information
            SetDatabaseRecord("Name", Trim(UCase($CategoryName)), $RSConversionString, $DatabaseFields[0]);
            SetDatabaseRecord("GLAC", Trim(UCase($GLAC)), $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Can_Be_Changed", $CanBeChanged, $RSConversionNumber, $DatabaseFields[2]);
        
            // save the database record
            if (UCase($AddModify) == "MODIFY")
            {
                // update the current current
                UpdateDatabaseRecord(
                            "Categories",
                            $DatabaseFields,
                            "(" .
                             "Name='" . $OldCategoryName . "' AND " .
                             "GLAC='" . $OldGLAC . "' " .
                            ")");
            }
            else
            {
                // add a new record
                AddDatabaseRecord("Categories", $DatabaseFields);
            }

            // log the change in the journal
            if (UCase($AddModify) == "MODIFY")
            {
            	$Description = 
                                "Updating category " . Trim(UCase($CategoryName));
            	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
            }
            else
            {
            	$Description = 
                                "Adding category " . Trim(UCase($CategoryName));
            	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
            }
        }
    }
                
    //********************************************************************
    // DeleteCategory()
    //
    // Purpose:  Delete a category from the database.
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
    function DeleteCategory()
    {
        global $OldCategoryName;
        global $OldGLAC;
        
        // user responded yes, delete the record
        DeleteDatabaseRecord("Categories",
            "(" .
                "Name='" . $OldCategoryName . "' AND " .
                "GLAC='" . $OldGLAC . "'" .
            ") LIMIT 1");

        // log the change in the journal
    	$Description = 
                        "Deleting category " . $OldCategoryName;
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
    $FilterParameter = "&order_by=$order_by";

    // this script will call itself whenever the submit or Cancel button is pressed
    // we will check here for the update and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $AddModifyCategories == "Submit")
    {
        // acquire mutex to prevent concurrent category modifications
        if (!sql_mutex_lock('AircraftScheduling_categories'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // if we are modifying an existing category, don't check for existing category
        if (UCase($AddModify) == "MODIFY")
        {
            // modifying an existing category, don't worry if category is unique
    		$ExistingCategory = 0;
        }
        else
        {
            // adding a new category, make sure that the category is not already
            // in the database
    		$ExistingCategory = sql_query1(
    		                            "SELECT COUNT(*) " . 
    		                            "FROM Categories " .
    		                            "WHERE Name = '$CategoryName'");
        }
        
        // if the category already exists in the database, make them choose another
        if ($ExistingCategory > 0)
        {
            // category already exists in the database
            $ErrorMessage = $ErrorMessage . "<b>Category name must be unique<br><br>";
        }
        else
        {
            // category is being modified or added
    
            // save the category information in the database
            SaveDatabaseInformation();
    
            // updates to the category are complete, take them back to the last screen
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
            sql_mutex_unlock('AircraftScheduling_categories');
            exit;
        }
        sql_mutex_unlock('AircraftScheduling_categories');
    }
    else if(count($_POST) > 0 && $CategoryCancel == "Cancel")
    {
        // user canceled the category changes, take them back to the last screen
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
    else if(count($_POST) > 0 && $CategoryDelete == "Delete") 
    {
        // user is deleting the category
        DeleteCategory(); 
             
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
        $OldCategoryName = $CategoryName;
        $OldGLAC = $GLAC;
    }
    else
    {
        $OldCategoryName = " ";
        $OldGLAC = " ";
    }
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='AddModifyCategories.php' METHOD='POST'>";

    // start the table to display the category information
    echo "<center>";
    echo "<table border=0>";

    // tell the user what we are doing
    echo "<TR><TD colspan=2>";
    if (UCase($AddModify) == "ADD")
    {
        echo "<CENTER><H2>Add New Category</H2>";
        
        // did we have any errors processing the new inputs
        if (len($ErrorMessage) > 0)
        {
            // errors found, show them
            echo "<br>$ErrorMessage";
        }
    }
    else
    {
        echo "<CENTER><H2>Modify Category Information</H2>";
    }
    echo "</CENTER></TD></TR>";
    
    // description
    echo "<tr>";
    echo "<td align=left>Category Name:";
    echo "</td>";
    echo "<td align=left>";

    // we don't allow some descriptions to be changed since we use them
    if ($CanBeChanged)
    {
        // allow changes to the name
        echo "<input type=text name='CategoryName' ID='CategoryName' align=left size=50 value='" . 
            $CategoryName . "'>";
    }
    else
    {
        // don't allow changes to the name
        echo "<input type=text name='NameDisabled' ID='NameDisabled' align=left size=50 value='" . 
            $CategoryName . "' disabled>";
        echo "<input type=hidden name='CategoryName' value='$CategoryName'>";
    }
    echo "</td>";
    echo "</tr>";
    
    // category
    echo "<tr>";
    echo "<td  align=left>GLAC:";
    echo "</td>";
    echo "<td align=left>";
    echo "<input type=text name='GLAC' ID='GLAC' align=left size=50 value='" . 
            $GLAC . "'>";
    echo "</td>";
    echo "</tr>";
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<input name='goback' type='hidden' value='$goback'>\n";
    if(isset($GoBackParameters)) echo "<input name='GoBackParameters' type='hidden' value='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<center>";
    echo "<table>";
    echo "<tr>";
    echo "<td><input name='AddModifyCategories' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<td><input name='CategoryCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                    $lang["CancelCategory"] . "')\"></TD>";

    // we don't allow some descriptions to be changed since we use them for reports
    if ($CanBeChanged)
    {
        if (UCase($AddModify) == "MODIFY")
            echo "<td><input name='CategoryDelete' type=submit value='Delete' onClick=\"return confirm('" .  
                        $lang["DeleteCategory"] . "')\"></TD>";
    }
    
    echo "</center></td></tr>";
    echo "</tr>";
    echo "</table>";
    
    echo "</center>";
    
    // save the input variables for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var AddModify = '$AddModify';";
    echo "</SCRIPT>";
    
    // save the original values for submiting or deleting the form
    echo "<INPUT NAME='OldCategoryName' TYPE='HIDDEN' VALUE='$OldCategoryName'>\n";
    echo "<INPUT NAME='OldGLAC' TYPE='HIDDEN' VALUE='$OldGLAC'>\n";
    echo "<INPUT NAME='AddModify' TYPE='HIDDEN' VALUE='$AddModify'>\n";
    echo "<INPUT NAME='CanBeChanged' TYPE='HIDDEN' VALUE='$CanBeChanged'>\n";
     
    // save the filter information
    echo "<INPUT NAME='order_by' TYPE='HIDDEN' VALUE='$order_by'>\n";
   
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
