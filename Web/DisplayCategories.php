<?php
//-----------------------------------------------------------------------------
// 
// DisplayCategories.php
// 
// PURPOSE: Displays the inventory categories on file to allow selection 
//          for modification.
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
//          FilterName - name to filter the categories on
//          FilterAircraft - aircraft tailnumber to filter the categories on
//          FromDay - start day to filter the categories on
//          FromMonth - start month to filter the categories on
//          FromYear - start year to filter the categories on
//          ToDay - end day to filter the categories on
//          ToMonth - end day to filter the categories on
//          ToYear - end day to filter the categories on
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
    $AllString = "All";
    
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
    
    // start the form
	echo "<FORM NAME='main'>";
    
    // display the title
    echo "<H2>Category Management</H2>";

    // if we are not generating a view for print, show the headers
    if ($pview == 0)
    {
        // get the name of the user from the login name if the filter
        // name is all
        if ($FilterName == $AllString)
            $CategoryName = getName();
        else
            $CategoryName = $FilterName;

        echo "<UL>";
        echo " <LI><b><a href='AddModifyCategories.php" . 
                                "?AddModify=Add" . 
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                                "'" .
                                ">Add Category</a></b>";
        echo " <LI><b>Click on a heading to sort</b>";
        echo " <LI><b>Click on a category name to edit that category</b>";
        echo "</UL>";
    }
    
    // buid the SQL for the categories
    if(! isset($order_by)) $order_by = "Name";
    $sql = "SELECT " .
    			"Name, " .             // 0
    			"GLAC, " .             // 1
    			"Can_Be_Changed " .    // 2
    		"FROM " .
    			"Categories " .
            "ORDER BY $order_by ";
    $res = sql_query($sql);
        
    // set the column sizes
    $Column1Width = "60%";
    $Column2Width = "40%";

    // build the script name and link back parameters
    $ScriptName = "DisplayCategories.php" . 
                    "?goback=$goback" . 
                    "&GoBackParameters=$GoBackParameters";
       
    // save the script parameters for the javascript procedures
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var order_by = '$order_by';";
    echo "var goback = '$goback';";
    echo "var GoBackParameters = '$GoBackParameters';";
    echo "</SCRIPT>";
    
    // put up the table headers with the links to sort the columns
    echo "<table border=1>";
    echo "<tr>";
    echo " <td align=center width=$Column1Width><a href='$ScriptName&order_by=Name'>Category Name</a></td>";
    echo " <td align=center width=$Column2Width><a href='$ScriptName&order_by=GLAC'>GLAC</a></td>";
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
    			    // category name column
    			    
    			    // build the parameter list for modifying the category
   			        $ModifyParameterList = 
                                "?AddModify=Modify" .
                                "&CategoryName=" . urlencode($row[0]) .
                                "&GLAC=" . urlencode($row[1]) .
                                "&CanBeChanged=$row[2]" .
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);

    			    // display a link to modify the category
					echo "<td align=left width=$Column1Width>";
					echo "<a href='AddModifyCategories.php$ModifyParameterList'>" .
					            stripslashes($row[$c]) . "</a></td>";
                    break;
				case 1:
    			    // GLAC column
					echo "<td align=center width=$Column2Width>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
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
        DisplayDatabaseError("DisplayCategories.php", $sql);
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
