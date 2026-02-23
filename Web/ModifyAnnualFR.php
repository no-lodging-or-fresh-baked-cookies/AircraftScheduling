<?php
//-----------------------------------------------------------------------------
// 
// ModifyAnnualFR.php
// 
// PURPOSE: Changes the name of rules fields values in the database.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      UpdateRules - set to true to modify the rules information
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//          NOT PART OF THE NORMAL AIRCRAFTSCHEDULING RELEASE
// 
// -----------------------------------------------------------------------------

    include "global_def.inc";
    include "config.inc";
    include "AircraftScheduling_auth.inc";
    include "$dbsys.inc";
    include "functions.inc";
    require_once("CurrencyFunctions.inc");
    
    // initialize variables
    $UpdateRules = 0;
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["UpdateRules"])) $UpdateRules = $rdata["UpdateRules"];
                
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // UpdateRules
    //
    // Purpose:  Update the rules field of the currency fields.
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
    function UpdateRules()
    {        
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $FlightTest, $WrittenTest, $InitialCheckout;
        
        include "DatabaseConstants.inc";
                
        // loop through the member's currency information and update the rules
        // type
        $MembersResult =
                SQLOpenRecordset(
                                "SELECT * " .
                                "FROM AircraftScheduling_person " .
                                "WHERE INSTR(Rules_Field, 'Biannual_Flight_Review')");
        
        // rename the currency field
        $RecordsUpdated = 0;
		for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++) 
        {
            // make sure we don't time out
            set_time_limit(30);

            // load the currency fields from the database
            LoadDBCurrencyFields("", $MembersRST[$Rules_Field_offset]);
            
            // get the currency value of the Biannual Flight Review
            $FAAFlightReviewDate  = LookupCurrencyFieldname("Biannual_Flight_Review");
            
            // save it as the new name
            UpdateCurrencyFieldname("FAA_Flight_Review", $FAAFlightReviewDate);
            
            // delete the old currency field value
            DeleteCurrencyField("Biannual_Flight_Review");
    
            // save the rules values to the database field
            SaveDBCurrencyFields($RulesField);
                
            // write the updated rule to the database
            $DatabaseFields = array();
            SetDatabaseRecord("Rules_Field", $RulesField, $RSConversionString, $DatabaseFields[0]);
            UpdateDatabaseRecord(
                                "AircraftScheduling_person",
                                $DatabaseFields,
                                "username='" . $MembersRST[$username_offset] . "'");
            $RecordsUpdated++;
        }
        
        // display the results
        echo "<h1>Records Updated: $RecordsUpdated</h1>";
    }

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
    
    if (empty($resource))
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
    
    // if the user is authorized
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelAdmin))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }    

    // this script will call itself whenever the submit or delete button is pressed
    // we will check here for the update and delete request before generating
    // the main screen information
    
    // if we are updating the rules information
    if($UpdateRules)
    {
        // acquire mutex to prevent concurrent flight review modifications
        if (!sql_mutex_lock('AircraftScheduling_flight'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // update the rule information
        UpdateRules();
           
        // make sure that the FAA_Flight_Review field exists in the database. if the 
        // database is an older version, it may not be there
        $sql = "SELECT Currency_Field_Name FROM CurrencyFields WHERE Currency_Field_Name = 'FAA_Flight_Review' LIMIT 1";
        if (sql_query1($sql) == -1)
        {
            // update the currency field
            sql_command(
                        "UPDATE CurrencyFields " .
                        "SET Currency_Field_Name = 'FAA_Flight_Review' " .
                        "WHERE Currency_Field_Name = 'Biannual_Flight_Review'");
        }            
           
        // make sure that the FAA_Flight_Review field exists in the database. if the 
        // database is an older version, it may not be there
        $sql = "SELECT Item FROM CurrencyRules WHERE Item = 'FAA Flight Review' LIMIT 1";
        if (sql_query1($sql) == -1)
        {
            // update the currency rule
            sql_command(
                "INSERT INTO CurrencyRules " .
                    "(ID, Item, Pass_Criteria, Expires_Month_End, Student, Private_Under_200, Private_Over_200, Instrument, CFI) " .
                "VALUES " .
                    "(NULL , 'FAA Flight Review', 'FAA_Flight_Review + 2Y > Now', '1', 'No', 'Information', 'Information', 'Information', 'Information')");
        }
           
        // make sure that the TSA Security Training field exists in the database. if the 
        // database is an older version, it may not be there
        $sql = "SELECT Currency_Field_Name FROM CurrencyFields WHERE Currency_Field_Name = 'TSA_Security_Training' LIMIT 1";
        if (sql_query1($sql) == -1)
        {
            // update the currency field
            sql_command(
                        "INSERT INTO CurrencyFields " .
                            "(Currency_Field_Name, Currency_Field_Type, Student, Private_Under_200, Private_Over_200, Instrument, CFI) " .
                        "VALUES " .
                            "('TSA_Security_Training', 'Date', 0, 0, 0, 0, 1)"
                            );
        }            
           
        // make sure that the TSA Security Training field exists in the database. if the 
        // database is an older version, it may not be there
        $sql = "SELECT Item FROM CurrencyRules WHERE Item = 'TSA Security Training' LIMIT 1";
        if (sql_query1($sql) == -1)
        {
            // update the currency rule
            sql_command(
                "INSERT INTO CurrencyRules " .
                    "(ID, Item, Pass_Criteria, Expires_Month_End, Student, Private_Under_200, Private_Over_200, Instrument, CFI) " .
                "VALUES " .
                    "(NULL , 'TSA Security Training', 'TSA_Security_Training + 1Y > Now', '1', 'No', 'No', 'No', 'No', 'Information')");
        }
                 
    	sql_mutex_unlock('AircraftScheduling_flight');

    	// log the change in to the journal
    	$Description =
    				"Updated the rules field in the database.";
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    }
    
    // start the form
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='ModifyAnnualFR.php' METHOD='POST'>";
    
    if (!isset($order_by)) $order_by = "username";
    $sql = "SELECT " .
    			"username, " .                           // 0
    			"$DatabaseNameFormat, " .                // 1
    			"Rules_Field " .                         // 2
    		"FROM " .
    			"AircraftScheduling_person " .
    	    "WHERE INSTR(Rules_Field, 'FAA_Flight_Review') " .
            "ORDER BY $order_by ";
    
    $res = sql_query($sql);
        
    // if we didn't have any errors, process the results of the database inquiry
    if($res) 
    {
        // display the title and header information
        echo "<H2>Update Rule Field Values</H2>";
    
        // put up the table headers with the links to sort the columns
        echo "<table border=1>";
        echo "<tr>";
        echo " <td align=center>Username</a></td>";
        echo " <td align=center>Name</a></td>";
        echo " <td align=center>Rules Field</a></td>";
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
					echo "<td align=left>" . stripslashes($row[$c]) . "</td>";
			    }
				else if($c == 1)
				{
    				// display name column
					echo "<td align=left>" . stripslashes(strlen($row[$c]) > 0 ? $row[$c] : "not specified") . "</td>";
				}
				else if($c == 2)
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
        DisplayDatabaseError("ModifyAnnualFR", $sql);
    }
    
    echo "</table>";
    
    // add the submit button
    echo "<center><input type='submit' name='UpdateRules' value='Update'></center>";
    
    echo "<BR>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc" 
    
?>
