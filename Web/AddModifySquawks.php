<?php
//-----------------------------------------------------------------------------
// 
// AddModifySquawks.php
// 
// PURPOSE: Displays the aircraft squawk screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      TailNumber - selected aircraft tailnumber
//      all - select all resources
//      pview - set true to build a screen suitable for printing
//      AddModifySquawks - set to OK to save a squawk for an aircraft
//      ModifySquawk - set to 1 to modify existing squawk otherwise adding new
//      AllowModifications - set to one if we are called from a screen that
//                  is modifying squawks.
//      RowNumber - row number within the table when modifying a squawk
//      debug_flag - set to non-zero to enable debug output information
//
//      Problem description fields
//          SquawkEntryDate - date the squawk was entered
//          SquawkTachTime - tach time when the squawk was entered
//          SquawkUserName - user name of the person entering the squawk
//          SquawkDescription - description of the squawk
//          SquawkIsGrounded - true if squawk grounded the aircraft
//
//      Repair description fields (only used if ModifySquawk == 1)
//          SquawkRepairDate - date of squawk repair
//          SquawkRepairTach - tach time when repair was made
//          SquawkRepairMechanic - initials of repairing mechanic
//          SquawkRepairDescription - description of the repair made
//
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
//      This screen is designed to be a popup so it does not perform the normal
//      header and trailer funcions. It also does not support goback to return
//      to previous screens.
// 
// -----------------------------------------------------------------------------

    include "global_def.inc";
    include "config.inc";
    include "AircraftScheduling_auth.inc";
    include "$dbsys.inc";
    include "functions.inc";
    require_once("CurrencyFunctions.inc");
    require_once("DatabaseFunctions.inc");
    require_once("SquawkFunctions.inc");
    require_once("PrintFunctions.inc");

    // initialize variables
    $all = '';
    $TailNumber = '';
    $ModifySquawk = 0;
    $AllowModifications = 0;
    $RowNumber = -1;
    $SquawkEntryDate = "now";
    $SquawkTachTime = 0.0;
    $SquawkUserName = getUserName();
    $SquawkDescription = '';
    $SquawkIsGrounded = 0;
    $SquawkRepairDate = "";
    $SquawkRepairTach = 0;
    $SquawkRepairMechanic = "";
    $SquawkRepairDescription = "";
        
    // set the line feed/carriage return replacement string
    $LFCRReplacement = " ";
        
    // set the column sizes
    $Column1Width = "15%";
    $Column2Width = "18%";
    $Column3Width = "15%";
    $Column4Width = "18%";
    $Column5Width = "15%";
    $Column6Width = "19%";

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
    if(isset($rdata["TailNumber"])) $TailNumber = $rdata["TailNumber"];
    if(isset($rdata["AddModifySquawks"])) $AddModifySquawks = $rdata["AddModifySquawks"];
    if(isset($rdata["ModifySquawk"])) $ModifySquawk = $rdata["ModifySquawk"];
    if(isset($rdata["AllowModifications"])) $AllowModifications = $rdata["AllowModifications"];
    if(isset($rdata["RowNumber"])) $RowNumber = $rdata["RowNumber"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];

    // problem description fields
    if(isset($rdata["SquawkEntryDate"])) $SquawkEntryDate = $rdata["SquawkEntryDate"];
    if(isset($rdata["SquawkTachTime"])) $SquawkTachTime = $rdata["SquawkTachTime"];
    if(isset($rdata["SquawkUserName"])) $SquawkUserName = $rdata["SquawkUserName"];
    if(isset($rdata["SquawkDescription"])) $SquawkDescription = $rdata["SquawkDescription"];
    if(isset($rdata["SquawkIsGrounded"])) $SquawkIsGrounded = $rdata["SquawkIsGrounded"];

    // repair description fields
    if(isset($rdata["SquawkRepairDate"])) $SquawkRepairDate = $rdata["SquawkRepairDate"];
    if(isset($rdata["SquawkRepairTach"])) $SquawkRepairTach = $rdata["SquawkRepairTach"];
    if(isset($rdata["SquawkRepairMechanic"])) $SquawkRepairMechanic = $rdata["SquawkRepairMechanic"];
    if(isset($rdata["SquawkRepairDescription"])) $SquawkRepairDescription = $rdata["SquawkRepairDescription"];
    
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // DisplayProblemDescription()
    //
    // Purpose: Display the problem description fields for a squawk.
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
    function DisplayProblemDescription()
    {
        global $ModifySquawk;
        
        // Problem description fields
        global $SquawkEntryDate;
        global $SquawkTachTime;
        global $SquawkUserName;
        global $SquawkDescription;
        global $SquawkIsGrounded;
        
        // column sizes
        global $Column1Width;
        global $Column2Width;
        global $Column3Width;
        global $Column4Width;
        global $Column5Width;
        global $Column6Width;
        
        $TextRows = 4;
        $TextColumns = 70;
        
        // start the table
        echo "<table border=0>";
        echo "<tr><td colspan=6 align=left><b>Problem Description</b></td></tr>";
        
        // date, tach time and user name row
        echo "<tr>";
        
        // date
        echo "<td align=left width=$Column1Width>Entry Date:</TD>";        
        echo "<td align=left width=$Column2Width>" . strftime("%d-%b-%Y", strtotime($SquawkEntryDate)) . "</TD>";        
        
        // tach time
        echo "<td align=left width=$Column3Width>Tach Time:</TD>";        
        echo "<td align=left width=$Column4Width>" . FormatField($SquawkTachTime, "FLOAT") . "</TD>";        
        
        // user name
        echo "<td align=left width=$Column5Width>Username:</TD>";        
        echo "<td align=left width=$Column6Width>$SquawkUserName</TD>";        
        
        // finish date, tach time and user name row
        echo "</tr>";

        // skip some space
        echo "<tr> </tr>";

        // description
        if ($ModifySquawk)
        {    
            // modifying squawk, display the title
            echo "<tr><td colspan=6 align=left><b>Description</b></td></tr>";
            echo "<tr><TD colspan=6>";
            echo "<TEXTAREA NAME='SquawkDescription' ID='SquawkDescription' " . 
                    "ROWS=$TextRows COLS=$TextColumns WRAP='virtual' READONLY>" . 
                    $SquawkDescription . 
                    "</TEXTAREA></TD></TR>";

            // skip some space
            echo "<tr> </tr>";
    
            // aircraft grounding
            echo "<tr><td colspan=6>";
            if ($SquawkIsGrounded)
            {
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' CHECKED VALUE=1>Aircraft Is Grounded";
        	    echo "<br>";
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' VALUE=0>Aircraft Is Not Grounded";
            }
            else
            {
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' VALUE=1>Aircraft Is Grounded";
        	    echo "<br>";
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' CHECKED VALUE=0>Aircraft Is Not Grounded";
            }
            echo "</TD></TR>";
        }
        else
        {
            // adding squawk, display the title
            echo "<tr><td colspan=6 align=left><b>Type a description of the problem:</b></td></tr>";
            echo "<tr><TD colspan=6>";
            echo "<TEXTAREA NAME='SquawkDescription' ID='SquawkDescription' " . 
                    "ROWS=$TextRows COLS=$TextColumns WRAP='virtual'>" . 
                    $SquawkDescription . 
                    "</TEXTAREA></TD></TR>";

            // skip some space
            echo "<tr> </tr>";
    
            // aircraft grounding
            echo "<tr><td colspan=6>";
            if ($SquawkIsGrounded)
            {
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' CHECKED VALUE=1>Aircraft Is Grounded";
        	    echo "<br>";
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' VALUE=0>Aircraft Is Not Grounded";
            }
            else
            {
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' VALUE=1>Aircraft Is Grounded";
        	    echo "<br>";
        	    echo "<INPUT NAME='SquawkIsGrounded' TYPE='RADIO' CHECKED VALUE=0>Aircraft Is Not Grounded";
            }
            echo "</TD></TR>";
        }

        // end the table
        echo "</table>";

    }
    //********************************************************************
    // DisplayRepairInformation()
    //
    // Purpose: Display the repair information fields for a squawk.
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
    function DisplayRepairInformation()
    {
        global $ModifySquawk;
        global $NotRepairedString, $RepairedString, $DeferredString, $ClosedString;

        // repair description fields
        global $SquawkRepairDate;
        global $SquawkRepairTach;
        global $SquawkRepairMechanic;
        global $SquawkRepairDescription;
        
        // column sizes
        global $Column1Width;
        global $Column2Width;
        global $Column3Width;
        global $Column4Width;
        global $Column5Width;
        global $Column6Width;
        global $Column7Width;
        global $Column8Width;

        $TextRows = 4;
        $TextColumns = 70;

    	// set the size of the input boxes
    	$ControlNameBoxSize = 10;
        
        // start the table
        echo "<table border=0>";
        echo "<tr><td colspan=6 align=left><b>Repair Description</b></td></tr>";
    
        // set the status of the repair fields based on the repair status
        if ($SquawkRepairDate == $DeferredString)
        {
            // squawk is deferred, disable all the repair fields
            $RepairDateTextBoxEnabled = "readonly";
            $RepairTachTextBoxEnabled = "";
            $MechanicTextBoxEnabled = "";
            $RepairDescriptionTextBoxEnabled = "";
            $RepairStatus = $DeferredString;
        }
        elseif ($SquawkRepairDate == $ClosedString)
        {
            // squawk is closed, enable all the repair fields
            $RepairDateTextBoxEnabled = "";
            $RepairTachTextBoxEnabled = "";
            $MechanicTextBoxEnabled = "";
            $RepairDescriptionTextBoxEnabled = "";
            $RepairStatus = $ClosedString;
        }
        elseif (Len(Trim($SquawkRepairDate)) != 0)
        {
            // squawk is repaired, enable all the repair fields
            $RepairDateTextBoxEnabled = "";
            $RepairTachTextBoxEnabled = "";
            $MechanicTextBoxEnabled = "";
            $RepairDescriptionTextBoxEnabled = "";
            $RepairStatus = $RepairedString;
        }
        else
        {
            // squawk is not repaired, disable all the repair fields
            $RepairDateTextBoxEnabled = "readonly";
            $RepairTachTextBoxEnabled = "readonly";
            $MechanicTextBoxEnabled = "readonly";
            $RepairDescriptionTextBoxEnabled = "readonly";
            $RepairStatus = $NotRepairedString;
        }
        
        // repair status
        echo "<tr><td>";
        echo "<select name='RepairStatus' id='RepairStatus' onchange='HandleRepairStatus()'>";
        if ($RepairStatus == $NotRepairedString)
            echo "<option value='$NotRepairedString' selected>$NotRepairedString</option>";
        else
            echo "<option value='$NotRepairedString'>$NotRepairedString</option>";
        if ($RepairStatus == $RepairedString)
            echo "<option value='$RepairedString' selected>$RepairedString</option>";
        else
            echo "<option value='$RepairedString'>$RepairedString</option>";
        if ($RepairStatus == $DeferredString)
            echo "<option value='$DeferredString' selected>$DeferredString</option>";
        else
            echo "<option value='$DeferredString'>$DeferredString</option>";
        if ($RepairStatus == $ClosedString)
            echo "<option value='$ClosedString' selected>$ClosedString</option>";
        else
            echo "<option value='$ClosedString'>$ClosedString</option>";
        echo "</select>";
        echo "</td></tr>";
       
        // generate the code to save the repair status for the javascipt
        // information variables
        echo "<SCRIPT LANGUAGE=\"JavaScript\">";
        echo "var DeferredString = '$DeferredString';";
        echo "var ClosedString = '$ClosedString';";
        echo "var NotRepairedString = '$NotRepairedString';";
        echo "var RepairedString = '$RepairedString';";
        echo "var DefaultSquawkRepairDate = '" . strftime("%d-%b-%Y") . "';";
        echo "</SCRIPT>";
        
        // repair date, tach time and mechanic row
        echo "<td><tr>";
        
        // repair date
        echo "<td align=left width=$Column1Width>Repair Date:</TD>";        
        echo "<TD ALIGN=left WIDTH=$Column2Width>" . 
                "<INPUT " .
                    "TYPE=TEXT " .
                    "NAME='SquawkRepairDate' " . 
                    "ID='SquawkRepairDate' " .
                    "ALIGN=RIGHT " . 
                    "SIZE=$ControlNameBoxSize " . 
                    "VALUE='" . $SquawkRepairDate . "' $RepairDateTextBoxEnabled>" . 
                "</TD>";
        
        // repair tach time
        echo "<td align=left width=$Column3Width>Repair Tach:</TD>";        
        echo "<TD ALIGN=left WIDTH=$Column4Width>" . 
                "<INPUT " .
                    "TYPE=TEXT " .
                    "NAME='SquawkRepairTach' " . 
                    "ID='SquawkRepairTach' " .
                    "ALIGN=RIGHT " . 
                    "SIZE=$ControlNameBoxSize " . 
                    "VALUE='" . $SquawkRepairTach . "' $RepairTachTextBoxEnabled>" . 
                "</TD>";
        
        // mechanic name
        echo "<td align=left width=$Column5Width>Mechanic:</TD>";        
        echo "<TD ALIGN=left WIDTH=$Column6Width>" . 
                "<INPUT " .
                    "TYPE=TEXT " .
                    "NAME='SquawkRepairMechanic' " . 
                    "ID='SquawkRepairMechanic' " .
                    "ALIGN=RIGHT " . 
                    "SIZE=$ControlNameBoxSize " . 
                    "VALUE='" . $SquawkRepairMechanic . "' $MechanicTextBoxEnabled>" . 
                "</TD>";
        
        // finish repair date, tach time and mechanic row
        echo "</tr>";

        // skip some space
        echo "<tr><td> </td></tr>";

        // repair description
        $TestString = "1\r\n2";
        echo "<tr><td colspan=6 align=left><b>Repair Description</b></td></tr>";
        echo "<tr><TD colspan=6>";
        echo "<TEXTAREA NAME='SquawkRepairDescription' " . 
                "ID='SquawkRepairDescription' " . 
                "ROWS=$TextRows COLS=$TextColumns WRAP='virtual' " . 
                "$RepairDescriptionTextBoxEnabled>" . 
                $SquawkRepairDescription . 
                "</TEXTAREA></TD></TR>";

        // end the table
        echo "</table>";

    }
   
    //********************************************************************
    // AddSquawkInformation($TailNumber)
    //
    // Purpose: Add a mew squawk to the database for the given aircraft.
    //
    // Inputs:
    //   TailNumber - aircraft tailnumber we are adding a squawk for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function AddSquawkInformation($TailNumber)
    {        
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $CheckOutProgressString, $OnLineString, $OffLineString;
        
        // Problem description fields
        global $SquawkEntryDate;
        global $SquawkTachTime;
        global $SquawkUserName;
        global $SquawkDescription;
        global $SquawkIsGrounded;

        // repair description fields
        global $SquawkRepairDate;
        global $SquawkRepairTach;
        global $SquawkRepairMechanic;
        global $SquawkRepairDescription;
    
        // if it is a grounding squawk, flag it in the database
        If ($SquawkIsGrounded)
        {
            $GroundingSquawk = 1;
        }
        else
        {
            // not a grounding squawk, don't reset the flag if we are already
            // grounded
            $GroundingSquawk = 0;
        }
    
        // add the new squawk to the database
        $DatabaseFields = array();

        // aircraft identifier
        SetDatabaseRecord("Aircraft", $TailNumber, $RSConversionString, $DatabaseFields[0]);
        
        // problem description
        SetDatabaseRecord("Date", "now", $RSConversionDate, $DatabaseFields[1]);
        SetDatabaseRecord("Initial_Tach", $SquawkTachTime, $RSConversionNumber, $DatabaseFields[2]);
        SetDatabaseRecord("KeyCode", $SquawkUserName, $RSConversionString, $DatabaseFields[3]);
        SetDatabaseRecord("Description", $SquawkDescription, $RSConversionString, $DatabaseFields[4]);
            
        // if it is a grounding squawk, flag it in the database
        SetDatabaseRecord("Grounding", $GroundingSquawk, $RSConversionNumber, $DatabaseFields[5]);
    
        // repair description
        SetDatabaseRecord("Repair_Date", " ", $RSConversionString, $DatabaseFields[6]);
        SetDatabaseRecord("Repair_Tach", 0, $RSConversionNumber, $DatabaseFields[7]);
        SetDatabaseRecord("Mechanic", " ", $RSConversionString, $DatabaseFields[8]);
        SetDatabaseRecord("Repair_Description", " ", $RSConversionString, $DatabaseFields[9]);
            
        AddDatabaseRecord("Squawks", $DatabaseFields);
                
        // update the aircraft status in the database if the squawk grounded the aircraft
        if ($GroundingSquawk)
        {
            $DatabaseFields = array();
            SetDatabaseRecord("status", LookupAircraftStatus($OffLineString), $RSConversionNumber, $DatabaseFields[0]);
            UpdateDatabaseRecord( 
                                 "AircraftScheduling_aircraft", 
                                 $DatabaseFields, 
                                 "n_number='" . $TailNumber . "'");
        }
    }
    
    //********************************************************************
    // UpdateExistingSquawk($TailNumber)
    //
    // Purpose:  Update an existing squawk in the database.
    //
    // Inputs:
    //   TailNumber - aircraft tailnumber we are adding a squawk for
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function UpdateExistingSquawk($TailNumber)
    {
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $CheckOutProgressString, $OnLineString, $OffLineString;
        
        // Problem description fields
        global $SquawkEntryDate;
        global $SquawkTachTime;
        global $SquawkUserName;
        global $SquawkDescription;
        global $SquawkIsGrounded;

        // repair description fields
        global $SquawkRepairDate;
        global $SquawkRepairTach;
        global $SquawkRepairMechanic;
        global $SquawkRepairDescription;

        $DatabaseFields = array();
        
        // aircraft identifier
        SetDatabaseRecord("Aircraft", $TailNumber, $RSConversionString, $DatabaseFields[0]);
        
        // problem description
        SetDatabaseRecord("Date", $SquawkEntryDate, $RSConversionDate, $DatabaseFields[1]);
        SetDatabaseRecord("Initial_Tach", $SquawkTachTime, $RSConversionNumber, $DatabaseFields[2]);
        SetDatabaseRecord("KeyCode", $SquawkUserName, $RSConversionString, $DatabaseFields[3]);
        SetDatabaseRecord("Description", $SquawkDescription, $RSConversionString, $DatabaseFields[4]);
            
        // if it is a grounding squawk, flag it in the database
        If ($SquawkIsGrounded)
        {
            SetDatabaseRecord("Grounding", 1, $RSConversionNumber, $DatabaseFields[5]);
            $GroundingSquawk = 1;
        }
        else
        {
            SetDatabaseRecord("Grounding", 0, $RSConversionNumber, $DatabaseFields[5]);
            $GroundingSquawk = 0;
        }
    
        // repair description
        SetDatabaseRecord("Repair_Date", $SquawkRepairDate, $RSConversionString, $DatabaseFields[6]);
        SetDatabaseRecord("Repair_Tach", $SquawkRepairTach, $RSConversionNumber, $DatabaseFields[7]);
        SetDatabaseRecord("Mechanic", $SquawkRepairMechanic, $RSConversionString, $DatabaseFields[8]);
        SetDatabaseRecord("Repair_Description", 
                                            $SquawkRepairDescription, $RSConversionString, $DatabaseFields[9]);
            
        UpdateDatabaseRecord(
                            "Squawks",
                            $DatabaseFields,
                            "(Aircraft='" . $TailNumber . "' AND " .
                                "Date='" . FormatField(DateValue($SquawkEntryDate), "DatabaseDate") . "' AND " .
                                "Initial_Tach=" . Str($SquawkTachTime) . " AND " .
                                "KeyCode='" . $SquawkUserName . "' AND " .
                                "Description='" . AddEscapes($SquawkDescription) . "')"
                                );
                
        // update the aircraft status in the database if the squawk grounded the aircraft
        if ($GroundingSquawk)
        {
            $DatabaseFields = array();
            SetDatabaseRecord("status", LookupAircraftStatus($OffLineString), $RSConversionNumber, $DatabaseFields[0]);
            UpdateDatabaseRecord( 
                                 "AircraftScheduling_aircraft", 
                                 $DatabaseFields, 
                                 "n_number='" . $TailNumber . "'");
        }
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }

    // stip any special characters from the description fields so that
    // the javascript doesn't get upset
    $SquawkDescription = AddEscapes(StripSpecialChars($SquawkDescription));
    $SquawkRepairDescription = AddEscapes(StripSpecialChars($SquawkRepairDescription));

    // this script will call itself whenever the submit button is pressed
    if(count($_POST) > 0 && $AddModifySquawks == "Submit")
    {
        // acquire mutex to prevent concurrent squawk modifications
        if (!sql_mutex_lock('AircraftScheduling_squawks'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // submit button selected
        
        // add or modify the squawk in the database
        if ($ModifySquawk)
        {    
            // modifying squawk
            UpdateExistingSquawk($TailNumber);

            // log the updated squawk in the journal
        	$Description = "Update squawk for aircraft " .
                                    $TailNumber .
                                    " User " . $SquawkUserName .
                                    " Date " . $SquawkEntryDate;
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
        else
        {
            // adding squawk
            AddSquawkInformation($TailNumber);

            // log the new squawk in the journal
        	$Description = "Member " . getUserName() . " (" . getName() . ") added a squawk for aircraft " . 
                                        $TailNumber;
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
        
        // put the header information in the form
        echo "<HEAD>";
        echo "<STYLE TYPE='text/css'>";
        echo "P.breakhere {page-break-before: always}";
        echo "</STYLE>";
        ?>
        
        <!-- ############################### javascript procedures ######################### -->
        <script type="text/javascript">
        //<!--
        
        //********************************************************************
        // CompleteSubmit(
        //                TailNumber,
        //                NumberOfCopies,
        //                ModifySquawk,
        //                RowNumber, 
        //                EntryDate, 
        //                SquawkRepairDate, 
        //                SquawkIsGrounded, 
        //                SquawkDescription,
        //                SquawkTachTime,
        //                SquawkUserName,
        //                SquawkRepairTach,
        //                SquawkRepairMechanic,
        //                SquawkRepairDescription,
        //                AllowModifications)
        //
        // Purpose: Complete the processing of the aircraft squawk form.
        //
        // Inputs:
        //   TailNumber - tailnumber of the aircraft squawk (for new squawks)
        //   NumberOfCopies - number of copies of the squawk sheet to print
        //   ModifySquawk - set to 1 if we are modifying an existing squawk
        //   RowNumber - row number within the table when modifying a squawk
        //   EntryDate - date the squawk was entered
        //   SquawkRepairDate - date repair was made
        //   SquawkIsGrounded - true if squawk grounded the aircraft
        //   SquawkDescription - description of the squawk
        //   SquawkTachTime - tach time when the squawk was entered
        //   SquawkUserName - username that entered the squawk
        //   SquawkRepairTach - tach time of the repair
        //   SquawkRepairMechanic - mechanic making the repair
        //   SquawkRepairDescription - repair description
        //   AllowModifications - set to one if we are called from a screen that
        //                  is modifying squawks.
        //
        // Outputs:
        //   none
        //
        // Returns:
        //   none
        //*********************************************************************
        function CompleteSubmit(
                                TailNumber,
                                NumberOfCopies,
                                ModifySquawk,
                                RowNumber, 
                                EntryDate, 
                                SquawkRepairDate, 
                                SquawkIsGrounded, 
                                SquawkDescription,
                                SquawkTachTime,
                                SquawkUserName,
                                SquawkRepairTach,
                                SquawkRepairMechanic,
                                SquawkRepairDescription,
                                AllowModifications)
        {
            // print the squawk sheets
            if (NumberOfCopies > 0) window.print();
    
            // if we are modifying a squawk
            if (ModifySquawk)
            {
                // the enclosing screen for the popup is the frame
                AircraftSquawks = self.opener.document.getElementById('AircraftSquawks');
            }
            else
            {
                // the enclosing screen for the popup is the parent screen
                AircraftSquawks = self.opener.SquawkFrame.document.getElementById('AircraftSquawks');
                
                // adding a new squawk, get the row number we are adding
                RowNumber = AircraftSquawks.rows.length;
            }
            
            // set the grounding value
            if (SquawkIsGrounded)
                GroundedString = "Yes";
            else
                GroundedString = "No";

            // build the URL for modifications
            ModURL = "?ModifySquawk=1" +
                      "&RowNumber=" + RowNumber +
                      "&TailNumber=" + TailNumber +
                      "&SquawkEntryDate=" + EntryDate +
                      "&SquawkTachTime=" + SquawkTachTime +
                      "&SquawkUserName=" + SquawkUserName +
                      "&SquawkDescription=" + SquawkDescription +
                      "&SquawkIsGrounded=" + SquawkIsGrounded +
                      "&SquawkRepairDate=" + SquawkRepairDate +
                      "&SquawkRepairTach=" + SquawkRepairTach +
                      "&SquawkRepairMechanic=" + SquawkRepairMechanic +
                      "&SquawkRepairDescription=" + SquawkRepairDescription;
                      
            // if we are modifying a squawk
            if (ModifySquawk)
            {
                // modifying an existing squawk, get the requested row
                var x = AircraftSquawks.rows;
                var y = x[RowNumber].cells;
                y[0].innerHTML =
                        "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                    EntryDate + "</a>"; 
                y[1].innerHTML = 
                        "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                    SquawkRepairDate + "</a>"; 
                y[2].innerHTML = 
                        "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                    "<center>" + GroundedString + "</center>" + "</a>"; 
                y[3].innerHTML = 
                        "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                    SquawkDescription + "</a>"; 
            }
            else
            {
                // adding a new squawk, add a new row
                var NewRow;
                var oCell;
                NewRow = AircraftSquawks.insertRow(-1);
                NewCellColumn1 = NewRow.insertCell(-1);
                NewCellColumn2 = NewRow.insertCell(-1);
                NewCellColumn3 = NewRow.insertCell(-1);
                
                // if we were called from a screen that allowed modifications
                // set the new entry to call the modify squawk screen
                if (AllowModifications)
                {
                    // modifications are allowed
                    NewCellColumn4 = NewRow.insertCell(-1);
                    NewCellColumn1.innerHTML = 
                            "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                        EntryDate + "</a>";
                    NewCellColumn2.innerHTML =  
                            "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                        SquawkRepairDate + "</a>";
                    NewCellColumn3.innerHTML = 
                            "<center>" + 
                            "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                        GroundedString + "</a>" +
                            "</center>";
                    NewCellColumn4.innerHTML = 
                            "<a href=\"javascript:AddModifySquawk(1, '" + ModURL + "')\">" + 
                                        SquawkDescription + "</a>";
                }
                else
                {
                    // no modifications allowed
                    NewCellColumn1.innerHTML = EntryDate;
                    NewCellColumn2.innerHTML = "<center>" + GroundedString + "</center>";
                    NewCellColumn3.innerHTML = SquawkDescription;
                }
            }
           
            // close the window when we are finished
            window.close();
        }
        
        //-->
        </script>
        <?php
        // get thep number of copies of the squawk we need to print
        if ($ModifySquawk)
        {
            // modifying a squawk, don't print any forms
            $NumberOfCopies = 0;
        }
        else
        {
            // adding a squawk, print a copy of the maintenance form if needed    
            $NumberOfCopies = GetServerPreferenceValue("Number_of_Fault_Record_Copies");
        }
        
        echo "</HEAD>";
	    echo "<BODY " . 
               "onload=\"CompleteSubmit(" .
                    "'$TailNumber', " .
                    "$NumberOfCopies, " .
                    "$ModifySquawk, " .
                    "$RowNumber, " . 
                    "'" . FormatField($SquawkEntryDate, "Date") . "', " .
                    "'" . $SquawkRepairDate . "', " .
                    "$SquawkIsGrounded, " .
                    "'$SquawkDescription', " . 
                    "$SquawkTachTime, " . 
                    "'$SquawkUserName', " . 
                    "'$SquawkRepairTach', " . 
                    "'$SquawkRepairMechanic', " . 
                    "'$SquawkRepairDescription', " . 
                    "$AllowModifications" .
                   ")\">";
	    echo "<FORM NAME='main'>";
	            
        // print the aircraft squawk form copies if we are adding a squawk
        if (!$ModifySquawk)
        {
            // adding a squawk, get the aircraft information from the database    
            $sql = "SELECT " .
                        "n_number, " .
                        "hourly_cost, " .
                        "tach1 " .
                    "FROM AircraftScheduling_aircraft " .  
                    "WHERE n_number='$TailNumber'";    
            $res = sql_query($sql);
            
            // if we didn't have any errors, process the results of the database inquiry
            if($res) 
            {
                // process the results of the database inquiry
                $AircraftRST = sql_row($res, 0);
                
                // print a copy of the maintenance form if needed    
                for ($i = 0; $i < $NumberOfCopies; $i++)
                {
                    PrintAircraftFaultRecord(
                                                $AircraftRST,
                                                $SquawkDescription,
                                                $SquawkRepairDescription,
                                                $SquawkRepairDate,
                                                $SquawkRepairTach,
                                                $SquawkRepairMechanic,
                                                getName(),
                                                getPhoneNumber2(),
                                                getPhoneNumber(),
                                                $SquawkEntryDate);
                                
                    // if this is not the last page, print a page break
                    if ($i < ($NumberOfCopies - 1))
                    {
                        // put a page break between pages
                        PrintNewPage();
                    }
                }
            }
            else
            {
                // error processing database request, tell the user
                DisplayDatabaseError("AddModifySquawks", $sql);
            }
        }
           
        // end the form
        echo "</FORM>";
        
        // complete the form
        echo "</BODY>";
                    
        // finished with this part of the script
        sql_mutex_unlock('AircraftScheduling_squawks');
        exit;
    }

    // submit was not selected, display the main screen
    
    // print the page header
    $pview = 1;     // we are a popup, don't display all the headers
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
    
    // start the form
	echo "<FORM NAME='main' ACTION='AddModifySquawks.php' METHOD='POST'>";
    
    // display the title based on adding or modifying
    echo "<center>";
    if ($ModifySquawk)
    {    
        // modifying squawk, display the title
        echo "<H2>Modify Aircraft Squawk</H2>";
    }
    else
    {
        // adding squawk, display the title
        echo "<H2>New Aircraft Squawk</H2>";
    }

    // display the aircraft tailnumber
    echo "<H4>Aircraft: $TailNumber</H4>";

    // start the table to display the squawk information
    echo "<table border=0>";
    
    // display the problem description
    echo "<tr>";
    DisplayProblemDescription();
    echo "</tr>";
    
    // if we are modifying an existing squawk, put up the repair information
    if ($ModifySquawk)
    {
        // skip some space
        echo "<tr><td><br></td></tr>";
        
        // display the repair information
        echo "<tr>";
        DisplayRepairInformation();
        echo "</tr>";
    }
    
    // finished with the table
    echo "</table>";
    
    // save the variables for submitting the form
    echo "<INPUT NAME='TailNumber' TYPE='HIDDEN' VALUE='$TailNumber'>\n";
    echo "<INPUT NAME='ModifySquawk' TYPE='HIDDEN' VALUE='$ModifySquawk'>\n";
    echo "<INPUT NAME='SquawkEntryDate' TYPE='HIDDEN' VALUE='$SquawkEntryDate'>\n";
    echo "<INPUT NAME='SquawkTachTime' TYPE='HIDDEN' VALUE='$SquawkTachTime'>\n";
    echo "<INPUT NAME='SquawkUserName' TYPE='HIDDEN' VALUE='$SquawkUserName'>\n";
    echo "<INPUT NAME='RowNumber' TYPE='HIDDEN' VALUE='$RowNumber'>\n";
    echo "<INPUT NAME='AllowModifications' TYPE='HIDDEN' VALUE='$AllowModifications'>\n";
   
    // generate the update and cancel buttons
    echo "<TABLE>";
    echo "<TR>";
    echo "<TD><input name='AddModifySquawks' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    echo "<TD><input name='SquawksCancel' type=button value='Cancel' onClick=\"HandleCancelButton('" .  
                    $lang["CancelSquawk"] . "')\"></TD>";
    echo "</TR>";
    echo "</TABLE>";
    
    echo "</center>";

    // save the update type for the javascript code
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "var ModifySquawk = $ModifySquawk;";
    echo "</SCRIPT>";

    // end the form
    echo "</FORM>";
    
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
    // are we updating or adding a new squawk?
    if (ModifySquawk)
    {
        // updating an existing squawk
        
        // get the current setting of the repair status control
        RepairStatus = document.getElementById('RepairStatus').value;
            
        //  if the repair status is repaired
        if (RepairStatus == RepairedString)
        {                
            // validate that a repair date was entered
            SquawkRepairDate = document.getElementById('SquawkRepairDate').value;
            if (SquawkRepairDate.length == 0)
        	{
                // tell the user that a date is required
                alert("The date of the squawk repair is required");
                document.getElementById('SquawkRepairDate').focus();
                document.getElementById('SquawkRepairDate').select();
    
                // error found, don't let them continue
                return false;
            }

            // validate that a repair tach was entered                
            SquawkRepairTach = document.getElementById('SquawkRepairTach').value;
            if (SquawkRepairTach.length == 0)
        	{
                // tell the user that a tach is required
                alert("The aircraft ending tach for the squawk repair is required");
                document.getElementById('SquawkRepairTach').focus();
                document.getElementById('SquawkRepairTach').select();
    
                // error found, don't let them continue
                return false;
            }
                
            // validate that a repair mechanic was entered
            SquawkRepairMechanic = document.getElementById('SquawkRepairMechanic').value;
            if (SquawkRepairMechanic.length == 0)
        	{
                // tell the user that a mechanic is required
                alert("The initials or name of the technician repairing the squawk is required");
                document.getElementById('SquawkRepairMechanic').focus();
                document.getElementById('SquawkRepairMechanic').select();
    
                // error found, don't let them continue
                return false;
            }
                
            // validate that a repair description was entered
            SquawkRepairDescription = document.getElementById('SquawkRepairDescription').value;
            if (SquawkRepairDescription.length == 0)
        	{
                // tell the user that a description is required
                alert("A description of the squawk repair is required");
                document.getElementById('SquawkRepairDescription').focus();
                document.getElementById('SquawkRepairDescription').select();
    
                // error found, don't let them continue
                return false;
            }
        }
    }
    else
    {
        // adding a new squawk
            
        // validate that a description was entered
        SquawkDescription = document.getElementById('SquawkDescription').value;
        if (SquawkDescription.length == 0)
    	{
            // tell the user that a description is required
            alert("A description of the squawk is required");
            document.getElementById('SquawkDescription').focus();
            document.getElementById('SquawkDescription').select();

            // error found, don't let them continue
            return false;
        }
    }
    
    // no errors found, return
	return true;
}

//********************************************************************
// HandleCancelButton(CloseMessage)
//
// Purpose: Verify that the user wants to close the window. Close the
//          window if the user confirms.
//
// Inputs:
//   CloseMessage - message to ask user to confirm the close
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function HandleCancelButton(CloseMessage)
{        
    // see if the user really wants to cancel
    Response = confirm(CloseMessage);
    if (Response)
    {
        // user said to close the window
        window.close();
    }
        
    // user said not to exit
    return false;
}

//********************************************************************
// HandleRepairStatus()
//
// Purpose:  Handle a change in the RepairStatus combobox
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
function HandleRepairStatus()
{
    // get the current setting of the repair status control
    RepairStatus = document.getElementById('RepairStatus').value;
    
    // repair status
    switch (RepairStatus)
    {
    case DeferredString:
        // squawk is deferred, disable all the repair fields
        document.getElementById('SquawkRepairDate').value = DeferredString;
        document.getElementById('SquawkRepairDate').readOnly = true;
        document.getElementById('SquawkRepairTach').readOnly = false;
        document.getElementById('SquawkRepairMechanic').readOnly = false;
        document.getElementById('SquawkRepairDescription').readOnly = false;
        break;
    case ClosedString:
        // squawk is closed, enable all the repair fields
        document.getElementById('SquawkRepairDate').value = ClosedString;
        document.getElementById('SquawkRepairDate').readOnly = false;
        document.getElementById('SquawkRepairTach').readOnly = false;
        document.getElementById('SquawkRepairMechanic').readOnly = false;
        document.getElementById('SquawkRepairDescription').readOnly = false;
        break;
    case RepairedString:
        // squawk is repaired, enable all the repair fields
        document.getElementById('SquawkRepairDate').value = DefaultSquawkRepairDate;
        document.getElementById('SquawkRepairDate').readOnly = false;
        document.getElementById('SquawkRepairTach').readOnly = false;
        document.getElementById('SquawkRepairMechanic').readOnly = false;
        document.getElementById('SquawkRepairDescription').readOnly = false;
        break;
    default:
        // squawk is not repaired, disable all the repair fields
        document.getElementById('SquawkRepairDate').value = " ";
        document.getElementById('SquawkRepairDate').readOnly = true;
        document.getElementById('SquawkRepairTach').readOnly = true;
        document.getElementById('SquawkRepairMechanic').readOnly = true;
        document.getElementById('SquawkRepairDescription').readOnly = true;
        break;
    }
}

//-->
</script>
