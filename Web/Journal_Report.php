<?php
//-----------------------------------------------------------------------------
// 
// Journal_Report.php
// 
// PURPOSE: Display a report of journal entries. Every change to the database
//          is logged in the journal. This file allows those changes to be
//          viewed.
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
//      namematch - username to match in journal entry filter
//      descrmatch - description match in journal entry filter
//      From_day - from day match in journal entry filter
//      From_month - from month match in journal entry filter
//      From_year - from year match in journal entry filter
//      To_day - to day match in journal entry filter
//      To_month - to month match in journal entry filter
//      To_year - to year match in journal entry filter
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
    
    // default data
    $order_by = "timestamp";
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["namematch"])) $namematch = $rdata["namematch"];
    if(isset($rdata["descrmatch"])) $descrmatch = $rdata["descrmatch"];
    if(isset($rdata["From_day"])) $From_day = $rdata["From_day"];
    if(isset($rdata["From_month"])) $From_month = $rdata["From_month"];
    if(isset($rdata["From_year"])) $From_year = $rdata["From_year"];
    if(isset($rdata["To_day"])) $To_day = $rdata["To_day"];
    if(isset($rdata["To_month"])) $To_month = $rdata["To_month"];
    if(isset($rdata["To_year"])) $To_year = $rdata["To_year"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];
    
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    #If we dont know the right date then make it up
    if(!isset($day) or !isset($month) or !isset($year))
    {
    	$day   = date("d");
    	$month = date("m");
    	$year  = date("Y");
    }
    if(empty($resource))
    	$resource = get_default_resource();
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    if (isset($namematch))
    {
    	# Resubmit - reapply parameters as defaults.
    	# Make sure these are not escape-quoted:
    	$namematch = unslashes($namematch);
    	$descrmatch = unslashes($descrmatch);
    
    	# Make default values when the form is reused.
    	$namematch_default = htmlspecialchars($namematch);
    	$descrmatch_default = htmlspecialchars($descrmatch);
    }
    else
    {
    	# New report - use defaults.
    	$namematch_default = "";
    	$descrmatch_default = "";
    	$From_day = $day;
    	$From_month = $month;
    	$From_year = $year;
    	$To_time = mktime(0, 0, 0 - TimeZoneAdjustment(), $month, $day + $default_report_days, $year);
    	$To_day   = date("d", $To_time);
    	$To_month = date("m", $To_time);
    	$To_year  = date("Y", $To_time);
    }
    
    # Upper part: The form.
    ?>
    <h1><?php echo $lang["journal_report_on"];?></h1>
    <form name="main" method=post action=Journal_Report.php>
    <table>
    <tr><td class="CR"><?php echo $lang["journal_report_start"];?></td>
        <td class="CL"> <font size="-1">
        <?php genDateSelector("From_", "main", $From_day, $From_month, $From_year); ?>
        </font></td></tr>
    <tr><td class="CR"><?php echo $lang["journal_report_end"];?></td>
        <td class="CL"> <font size="-1">
        <?php genDateSelector("To_", "main", $To_day, $To_month, $To_year); ?>
        </font></td></tr>
    <tr><td class="CR"><?php echo $lang["journal_match_entry"];?></td>
        <td class="CL"><input type=text name=namematch size=18
        value="<?php echo $namematch_default; ?>">
        </td></tr>
    <tr><td class="CR"><?php echo $lang["match_descr"];?></td>
        <td class="CL"><input type=text name=descrmatch size=18
        value="<?php echo $descrmatch_default; ?>">
        </td></tr>
    <tr><td class="CR"><?php echo "Sort Output:";?></td>
        <td class="CL">
          <input type=radio name=order_by value="timestamp"<?php if ($order_by=="timestamp") echo " checked";
            echo ">" . "Time";?>
          <input type=radio name=order_by value="username"<?php if ($order_by=="username") echo " checked";
            echo ">" . "Username";?>
          <input type=radio name=order_by value="description"<?php if ($order_by=="description") echo " checked";
            echo ">" . "Description";?>
        </td></tr>
    <tr><td colspan=2 align=center><input type=submit>
    </td></tr>
    </table>
    <input type=hidden name=pview value=<?php echo $pview; ?>>
    </form>
    
    <?php
    
    # Lower part: Results, if called with parameters:
    if (isset($namematch))
    {
    	# Make sure these are not escape-quoted:
    	$namematch = unslashes($namematch);
    	$descrmatch = unslashes($descrmatch);
    
    	# Start and end times are also used to clip the times for summary info.
    	$report_start = mktime(0, 0, 0 + TimeZoneAdjustment(), $From_month, $From_day, $From_year);
    	$report_end = mktime(0, 0, 0 + TimeZoneAdjustment(), $To_month, $To_day+1, $To_year);
    
    	#   SQL result will contain the following columns:
    	# Col Index  Description:
    	#   1  [0]   journal_id (not displayed)
    	#   2  [1]   timestamp for the record
    	#   3  [2]   username making the journal entry
    	#   4  [3]   description of the journal entry
    	$sql = "SELECT journal_id, timestamp, username, description"
    		. " FROM 
    				AircraftScheduling_journal"
    		. " WHERE timestamp < $report_end AND timestamp > $report_start";
    
    	if (!empty($namematch))
    		$sql .= " AND" .  sql_syntax_caseless_contains("username", $namematch);
    	if (!empty($descrmatch))
    		$sql .= " AND" .  sql_syntax_caseless_contains("description", $descrmatch);
    
    	# Order by selected parameter
    	$sql .= " ORDER BY $order_by";
    
    	$res = sql_query($sql);
    	if (! $res) fatal_error(0, sql_error());
    	$nmatch = sql_count($res);
    	if ($nmatch == 0)
    	{
    		echo "<P><B>" . $lang["nothing_found"] . "</B>\n";
    		sql_free($res);
    	}
    	else
    	{
    		$last_area_room = "";
    		echo "<P><B>" . $nmatch . " "
    					. ($nmatch == 1 ? $lang["entry_found"] : $lang["entries_found"])
    					.  "</B>\n";
    		
    		// display the list of journal entries
    		echo "<center><table border=2>";
    		echo "<tr>";
    		echo   "<td><center>Time</center></td>
    				<td><center>Username</center></td>
    				<td><center>Description</center></td>";
    		echo "</tr>";
    
    		for ($i = 0; ($row = sql_row($res, $i)); $i++)
    		{
    			// display the values
    			echo "<tr>";
    			echo "<td> " . 
    					strftime('%X - %m/%d/%y', $row[1] - TimeZoneAdjustment()) . 
    				 "</td>";
    			echo "<td> $row[2]</td>";
    			echo "<td> $row[3]</td>";
    			echo "</tr>";	
    		}
    		echo "</table></center>";
    		
    		// add a link back to admin page
    		echo "<BR>";
    	    echo "<A HREF=\"admin.php\">" . "Return to administrator page" . "</A>"; 
    	}
    }
    
include "trailer.inc";
?>
