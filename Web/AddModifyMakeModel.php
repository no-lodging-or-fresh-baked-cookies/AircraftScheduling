<?php
//-----------------------------------------------------------------------------
// 
// AddModifyMakeModel.php
// 
// PURPOSE: Displays a screen to allow the adding or modifing of the aircraft
//          makes and models.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      add_make - set to non-zero to add a new make
//      add_model - set to non-zero to add a new model
//      makes - selected make
//      models - selected model
//      delete - set to non-zero to delete
//      UpdateComplexity - set to true to modify the aircraft complexity information
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
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
    
    // initialize variables
    $delete = false;
    $MaxComplexityFields = 100;          // max number of complexity fields to allow
    $AircraftComplexity = array();
    $AircraftTypeLabel = array();
    $UpdateComplexity = 0;
    
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["add_make"])) $add_make = $rdata["add_make"];
    if(isset($rdata["add_model"])) $add_model = $rdata["add_model"];
    if(isset($rdata["makes"])) $makes = $rdata["makes"];
    if(isset($rdata["models"])) $models = $rdata["models"];
    if(isset($rdata["delete"])) $delete = $rdata["delete"];
    if(isset($rdata["UpdateComplexity"])) $UpdateComplexity = $rdata["UpdateComplexity"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    
    // get the complexity fields
    for ($i = 0; $i < $MaxComplexityFields; $i++)
    {
        if(isset($rdata["AircraftComplexity$i"])) $AircraftComplexity[$i] = $rdata["AircraftComplexity$i"];
        if(isset($rdata["AircraftTypeLabel$i"])) $AircraftTypeLabel[$i] = $rdata["AircraftTypeLabel$i"];
    }
            
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // AircraftTypeIsStandalone(
    //                AircraftType as string,
    //                RuleComplexity as integer) as Boolean
    //
    // Purpose:  Determine if a particular aircraft is standalone
    //
    // Inputs:
    //   AircraftType - the aircraft type (C-172, C-152, etc.)
    //   RuleComplexity - the current complexity of the aircraft
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   Returns True if the aircraft is standalone
    //*********************************************************************
    function AircraftTypeIsStandalone($AircraftType, $RuleComplexity)
    {        
        global $FlightTest, $WrittenTest, $InitialCheckout;
        
        // if the rule complexity is greater than one, it can't be standalone
        // since other aircraft can clear this one
        if ($RuleComplexity > 1)
        {
            $AircraftTypeIsStandalone = false;
            return $AircraftTypeIsStandalone;
        }
        
        // Open Recordset from the query
        $RecordCount = sql_query1("SELECT COUNT(*) FROM CurrencyRules WHERE Instr(Pass_Criteria,'" .
                        $AircraftType . "_" . $FlightTest . "')");
        
        // determine the number of rules that the aircraft type occurs in
        // if it is more than one, the aircraft is not standalone
        if ($RecordCount > 1)
        {
            $AircraftTypeIsStandalone = false;
            return $AircraftTypeIsStandalone;
        }
        
        // aircraft must be standalone
        $AircraftTypeIsStandalone = true;
        return $AircraftTypeIsStandalone;
    }
    
    //********************************************************************
    // CreateNewModel($AircraftModel As String)
    //
    // Purpose:  Add a mew aircraft model to the database.
    //
    // Inputs:
    //   AircraftModel - number new aircraft types
    //
    // Outputs:
    //  none
    //
    // Returns:
    //   none
    //*********************************************************************
    function CreateNewModel($AircraftModel)
    {
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $FlightTest, $WrittenTest, $InitialCheckout;
        global $AllowAircraftCheckout;
        global $CurrencyRequiredToFly, $CurrencyRequiredToSolo, $CurrencyRequiredToSoloRental;
        global $CurrencyInformation;
        global $FlightTimeExpirationTime;

        // write the model information to the database
        $DatabaseFields = array();
        SetDatabaseRecord(
                            "model",
                            $AircraftModel,
                            $RSConversionString,
                            $DatabaseFields[0]);
        SetDatabaseRecord("hourly_cost", 0, $RSConversionNumber, $DatabaseFields[1]);
        SetDatabaseRecord("rental_fee", 0, $RSConversionNumber, $DatabaseFields[2]);
        AddDatabaseRecord("AircraftScheduling_model", $DatabaseFields);

    	// log the change in to the journal
    	$Description = 
    				"Added model $AircraftModel to the database.";
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        
        // write a currency rule for the new aircraft model if
        // we are allowing aircraft checkout. the rule written assumes
        // a type complexity of 0 (doesn't clear anything). this can be
        // changed later if the user wants
    	if ($AllowAircraftCheckout)
    	{               
            // get the count of the number of currency rules
            $CurrencyRSTRecordCount = sql_query1("SELECT COUNT(*) FROM CurrencyRules");

            // add the aircraft written test to the currency rules
            $DatabaseFields = array();
            SetDatabaseRecord("ID", $CurrencyRSTRecordCount + 2, $RSConversionNumber, $DatabaseFields[0]);
            SetDatabaseRecord("Item", $AircraftModel . " Written Test ", $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Pass_Criteria", 
                                Chr(34) . $AircraftModel . Chr(34) . "_" . 
                                        $WrittenTest . " + " . $FlightTimeExpirationTime . " >= Now", 
                                $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("Student", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[3]);
            SetDatabaseRecord("Private_Under_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[4]);
            SetDatabaseRecord("Private_Over_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[5]);
            SetDatabaseRecord("Instrument", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[6]);
            SetDatabaseRecord("CFI", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[7]);
            SetDatabaseRecord("Expires_Month_End", 1, $RSConversionNumber, $DatabaseFields[8]);
            AddDatabaseRecord("CurrencyRules", $DatabaseFields);
            
            // add the aircraft initial checkout test to the currency rules
            $DatabaseFields = array();
            SetDatabaseRecord("ID", $CurrencyRSTRecordCount + 2, $RSConversionNumber, $DatabaseFields[0]);
            SetDatabaseRecord("Item", $AircraftModel . " Initial Checkout ", $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Pass_Criteria", 
                                Chr(34) . $AircraftModel . Chr(34) . "_" . 
                                        $InitialCheckout . " + 100Y >= Now", 
                                $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("Student", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[3]);
            SetDatabaseRecord("Private_Under_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[4]);
            SetDatabaseRecord("Private_Over_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[5]);
            SetDatabaseRecord("Instrument", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[6]);
            SetDatabaseRecord("CFI", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[7]);
            SetDatabaseRecord("Expires_Month_End", 1, $RSConversionNumber, $DatabaseFields[8]);
            AddDatabaseRecord("CurrencyRules", $DatabaseFields);
            
            // add the aircraft flight test to the currency rules
            $DatabaseFields = array();
            SetDatabaseRecord("ID", $CurrencyRSTRecordCount + 2, $RSConversionNumber, $DatabaseFields[0]);
            SetDatabaseRecord("Item", $AircraftModel . " Flight Test ", $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Pass_Criteria", 
                                Chr(34) . UCase($AircraftModel) . Chr(34) . "_" . 
                                        $FlightTest . " + " . $FlightTimeExpirationTime . " >= Now", 
                                $RSConversionString, $DatabaseFields[2]);
            SetDatabaseRecord("Student", "No", $RSConversionString, $DatabaseFields[3]);
            SetDatabaseRecord("Private_Under_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[4]);
            SetDatabaseRecord("Private_Over_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[5]);
            SetDatabaseRecord("Instrument", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[6]);
            SetDatabaseRecord("CFI", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[7]);
            SetDatabaseRecord("Expires_Month_End", 1, $RSConversionNumber, $DatabaseFields[8]);
            AddDatabaseRecord("CurrencyRules", $DatabaseFields);
            
            // add the aircraft type to the currency fields database
            $DatabaseFields = array();
            SetDatabaseRecord("Currency_Field_Name", 
                              Chr(34) . UCase($AircraftModel) . Chr(34) . "_" . $FlightTest, 
                              $RSConversionString, $DatabaseFields[0]);
            SetDatabaseRecord("Currency_Field_Type", "Date", $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Student", 0, $RSConversionNumber, $DatabaseFields[2]);
            SetDatabaseRecord("Private_Under_200", 1, $RSConversionNumber, $DatabaseFields[3]);
            SetDatabaseRecord("Private_Over_200", 1, $RSConversionNumber, $DatabaseFields[4]);
            SetDatabaseRecord("Instrument", 1, $RSConversionNumber, $DatabaseFields[5]);
            SetDatabaseRecord("CFI", 1, $RSConversionNumber, $DatabaseFields[6]);
            AddDatabaseRecord("CurrencyFields", $DatabaseFields);
            
            $DatabaseFields = array();
            SetDatabaseRecord("Currency_Field_Name", 
                                Chr(34) . UCase($AircraftModel) . Chr(34) . "_" . $WrittenTest,
                                $RSConversionString, $DatabaseFields[0]);
            SetDatabaseRecord("Currency_Field_Type", "Date", $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Student", 1, $RSConversionNumber, $DatabaseFields[2]);
            SetDatabaseRecord("Private_Under_200", 1, $RSConversionNumber, $DatabaseFields[3]);
            SetDatabaseRecord("Private_Over_200", 1, $RSConversionNumber, $DatabaseFields[4]);
            SetDatabaseRecord("Instrument", 1, $RSConversionNumber, $DatabaseFields[5]);
            SetDatabaseRecord("CFI", 1, $RSConversionNumber, $DatabaseFields[6]);
            AddDatabaseRecord("CurrencyFields", $DatabaseFields);
            
            $DatabaseFields = array();
            SetDatabaseRecord("Currency_Field_Name", 
                                Chr(34) . UCase($AircraftModel) . Chr(34) . "_" . $InitialCheckout, 
                                $RSConversionString, $DatabaseFields[0]);
            SetDatabaseRecord("Currency_Field_Type", "Date", $RSConversionString, $DatabaseFields[1]);
            SetDatabaseRecord("Student", 1, $RSConversionNumber, $DatabaseFields[2]);
            SetDatabaseRecord("Private_Under_200", 1, $RSConversionNumber, $DatabaseFields[3]);
            SetDatabaseRecord("Private_Over_200", 1, $RSConversionNumber, $DatabaseFields[4]);
            SetDatabaseRecord("Instrument", 1, $RSConversionNumber, $DatabaseFields[5]);
            SetDatabaseRecord("CFI", 1, $RSConversionNumber, $DatabaseFields[6]);
            AddDatabaseRecord("CurrencyFields", $DatabaseFields);
        }
    }
    
    //********************************************************************
    // LoadAircraftTypeComplexity(
    //                            AircraftComplexity() As String,
    //                            AircraftTypeLabel() As String,
    //                            NumberNewAircraftType As Integer,
    //                            NewAircraftType() As String,
    //                            MaxComplexityTypes As Integer)
    //
    // Purpose:  Scan the currency rules for aircraft type rules and
    //           determine the complexity of the aircraft. We will
    //           determine the complexity by counting the number
    //           of subrules within the currency rule. An aircraft with
    //           only one subrule is the most complex, more subrules will
    //           be less complex.
    //
    // Inputs:
    //   NumberNewAircraftType - number new aircraft types
    //   NewAircraftType() - list of new aircraft types
    //   MaxComplexityTypes - maximum number of aircraft complexities
    //
    // Outputs:
    //   AircraftComplexity() - list of aircraft complexities
    //   AircraftTypeLabel() - list of aircraft types
    //
    // Returns:
    //   none
    //*********************************************************************
    function LoadAircraftTypeComplexity(
                                    &$AircraftComplexity,
                                    &$AircraftTypeLabel,
                                    $NumberNewAircraftType,
                                    $NewAircraftType,
                                    $MaxComplexityTypes)
    {                                    
        // get the currency rules from the database
		$sql = "SELECT Pass_Criteria FROM CurrencyRules ORDER BY ID";		
		$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {    	        
            // loop through all the rules to get the aircraft type rules
            $ControlIndex = 0;
    		
    		// build the selection entries
    		for($i=0; $row = sql_row($res, $i); $i++) 
    		{
                // get the rule from the record
                $CompleteRule = $row[0];
                
                // get the first subrule
                $SubRule = CurrencyGetNextSubRule($CompleteRule);
        
                // If this rule is a aircraft type rating rule there should be a matching
                // aircraft in the database. We assume that the first part of the subrule
                // (up to the "_") is the aircraft type
                $AircraftType = GetNextToken($SubRule, "_");
                
                // get the second token (flight or written). we will ignore
                // written tests since each aircraft has one
                $TestType = GetNextToken($SubRule, "_");
            
                // see if the aircraft type is found in the database and that it is
                // a flight test rule
                if (IsAircraftType($AircraftType) && UCase($TestType) == "FLIGHT")
                {
                    // aircraft was found, must be a type rule.
                    // process the rules, each rule can be made of several "subrules" seperated by
                    // logical operators. we will process each subrule to determine the complexity
                    // note that 1 is the most complex after the following loop
                    $RuleComplexity = 1;
                    while (Len($CompleteRule) > 0)
                    {
                        // remove the operator from the rule
                        $NextOperator = GetNextToken($CompleteRule, " ");
                        
                        $RuleComplexity = $RuleComplexity + 1;
                        $SubRule = CurrencyGetNextSubRule($CompleteRule);
                    }
                    
                    // subtract 1 for the initial checkout field
                    $RuleComplexity = $RuleComplexity - 1;
        
                    // if the aircraft is standalone (ie won't clear any other aircraft)
                    // set the complexity to 0
                    if (AircraftTypeIsStandalone($AircraftType, $RuleComplexity))
                    {
                        $RuleComplexity = 0;
                    }
                    else
                    {
                        // aircraft is not standalone, add one to the rule complexity
                        // so that zero is reserved for standalone aircraft
                        $RuleComplexity = $RuleComplexity + 1;
                    }
                    
                    // set the aircraft type and complexity
                    $AircraftTypeLabel[$ControlIndex] = $AircraftType;
                    $AircraftComplexity[$ControlIndex] = str($RuleComplexity);
                    
                    // point to next control
                    $ControlIndex = $ControlIndex + 1;
                }
            }
            
            // if the NumberNewAircraftType is non-zero, we are adding a
            // new type otherwise, we are modifing existing types
            if ($NumberNewAircraftType > 0)
            {
                for ($i = 0; $i < $NumberNewAircraftType; $i++)
                {
                    // see if this aircraft type is already in the list
                    $AircraftTypeInList = false;
                    for ($j = 0; $j < $ControlIndex; $j++)
                    {
                        if (strtoupper($NewAircraftType[$i]) == 
							strtoupper(RemoveCurrencyFieldQuotes($AircraftTypeLabel[$j])))
                        {
                            $AircraftTypeInList = true;
                        }
                    }
                    
                    // add the new aircraft type to the screen if it is not already there
                    if (!$AircraftTypeInList)
                    {
                        $AircraftTypeLabel[$ControlIndex] = $NewAircraftType[$i];
                        $AircraftComplexity[$ControlIndex] = Str($MaxComplexityTypes + 2);
                            
                        // point to next control
                        $ControlIndex = $ControlIndex + 1;
                    }
                }
            }
      	}
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("LoadAircraftTypeComplexity", $sql);
        }
    }

    //********************************************************************
    // UpdateAircraftModelTable(
    //                         AircraftTypeRules() As AircraftTypeRuleType,
    //                         AircraftTypeCount As Integer)
    //
    // Purpose:  Update the aircraft model table in the database. Any
    //           new aircraft types are added, existing ones are
    //           not touched.
    //
    // Inputs:
    //   AircraftTypeRules - array containing the aircraft types
    //   AircraftTypeCount - number of entries in the array
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function UpdateAircraftModelTable(
                            $AircraftTypeRulesAircraftType,
                            $AircraftTypeCount)
    {        
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;

        // loop through the aircraft type array and add any new types
        // to the database
        for ($TypeCounter = 0; $TypeCounter < $AircraftTypeCount; $TypeCounter++)
        {
            // if this type does not exist, add it
            
            // Open Recordset for the table we are interested in
            $AircraftModelRSTRecordCount = sql_query1(
                            "SELECT COUNT(*) FROM AircraftScheduling_model " .
                                "WHERE model='" .
                                $AircraftTypeRulesAircraftType[$TypeCounter] . "'");
                            
            if ($AircraftModelRSTRecordCount == 0)
            {
                // type does not exist, add it to the database
                $DatabaseFields = array();
                SetDatabaseRecord(
                                    "model",
                                    $AircraftTypeRulesAircraftType[$TypeCounter],
                                    $RSConversionString,
                                    $DatabaseFields[0]);
                SetDatabaseRecord("hourly_cost", 0, $RSConversionNumber, $DatabaseFields[1]);
                SetDatabaseRecord("rental_fee", 0, $RSConversionNumber, $DatabaseFields[2]);
                AddDatabaseRecord("AircraftScheduling_model", $DatabaseFields);
            }
       }
    }
    
    //********************************************************************
    // WriteCurrencyRules()
    //
    // Purpose:  Use the complexities set by the user to write the
    //           aircraft currency rules. A lower complexity number
    //           aircraft qualifies the user to fly any higher numbered
    //           complexity aircraft.
    //
    // Inputs:
    //   AircraftComplexity() - list of aircraft complexities
    //   AircraftTypeLabel() - list of aircraft types
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function WriteCurrencyRules(
                                $AircraftComplexity,
                                $AircraftTypeLabel)
    {
        global $FlightTest, $WrittenTest, $InitialCheckout;
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $CurrencyRequiredToFly, $CurrencyRequiredToSolo, $CurrencyRequiredToSoloRental;
        global $CurrencyInformation;
        global $SimulatorAircraftType, $PCATDAircraftType;
        global $FlightTimeExpirationTime;
                
        // build the currency rules for each aircraft in the screen
        $AircraftTypeRulesAircraftType = array();
        $AircraftTypeRulesTypeRule = array();
        $AircraftTypeRulesAircraftComplexity = array();
        $AircraftTypeCount = 0;
        $PreviousRules = "";
        $PreviousAircraftTypeComplexity = 0;
        while(1)
        {
            $EntryFound = false;
        
            // find the lowest number (highest complexity) in the table
            // note that 0 is a special case that says this aircraft type is standalone
            $PreviousSearchComplexity = count($AircraftComplexity) + 3;
            for ($i = 0; $i < count($AircraftComplexity); $i++)
            {
                if (Len($AircraftComplexity[$i]) > 0)
                {
                    if (Val($AircraftComplexity[$i]) < $PreviousSearchComplexity)
                    {
                        $PreviousSearchComplexity = Val($AircraftComplexity[$i]);
                        $LowestEntry = $i;
                        $EntryFound = true;
                    }
                }
            }
                    
            if ($EntryFound)
            {
                // a complexity entry found, build the complexity rule
                if (Val($AircraftComplexity[$LowestEntry]) > 0)
                {
                    // non-standalone aircraft type found, build the rule
                    // if the complexity of this aircraft type is the same as the previous
                    // complexity, these aircraft types are equal in complexity so we want
                    // both types to be cleared by the more complex and to clear each other
                    if (Val($AircraftComplexity[$LowestEntry]) == $PreviousAircraftTypeComplexity)
                    {
                        // aircraft types are equal, add the equal complexity aircraft type
                        // to this type rule
                        $AircraftTypeRulesAircraftType[$AircraftTypeCount] = 
                                                UCase(Trim($AircraftTypeLabel[$LowestEntry]));
                        $AircraftTypeRulesTypeRule[$AircraftTypeCount] = 
                                                Chr(34) . UCase($AircraftTypeLabel[$LowestEntry]) . Chr(34) .
                                                "_" . $FlightTest . " + " . $FlightTimeExpirationTime .
                                                " >= Now" . $PreviousRules;
                        $AircraftTypeRulesAircraftComplexity[$AircraftTypeCount] = 
                                                Val($AircraftComplexity[$LowestEntry]);
                                    
                        // if any of the previous aircraft entries are used, set this aircraft type to it's rule
                        if ($AircraftTypeCount > 1)
                        {
                            for ($i = 0; $i < $AircraftTypeCount; $i++)
                            {
                                if ($AircraftTypeRulesAircraftComplexity[$i] ==
                                    Val($AircraftComplexity[$LowestEntry]))
                                {
                                    $AircraftTypeRulesTypeRule[$i] = 
                                                            $AircraftTypeRulesTypeRule[$i] .
                                                            " | " .
                                                            Chr(34) . $AircraftTypeLabel[$LowestEntry] . Chr(34) .
                                                            "_" . $FlightTest . " + " . $FlightTimeExpirationTime .
                                                            " >= Now";
                                }
                            }
                        }
                        
                        // save the current rule. since we are processing from most complex
                        // to least complex, all previous rules will apply to the next aircraft
                        $PreviousRules = " | " . $AircraftTypeRulesTypeRule[$AircraftTypeCount];
                    }
                    else
                    {
                        // aircraft type complexity is less than previous type, let the
                        // previous type clear this type
                        $AircraftTypeRulesAircraftType[$AircraftTypeCount] = 
                                                             UCase(Trim($AircraftTypeLabel[$LowestEntry]));
                        $AircraftTypeRulesTypeRule[$AircraftTypeCount] = 
                                                             Chr(34) . $AircraftTypeLabel[$LowestEntry] . Chr(34) .
                                                             "_" . $FlightTest . " + " . $FlightTimeExpirationTime .
                                                             " >= Now" . $PreviousRules;
                        $AircraftTypeRulesAircraftComplexity[$AircraftTypeCount] = 
                                                             Val($AircraftComplexity[$LowestEntry]);
                                  
                        // save the current rule. since we are processing from most complex
                        // to least complex, all previous rules will apply to the next aircraft
                        $PreviousRules = " | " . $AircraftTypeRulesTypeRule[$AircraftTypeCount];
                    }
                }
                elseIf (Val($AircraftComplexity[$LowestEntry]) == 0)
                {
                    // a standalone aircraft found, build the rule
                    $AircraftTypeRulesAircraftType[$AircraftTypeCount] = 
                                                    UCase(Trim($AircraftTypeLabel[$LowestEntry]));
                    $AircraftTypeRulesTypeRule[$AircraftTypeCount] =
                                                    Chr(34) . UCase($AircraftTypeLabel[$LowestEntry]) . Chr(34) .
                                                    "_" . $FlightTest . " + " . $FlightTimeExpirationTime . " >= Now";
                    $AircraftTypeRulesAircraftComplexity[$AircraftTypeCount] = Val($AircraftComplexity[$LowestEntry]);
                }
                else
                {
                    // the simulator aircraft type, don't let it clear anything
                    $AircraftTypeRulesAircraftType[$AircraftTypeCount] = 
                                                    UCase(Trim($AircraftTypeLabel[$LowestEntry]));
                    $AircraftTypeRulesTypeRule[$AircraftTypeCount] = "";
                    $AircraftTypeRulesAircraftComplexity[$AircraftTypeCount] = 
                                                    Val($AircraftComplexity[$LowestEntry]);
                }
                        
                // save the current complexity in case the next is equal to this one
                $PreviousAircraftTypeComplexity = Val($AircraftComplexity[$LowestEntry]);
                
                // clear the last entry so we won't process it again
                $AircraftComplexity[$LowestEntry] = "";
                $AircraftTypeCount = $AircraftTypeCount + 1;
            }
            else
            {
                // if we didn't find a valid entry, we're done
                break;
            }
        }
        
        // the rules are built, save them to the database
        
        // delete all the old aircraft type currency rules from the database
        // so that they will appear on all screens in complexity order (the
        // following code will add them back in complexity order)
        DeleteOldTypeCurrencyRecords();
        
        // get the count of the number of currency rules
        $CurrencyRSTRecordCount = sql_query1("SELECT COUNT(*) FROM CurrencyRules");
        
        // add the currency records to the database for each aircraft type
        for ($i = 0; $i < $AircraftTypeCount; $i++)
        {
            // remove any quotes from the aircraft type for the displayed value
            $AircraftType = RemoveCurrencyFieldQuotes($AircraftTypeRulesAircraftType[$i]);
            
            // if it is not a simulator or a PCATD (simulators and PCATDs don't have currency rules),
            // add the rules to the database
            if ($AircraftTypeRulesAircraftType[$i] != $SimulatorAircraftType &&
                $AircraftTypeRulesAircraftType[$i] != $PCATDAircraftType)
            {
                // add the aircraft written test to the currency rules
                $DatabaseFields = array();
                SetDatabaseRecord("ID", $CurrencyRSTRecordCount + $i + 2, $RSConversionNumber, $DatabaseFields[0]);
                SetDatabaseRecord("Item", $AircraftType . " Written Test ", $RSConversionString, $DatabaseFields[1]);
                SetDatabaseRecord("Pass_Criteria", 
                                    Chr(34) . $AircraftTypeRulesAircraftType[$i] . Chr(34) . "_" . 
                                            $WrittenTest . " + " . $FlightTimeExpirationTime . " >= Now", 
                                    $RSConversionString, $DatabaseFields[2]);
                SetDatabaseRecord("Student", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[3]);
                SetDatabaseRecord("Private_Under_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[4]);
                SetDatabaseRecord("Private_Over_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[5]);
                SetDatabaseRecord("Instrument", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[6]);
                SetDatabaseRecord("CFI", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[7]);
                SetDatabaseRecord("Expires_Month_End", 1, $RSConversionNumber, $DatabaseFields[8]);
                AddDatabaseRecord("CurrencyRules", $DatabaseFields);
                
                // add the aircraft initial checkout test to the currency rules
                $DatabaseFields = array();
                SetDatabaseRecord("ID", $CurrencyRSTRecordCount + $i + 2, $RSConversionNumber, $DatabaseFields[0]);
                SetDatabaseRecord("Item", $AircraftType . " Initial Checkout ", $RSConversionString, $DatabaseFields[1]);
                SetDatabaseRecord("Pass_Criteria", 
                                    Chr(34) . $AircraftTypeRulesAircraftType[$i] . Chr(34) . "_" . 
                                            $InitialCheckout . " + 100Y >= Now", 
                                    $RSConversionString, $DatabaseFields[2]);
                SetDatabaseRecord("Student", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[3]);
                SetDatabaseRecord("Private_Under_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[4]);
                SetDatabaseRecord("Private_Over_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[5]);
                SetDatabaseRecord("Instrument", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[6]);
                SetDatabaseRecord("CFI", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[7]);
                SetDatabaseRecord("Expires_Month_End", 1, $RSConversionNumber, $DatabaseFields[8]);
                AddDatabaseRecord("CurrencyRules", $DatabaseFields);
                
                // add the aircraft flight test to the currency rules
                $DatabaseFields = array();
                SetDatabaseRecord("ID", $CurrencyRSTRecordCount + $i + 2, $RSConversionNumber, $DatabaseFields[0]);
                SetDatabaseRecord("Item", $AircraftType . " Flight Test ", $RSConversionString, $DatabaseFields[1]);
                SetDatabaseRecord("Pass_Criteria", $AircraftTypeRulesTypeRule[$i], $RSConversionString, $DatabaseFields[2]);
                SetDatabaseRecord("Student", "No", $RSConversionString, $DatabaseFields[3]);
                SetDatabaseRecord("Private_Under_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[4]);
                SetDatabaseRecord("Private_Over_200", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[5]);
                SetDatabaseRecord("Instrument", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[6]);
                SetDatabaseRecord("CFI", $CurrencyRequiredToSolo, $RSConversionString, $DatabaseFields[7]);
                SetDatabaseRecord("Expires_Month_End", 1, $RSConversionNumber, $DatabaseFields[8]);
                AddDatabaseRecord("CurrencyRules", $DatabaseFields);
            }
        }
      
        // add the currency field records to the database
        for ($i = 0; $i < $AircraftTypeCount; $i++)
        {
            // if it is not a simulator or a PCATD (simulators and PCATDs don't have currency rules),
            // add the rules to the database
            if ($AircraftTypeRulesAircraftType[$i] != $SimulatorAircraftType &&
                $AircraftTypeRulesAircraftType[$i] != $PCATDAircraftType)
            {                
                // add the aircraft type to the currency fields database
                $DatabaseFields = array();
                SetDatabaseRecord("Currency_Field_Name", 
                                  Chr(34) . UCase($AircraftTypeRulesAircraftType[$i]) . Chr(34) . "_" . $FlightTest, 
                                  $RSConversionString, $DatabaseFields[0]);
                SetDatabaseRecord("Currency_Field_Type", "Date", $RSConversionString, $DatabaseFields[1]);
                SetDatabaseRecord("Student", 0, $RSConversionNumber, $DatabaseFields[2]);
                SetDatabaseRecord("Private_Under_200", 1, $RSConversionNumber, $DatabaseFields[3]);
                SetDatabaseRecord("Private_Over_200", 1, $RSConversionNumber, $DatabaseFields[4]);
                SetDatabaseRecord("Instrument", 1, $RSConversionNumber, $DatabaseFields[5]);
                SetDatabaseRecord("CFI", 1, $RSConversionNumber, $DatabaseFields[6]);
                AddDatabaseRecord("CurrencyFields", $DatabaseFields);
                
                $DatabaseFields = array();
                SetDatabaseRecord("Currency_Field_Name", 
                                    Chr(34) . UCase($AircraftTypeRulesAircraftType[$i]) . Chr(34) . "_" . $WrittenTest,
                                    $RSConversionString, $DatabaseFields[0]);
                SetDatabaseRecord("Currency_Field_Type", "Date", $RSConversionString, $DatabaseFields[1]);
                SetDatabaseRecord("Student", 1, $RSConversionNumber, $DatabaseFields[2]);
                SetDatabaseRecord("Private_Under_200", 1, $RSConversionNumber, $DatabaseFields[3]);
                SetDatabaseRecord("Private_Over_200", 1, $RSConversionNumber, $DatabaseFields[4]);
                SetDatabaseRecord("Instrument", 1, $RSConversionNumber, $DatabaseFields[5]);
                SetDatabaseRecord("CFI", 1, $RSConversionNumber, $DatabaseFields[6]);
                AddDatabaseRecord("CurrencyFields", $DatabaseFields);
                
                $DatabaseFields = array();
                SetDatabaseRecord("Currency_Field_Name", 
                                    Chr(34) . UCase($AircraftTypeRulesAircraftType[$i]) . Chr(34) . "_" . $InitialCheckout, 
                                    $RSConversionString, $DatabaseFields[0]);
                SetDatabaseRecord("Currency_Field_Type", "Date", $RSConversionString, $DatabaseFields[1]);
                SetDatabaseRecord("Student", 1, $RSConversionNumber, $DatabaseFields[2]);
                SetDatabaseRecord("Private_Under_200", 1, $RSConversionNumber, $DatabaseFields[3]);
                SetDatabaseRecord("Private_Over_200", 1, $RSConversionNumber, $DatabaseFields[4]);
                SetDatabaseRecord("Instrument", 1, $RSConversionNumber, $DatabaseFields[5]);
                SetDatabaseRecord("CFI", 1, $RSConversionNumber, $DatabaseFields[6]);
                AddDatabaseRecord("CurrencyFields", $DatabaseFields);
            }
        }
        
        // add any new types to the model table
        UpdateAircraftModelTable($AircraftTypeRulesAircraftType, $AircraftTypeCount);
    }
    
    //********************************************************************
    // DeleteAircraftType(DeleteAircraftType As String)
    //
    // Purpose:  Delete the aircraft type currency rule and currency field.
    //
    // Inputs:
    //   DeleteAircraftType - the aircraft type
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DeleteAircraftType($DeleteAircraftType)
    {        
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $FlightTest, $WrittenTest, $InitialCheckout;
        
        include "DatabaseConstants.inc";
        
        // look for the flight test currency field record
        $DeleteAircraftTypeCondition =
                        "Currency_Field_Name='" .
                        Chr(34) . UCase(RemoveCurrencyFieldQuotes($DeleteAircraftType)) .
                        Chr(34) . "_" . $FlightTest . "'";
        $DeleteAircraftTypeResult =
                SQLOpenRecordset(
                                    "SELECT * FROM CurrencyFields WHERE " .
                                    $DeleteAircraftTypeCondition);
        
        // if the aircraft type flight test currency field record is found, delete it
        if (sql_count($DeleteAircraftTypeResult) > 0)
        {
            // record found, delete it
            DeleteDatabaseRecord("CurrencyFields", $DeleteAircraftTypeCondition);
        }
        
        // look for the written test currency field record
        $DeleteAircraftTypeCondition =
                        "Currency_Field_Name='" .
                        Chr(34) . UCase(RemoveCurrencyFieldQuotes($DeleteAircraftType)) .
                        Chr(34) . "_" . $WrittenTest . "'";
        $DeleteAircraftTypeResult =
                SQLOpenRecordset(
                                    "SELECT * FROM CurrencyFields WHERE " .
                                    $DeleteAircraftTypeCondition);
        
        // if the aircraft type written test currency field record is found, delete it
        if (sql_count($DeleteAircraftTypeResult) > 0)
        {
            // record found, delete it
            DeleteDatabaseRecord("CurrencyFields", $DeleteAircraftTypeCondition);
        }
        
        // look for the initial checkout currency field record
        $DeleteAircraftTypeCondition =
                        "Currency_Field_Name='" .
                        Chr(34) . UCase(RemoveCurrencyFieldQuotes($DeleteAircraftType)) .
                        Chr(34) . "_" . $InitialCheckout . "'";
        $DeleteAircraftTypeResult =
                SQLOpenRecordset(
                                    "SELECT * FROM CurrencyFields WHERE " .
                                    $DeleteAircraftTypeCondition);
        
        // if the aircraft type written test currency field record is found, delete it
        if (sql_count($DeleteAircraftTypeResult) > 0)
        {
            // record found, delete it
            DeleteDatabaseRecord("CurrencyFields", $DeleteAircraftTypeCondition);
        }
        
        // delete the currency rules and subrules for this aircraft type
        $CurrencyResult = SQLOpenRecordset("SELECT * FROM CurrencyRules");
    
        // loop through all the rules to get the aircraft type rules
		for($CurrencyCnt=0; $CurrencyRST = sql_row($CurrencyResult, $CurrencyCnt); $CurrencyCnt++) 
        {
            // get the rule from the record
            $CompleteRule = $CurrencyRST[$Currency_Rules_Pass_Criteria_offset];
            
            // get the first subrule
            $SubRule = CurrencyGetNextSubRule($CompleteRule);
            $NewCompleteRule = $SubRule;
    
            // If this rule is a aircraft type rating rule there should be a matching
            // aircraft in the database. We assume that the first part of the subrule
            // (up to the "_") is the aircraft type
            $RuleAircraftType = GetNextToken($SubRule, "_");
            
            // if this rule is for the deleted type of aircraft, delete the rule
            if (UCase(RemoveCurrencyFieldQuotes($RuleAircraftType)) == 
                        UCase(RemoveCurrencyFieldQuotes($DeleteAircraftType)))
            {
                // this rule is for this aircraft, delete the rule
                DeleteDatabaseRecord(
                                    "CurrencyRules",
                                    "Item='" . $CurrencyRST[$Currency_Rules_Item_offset] . "'");
            }
            else
            {
                // go through all the subrules and remove the subrule if it uses
                // the deleted aircraft type in the subrule
                while (Len($CompleteRule) > 0)
                {
                    // remove the operator from the rule
                    $NextOperator = GetNextToken($CompleteRule, " ");
                    
                    // get the next subrule
                    $SubRule = CurrencyGetNextSubRule($CompleteRule);
                    $SaveSubRule = $SubRule;
                    
                    // get the aircraft type from the rule
                    $RuleAircraftType = GetNextToken($SubRule, "_");
                    
                    // if this subrule is for the deleted type of aircraft, delete the subrule
                    if (RemoveCurrencyFieldQuotes($RuleAircraftType) != 
                                RemoveCurrencyFieldQuotes($DeleteAircraftType))
                    {
                        // this rule is not for the deleted aircraft, keep the subrule
                        $NewCompleteRule = $NewCompleteRule . " " . $NextOperator . " " . $SaveSubRule;
                    }
                }
                
                // write the updated rule to the database
                $DatabaseFields = array();
                SetDatabaseRecord("Pass_Criteria", $NewCompleteRule, $RSConversionString, $DatabaseFields[0]);
                UpdateDatabaseRecord(
                                    "CurrencyRules",
                                    $DatabaseFields,
                                    "Item='" . $CurrencyRST[$Currency_Rules_Item_offset] . "'");
            }
        }
        
        // loop through the member's currency information and remove this aircraft
        // type
        $MembersResult =
                SQLOpenRecordset("SELECT * FROM AircraftScheduling_person");
        
        // delete the currency field
		for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++) 
        {
            // make sure we don't time out
            set_time_limit(30);

            // load the currency fields from the database
            LoadDBCurrencyFields("", $MembersRST[$Rules_Field_offset]);
            
            // delete the currency fields
            DeleteCurrencyField(RemoveCurrencyFieldQuotes($DeleteAircraftType) . "_" . $FlightTest);
            DeleteCurrencyField(RemoveCurrencyFieldQuotes($DeleteAircraftType) . "_" . $WrittenTest);
            DeleteCurrencyField(RemoveCurrencyFieldQuotes($DeleteAircraftType) . "_" . $InitialCheckout);
    
            // save the rules values to the database field
            SaveDBCurrencyFields($RulesField);
                
            // write the updated rule to the database
            $DatabaseFields = array();
            SetDatabaseRecord("Rules_Field", $RulesField, $RSConversionString, $DatabaseFields[0]);
            UpdateDatabaseRecord(
                                "AircraftScheduling_person",
                                $DatabaseFields,
                                "username='" . $MembersRST[$username_offset] . "'");
        }
                
        // delete the requested model
        DeleteDatabaseRecord(
                    "AircraftScheduling_model",
                    "model='" . $DeleteAircraftType . "'");
    }

    //********************************************************************
    // DisplayMakeForm()
    //
    // Purpose: Display the form for adding or deleting an aircraft make.
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
    function DisplayMakeForm()
    {
        global $lang;
        
        // display the existing makes and let them select one to delete
        echo "<form name='del_makes' action='AddModifyMakeModel.php' method='POST'>";
        echo "<table border=0>";
        echo "<tr><td>";
        echo "<select name='makes' SIZE=5'>";
        
        $makes_res = sql_query(
                                "SELECT make_id, make, hourly_cost, rental_fee " .
                                "FROM AircraftScheduling_make " . 
                                "ORDER BY make");
        
        if($makes_res) 
        {
            for ($i = 0; ($row = sql_row($makes_res, $i)); $i++) 
            {
                echo "<option VALUE=$row[0]>" . stripslashes($row[1]) . "</option>\n";
            }
        }
        echo "</select></td>";
        echo "<td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td><input type='submit' name='delete' value='Delete' onClick=\"return confirm('" . $lang['confirmdel'] . "')\"></td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
        
        // put in the add form
        echo "<form name='add_makes' action='AddModifyMakeModel.php' method='POST'>";
        echo "Name: <input type='text' name='add_make' SIZE='15'><br>";
        echo "<input type='submit' value='Add Make'><br>";
        echo "</form>";
    }

    //********************************************************************
    // DisplayModelForm()
    //
    // Purpose: Display the form for adding or deleting an aircraft model.
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
    function DisplayModelForm()
    {
        global $lang;
        
        // display the existing models and let them select one to delete
        echo "<form name='del_models' action='AddModifyMakeModel.php' method='POST'>";
        echo "<table border=0>";
        echo "<tr><td>";
        echo "<select name='models' SIZE=5'>";
    
        $models_res = sql_query(
                                "SELECT model_id, model, hourly_cost, rental_fee " . 
                                "FROM AircraftScheduling_model " .
                                "ORDER BY model");
        
        if($models_res) 
        {
            for ($i = 0; ($row = sql_row($models_res, $i)); $i++) 
            {
                echo "<option VALUE=$row[0]>" . stripslashes($row[1]) . "</option>\n";
                $model_prices_hourly[$i] = "'$row[2]'";
                $model_prices_rental[$i] = "'$row[3]'";
            }
        }
    
        echo "</select></td><td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td><input type='submit' name='delete' value='Delete' onClick=\"return confirm('" . $lang["confirmdel"] . "')\"></td>";
        echo "</tr>";
        echo "</table>";
        echo "</form>";
        
        // put in the add model form
        echo "<form name='add_models' action='AddModifyMakeModel.php' method='POST'>";
        echo "Name: <input type='text' name='add_model' SIZE='15'><br>";
        echo "<input type='submit' value='Add Model'><br>";
        echo "</form>";
    }

    //********************************************************************
    // DisplayModelComplexityForm()
    //
    // Purpose: Display the form for adding or deleting an aircraft model.
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
    function DisplayModelComplexityForm()
    {
        global $SimulatorAircraftType, $PCATDAircraftType;
        global $MaxComplexityFields;
        
        include "DatabaseConstants.inc";

        // set the column sizes
        $Column1Width = "20%";
        $Column2Width = "40%";

    	// set the size of the input boxes
    	$ControlNameBoxSize = 4;
    	
    	// put all the models in the new list (the LoadAircraftTypeComplexity
    	// procedure will remove any duplicates
        $NumberNewAircraftType = 0;
        $NewAircraftType = array();
        $ModelsResult = SQLOpenRecordset(
                                "SELECT * " . 
                                "FROM AircraftScheduling_model " .
                                "ORDER BY model");
        for ($i = 0; ($ModelsRST = sql_row($ModelsResult, $i)); $i++) 
        {
            // don't add the SIM or PCATD (they will be added last)
            if ($ModelsRST[$Modelmodel_offset] != $SimulatorAircraftType &&
                $ModelsRST[$Modelmodel_offset] != $PCATDAircraftType)
            {
                $NewAircraftType[$NumberNewAircraftType++] = $ModelsRST[$Modelmodel_offset];
            }
        }
        
        // add the simulation and PCATD types to the array
        $NewAircraftType[$NumberNewAircraftType++] = $SimulatorAircraftType;
        $NewAircraftType[$NumberNewAircraftType++] = $PCATDAircraftType;

        // get the model complexity from the database
        LoadAircraftTypeComplexity(
                                    $AircraftComplexity,
                                    $AircraftTypes,
                                    $NumberNewAircraftType,
                                    $NewAircraftType,
                                    $MaxComplexityFields);

        // display the existing models and complexity
        echo "<form name='ModelComplexity' action='AddModifyMakeModel.php' method='POST'>";
        echo "The complexity is used to determine which aircraft type flight tests will apply " . 
                "to another aircraft type. A complexity of 1 will clear a complexity of 2, " .
                "a complexity of 2 will clear a complexity 3 but not complexity 1, etc. " .
                "A complexity of 0 means that aircraft flight test will clear only that aircraft type.";
        
        // display the models and complexitities
        echo "<table border=0>";
        for ($i = 0; $i < count($AircraftComplexity); $i++)
        {
            if (Len($AircraftTypes[$i]) > 0)
            {
                echo "<tr>";
    
                // remove any quotes from the aircraft type for the displayed value
                $AircraftTypeString = RemoveCurrencyFieldQuotes($AircraftTypes[$i]);
                
                // if this is the simulator or a PCATD type, set the complexity
                if ($AircraftTypeString == $SimulatorAircraftType ||
                   $AircraftTypeString == $PCATDAircraftType)
                {
                    // set the simulator type (don't let them change complexity)
                    echo "<td width=$Column1Width>$AircraftTypeString</td>";
                    echo "<input name='AircraftTypeLabel$i' type='hidden' value='$AircraftTypeString'>";
                    echo "<td width=$Column2Width>-1</td>";
                    echo "<input name='AircraftComplexity$i' type='hidden' value=-1>";
                }
                else
                {
                    // put the aircraft type and complexity in the screen
                    echo "<td width=$Column1Width>$AircraftTypeString</td>";
                    echo "<input name='AircraftTypeLabel$i' type='hidden' value='$AircraftTypeString'>";
                    echo "<td align=left width=$Column2Width>" . 
                            "<input " .
                                "type=text " .
                                "name='AircraftComplexity$i' " . 
                                "id='AircraftComplexity$i' " .
                                "align=right " . 
                                "size=$ControlNameBoxSize " . 
                                "value='" . $AircraftComplexity[$i] . "'>" . 
                            "</td>";
                }
                echo "</tr>";
            }
        }
        
        // add the submit button
        echo "<tr>";
        echo "<td colspan=2><center><input type='submit' name='UpdateComplexity' value='Update'></center></td>";
        echo "</tr>";

        echo "</table>";
        echo "</form>";
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelMaintenance))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }    

    // this script will call itself whenever the submit or delete button is pressed
    // we will check here for the update and delete request before generating
    // the main screen information
    // if we are adding a make
    if($add_make) 
    {
    	// if the make is already in the database, don't add it
    	if(0 == sql_query1("SELECT COUNT(*) FROM AircraftScheduling_make WHERE make = '$add_make'"))
    	{
            $sql = "INSERT INTO AircraftScheduling_make (make, hourly_cost, rental_fee) " . 
                    "VALUES ('" . addslashes($add_make) . "', 0, 0)";
            if(-1 == sql_command($sql))
            {                
                echo "<h2>Unable to add make: $add_make</h2>\n$sql<br>" . sql_error() . "</h2>";
            }
            else
            {            	
            	// log the change in to the journal
            	$Description = 
            				"Added make $add_make to the database.";
            	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
            }
    	}
    	else
    	{
    		echo "<h2>Unable to add make. It already exists in the database.</h2>";
    	}
    }
    
    // if we are adding a model
    if($add_model) 
    {
    	// if the model is already in the database, don't add it
    	if(0 == sql_query1("SELECT COUNT(*) FROM AircraftScheduling_model WHERE model = '$add_model'"))
    	{
    	    // create the new model
    	    CreateNewModel($add_model);
    	}
    	else
    	{
    		echo "<h2>Unable to add model. It already exists in the database.</h2>";
    	}
    }
    
    // if we are deleting a make
    if($makes && $delete) 
    {
    	// if the make is still in use, don't delete it
    	if(0 == sql_query1("SELECT COUNT(*) FROM AircraftScheduling_aircraft WHERE make_id = $makes"))
    	{
        	// if the model is the special Simulator make, don't delete it
            if ($makes == LookupMakeID("Simulator"))
            {
        		echo "<h2>Unable to delete make. You cannot delete the Simulator make.</h2>";
            }
            else
            {
                $MakeName = LookupAircraftMake($makes);
        	    $sql = "DELETE FROM AircraftScheduling_make WHERE make_id = $makes";
                if(-1 == sql_command($sql))
                {
                	echo "<h2>Unable to delete make id = $makes</h2>\n$sql<br>" . sql_error() . "</h2>";
                }
                else
                {            	
                	// log the change in to the journal
                	$Description = 
                				"Deleted make $MakeName from the database.";
                	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
                }
            }
    	}
    	else
    	{
    		echo "<h2>Unable to delete make. It is still in use by an aircraft. Try deleting the aircraft first.</h2>";
    	}
    }
    
    // if we are deleting a model
    if($models && $delete) 
    {
    	// if the model is still in use, don't delete it
    	if(0 == sql_query1("SELECT COUNT(*) FROM AircraftScheduling_aircraft WHERE model_id = $models"))
    	{
        	// if the model is the special sim or pcatd, don't delete it
            if ($models == LookupModelID($SimulatorAircraftType) ||
                $models == LookupModelID($PCATDAircraftType))
            {
        		echo "<h2>Unable to delete model. You cannot delete the SIM or PCATD models.</h2>";
            }
            else
            {
                $ModelName = LookupAircraftType($models);
                DeleteAircraftType($ModelName);
                
            	// log the change in to the journal
            	$Description = 
            				"Deleted model $ModelName from the database.";
            	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
            }
    	}
    	else
    	{
    		echo "<h2>Unable to delete model. It is still in use by an aircraft. Try deleting the aircraft first.</h2>";
    	}
    }
    
    // if we are updating the model complexity
    if($UpdateComplexity) 
    {
        // update the complexity information
        WriteCurrencyRules($AircraftComplexity, $AircraftTypeLabel);

    	// log the change in to the journal
    	$Description = 
    				"Updated the model complexity in the database.";
    	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    }
    
    // start the form
    print_header($day, $month, $year, isset($resource) ? $resource : "", isset($resource_id) ? $resource_id : "", $makemodel, "");
    
    echo "<h2>Maintain Aircraft Make and Model</h2>";
    
    echo "<center>";
    echo "<table border=1 width='800'>";
    echo "<tr>";
    echo "<td><center><b>Aircraft Makes</b></center></td>";
    echo "<td><center><b>Aircraft Models</b></center></td>";
	if ($AllowAircraftCheckout)
	{
        echo "<td><center><b>Aircraft Model Complexity</b></center></td>";
    }
    echo "</tr>";
    
    echo "<tr>";
    
    // display make modification form
    echo "<td>";
    DisplayMakeForm();
    echo "</td>";
    
    // display model modification form
    echo "<td>";
    DisplayModelForm();
    echo "</td>";
    
    // display model complexity form if we are allowing aircraft checkout
	if ($AllowAircraftCheckout)
	{
        echo "<td>";
        DisplayModelComplexityForm();
        echo "</td>";
    }
    
    echo "</tr>";
    echo "</table>";
    echo "</center>";
    
    echo "<BR>";
    
    // give them a link back to the admin page
    echo "<A HREF='admin.php'>Return to administrator page</A>";
    
    echo "<br>";
    
    include "trailer.inc" 
    
?>
