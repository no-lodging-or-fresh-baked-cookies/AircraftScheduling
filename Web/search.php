<?php
//-----------------------------------------------------------------------------
// 
// search.php
// 
// PURPOSE: Displays the results of a search request.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      InstructorResource - instructor resource for the header
//      search_str - the string to search the database for
//      search_pos - set to the current position in multi-page searches
//      pview - set true to build a screen suitable for printing
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
    
    // initialize variables
    $InstructorResource = "";
    $debug_flag = 0;
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];
    if(isset($rdata["search_str"])) $search_str = $rdata["search_str"];
    if(isset($rdata["search_pos"])) $search_pos = $rdata["search_pos"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
            
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // DisplayScheduleEntries($ScheduleResult)
    //
    // Purpose: Display the results of searching the schedule entries 
    //
    // Inputs:
    //   ScheduleResult - the search results from the schedule information
    //                    in the database
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayScheduleEntries($ScheduleResult)
    {
        global $lang;
	    global $EntryTypeStandby, $EntryTypeNormal, $EntryTypeRepeating, $EntryTypeModified;
        global $_SERVER;
    	global $AircraftScheduleType, $InstructorScheduleType;
    	global $debug_flag;
		global $InstructorResource;
                
        // display the schedule entry header row
        echo "<p>";
        echo "<table border=2 cellspacing=0 cellpadding=3>";
        echo "<tr>";
        echo "<th>" . $lang["entry"] . "</th>";
        echo "<th>" . $lang["createdby"] . "</th>";
        echo "<th>" . $lang["namebooker"] . "</th>";
        echo "<th>" . $lang["description"] . "</th>";
        echo "<th>" . $lang["resource_type"] . "</th>";
        echo "<th>" . $lang["resource_item"] . "</th>";
        echo "<th>" . $lang["start_date"] . "</th>";
        echo "</tr>";
    
        for ($i = 0; ($row = sql_row($ScheduleResult, $i)); $i++)
        {
        	// if this is a good entry (resource is known), display it otherwise ignore it
        	if (strlen($row[7]) > 0)
        	{		
        		echo "<TR>";
        		if ($row[8] == $EntryTypeStandby)
        			echo "<td><a href='StandbyRequestView.php?StandbyID=$row[0]" .
        		            "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
                            "'>$lang[view]</a></td>\n";
        		else
        			echo "<td><a href='view_entry.php?id=$row[0]" .
        		            "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) .
                            "'>$lang[view]</a></td>\n";
        		echo "<td>" . htmlspecialchars($row[1]) . "</td>\n";
        		echo "<td>" . htmlspecialchars($row[2]) . "</td>\n";
        		echo "<td>" . htmlspecialchars(strlen($row[3]) ? $row[3] : "none") . "</td>\n";
        		echo "<td>" . htmlspecialchars(($row[6] == $AircraftScheduleType) ? "Aircraft" : "Instructor") . "</td>\n";
        		if ($row[8] == $EntryTypeStandby)
        			echo "<td>" . htmlspecialchars(strlen($row[7]) ? $row[7] : "unknown") . " (Standby)" . "</td>\n";
        		else
        			echo "<td>" . htmlspecialchars(strlen($row[7]) ? $row[7] : "unknown") . "</td>\n";
        		// generate a link to the day.php
        		$link = getdate($row[4]);
        		echo "<td><a href='day.php?day=$link[mday]&month=$link[mon]&year=$link[year]&area=$row[5]" 
        					. (($row[6] == $AircraftScheduleType) ? 
        								"&resource=Aircraft&InstructorResource=$InstructorResource" : 
        								"&resource=$resource&InstructorResource=Instructor")
        					. "'>"
        					.  strftime('%X - %A %d %B %Y', $row[4]) . "</a></td>";
        		echo "</TR>\n";
        	}
        }
        
        echo "</table>\n";
    }    
    
    //********************************************************************
    // DisplayUserEntries($UserResult)
    //
    // Purpose: Display the results of searching the user entries 
    //
    // Inputs:
    //   UserResult - the search results from the user information
    //                in the database
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayUserEntries($UserResult)
    {
        global $_SERVER;
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	global $debug_flag;
        
        // put up the table headers
        echo "<table border=1>";
        echo "<tr>";
        echo " <th align=center>Username</td>";
        echo " <th align=center>Name</td>";
        echo " <th align=center>Instructor</td>";
        echo " <th align=center>Phone</td>";
        echo " <th align=center>email</td>";
        echo "</tr>";

        // process the results of the database inquiry
		for ($i = 0; ($row = sql_row($UserResult, $i)); $i++) 
		{
			echo "<tr>";
			
		    // username column
            if(getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
            {
            	// changes always allowed if we are administator or a super user
				echo "<td align=left><a href='AddModifyMember.php?AddModify=Modify" . 
                        "&username=" . stripslashes($row[0]) . 
                        "&goback=" . GetScriptName() .
                        "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]) . 
                      "'>" . stripslashes($row[0]) . "</a></td>";
            }
            else
            {
                // no changes are allowed
			    echo "<td>" . stripslashes(strlen($row[0]) > 0 ? $row[0] : "not specified") . "</td>";
            }

			// display name column
			echo "<td align=left>" . stripslashes(strlen($row[1]) > 0 ? $row[1] : "not specified") . "</td>";

			// instructor of record column
			$InstructorName = GetNameFromUsername(stripslashes($row[10]));
			if ($InstructorName == -1) $InstructorName = "None";
			echo "<td align=left>" . $InstructorName . "</td>";

			// display phone numbers if the user has priviledge or the user has said it is OK
			if ($row[9] == 1 || getAuthorised(getUserName(), getUserPassword(), $UserLevelSuper))
    			echo "<td align=left>" . FormatPhoneNumber($row[6], $row[7]) . "</td>";
			else
    			echo "<td align=left>UNLISTED</td>";

		    // display the email address if one is specified
			echo "<td>";
			if (strlen($row[8]) > 0)
			{
			    $EmailAddress =stripslashes($row[8]);
                echo "<A href='mailto:$EmailAddress'>$EmailAddress</A>";
		    }
		    else
		    {
			    echo "not specified";
		    }
			echo "</td>";			     

			echo "</tr>\n";
		}
    
        echo "</table>";
    }         
    
    //********************************************************************
    // DisplayInventoryEntries($InventoryResult)
    //
    // Purpose: Display the results of searching the inventory entries 
    //
    // Inputs:
    //   InventoryResult - the search results from the inventory information
    //                in the database
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayInventoryEntries($InventoryResult)
    {
        global $_SERVER;
    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	global $debug_flag;
        
        include "DatabaseConstants.inc";
        
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
        
        // put up the table headers
        echo "<table border=1>";
        echo "<tr>";
        echo " <th align=center width=$Column1Width>Date</td>";
        echo " <th align=center width=$Column2Width>Part Number</td>";
        echo " <th align=center width=$Column3Width>Description</td>";
        echo " <th align=center width=$Column4Width>Unit<br>Price</td>";
        echo " <th align=center width=$Column5Width>Retail<br>Price</td>";
        echo " <th align=center width=$Column6Width>Quantity<br>In Stock</td>";
        echo " <th align=center width=$Column7Width>Reorder<br>Quantity</td>";
        echo " <th align=center width=$Column8Width>Position</td>";
        echo " <th align=center width=$Column9Width>Category</td>";
        echo "</tr>";

        // process the results of the database inquiry
		for ($i = 0; ($row = sql_row($InventoryResult, $i)); $i++) 
		{
			echo "<tr>";
			
		    // date column
            if(getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
            {
            	// changes always allowed if we are administator or a super user
             	$Inventoryday   = date("d", strtotime($row[0]));
            	$Inventorymonth = date("m", strtotime($row[0]));
            	$Inventoryyear  = date("Y", strtotime($row[0]));
		        $ModifyParameterList = 
                            "?AddModify=Modify" .
                            "&Inventoryday=$Inventoryday" .
                            "&Inventorymonth=$Inventorymonth" .
                            "&Inventoryyear=$Inventoryyear" .
                            "&Part_Number=" . urlencode($row[1]) .
                            "&PartDescription=" . urlencode($row[2]) .
                            "&Unit_Price=$row[3]" .
                            "&Retail_Price=$row[4]" .
                            "&Quantity_In_Stock=$row[5]" .
                            "&Reorder_Quantity=$row[6]" .
                            "&Position=" . urlencode($row[7]) .
                            "&Category=" . urlencode($row[8]) .
                            "&Inventory_Type=$row[9]" .
                            "&goback=" . GetScriptName() .
                            "&GoBackParameters=" . BuildGoBackParameters("?" . $_SERVER["QUERY_STRING"]);
				echo "<td align=left width=$Column1Width>";
				echo "<a href='AddModifyInventory.php$ModifyParameterList'>" .
				            FormatField($row[0], "Date") . "</a></td>";
            }
            else
            {
                // no changes are allowed
			    echo "<td>" . FormatField($row[0], "Date") . "</td>";
            }
            
		    // Part Number
			echo "<td align=center width=$Column2Width>" . stripslashes(strlen($row[1]) > 0 ? $row[1] : "&nbsp") . "</td>";

		    // Description
			echo "<td align=center width=$Column3Width>" . stripslashes(strlen($row[2]) > 0 ? $row[2] : "&nbsp") . "</td>";

		    // Unit Price
			echo "<td align=right width=$Column4Width>" . FormatField($row[3], "Currency") . "</td>";

		    // Retail Price
			echo "<td align=right width=$Column5Width>" . FormatField($row[4], "Currency") . "</td>";

		    // Quantity in stock
			echo "<td align=right width=$Column6Width>" . FormatField($row[5], "Integer") . "</td>";

		    // Reorder Quantity
			echo "<td align=right width=$Column7Width>" . FormatField($row[6], "Integer") . "</td>";

		    // Position
			echo "<td align=left width=$Column8Width>" . stripslashes(strlen($row[7]) > 0 ? $row[7] : "&nbsp") . "</td>";

		    // Category
			echo "<td align=left width=$Column9Width>" . stripslashes(strlen($row[8]) > 0 ? $row[8] : "&nbsp") . "</td>";

			echo "</tr>\n";
		}
    
        echo "</table>";
    }         
    
    //********************************************************************
    // function BuildPreviousNextLinks(
    //                                 $search_pos, 
    //                                 $NumberDisplayRecords, 
    //                                 $TotalEntries, 
    //                                 $search_url, 
    //                                 $day, $month, $year)
    //
    // Purpose: Display the previous and next links if we need them. 
    //
    // Inputs:
    //   search_pos - starting position of the current search 
    //   NumberDisplayRecords - number of records displayed on this page 
    //   TotalEntries - total entries for this search 
    //   search_url - the url for the search 
    //   day - day of the month for the current schedule
    //   month - month for the current schedule
    //   year - year for the current schedule
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildPreviousNextLinks(
                                    $search_pos, 
                                    $NumberDisplayRecords, 
                                    $TotalEntries, 
                                    $search_url, 
                                    $day, $month, $year)
    {
        global $lang;
        global $search;
    	global $debug_flag;
         
        // this is a flag to tell us not to display a "Next" link
        $has_prev = $search_pos > 0;
        $has_next = $search_pos < ($TotalEntries - $search["count"]);
        
        // do we need the next or previous buttons
        if($has_prev || $has_next)
        {
        	echo "<b>" . 
        	        $lang["records"] . 
        	        ($search_pos+1) . 
        	        $lang["through"] . 
        	        ($search_pos + $NumberDisplayRecords) . 
        	        $lang["of"] . 
        	        $TotalEntries . "</b><br>";
        
        	// display a "Previous" button if necessary
        	if($has_prev)
        	{
        		echo "<a href='search.php?search_str=$search_url&search_pos=";
        		echo max(0, $search_pos - $search["count"]);
        		echo "&year=$year&month=$month&day=$day'>";
        	}
        
        	echo "<B>" . $lang["previous"] . "</B>";
        
        	if($has_prev)
        		echo "</a>";
        
        	// print a separator for Next and Previous
        	echo(" | ");
        
        	// display a "Next" button if necessary
        	if($has_next)
        	{
        		echo "<a href='search.php?search_str=$search_url&search_pos=";
        		echo max(0, $search_pos + $search["count"]);
        		echo "&year=$year&month=$month&day=$day'>";
        	}
        
        	echo "<B>". $lang["next"] ."</B>";
        
        	if($has_next)
        		echo "</a>";
        }
    }        
    
    //********************************************************************
    // function GetQueryResult(
    //                        $SQL,  
    //                        $MaxRecords, 
    //                        $DatabaseOffset, 
    //                        &$QueryResult, 
    //                        &$NumberQueryRecords)
    //
    // Purpose: Perform a database query to retrieve the results for a search. 
    //
    // Inputs:
    //   SQL - SQL statement to use to search the database  
    //   MaxRecords - number of records to return from the query
    //   DatabaseOffset - starting position within the database
    //
    // Outputs:
    //   QueryResult - SQL record of the query result
    //   NumberQueryRecords - number of records in the query
    //
    // Returns:
    //   none
    //*********************************************************************
    function GetQueryResult(
                            $SQL,  
                            $MaxRecords, 
                            $DatabaseOffset, 
                            &$QueryResult, 
                            &$NumberQueryRecords)
    {
        global $debug_flag;
        
        // add the limit statements to the SQL
        $FullSQL = $SQL . sql_syntax_limit($MaxRecords, $DatabaseOffset);
        
        // perform the query
        $QueryResult = sql_query($FullSQL);
        if (! $QueryResult) 
        {
            // error show the SQL and the error
            echo "GetQueryResult SQL $FullSQL<br>";
            fatal_error(0, sql_error());
        }
        $NumberQueryRecords = sql_count($QueryResult);
    }

    //********************************************************************
    // function ProcessThisRecord($CurrentPosition, $LastPosition, $NextPosition)
    //
    // Purpose: Determine if the current record is within range to be
    //          processed. 
    //
    // Inputs:
    //   CurrentPosition - current position being processed  
    //   LastPosition - previous starting position
    //   NextPosition - next starting position
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   true - if this record should be processed
    //*********************************************************************
    function ProcessThisRecord($CurrentPosition, $LastPosition, $NextPosition)
    {
        global $debug_flag;
        
        if ($debug_flag)
    	    echo "DEBUG: ProcessThisRecord CurrentPosition: $CurrentPosition LastPosition: $LastPosition NextPosition: $NextPosition<br>";
    	    
        // if we are within range, pass back a true
        if ($CurrentPosition >= $LastPosition &&
            $CurrentPosition < $NextPosition)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    //********************************************************************
    // function ComputeMaxRecords(
    //                                $PreviousQueryRecords,
    //                                &$MaxRecords)
    //
    // Purpose: Determine the number of records to retreive from the
    //          database query. 
    //
    // Inputs:
    //   PreviousQueryRecords - previous records displayed
    //
    // Outputs:
    //   MaxRecords - number of records to process
    //
    // Returns:
    //   none
    //*********************************************************************
    function ComputeMaxRecords(
                                $PreviousQueryRecords,
                                &$MaxRecords)
    {
        global $search;
    	global $debug_flag;
        
        // set max records to the screen limit
        $MaxRecords = $search["count"];
        
        // if we have already displayed some query records, take away those from the 
        // totals
        $MaxRecords = $MaxRecords - $PreviousQueryRecords;

        // don't allow less than 0 records
        if ($MaxRecords < 0) $MaxRecords = 0;
    
        if ($debug_flag)
        	echo "DEBUG: ComputeMaxRecords PreviousQueryRecords: $PreviousQueryRecords MaxRecords: $MaxRecords<br>";
    } 
      
    //********************************************************************
    // function ComputeStartPosition($CurrentPosition, $LastPosition, &$StartingRecord)
    //
    // Purpose: Determine the position to start processing within
    //          the database table for the query. 
    //
    // Inputs:
    //   CurrentPosition - current position being processed  
    //   LastPosition - previous starting position
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   true - if this record should be processed
    //*********************************************************************
    function ComputeStartPosition($CurrentPosition, $LastPosition, &$StartingRecord)
    {
        global $debug_flag;
        
        $StartingRecord = $CurrentPosition - $LastPosition;
        if ($StartingRecord < 0) $StartingRecord = 0;

        if ($debug_flag)
        	echo "DEBUG: ComputeStartPosition CurrentPosition: $CurrentPosition LastPosition: $LastPosition StartingRecord: $StartingRecord<br>";
    }   
     
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################

    // if we dont know the right date then make it up 
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }
    
    // Need all these different versions with different escaping.
    // search_str must be left as the html-escaped version because this is
    // used as the default value for the search box in the header.
    $search_text = unslashes($search_str);
    $search_url = urlencode($search_text);
    $search_str = htmlspecialchars($search_text);
    
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    if(!$search_str)
    {
    	echo "<H3>" . $lang["invalid_search"] . "</H3>";
    	include "trailer.inc";
    	exit;
    }
    
    // display the search string
    echo "<H3>" . $lang["search_results"] . " '<font color='blue'>$search_str</font>'</H3>\n";
    
    // now is used so that we only display entries newer than the current time
    $now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
    
    // setup the query for the schedule entries
    $ScheduleSQLPred = "( " . sql_syntax_caseless_contains("create_by", $search_text)
    		. " OR " . sql_syntax_caseless_contains("name", $search_text)
    		. " OR " . sql_syntax_caseless_contains("description", $search_text)
    		. " OR " . sql_syntax_caseless_contains("resource_name", $search_text)
    		. " OR " . sql_syntax_caseless_contains("resource_make", $search_text)
    		. " OR " . sql_syntax_caseless_contains("resource_model", $search_text)
    		. ") AND end_time >= $now";
    
    // get the total number of entries in the schedule
	$TotalScheduleEntries = sql_query1(
	                    "SELECT count(*) " .
	                    "FROM AircraftScheduling_entry " .
	                    "     LEFT JOIN AircraftScheduling_resource " .
	                    "USING (resource_id) " . 
	                    "WHERE $ScheduleSQLPred");
    
    // setup the query for the user entries
    $UsersSQLPred = "( " . sql_syntax_caseless_contains("first_name", $search_text)
    		. " OR " . sql_syntax_caseless_contains("middle_name", $search_text)
    		. " OR " . sql_syntax_caseless_contains("last_name", $search_text)
    		. " OR " . sql_syntax_caseless_contains("username", $search_text)
    		. " OR " . sql_syntax_caseless_contains("phone_number", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Home_Phone", $search_text)
    		. " OR " . sql_syntax_caseless_contains("email", $search_text)
    		. " OR " . sql_syntax_caseless_contains("InstructorOfRecord", $search_text)
    		. ")";
    
    // get the total number of entries in the users
	$TotalUsersEntries = sql_query1(
	                    "SELECT count(*) " .
	                    "FROM AircraftScheduling_person " . 
	                    "WHERE $UsersSQLPred");
    
    // setup the query for the inventory entries
    $InventorySQLPred = "( " . sql_syntax_caseless_contains("Date", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Part_Number", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Description", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Unit_Price", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Retail_Price", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Quantity_In_Stock", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Reorder_Quantity", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Position", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Category", $search_text)
    		. " OR " . sql_syntax_caseless_contains("Inventory_Type", $search_text)
    		. ")";
    
    // only allow office, maintenance and admin users to search the
    // inventory items
    if(getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
        // get the total number of entries in the inventory query
    	$TotalInventoryEntries = sql_query1(
    	                    "SELECT count(*) " .
    	                    "FROM Inventory " . 
    	                    "WHERE $InventorySQLPred");
        $SearchInventory = true;
    }
    else
    {
        // don't allow inventory searches
    	$TotalInventoryEntries = 0;
        $SearchInventory = false;
    }
	                    
	// compute the total of all the entries
	$TotalEntries = $TotalScheduleEntries + $TotalUsersEntries + $TotalInventoryEntries;
    if ($debug_flag)
        echo "DEBUG: TotalEntries: $TotalEntries TotalScheduleEntries: $TotalScheduleEntries TotalUsersEntries: $TotalUsersEntries TotalInventoryEntries: $TotalInventoryEntries<br>";
    
    // if we didn't find anything, tell the user
    if($TotalEntries <= 0)
    {
    	echo "<B>" . $lang["nothing_found"] . "</B>\n";
    	echo "<br>";
    	include "trailer.inc";
    	exit;
    }
    
    // make sure we have a valid search position
    if(!isset($search_pos) || ($search_pos <= 0))
    	$search_pos = 0;
    elseif($search_pos >= $TotalEntries)
    	$search_pos = $TotalEntries - ($TotalEntries % $search["count"]);
    
    // save counters so we can determine where to start each search
    $SearchCounter = $search_pos;
    
    // should we process the schedule entries?
    if ($debug_flag)
        echo "DEBUG: Schedule Entries<br>";
    if (ProcessThisRecord($SearchCounter, 0, $TotalScheduleEntries))
    {
        // searching the schedule entries
        $sql = "SELECT " .
        				"entry_id,  " .
        				"create_by,  " .
        				"name,  " .
        				"description,  " .
        				"start_time,  " .
        				"AircraftScheduling_resource.resource_id, " .
        				"schedulable_id, " .
        				"resource_name, " .
        				"entry_type " .
                "FROM AircraftScheduling_entry LEFT JOIN AircraftScheduling_resource USING (resource_id) " .
                "WHERE $ScheduleSQLPred " . 
                "ORDER BY start_time asc ";
        ComputeMaxRecords(0, $MaxRecords);
        ComputeStartPosition($SearchCounter, 0, $StartingRecord);               
        GetQueryResult($sql, $MaxRecords, $StartingRecord, $ScheduleResult, $NumberScheduleRecords);
    }
    else
    {
        // no records left to process from this database table
        $NumberScheduleRecords = 0;
    }
    $SearchCounter = $SearchCounter + $NumberScheduleRecords;
        
    // should we process the user entries?
    if ($debug_flag)
        echo "DEBUG: User Entries<br>";
    if (ProcessThisRecord($SearchCounter, $TotalScheduleEntries, $TotalScheduleEntries + $TotalUsersEntries))
    {
        // searching the user table
        $sql = "SELECT " .
        			"username, " .                   // 0
        			"$DatabaseNameFormat, " .        // 1
        			"address1, " .                   // 2
        			"city, " .                       // 3
        			"state, " .                      // 4
        			"zip, " .                        // 5
        			"phone_number, " .               // 6
        			"Home_Phone, " .                 // 7
      				"email, " .                      // 8
      				"Allow_Phone_Number_Display, " . // 9
      				"InstructorOfRecord " .          // 10
                "FROM AircraftScheduling_person " .
                "WHERE $UsersSQLPred " . 
                "ORDER BY last_name ";
        ComputeMaxRecords(
                            $NumberScheduleRecords, 
                            $MaxRecords);
        ComputeStartPosition($SearchCounter, $TotalScheduleEntries, $StartingRecord);               
        GetQueryResult($sql, $MaxRecords, $StartingRecord, $UserResult, $NumberUserRecords);
    }
    else
    {
        // no records left to process from this database table
        $NumberUserRecords = 0;
    }
    $SearchCounter = $SearchCounter + $NumberUserRecords;
    
    // search the inventory records if we have enough privilege
    if ($SearchInventory)
    {
        
        // should we process the user entries?
        if ($debug_flag)
            echo "DEBUG: Inventory Entries<br>";
        if (ProcessThisRecord($SearchCounter, $TotalScheduleEntries + $TotalUsersEntries, $TotalScheduleEntries + $TotalUsersEntries + $TotalInventoryEntries))
        {
            // searching the inventory table
            $sql = "SELECT * " .
                    "FROM Inventory " .
                    "WHERE $InventorySQLPred " . 
                    "ORDER BY Part_Number ";
            ComputeMaxRecords(
                                $NumberScheduleRecords + $NumberUserRecords, 
                                $MaxRecords);
            ComputeStartPosition($SearchCounter, $TotalScheduleEntries + $TotalUsersEntries, $StartingRecord);               
            GetQueryResult($sql, $MaxRecords, $StartingRecord, $InventoryResult, $NumberInventoryRecords);
        }
    }
    else
    {
        // no records left to process from this database table
        $NumberInventoryRecords = 0;
    }
    $SearchCounter = $SearchCounter + $NumberInventoryRecords;
    
    // display the next/previous links if we need them
    BuildPreviousNextLinks(
                            $search_pos, 
                            $NumberScheduleRecords + $NumberUserRecords + $NumberInventoryRecords, 
                            $TotalEntries, 
                            $search_url, 
                            $day, $month, $year);
    
    // should we display the schedule records
    if ($NumberScheduleRecords > 0)
    {
        // searching the schedule entries
        DisplayScheduleEntries($ScheduleResult);
        
        // skip some space
        echo "<br><br>";        
    }
    
    // should we display the user records?
    if ($NumberUserRecords > 0) 
    {
        // display the results
        DisplayUserEntries($UserResult);
        
        // skip some space
        echo "<br><br>";        
    }
    
    // display the inventory records if we have enough privilege
    if ($SearchInventory)
    {
        // should we display the inventory records?
        if ($NumberInventoryRecords > 0)
        {
            // display the results
            DisplayInventoryEntries($InventoryResult);
        }
    }
    
    // finish the page
    include "trailer.inc";
?>
