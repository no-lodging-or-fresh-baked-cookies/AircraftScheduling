<?php
//-----------------------------------------------------------------------------
// 
// AddModifyMember.php
// 
// PURPOSE: Displays the add or modify member screen.
// 
// PARAMETERS:
//      day - currently selected day for the date selector
//      month - currently selected month for the date selector
//      year - currently selected year for the date selector
//      make - aircraft make selected by the user
//      model - aircraft model selected by the user
//      resource - resource selected by the user
//      resource_id - selected resource ID to pass to selected URLs
//      update - set to Update to update the given member
//      delete - set to Delete to delete the given member
//      AddModify - set to modify to modify a member or add to add a member
//      username - username of the member
//      pview - set true to build a screen suitable for printing
//      goback - URL to return to previous page
//      GoBackParameters - parameters to return to previous page
//
//      Database information is passed in in numerious controls.
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
    require_once("CurrencyFunctions.inc");
    require_once("DatabaseFunctions.inc");
    require_once("DatabaseConstants.inc");

    // initialize variables
    $all = '';
    $AddModify = "Add";
    $Medical_Class = -1;
    $ErrorMessage = "";
    $InstructorOfRecord = '';
	$AddModifyMember = '';
	$SaveCheckCurrency = '';
	$MemberCancel = '';
	$MemberDelete = '';
	$order_by = '';
   
    // get the input parameters if they have been passed in
    $rdata = array_merge($GLOBALS['_GET'], $GLOBALS['_POST']);
    if(isset($rdata["day"])) $day = $rdata["day"];
    if(isset($rdata["month"])) $month = $rdata["month"];
    if(isset($rdata["year"])) $year = $rdata["year"];
    if(isset($rdata["make"])) $make = $rdata["make"];
    if(isset($rdata["model"])) $model = $rdata["model"];
    if(isset($rdata["resource"])) $resource = $rdata["resource"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    if(isset($rdata["AddModifyMember"])) $AddModifyMember = $rdata["AddModifyMember"];
    if(isset($rdata["SaveCheckCurrency"])) $SaveCheckCurrency = $rdata["SaveCheckCurrency"];
    if(isset($rdata["AddModify"])) $AddModify = $rdata["AddModify"];
    if(isset($rdata["MemberCancel"])) $MemberCancel = $rdata["MemberCancel"];
    if(isset($rdata["MemberDelete"])) $MemberDelete = $rdata["MemberDelete"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    
    // user database table fields
    if(isset($rdata["person_id"])) $person_id = $rdata["person_id"];
    if(isset($rdata["first_name"])) $first_name = $rdata["first_name"];
    if(isset($rdata["middle_name"])) $middle_name = $rdata["middle_name"];
    if(isset($rdata["last_name"])) $last_name = $rdata["last_name"];
    if(isset($rdata["title"])) $title = $rdata["title"];
    if(isset($rdata["email"])) $email = $rdata["email"];
    if(isset($rdata["username"])) $username = $rdata["username"];
    if(isset($rdata["password"])) $password = $rdata["password"];
    if(isset($rdata["user_level"])) $user_level = $rdata["user_level"];
    if(isset($rdata["login_counter"])) $login_counter = $rdata["login_counter"];
    if(isset($rdata["last_login"])) $last_login = $rdata["last_login"];
    if(isset($rdata["address1"])) $address1 = $rdata["address1"];
    if(isset($rdata["address2"])) $address2 = $rdata["address2"];
    if(isset($rdata["city"])) $city = $rdata["city"];
    if(isset($rdata["state"])) $state = $rdata["state"];
    if(isset($rdata["zip"])) $zip = $rdata["zip"];
    if(isset($rdata["phone_number"])) $phone_number = $rdata["phone_number"];
    if(isset($rdata["SSN"])) $SSN = $rdata["SSN"];
    if(isset($rdata["Organization"])) $Organization = $rdata["Organization"];
    if(isset($rdata["Work_Ext"])) $Work_Ext = $rdata["Work_Ext"];
    if(isset($rdata["Home_Phone"])) $Home_Phone = $rdata["Home_Phone"];
    if(isset($rdata["InstructorOfRecord"])) $InstructorOfRecord = $rdata["InstructorOfRecord"];
    if(isset($rdata["Dues_Amount"])) $Dues_Amount = $rdata["Dues_Amount"];
    if(isset($rdata["Member_Ground_Instruction_Amount"])) $Member_Ground_Instruction_Amount = $rdata["Member_Ground_Instruction_Amount"];
    if(isset($rdata["Member_Private_Instruction_Amount"])) $Member_Private_Instruction_Amount = $rdata["Member_Private_Instruction_Amount"];
    if(isset($rdata["Member_Instrument_Instruction_Amount"])) $Member_Instrument_Instruction_Amount = $rdata["Member_Instrument_Instruction_Amount"];
    if(isset($rdata["Member_Commercial_Instruction_Amount"])) $Member_Commercial_Instruction_Amount = $rdata["Member_Commercial_Instruction_Amount"];
    if(isset($rdata["Member_CFI_Instruction_Amount"])) $Member_CFI_Instruction_Amount = $rdata["Member_CFI_Instruction_Amount"];
    if(isset($rdata["Member_CFII_Instruction_Amount"])) $Member_CFII_Instruction_Amount = $rdata["Member_CFII_Instruction_Amount"];
    if(isset($rdata["Contract_Number"])) $Contract_Number = $rdata["Contract_Number"];
    if(isset($rdata["Notify_First_Name"])) $Notify_First_Name = $rdata["Notify_First_Name"];
    if(isset($rdata["Notify_Middle_Initial"])) $Notify_Middle_Initial = $rdata["Notify_Middle_Initial"];
    if(isset($rdata["Notify_Last_Name"])) $Notify_Last_Name = $rdata["Notify_Last_Name"];
    if(isset($rdata["Notify_Relation"])) $Notify_Relation = $rdata["Notify_Relation"];
    if(isset($rdata["Notify_Address"])) $Notify_Address = $rdata["Notify_Address"];
    if(isset($rdata["Notify_City"])) $Notify_City = $rdata["Notify_City"];
    if(isset($rdata["Notify_State"])) $Notify_State = $rdata["Notify_State"];
    if(isset($rdata["Notify_Zip"])) $Notify_Zip = $rdata["Notify_Zip"];
    if(isset($rdata["Notify_Phone1"])) $Notify_Phone1 = $rdata["Notify_Phone1"];
    if(isset($rdata["Notify_Phone2"])) $Notify_Phone2 = $rdata["Notify_Phone2"];
    if(isset($rdata["Contract_Expiration_Date"])) $Contract_Expiration_Date = $rdata["Contract_Expiration_Date"];
    if(isset($rdata["Rules_Field"])) $Rules_Field = $rdata["Rules_Field"];
    if(isset($rdata["Member_Notes"])) $Member_Notes = $rdata["Member_Notes"];
    if(isset($rdata["Credit_Card_Number"])) $Credit_Card_Number = $rdata["Credit_Card_Number"];
    if(isset($rdata["Credit_Card_Expiration"])) $Credit_Card_Expiration = $rdata["Credit_Card_Expiration"];
    if(isset($rdata["Manager_Message"])) $Manager_Message = $rdata["Manager_Message"];
    if(isset($rdata["Membership_Date"])) $Membership_Date = $rdata["Membership_Date"];
    if(isset($rdata["Resign_Date"])) $Resign_Date = $rdata["Resign_Date"];
    if(isset($rdata["Clearing_Authority"])) $Clearing_Authority = $rdata["Clearing_Authority"];
    if(isset($rdata["Password_Expires_Date"])) $Password_Expires_Date = $rdata["Password_Expires_Date"];
    if(isset($rdata["Allow_Phone_Number_Display"])) $Allow_Phone_Number_Display = $rdata["Allow_Phone_Number_Display"];
    if(isset($rdata["OldUsername"])) $OldUsername = $rdata["OldUsername"];

    // currency database fields
    if(isset($rdata["Rating"])) $Rating = $rdata["Rating"];            
    if(isset($rdata["Member_Status"])) $Member_Status = $rdata["Member_Status"];            
    if(isset($rdata["Medical_Class"])) $Medical_Class = $rdata["Medical_Class"];            
    if(isset($rdata["Medical_Date"])) $Medical_Date = $rdata["Medical_Date"]; 
               
    if(isset($rdata["OldFirstName"])) $OldFirstName = $rdata["OldFirstName"];            
    if(isset($rdata["OldLastName"])) $OldLastName = $rdata["OldLastName"]; 
    
    // filter parameters (from display inventory screen)
    if(isset($rdata["order_by"])) $order_by = $rdata["order_by"];

    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################

    //********************************************************************
    // BuildMedicalSelector(MedicalType, MedicalDate)
    //
    // Purpose: Display a selector for the medical types.
    //
    // Inputs:
    //   MedicalType - current medical type 
    //   MedicalDate - date of last medical
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildMedicalSelector($MedicalType, $MedicalDate)
    {
        global $FirstClassMedical;
        global $SecondClassMedical;
        global $ThirdClassMedical;
        global $SpecialClassMedical;

        // build the select HTML	
		echo "<SELECT NAME='Medical_Class' id='Medical_Class' " . 
		        "onChange='document.forms[\"main\"].submit()'>";
		
		// build the selection entries
		echo "<OPTION " .
				"VALUE='" . 0 . "'" . 
				(0 == $MedicalType ? " SELECTED" : "") . 
				">" . $SpecialClassMedical;
		echo "<OPTION " .
				"VALUE='" . 1 . "'" . 
				(1 == $MedicalType ? " SELECTED" : "") . 
				">" . $FirstClassMedical;
		echo "<OPTION " .
				"VALUE='" . 2 . "'" . 
				(2 == $MedicalType ? " SELECTED" : "") . 
				">" . $SecondClassMedical;
		echo "<OPTION " .
				"VALUE='" . 3 . "'" . 
				(3 == $MedicalType ? " SELECTED" : "") . 
				">" . $ThirdClassMedical;
        
        // end the select entry
 		echo "</SELECT>";	
    
        // put the pilot medical date information on the screen
        echo " ";

        // if it is a special medical class, set the date field as an
        // expiration date
        if (0 == $MedicalType)
        {
            // special medical class
            echo "Date Medical Expires ";
        }
        else
        {
            // regular medical class
            echo "Date Medical Issued ";
        }
        echo  "<input " .
                    "type=text " .
                    "NAME='Medical_Date' " . 
                    "ID='Medical_Date' " .
                    "align=left " . 
                    "SIZE=10 " . 
                    "VALUE='" . FormatField($MedicalDate, "Date") . "' " . 
                    ">";
    }
    
    //********************************************************************
    // SetVariable($DatabaseOffset, &$ScreenVariable)
    //
    // Purpose: If a screen variable does not already have a value, copy
    //          the database variable to the screen variable
    //
    // Inputs:
    //   DatabaseOffset - offset into the database for the screen variable
    //                    value
    //   ScreenVariable - the screen variable to check and set if needed
    //
    // Outputs:
    //   ScreenVariable - the updated screen variable
    //
    // Returns:
    //   none
    //*********************************************************************
    function SetVariable($DatabaseOffset, &$ScreenVariable)
    {
        global $MemberRST;

        // is the screen variable empty?
        if (empty($ScreenVariable))
        {
            // yes, copy the database value if it exists
			if (array_key_exists($DatabaseOffset, $MemberRST ))
			{
				// key exists copy it
				$ScreenVariable = $MemberRST[$DatabaseOffset];
			}
			else
			{
				// key doesn't exists set to default
				$ScreenVariable = "";
			}
        }
    }
    
    //********************************************************************
    // SetScreenVariables()
    //
    // Purpose: Since this screen may be reloaded when the currency 
    //          values are changed, copy the database variables to the 
    //          screen variables.
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
    function SetScreenVariables()
    {
        global $NullDate;
        global $MemberStatusActive, $MemberStatusInActive, $MemberStatusAircraft, $MemberStatusResigned;
        
        include "DatabaseConstants.inc";

        global $person_id;
        global $first_name;
        global $middle_name;
        global $last_name;
        global $title;
        global $email;
        global $username;
        global $password;
        global $user_level;
        global $login_counter;
        global $last_login;
        global $address1;
        global $address2;
        global $city;
        global $state;
        global $zip;
        global $phone_number;
        global $SSN;
        global $Organization;
        global $Work_Ext;
        global $Home_Phone;
        global $Dues_Amount;
        global $Member_Ground_Instruction_Amount;
        global $Member_Private_Instruction_Amount;
        global $Member_Instrument_Instruction_Amount;
        global $Member_Commercial_Instruction_Amount;
        global $Member_CFI_Instruction_Amount;
        global $Member_CFII_Instruction_Amount;
        global $Contract_Number;
        global $Notify_First_Name;
        global $Notify_Middle_Initial;
        global $Notify_Last_Name;
        global $Notify_Relation;
        global $Notify_Address;
        global $Notify_City;
        global $Notify_State;
        global $Notify_Zip;
        global $Notify_Phone1;
        global $Notify_Phone2;
        global $Contract_Expiration_Date;
        global $Rules_Field;
        global $Member_Notes;
        global $Credit_Card_Number;
        global $Credit_Card_Expiration;
        global $Manager_Message;
        global $Membership_Date;
        global $Resign_Date;
        global $Clearing_Authority;
        global $Password_Expires_Date;
        global $Allow_Phone_Number_Display;
        global $OldUsername;
        global $InstructorOfRecord;
        
        // special currency values
        global $Rating;            
        global $Member_Status;            
        global $Medical_Class;      
        global $Medical_Date;      
        
        // set the screen variables if they are not already set
        SetVariable($person_id_offset, $person_id);
        SetVariable($first_name_offset, $first_name);
        SetVariable($middle_name_offset, $middle_name);
        SetVariable($last_name_offset, $last_name);
        SetVariable($title_offset, $title);
        SetVariable($email_offset, $email);
        SetVariable($username_offset, $username);
        SetVariable($password_offset, $password);
        SetVariable($user_level_offset, $user_level);
        SetVariable($counter_offset, $login_counter);
        SetVariable($last_login_offset, $last_login);
        SetVariable($address1_offset, $address1);
        SetVariable($address2_offset, $address2);
        SetVariable($city_offset, $city);
        SetVariable($state_offset, $state);
        SetVariable($zip_offset, $zip);
        SetVariable($phone_number_offset, $phone_number);
        SetVariable($SSN_offset, $SSN);
        SetVariable($Organization_offset, $Organization);
        SetVariable($Work_Ext_offset, $Work_Ext);
        SetVariable($Home_Phone_offset, $Home_Phone);
        SetVariable($Dues_Amount_offset, $Dues_Amount);
        SetVariable($Ground_Instruction_Amount_offset, $Member_Ground_Instruction_Amount);
        SetVariable($Private_Instruction_Amount_Offset, $Member_Private_Instruction_Amount);
        SetVariable($Instrument_Instruction_Amount_offset, $Member_Instrument_Instruction_Amount);
        SetVariable($Commercial_Instruction_Amount_offset, $Member_Commercial_Instruction_Amount);
        SetVariable($CFI_Instruction_Amount_offset, $Member_CFI_Instruction_Amount);
        SetVariable($CFII_Instruction_Amount_offset, $Member_CFII_Instruction_Amount);
        SetVariable($Contract_Number_offset, $Contract_Number);
        SetVariable($Notify_First_Name_offset, $Notify_First_Name);
        SetVariable($Notify_Middle_Initial_offset, $Notify_Middle_Initial);
        SetVariable($Notify_Last_Name_offset, $Notify_Last_Name);
        SetVariable($Notify_Relation_offset, $Notify_Relation);
        SetVariable($Notify_Address_offset, $Notify_Address);
        SetVariable($Notify_City_offset, $Notify_City);
        SetVariable($Notify_State_offset, $Notify_State);
        SetVariable($Notify_Zip_offset, $Notify_Zip);
        SetVariable($Notify_Phone1_offset, $Notify_Phone1);
        SetVariable($Notify_Phone2_offset, $Notify_Phone2);
        SetVariable($Contract_Expiration_Date_offset, $Contract_Expiration_Date);
        SetVariable($Rules_Field_offset, $Rules_Field);
        SetVariable($Member_Notes_offset, $Member_Notes);
        SetVariable($Credit_Card_Number_offset, $Credit_Card_Number);
        SetVariable($Credit_Card_Expiration_offset, $Credit_Card_Expiration);
        SetVariable($Manager_Message_offset, $Manager_Message);
        SetVariable($Membership_Date_offset, $Membership_Date);
        SetVariable($Resign_Date_offset, $Resign_Date);
        SetVariable($Clearing_Authority_offset, $Clearing_Authority);
        SetVariable($Password_Expires_Date_offset, $Password_Expires_Date);
        SetVariable($Allow_Phone_Number_Display_offset, $Allow_Phone_Number_Display);
        SetVariable($OldUsername_offset, $OldUsername);
        SetVariable($InstructorOfRecord_offset, $InstructorOfRecord);
        
        // get the currency information
        LoadDBCurrencyFields("", $Rules_Field);
        
        // special currency values
        if (empty($Rating)) $Rating = LookupCurrencyFieldname("Rating");
        if (empty($Member_Status)) $Member_Status = LookupCurrencyFieldname("Member_Status");
        if ($Medical_Class == -1) $Medical_Class = LookupCurrencyFieldname("Medical_Class");
        if (empty($Medical_Date)) $Medical_Date = LookupCurrencyFieldname("Medical_Date");
        
        // make sure that the special currency values have valid information
        if ($Rating == $NullDate) $Rating = "Student";
        if ($Member_Status == $NullDate) $Member_Status = $MemberStatusInActive;
        if ($Medical_Class == $NullDate) $Medical_Class = 3;
        if ($Medical_Date == $NullDate) $Medical_Date = $NullDate;
    }
    
    //********************************************************************
    // DisplayCurrencyInformation(PilotRating as String)
    //
    // Purpose: Display currency information on the form
    //
    // Inputs:
    //   PilotRating - rating (student, private, private under 200,
    //                 private over 200, instrument, CFI) of the pilot
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DisplayCurrencyInformation($PilotRating)
    {
        global $NoCurrencyPilot, $StudentPilot, $PrivatePilotUnder200;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
  
        include "DatabaseConstants.inc";
        
        // arrays to hold the currency values and formats
        $CurrencyRuleText = array();
        $CurrencyStatusText = array();

        // if the pilot type is other than NoCurrencyRequired, load the currency fields
        if ($PilotRating != $NoCurrencyPilot)
        {
            // changes " " to "_" in the pilot rating currency field name
            $locPilotRating = Replace($PilotRating, " ", "_");
            
            // open a recordset for the currency fields
        	$sql = 
                "SELECT * FROM CurrencyFields WHERE " .
                    $locPilotRating .
                    " = 1 ORDER BY Currency_Field_Name";
        	$res = sql_query($sql);
            
            // if we had an error no records are found for this pilot type, don't display
            // the currency fields
            if($res) 
            {
                // no errors opening recordset, process the currency
                // put the pilot currency information on the screen
                echo "<table border=0>";
                $SaveCurrencyFieldName = array();
                $SaveCurrencyFieldFormat = array();
                $ControlIndex = 0;
        		for($reccnt=0; $CurrencyFieldsRST = sql_row($res, $reccnt); $reccnt++) 
        		{
                    // If this rule is an aircraft type rating rule there should be a matching
                    // aircraft in the database. We assume that the first part of the subrule
                    // (up to the "_") is the aircraft type
                    $AircraftCurrencyFieldName = $CurrencyFieldsRST[$Currency_Field_Name_offset];
                    $AircraftType = GetNextToken($AircraftCurrencyFieldName, "_");
                    
                    // if this is a valid aircraft type and it is a rental aircraft type
                    // put the field on the screen (non-rental aircraft don't have currency rules)
                    if (IsAircraftType($AircraftType))
                    {
                        // an aircraft type, is it a rental aircraft type
                        if (IsRentalAircraftType($AircraftType))
                        {
                            $ShowField = True;
                        }
                        else
                        {
                            // non-rental (private aircraft) don't show flight test
                            // or written test fields
                            $ShowField = False;
                        }
                    }
                    else
                    {
                        // not an aircraft type, display the field
                        $ShowField = True;
                    }
            
                    // should this field be displayed?
                    if ($ShowField)
                    {
                        // we have a currency field, save the field name and format for later use
                        $SaveCurrencyFieldName[$ControlIndex] = $CurrencyFieldsRST[$Currency_Field_Name_offset];
                        $SaveCurrencyFieldFormat[$ControlIndex] = $CurrencyFieldsRST[$Currency_Field_Type_offset];
                        
                        // don't display in the form any combo box or special entries
                        if ($SaveCurrencyFieldName[$ControlIndex] != "Rating" &&
                            $SaveCurrencyFieldName[$ControlIndex] != "Member_Status" &&
                            $SaveCurrencyFieldName[$ControlIndex] != "Medical_Class" &&
                            $SaveCurrencyFieldName[$ControlIndex] != "Medical_Date")
                        {
                            // format the caption string by removing the "_" seperator
                            $CaptionString = Replace($SaveCurrencyFieldName[$ControlIndex], "_", " ");
                            
                            // set the field text and value in the screen
                            $CurrencyRuleText[$ControlIndex] = RemoveCurrencyFieldQuotes($CaptionString);
                            $CurrencyStatusText[$ControlIndex] = 
                                        FormatField(
                                                    LookupCurrencyFieldname($SaveCurrencyFieldName[$ControlIndex]), 
                                                    $SaveCurrencyFieldFormat[$ControlIndex]);
                            
                            // update the counters
                            $ControlIndex = $ControlIndex + 1;
                        }
                    }
                }
                
                // display the currency fields
                DisplayColumns(
                                count($CurrencyRuleText), 
                                3, 
                                $CurrencyRuleText, 
                                $CurrencyStatusText, 
                                true,
                                $SaveCurrencyFieldFormat); 
                echo "</table>";
            }
        	else 
            {
                // error processing database request, tell the user
                DisplayDatabaseError("DisplayCurrencyInformation", $sql);
            }
        }
    }
        
    //********************************************************************
    // DisplayMemberInformation()
    //
    // Purpose:  Display the member's information fields for the user to
    //           enter the user information.
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
    function DisplayMemberInformation()
    {    	
        global $AllowAircraftCheckout;

        global $MemberRST;
        global $first_name;
        global $middle_name;
        global $last_name;
        global $address1;
        global $address2;
        global $city;
        global $state;
        global $zip;
        global $Organization;
        global $SSN;
        global $phone_number;
        global $Home_Phone;
        global $InstructorOfRecord;

        // set the column sizes
        $Column1Width = "40%";
        $Column2Width = "20%";
        $Column3Width = "40%";
         
        // set the table name  	
        $TableName = "MemberInformationTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>User Information</td>";
        echo "</tr>";
        
        // user's name
        echo "<tr>";
        echo "<td width=$Column1Width align=left>First Name</td>";
        echo "<td width=$Column2Width align=left>MI</td>";
        echo "<td width=$Column3Width align=left>Last Name</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td width=$Column1Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='first_name' " . 
                    "ID='first_name' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($first_name) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td width=$Column2Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='middle_name' " . 
                    "ID='middle_name' " .
                    "align=left " . 
                    "SIZE=3 " . 
                    "VALUE=\"" . stripslashes($middle_name) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td width=$Column3Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='last_name' " . 
                    "ID='last_name' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($last_name) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
                
        // user's address
        echo "<tr>";
        echo "<td colspan=3>Address</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=3>" . 
                "<input " .
                    "type=text " .
                    "NAME='address1' " . 
                    "ID='address1' " .
                    "align=left " . 
                    "SIZE=45 " . 
                    "VALUE=\"" . stripslashes($address1) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // user's city, state, zip
        echo "<tr>";
        echo "<td width=$Column1Width>City</td>";
        echo "<td width=$Column2Width>State</td>";
        echo "<td width=$Column3Width>Zip Code</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left width=$Column1Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='city' " . 
                    "ID='city' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($city) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column2Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='state' " . 
                    "ID='state' " .
                    "align=left " . 
                    "SIZE=3 " . 
                    "VALUE=\"" . stripslashes($state) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column3Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='zip' " . 
                    "ID='zip' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($zip) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // organization and SSN
        echo "<tr>";
        echo "<td align=left colspan=2>Organization</td>";
        echo "<td align=left>SSN</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=2>" . 
                "<input " .
                    "type=text " .
                    "NAME='Organization' " . 
                    "ID='Organization' " .
                    "align=left " . 
                    "SIZE=24 " . 
                    "VALUE=\"" . stripslashes($Organization) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='SSN' " . 
                    "ID='SSN' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($SSN) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // phone numbers
        echo "<tr>";
        echo "<td colspan=2>Phone 1</td>";
        echo "<td  width=$Column3Width>Phone 2</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=2>" . 
                "<input " .
                    "type=text " .
                    "NAME='phone_number' " . 
                    "ID='phone_number' " .
                    "align=left " . 
                    "SIZE=24 " . 
                    "VALUE=\"" . stripslashes($phone_number) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column3Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Home_Phone' " . 
                    "ID='Home_Phone' " .
                    "align=left " . 
                    "SIZE=14 " . 
                    "VALUE=\"" . stripslashes($Home_Phone) . "\" " . 
                    ">" . 
                "</td>";
        
        // instructor assigned
        echo "<tr>";
        echo "<td align=left colspan=3>"; 
        echo "Instructor: ";
        BuildInstructorSelector(
                            GetNameFromUsername($InstructorOfRecord), 
                            true, 
                            "InstructorOfRecord",
                            20,
                            true,
                            false,
                            "",
                            true);
        echo "</td>";
    
        // finished with the table
        echo "</table>";
    }
        
    //********************************************************************
    // DisplaySchedulingInformation()
    //
    // Purpose:  Display the member's scheduling information fields.
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
    function DisplaySchedulingInformation()
    {    	
        global $AllowAircraftCheckout;

    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	global $UserLevelDisabledName, $UserLevelNormalName, $UserLevelSuperName, $UserLevelOfficeName; 
    	global $UserLevelMaintenanceName, $UserLevelAdminName;

        global $MemberRST;
        global $Allow_Phone_Number_Display;
        global $password;
        global $user_level;
        global $Member_Notes;
        global $email;

        // set the column sizes
        $Column1Width = "50%";
        $Column2Width = "50%";
         
        // set the table name  	
        $TableName = "MemberSchedulingTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='2'>Scheduling Information</td>";
        echo "</tr>";
        
        // password
        echo "<tr>";
        echo "<td width=$Column1Width align=left>Password</td>";
        echo "<td width=$Column2Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='password' " . 
                    "ID='password' " .
                    "align=left " . 
                    "SIZE=20 " . 
                    "VALUE=\"" . $password . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // email
        echo "<tr>";
        echo "<td width=$Column1Width align=left>Email</td>";
        echo "<td width=$Column2Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='email' " . 
                    "ID='email' " .
                    "align=left " . 
                    "SIZE=20 " . 
                    "VALUE=\"" . $email . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // user level
    	$UserLevelTypes[$UserLevelDisabled] = $UserLevelDisabledName;
    	$UserLevelTypes[$UserLevelNormal] = $UserLevelNormalName;
    	$UserLevelTypes[$UserLevelSuper] = $UserLevelSuperName;
    	$UserLevelTypes[$UserLevelOffice] = $UserLevelOfficeName;
    	$UserLevelTypes[$UserLevelMaintenance] = $UserLevelMaintenanceName;
    	$UserLevelTypes[$UserLevelAdmin] = $UserLevelAdminName;
        echo "<tr>";
        echo "<td width=$Column1Width align=left>User Level</td>";
		echo "<td width=$Column2Width align=left>";
		echo "<SELECT NAME='user_level'>";
		for($i = 0; $i <= $UserLevelAdmin; $i++)
			echo "<OPTION VALUE=" . $i . ($i == $user_level ? " SELECTED" : "") . ">$UserLevelTypes[$i]";
		echo "</SELECT>";
		echo "</td>";
        echo "</tr>";
    
        // display phone number choice
    	echo "<tr>";
    	echo "<td align=center colspan=2>";
    	echo "<input type=checkbox name=Allow_Phone_Number_Display value=1 ";
    	if ($Allow_Phone_Number_Display == 1) echo "CHECKED";
    	echo ">List Phone Numbers Online";
    	echo "</td>";
    	echo "</tr>";
    
        // finished with the table
        echo "</table>";
    }
        
    //********************************************************************
    // DisplayMemberNotes()
    //
    // Purpose:  Display the member's note information
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
    function DisplayMemberNotes()
    {    	
        global $AllowAircraftCheckout;

        global $MemberRST;
        global $Member_Notes;

        // set the column sizes
        $Column1Width = "50%";
        $Column2Width = "50%";
         
        // set the table name  	
        $TableName = "MemberNotesTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='2'>User Notes</td>";
        echo "</tr>";
        
        // user notes
        echo "<tr>";
        echo "<td class=tl>";
        echo "<textarea name='Member_Notes' rows=6 cols=34 wrap='virtual'>";
        echo htmlentities($Member_Notes); 
        echo "</textarea>";
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
    }
        
    //********************************************************************
    // DisplayNotifyInformation()
    //
    // Purpose:  Display the member's notify information fields.
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
    function DisplayNotifyInformation()
    {    	
        global $AllowAircraftCheckout;

        global $MemberRST;
        global $Notify_First_Name;
        global $Notify_Middle_Initial;
        global $Notify_Last_Name;
        global $Notify_Relation;
        global $Notify_Address;
        global $Notify_City;
        global $Notify_State;
        global $Notify_Zip;
        global $Notify_Phone1;
        global $Notify_Phone2;

        // set the column sizes
        $Column1Width = "40%";
        $Column2Width = "20%";
        $Column3Width = "40%";
         
        // set the table name  	
        $TableName = "MemberInformationTable";        
    
        // start the table
        echo "<TABLE ID='$TableName' border=1>";        
        
        // title
        echo "<tr>";
        echo " <th colspan='3'>Notify Information</td>";
        echo "</tr>";
        
        // user's name
        echo "<tr>";
        echo "<td width=$Column1Width align=left>First Name</td>";
        echo "<td width=$Column2Width align=left>MI</td>";
        echo "<td width=$Column3Width align=left>Last Name</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td width=$Column1Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_First_Name' " . 
                    "ID='Notify_First_Name' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($Notify_First_Name) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td width=$Column2Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_Middle_Initial' " . 
                    "ID='Notify_Middle_Initial' " .
                    "align=left " . 
                    "SIZE=3 " . 
                    "VALUE=\"" . stripslashes($Notify_Middle_Initial) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td width=$Column3Width align=left>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_Last_Name' " . 
                    "ID='Notify_Last_Name' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($Notify_Last_Name) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
                
        // user's address
        echo "<tr>";
        echo "<td colspan=3>Address</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=3>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_Address' " . 
                    "ID='Notify_Address' " .
                    "align=left " . 
                    "SIZE=45 " . 
                    "VALUE=\"" . stripslashes($Notify_Address) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // user's city, state, zip
        echo "<tr>";
        echo "<td width=$Column1Width>City</td>";
        echo "<td width=$Column2Width>State</td>";
        echo "<td width=$Column3Width>Zip Code</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left width=$Column1Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_City' " . 
                    "ID='Notify_City' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($Notify_City) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column2Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_State' " . 
                    "ID='Notify_State' " .
                    "align=left " . 
                    "SIZE=3 " . 
                    "VALUE=\"" . stripslashes($Notify_State) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column3Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_Zip' " . 
                    "ID='Notify_Zip' " .
                    "align=left " . 
                    "SIZE=16 " . 
                    "VALUE=\"" . stripslashes($Notify_Zip) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // relationship
        echo "<tr>";
        echo "<td align=left colspan=3>Relationship</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=3>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_Relation' " . 
                    "ID='Notify_Relation' " .
                    "align=left " . 
                    "SIZE=24 " . 
                    "VALUE=\"" . stripslashes($Notify_Relation) . "\" " . 
                    ">" . 
                "</td>";
        echo "</tr>";
        
        // phone numbers
        echo "<tr>";
        echo "<td colspan=2>Phone 1</td>";
        echo "<td  width=$Column3Width>Phone 2</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=left colspan=2>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_Phone1' " . 
                    "ID='Notify_Phone1' " .
                    "align=left " . 
                    "SIZE=24 " . 
                    "VALUE=\"" . stripslashes($Notify_Phone1) . "\" " . 
                    ">" . 
                "</td>";
        echo "<td align=left width=$Column3Width>" . 
                "<input " .
                    "type=text " .
                    "NAME='Notify_Phone2' " . 
                    "ID='Notify_Phone2' " .
                    "align=left " . 
                    "SIZE=14 " . 
                    "VALUE=\"" . stripslashes($Notify_Phone2) . "\" " . 
                    ">" . 
                "</td>";
    
        // finished with the table
        echo "</table>";
    }
        
    //********************************************************************
    // DisplayContractDues()
    //
    // Purpose:  Display the member's contract and dues information fields.
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
    function DisplayContractDues()
    {    	
        global $AllowAircraftCheckout;

        global $MemberRST;
        global $Contract_Number;
        global $Contract_Expiration_Date;
        global $Clearing_Authority;
        global $Dues_Amount;

    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // set the column sizes
            $Column1Width = "22%";
            $Column2Width = "41%";
            $Column3Width = "12%";
            $Column4Width = "25%";
             
            // set the table name  	
            $TableName = "ContractDuesTable";        
        
            // start the table
            echo "<TABLE ID='$TableName' border=1>";        
            
            // title
            echo "<tr>";
            echo " <th colspan='4'>Contract/Dues Information</td>";
            echo "</tr>";
            
            // contract information
            echo "<tr>";
            echo "<td width=$Column1Width align=left>Contract</td>";
            echo "<td width=$Column2Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Contract_Number' " . 
                        "ID='Contract_Number' " .
                        "align=left " . 
                        "SIZE=16 " . 
                        "VALUE=\"" . stripslashes($Contract_Number) . "\" " . 
                        ">" . 
                    "</td>";
            echo "<td width=$Column3Width align=left>Exp</td>";
            echo "<td width=$Column4Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Contract_Expiration_Date' " . 
                        "ID='Contract_Expiration_Date' " .
                        "align=left " . 
                        "SIZE=9 " . 
                        "VALUE=\"" . FormatField($Contract_Expiration_Date, "Date") . "\" " . 
                        ">" . 
                    "</td>";
            echo "</tr>";
        
            // clearing authority and dues
        	echo "<tr>";
        	echo "<td align=center colspan=2>";
        	echo "<input type=checkbox name=Clearing_Authority value=1 ";
        	if ($Clearing_Authority == 1) echo "CHECKED";
        	echo ">Clearing Authority";
            echo "<td width=$Column3Width align=left>Dues</td>";
            echo "<td width=$Column4Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Dues_Amount' " . 
                        "ID='Dues_Amount' " .
                        "align=left " . 
                        "SIZE=9 " . 
                        "VALUE=\"" . FormatField($Dues_Amount, "Currency") . "\" " . 
                        ">" . 
                    "</td>";
        	echo "</td>";
        	echo "</tr>";
        
            // finished with the table
            echo "</table>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            // contract information
            echo "<INPUT NAME='Contract_Number' TYPE='HIDDEN' value=\"$Contract_Number\">\n";
            echo "<INPUT NAME='Contract_Expiration_Date' TYPE='HIDDEN' value=\"$Contract_Expiration_Date\">\n";
        
            // clearing authority and dues
            echo "<INPUT NAME='Clearing_Authority' TYPE='HIDDEN' value=\"$Clearing_Authority\">\n";
            echo "<INPUT NAME='Dues_Amount' TYPE='HIDDEN' value=\"$Dues_Amount\">\n";
        }
    }
        
    //********************************************************************
    // DisplayCreditCard()
    //
    // Purpose:  Display the member's credit card information fields.
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
    function DisplayCreditCard()
    {    	
        global $AllowAircraftCheckout;

        global $MemberRST;
        global $Credit_Card_Number;
        global $Credit_Card_Expiration;

    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // set the column sizes
            $Column1Width = "40%";
            $Column2Width = "60%";
             
            // set the table name  	
            $TableName = "CreditCardTable";        
        
            // start the table
            echo "<TABLE ID='$TableName' border=1>";        
            
            // title
            echo "<tr>";
            echo " <th colspan='2'>Credit Card</td>";
            echo "</tr>";
            
            // credit card expiration
            echo "<tr>";
            echo "<td width=$Column1Width align=left>Exp</td>";
            echo "<td width=$Column2Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Credit_Card_Expiration' " . 
                        "ID='Credit_Card_Expiration' " .
                        "align=left " . 
                        "SIZE=10 " . 
                        "VALUE=\"" . FormatField($Credit_Card_Expiration, "Date") . "\" " . 
                        ">" . 
                    "</td>";
            echo "</tr>";
            
            // credit card number
            echo "<tr>";
            echo "<td width=$Column1Width align=left>Number</td>";
            echo "<td width=$Column2Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Credit_Card_Number' " . 
                        "ID='Credit_Card_Number' " .
                        "align=left " . 
                        "SIZE=32 " . 
                        "VALUE=\"" . stripslashes($Credit_Card_Number) . "\" " . 
                        ">" . 
                    "</td>";
            echo "</tr>";
        
            // finished with the table
            echo "</table>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            // credit card expiration
            echo "<INPUT NAME='Credit_Card_Expiration' TYPE='HIDDEN' value=\"$Credit_Card_Expiration\">\n";
            
            // credit card expiration
            echo "<INPUT NAME='Credit_Card_Number' TYPE='HIDDEN' value=\"$Credit_Card_Number\">\n";
        }
    }
    
    //********************************************************************
    // DisplayInstructorPay()
    //
    // Purpose:  Display the member's instructor pay information fields.
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
    function DisplayInstructorPay()
    {    	
        global $AllowAircraftCheckout;

        global $MemberRST;
        global $Member_Ground_Instruction_Amount;
        global $Member_Private_Instruction_Amount;
        global $Member_Instrument_Instruction_Amount;
        global $Member_Commercial_Instruction_Amount;
        global $Member_CFI_Instruction_Amount;
        global $Member_CFII_Instruction_Amount;

        // set the column sizes
        $Column1Width = "20%";
        $Column2Width = "20%";
        $Column3Width = "20%";
        $Column4Width = "20%";
        $Column5Width = "20%";
        $Column6Width = "20%";

    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // set the table name  	
            $TableName = "InstructorPayTable";        
        
            // start the table
            echo "<TABLE ID='$TableName' border=1>";        
            
            // title
            echo "<tr>";
            echo " <th colspan='6'>Instructor Payment Per Hour</td>";
            echo "</tr>";
            
            // ground, instrument, CFI
            echo "<tr>";
            echo "<td width=$Column1Width align=left>Ground</td>";
            echo "<td width=$Column2Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Member_Ground_Instruction_Amount' " . 
                        "ID='Member_Ground_Instruction_Amount' " .
                        "align=left " . 
                        "SIZE=4 " . 
                        "VALUE=\"" . FormatField($Member_Ground_Instruction_Amount, "Currency") . "\" " . 
                        ">" . 
                    "</td>";
            echo "<td width=$Column3Width align=left>Inst</td>";
            echo "<td width=$Column4Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Member_Instrument_Instruction_Amount' " . 
                        "ID='Member_Instrument_Instruction_Amount' " .
                        "align=left " . 
                        "SIZE=4 " . 
                        "VALUE=\"" . FormatField($Member_Instrument_Instruction_Amount, "Currency") . "\" " . 
                        ">" . 
                    "</td>";
            echo "<td width=$Column5Width align=left>CFI</td>";
            echo "<td width=$Column6Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Member_CFI_Instruction_Amount' " . 
                        "ID='Member_CFI_Instruction_Amount' " .
                        "align=left " . 
                        "SIZE=4 " . 
                        "VALUE=\"" . FormatField($Member_CFI_Instruction_Amount, "Currency") . "\" " . 
                        ">" . 
                    "</td>";
            echo "</tr>";
            
            // private, commercial, CFII
            echo "<tr>";
            echo "<td width=$Column1Width align=left>Private</td>";
            echo "<td width=$Column2Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Member_Private_Instruction_Amount' " . 
                        "ID='Member_Private_Instruction_Amount' " .
                        "align=left " . 
                        "SIZE=4 " . 
                        "VALUE=\"" . FormatField($Member_Private_Instruction_Amount, "Currency") . "\" " . 
                        ">" . 
                    "</td>";
            echo "<td width=$Column3Width align=left>Comm</td>";
            echo "<td width=$Column4Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Member_Commercial_Instruction_Amount' " . 
                        "ID='Member_Commercial_Instruction_Amount' " .
                        "align=left " . 
                        "SIZE=4 " . 
                        "VALUE=\"" . FormatField($Member_Commercial_Instruction_Amount, "Currency") . "\" " . 
                        ">" . 
                    "</td>";
            echo "<td width=$Column5Width align=left>CFII</td>";
            echo "<td width=$Column6Width align=left>" . 
                    "<input " .
                        "type=text " .
                        "NAME='Member_CFII_Instruction_Amount' " . 
                        "ID='Member_CFII_Instruction_Amount' " .
                        "align=left " . 
                        "SIZE=4 " . 
                        "VALUE=\"" . FormatField($Member_CFII_Instruction_Amount, "Currency") . "\" " . 
                        ">" . 
                    "</td>";
            echo "</tr>";
        
            // finished with the table
            echo "</table>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            // ground, instrument, CFI
            echo "<INPUT NAME='Member_Ground_Instruction_Amount' TYPE='HIDDEN' value=\"$Member_Ground_Instruction_Amount\">\n";
            echo "<INPUT NAME='Member_Instrument_Instruction_Amount' TYPE='HIDDEN' value=\"$Member_Instrument_Instruction_Amount\">\n";
            echo "<INPUT NAME='Member_CFI_Instruction_Amount' TYPE='HIDDEN' value=\"$Member_CFI_Instruction_Amount\">\n";
            
            // private, commercial, CFII
            echo "<INPUT NAME='Member_Private_Instruction_Amount' TYPE='HIDDEN' value=\"$Member_Private_Instruction_Amount\">\n";
            echo "<INPUT NAME='Member_Commercial_Instruction_Amount' TYPE='HIDDEN' value=\"$Member_Commercial_Instruction_Amount\">\n";
            echo "<INPUT NAME='Member_CFII_Instruction_Amount' TYPE='HIDDEN' value=\"$Member_CFII_Instruction_Amount\">\n";
        }
    }

    //********************************************************************
    // DisplayUserFields()
    //
    // Purpose:  Display the user fields for the user to
    //           add or modify the information.
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
    function DisplayUserFields()
    { 
        global $AllowAircraftCheckout;

        // set the column sizes
        $Column1Width = "35%";
        $Column2Width = "30%";
        $Column3Width = "35%";
        
        // start the table to display the user fields
        echo "<table border=0>";
        
        // start the first row
        echo "<tr>";
         
        // fill in the left column information
        echo "<td width=$Column1Width>";
        DisplayMemberInformation();
        echo "</td>";
         
        // fill in the middle column information
        echo "<td width=$Column2Width>";
        DisplaySchedulingInformation();
        echo "<br>";
        DisplayMemberNotes();
        echo "</td>";
        
        // fill in the right column information
        echo "<td width=$Column3Width>";
        DisplayNotifyInformation();
        echo "</td>";
        
        // end the first row
        echo "</tr>";
        
        // start the second row
        echo "<tr>";
         
        // fill in the left column information
        echo "<td width=$Column1Width>";
        DisplayContractDues();
        echo "</td>";
         
        // fill in the middle column information
        echo "<td width=$Column2Width>";
        DisplayCreditCard();
        echo "</td>";
        
        // fill in the right column information
        echo "<td width=$Column3Width>";
        DisplayInstructorPay();
        echo "</td>";
        
        // end the second row
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
    }

    //********************************************************************
    // DisplayCurrenyFields()
    //
    // Purpose:  Display the currency fields for the user to
    //           add or modify the information.
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
    function DisplayCurrenyFields()
    { 
        global $AllowAircraftCheckout;

        global $NoCurrencyPilot, $StudentPilot, $PrivatePilotUnder200;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;

    	global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
    	global $UserLevelMaintenance, $UserLevelAdmin;
    	global $UserLevelDisabledName, $UserLevelNormalName, $UserLevelSuperName, $UserLevelOfficeName; 
    	global $UserLevelMaintenanceName, $UserLevelAdminName;
        
        global $Rating;            
        global $Member_Status;            
        global $Medical_Class;      
        global $Medical_Date;      

        global $MemberRST;
        global $Rules_Field;
		global $auth;
                
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            // start the table to display the currency fields
            echo "<center>";
            echo "<table border=1>";
            
            // display the header
            echo "<tr>";
            echo "<th colspan=6>Currency Information</td>";
            echo "</tr>";
            
            // start the first row
            echo "<tr>";
             
            // display the pilot identification information
            echo "<td>";
            BuildPilotIdentificationSelector($Rating);
            echo "</td>";
             
            // display the member status information
            echo "<td>";
            BuildMemberStatusSelector($Member_Status);
            echo "</td>";
             
            // display the medical information
            echo "<td>";
            BuildMedicalSelector($Medical_Class, $Medical_Date);
            echo "</td>";
        
            // if it is the office users or administrators, show a button to save and check currency
            if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
                authGetUserLevel(getUserName(), $auth["admin"]) ==  $UserLevelOffice)
            {
                echo "<td><input name='SaveCheckCurrency' type=submit value=\"CheckCurrency\" " . 
                        "ONCLICK='return ValidateAndSubmit()'></td>";
            }
            
            // end the first row
            echo "</tr>";
            
            // display the currency fields        
            echo "<tr><td colspan=10>";
            DisplayCurrencyInformation($Rating);
            echo "</td></tr>";
            
            // end the table
            echo "</table>";
            echo "</center>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            // pilot identification information
            echo "<INPUT NAME='Rating' TYPE='HIDDEN' value=\"$NoCurrencyPilot\">\n";
             
            // member status information
            echo "<INPUT NAME='Member_Status' TYPE='HIDDEN' value=\"$Member_Status\">\n";
             
            // medical information
            echo "<INPUT NAME='Medical_Class' TYPE='HIDDEN' value=\"$Medical_Class\">\n";
            echo "<INPUT NAME='MedicalDate' TYPE='HIDDEN' value=\"$MedicalDate\">\n";
            
            // no currency fields (only used if checking out an aircraft)
         }
    }

    //********************************************************************
    // DisplayManagersMessage()
    //
    // Purpose:  Display the manager's message for the user to
    //           add or modify the information.
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
    function DisplayManagersMessage()
    { 
        global $AllowAircraftCheckout;

        global $MemberRST;
        global $Manager_Message;
                
        // start the table to display the currency fields
        echo "<center>";
        echo "<table border=1>";
        
        // display the header
        echo "<tr>";
        echo "<th>Manager's Message</td>";
        echo "</tr>";
        
        // start the first row
        echo "<tr>";
        
        // display the manager message
        echo "<td>";
        echo "<TEXTAREA NAME='Manager_Message' ROWS=1 COLS=80 WRAP='virtual'>" . 
                htmlentities ( $Manager_Message ) . 
                "</TEXTAREA></TD></TR>";
    	echo "</td>";
         
        // end the first row
        echo "</tr>";
        
        // end the table
        echo "</table>";
        echo "</center>";
    }
    
    //********************************************************************
    // LoadNewUserValues()
    //
    // Purpose: Load new aircraft's information into the form
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
    function LoadNewUserValues()
    {
        global $NoCurrencyPilot, $StudentPilot, $PrivatePilotUnder200;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        global $MemberStatusActive, $MemberStatusInActive, $MemberStatusAircraft, $MemberStatusResigned;
        global $FirstClassMedical;
        global $SecondClassMedical;
        global $ThirdClassMedical;
        global $SpecialClassMedical;
        global $NullDate;
        
        global $MemberRST;
        
        include "DatabaseConstants.inc";
        
        // clear the currency fields
        PurgeCurrencyFields();
 
        // set the pilot identification information
        UpdateCurrencyFieldname("Rating", $StudentPilot);
    
        // set the member status information
        UpdateCurrencyFieldname("Member_Status", $MemberStatusActive);
    
        // set the pilot medical information
        UpdateCurrencyFieldname("Medical_Class", $ThirdClassMedical);
        
        // set the pilot medical date information
        UpdateCurrencyFieldname("Medical_Date", FormatField($NullDate, "Date"));
             
        // load the membership dates (join and resignation dates)
        $MemberRST[$Membership_Date_offset] = FormatField("now", "DatabaseDate");
        $MemberRST[$Resign_Date_offset] = FormatField($NullDate, "DatabaseDate");
        
        // set the online secheduling information
        $MemberRST[$password_offset] = "";
        $MemberRST[$email_offset] = "";
        $MemberRST[$user_level_offset] = GetUserLevel("Student");
        $MemberRST[$Allow_Phone_Number_Display_offset] = 1;
       
        // update the currency fields in the database record
        SaveDBCurrencyFields($RulesField);
        $MemberRST[$Rules_Field_offset] = $RulesField;
        
        // set the instructor of record field
        $MemberRST[$InstructorOfRecord_offset] = "None";
    }
                
    //********************************************************************
    // DeleteMember($UserName)
    //
    // Purpose:  Delete a member from the database.
    //
    // Inputs:
    //   UserName - username of the member to delete
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function DeleteMember($UserName)
    {
        global $day, $month, $year, $resource, $resource_id, $makemodel;
        
        // get the name for the journal entry from the database before 
        // we delete the record
        $DeletedName = GetNameFromUsername($UserName);
        
    	// delete the member from the database
        DeleteDatabaseRecord(
                            "AircraftScheduling_person",
                            "username='$UserName'");  
                             	
		// log the delete in to the journal
		$Description = 
					"Deleting information for user " . $UserName . 
                        " ($DeletedName)";
		CreateJournalEntry(strtotime("now"), getUserName(), $Description);
    }
        
    //********************************************************************
    // SetCurrencyValues($PilotRating)
    //
    // Purpose: Update the currency field values from the input values.
    //          Since the currency fields may change based on the aircraft
    //          types and currency rules, we will process the fields from
    //          the raw input.
    //
    // Inputs:
    //   PilotRating - rating (student, private, private under 200,
    //                 private over 200, instrument, CFI) of the pilot
    //
    // Outputs:
    //   none
    //
    // Returns:
    //   none
    //*********************************************************************
    function SetCurrencyValues($PilotRating)
    {        
        global $NoCurrencyPilot, $StudentPilot, $PrivatePilotUnder200;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        global $rdata;
      
        include "DatabaseConstants.inc";
        
        // arrays to hold the currency values and formats
        $CurrencyRuleText = array();
        $CurrencyStatusText = array();
        
        // if the pilot type is other than NoCurrencyRequired, load the currency fields
        if ($PilotRating != $NoCurrencyPilot)
        {
            // changes " " to "_" in the pilot rating currency field name
            $locPilotRating = Replace($PilotRating, " ", "_");
            
            // open a recordset for the currency fields
        	$sql = 
                "SELECT * FROM CurrencyFields WHERE " .
                    $locPilotRating .
                    " = 1 ORDER BY Currency_Field_Name";
        	$res = sql_query($sql);
            
            // if we had an error no records are found for this pilot type, don't process
            // the currency fields
            if($res) 
            {
                // no errors opening recordset, process the currency
        		for($reccnt=0; $CurrencyFieldsRST = sql_row($res, $reccnt); $reccnt++) 
        		{
                    // If this rule is an aircraft type rating rule there should be a matching
                    // aircraft in the database. We assume that the first part of the subrule
                    // (up to the "_") is the aircraft type
                    $AircraftCurrencyFieldName = $CurrencyFieldsRST[$Currency_Field_Name_offset];
                    $AircraftType = GetNextToken($AircraftCurrencyFieldName, "_");
                    
                    // if this is a valid aircraft type and it is a rental aircraft type
                    // put the field on the screen (non-rental aircraft don't have currency rules)
                    if (IsAircraftType($AircraftType))
                    {
                        // an aircraft type, is it a rental aircraft type
                        if (IsRentalAircraftType($AircraftType))
                        {
                            $ProcessField = True;
                        }
                        else
                        {
                            // non-rental (private aircraft) don't process flight test
                            // or written test fields
                            $ProcessField = False;
                        }
                    }
                    else
                    {
                        // not an aircraft type, process the field
                        $ProcessField = True;
                    }
            
                    // should this field be processed?
                    if ($ProcessField)
                    {
                        $FieldName = RemoveCurrencyFieldQuotes(
                                            $CurrencyFieldsRST[$Currency_Field_Name_offset]);
                        $FieldValue = FormatField(
                                                 $rdata[$FieldName], 
                                                 $CurrencyFieldsRST[$Currency_Field_Type_offset]);                                         
                        UpdateCurrencyFieldname(
                                                $CurrencyFieldsRST[$Currency_Field_Name_offset], 
                                                $FieldValue);
                    }
                }
            }
        	else 
            {
                // error processing database request, tell the user
                DisplayDatabaseError("SetCurrencyValues", $sql);
            }
        }
    }
        
    //********************************************************************
    // UpdateMemberInformation()
    //
    // Purpose: Update the member information database from the information
    //          entered by the user on the screen
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
    function UpdateMemberInformation()
    {        
        global $RSConversionNone, $RSConversionString, $RSConversionNumber, $RSConversionDate;
        global $AddModify;
        global $MemberStatusActive, $MemberStatusInActive, $MemberStatusAircraft, $MemberStatusResigned;
        global $PrivatePilotOver200, $InstrumentPilot, $CFIPilot, $InstructorInstruction;
        global $NullDate;

        global $OldFirstName;
        global $OldLastName;

        global $person_id;
        global $first_name;
        global $middle_name;
        global $last_name;
        global $title;
        global $email;
        global $username;
        global $password;
        global $user_level;
        global $login_counter;
        global $last_login;
        global $address1;
        global $address2;
        global $city;
        global $state;
        global $zip;
        global $phone_number;
        global $SSN;
        global $Organization;
        global $Work_Ext;
        global $Home_Phone;
        global $Dues_Amount;
        global $Member_Ground_Instruction_Amount;
        global $Member_Private_Instruction_Amount;
        global $Member_Instrument_Instruction_Amount;
        global $Member_Commercial_Instruction_Amount;
        global $Member_CFI_Instruction_Amount;
        global $Member_CFII_Instruction_Amount;
        global $Contract_Number;
        global $Notify_First_Name;
        global $Notify_Middle_Initial;
        global $Notify_Last_Name;
        global $Notify_Relation;
        global $Notify_Address;
        global $Notify_City;
        global $Notify_State;
        global $Notify_Zip;
        global $Notify_Phone1;
        global $Notify_Phone2;
        global $Contract_Expiration_Date;
        global $Rules_Field;
        global $Member_Notes;
        global $Credit_Card_Number;
        global $Credit_Card_Expiration;
        global $Manager_Message;
        global $Membership_Date;
        global $Resign_Date;
        global $Clearing_Authority;
        global $Password_Expires_Date;
        global $Allow_Phone_Number_Display;
        global $OldUsername;
        global $InstructorOfRecord;
        
        // special currency values
        global $Rating;            
        global $Member_Status;            
        global $Medical_Class;      
        global $Medical_Date;      

        $DatabaseFields = array();
            
        // put the user's keycode in the database
        SetDatabaseRecord("username", $username, $RSConversionString, $DatabaseFields[0]);
        
        // update the member information from the screen
        SetDatabaseRecord("first_name",
                        Trim($first_name), $RSConversionString, $DatabaseFields[1]);
        SetDatabaseRecord("middle_name",
                        Trim($middle_name), $RSConversionString, $DatabaseFields[2]);
        SetDatabaseRecord("last_name",
                        Trim($last_name), $RSConversionString, $DatabaseFields[3]);
        SetDatabaseRecord("address1",
                        Trim($address1), $RSConversionString, $DatabaseFields[4]);
        SetDatabaseRecord("city",
                        Trim($city), $RSConversionString, $DatabaseFields[5]);
        SetDatabaseRecord("state",
                        Trim($state), $RSConversionString, $DatabaseFields[6]);
        SetDatabaseRecord("zip",
                        Trim($zip), $RSConversionString, $DatabaseFields[7]);
        SetDatabaseRecord("Organization",
                        Trim($Organization), $RSConversionString, $DatabaseFields[8]);
        SetDatabaseRecord("SSN",
                        EncryptString($SSN), $RSConversionString, $DatabaseFields[9]);
        SetDatabaseRecord("Home_Phone",
                        Trim($Home_Phone), $RSConversionString, $DatabaseFields[10]);
        SetDatabaseRecord("phone_number",
                        Trim($phone_number), $RSConversionString, $DatabaseFields[11]);
        
        // update the notify information from the screen
        SetDatabaseRecord("Notify_First_Name",
                        Trim($Notify_First_Name), $RSConversionString, $DatabaseFields[12]);
        SetDatabaseRecord("Notify_Middle_Initial",
                        Trim($Notify_Middle_Initial), $RSConversionString, $DatabaseFields[13]);
        SetDatabaseRecord("Notify_Last_Name",
                        Trim($Notify_Last_Name), $RSConversionString, $DatabaseFields[14]);
        SetDatabaseRecord("Notify_Address",
                        Trim($Notify_Address), $RSConversionString, $DatabaseFields[15]);
        SetDatabaseRecord("Notify_City",
                        Trim($Notify_City), $RSConversionString, $DatabaseFields[16]);
        SetDatabaseRecord("Notify_State",
                        Trim($Notify_State), $RSConversionString, $DatabaseFields[17]);
        SetDatabaseRecord("Notify_Zip",
                        Trim($Notify_Zip), $RSConversionString, $DatabaseFields[18]);
        SetDatabaseRecord("Notify_Relation",
                        Trim($Notify_Relation), $RSConversionString, $DatabaseFields[19]);
        SetDatabaseRecord("Notify_Phone1",
                        Trim($Notify_Phone1), $RSConversionString, $DatabaseFields[20]);
        SetDatabaseRecord("Notify_Phone2",
                        Trim($Notify_Phone2), $RSConversionString, $DatabaseFields[21]);
        SetDatabaseRecord("Dues_Amount",
                        GetNumber($Dues_Amount), $RSConversionNumber, $DatabaseFields[22]);
        if ($Clearing_Authority)
            SetDatabaseRecord("Clearing_Authority", 1, $RSConversionNumber, $DatabaseFields[23]);
        else
            SetDatabaseRecord("Clearing_Authority", 0, $RSConversionNumber, $DatabaseFields[23]);
        
        SetDatabaseRecord("Credit_Card_Number", EncryptString($Credit_Card_Number), $RSConversionString, $DatabaseFields[24]);
        SetDatabaseRecord("Credit_Card_Expiration",
                                FormatField(
                                    DateSerial(Year($Credit_Card_Expiration), Month($Credit_Card_Expiration), 1), 
                                    "DatabaseDate"), 
                                $RSConversionString, $DatabaseFields[25]);
        SetDatabaseRecord("Manager_Message",
                        $Manager_Message, $RSConversionString, $DatabaseFields[26]);
    
        // instructor rates
        SetDatabaseRecord("Ground_Instruction_Amount",
                        GetNumber($Member_Ground_Instruction_Amount), $RSConversionNumber, $DatabaseFields[27]);
        SetDatabaseRecord("Private_Instruction_Amount",
                        GetNumber($Member_Private_Instruction_Amount), $RSConversionNumber, $DatabaseFields[28]);
        SetDatabaseRecord("Instrument_Instruction_Amount",
                        GetNumber($Member_Instrument_Instruction_Amount), $RSConversionNumber, $DatabaseFields[29]);
        SetDatabaseRecord("Commercial_Instruction_Amount",
                        GetNumber($Member_Commercial_Instruction_Amount), $RSConversionNumber, $DatabaseFields[30]);
        SetDatabaseRecord("CFI_Instruction_Amount",
                        GetNumber($Member_CFI_Instruction_Amount), $RSConversionNumber, $DatabaseFields[31]);
        SetDatabaseRecord("CFII_Instruction_Amount",
                        GetNumber($Member_CFII_Instruction_Amount), $RSConversionNumber, $DatabaseFields[32]);
        
        // instructor information
        SetDatabaseRecord("Contract_Number",
                        $Contract_Number, $RSConversionString, $DatabaseFields[33]);
        SetDatabaseRecord("Contract_Expiration_Date",
                    FormatField($Contract_Expiration_Date, "DatabaseDate"), $RSConversionString, $DatabaseFields[34]);
    
        // member notes
        SetDatabaseRecord("Member_Notes",
                        $Member_Notes, $RSConversionString, $DatabaseFields[35]);
    
        // clear any existing currency information in case the pilot type has changed
        // this way we won't have any carry overs if the pilot rank decreses
        PurgeCurrencyFields();
        
        // update the currency field values from the input values
        SetCurrencyValues($Rating);
        
        // PilotIdentificationCombo is a special case, save the value
        UpdateCurrencyFieldname("Rating", $Rating);
        
        // MemberStatusCombo is a special case, save the value
        UpdateCurrencyFieldname("Member_Status", $Member_Status);
        
        // PilotMedicalCombo is a special case, save the value
        UpdateCurrencyFieldname("Medical_Class", $Medical_Class);
        
        // Medical_Date is a special case, save the value
        UpdateCurrencyFieldname("Medical_Date", $Medical_Date);
    
        // save the currency values to the database
        SaveDBCurrencyFields($RulesField);
        SetDatabaseRecord("Rules_Field", $RulesField, $RSConversionString, $DatabaseFields[36]);
        
        // save the membership dates (join and resignation dates)
        if ($Member_Status != $MemberStatusResigned)
        {
            // member not resigned, clear the resignation date
            $Resign_Date = FormatField($NullDate, "DatabaseDate");
        }
        SetDatabaseRecord("Membership_Date", $Membership_Date, $RSConversionString, $DatabaseFields[37]);
        if ((FormatField($Resign_Date, "Date") == FormatField($NullDate, "Date") ||
             $Resign_Date == "0000-00-00 00:00:00") &&
            $Member_Status == $MemberStatusResigned)
        {            
            // the member has resigned, set the resignation date
            SetDatabaseRecord("Resign_Date", FormatField("now", "DatabaseDate"), $RSConversionString, $DatabaseFields[38]);
        }
        else
        {
            // use the previous resignation value
            SetDatabaseRecord("Resign_Date", FormatField($Resign_Date, "DatabaseDate"), $RSConversionString, $DatabaseFields[38]);
        }
                
        // if the member has resigned, disable the login
        if ($Member_Status == $MemberStatusResigned ||
            $Member_Status == $MemberStatusInActive)
        {            
           // member has resigned, disable the online scheduling login
            SetDatabaseRecord("user_level",
                        0,
                        $RSConversionString, $DatabaseFields[39]);
        }
        else
        {
            // member has not resigned, set the user level based on their
            // pilot type
            SetDatabaseRecord("user_level",
                        GetUserLevel($Rating),
                        $RSConversionString, $DatabaseFields[39]);
        }
        
        // set the online secheduling information
        SetDatabaseRecord("user_level", $user_level, $RSConversionString, $DatabaseFields[39]);
        SetDatabaseRecord("email", $email, $RSConversionString, $DatabaseFields[40]);
        SetDatabaseRecord("password", $password, $RSConversionString, $DatabaseFields[41]);
        SetDatabaseRecord("Allow_Phone_Number_Display", $Allow_Phone_Number_Display, $RSConversionNumber, $DatabaseFields[42]);
        
        // save the instructor of record
        SetDatabaseRecord("InstructorOfRecord", $InstructorOfRecord, $RSConversionString, $DatabaseFields[43]);
    
        // save the database record
        if (UCase($AddModify) == "MODIFY")
        {
            // update the current record
            UpdateDatabaseRecord(
                                "AircraftScheduling_person",
                                $DatabaseFields,
                                "username='" . UCase(Trim($username)) . "'");
        }
        else
        {
            // adding a new member, set the
            // online scheduling information to defaults
            SetDatabaseRecord("counter", 0, $RSConversionNumber, $DatabaseFields[44]);
            SetDatabaseRecord("last_login", FormatField("now", "DatabaseDate"), $RSConversionString, $DatabaseFields[45]);
            SetDatabaseRecord("address2", " ", $RSConversionString, $DatabaseFields[46]);
            SetDatabaseRecord("Password_Expires_Date", FormatField("now", "DatabaseDate"), $RSConversionString, $DatabaseFields[47]);
            SetDatabaseRecord("title", " ", $RSConversionString, $DatabaseFields[48]);
            
            // add a new record
            AddDatabaseRecord(
                                "AircraftScheduling_person",
                                $DatabaseFields);
        }
        
        // set the scheduling status for this member
        if ($Rating == $CFIPilot && $Member_Status == $MemberStatusActive)
        {
            // member is schedulable by the online schedule
            EnableScheduling(BuildName(Trim($first_name), Trim($last_name)));
            
            // update the instructor costs in the instructor table
            $DatabaseFields = array();
            SetDatabaseRecord("hourly_cost",
                            GetGeneralPreferenceValue("Private_Instruction_Amount"),
                            $RSConversionNumber,
                            $DatabaseFields[0]);
            UpdateDatabaseRecord(
                                "AircraftScheduling_instructors",
                                $DatabaseFields,
                                "person_id=" . Str($person_id));
        }
        else
        {
            // member is not schedulable by the online schedule
            DisableScheduling(BuildName(Trim($first_name), Trim($last_name)));
        }
        
        // update the name in the schedule entries if the first or last name has changed
        if (!empty($OldFirstName) && !empty($OldLastName))
        {
            if ($OldFirstName != Trim($first_name) ||
                $OldLastName != Trim($last_name))
            {
                
                // update the user name in the entries table
                $DatabaseFields = array();
                SetDatabaseRecord("name",
                                BuildName(Trim($first_name), Trim($last_name)),
                                $RSConversionString,
                                $DatabaseFields[0]);
                UpdateDatabaseRecord(
                                    "AircraftScheduling_entry",
                                    $DatabaseFields,
                                    "name='" . BuildName($OldFirstName, $OldLastName) . "'");
            }
        }
        
        // log the change in the journal
        if (UCase($AddModify) == "MODIFY")
        {
        	$Description = 
        				"Updating member information for " . $username . 
                        " (" . GetNameFromUsername($username) . ")";
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
        }
        else
        {
        	$Description = 
        				"Adding member " . $username . 
                        " (" . GetNameFromUsername($username) . ")";
        	CreateJournalEntry(strtotime("now"), getUserName(), $Description);
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
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelOffice))
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    	exit();
    }

    // build the filter parameters
    $FilterParameter =  "&order_by=$order_by";

    // this script will call itself whenever the submit or Cancel button is pressed
    // we will check here for the update and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 &&
        ($AddModifyMember == "Submit" || $SaveCheckCurrency == "CheckCurrency"))
    {
        // acquire mutex to prevent concurrent member modifications
        if (!sql_mutex_lock('AircraftScheduling_person'))
            fatal_error(1, "Failed to acquire exclusive database access");

        // if we are modifying an existing user, don't check for existing users
        if (UCase($AddModify) == "MODIFY")
        {
            // modifying an existing user, don't worry if name or username is unique
    		$ExistingUsername = 0;
    		$ExistingName = 0;
        }
        else
        {
            // adding a new user, make sure that the username and name are not already
            // in the database
    		$ExistingUsername = sql_query1("SELECT COUNT(*) FROM AircraftScheduling_person WHERE username = '$username'");
    		$ExistingName = sql_query1(
    		                            "SELECT COUNT(*) " . 
    		                            "FROM AircraftScheduling_person " .
    		                            "WHERE $DatabaseNameFormat = '" . buildName($first_name, $last_name) . "'");
        }
        
        // if the username or name already exists in the database, make them choose another
        if ($ExistingUsername > 0)
        {
            // username already exists in the database
            $ErrorMessage = $ErrorMessage . "<b>Username must be unique<br><br>";
        }
        else if ($ExistingName > 0)
        {
            // name already exists in the database
            $ErrorMessage = $ErrorMessage . "<b>First name and last name must be unique<br><br>";
        }
        else
        {
            // unique new user or modifying existing user
                
            // save the old user level so we can detect a change
    		$old_user_level = sql_query1("SELECT user_level FROM AircraftScheduling_person WHERE username = '$username'");
    
            // save the member information in the database
            UpdateMemberInformation();
    	
    		// if the user level has changed, send them email notifying them of the change
    		if (isset($old_user_level) && 
    			isset($user_level) && 
    			$old_user_level != $user_level) 
    		{
    			// if the user is enabled, let them know they can add to the schedule
    			if ($user_level > $UserLevelDisabled)
    			{
    				// user can login
    				AircraftSchedulingMail(
    					$email, 
    					$AircraftScheduling_company . $lang["subject_user_enabled"], 
    					$lang["user_enabled"] . $AircraftScheduling_company);
    			}
    			else
    			{
    				// user cannot login
    				AircraftSchedulingMail(
    					$email, 
    					$AircraftScheduling_company . $lang["subject_user_enabled"], 
    					$lang["user_disabled"] .  $AircraftScheduling_company);
    			}
    		}
    		
    		// if we have been asked to check currency, take them to that screen
    		// with the parameters set
    		if ($SaveCheckCurrency == "CheckCurrency")
    		{
        	    header("Location: CheckCurrency.php" . 
                	                "?CurrencyName=" . buildName($first_name, $last_name) .
                	                "&day=$day&month=$month&year=$year" .
                	                "&resource=$resource" .
                	                "&resource_id=$resource_id" .
                	                "&InstructorResource=$InstructorResource" .
                	                "$makemodel" . 
         	                        "&goback=AddModifyMember.php" . 
        	                        "&GoBackParameters=" . 
        	                            BuildGoBackParameters("?AddModify=Modify&username=$username" . 
                                                $FilterParameter .
        	                                    "&goback=DisplayMembers.php"));
    		}
    		else
    		{
                // updates to the member are complete, take them back to the last screen
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
            }
                        
            // finished with this part of the script
            sql_mutex_unlock('AircraftScheduling_person');
            exit;
        }
        sql_mutex_unlock('AircraftScheduling_person');
    }
    else if(count($_POST) > 0 && $MemberCancel == "Cancel")
    {
        // user canceled the member changes, take them back to the last screen
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
    else if(count($_POST) > 0 && $MemberDelete == "Delete") 
    {
        // user is deleting the member 
        DeleteMember($username);
        
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
    
    // make sure that the InstructorOfRecord field exists in the database. if the 
    // database is an older version, it may not be there
    $sql = "SELECT InstructorOfRecord FROM AircraftScheduling_person LIMIT 1";
    if (sql_query1($sql) == -1)
    {
        // value doesn't exist, add it to the database
        $sql = "ALTER TABLE AircraftScheduling_person " .
                "ADD InstructorOfRecord VARCHAR( 20 ) " .
                "DEFAULT 'None' NOT NULL";
		sql_command($sql);
    }
    
    // are we modifying or adding a new member
    if (UCase($AddModify) == "MODIFY")
    {
        // modifying an existing member, get the information from the database
    	$sql = 
    			"SELECT " .
    			    "* " .
    			"FROM " .
    			    "AircraftScheduling_person " .
        		"WHERE " .
        			"username='$username'";
    	$res = sql_query($sql);
         
        // if we didn't have any errors, process the results of the database inquiry
        if($res) 
        {
            // database enquiry successful, get the row data
            $MemberRST = sql_row($res, 0);
        
            // decrypt any variables
            $MemberRST[$SSN_offset] = DecryptString($MemberRST[$SSN_offset]);
            $MemberRST[$Credit_Card_Number_offset] = DecryptString($MemberRST[$Credit_Card_Number_offset]);
        }
    	else 
        {
            // error processing database request, tell the user
            DisplayDatabaseError("AddModifyMember", $sql);
        }
    }
    else
    {
        // adding a new member, fill in the default information
        $MemberRST = array();
        LoadNewUserValues();
    }
    
    // since this screen may be reloaded when the currency values are changed, copy
    // the database variables to the screen variables
    SetScreenVariables();
        
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");

    // start the form
	echo "<FORM NAME='main' ACTION='AddModifyMember.php' METHOD='POST'>";

    // start the table to display the member information
    echo "<center>";
    echo "<table border=0>";

    // tell the user what we are doing
    echo "<TR><TD colspan=2>";
    if (UCase($AddModify) == "ADD")
    {
        // adding a member
        echo "<CENTER><H2>Add New User</H2>";
        echo "Username: <input name='username' " . 
                "id='username' " .
                "type='text' " .
                "value=\"$username\" " .
                "onChange='UpdatePassword()' " .
                "size=8>\n";
        
        // did we have any errors processing the new inputs
        if (len($ErrorMessage) > 0)
        {
            // errors found, show them
            echo "<br>$ErrorMessage";
        }
    }
    else
    {
        echo "<center><h2>Modify User $username Information</h2>";
        
        // if we have a valid login date
        if ($last_login != "00000000000000")
        {
            // convert the MySQL date stamp into a unix date and time
            if (strlen($last_login) == 14)
            {
                list($LL_year, $LL_month, $LL_day, $LL_hour, $LL_minute, $LL_seconds) = 
                                sscanf($last_login, '%4s%2s%2s%2s%2s%2s');
            }
            else                            
            {
                list($LL_year, $LL_month, $LL_day, $LL_hour, $LL_minute, $LL_seconds) = 
                                sscanf($last_login, '%4s-%2s-%2s %2s:%2s:%2s');
            }
            $LastLoginFormated = 
                    date('d-M-Y h:i:s', 
                            mktime($LL_hour, $LL_minute, $LL_seconds - TimeZoneAdjustment(), $LL_month, $LL_day, $LL_year));
        }
        else
        {
            // we had an error in the login date
            $LastLoginFormated = "Unknown";
        }
        echo "<b>Last Login: " . $LastLoginFormated . " Login Count: " . $login_counter . "</b>";
        echo "<input name='username' type='Hidden' value=\"$username\">\n";
    }
    echo "</center></td></tr>";

    // finished with the table
    echo "</table>";
    
    // member information
    echo "<table border=0>";
    echo "<tr>";
    echo "<TD>";
    DisplayUserFields();
    echo "</TD></TR>";

    // display the currency information
    echo "<tr>";
    echo "<td width=100% colspan=3>";
    DisplayCurrenyFields();
    echo "</td></tr>";
    
    // manager's message
    echo "<tr>";
    echo "<td width=100% colspan=3>";
    DisplayManagersMessage();
    echo "</td></tr>";
    
    // finished with the table
    echo "</table>";
    
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' value=\"$goback\">\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' value=\"$GoBackParameters\">\n";
   
    // generate the update and cancel buttons
    echo "<center>";
    echo "<table>";
    echo "<tr>";
    
    // only office users or administrators can save changes
    if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
        authGetUserLevel(getUserName(), $auth["admin"]) ==  $UserLevelOffice)
    {
        echo "<td><input name='AddModifyMember' type=submit value='Submit' ONCLICK='return ValidateAndSubmit()'></TD>";
    }
        
    // if it is the office users or administrators, give them a cancel button with confirmation
    if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
        authGetUserLevel(getUserName(), $auth["admin"]) ==  $UserLevelOffice)
    {
        echo "<td><input name='MemberCancel' type=submit value='Cancel' onClick=\"return confirm('" .  
                        $lang["CancelMember"] . "')\"></TD>";
    }
    else
    {
        // all other users get an Cancel button without a confirm
        // since the information is read only
        echo "<td><input name='MemberCancel' type=submit value='Cancel'></TD>";
    }

    // only allow deletes if we are not adding a new user
    if (UCase($AddModify) == "MODIFY")
    {
        // only office users or administrators can delete users
        if(authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelAdmin ||
            authGetUserLevel(getUserName(), $auth["admin"]) ==  $UserLevelOffice)
        {
            echo "<TD><input name='MemberDelete' type=submit value='Delete' onClick=\"return confirm('" .  
                        $lang["DeleteMember"] . "')\"></td>";
        }
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
    echo "<INPUT NAME='AddModify' TYPE='HIDDEN' value=\"" . $AddModify ."\">\n";
	if (array_key_exists($first_name_offset, $MemberRST ))
	{
		// key exists copy it
		echo "<INPUT NAME='OldFirstName' TYPE='HIDDEN' VALUE=\"" . $MemberRST[$first_name_offset] ."\">\n";
	}
	else
	{
		// key doesn't exists set to default
		echo "<INPUT NAME='OldFirstName' TYPE='HIDDEN' VALUE=''>\n";
	}

	if (array_key_exists($last_name_offset, $MemberRST ))
	{
		// key exists copy it
		echo "<INPUT NAME='OldLastName' TYPE='HIDDEN' VALUE=\"" . $MemberRST[$last_name_offset] ."\">\n";
	}
	else
	{
		// key doesn't exists set to default
		echo "<INPUT NAME='OldLastName' TYPE='HIDDEN' VALUE=''>\n";
	}

	if (array_key_exists($person_id_offset, $MemberRST ))
	{
		// key exists copy it
		echo "<INPUT NAME='person_id' TYPE='HIDDEN' VALUE=\"" . $MemberRST[$person_id_offset] ."\">\n";
	}
	else
	{
		// key doesn't exists set to default
		echo "<INPUT NAME='person_id' TYPE='HIDDEN' VALUE=''>\n";
	}

	if (array_key_exists($Membership_Date_offset, $MemberRST ))
	{
		// key exists copy it
		echo "<INPUT NAME='Membership_Date' TYPE='HIDDEN' VALUE=\"" . $MemberRST[$Membership_Date_offset] ."\">\n";
	}
	else
	{
		// key doesn't exists set to default
		echo "<INPUT NAME='Membership_Date' TYPE='HIDDEN' VALUE=''>\n";
	}

	if (array_key_exists($Resign_Date_offset, $MemberRST ))
	{
		// key exists copy it
		echo "<INPUT NAME='Resign_Date' TYPE='HIDDEN' VALUE=\"" . $MemberRST[$Resign_Date_offset] ."\">\n";
	}
	else
	{
		// key doesn't exists set to default
		echo "<INPUT NAME='Resign_Date' TYPE='HIDDEN' VALUE=''>\n";
	}
    
    // save the filter information
    echo "<INPUT NAME='order_by' TYPE='HIDDEN' VALUE='$order_by'>\n";
        
    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
    
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--
// global variables
var ErrorInDates = false;       // set true to prevent the submit from
                                // continuing if we have an error in a
                                // date.

//********************************************************************
// FormatDate(ControlName)
//
// Purpose: Format and validate a date field.
//
// Inputs:
//   ControlName - name of the control to check the date for
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function FormatDate(ControlName)
{ 
    // array of month days
    var MonthNames = new Array(
                    "Jan", "Feb", "Mar", "Apr", "May", "Jun", 
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
            
    // set the password to the username if one was not entered
    DateString = document.getElementById(ControlName).value;
    
    // remove "-" since the Date object doesn't understand them
    DateString = DateString.replace(/-/g, " ");
    var FormatedDate = new Date(DateString);
    
    // if the date is valid, put it in the screen
    if (FormatedDate != "Invalid Date" && FormatedDate != "NaN")
    {
        // valid date, format the date
        DayOfMonth = FormatedDate.getDate();
        if (DayOfMonth > 0 && DayOfMonth < 10)
        {
            OutputDate = "0" + DayOfMonth + "-" +
                         MonthNames[FormatedDate.getMonth()] + "-" +
                         FormatedDate.getFullYear();
        }
        else
        {
            OutputDate = DayOfMonth + "-" +
                         MonthNames[FormatedDate.getMonth()] + "-" +
                         FormatedDate.getFullYear();
        }                         
        document.getElementById(ControlName).value = OutputDate;
        ErrorInDates = false;
        return true;
    }
    else
    {
        // invalid date, tell the user   
        alert("The date " + DateString + " is not a valid date format.\n" +
                "Date format must be: dd mmm yyyy, dd-mmm-yyyy, mm/dd/yy or mm/dd/yyyy");
        setTimeout("SetFocus('" + ControlName + "')", 0);
        ErrorInDates = true;
        return false;
    }
}

//********************************************************************
// SetFocus(ControlName)
//
// Purpose: Select and set the focus to the given field.
//
// Inputs:
//   ControlName - name of the control to check the date for
//
// Outputs:
//   none
//
// Returns:
//   none
//*********************************************************************
function SetFocus(ControlName)
{ 
    document.getElementById(ControlName).focus();
    document.getElementById(ControlName).select();
}

//********************************************************************
// UpdatePassword()
//
// Purpose: Set the password to the username if the password is not
//          already entered.
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
function UpdatePassword()
{         
    // set the password to the username if one was not entered
    Password = document.getElementById('password').value;
    if (Password.length == 0)
    {
        UserName = document.getElementById('username').value;
        document.getElementById('password').value = UserName;
    }
}

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
    // if there are any uncorrected errors in the date fields,
    // don't allow the submit
    if (ErrorInDates) return false;

    // if we are adding a member, make sure the UserName was entered
    if (AddModify == "Add")
    {
        // adding a member
        // make sure the username was entered
        UserName = document.getElementById('username').value;
        if (UserName.length == 0)
        {
            alert("Username for the new member is required.\n" +
                    "Please enter a value.");
            document.getElementById('username').focus();
            document.getElementById('username').select();
            
            // error found, don't let them continue
            return false;
        }
        
        // set the password to the username if one was not entered
        Password = document.getElementById('password').value;
        if (Password.length == 0)
        {
            document.getElementById('password').value = UserName;
        }
    }
        
    // make sure the first name was entered
    FirstName = document.getElementById('first_name').value;
    if (FirstName.length == 0)
    {
        alert("The first name for the member is required.\n" +
                "Please enter a value.");
        document.getElementById('first_name').focus();
        document.getElementById('first_name').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // make sure the last name was entered
    LastName = document.getElementById('last_name').value;
    if (LastName.length == 0)
    {
        alert("The last name for the member is required.\n" +
                "Please enter a value.");
        document.getElementById('last_name').focus();
        document.getElementById('last_name').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // make sure the address was entered
    Address = document.getElementById('address1').value;
    if (Address.length == 0)
    {
        alert("The address for the member is required.\n" +
                "Please enter a value.");
        document.getElementById('address1').focus();
        document.getElementById('address1').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // make sure the city was entered
    City = document.getElementById('city').value;
    if (City.length == 0)
    {
        alert("The city for the member is required.\n" +
                "Please enter a value.");
        document.getElementById('city').focus();
        document.getElementById('city').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // make sure the state was entered
    State = document.getElementById('state').value;
    if (State.length == 0)
    {
        alert("The state for the member is required.\n" +
                "Please enter a value.");
        document.getElementById('state').focus();
        document.getElementById('state').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // make sure the zip code was entered
    Zip = document.getElementById('zip').value;
    if (Zip.length == 0)
    {
        alert("The zip code for the member is required.\n" +
                "Please enter a value.");
        document.getElementById('zip').focus();
        document.getElementById('zip').select();
        
        // error found, don't let them continue
        return false;
    }
    
    // make sure the password was entered
    Password = document.getElementById('password').value;
    if (Password.length == 0)
    {
        alert("The password for the member is required.\n" +
                "Please enter a value.");
        document.getElementById('password').focus();
        document.getElementById('password').select();
        
        // error found, don't let them continue
        return false;
    }
             
    // no errors found, return
	return true;
}

//-->
</script>
