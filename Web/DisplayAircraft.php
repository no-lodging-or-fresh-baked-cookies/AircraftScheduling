<?php
//-----------------------------------------------------------------------------
// 
// DisplayAircraft.php
// 
// PURPOSE: Displays the aircraft on file to allow selection for modification.
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelMaintenance))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // start the page
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    if(! isset($order_by)) $order_by = "n_number";
    $sql = "SELECT " .
    			"n_number, " .                      // 0
    			"make, " .                          // 1
    			"model, " .                         // 2
    			"status, " .                        // 3
    			"ROUND(Hrs_Till_100_Hr, 1), " .     // 4
    			"ROUND(tach1, 1), " .               // 5
    			"Annual_Due, " .                    // 6
    			"a.hourly_cost, " .                 // 7
    			"ROUND(Current_Hobbs, 1), " .       // 8
    			"`Current_User` " .                 // 9
    		"FROM " .
    			"AircraftScheduling_aircraft a, " .
    			"AircraftScheduling_make b, " .
    			"AircraftScheduling_model c, " .
    			"AircraftScheduling_equipment_codes d " .
    		"WHERE " .
    			"a.make_id=b.make_id AND " .
    			"a.model_id=c.model_id AND " .
    			"a.code_id=d.code_id " .
    		"ORDER BY `$order_by`";
    
    $res = sql_query($sql);
    
         
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {
        // display the title and header information
        echo "<H2>Aircraft Management</H2>";
        echo "<UL>";
        echo " <LI><b><a href='AddModifyAircraft.php?AddModify=Add" . 
                            "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                        "'>Add Aircraft</a></b>";
        echo " <LI><b>Click on a heading to sort</b>";
        echo " <LI><b>Click on an N-Number to edit that aircraft</b>";
        echo "</UL>";
    
        // put up the table headers with the links to sort the columns
        echo "<table border=1>";
        echo "<tr>";
        echo " <td align=center><a href='DisplayAircraft.php?order_by=n_number'>Aircraft</a></td>";
        echo " <td align=center><a href='DisplayAircraft.php?order_by=make'>Make</a></td>";
        echo " <td align=center><a href='DisplayAircraft.php?order_by=model'>Model</a></td>";
        echo " <td align=center><a href='DisplayAircraft.php?order_by=status'>Status</a></td>";
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo " <td align=center><a href='DisplayAircraft.php?order_by=Hrs_Till_100_Hr'>Hrs Till<BR>100 Hr</a></td>";
            echo " <td align=center><a href='DisplayAircraft.php?order_by=tach1'>Current<br>Tach</a></td>";
            echo " <td align=center><a href='DisplayAircraft.php?order_by=Annual_Due'>Current<br>Annual</a></td>";
        }
        echo " <td align=center><a href='DisplayAircraft.php?order_by=hourly_cost'>Hourly Cost</a></td>";
        echo " <td align=center><a href='DisplayAircraft.php?order_by=Current_Hobbs'>Current<br>Hobbs</a></td>";
        echo " <td align=center><a href='DisplayAircraft.php?order_by=Current_User'>Current User</a></td>";
        echo "</tr>";

        // process the results of the database inquiry
		for ($i = 0; ($row = sql_row($res, $i)); $i++) 
		{
			echo "<tr>";
			for($c = 0; $c < count($row); $c++) 
			{
			    // process the columns
				if($c == 0)
				{
				    // aircraft tailnumber column
					echo "<td align=center><a href='AddModifyAircraft.php?AddModify=Modify" . 
                            "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                            "&n_number=" . stripslashes($row[$c]) . 
                          "'>" . stripslashes($row[$c]) . "</a></td>";
			    }
				else if($c == 1)
				{
    				// display make column
					echo "<td align=center>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 2)
				{
    				// display model column
					echo "<td align=center>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 3)
				{
					// display aircraft status column
					echo "<td align=center>" . LookupAircraftStatusString($row[$c]) . "</td>";
				}
				else if($c == 4 && $AllowAircraftCheckout)
				{
    				// hours until 100 hour column
					echo "<td align=right>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 5 && $AllowAircraftCheckout)
				{
    				// current tach column
					echo "<td align=right>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 6 && $AllowAircraftCheckout)
				{
					// display aircraft annual due column
    				if (strlen($row[$c]) > 0)
    				    $AnnualDate = FormatField($row[$c], "Date");
    			    else
    			        $AnnualDate = "not specified";
					echo "<td align=right>$AnnualDate</td>";
				}
				else if($c == 7)
				{
    				// hourly cost column
    				if (strlen($row[$c]) > 0)
    				    $HourlyCost = FormatField($row[$c], "Currency");
    			    else
    			        $HourlyCost = "not specified";
					echo "<td align=right>$HourlyCost</td>";
				}
				else if($c == 8)
				{
    				// current hobbs column
					echo "<td align=right>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "0.0") . "</td>";
				}
				else if($c == 9)
				{
    				// current user
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "none") . "</td>";
				}
			}
			echo "</tr>\n";
		}
	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("DisplayAircraft", $sql);
    }
    
    echo "</table>";
    
    echo "<BR>";
    
    // give them a link back to the admin page
    echo "<A HREF='admin.php'>Return to administrator page</A>";
    
    echo "<br>";
    include "trailer.inc" 

?>
