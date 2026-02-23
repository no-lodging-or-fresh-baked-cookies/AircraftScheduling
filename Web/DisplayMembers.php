<?php
//-----------------------------------------------------------------------------
// 
// DisplayMembers.php
// 
// PURPOSE: Displays the members on file to allow selection for modification.
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // start the page
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    if (!isset($order_by)) $order_by = "username";
    $sql = "SELECT " .
    			"username, " .                           // 0
    			"$DatabaseNameFormat, " .                // 1
    			"address1, " .                           // 2
    			"city, " .                               // 3
    			"state, " .                              // 4
    			"zip, " .                                // 5
    			"phone_number, " .                       // 6
    			"Home_Phone " .                          // 7
    		"FROM " .
    			"AircraftScheduling_person " .
            "ORDER BY $order_by ";
    
    $res = sql_query($sql);
    
         
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {
        // display the title and header information
        echo "<H2>User Management</H2>";
        echo "<UL>";
        // only office users or administrators can add new users
        if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
            authGetUserLevel(getUserName(), $auth["admin"]) ==  $UserLevelOffice)
        {
            echo " <LI><b><a href='AddModifyMember.php?AddModify=Add" . 
                                "&order_by=$order_by" .
                                "&goback=" . GetScriptName() .
                                "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                            "'>Add User</a></b>";
        }
        echo " <LI><b>Click on a heading to sort</b>";
        echo " <LI><b>Click on a username to edit that user</b>";
        echo "</UL>";
    
        // put up the table headers with the links to sort the columns
        echo "<table border=1>";
        echo "<tr>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=username'>Username</a></td>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=last_name'>Name</a></td>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=address1'>Address</a></td>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=city'>City</a></td>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=state'>State</a></td>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=zip'>Zip</a></td>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=phone_number'>Phone 1</a></td>";
        echo " <td align=center><a href='DisplayMembers.php?order_by=Home_Phone'>Phone 2</a></td>";
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
				    // username column
					echo "<td align=left><a href='AddModifyMember.php?AddModify=Modify" . 
                            "&username=" . stripslashes($row[$c]) . 
                            "&order_by=$order_by" .
                            "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                          "'>" . stripslashes($row[$c]) . "</a></td>";
			    }
				else if($c == 1)
				{
    				// display name column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 2)
				{
    				// display address column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 3)
				{
					// display city column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 4)
				{
    				// state column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 5)
				{
    				// zip column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 6)
				{
					// display phone 1 column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 7)
				{
 					// display phone 2 column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else
				{
    				// all other columns
					echo "<td>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
			}
			echo "</tr>\n";
		}
	}
	else 
    {
        // error processing database request, tell the user
        DisplayDatabaseError("DisplayMembers", $sql);
    }
    
    echo "</table>";
    
    echo "<BR>";
    
    // give them a link back to the admin page
    echo "<A HREF='admin.php'>Return to administrator page</A>";
    
    echo "<br>";
    include "trailer.inc" 

?>
