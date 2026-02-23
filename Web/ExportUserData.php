<?php
//-----------------------------------------------------------------------------
// 
// ExportUserData.php
// 
// PURPOSE: Export the selected members.
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
//      debug_flag - set to non-zero to enable debug output information
//      SelectedTable - set to the name of the table to export
// 
// REQUREMENTS IMPLEMENTED:
//      none
//
// COMMENTS:
//      Database selection fields are passed in to control the exporting of
//      the data.
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
    $SelectedTable = "All";
    $UserSortSelection = "last_name";
    $Rating = "All";
    $Member_Status = "All";
    $FieldSeparator = ",";

    $Aircraft_status = "All";    
    $AircraftSortSelection = "TailNumber";
    $AircraftRentalSelection = "All";
    
    // get the starting and ending name in the member list
    $UserResult = SQLOpenRecordset(
                "SELECT $DatabaseNameFormat " .
                "FROM AircraftScheduling_person " .
                "WHERE user_level != $UserLevelDisabled " .
                "ORDER by last_name");      
     
    // process the results of the database inquiry
    $row = sql_row($UserResult, 0);     
    $MembersFromNameOfUser = $row[0];
    $row = sql_row($UserResult, (sql_count($UserResult) - 1));  
    $MembersToNameOfUser = $row[0];
    
    // get the starting and ending aircraft in the aircraft list
    $AircraftResult = SQLOpenRecordset(
                "SELECT n_number " .
                "FROM AircraftScheduling_aircraft " .
                "ORDER by n_number");       
     
    // process the results of the database inquiry
    $row = sql_row($AircraftResult, 0);     
    $AircraftFromTailNumber = $row[0];
    $row = sql_row($AircraftResult, (sql_count($AircraftResult) - 1));  
    $AircraftToTailNumber = $row[0];

    // default export schedule selection
    $default_schedule_days = 30;
    $FromScheduleTime = mktime(0, 0, 0, date("m"), date("d") - $default_schedule_days, date("Y"));
    $FromScheduleday = date("d", $FromScheduleTime);
    $FromSchedulemonth = date("m", $FromScheduleTime);
    $FromScheduleyear = date("Y", $FromScheduleTime);
    $ToScheduleDay   = date("d");
    $ToScheduleMonth = date("m");
    $ToScheduleYear  = date("Y");

    // default export inventory selection
    $default_inventory_days = 30 * 365;
    $FromInventoryTime = mktime(0, 0, 0, date("m"), date("d") - $default_inventory_days, date("Y"));
    $FromInventoryday = date("d", $FromInventoryTime);
    $FromInventorymonth = date("m", $FromInventoryTime);
    $FromInventoryyear = date("Y", $FromInventoryTime);
    $ToInventoryDay   = date("d");
    $ToInventoryMonth = date("m");
    $ToInventoryYear  = date("Y");

    // default export flight selection
    $FlightSortSelection = "Date";
    $default_flight_days = 30;
    $FromFlightTime = mktime(0, 0, 0, date("m"), date("d") - $default_flight_days, date("Y"));
    $FromFlightday = date("d", $FromFlightTime);
    $FromFlightmonth = date("m", $FromFlightTime);
    $FromFlightyear = date("Y", $FromFlightTime);
    $ToFlightDay   = date("d");
    $ToFlightMonth = date("m");
    $ToFlightYear  = date("Y");

    // default export charge selection
    $ChargeSortSelection = "Date";
    $default_charge_days = 30;
    $FromChargeTime = mktime(0, 0, 0, date("m"), date("d") - $default_charge_days, date("Y"));
    $FromChargeday = date("d", $FromChargeTime);
    $FromChargemonth = date("m", $FromChargeTime);
    $FromChargeyear = date("Y", $FromChargeTime);
    $ToChargeDay   = date("d");
    $ToChargeMonth = date("m");
    $ToChargeYear  = date("Y");

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
    if(isset($rdata["ExportUserData"])) $ExportUserData = $rdata["ExportUserData"];
    if(isset($rdata["ExportCancel"])) $ExportCancel = $rdata["ExportCancel"];
    if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
    if(isset($rdata["SelectedTable"])) $SelectedTable = $rdata["SelectedTable"];

    if(isset($rdata["FieldSeparator"])) $FieldSeparator = $rdata["FieldSeparator"];

    if(isset($rdata["MembersFromNameOfUser"])) $MembersFromNameOfUser = $rdata["MembersFromNameOfUser"];
    if(isset($rdata["MembersToNameOfUser"])) $MembersToNameOfUser = $rdata["MembersToNameOfUser"];
    if(isset($rdata["Rating"])) $Rating = $rdata["Rating"];
    if(isset($rdata["Member_Status"])) $Member_Status = $rdata["Member_Status"];
    if(isset($rdata["UserSortSelection"])) $UserSortSelection = $rdata["UserSortSelection"];
    
    if(isset($rdata["AircraftFromTailNumber"])) $AircraftFromTailNumber = $rdata["AircraftFromTailNumber"];
    if(isset($rdata["AircraftToTailNumber"])) $AircraftToTailNumber = $rdata["AircraftToTailNumber"];    
    if(isset($rdata["Aircraft_status"])) $Aircraft_status = $rdata["Aircraft_status"];
    if(isset($rdata["AircraftSortSelection"])) $AircraftSortSelection = $rdata["AircraftSortSelection"];
    if(isset($rdata["AircraftRentalSelection"])) $AircraftRentalSelection = $rdata["AircraftRentalSelection"];
    
    if(isset($rdata["Aircraft_status"])) $Aircraft_status = $rdata["Aircraft_status"];
        
    // schedule database table fields
    if(isset($rdata["FromScheduleday"])) $FromScheduleday = $rdata["FromScheduleday"];
    if(isset($rdata["FromSchedulemonth"])) $FromSchedulemonth = $rdata["FromSchedulemonth"];
    if(isset($rdata["FromScheduleyear"])) $FromScheduleyear = $rdata["FromScheduleyear"];
    if(isset($rdata["ToScheduleDay"])) $ToScheduleDay = $rdata["ToScheduleDay"];
    if(isset($rdata["ToScheduleMonth"])) $ToScheduleMonth = $rdata["ToScheduleMonth"];
    if(isset($rdata["ToScheduleYear"])) $ToScheduleYear = $rdata["ToScheduleYear"];
    if(isset($rdata["start_time"])) $start_time = $rdata["start_time"];
    if(isset($rdata["end_time"])) $end_time = $rdata["end_time"];
    if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
    if(isset($rdata["create_time"])) $create_time = $rdata["create_time"];
    if(isset($rdata["create_by"])) $create_by = $rdata["create_by"];
    if(isset($rdata["ScheduleName"])) $ScheduleName = $rdata["ScheduleName"];
    if(isset($rdata["Schedulephone_number"])) $Schedulephone_number = $rdata["Schedulephone_number"];
    if(isset($rdata["description"])) $description = $rdata["description"];
    
    // user database table fields
    if(isset($rdata["first_name"])) $first_name = $rdata["first_name"];
    if(isset($rdata["middle_name"])) $middle_name = $rdata["middle_name"];
    if(isset($rdata["last_name"])) $last_name = $rdata["last_name"];
    if(isset($rdata["title"])) $title = $rdata["title"];
    if(isset($rdata["email"])) $email = $rdata["email"];
    if(isset($rdata["username"])) $username = $rdata["username"];
    if(isset($rdata["password"])) $password = $rdata["password"];
    if(isset($rdata["user_level"])) $user_level = $rdata["user_level"];
    if(isset($rdata["counter"])) $counter = $rdata["counter"];
    if(isset($rdata["last_login"])) $last_login = $rdata["last_login"];
    if(isset($rdata["address1"])) $address1 = $rdata["address1"];
    if(isset($rdata["city"])) $city = $rdata["city"];
    if(isset($rdata["state"])) $state = $rdata["state"];
    if(isset($rdata["zip"])) $zip = $rdata["zip"];
    if(isset($rdata["phone_number"])) $phone_number = $rdata["phone_number"];
    if(isset($rdata["SSN"])) $SSN = $rdata["SSN"];
    if(isset($rdata["Organization"])) $Organization = $rdata["Organization"];
    if(isset($rdata["Home_Phone"])) $Home_Phone = $rdata["Home_Phone"];
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
    if(isset($rdata["Member_Notes"])) $Member_Notes = $rdata["Member_Notes"];
    if(isset($rdata["Credit_Card_Number"])) $Credit_Card_Number = $rdata["Credit_Card_Number"];
    if(isset($rdata["Credit_Card_Expiration"])) $Credit_Card_Expiration = $rdata["Credit_Card_Expiration"];
    if(isset($rdata["Manager_Message"])) $Manager_Message = $rdata["Manager_Message"];
    if(isset($rdata["Membership_Date"])) $Membership_Date = $rdata["Membership_Date"];
    if(isset($rdata["Resign_Date"])) $Resign_Date = $rdata["Resign_Date"];
    if(isset($rdata["Clearing_Authority"])) $Clearing_Authority = $rdata["Clearing_Authority"];
    if(isset($rdata["Password_Expires_Date"])) $Password_Expires_Date = $rdata["Password_Expires_Date"];
    if(isset($rdata["Birth_Date"])) $Birth_Date = $rdata["Birth_Date"];

    // aircraft data fields
    if(isset($rdata["n_number"])) $n_number = $rdata["n_number"];
    if(isset($rdata["serial_number"])) $serial_number = $rdata["serial_number"];
    if(isset($rdata["hobbs"])) $hobbs = $rdata["hobbs"];
    if(isset($rdata["tach1"])) $tach1 = $rdata["tach1"];
    if(isset($rdata["hourly_cost"])) $hourly_cost = $rdata["hourly_cost"];
    if(isset($rdata["rental_fee"])) $rental_fee = $rdata["rental_fee"];
    if(isset($rdata["empty_weight"])) $empty_weight = $rdata["empty_weight"];
    if(isset($rdata["max_gross"])) $max_gross = $rdata["max_gross"];
    if(isset($rdata["AircraftYear"])) $AircraftYear = $rdata["AircraftYear"];
    if(isset($rdata["code_id"])) $code_id = $rdata["code_id"];
    if(isset($rdata["make_id"])) $make_id = $rdata["make_id"];
    if(isset($rdata["model_id"])) $model_id = $rdata["model_id"];
    if(isset($rdata["ifr_cert"])) $ifr_cert = $rdata["ifr_cert"];
    if(isset($rdata["status"])) $status = $rdata["status"];
    if(isset($rdata["Aircraft_Color"])) $Aircraft_Color = $rdata["Aircraft_Color"];
    if(isset($rdata["Hrs_Till_100_Hr"])) $Hrs_Till_100_Hr = $rdata["Hrs_Till_100_Hr"];
    if(isset($rdata["Hundred_Hr_Tach"])) $Hundred_Hr_Tach = $rdata["Hundred_Hr_Tach"];
    if(isset($rdata["Annual_Due"])) $Annual_Due = $rdata["Annual_Due"];
    if(isset($rdata["Default_Fuel_Gallons"])) $Default_Fuel_Gallons = $rdata["Default_Fuel_Gallons"];
    if(isset($rdata["Full_Fuel_Gallons"])) $Full_Fuel_Gallons = $rdata["Full_Fuel_Gallons"];
    if(isset($rdata["Va_Max_Weight"])) $Va_Max_Weight = $rdata["Va_Max_Weight"];
    if(isset($rdata["Current_Hobbs"])) $Current_Hobbs = $rdata["Current_Hobbs"];
    if(isset($rdata["Current_User"])) $Current_User = $rdata["Current_User"];
    if(isset($rdata["CurrentKeycode"])) $CurrentKeycode = $rdata["CurrentKeycode"];
    if(isset($rdata["Aircraft_Owner_Name"])) $Aircraft_Owner_Name = $rdata["Aircraft_Owner_Name"];
    if(isset($rdata["Aircraft_Owner_Address"])) $Aircraft_Owner_Address = $rdata["Aircraft_Owner_Address"];
    if(isset($rdata["Aircraft_Owner_City"])) $Aircraft_Owner_City = $rdata["Aircraft_Owner_City"];
    if(isset($rdata["Aircraft_Owner_State"])) $Aircraft_Owner_State = $rdata["Aircraft_Owner_State"];
    if(isset($rdata["Aircraft_Owner_Zip"])) $Aircraft_Owner_Zip = $rdata["Aircraft_Owner_Zip"];
    if(isset($rdata["Aircraft_Owner_Contract"])) $Aircraft_Owner_Contract = $rdata["Aircraft_Owner_Contract"];
    if(isset($rdata["Aircraft_Owner_Phone1"])) $Aircraft_Owner_Phone1 = $rdata["Aircraft_Owner_Phone1"];
    if(isset($rdata["Aircraft_Owner_Phone2"])) $Aircraft_Owner_Phone2 = $rdata["Aircraft_Owner_Phone2"];
    if(isset($rdata["Aircraft_Remarks"])) $Aircraft_Remarks = $rdata["Aircraft_Remarks"];
    if(isset($rdata["Aircraft_Airspeed"])) $Aircraft_Airspeed = $rdata["Aircraft_Airspeed"];
    if(isset($rdata["Flight_ID"])) $Flight_ID = $rdata["Flight_ID"];
    if(isset($rdata["Oil_Type"])) $Oil_Type = $rdata["Oil_Type"];

    // flight data fields   
    if(isset($rdata["FlightSortSelection"])) $FlightSortSelection = $rdata["FlightSortSelection"];
    if(isset($rdata["FromFlightday"])) $FromFlightday = $rdata["FromFlightday"];
    if(isset($rdata["FromFlightmonth"])) $FromFlightmonth = $rdata["FromFlightmonth"];
    if(isset($rdata["FromFlightyear"])) $FromFlightyear = $rdata["FromFlightyear"];
    if(isset($rdata["ToFlightDay"])) $ToFlightDay = $rdata["ToFlightDay"];
    if(isset($rdata["ToFlightMonth"])) $ToFlightMonth = $rdata["ToFlightMonth"];
    if(isset($rdata["ToFlightYear"])) $ToFlightYear = $rdata["ToFlightYear"];
    if(isset($rdata["FlightKeycode"])) $FlightKeycode = $rdata["FlightKeycode"];
    if(isset($rdata["FlightDate"])) $FlightDate = $rdata["FlightDate"];
    if(isset($rdata["FlightAircraft"])) $FlightAircraft = $rdata["FlightAircraft"];
    if(isset($rdata["FlightModelId"])) $FlightModelId = $rdata["FlightModelId"];
    if(isset($rdata["Begin_Hobbs"])) $Begin_Hobbs = $rdata["Begin_Hobbs"];
    if(isset($rdata["End_Hobbs"])) $End_Hobbs = $rdata["End_Hobbs"];
    if(isset($rdata["Hobbs_Elapsed"])) $Hobbs_Elapsed = $rdata["Hobbs_Elapsed"];
    if(isset($rdata["Aircraft_Rate"])) $Aircraft_Rate = $rdata["Aircraft_Rate"];
    if(isset($rdata["Aircraft_Cost"])) $Aircraft_Cost = $rdata["Aircraft_Cost"];
    if(isset($rdata["Begin_Tach"])) $Begin_Tach = $rdata["Begin_Tach"];
    if(isset($rdata["End_Tach"])) $End_Tach = $rdata["End_Tach"];
    if(isset($rdata["Day_Time"])) $Day_Time = $rdata["Day_Time"];
    if(isset($rdata["Night_Time"])) $Night_Time = $rdata["Night_Time"];
    if(isset($rdata["Instruction_Type"])) $Instruction_Type = $rdata["Instruction_Type"];
    if(isset($rdata["Dual_Time"])) $Dual_Time = $rdata["Dual_Time"];
    if(isset($rdata["Dual_PP_Time"])) $Dual_PP_Time = $rdata["Dual_PP_Time"];
    if(isset($rdata["Instruction_Rate"])) $Instruction_Rate = $rdata["Instruction_Rate"];
    if(isset($rdata["Instructor_Charge"])) $Instructor_Charge = $rdata["Instructor_Charge"];
    if(isset($rdata["Instructor_Keycode"])) $Instructor_Keycode = $rdata["Instructor_Keycode"];
    if(isset($rdata["Student_Keycode"])) $Student_Keycode = $rdata["Student_Keycode"];
    if(isset($rdata["Day_Landings"])) $Day_Landings = $rdata["Day_Landings"];
    if(isset($rdata["Night_Landings"])) $Night_Landings = $rdata["Night_Landings"];
    if(isset($rdata["Navigation_Intercepts"])) $Navigation_Intercepts = $rdata["Navigation_Intercepts"];
    if(isset($rdata["Holding_Procedures"])) $Holding_Procedures = $rdata["Holding_Procedures"];
    if(isset($rdata["Instrument_Approach"])) $Instrument_Approach = $rdata["Instrument_Approach"];
    if(isset($rdata["Fuel_Cost"])) $Fuel_Cost = $rdata["Fuel_Cost"];
    if(isset($rdata["Local_Fuel"])) $Local_Fuel = $rdata["Local_Fuel"];
    if(isset($rdata["Local_Fuel_Cost"])) $Local_Fuel_Cost = $rdata["Local_Fuel_Cost"];
    if(isset($rdata["Cross_Country_Fuel"])) $Cross_Country_Fuel = $rdata["Cross_Country_Fuel"];
    if(isset($rdata["Cross_Country_Fuel_Credit"])) $Cross_Country_Fuel_Credit = $rdata["Cross_Country_Fuel_Credit"];
    if(isset($rdata["Oil"])) $Oil = $rdata["Oil"];
    if(isset($rdata["Oil_Rate"])) $Oil_Rate = $rdata["Oil_Rate"];
    if(isset($rdata["Oil_Cost"])) $Oil_Cost = $rdata["Oil_Cost"];
    if(isset($rdata["Owner_Rate"])) $Owner_Rate = $rdata["Owner_Rate"];
    if(isset($rdata["Owner_Reimbursement"])) $Owner_Reimbursement = $rdata["Owner_Reimbursement"];
    if(isset($rdata["Cleared_By"])) $Cleared_By = $rdata["Cleared_By"];
    
    // charge data fields
    if(isset($rdata["ChargeSortSelection"])) $ChargeSortSelection = $rdata["ChargeSortSelection"];
    if(isset($rdata["FromChargeday"])) $FromChargeday = $rdata["FromChargeday"];
    if(isset($rdata["FromChargemonth"])) $FromChargemonth = $rdata["FromChargemonth"];
    if(isset($rdata["FromChargeyear"])) $FromChargeyear = $rdata["FromChargeyear"];
    if(isset($rdata["ToChargeDay"])) $ToChargeDay = $rdata["ToChargeDay"];
    if(isset($rdata["ToChargeMonth"])) $ToChargeMonth = $rdata["ToChargeMonth"];
    if(isset($rdata["ToChargeYear"])) $ToChargeYear = $rdata["ToChargeYear"];
    if(isset($rdata["ChargeKeyCode"])) $ChargeKeyCode = $rdata["ChargeKeyCode"];
    if(isset($rdata["ChargeDate"])) $ChargeDate = $rdata["ChargeDate"];
    if(isset($rdata["Part_Number"])) $Part_Number = $rdata["Part_Number"];
    if(isset($rdata["Part_Description"])) $Part_Description = $rdata["Part_Description"];
    if(isset($rdata["Quantity"])) $Quantity = $rdata["Quantity"];
    if(isset($rdata["Price"])) $Price = $rdata["Price"];
    if(isset($rdata["Total_Price"])) $Total_Price = $rdata["Total_Price"];
    if(isset($rdata["ChargeCategory"])) $ChargeCategory = $rdata["ChargeCategory"];
    if(isset($rdata["Unit_Price"])) $Unit_Price = $rdata["Unit_Price"];
    
    // inventory data fields
    if(isset($rdata["InventorySortSelection"])) $InventorySortSelection = $rdata["InventorySortSelection"];
    if(isset($rdata["FromInventoryday"])) $FromInventoryday = $rdata["FromInventoryday"];
    if(isset($rdata["FromInventorymonth"])) $FromInventorymonth = $rdata["FromInventorymonth"];
    if(isset($rdata["FromInventoryyear"])) $FromInventoryyear = $rdata["FromInventoryyear"];
    if(isset($rdata["ToInventoryDay"])) $ToInventoryDay = $rdata["ToInventoryDay"];
    if(isset($rdata["ToInventoryMonth"])) $ToInventoryMonth = $rdata["ToInventoryMonth"];
    if(isset($rdata["ToInventoryYear"])) $ToInventoryYear = $rdata["ToInventoryYear"];        
    if(isset($rdata["InventoryDate"])) $InventoryDate = $rdata["InventoryDate"];
    if(isset($rdata["InventoryPart_Number"])) $InventoryPart_Number = $rdata["InventoryPart_Number"];
    if(isset($rdata["InventoryDescription"])) $InventoryDescription = $rdata["InventoryDescription"];
    if(isset($rdata["InventoryUnit_Price"])) $InventoryUnit_Price = $rdata["InventoryUnit_Price"];
    if(isset($rdata["InventoryRetail_Price"])) $InventoryRetail_Price = $rdata["InventoryRetail_Price"];
    if(isset($rdata["InventoryQuantity_In_Stock"])) $InventoryQuantity_In_Stock = $rdata["InventoryQuantity_In_Stock"];
    if(isset($rdata["InventoryReorder_Quantity"])) $InventoryReorder_Quantity = $rdata["InventoryReorder_Quantity"];
    if(isset($rdata["InventoryPosition"])) $InventoryPosition = $rdata["InventoryPosition"];
    if(isset($rdata["InventoryCategory"])) $InventoryCategory = $rdata["InventoryCategory"];
    if(isset($rdata["Inventory_Type"])) $Inventory_Type = $rdata["Inventory_Type"];
        
    // #################### DEFINITION OF LOCAL FUNCTIONS ######################################
    
    //********************************************************************
    // SetScheduleDefaults()
    //
    // Purpose:  Set the defaults for the schedule database export.
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
    function SetScheduleDefaults()
    {   
        global $start_time;
        global $end_time;
        global $resource_id;
        global $create_time;
        global $create_by;
        global $ScheduleName;
        global $Schedulephone_number;
        global $description;

        $start_time = 1;
        $end_time = 1;
        $resource_id = 1;
        $create_time = 1;
        $create_by = 1;
        $ScheduleName = 1;
        $Schedulephone_number = 1;
        $description = 1;
    }
    
    //********************************************************************
    // SetUserDefaults()
    //
    // Purpose:  Set the defaults for the user database export.
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
    function SetUserDefaults()
    {   
        global $first_name;
        global $middle_name;
        global $last_name;
        global $title;
        global $email;
        global $username;
        global $password;
        global $user_level;
        global $counter;
        global $last_login;
        global $address1;
        global $city;
        global $state;
        global $zip;
        global $phone_number;
        global $SSN;
        global $Organization;
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
        global $Member_Notes;
        global $Credit_Card_Number;
        global $Credit_Card_Expiration;
        global $Manager_Message;
        global $Membership_Date;
        global $Resign_Date;
        global $Clearing_Authority;
        global $Password_Expires_Date;
        global $Birth_Date;
        
        // set the user information defaults
        $first_name = 1;
        $middle_name = 1;
        $last_name = 1;
        $title = 0;
        $email = 0;
        $username = 0;
        $password = 0;
        $user_level = 0;
        $counter = 0;
        $last_login = 0;
        $address1 = 0;
        $city = 0;
        $state = 0;
        $zip = 0;
        $phone_number = 0;
        $SSN = 0;
        $Organization = 0;
        $Home_Phone = 0;
        $Dues_Amount = 0;
        $Member_Ground_Instruction_Amount = 0;
        $Member_Private_Instruction_Amount = 0;
        $Member_Instrument_Instruction_Amount = 0;
        $Member_Commercial_Instruction_Amount = 0;
        $Member_CFI_Instruction_Amount = 0;
        $Member_CFII_Instruction_Amount = 0;
        $Contract_Number = 0;
        $Notify_First_Name = 0;
        $Notify_Middle_Initial = 0;
        $Notify_Last_Name = 0;
        $Notify_Relation = 0;
        $Notify_Address = 0;
        $Notify_City = 0;
        $Notify_State = 0;
        $Notify_Zip = 0;
        $Notify_Phone1 = 0;
        $Notify_Phone2 = 0;
        $Contract_Expiration_Date = 0;
        $Member_Notes = 0;
        $Credit_Card_Number = 0;
        $Credit_Card_Expiration = 0;
        $Manager_Message = 0;
        $Membership_Date = 0;
        $Resign_Date = 0;
        $Clearing_Authority = 0;
        $Password_Expires_Date = 0;
        $Birth_Date = 0;
    }
    
    //********************************************************************
    // SetAircraftDefaults()
    //
    // Purpose:  Set the defaults for the aircraft database export.
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
    function SetAircraftDefaults()
    {
        global $n_number;
        global $serial_number;
        global $hobbs;
        global $tach1;
        global $hourly_cost;
        global $rental_fee;
        global $empty_weight;
        global $max_gross;
        global $AircraftYear;
        global $code_id;
        global $make_id;
        global $model_id;
        global $ifr_cert;
        global $status;
        global $Aircraft_Color;
        global $Hrs_Till_100_Hr;
        global $Hundred_Hr_Tach;
        global $Annual_Due;
        global $Default_Fuel_Gallons;
        global $Full_Fuel_Gallons;
        global $Va_Max_Weight;
        global $Current_Hobbs;
        global $Current_User;
        global $CurrentKeycode;
        global $Aircraft_Owner_Name;
        global $Aircraft_Owner_Address;
        global $Aircraft_Owner_City;
        global $Aircraft_Owner_State;
        global $Aircraft_Owner_Zip;
        global $Aircraft_Owner_Contract;
        global $Aircraft_Owner_Phone1;
        global $Aircraft_Owner_Phone2;
        global $Aircraft_Remarks;
        global $Aircraft_Airspeed;
        global $Flight_ID;
        global $Oil_Type;

        $n_number = 1;
        $serial_number = 0;
        $hobbs = 1;
        $tach1 = 1;
        $hourly_cost = 0;
        $rental_fee = 0;
        $empty_weight = 0;
        $max_gross = 0;
        $AircraftYear = 0;
        $code_id = 0;
        $make_id = 1;
        $model_id = 1;
        $ifr_cert = 0;
        $status = 1;
        $Aircraft_Color = 0;
        $Hrs_Till_100_Hr = 1;
        $Hundred_Hr_Tach = 1;
        $Annual_Due = 1;
        $Default_Fuel_Gallons = 0;
        $Full_Fuel_Gallons = 0;
        $Va_Max_Weight = 0;
        $Current_Hobbs = 0;
        $Current_User = 0;
        $CurrentKeycode = 0;
        $Aircraft_Owner_Name = 0;
        $Aircraft_Owner_Address = 0;
        $Aircraft_Owner_City = 0;
        $Aircraft_Owner_State = 0;
        $Aircraft_Owner_Zip = 0;
        $Aircraft_Owner_Contract = 0;
        $Aircraft_Owner_Phone1 = 0;
        $Aircraft_Owner_Phone2 = 0;
        $Aircraft_Remarks = 0;
        $Aircraft_Airspeed = 0;
        $Flight_ID = 0;
        $Oil_Type = 0;
    }
    
    //********************************************************************
    // SetAircraftDefaults()
    //
    // Purpose:  Set the defaults for the aircraft database export.
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
    function SetFlightDefaults()
    {
        global $FlightKeycode;
        global $FlightDate;
        global $FlightAircraft;
        global $FlightModelId;
        global $Begin_Hobbs;
        global $End_Hobbs;
        global $Hobbs_Elapsed;
        global $Aircraft_Rate;
        global $Aircraft_Cost;
        global $Begin_Tach;
        global $End_Tach;
        global $Day_Time;
        global $Night_Time;
        global $Instruction_Type;
        global $Dual_Time;
        global $Dual_PP_Time;
        global $Instruction_Rate;
        global $Instructor_Charge;
        global $Instructor_Keycode;
        global $Student_Keycode;
        global $Day_Landings;
        global $Night_Landings;
        global $Navigation_Intercepts;
        global $Holding_Procedures;
        global $Instrument_Approach;
        global $Fuel_Cost;
        global $Local_Fuel;
        global $Local_Fuel_Cost;
        global $Cross_Country_Fuel;
        global $Cross_Country_Fuel_Credit;
        global $Oil;
        global $Oil_Rate;
        global $Oil_Cost;
        global $Owner_Rate;
        global $Owner_Reimbursement;
        global $Cleared_By;

        $FlightKeycode = 1;
        $FlightDate = 1;
        $FlightAircraft = 1;
        $FlightModelId = 1;
        $Begin_Hobbs = 1;
        $End_Hobbs = 1;
        $Hobbs_Elapsed = 1;
        $Aircraft_Rate = 0;
        $Aircraft_Cost = 0;
        $Begin_Tach = 1;
        $End_Tach = 1;
        $Day_Time = 1;
        $Night_Time = 1;
        $Instruction_Type = 0;
        $Dual_Time = 1;
        $Dual_PP_Time = 1;
        $Instruction_Rate = 0;
        $Instructor_Charge = 0;
        $Instructor_Keycode = 0;
        $Student_Keycode = 0;
        $Day_Landings = 1;
        $Night_Landings = 1;
        $Navigation_Intercepts = 1;
        $Holding_Procedures = 1;
        $Instrument_Approach = 1;
        $Fuel_Cost = 0;
        $Local_Fuel = 0;
        $Local_Fuel_Cost = 0;
        $Cross_Country_Fuel = 0;
        $Cross_Country_Fuel_Credit = 0;
        $Oil = 0;
        $Oil_Rate = 0;
        $Oil_Cost = 0;
        $Owner_Rate = 0;
        $Owner_Reimbursement = 0;
        $Cleared_By = 0;
    }
    
    //********************************************************************
    // SetChargeDefaults()
    //
    // Purpose:  Set the defaults for the charge database export.
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
    function SetChargeDefaults()
    {
        global $ChargeKeyCode;
        global $ChargeDate;
        global $Part_Number;
        global $Part_Description;
        global $Quantity;
        global $Price;
        global $Total_Price;
        global $ChargeCategory;
        global $Unit_Price;

        $ChargeKeyCode = 1;
        $ChargeDate = 1;
        $Part_Number = 1;
        $Part_Description = 1;
        $Quantity = 1;
        $Price = 1;
        $Total_Price = 1;
        $ChargeCategory = 1;
        $Unit_Price = 1;
    }
    
    //********************************************************************
    // SetInventoryDefaults()
    //
    // Purpose:  Set the defaults for the Inventory database export.
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
    function SetInventoryDefaults()
    {
        global $InventoryDate;
        global $InventoryPart_Number;
        global $InventoryDescription;
        global $InventoryUnit_Price;
        global $InventoryRetail_Price;
        global $InventoryQuantity_In_Stock;
        global $InventoryReorder_Quantity;
        global $InventoryPosition;
        global $InventoryCategory;
        global $Inventory_Type;

        $InventoryDate = 1;
        $InventoryPart_Number = 1;
        $InventoryDescription = 1;
        $InventoryUnit_Price = 1;
        $InventoryRetail_Price = 1;
        $InventoryQuantity_In_Stock = 1;
        $InventoryReorder_Quantity = 1;
        $InventoryPosition = 1;
        $InventoryCategory = 1;
        $Inventory_Type = 1;
    }
    
    //********************************************************************
    // SetDefaults()
    //
    // Purpose:  Set the defaults for the database export.
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
    function SetDefaults()
    {
        // set the schedule defaults
        SetScheduleDefaults();
        
        // set the user defaults
        SetUserDefaults();
        
        // set the aircraft defaults
        SetAircraftDefaults();
        
        // set the flight defaults
        SetFlightDefaults();
        
        // set the charge defaults
        SetChargeDefaults();
        
        // set the inventory defaults
        SetInventoryDefaults();
    }
    
    //********************************************************************
    // BuildScheduleExportTable()
    //
    // Purpose:  Build the schedule export table to allow the selection of the
    //           values to export.
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
    function BuildScheduleExportTable()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $lang;
        
        global $FromScheduleday;
        global $FromSchedulemonth;
        global $FromScheduleyear;
        global $ToScheduleDay;
        global $ToScheduleMonth;
        global $ToScheduleYear;
              
        global $start_time;
        global $end_time;
        global $resource_id;
        global $create_time;
        global $create_by;
        global $ScheduleName;
        global $Schedulephone_number;
        global $description;

        // start the table to display the export information
        echo "<center>";
        
        // draw a border around user information
        echo "<table border=1>";
        echo "<th>Export Schedule Information</th>";
        echo "<tr><td>";

        // selection information
        echo "<table border=0>";

        // Schedule dates for export
        echo "<tr>";
        echo "<td class=CL>";
        echo "Dates from ";
        echo "</td>";
        echo "<td class=CL>";
        genDateSelector("FromSchedule", "main", $FromScheduleday, $FromSchedulemonth, $FromScheduleyear, "", "");
        echo "&nbsp;to&nbsp;";
        genDateSelector("ToSchedule", "main", $ToScheduleDay, $ToScheduleMonth, $ToScheduleYear, "", "");
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
 
        // start the table to display the user export field selection
        echo "<table border=1>";
        
        // title
        echo "<tr>";
        echo "<td class=CC colspan=6>";
        echo "<center><b>Schedule Fields to Export</b></center>";
        echo "</td>";
		echo "</tr>";

		echo "<td class=CL>";
		echo "<input type=checkbox name=ScheduleName value=1 ";
		if ($ScheduleName == 1) echo "checked";
		echo ">Name";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=start_time value=1 ";
		if ($start_time == 1) echo "checked";
		echo ">Start Time";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=end_time value=1 ";
		if ($end_time == 1) echo "checked";
		echo ">End Time";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=resource_id value=1 ";
		if ($resource_id == 1) echo "checked";
		echo ">Resource ";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=create_time value=1 ";
		if ($create_time == 1) echo "checked";
		echo ">Creation Time";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=create_by value=1 ";
		if ($create_by == 1) echo "checked";
		echo ">Created By";
		echo "</td>";
		echo "</tr>";

        echo "<tr>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=Schedulephone_number value=1 ";
		if ($Schedulephone_number == 1) echo "checked";
		echo ">Phone Number";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=description value=1 ";
		if ($description == 1) echo "checked";
		echo ">Description";
		echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
		echo "</tr>";
        
        // give them buttons to set and clear all checkboxes
        echo "<tr>";
        echo "<td class=CR colspan=6>";
        echo "<input name='SetSchedule' type='button' value='Set All' onclick='SetAllSchedule(this.form, 1)'>";
        echo "&nbsp;";
        echo "<input name='ClearSchedule' type='button' value='Clear All' onclick='SetAllSchedule(this.form, 0)'>";
        echo "</td>";
        echo "</tr>";

        // finished with the table
        echo "</table>";
        
        // finish the border
        echo "</td></tr></table>";
    }
        
    //********************************************************************
    // BuildUserExportTable()
    //
    // Purpose:  Build the user export table to allow the selection of the
    //           values to export.
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
    function BuildUserExportTable()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $lang;
        global $AllowAircraftCheckout;

        global $UserSortSelection;
        global $Rating;
        global $Member_Status;
        global $MembersFromNameOfUser;
        global $MembersToNameOfUser;
        
        global $first_name;
        global $middle_name;
        global $last_name;
        global $title;
        global $email;
        global $username;
        global $password;
        global $user_level;
        global $counter;
        global $last_login;
        global $address1;
        global $city;
        global $state;
        global $zip;
        global $phone_number;
        global $SSN;
        global $Organization;
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
        global $Member_Notes;
        global $Credit_Card_Number;
        global $Credit_Card_Expiration;
        global $Manager_Message;
        global $Membership_Date;
        global $Resign_Date;
        global $Clearing_Authority;
        global $Password_Expires_Date;
        global $Birth_Date;
		global $auth;

        // start the table to display the export information
        echo "<center>";
        
        // draw a border around user information
        echo "<table border=1>";
        echo "<th>Export User Information</th>";
        echo "<tr><td>";
        
        // selection information
        echo "<table border=0>";
    
        // if the user is a normal user, put in the user name for the currency fields
        if (authGetUserLevel(getUserName(), $auth["admin"]) > $UserLevelNormal)
        {
            // admin or super user
        
            // Sort Exported Data By: 
            echo "<tr>";
            echo "<td class=CC colspan=2>Sort Users by:&nbsp;";
            BuildMemberSortSelector($UserSortSelection);
            echo "</td>";
            echo "</tr>";
                
        	// if the aircraft checkout functions are enabled, add additional fields
        	if ($AllowAircraftCheckout)
        	{
                // pilot status for export
                echo "<tr>";
                echo "<td class=CL>";
                echo "With pilot status:";
                echo "</td>";
                echo "<td class=CL>";
                BuildPilotIdentificationSelector($Rating, false, true);
                echo "</td>";
                echo "</tr>";
             
                // user status for export
                echo "<tr>";
                echo "<td class=CL>";
                echo "With user status:";
                echo "</td>";
                echo "<td class=CL>";
                BuildMemberStatusSelector($Member_Status, true);
                echo "</td>";
                echo "</tr>";
            }
            else
            {
                // checkout functions are not enabled, set the not used fields
                echo "<tr>";
                echo "<td class=CL>";
                echo "<input name='Rating' type='hidden' value='ALL'>";
                echo "&nbsp;";
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td class=CL>";
                echo "<input name='Member_Status' type='hidden' value='ALL'>";
                echo "&nbsp;";
                echo "</td>";
                echo "</tr>";
            }
        }
    
        // finished with the table
        echo "</table>";

        // start the table to display the user export field selection
        echo "<table border=1>";
        
        // title
        echo "<tr>";
        echo "<td class=CC colspan=4>";
        echo "<center><b>User Fields to Export</b></center>";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=first_name value=1 ";
        if ($first_name == 1) echo "checked";
        echo ">First Name";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=phone_number value=1 ";
        if ($phone_number == 1) echo "checked";
        echo ">Phone Number";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_First_Name value=1 ";
        if ($Notify_First_Name == 1) echo "checked";
        echo ">Notify First Name";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Member_Notes value=1 ";
        if ($Member_Notes == 1) echo "checked";
        echo ">Member Notes";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=middle_name value=1 ";
        if ($middle_name == 1) echo "checked";
        echo ">Middle Name";
        echo "</td>";
        
        // only office or admin
        if (authGetUserLevel(getUserName(), $auth["admin"]) > $UserLevelSuper)
        {
            echo "<td class=CL>";
            echo "<input type=checkbox name=SSN value=1 ";
            if ($SSN == 1) echo "checked";
            echo ">SSN";
            echo "</td>";
        }
        else
        {
            echo "<td class=CL>";
            echo "&nbsp;";
            echo "</td>";
        }
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_Middle_Initial value=1 ";
        if ($Notify_Middle_Initial == 1) echo "checked";
        echo ">Notify Middle Initial";
        echo "</td>";
        
        // only office or admin
        if (authGetUserLevel(getUserName(), $auth["admin"]) > $UserLevelSuper)
        {
            echo "<td class=CL>";
            echo "<input type=checkbox name=Credit_Card_Number value=1 ";
            if ($Credit_Card_Number == 1) echo "checked";
            echo ">Credit Card Number";
            echo "</td>";
        }
        else
        {
            echo "<td class=CL>";
            echo "&nbsp;";
            echo "</td>";
        }
    
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=last_name value=1 ";
        if ($last_name == 1) echo "checked";
        echo ">Last Name";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Organization value=1 ";
        if ($Organization == 1) echo "checked";
        echo ">Organization";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_Last_Name value=1 ";
        if ($Notify_Last_Name == 1) echo "checked";
        echo ">Notify Last Name";
        echo "</td>";
        
        // only office or admin
        if (authGetUserLevel(getUserName(), $auth["admin"]) > $UserLevelSuper)
        {
            echo "<td class=CL>";
            echo "<input type=checkbox name=Credit_Card_Expiration value=1 ";
            if ($Credit_Card_Expiration == 1) echo "checked";
            echo ">Credit Card Expiration";
            echo "</td>";
        }
        else
        {
            echo "<td class=CL>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=email value=1 ";
        if ($email == 1) echo "checked";
        echo ">EMail";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Home_Phone value=1 ";
        if ($Home_Phone == 1) echo "checked";
        echo ">Home Phone";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_Relation value=1 ";
        if ($Notify_Relation == 1) echo "checked";
        echo ">Notify Relation";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Manager_Message value=1 ";
        if ($Manager_Message == 1) echo "checked";
        echo ">Manager Message";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=username value=1 ";
        if ($username == 1) echo "checked";
        echo ">Keycode";
        echo "</td>";
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Dues_Amount value=1 ";
            if ($Dues_Amount == 1) echo "checked";
            echo ">Dues Amount";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Dues_Amount' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_Address value=1 ";
        if ($Notify_Address == 1) echo "checked";
        echo ">Notify_Address";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Membership_Date value=1 ";
        if ($Membership_Date == 1) echo "checked";
        echo ">Membership Date";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        
        // only office or admin can get the social security number
        if (authGetUserLevel(getUserName(), $auth["admin"]) > $UserLevelSuper)
        {
            echo "<td class=CL>";
            echo "<input type=checkbox name=password value=1 ";
            if ($password == 1) echo "checked";
            echo ">Password";
            echo "</td>";
        }
        else
        {
            echo "<td class=CL>";
            echo "&nbsp;";
            echo "</td>";
        }
    
            
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Member_Ground_Instruction_Amount value=1 ";
            if ($Member_Ground_Instruction_Amount == 1) echo "checked";
            echo ">Ground Instruction Amount";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Member_Ground_Instruction_Amount' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_City value=1 ";
        if ($Notify_City == 1) echo "checked";
        echo ">Notify City";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Resign_Date value=1 ";
        if ($Resign_Date == 1) echo "checked";
        echo ">Resign Date";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=user_level value=1 ";
        if ($user_level == 1) echo "checked";
        echo ">User Level";
        echo "</td>";
        
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Member_Private_Instruction_Amount value=1 ";
            if ($Member_Private_Instruction_Amount == 1) echo "checked";
            echo ">Private Instruction Amount";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Member_Private_Instruction_Amount' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_State value=1 ";
        if ($Notify_State == 1) echo "checked";
        echo ">Notify State";
        echo "</td>";
        
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
           echo "<td class=CL>";
            echo "<input type=checkbox name=Clearing_Authority value=1 ";
            if ($Clearing_Authority == 1) echo "checked";
            echo ">Clearing Authority";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Clearing_Authority' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=counter value=1 ";
        if ($counter == 1) echo "checked";
        echo ">Login Counter";
        echo "</td>";
        
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Member_Instrument_Instruction_Amount value=1 ";
            if ($Member_Instrument_Instruction_Amount == 1) echo "checked";
            echo ">Instrument Instruction Amount";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Member_Instrument_Instruction_Amount' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_Zip value=1 ";
        if ($Notify_Zip == 1) echo "checked";
        echo ">Notify Zip";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Password_Expires_Date value=1 ";
        if ($Password_Expires_Date == 1) echo "checked";
        echo ">Password Expires Date";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=last_login value=1 ";
        if ($last_login == 1) echo "checked";
        echo ">Last Login";
        echo "</td>";
        
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Member_Commercial_Instruction_Amount value=1 ";
            if ($Member_Commercial_Instruction_Amount == 1) echo "checked";
            echo ">Commercial Instruction Amount";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Member_Commercial_Instruction_Amount' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_Phone1 value=1 ";
        if ($Notify_Phone1 == 1) echo "checked";
        echo ">Notify Phone 1";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Birth_Date value=1 ";
        if ($Birth_Date == 1) echo "checked";
        echo ">Birth Date";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=address1 value=1 ";
        if ($address1 == 1) echo "checked";
        echo ">Address";
        echo "</td>";
        
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Member_CFI_Instruction_Amount value=1 ";
            if ($Member_CFI_Instruction_Amount == 1) echo "checked";
            echo ">CFI Instruction Amount";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Member_CFI_Instruction_Amount' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Notify_Phone2 value=1 ";
        if ($Notify_Phone2 == 1) echo "checked";
        echo ">Notify Phone 2";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=city value=1 ";
        if ($city == 1) echo "checked";
        echo ">City";
        echo "</td>";
        
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Member_CFII_Instruction_Amount value=1 ";
            if ($Member_CFII_Instruction_Amount == 1) echo "checked";
            echo ">CFII Instruction Amount";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Member_CFII_Instruction_Amount' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=state value=1 ";
        if ($state == 1) echo "checked";
        echo ">State";
        echo "</td>";
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Contract_Expiration_Date value=1 ";
            if ($Contract_Expiration_Date == 1) echo "checked";
            echo ">Contract Expiration Date";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Contract_Expiration_Date' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "</tr>";
    
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=zip value=1 ";
        if ($zip == 1) echo "checked";
        echo ">ZIP";
        echo "</td>";
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Contract_Number value=1 ";
            if ($Contract_Number == 1) echo "checked";
            echo ">Contract Number";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Contract_Number' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "</tr>";
        
        // give them buttons to set and clear all checkboxes
        echo "<tr>";
        echo "<td class=CR colspan=4>";
        if (authGetUserLevel(getUserName(), $auth["admin"]) > $UserLevelSuper)
        {
            echo "<input name='SetUsers' type='button' value='Set All' onclick='SetAllUsers(this.form, 1, " . 
                            "1)'>";
            echo "&nbsp;";
            echo "<input name='ClearUsers' type='button' value='Clear All' onclick='SetAllUsers(this.form, 0, " . 
                            "1)'>";
        }
        else
        {
            echo "<input name='SetUsers' type='button' value='Set All' onclick='SetAllUsers(this.form, 1, " . 
                            "0)'>";
            echo "&nbsp;";
            echo "<input name='ClearUsers' type='button' value='Clear All' onclick='SetAllUsers(this.form, 0, " . 
                            "0)'>";
        }
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
        
        // finish the border
        echo "</td></tr></table>";
    }
    
    //********************************************************************
    // BuildAircraftExportTable()
    //
    // Purpose:  Build the aircraft export table to allow the selection of the
    //           values to export.
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
    function BuildAircraftExportTable()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $lang;
        global $AllowAircraftCheckout;
        
        global $AircraftSortSelection;
        global $Aircraft_status;
        global $AircraftFromTailNumber;
        global $AircraftToTailNumber;
        global $AircraftRentalSelection;
        
        global $n_number;
        global $serial_number;
        global $hobbs;
        global $tach1;
        global $hourly_cost;
        global $rental_fee;
        global $empty_weight;
        global $max_gross;
        global $AircraftYear;
        global $code_id;
        global $make_id;
        global $model_id;
        global $ifr_cert;
        global $status;
        global $Aircraft_Color;
        global $Hrs_Till_100_Hr;
        global $Hundred_Hr_Tach;
        global $Annual_Due;
        global $Default_Fuel_Gallons;
        global $Full_Fuel_Gallons;
        global $Va_Max_Weight;
        global $Current_Hobbs;
        global $Current_User;
        global $CurrentKeycode;
        global $Aircraft_Owner_Name;
        global $Aircraft_Owner_Address;
        global $Aircraft_Owner_City;
        global $Aircraft_Owner_State;
        global $Aircraft_Owner_Zip;
        global $Aircraft_Owner_Contract;
        global $Aircraft_Owner_Phone1;
        global $Aircraft_Owner_Phone2;
        global $Aircraft_Remarks;
        global $Aircraft_Airspeed;
        global $Flight_ID;
        global $Oil_Type;
 
        // start the table to display the export information
        echo "<center>";
        
        // draw a border around user information
        echo "<table border=1>";
        echo "<th>Export Aircraft Information</th>";
        echo "<tr><td>";

        // selection information
        echo "<table border=0>";
            
        // sort exported data by: 
        echo "<tr>";
        echo "<td class=CC colspan=2>Sort Aircraft by:&nbsp;";
        BuildAircraftSortSelector($AircraftSortSelection);
        echo "</td>";
        echo "</tr>";

        // aircraft selection for export
        echo "<tr>";
        echo "<td class=CL>";
        echo $lang["ExportName"];
        echo "</td>";
        echo "<td class=CL>";
        BuildAircraftSelector($AircraftFromTailNumber, false, "AircraftFrom");
        echo $lang["ExportNameTo"];
        BuildAircraftSelector($AircraftToTailNumber, false, "AircraftTo");
        echo "</td>";
        echo "</tr>";
         
        // user status for export
        echo "<tr>";
        echo "<td class=CL>";
        echo "With aircraft status:";
        echo "</td>";
        echo "<td class=CL>";
        BuildStatusSelector($Aircraft_status, true, "Aircraft_");
        echo "</td>";
        echo "</tr>";
         
        // rental/non-rental
        echo "<tr>";
        echo "<td class=CL>";
        echo "Rental status:";
        echo "</td>";
        echo "<td class=CL>";
        BuildAircraftRentalSelector($AircraftRentalSelection);
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
 
        // start the table to display the user export field selection
        echo "<table border=1>";
        
        // title
        echo "<tr>";
        echo "<td class=CC colspan=4>";
        echo "<center><b>Aircraft Fields to Export</b></center>";
        echo "</td>";
        echo "</tr>";
            
        echo "<td class=CL>";
        echo "<input type=checkbox name=n_number value=1 ";
        if ($n_number == 1) echo "checked";
        echo ">Tail Number";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=serial_number value=1 ";
        if ($serial_number == 1) echo "checked";
        echo ">Serial Number";
        echo "</td>";
       
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=tach1 value=1 ";
            if ($tach1 == 1) echo "checked";
            echo ">Tach";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='tach1' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=hourly_cost value=1 ";
        if ($hourly_cost == 1) echo "checked";
        echo ">Hourly Cost";
        echo "</td>";
        echo "</tr>";

    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=rental_fee value=1 ";
            if ($rental_fee == 1) echo "checked";
            echo ">Rental Fee";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='rental_fee' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=empty_weight value=1 ";
        if ($empty_weight == 1) echo "checked";
        echo ">Empty Weight";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=max_gross value=1 ";
        if ($max_gross == 1) echo "checked";
        echo ">Max Gross";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=AircraftYear value=1 ";
        if ($AircraftYear == 1) echo "checked";
        echo ">Year";
        echo "</td>";
        echo "</tr>";

        echo "<td class=CL>";
        echo "<input type=checkbox name=code_id value=1 ";
        if ($code_id == 1) echo "checked";
        echo ">Code ID";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=make_id value=1 ";
        if ($make_id == 1) echo "checked";
        echo ">Make ID";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=model_id value=1 ";
        if ($model_id == 1) echo "checked";
        echo ">Model ID";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=ifr_cert value=1 ";
        if ($ifr_cert == 1) echo "checked";
        echo ">IFR Certified";
        echo "</td>";
        echo "</tr>";

        echo "<td class=CL>";
        echo "<input type=checkbox name=status value=1 ";
        if ($status == 1) echo "checked";
        echo ">Status";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Color value=1 ";
        if ($Aircraft_Color == 1) echo "checked";
        echo ">Aircraft Color";
        echo "</td>";
    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Hrs_Till_100_Hr value=1 ";
            if ($Hrs_Till_100_Hr == 1) echo "checked";
            echo ">Hours until 100 Hr";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input type=checkbox name=Hundred_Hr_Tach value=1 ";
            if ($Hundred_Hr_Tach == 1) echo "checked";
            echo ">Hundred Hour Tach";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input type='hidden' name=Hrs_Till_100_Hr value=0>";
            echo "&nbsp;";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input type='hidden' name=Hundred_Hr_Tach value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "</tr>";

    	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Annual_Due value=1 ";
            if ($Annual_Due == 1) echo "checked";
            echo ">Annual Due";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Annual_Due' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=Default_Fuel_Gallons value=1 ";
        if ($Default_Fuel_Gallons == 1) echo "checked";
        echo ">Default Fuel Gallons";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Full_Fuel_Gallons value=1 ";
        if ($Full_Fuel_Gallons == 1) echo "checked";
        echo ">Full Fuel Gallons";
        echo "</td>";

     	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Va_Max_Weight value=1 ";
            if ($Va_Max_Weight == 1) echo "checked";
            echo ">Va Max Weight";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Va_Max_Weight' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "</tr>";

     	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
           echo "<td class=CL>";
            echo "<input type=checkbox name=Current_Hobbs value=1 ";
            if ($Current_Hobbs == 1) echo "checked";
            echo ">Current Hobbs";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input type=checkbox name=Current_User value=1 ";
            if ($Current_User == 1) echo "checked";
            echo ">Current User";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input type=checkbox name=CurrentKeycode value=1 ";
            if ($CurrentKeycode == 1) echo "checked";
            echo ">CurrentKeycode";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Current_Hobbs' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input name='Current_User' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input name='CurrentKeycode' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_Name value=1 ";
        if ($Aircraft_Owner_Name == 1) echo "checked";
        echo ">Aircraft Owner Name";
        echo "</td>";
        echo "</tr>";

        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_Address value=1 ";
        if ($Aircraft_Owner_Address == 1) echo "checked";
        echo ">Aircraft Owner Address";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_City value=1 ";
        if ($Aircraft_Owner_City == 1) echo "checked";
        echo ">Aircraft Owner City";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_State value=1 ";
        if ($Aircraft_Owner_State == 1) echo "checked";
        echo ">Aircraft Owner State";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_Zip value=1 ";
        if ($Aircraft_Owner_Zip == 1) echo "checked";
        echo ">Aircraft Owner Zip";
        echo "</td>";
        echo "</tr>";

        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_Contract value=1 ";
        if ($Aircraft_Owner_Contract == 1) echo "checked";
        echo ">Aircraft Owner Contract";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_Phone1 value=1 ";
        if ($Aircraft_Owner_Phone1 == 1) echo "checked";
        echo ">Aircraft Owner Phone 1";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Owner_Phone2 value=1 ";
        if ($Aircraft_Owner_Phone2 == 1) echo "checked";
        echo ">Aircraft Owner Phone 2";
        echo "</td>";

     	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Aircraft_Remarks value=1 ";
            if ($Aircraft_Remarks == 1) echo "checked";
            echo ">Aircraft Remarks";
            echo "</td>";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Aircraft_Remarks' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "</tr>";

     	// if the aircraft checkout functions are enabled, add additional fields
    	if ($AllowAircraftCheckout)
    	{
            echo "<td class=CL>";
            echo "<input type=checkbox name=Aircraft_Airspeed value=1 ";
            if ($Aircraft_Airspeed == 1) echo "checked";
            echo ">Aircraft Airspeed";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input type=checkbox name=Flight_ID value=1 ";
            if ($Flight_ID == 1) echo "checked";
            echo ">Flight ID";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input type=checkbox name=Oil_Type value=1 ";
            if ($Oil_Type == 1) echo "checked";
            echo ">Oil Type";
        }
        else
        {
            // checkout functions are not enabled, set the not used fields
            echo "<td class=CL>";
            echo "<input name='Aircraft_Airspeed' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input name='Flight_ID' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
            echo "<td class=CL>";
            echo "<input name='Oil_Type' type='hidden' value=0>";
            echo "&nbsp;";
            echo "</td>";
        }
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "</tr>";
        
        // give them buttons to set and clear all checkboxes
        echo "<tr>";
        echo "<td class=CR colspan=4>";
        echo "<input name='SetAircraft' type='button' value='Set All' onclick='SetAllAircraft(this.form, 1)'>";
        echo "&nbsp;";
        echo "<input name='ClearAircraft' type='button' value='Clear All' onclick='SetAllAircraft(this.form, 0)'>";
        echo "</td>";
        echo "</tr>";

        // finished with the table
        echo "</table>";
        
        // finish the border
        echo "</td></tr></table>";
    }
    
    //********************************************************************
    // BuildFlightExportTable()
    //
    // Purpose:  Build the flight export table to allow the selection of the
    //           values to export.
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
    function BuildFlightExportTable()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $lang;
        
        global $FlightSortSelection;
        global $AircraftRentalSelection;
        global $FromFlightday;
        global $FromFlightmonth;
        global $FromFlightyear;
        global $ToFlightDay;
        global $ToFlightMonth;
        global $ToFlightYear;
        
        global $FlightKeycode;
        global $FlightDate;
        global $FlightAircraft;
        global $FlightModelId;
        global $Begin_Hobbs;
        global $End_Hobbs;
        global $Hobbs_Elapsed;
        global $Aircraft_Rate;
        global $Aircraft_Cost;
        global $Begin_Tach;
        global $End_Tach;
        global $Day_Time;
        global $Night_Time;
        global $Instruction_Type;
        global $Dual_Time;
        global $Dual_PP_Time;
        global $Instruction_Rate;
        global $Instructor_Charge;
        global $Instructor_Keycode;
        global $Student_Keycode;
        global $Day_Landings;
        global $Night_Landings;
        global $Navigation_Intercepts;
        global $Holding_Procedures;
        global $Instrument_Approach;
        global $Fuel_Cost;
        global $Local_Fuel;
        global $Local_Fuel_Cost;
        global $Cross_Country_Fuel;
        global $Cross_Country_Fuel_Credit;
        global $Oil;
        global $Oil_Rate;
        global $Oil_Cost;
        global $Owner_Rate;
        global $Owner_Reimbursement;
        global $Cleared_By;
      
        // start the table to display the export information
        echo "<center>";
        
        // draw a border around user information
        echo "<table border=1>";
        echo "<th>Export Flight Information</th>";
        echo "<tr><td>";

        // selection information
        echo "<table border=0>";
            
        // sort exported data by: 
        echo "<tr>";
        echo "<td class=CC colspan=2>Sort Flights by:&nbsp;";
        BuildFlightSortSelector($FlightSortSelection);
        echo "</td>";
        echo "</tr>";

        // flight dates for export
        echo "<tr>";
        echo "<td class=CL>";
        echo "Dates from ";
        echo "</td>";
        echo "<td class=CL>";
        genDateSelector("FromFlight", "main", $FromFlightday, $FromFlightmonth, $FromFlightyear, "", "");
        echo "&nbsp;to&nbsp;";
        genDateSelector("ToFlight", "main", $ToFlightDay, $ToFlightMonth, $ToFlightYear, "", "");
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
 
        // start the table to display the user export field selection
        echo "<table border=1>";
        
        // title
        echo "<tr>";
        echo "<td class=CC colspan=4>";
        echo "<center><b>Flight Fields to Export</b></center>";
        echo "</td>";
        echo "</tr>";

        echo "<td class=CL>";
        echo "<input type=checkbox name=FlightKeycode value=1 ";
        if ($FlightKeycode == 1) echo "checked";
        echo ">Name";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=FlightDate value=1 ";
        if ($FlightDate == 1) echo "checked";
        echo ">Date";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=FlightAircraft value=1 ";
        if ($FlightAircraft == 1) echo "checked";
        echo ">Aircraft";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=FlightModelId value=1 ";
        if ($FlightModelId == 1) echo "checked";
        echo ">Model";
        echo "</td>";
        echo "</tr>";
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Begin_Hobbs value=1 ";
        if ($Begin_Hobbs == 1) echo "checked";
        echo ">Begin Hobbs";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=End_Hobbs value=1 ";
        if ($End_Hobbs == 1) echo "checked";
        echo ">End Hobbs";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Hobbs_Elapsed value=1 ";
        if ($Hobbs_Elapsed == 1) echo "checked";
        echo ">Hobbs Elapsed";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Rate value=1 ";
        if ($Aircraft_Rate == 1) echo "checked";
        echo ">Aircraft Rate";
        echo "</td>";
        
        echo "</tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Aircraft_Cost value=1 ";
        if ($Aircraft_Cost == 1) echo "checked";
        echo ">Aircraft Cost";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Begin_Tach value=1 ";
        if ($Begin_Tach == 1) echo "checked";
        echo ">Begin Tach";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=End_Tach value=1 ";
        if ($End_Tach == 1) echo "checked";
        echo ">End Tach";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Day_Time value=1 ";
        if ($Day_Time == 1) echo "checked";
        echo ">Day Time";
        echo "</td>";
        echo "</tr>";
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Night_Time value=1 ";
        if ($Night_Time == 1) echo "checked";
        echo ">Night Time";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Instruction_Type value=1 ";
        if ($Instruction_Type == 1) echo "checked";
        echo ">Instruction Type";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Dual_Time value=1 ";
        if ($Dual_Time == 1) echo "checked";
        echo ">Dual Time";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Dual_PP_Time value=1 ";
        if ($Dual_PP_Time == 1) echo "checked";
        echo ">Dual PP Time";
        echo "</td>";
        echo "</tr>";
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Instruction_Rate value=1 ";
        if ($Instruction_Rate == 1) echo "checked";
        echo ">Instruction Rate";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Instructor_Charge value=1 ";
        if ($Instructor_Charge == 1) echo "checked";
        echo ">Instructor Charge";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Instructor_Keycode value=1 ";
        if ($Instructor_Keycode == 1) echo "checked";
        echo ">Instructor Keycode";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Student_Keycode value=1 ";
        if ($Student_Keycode == 1) echo "checked";
        echo ">Student Keycode";
        echo "</td>";
        echo "</tr>";
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Day_Landings value=1 ";
        if ($Day_Landings == 1) echo "checked";
        echo ">Day Landings";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Night_Landings value=1 ";
        if ($Night_Landings == 1) echo "checked";
        echo ">Night Landings";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Navigation_Intercepts value=1 ";
        if ($Navigation_Intercepts == 1) echo "checked";
        echo ">Navigation Intercepts";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Holding_Procedures value=1 ";
        if ($Holding_Procedures == 1) echo "checked";
        echo ">Holding Procedures";
        echo "</td>";
        echo "</tr>";
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Instrument_Approach value=1 ";
        if ($Instrument_Approach == 1) echo "checked";
        echo ">Instrument Approach";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Fuel_Cost value=1 ";
        if ($Fuel_Cost == 1) echo "checked";
        echo ">Fuel Cost";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Local_Fuel value=1 ";
        if ($Local_Fuel == 1) echo "checked";
        echo ">Local Fuel";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Local_Fuel_Cost value=1 ";
        if ($Local_Fuel_Cost == 1) echo "checked";
        echo ">Local Fuel Cost";
        echo "</td>";
        echo "</tr>";
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Cross_Country_Fuel value=1 ";
        if ($Cross_Country_Fuel == 1) echo "checked";
        echo ">Cross Country Fuel";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Cross_Country_Fuel_Credit value=1 ";
        if ($Cross_Country_Fuel_Credit == 1) echo "checked";
        echo ">Cross Country Fuel Credit";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Oil value=1 ";
        if ($Oil == 1) echo "checked";
        echo ">Oil";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Oil_Rate value=1 ";
        if ($Oil_Rate == 1) echo "checked";
        echo ">Oil Rate";
        echo "</td>";
        echo "</tr>";
        
        echo "<td class=CL>";
        echo "<input type=checkbox name=Oil_Cost value=1 ";
        if ($Oil_Cost == 1) echo "checked";
        echo ">Oil Cost";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Owner_Rate value=1 ";
        if ($Owner_Rate == 1) echo "checked";
        echo ">Owner Rate";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Owner_Reimbursement value=1 ";
        if ($Owner_Reimbursement == 1) echo "checked";
        echo ">Owner Reimbursement";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Cleared_By value=1 ";
        if ($Cleared_By == 1) echo "checked";
        echo ">Cleared By";
        echo "</td>";
        echo "</tr>";
        
        // give them buttons to set and clear all checkboxes
        echo "<tr>";
        echo "<td class=CR colspan=4>";
        echo "<input name='SetFlights' type='button' value='Set All' onclick='SetAllFlights(this.form, 1)'>";
        echo "&nbsp;";
        echo "<input name='ClearFlights' type='button' value='Clear All' onclick='SetAllFlights(this.form, 0)'>";
        echo "</td>";
        echo "</tr>";
            
        // finished with the table
        echo "</table>";
        
        // finish the border
        echo "</td></tr></table>";
    }
    
    //********************************************************************
    // BuildChargeExportTable()
    //
    // Purpose:  Build the charge export table to allow the selection of the
    //           values to export.
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
    function BuildChargeExportTable()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $lang;
        
        global $ChargeSortSelection;
        global $AircraftRentalSelection;
        global $FromChargeday;
        global $FromChargemonth;
        global $FromChargeyear;
        global $ToChargeDay;
        global $ToChargeMonth;
        global $ToChargeYear;
        
        global $ChargeKeyCode;
        global $ChargeDate;
        global $Part_Number;
        global $Part_Description;
        global $Quantity;
        global $Price;
        global $Total_Price;
        global $ChargeCategory;
        global $Unit_Price;
          
        // start the table to display the export information
        echo "<center>";
        
        // draw a border around user information
        echo "<table border=1>";
        echo "<th>Export Charge Information</th>";
        echo "<tr><td>";

        // selection information
        echo "<table border=0>";
            
        // sort exported data by: 
        echo "<tr>";
        echo "<td class=CC colspan=2>Sort Charges by:&nbsp;";
        BuildChargeSortSelector($ChargeSortSelection);
        echo "</td>";
        echo "</tr>";

        // Charge dates for export
        echo "<tr>";
        echo "<td class=CL>";
        echo "Dates from ";
        echo "</td>";
        echo "<td class=CL>";
        genDateSelector("FromCharge", "main", $FromChargeday, $FromChargemonth, $FromChargeyear, "", "");
        echo "&nbsp;to&nbsp;";
        genDateSelector("ToCharge", "main", $ToChargeDay, $ToChargeMonth, $ToChargeYear, "", "");
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
 
        // start the table to display the user export field selection
        echo "<table border=1>";
        
        // title
        echo "<tr>";
        echo "<td class=CC colspan=7>";
        echo "<center><b>Charge Fields to Export</b></center>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=ChargeKeyCode value=1 ";
        if ($ChargeKeyCode == 1) echo "checked";
        echo ">Name";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=ChargeDate value=1 ";
        if ($ChargeDate == 1) echo "checked";
        echo ">Date";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Part_Number value=1 ";
        if ($Part_Number == 1) echo "checked";
        echo ">Part Number";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Part_Description value=1 ";
        if ($Part_Description == 1) echo "checked";
        echo ">Part Description";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Quantity value=1 ";
        if ($Quantity == 1) echo "checked";
        echo ">Quantity";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Price value=1 ";
        if ($Price == 1) echo "checked";
        echo ">Price";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Total_Price value=1 ";
        if ($Total_Price == 1) echo "checked";
        echo ">Total Price";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=ChargeCategory value=1 ";
        if ($ChargeCategory == 1) echo "checked";
        echo ">Category";
        echo "</td>";
        echo "<td class=CL>";
        echo "<input type=checkbox name=Unit_Price value=1 ";
        if ($Unit_Price == 1) echo "checked";
        echo ">Unit Price";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "</tr>";
        
        // give them buttons to set and clear all checkboxes
        echo "<tr>";
        echo "<td class=CR colspan=7>";
        echo "<input name='SetCharges' type='button' value='Set All' onclick='SetAllCharges(this.form, 1)'>";
        echo "&nbsp;";
        echo "<input name='ClearCharges' type='button' value='Clear All' onclick='SetAllCharges(this.form, 0)'>";
        echo "</td>";
        echo "</tr>";
                    
        // finished with the table
        echo "</table>";
        
        // finish the border
        echo "</td></tr></table>";
    }
    
    //********************************************************************
    // BuildInventoryExportTable()
    //
    // Purpose:  Build the inventory export table to allow the selection of the
    //           values to export.
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
    function BuildInventoryExportTable()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $lang;
        
        global $InventorySortSelection;
        global $FromInventoryday;
        global $FromInventorymonth;
        global $FromInventoryyear;
        global $ToInventoryDay;
        global $ToInventoryMonth;
        global $ToInventoryYear;
        
        global $InventoryDate;
        global $InventoryPart_Number;
        global $InventoryDescription;
        global $InventoryUnit_Price;
        global $InventoryRetail_Price;
        global $InventoryQuantity_In_Stock;
        global $InventoryReorder_Quantity;
        global $InventoryPosition;
        global $InventoryCategory;
        global $Inventory_Type;
          
        // start the table to display the export information
        echo "<center>";
        
        // draw a border around user information
        echo "<table border=1>";
        echo "<th>Export Inventory Information</th>";
        echo "<tr><td>";

        // selection information
        echo "<table border=0>";
            
        // sort exported data by: 
        echo "<tr>";
        echo "<td class=CC colspan=2>Sort Inventory by:&nbsp;";
        BuildInventorySortSelector($InventorySortSelection);
        echo "</td>";
        echo "</tr>";

        // Inventory dates for export
        echo "<tr>";
        echo "<td class=CL>";
        echo "Dates from ";
        echo "</td>";
        echo "<td class=CL>";
        genDateSelector("FromInventory", "main", $FromInventoryday, $FromInventorymonth, $FromInventoryyear, "", "");
        echo "&nbsp;to&nbsp;";
        genDateSelector("ToInventory", "main", $ToInventoryDay, $ToInventoryMonth, $ToInventoryYear, "", "");
        echo "</td>";
        echo "</tr>";
    
        // finished with the table
        echo "</table>";
 
        // start the table to display the user export field selection
        echo "<table border=1>";
        
        // title
        echo "<tr>";
        echo "<td class=CC colspan=6>";
        echo "<center><b>Inventory Fields to Export</b></center>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryDate value=1 ";
		if ($InventoryDate == 1) echo "checked";
		echo ">Date";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryPart_Number value=1 ";
		if ($InventoryPart_Number == 1) echo "checked";
		echo ">Part Number";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryDescription value=1 ";
		if ($InventoryDescription == 1) echo "checked";
		echo ">Description";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryUnit_Price value=1 ";
		if ($InventoryUnit_Price == 1) echo "checked";
		echo ">Unit Price";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryRetail_Price value=1 ";
		if ($InventoryRetail_Price == 1) echo "checked";
		echo ">Retail Price";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryQuantity_In_Stock value=1 ";
		if ($InventoryQuantity_In_Stock == 1) echo "checked";
		echo ">Quantity In Stock";
		echo "</td>";
		echo "</tr>";

        echo "<tr>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryReorder_Quantity value=1 ";
		if ($InventoryReorder_Quantity == 1) echo "checked";
		echo ">Reorder Quantity";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryPosition value=1 ";
		if ($InventoryPosition == 1) echo "checked";
		echo ">Position";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=InventoryCategory value=1 ";
		if ($InventoryCategory == 1) echo "checked";
		echo ">Category";
		echo "</td>";
		echo "<td class=CL>";
		echo "<input type=checkbox name=Inventory_Type value=1 ";
		if ($Inventory_Type == 1) echo "checked";
		echo ">Inventory Type";
		echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
        echo "<td class=CL>";
        echo "&nbsp;";
        echo "</td>";
		echo "</tr>";
     
        // give them buttons to set and clear all checkboxes
        echo "<tr>";
        echo "<td class=CR colspan=7>";
        echo "<input name='SetInventory' type='button' value='Set All' onclick='SetAllInventory(this.form, 1)'>";
        echo "&nbsp;";
        echo "<input name='ClearInventory' type='button' value='Clear All' onclick='SetAllInventory(this.form, 0)'>";
        echo "</td>";
        echo "</tr>";
                    
        // finished with the table
        echo "</table>";
        
        // finish the border
        echo "</td></tr></table>";
    }
       
    //********************************************************************
    // BuildExportTables()
    //
    // Purpose:  Build the export tables to allow the selection of the
    //           values to export.
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
    function BuildExportTables()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $SelectedTable;
 		global $auth;
      
        // build the table for the schedule database information
        if (Instr(1, UCase($SelectedTable), "SCHEDULE") || UCase($SelectedTable) == "ALL") 
        {
            BuildScheduleExportTable();
            echo "<br>";
        }
 
        // build the table for the user database information
        if (Instr(1, UCase($SelectedTable), "USER") || UCase($SelectedTable) == "ALL") 
        {
            BuildUserExportTable();
            echo "<br>";
        }
        
        // if the user has permission, build the aircraft export table
        if (authGetUserLevel(getUserName(), $auth["admin"]) >= $UserLevelOffice)
        {
            if (Instr(1, UCase($SelectedTable), "AIRCRAFT") || UCase($SelectedTable) == "ALL") 
            {
                // build the table for the user database information
                BuildAircraftExportTable();
                echo "<br>";
            }
        }
 
        // build the table for the flight database information
        if (Instr(1, UCase($SelectedTable), "FLIGHT") || UCase($SelectedTable) == "ALL") 
        {
            BuildFlightExportTable();
            echo "<br>";
        }
 
        // build the table for the charge database information
        if (Instr(1, UCase($SelectedTable), "CHARGE") || UCase($SelectedTable) == "ALL") 
        {
            BuildChargeExportTable();
            echo "<br>";
        }
        
        // if the user has permission, build the inventory export table
        if (authGetUserLevel(getUserName(), $auth["admin"]) >= $UserLevelOffice)
        {
            if (Instr(1, UCase($SelectedTable), "INVENTORY") || UCase($SelectedTable) == "ALL") 
            {
                // build the table for the user database information
                BuildInventoryExportTable();
                echo "<br>";
            }
        }
    }

    //********************************************************************
    // BuildScheduleSelectionFields($ScheduleFields, $FieldNames)
    //
    // Purpose:  Build the selection field and export field names based
    //           on the user selection.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   ScheduleFields - the database fields for the select field
    //   FieldNames - the names of the fields selected
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildScheduleSelectionFields(&$ScheduleFields, &$FieldNames)
    {    
        global $FieldSeparator;
        
        global $start_time;
        global $end_time;
        global $resource_id;
        global $create_time;
        global $create_by;
        global $ScheduleName;
        global $Schedulephone_number;
        global $description;

        // build the field name and field selection according to the fields
        // that the user has selected for export
        $ScheduleFields = "";
        $FieldNames = "";
        $locFieldSeparator = $FieldSeparator . " ";     
        if ($ScheduleName == 1)
		{
			$ScheduleFields = $ScheduleFields . "name, ";
			$FieldNames = $FieldNames . "Name" . $locFieldSeparator;
		}
        if ($start_time == 1)
		{
			$ScheduleFields = $ScheduleFields . "start_time, ";
			$FieldNames = $FieldNames . "Start Time" . $locFieldSeparator;
		}
        if ($end_time == 1)
		{
			$ScheduleFields = $ScheduleFields . "end_time, ";
			$FieldNames = $FieldNames . "End Time" . $locFieldSeparator;
		}
        if ($resource_id == 1)
		{
			$ScheduleFields = $ScheduleFields . "resource_id, ";
			$FieldNames = $FieldNames . "Resource" . $locFieldSeparator;
		}
        if ($create_time == 1)
		{
			$ScheduleFields = $ScheduleFields . "create_time, ";
			$FieldNames = $FieldNames . "Creation Time" . $locFieldSeparator;
		}
        if ($create_by == 1)
		{
			$ScheduleFields = $ScheduleFields . "create_by, ";
			$FieldNames = $FieldNames . "Created By" . $locFieldSeparator;
		}
        if ($Schedulephone_number == 1)
		{
			$ScheduleFields = $ScheduleFields . "phone_number, ";
			$FieldNames = $FieldNames . "Phone Number" . $locFieldSeparator;
		}
        if ($description == 1)
		{
			$ScheduleFields = $ScheduleFields . "description, ";
			$FieldNames = $FieldNames . "Description" . $locFieldSeparator;
		}
        
        // remove the last field separator from the field name
        if (Right($FieldNames, Len($locFieldSeparator)) == $locFieldSeparator)
        {
            $FieldNames = Left($FieldNames, Len($FieldNames) - Len($locFieldSeparator));
        }
        if (Right($ScheduleFields, 2) == ", ")
        {
            $ScheduleFields = Left($ScheduleFields, Len($ScheduleFields) - 2);
        }
    }

    //********************************************************************
    // BuildUserSelectionFields($MemberFields, $RulesFields, $FieldNames)
    //
    // Purpose:  Build the selection field and export field names based
    //           on the user selection.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   MemberFields - the database fields for the select field
    //   RulesFields - the rules database fields to export
    //   FieldNames - the names of the fields selected
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildUserSelectionFields(&$MemberFields, &$RulesFields, &$FieldNames)
    {
        global $FieldSeparator;
        global $first_name;
        global $middle_name;
        global $last_name;
        global $title;
        global $email;
        global $username;
        global $password;
        global $user_level;
        global $counter;
        global $last_login;
        global $address1;
        global $city;
        global $state;
        global $zip;
        global $phone_number;
        global $SSN;
        global $Organization;
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
        global $Member_Notes;
        global $Credit_Card_Number;
        global $Credit_Card_Expiration;
        global $Manager_Message;
        global $Membership_Date;
        global $Resign_Date;
        global $Clearing_Authority;
        global $Password_Expires_Date;
        global $Birth_Date;
        
        // build the field name and field selection according to the fields
        // that the user has selected for export
        $MemberFields = "";
        $RulesFields = "";
        $FieldNames = "";
        $locFieldSeparator = $FieldSeparator . " ";
        if ($first_name == 1) 
        {
            $MemberFields = $MemberFields . "first_name, ";
            $FieldNames = $FieldNames . "First Name" . $locFieldSeparator;
        }
        if ($middle_name == 1) 
        {
            $MemberFields = $MemberFields . "middle_name, ";
            $FieldNames = $FieldNames . "Middle Name" . $locFieldSeparator;
        }
        if ($last_name == 1) 
        {
            $MemberFields = $MemberFields . "last_name, ";
            $FieldNames = $FieldNames . "Last Name" . $locFieldSeparator;
        }
        if ($title == 1) 
        {
            $MemberFields = $MemberFields . "title, ";
            $FieldNames = $FieldNames . "Title" . $locFieldSeparator;
        }
        if ($email == 1) 
        {
            $MemberFields = $MemberFields . "email, ";
            $FieldNames = $FieldNames . "EMail" . $locFieldSeparator;
        }
        if ($username == 1) 
        {
            $MemberFields = $MemberFields . "username, ";
            $FieldNames = $FieldNames . "Username" . $locFieldSeparator;
        }
        if ($password == 1) 
        {
            $MemberFields = $MemberFields . "password, ";
            $FieldNames = $FieldNames . "Password" . $locFieldSeparator;
        }
        if ($user_level == 1) 
        {
            $MemberFields = $MemberFields . "user_level, ";
            $FieldNames = $FieldNames . "User Level" . $locFieldSeparator;
        }
        if ($counter == 1) 
        {
            $MemberFields = $MemberFields . "counter, ";
            $FieldNames = $FieldNames . "Counter" . $locFieldSeparator;
        }
        if ($last_login == 1) 
        {
            $MemberFields = $MemberFields . "last_login, ";
            $FieldNames = $FieldNames . "Last Login" . $locFieldSeparator;
        }
        if ($address1 == 1) 
        {
            $MemberFields = $MemberFields . "address1, ";
            $FieldNames = $FieldNames . "Address" . $locFieldSeparator;
        }
        if ($city == 1) 
        {
            $MemberFields = $MemberFields . "city, ";
            $FieldNames = $FieldNames . "City" . $locFieldSeparator;
        }
        if ($state == 1) 
        {
            $MemberFields = $MemberFields . "state, ";
            $FieldNames = $FieldNames . "State" . $locFieldSeparator;
        }
        if ($zip == 1) 
        {
            $MemberFields = $MemberFields . "zip, ";
            $FieldNames = $FieldNames . "Zip" . $locFieldSeparator;
        }
        if ($phone_number == 1) 
        {
            $MemberFields = $MemberFields . "phone_number, ";
            $FieldNames = $FieldNames . "Phone Number 1" . $locFieldSeparator;
        }
        if ($SSN == 1) 
        {
            $MemberFields = $MemberFields . "SSN, ";
            $FieldNames = $FieldNames . "SSN" . $locFieldSeparator;
        }
        if ($Organization == 1) 
        {
            $MemberFields = $MemberFields . "Organization, ";
            $FieldNames = $FieldNames . "Organization" . $locFieldSeparator;
        }
        if ($Home_Phone == 1) 
        {
            $MemberFields = $MemberFields . "Home_Phone, ";
            $FieldNames = $FieldNames . "Phone Number 2" . $locFieldSeparator;
        }
        if ($Dues_Amount == 1) 
        {
            $MemberFields = $MemberFields . "Dues_Amount, ";
            $FieldNames = $FieldNames . "Dues Amount" . $locFieldSeparator;
        }
        if ($Member_Ground_Instruction_Amount == 1) 
        {
            $MemberFields = $MemberFields . "Ground_Instruction_Amount, ";
            $FieldNames = $FieldNames . "Ground Instruction Amount" . $locFieldSeparator;
        }
        if ($Member_Private_Instruction_Amount == 1) 
        {
            $MemberFields = $MemberFields . "Private_Instruction_Amount, ";
            $FieldNames = $FieldNames . "Private Instruction Amount" . $locFieldSeparator;
        }
        if ($Member_Instrument_Instruction_Amount == 1) 
        {
            $MemberFields = $MemberFields . "Instrument_Instruction_Amount, ";
            $FieldNames = $FieldNames . "Instrument Instruction Amount" . $locFieldSeparator;
        }
        if ($Member_Commercial_Instruction_Amount == 1) 
        {
            $MemberFields = $MemberFields . "Commercial_Instruction_Amount, ";
            $FieldNames = $FieldNames . "Commercial Instruction Amount" . $locFieldSeparator;
        }
        if ($Member_CFI_Instruction_Amount == 1) 
        {
            $MemberFields = $MemberFields . "CFI_Instruction_Amount, ";
            $FieldNames = $FieldNames . "CFI Instruction Amount" . $locFieldSeparator;
        }
        if ($Member_CFII_Instruction_Amount == 1) 
        {
            $MemberFields = $MemberFields . "CFII_Instruction_Amount, ";
            $FieldNames = $FieldNames . "CFII Instruction Amount" . $locFieldSeparator;
        }
        if ($Contract_Number == 1) 
        {
            $MemberFields = $MemberFields . "Contract_Number, ";
            $FieldNames = $FieldNames . "Contract Number" . $locFieldSeparator;
        }
        if ($Notify_First_Name == 1) 
        {
            $MemberFields = $MemberFields . "Notify_First_Name, ";
            $FieldNames = $FieldNames . "Notify First Name" . $locFieldSeparator;
        }
        if ($Notify_Middle_Initial == 1) 
        {
            $MemberFields = $MemberFields . "Notify_Middle_Initial, ";
            $FieldNames = $FieldNames . "Notify Middle Initial" . $locFieldSeparator;
        }
        if ($Notify_Last_Name == 1) 
        {
            $MemberFields = $MemberFields . "Notify_Last_Name, ";
            $FieldNames = $FieldNames . "Notify Last Name" . $locFieldSeparator;
        }
        if ($Notify_Relation == 1) 
        {
            $MemberFields = $MemberFields . "Notify_Relation, ";
            $FieldNames = $FieldNames . "Notify Relation" . $locFieldSeparator;
        }
        if ($Notify_Address == 1) 
        {
            $MemberFields = $MemberFields . "Notify_Address, ";
            $FieldNames = $FieldNames . "Notify Address" . $locFieldSeparator;
        }
        if ($Notify_City == 1) 
        {
            $MemberFields = $MemberFields . "Notify_City, ";
            $FieldNames = $FieldNames . "Notify City" . $locFieldSeparator;
        }
        if ($Notify_State == 1) 
        {
            $MemberFields = $MemberFields . "Notify_State, ";
            $FieldNames = $FieldNames . "Notify State" . $locFieldSeparator;
        }
        if ($Notify_Zip == 1) 
        {
            $MemberFields = $MemberFields . "Notify_Zip, ";
            $FieldNames = $FieldNames . "Notify Zip" . $locFieldSeparator;
        }
        if ($Notify_Phone1 == 1) 
        {
            $MemberFields = $MemberFields . "Notify_Phone1, ";
            $FieldNames = $FieldNames . "Notify Phone 1" . $locFieldSeparator;
        }
        if ($Notify_Phone2 == 1) 
        {
            $MemberFields = $MemberFields . "Notify_Phone2, ";
            $FieldNames = $FieldNames . "Notify Phone 2" . $locFieldSeparator;
        }
        if ($Contract_Expiration_Date == 1) 
        {
            $MemberFields = $MemberFields . "Contract_Expiration_Date, ";
            $FieldNames = $FieldNames . "Contract Expiration Date" . $locFieldSeparator;
        }
        if ($Member_Notes == 1) 
        {
            $MemberFields = $MemberFields . "Member_Notes, ";
            $FieldNames = $FieldNames . "Member Notes" . $locFieldSeparator;
        }
        if ($Credit_Card_Number == 1) 
        {
            $MemberFields = $MemberFields . "Credit_Card_Number, ";
            $FieldNames = $FieldNames . "Credit Card Number" . $locFieldSeparator;
        }
        if ($Credit_Card_Expiration == 1) 
        {
            $MemberFields = $MemberFields . "Credit_Card_Expiration, ";
            $FieldNames = $FieldNames . "Credit Card Expiration" . $locFieldSeparator;
        }
        if ($Manager_Message == 1) 
        {
            $MemberFields = $MemberFields . "Manager_Message, ";
            $FieldNames = $FieldNames . "Manager Message" . $locFieldSeparator;
        }
        if ($Membership_Date == 1) 
        {
            $MemberFields = $MemberFields . "Membership_Date, ";
            $FieldNames = $FieldNames . "Membership Date" . $locFieldSeparator;
        }
        if ($Resign_Date == 1) 
        {
            $MemberFields = $MemberFields . "Resign_Date, ";
            $FieldNames = $FieldNames . "Resign Date" . $locFieldSeparator;
        }
        if ($Clearing_Authority == 1) 
        {
            $MemberFields = $MemberFields . "Clearing_Authority, ";
            $FieldNames = $FieldNames . "Clearing Authority" . $locFieldSeparator;
        }
        if ($Password_Expires_Date == 1) 
        {
            $MemberFields = $MemberFields . "Password_Expires_Date, ";
            $FieldNames = $FieldNames . "Password Expires Date" . $locFieldSeparator;
        }
        if ($Birth_Date == 1) 
        {
            $RulesFields = $RulesFields . "Birth_Date, ";
            $FieldNames = $FieldNames . "Birth Date" . $locFieldSeparator;
        }
        
        // always include the rules field for any special fields
        $MemberFields = $MemberFields . "Rules_Field, ";
        
        // remove the last field separator from the field name
        if (Right($FieldNames, Len($locFieldSeparator)) == $locFieldSeparator)
        {
            $FieldNames = Left($FieldNames, Len($FieldNames) - Len($locFieldSeparator));
        }
        if (Right($MemberFields, 2) == ", ")
        {
            $MemberFields = Left($MemberFields, Len($MemberFields) - 2);
        }
        if (Right($RulesFields, 2) == ", ")
        {
            $RulesFields = Left($RulesFields, Len($RulesFields) - 2);
        }
    }

    //********************************************************************
    // BuildAircraftSelectionFields($AircraftFields, $FieldNames)
    //
    // Purpose:  Build the selection field and export field names based
    //           on the user selection.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   AircraftFields - the database fields for the select field
    //   FieldNames - the names of the fields selected
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildAircraftSelectionFields(&$AircraftFields, &$FieldNames)
    {    
        global $FieldSeparator;

        global $n_number;
        global $serial_number;
        global $hobbs;
        global $tach1;
        global $hourly_cost;
        global $rental_fee;
        global $empty_weight;
        global $max_gross;
        global $AircraftYear;
        global $code_id;
        global $make_id;
        global $model_id;
        global $ifr_cert;
        global $status;
        global $Aircraft_Color;
        global $Hrs_Till_100_Hr;
        global $Hundred_Hr_Tach;
        global $Annual_Due;
        global $Default_Fuel_Gallons;
        global $Full_Fuel_Gallons;
        global $Va_Max_Weight;
        global $Current_Hobbs;
        global $Current_User;
        global $CurrentKeycode;
        global $Aircraft_Owner_Name;
        global $Aircraft_Owner_Address;
        global $Aircraft_Owner_City;
        global $Aircraft_Owner_State;
        global $Aircraft_Owner_Zip;
        global $Aircraft_Owner_Contract;
        global $Aircraft_Owner_Phone1;
        global $Aircraft_Owner_Phone2;
        global $Aircraft_Remarks;
        global $Aircraft_Airspeed;
        global $Flight_ID;
        global $Oil_Type;

        // build the field name and field selection according to the fields
        // that the user has selected for export
        $AircraftFields = "";
        $FieldNames = "";
        $locFieldSeparator = $FieldSeparator . " ";
        if ($n_number == 1)
        {
            $AircraftFields = $AircraftFields . "n_number, ";
            $FieldNames = $FieldNames . "Tail Number" . $locFieldSeparator;
        }
        if ($serial_number == 1)
        {
            $AircraftFields = $AircraftFields . "serial_number, ";
            $FieldNames = $FieldNames . "Serial Number" . $locFieldSeparator;
        }
        if ($tach1 == 1)
        {
            $AircraftFields = $AircraftFields . "tach1, ";
            $FieldNames = $FieldNames . "Tach" . $locFieldSeparator;
        }
        if ($hourly_cost == 1)
        {
            $AircraftFields = $AircraftFields . "hourly_cost, ";
            $FieldNames = $FieldNames . "Hourly Cost" . $locFieldSeparator;
        }
        if ($rental_fee == 1)
        {
            $AircraftFields = $AircraftFields . "rental_fee, ";
            $FieldNames = $FieldNames . "Rental Fee" . $locFieldSeparator;
        }
        if ($empty_weight == 1)
        {
            $AircraftFields = $AircraftFields . "empty_weight, ";
            $FieldNames = $FieldNames . "Empty Weight" . $locFieldSeparator;
        }
        if ($max_gross == 1)
        {
            $AircraftFields = $AircraftFields . "max_gross, ";
            $FieldNames = $FieldNames . "Max Gross" . $locFieldSeparator;
        }
        if ($AircraftYear == 1)
        {
            $AircraftFields = $AircraftFields . "year, ";
            $FieldNames = $FieldNames . "Year" . $locFieldSeparator;
        }
        if ($code_id == 1)
        {
            $AircraftFields = $AircraftFields . "code_id, ";
            $FieldNames = $FieldNames . "Code ID" . $locFieldSeparator;
        }
        if ($make_id == 1)
        {
            $AircraftFields = $AircraftFields . "make_id, ";
            $FieldNames = $FieldNames . "Make ID" . $locFieldSeparator;
        }
        if ($model_id == 1)
        {
            $AircraftFields = $AircraftFields . "model_id, ";
            $FieldNames = $FieldNames . "Model ID" . $locFieldSeparator;
        }
        if ($ifr_cert == 1)
        {
            $AircraftFields = $AircraftFields . "ifr_cert, ";
            $FieldNames = $FieldNames . "IFR Certified" . $locFieldSeparator;
        }
        if ($status == 1)
        {
            $AircraftFields = $AircraftFields . "status, ";
            $FieldNames = $FieldNames . "Status" . $locFieldSeparator;
        }
        if ($Aircraft_Color == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Color, ";
            $FieldNames = $FieldNames . "Aircraft Color" . $locFieldSeparator;
        }
        if ($Hrs_Till_100_Hr == 1)
        {
            $AircraftFields = $AircraftFields . "Hrs_Till_100_Hr, ";
            $FieldNames = $FieldNames . "Hours until 100 Hr" . $locFieldSeparator;
        }
        if ($Hundred_Hr_Tach == 1)
        {
            $AircraftFields = $AircraftFields . "100_Hr_Tach, ";
            $FieldNames = $FieldNames . "Hundred Hour Tach" . $locFieldSeparator;
        }
        if ($Annual_Due == 1)
        {
            $AircraftFields = $AircraftFields . "Annual_Due, ";
            $FieldNames = $FieldNames . "Annual Due" . $locFieldSeparator;
        }
        if ($Default_Fuel_Gallons == 1)
        {
            $AircraftFields = $AircraftFields . "Default_Fuel_Gallons, ";
            $FieldNames = $FieldNames . "Default Fuel Gallons" . $locFieldSeparator;
        }
        if ($Full_Fuel_Gallons == 1)
        {
            $AircraftFields = $AircraftFields . "Full_Fuel_Gallons, ";
            $FieldNames = $FieldNames . "Full Fuel Gallons" . $locFieldSeparator;
        }
        if ($Va_Max_Weight == 1)
        {
            $AircraftFields = $AircraftFields . "Va_Max_Weight, ";
            $FieldNames = $FieldNames . "Va Max Weight" . $locFieldSeparator;
        }
        if ($Current_Hobbs == 1)
        {
            $AircraftFields = $AircraftFields . "Current_Hobbs, ";
            $FieldNames = $FieldNames . "Current Hobbs" . $locFieldSeparator;
        }
        if ($Current_User == 1)
        {
            $AircraftFields = $AircraftFields . "Current_User, ";
            $FieldNames = $FieldNames . "Current User" . $locFieldSeparator;
        }
        if ($CurrentKeycode == 1)
        {
            $AircraftFields = $AircraftFields . "CurrentKeycode, ";
            $FieldNames = $FieldNames . "CurrentKeycode" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_Name == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_Name, ";
            $FieldNames = $FieldNames . "Aircraft Owner Name" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_Address == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_Address, ";
            $FieldNames = $FieldNames . "Aircraft Owner Address" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_City == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_City, ";
            $FieldNames = $FieldNames . "Aircraft Owner City" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_State == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_State, ";
            $FieldNames = $FieldNames . "Aircraft Owner State" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_Zip == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_Zip, ";
            $FieldNames = $FieldNames . "Aircraft Owner Zip" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_Contract == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_Contract, ";
            $FieldNames = $FieldNames . "Aircraft Owner Contract" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_Phone1 == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_Phone1, ";
            $FieldNames = $FieldNames . "Aircraft Owner Phone 1" . $locFieldSeparator;
        }
        if ($Aircraft_Owner_Phone2 == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Owner_Phone2, ";
            $FieldNames = $FieldNames . "Aircraft Owner Phone 2" . $locFieldSeparator;
        }
        if ($Aircraft_Remarks == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Remarks, ";
            $FieldNames = $FieldNames . "Aircraft Remarks" . $locFieldSeparator;
        }
        if ($Aircraft_Airspeed == 1)
        {
            $AircraftFields = $AircraftFields . "Aircraft_Airspeed, ";
            $FieldNames = $FieldNames . "Aircraft Airspeed" . $locFieldSeparator;
        }
        if ($Flight_ID == 1)
        {
            $AircraftFields = $AircraftFields . "Flight_ID, ";
            $FieldNames = $FieldNames . "Flight ID" . $locFieldSeparator;
        }
        if ($Oil_Type == 1)
        {
            $AircraftFields = $AircraftFields . "Oil_Type, ";
            $FieldNames = $FieldNames . "Oil Type" . $locFieldSeparator;
        }
        
        // remove the last field separator from the field name
        if (Right($FieldNames, Len($locFieldSeparator)) == $locFieldSeparator)
        {
            $FieldNames = Left($FieldNames, Len($FieldNames) - Len($locFieldSeparator));
        }
        if (Right($AircraftFields, 2) == ", ")
        {
            $AircraftFields = Left($AircraftFields, Len($AircraftFields) - 2);
        }
    }

    //********************************************************************
    // BuildFlightSelectionFields($FlightFields, $FieldNames)
    //
    // Purpose:  Build the selection field and export field names based
    //           on the user selection.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   FlightFields - the database fields for the select field
    //   FieldNames - the names of the fields selected
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildFlightSelectionFields(&$FlightFields, &$FieldNames)
    {    
        global $FieldSeparator;
        
        global $FlightKeycode;
        global $FlightDate;
        global $FlightAircraft;
        global $FlightModelId;
        global $Begin_Hobbs;
        global $End_Hobbs;
        global $Hobbs_Elapsed;
        global $Aircraft_Rate;
        global $Aircraft_Cost;
        global $Begin_Tach;
        global $End_Tach;
        global $Day_Time;
        global $Night_Time;
        global $Instruction_Type;
        global $Dual_Time;
        global $Dual_PP_Time;
        global $Instruction_Rate;
        global $Instructor_Charge;
        global $Instructor_Keycode;
        global $Student_Keycode;
        global $Day_Landings;
        global $Night_Landings;
        global $Navigation_Intercepts;
        global $Holding_Procedures;
        global $Instrument_Approach;
        global $Fuel_Cost;
        global $Local_Fuel;
        global $Local_Fuel_Cost;
        global $Cross_Country_Fuel;
        global $Cross_Country_Fuel_Credit;
        global $Oil;
        global $Oil_Rate;
        global $Oil_Cost;
        global $Owner_Rate;
        global $Owner_Reimbursement;
        global $Cleared_By;

        // build the field name and field selection according to the fields
        // that the user has selected for export
        $FlightFields = "";
        $FieldNames = "";
        $locFieldSeparator = $FieldSeparator . " ";     
        if ($FlightKeycode == 1)
        {
            $FlightFields = $FlightFields . "Keycode, ";
            $FieldNames = $FieldNames . "Name" . $locFieldSeparator;
        }
        if ($FlightDate == 1)
        {
            $FlightFields = $FlightFields . "Date, ";
            $FieldNames = $FieldNames . "Date" . $locFieldSeparator;
        }
        if ($FlightAircraft == 1)
        {
            $FlightFields = $FlightFields . "Aircraft, ";
            $FieldNames = $FieldNames . "Tailnumber" . $locFieldSeparator;
        }
        if ($FlightModelId == 1)
        {
            $FlightFields = $FlightFields . "model_id, ";
            $FieldNames = $FieldNames . "Model" . $locFieldSeparator;
        }
        if ($Begin_Hobbs == 1)
        {
            $FlightFields = $FlightFields . "Begin_Hobbs, ";
            $FieldNames = $FieldNames . "Begin Hobbs" . $locFieldSeparator;
        }
        if ($End_Hobbs == 1)
        {
            $FlightFields = $FlightFields . "End_Hobbs, ";
            $FieldNames = $FieldNames . "End Hobbs" . $locFieldSeparator;
        }
        if ($Hobbs_Elapsed == 1)
        {
            $FlightFields = $FlightFields . "Hobbs_Elapsed, ";
            $FieldNames = $FieldNames . "Hobbs Elapsed" . $locFieldSeparator;
        }
        if ($Aircraft_Rate == 1)
        {
            $FlightFields = $FlightFields . "Aircraft_Rate, ";
            $FieldNames = $FieldNames . "Aircraft Rate" . $locFieldSeparator;
        }
        if ($Aircraft_Cost == 1)
        {
            $FlightFields = $FlightFields . "Aircraft_Cost, ";
            $FieldNames = $FieldNames . "Aircraft Cost" . $locFieldSeparator;
        }
        if ($Begin_Tach == 1)
        {
            $FlightFields = $FlightFields . "Begin_Tach, ";
            $FieldNames = $FieldNames . "Begin Tach" . $locFieldSeparator;
        }
        if ($End_Tach == 1)
        {
            $FlightFields = $FlightFields . "End_Tach, ";
            $FieldNames = $FieldNames . "End Tach" . $locFieldSeparator;
        }
        if ($Day_Time == 1)
        {
            $FlightFields = $FlightFields . "Day_Time, ";
            $FieldNames = $FieldNames . "Day Time" . $locFieldSeparator;
        }
        if ($Night_Time == 1)
        {
            $FlightFields = $FlightFields . "Night_Time, ";
            $FieldNames = $FieldNames . "Night Time" . $locFieldSeparator;
        }
        if ($Instruction_Type == 1)
        {
            $FlightFields = $FlightFields . "Instruction_Type, ";
            $FieldNames = $FieldNames . "Instruction Type" . $locFieldSeparator;
        }
        if ($Dual_Time == 1)
        {
            $FlightFields = $FlightFields . "Dual_Time, ";
            $FieldNames = $FieldNames . "Dual Time" . $locFieldSeparator;
        }
        if ($Dual_PP_Time == 1)
        {
            $FlightFields = $FlightFields . "Dual_PP_Time, ";
            $FieldNames = $FieldNames . "Dual PP Time" . $locFieldSeparator;
        }
        if ($Instruction_Rate == 1)
        {
            $FlightFields = $FlightFields . "Instruction_Rate, ";
            $FieldNames = $FieldNames . "Instruction Rate" . $locFieldSeparator;
        }
        if ($Instructor_Charge == 1)
        {
            $FlightFields = $FlightFields . "Instructor_Charge, ";
            $FieldNames = $FieldNames . "Instructor Charge" . $locFieldSeparator;
        }
        if ($Instructor_Keycode == 1)
        {
            $FlightFields = $FlightFields . "Instructor_Keycode, ";
            $FieldNames = $FieldNames . "Instructor Username" . $locFieldSeparator;
        }
        if ($Student_Keycode == 1)
        {
            $FlightFields = $FlightFields . "Student_Keycode, ";
            $FieldNames = $FieldNames . "Student Username" . $locFieldSeparator;
        }
        if ($Day_Landings == 1)
        {
            $FlightFields = $FlightFields . "Day_Landings, ";
            $FieldNames = $FieldNames . "Day Landings" . $locFieldSeparator;
        }
        if ($Night_Landings == 1)
        {
            $FlightFields = $FlightFields . "Night_Landings, ";
            $FieldNames = $FieldNames . "Night Landings" . $locFieldSeparator;
        }
        if ($Navigation_Intercepts == 1)
        {
            $FlightFields = $FlightFields . "Navigation_Intercepts, ";
            $FieldNames = $FieldNames . "Navigation Intercepts" . $locFieldSeparator;
        }
        if ($Holding_Procedures == 1)
        {
            $FlightFields = $FlightFields . "Holding_Procedures, ";
            $FieldNames = $FieldNames . "Holding Procedures" . $locFieldSeparator;
        }
        if ($Instrument_Approach == 1)
        {
            $FlightFields = $FlightFields . "Instrument_Approach, ";
            $FieldNames = $FieldNames . "Instrument Approach" . $locFieldSeparator;
        }
        if ($Fuel_Cost == 1)
        {
            $FlightFields = $FlightFields . "Fuel_Cost, ";
            $FieldNames = $FieldNames . "Fuel Cost" . $locFieldSeparator;
        }
        if ($Local_Fuel == 1)
        {
            $FlightFields = $FlightFields . "Local_Fuel, ";
            $FieldNames = $FieldNames . "Local Fuel" . $locFieldSeparator;
        }
        if ($Local_Fuel_Cost == 1)
        {
            $FlightFields = $FlightFields . "Local_Fuel_Cost, ";
            $FieldNames = $FieldNames . "Local Fuel Cost" . $locFieldSeparator;
        }
        if ($Cross_Country_Fuel == 1)
        {
            $FlightFields = $FlightFields . "Cross_Country_Fuel, ";
            $FieldNames = $FieldNames . "Cross Country Fuel" . $locFieldSeparator;
        }
        if ($Cross_Country_Fuel_Credit == 1)
        {
            $FlightFields = $FlightFields . "Cross_Country_Fuel_Credit, ";
            $FieldNames = $FieldNames . "Cross Country Fuel Credit" . $locFieldSeparator;
        }
        if ($Oil == 1)
        {
            $FlightFields = $FlightFields . "Oil, ";
            $FieldNames = $FieldNames . "Oil" . $locFieldSeparator;
        }
        if ($Oil_Rate == 1)
        {
            $FlightFields = $FlightFields . "Oil_Rate, ";
            $FieldNames = $FieldNames . "Oil Rate" . $locFieldSeparator;
        }
        if ($Oil_Cost == 1)
        {
            $FlightFields = $FlightFields . "Oil_Cost, ";
            $FieldNames = $FieldNames . "Oil Cost" . $locFieldSeparator;
        }
        if ($Owner_Rate == 1)
        {
            $FlightFields = $FlightFields . "Owner_Rate, ";
            $FieldNames = $FieldNames . "Owner Rate" . $locFieldSeparator;
        }
        if ($Owner_Reimbursement == 1)
        {
            $FlightFields = $FlightFields . "Owner_Reimbursement, ";
            $FieldNames = $FieldNames . "Owner Reimbursement" . $locFieldSeparator;
        }
        if ($Cleared_By == 1)
        {
            $FlightFields = $FlightFields . "Cleared_By, ";
            $FieldNames = $FieldNames . "Cleared By" . $locFieldSeparator;
        }
        
        // remove the last field separator from the field name
        if (Right($FieldNames, Len($locFieldSeparator)) == $locFieldSeparator)
        {
            $FieldNames = Left($FieldNames, Len($FieldNames) - Len($locFieldSeparator));
        }
        if (Right($FlightFields, 2) == ", ")
        {
            $FlightFields = Left($FlightFields, Len($FlightFields) - 2);
        }
    }

    //********************************************************************
    // BuildChargeSelectionFields($ChargeFields, $FieldNames)
    //
    // Purpose:  Build the selection field and export field names based
    //           on the user selection.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   ChargeFields - the database fields for the select field
    //   FieldNames - the names of the fields selected
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildChargeSelectionFields(&$ChargeFields, &$FieldNames)
    {    
        global $FieldSeparator;
        
        global $ChargeKeyCode;
        global $ChargeDate;
        global $Part_Number;
        global $Part_Description;
        global $Quantity;
        global $Price;
        global $Total_Price;
        global $ChargeCategory;
        global $Unit_Price;

        // build the field name and field selection according to the fields
        // that the user has selected for export
        $ChargeFields = "";
        $FieldNames = "";
        $locFieldSeparator = $FieldSeparator . " ";     
        if ($ChargeKeyCode == 1)
        {
            $ChargeFields = $ChargeFields . "KeyCode, ";
            $FieldNames = $FieldNames . "Name" . $locFieldSeparator;
        }
        if ($ChargeDate == 1)
        {
            $ChargeFields = $ChargeFields . "Date, ";
            $FieldNames = $FieldNames . "Date" . $locFieldSeparator;
        }
        if ($Part_Number == 1)
        {
            $ChargeFields = $ChargeFields . "Part_Number, ";
            $FieldNames = $FieldNames . "Part Number" . $locFieldSeparator;
        }
        if ($Part_Description == 1)
        {
            $ChargeFields = $ChargeFields . "Part_Description, ";
            $FieldNames = $FieldNames . "Part Description" . $locFieldSeparator;
        }
        if ($Quantity == 1)
        {
            $ChargeFields = $ChargeFields . "Quantity, ";
            $FieldNames = $FieldNames . "Quantity" . $locFieldSeparator;
        }
        if ($Price == 1)
        {
            $ChargeFields = $ChargeFields . "Price, ";
            $FieldNames = $FieldNames . "Price" . $locFieldSeparator;
        }
        if ($Total_Price == 1)
        {
            $ChargeFields = $ChargeFields . "Total_Price, ";
            $FieldNames = $FieldNames . "Total Price" . $locFieldSeparator;
        }
        if ($ChargeCategory == 1)
        {
            $ChargeFields = $ChargeFields . " Category, ";
            $FieldNames = $FieldNames . " Category" . $locFieldSeparator;
        }
        if ($Unit_Price == 1)
        {
            $ChargeFields = $ChargeFields . "Unit_Price, ";
            $FieldNames = $FieldNames . "Unit Price" . $locFieldSeparator;
        }
        
        // remove the last field separator from the field name
        if (Right($FieldNames, Len($locFieldSeparator)) == $locFieldSeparator)
        {
            $FieldNames = Left($FieldNames, Len($FieldNames) - Len($locFieldSeparator));
        }
        if (Right($ChargeFields, 2) == ", ")
        {
            $ChargeFields = Left($ChargeFields, Len($ChargeFields) - 2);
        }
    }

    //********************************************************************
    // BuildInventorySelectionFields($InventoryFields, $FieldNames)
    //
    // Purpose:  Build the selection field and export field names based
    //           on the user selection.
    //
    // Inputs:
    //   none
    //
    // Outputs:
    //   InventoryFields - the database fields for the select field
    //   FieldNames - the names of the fields selected
    //
    // Returns:
    //   none
    //*********************************************************************
    function BuildInventorySelectionFields(&$InventoryFields, &$FieldNames)
    {    
        global $FieldSeparator;
        
        global $InventoryDate;
        global $InventoryPart_Number;
        global $InventoryDescription;
        global $InventoryUnit_Price;
        global $InventoryRetail_Price;
        global $InventoryQuantity_In_Stock;
        global $InventoryReorder_Quantity;
        global $InventoryPosition;
        global $InventoryCategory;
        global $Inventory_Type;

        // build the field name and field selection according to the fields
        // that the user has selected for export
        $InventoryFields = "";
        $FieldNames = "";
        $locFieldSeparator = $FieldSeparator . " ";     
        if ($InventoryDate == 1)
		{
			$InventoryFields = $InventoryFields . "Date, ";
			$FieldNames = $FieldNames . "Date" . $locFieldSeparator;
		}
        if ($InventoryPart_Number == 1)
		{
			$InventoryFields = $InventoryFields . "Part_Number, ";
			$FieldNames = $FieldNames . "Part Number" . $locFieldSeparator;
		}
        if ($InventoryDescription == 1)
		{
			$InventoryFields = $InventoryFields . "Description, ";
			$FieldNames = $FieldNames . "Description" . $locFieldSeparator;
		}
        if ($InventoryUnit_Price == 1)
		{
			$InventoryFields = $InventoryFields . "Unit_Price, ";
			$FieldNames = $FieldNames . "Unit Price" . $locFieldSeparator;
		}
        if ($InventoryRetail_Price == 1)
		{
			$InventoryFields = $InventoryFields . "Retail_Price, ";
			$FieldNames = $FieldNames . "Retail Price" . $locFieldSeparator;
		}
        if ($InventoryQuantity_In_Stock == 1)
		{
			$InventoryFields = $InventoryFields . "Quantity_In_Stock, ";
			$FieldNames = $FieldNames . "Quantity In Stock" . $locFieldSeparator;
		}
        if ($InventoryReorder_Quantity == 1)
		{
			$InventoryFields = $InventoryFields . "Reorder_Quantity, ";
			$FieldNames = $FieldNames . "Reorder Quantity" . $locFieldSeparator;
		}
        if ($InventoryPosition == 1)
		{
			$InventoryFields = $InventoryFields . "Position, ";
			$FieldNames = $FieldNames . "Position" . $locFieldSeparator;
		}
        if ($InventoryCategory == 1)
		{
			$InventoryFields = $InventoryFields . "Category, ";
			$FieldNames = $FieldNames . "Category" . $locFieldSeparator;
		}
        if ($Inventory_Type == 1)
		{
			$InventoryFields = $InventoryFields . "Inventory_Type, ";
			$FieldNames = $FieldNames . "Inventory Type" . $locFieldSeparator;
		}
        
        // remove the last field separator from the field name
        if (Right($FieldNames, Len($locFieldSeparator)) == $locFieldSeparator)
        {
            $FieldNames = Left($FieldNames, Len($FieldNames) - Len($locFieldSeparator));
        }
        if (Right($InventoryFields, 2) == ", ")
        {
            $InventoryFields = Left($InventoryFields, Len($InventoryFields) - 2);
        }
    }
       
    //********************************************************************
    // ExportScheduleDatabase()
    //
    // Purpose:  Export the Schedule database information
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
    function ExportScheduleDatabase()
    {   
        global $FieldSeparator;
        global $MembersFromNameOfUser;
        global $MembersToNameOfUser;
        
        global $FromScheduleday;
        global $FromSchedulemonth;
        global $FromScheduleyear;
        global $ToScheduleDay;
        global $ToScheduleMonth;
        global $ToScheduleYear;

        global $start_time;
        global $end_time;
        global $resource_id;
        global $create_time;
        global $create_by;
        global $ScheduleName;
        global $Schedulephone_number;
        global $description;

        // get the Schedule database selection fields and the field titles
        BuildScheduleSelectionFields($ScheduleFields, $FieldNames);
        
        // if any fields are seleted for export, export the data
        if (len(trim($ScheduleFields)) > 0)
        {            
            // build the WHERE clause parameters
            $StartTime = mktime(0, 0, 0, $FromSchedulemonth, $FromScheduleday, $FromScheduleyear);
            $EndTime = mktime(0, 0, 0 , $ToScheduleMonth, $ToScheduleDay, $ToScheduleYear);
            $WhereClause =  "start_time >= '$StartTime' AND " .
                            "start_time <='$EndTime'";
                            
            // get all records for all users since the name format doesn't allow us to
            // select on just the last name 
            $StartName = GetLastName($MembersFromNameOfUser) . " " . GetFirstName($MembersFromNameOfUser);
            $EndName = GetLastName($MembersToNameOfUser) . " " . GetFirstName($MembersToNameOfUser);

            // get the requested data from the database
            $ScheduleResult = SQLOpenRecordset(
                "SELECT name, $ScheduleFields FROM AircraftScheduling_entry WHERE ($WhereClause) " .
                    "ORDER BY start_time");
            // export the header information
            echo $FieldNames .  "\r\n";
            
            // get the field names
            $FieldNamesArray = preg_split("/$FieldSeparator /", $FieldNames);
    
            for($ScheduleCnt=0; $ScheduleRST = sql_row($ScheduleResult, $ScheduleCnt); $ScheduleCnt++) 
            {
                // the name format is First Last in this table. since we want to filter
                // on last name, we have to do some checking here rather than in the SQL
                $RecordName = GetLastName($ScheduleRST[0]) . " " . GetFirstName($ScheduleRST[0]);
                if ($RecordName >= $StartName && $RecordName <= $EndName)
                {
                    // export the rows
                    for ($i = 1; $i < count($ScheduleRST); $i++)
                    {
                        // format the field if needed
                        switch ($FieldNamesArray[$i - 1])
                        {
                            case "Start Time":
                                $FormatedField = date("d-M-Y H:i:s", $ScheduleRST[$i] - TimeZoneAdjustment());
                            break;
                            case "End Time":
                                $FormatedField = date("d-M-Y H:i:s", $ScheduleRST[$i] - TimeZoneAdjustment());
                            break;
                            case "Resource":
                                $FormatedField = 
                                              sql_query1(
                                                         "SELECT resource_name " .
                                                         "FROM AircraftScheduling_resource " .
                                                         "WHERE resource_id = $ScheduleRST[$i]");
                            break;
                            case "Creation Time":
                                $FormatedField = date("d-M-Y H:i:s", $ScheduleRST[$i] - TimeZoneAdjustment());
                            break;
                            case "Created By":
                                $FormatedField = GetNameFromUsername($ScheduleRST[$i]);
                            break;
                            default:
                                $FormatedField = $ScheduleRST[$i];
                            break;
                        }
                        if ($i < (count($ScheduleRST) -  1))
                            echo $FormatedField . $FieldSeparator . " ";
                        else
                            echo $FormatedField . "\r";
                    }
                    echo "\n";
                }
            }
        }
    }
       
    //********************************************************************
    // ExportUserDatabase()
    //
    // Purpose:  Export the user database information
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
    function ExportUserDatabase()
    {
        global $UserSortSelection;
        global $Rating;
        global $Member_Status;
        global $MembersFromNameOfUser;
        global $MembersToNameOfUser;
        global $FieldSeparator;
        global $DatabaseNameFormat;

        // get the user database selection fields and the field titles
        BuildUserSelectionFields($MemberFields, $RulesFields, $FieldNames);
        
        // if any fields are seleted for export, export the data
        if ($MemberFields != "Rules_Field" || len(trim($RulesFields)) > 0)
        {
            // build the WHERE clause parameters
            $WhereClause =  "CONCAT(last_name, ' ', first_name) >= '" . 
                                GetLastName($MembersFromNameOfUser) . " " . GetFirstName($MembersFromNameOfUser) . "' AND " .
                            "CONCAT(last_name, ' ', first_name) <='" . 
                                GetLastName($MembersToNameOfUser) . " " . GetFirstName($MembersToNameOfUser) . "'";
            if (UCase($Member_Status) != "ALL")
            {
                // limit exported information to the member status specified
                $WhereClause = $WhereClause .
                    " AND INSTR(Rules_Field, 'Member_Status,$Member_Status')";
            }
            if (UCase($Rating) != "ALL")
            {
                // limit exported information to the rating specified
                $WhereClause = $WhereClause .
                    " AND INSTR(Rules_Field, 'Rating,$Rating')";
            }
    
            // get the requested data from the database
            $MembersResult = SQLOpenRecordset(
                "SELECT $MemberFields FROM AircraftScheduling_person WHERE ($WhereClause) " .
                    "ORDER BY $UserSortSelection");
            
            // export the header information
            echo $FieldNames .  "\r\n";
    
            for($MembersCnt=0; $MembersRST = sql_row($MembersResult, $MembersCnt); $MembersCnt++) 
            {
                // export the rows
                // if we have any rules fields to export
                if (len($RulesFields) > 0)
                {
                    // we have some rule fields to support, export the
                    // normal fields first
                    for ($i = 0; $i < count($MembersRST) - 1; $i++)
                    {
                        echo $MembersRST[$i] . $FieldSeparator . " ";
                    }
                    
                    // export the rules fields
                    // load the currency fields from the rules field of the database database
                    LoadDBCurrencyFields("", $MembersRST[count($MembersRST) - 1]);
    
                    // get the list of rule field variables to export
                    $RuleArray = split(", ", $RulesFields);
                    foreach ($RuleArray as $Item)
                    {
                        // lookup the requested value
                        $RuleValue = LookupCurrencyFieldname($Item);
                        
                        // add a seperator and export the value
                        if ($i < (count($RuleArray) -  1))
                            echo $RuleValue . $FieldSeparator . " ";
                        else
                            echo $RuleValue . "\r";
                    }                
                }
                else
                {
                    // no rule fields to export, export the normal fields
                    for ($i = 0; $i < count($MembersRST) - 1; $i++)
                    {
                        if ($i < (count($MembersRST) -  2))
                            echo $MembersRST[$i] . $FieldSeparator . " ";
                        else
                            echo $MembersRST[$i] . "\r";
                    }
                }
                echo "\n";
            }
        }
    }
       
    //********************************************************************
    // ExportAircraftDatabase()
    //
    // Purpose:  Export the aircraft database information
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
    function ExportAircraftDatabase()
    {
        global $AircraftSortSelection;
        global $Aircraft_status;
        global $AircraftFromTailNumber;
        global $AircraftToTailNumber;
        global $AircraftRentalSelection;
        global $FieldSeparator;

        // get the aircraft database selection fields and the field titles
        BuildAircraftSelectionFields($AircraftFields, $FieldNames);
        
        // if any fields are seleted for export, export the data
        if (len(trim($AircraftFields)) > 0)
        {            
            // build the WHERE clause parameters
            $WhereClause =  "n_number >= '$AircraftFromTailNumber' AND " .
                            "n_number <='$AircraftToTailNumber'";
            if (UCase($Aircraft_status) != "ALL")
            {
                // limit exported information to the aircraft status specified
                $WhereClause = $WhereClause .
                    " AND status=" . LookupAircraftStatus($Aircraft_status);;
            }
            if (UCase($AircraftRentalSelection) == "RENTAL")
            {
                // limit exported information to rental aircraft
                $WhereClause = $WhereClause .
                    " AND hourly_cost > 0";
            }
            else if (UCase($AircraftRentalSelection) == "NONRENTAL")
            {
                // limit exported information to non-rental aircraft
                $WhereClause = $WhereClause .
                    " AND hourly_cost = 0";
            }
    
            // get the requested data from the database
            $AircraftResult = SQLOpenRecordset(
                "SELECT $AircraftFields FROM AircraftScheduling_aircraft WHERE ($WhereClause) " .
                    "ORDER BY $AircraftSortSelection");
            
            // export the header information
            echo $FieldNames .  "\r\n";
            
            // get the field names
            $FieldNamesArray = preg_split("/$FieldSeparator /", $FieldNames);
    
            for($AircraftCnt=0; $AircraftRST = sql_row($AircraftResult, $AircraftCnt); $AircraftCnt++) 
            {
                // export the rows
                for ($i = 0; $i < count($AircraftRST); $i++)
                {
                    // format the field if needed
                    switch ($FieldNamesArray[$i])
                    {
                        case "Annual Due":
                            $FormatedField = FormatField($AircraftRST[$i], "Date");
                        break;
                        case "Make ID":
                            $FormatedField = LookupAircraftMake($AircraftRST[$i]);
                        break;
                        case "Model ID":
                            $FormatedField = LookupAircraftType($AircraftRST[$i]);
                        break;
                        case "Status":
                            $FormatedField = LookupAircraftStatusString($AircraftRST[$i]);
                        break;
                        case "Code ID":
                            $FormatedField = LookupAircraftEquipmentCodeString($AircraftRST[$i]);
                        break;
                        default:
                            $FormatedField = $AircraftRST[$i];
                        break;
                    }
                    if ($i < (count($AircraftRST) -  1))
                        echo $FormatedField . $FieldSeparator . " ";
                    else
                        echo $FormatedField . "\r";
                }
                echo "\n";
            }
        }
    }
       
    //********************************************************************
    // ExportFlightDatabase()
    //
    // Purpose:  Export the flight database information
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
    function ExportFlightDatabase()
    {   
        global $FlightSortSelection;
        global $FromFlightday;
        global $FromFlightmonth;
        global $FromFlightyear;
        global $ToFlightDay;
        global $ToFlightMonth;
        global $ToFlightYear;
        global $MembersFromNameOfUser;
        global $MembersToNameOfUser;
        global $FieldSeparator;

        // get the Flight database selection fields and the field titles
        BuildFlightSelectionFields($FlightFields, $FieldNames);
        
        // if any fields are seleted for export, export the data
        if (len(trim($FlightFields)) > 0)
        {            
            // build the WHERE clause parameters
            $StartDate = date("Y-m-d", mktime(0, 0, 0, $FromFlightmonth, $FromFlightday, $FromFlightyear));
            $EndDate = date("Y-m-d", mktime(0, 0, 0 , $ToFlightMonth, $ToFlightDay, $ToFlightYear));
            $WhereClause =  "Date >= '$StartDate' AND " .
                            "Date <='$EndDate'";
                            
            // add the username 
            $WhereClause =  $WhereClause . " AND Keycode >= '" . 
                                GetUsernameFromName($MembersFromNameOfUser) . "' AND " .
                            "Keycode <='" . 
                                GetUsernameFromName($MembersToNameOfUser) . "'";
    
            // get the requested data from the database
            $FlightResult = SQLOpenRecordset(
                "SELECT $FlightFields FROM Flight WHERE ($WhereClause) " .
                    "ORDER BY $FlightSortSelection");
            // export the header information
            echo $FieldNames .  "\r\n";
            
            // get the field names
            $FieldNamesArray = preg_split("/$FieldSeparator /", $FieldNames);
    
            for($FlightCnt=0; $FlightRST = sql_row($FlightResult, $FlightCnt); $FlightCnt++) 
            {
                // export the rows
                for ($i = 0; $i < count($FlightRST); $i++)
                {
                    // format the field if needed
                    switch ($FieldNamesArray[$i])
                    {
                        case "Name":
                            $FormatedField = GetNameFromUsername($FlightRST[$i]);
                        break;
                        case "Date":
                            $FormatedField = FormatField($FlightRST[$i], "Date");
                        break;
                        case "Model":
                            $FormatedField = LookupAircraftType($FlightRST[$i]);
                        break;
                        default:
                            $FormatedField = $FlightRST[$i];
                        break;
                    }
                    if ($i < (count($FlightRST) -  1))
                        echo $FormatedField . $FieldSeparator . " ";
                    else
                        echo $FormatedField . "\r";
                }
                echo "\n";
            }
        }
    }
       
    //********************************************************************
    // ExportChargeDatabase()
    //
    // Purpose:  Export the charge database information
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
    function ExportChargeDatabase()
    {   
        global $ChargeSortSelection;
        global $FromChargeday;
        global $FromChargemonth;
        global $FromChargeyear;
        global $ToChargeDay;
        global $ToChargeMonth;
        global $ToChargeYear;
        global $MembersFromNameOfUser;
        global $MembersToNameOfUser;
        global $FieldSeparator;

        // get the Charge database selection fields and the field titles
        BuildChargeSelectionFields($ChargeFields, $FieldNames);
        
        // if any fields are seleted for export, export the data
        if (len(trim($ChargeFields)) > 0)
        {            
            // build the WHERE clause parameters
            $StartDate = date("Y-m-d", mktime(0, 0, 0, $FromChargemonth, $FromChargeday, $FromChargeyear));
            $EndDate = date("Y-m-d", mktime(0, 0, 0 , $ToChargeMonth, $ToChargeDay, $ToChargeYear));
            $WhereClause =  "Date >= '$StartDate' AND " .
                            "Date <='$EndDate'";
                            
            // add the username 
            $WhereClause =  $WhereClause . " AND KeyCode >= '" . 
                                GetUsernameFromName($MembersFromNameOfUser) . "' AND " .
                            "KeyCode <='" . 
                                GetUsernameFromName($MembersToNameOfUser) . "'";
    
            // get the requested data from the database
            $ChargeResult = SQLOpenRecordset(
                "SELECT $ChargeFields FROM Charges WHERE ($WhereClause) " .
                    "ORDER BY $ChargeSortSelection");
            // export the header information
            echo $FieldNames .  "\r\n";
            
            // get the field names
            $FieldNamesArray = preg_split("/$FieldSeparator /", $FieldNames);
    
            for($ChargeCnt=0; $ChargeRST = sql_row($ChargeResult, $ChargeCnt); $ChargeCnt++) 
            {
                // export the rows
                for ($i = 0; $i < count($ChargeRST); $i++)
                {
                    // format the field if needed
                    switch ($FieldNamesArray[$i])
                    {
                        case "Name":
                            $FormatedField = GetNameFromUsername($ChargeRST[$i]);
                        break;
                        case "Date":
                            $FormatedField = FormatField($ChargeRST[$i], "Date");
                        break;
                        default:
                            $FormatedField = $ChargeRST[$i];
                        break;
                    }
                    if ($i < (count($ChargeRST) -  1))
                        echo $FormatedField . $FieldSeparator . " ";
                    else
                        echo $FormatedField . "\r";
                }
                echo "\n";
            }
        }
    }
       
    //********************************************************************
    // ExportInventoryDatabase()
    //
    // Purpose:  Export the Inventory database information
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
    function ExportInventoryDatabase()
    {   
        global $InventorySortSelection;
        global $FromInventoryday;
        global $FromInventorymonth;
        global $FromInventoryyear;
        global $ToInventoryDay;
        global $ToInventoryMonth;
        global $ToInventoryYear;
        global $FieldSeparator;

        // get the Inventory database selection fields and the field titles
        BuildInventorySelectionFields($InventoryFields, $FieldNames);
        
        // if any fields are seleted for export, export the data
        if (len(trim($InventoryFields)) > 0)
        {            
            // build the WHERE clause parameters
            $StartDate = date("Y-m-d", mktime(0, 0, 0, $FromInventorymonth, $FromInventoryday, $FromInventoryyear));
            $EndDate = date("Y-m-d", mktime(0, 0, 0 , $ToInventoryMonth, $ToInventoryDay, $ToInventoryYear));
            $WhereClause =  "Date >= '$StartDate' AND " .
                            "Date <='$EndDate'";
    
            // get the requested data from the database
            $InventoryResult = SQLOpenRecordset(
                "SELECT $InventoryFields FROM Inventory WHERE ($WhereClause) " .
                    "ORDER BY $InventorySortSelection");
            // export the header information
            echo $FieldNames .  "\r\n";
            
            // get the field names
            $FieldNamesArray = preg_split("/$FieldSeparator /", $FieldNames);
    
            for($InventoryCnt=0; $InventoryRST = sql_row($InventoryResult, $InventoryCnt); $InventoryCnt++) 
            {
                // export the rows
                for ($i = 0; $i < count($InventoryRST); $i++)
                {
                    // format the field if needed
                    switch ($FieldNamesArray[$i])
                    {
                        case "Date":
                            $FormatedField = FormatField($InventoryRST[$i], "Date");
                        break;
                        default:
                            $FormatedField = $InventoryRST[$i];
                        break;
                    }
                    if ($i < (count($InventoryRST) -  1))
                        echo $FormatedField . $FieldSeparator . " ";
                    else
                        echo $FormatedField . "\r";
                }
                echo "\n";
            }
        }
    }
       
    //********************************************************************
    // ExportDatabase()
    //
    // Purpose:  Export the database information
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
    function ExportDatabase()
    {
        global $UserLevelDisabled, $UserLevelNormal, $UserLevelSuper, $UserLevelOffice; 
        global $UserLevelMaintenance, $UserLevelAdmin;
        global $SelectedTable;
		global $auth;
        
        // setup HTML headers for download
        //header("Content-length: $size");
        header("Content-type: text");
        header("Content-Disposition: attachment; filename=DatabaseExport.txt");

        // export the schedule database entries
        if (Instr(1, UCase($SelectedTable), "SCHEDULE") || UCase($SelectedTable) == "ALL") 
        {
            ExportScheduleDatabase();
        }
        
        // export the user database information
        if (Instr(1, UCase($SelectedTable), "USER") || UCase($SelectedTable) == "ALL") 
        {
            ExportUserDatabase();
        }
        
        // if the user has permission, export the aircraft table
        if (authGetUserLevel(getUserName(), $auth["admin"]) >= $UserLevelOffice)
        {
            // export the aircraft database
            if (Instr(1, UCase($SelectedTable), "AIRCRAFT") || UCase($SelectedTable) == "ALL") 
            {
                ExportAircraftDatabase();
            }
        }

        // export the flight database information
        if (Instr(1, UCase($SelectedTable), "FLIGHT") || UCase($SelectedTable) == "ALL") 
        {
            ExportFlightDatabase();
        }

        // export the charge database information
        if (Instr(1, UCase($SelectedTable), "CHARGE") || UCase($SelectedTable) == "ALL") 
        {
            ExportChargeDatabase();
        }
        
        // if the user has permission, export the inventory table
        if (authGetUserLevel(getUserName(), $auth["admin"]) >= $UserLevelOffice)
        {
            // export the inventory database
            if (Instr(1, UCase($SelectedTable), "INVENTORY") || UCase($SelectedTable) == "ALL") 
            {
                ExportInventoryDatabase();
            }
        }
    }
   
    // ################ END DEFINITION OF LOCAL FUNCTIONS ######################################
    
    // if we dont know the right date then make it up 
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
    if (empty($FromFlightday)) $FromFlightday = date("d", $FromFlightTime);
    if (empty($FromFlightmonth)) $FromFlightmonth = date("m", $FromFlightTime);
    if (empty($FromFlightyear)) $FromFlightyear = date("Y", $FromFlightTime);
    if (empty($ToFlightDay)) $ToFlightDay = date("d");
    if (empty($ToFlightMonth)) $ToFlightMonth = date("m");
    if (empty($ToFlightYear)) $ToFlightYear = date("Y");

    if (empty($FromChargeday)) $FromChargeday = date("d", $FromChargeTime);
    if (empty($FromChargemonth)) $FromChargemonth = date("m", $FromChargeTime);
    if (empty($FromChargeyear)) $FromChargeyear = date("Y", $FromChargeTime);
    if (empty($ToChargeDay)) $ToChargeDay = date("d");
    if (empty($ToChargeMonth)) $ToChargeMonth = date("m");
    if (empty($ToChargeYear)) $ToChargeYear = date("Y");

    if (empty($FromScheduleday)) $FromScheduleday = date("d", $FromScheduleTime);
    if (empty($FromSchedulemonth)) $FromSchedulemonth = date("m", $FromScheduleTime);
    if (empty($FromScheduleyear)) $FromScheduleyear = date("Y", $FromScheduleTime);
    if (empty($ToScheduleDay)) $ToScheduleDay = date("d");
    if (empty($ToScheduleMonth)) $ToScheduleMonth = date("m");
    if (empty($ToScheduleYear)) $ToScheduleYear = date("Y");

    if (empty($FromInventoryday)) $FromInventoryday = date("d", $FromInventoryTime);
    if (empty($FromInventorymonth)) $FromInventorymonth = date("m", $FromInventoryTime);
    if (empty($FromInventoryyear)) $FromInventoryyear = date("Y", $FromInventoryTime);
    if (empty($ToInventoryDay)) $ToInventoryDay = date("d");
    if (empty($ToInventoryMonth)) $ToInventoryMonth = date("m");
    if (empty($ToInventoryYear)) $ToInventoryYear = date("Y");
    
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
    
    if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal) && $User_Must_Login)
    {
    	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, $goback, "", $InstructorResource);
    	exit();
    }

    // this script will call itself whenever the download or finished button is pressed
    // we will check here for the checkout and cancel request before generating
    // the main screen information
    if(count($_POST) > 0 && $ExportUserData == "Download") 
    {

        // download button was selected, export the requested data
        ExportDatabase();

        exit();
    }
    else if(count($_POST) > 0 && $ExportCancel == "Finished") 
    {
        // user canceled the download, take them back to the last screen
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
                        "$makemodel");
        }
        exit();
    }

    // neither download or complete were selected display the main screen
    
    # print the page header
    print_header($day, $month, $year, $resource, $resource_id, $makemodel, "", "");
    
    // start the form
    echo "<FORM NAME='main' ACTION='ExportUserData.php' METHOD='POST'>";
    
    // set the default export selections
    SetDefaults();

    // start the table to display the export information
    echo "<center>";
    echo "<h2>Export Information</h2>";
    echo "<table border=0>";

    // field seperator
    echo "<tr>";
    echo "<td class=CL>";
    echo "Field Separator:";
    echo "</td>";
    echo "<td class=CL>";
    echo  "<input " .
                "type=text " .
                "NAME='FieldSeparator' " . 
                "ID='FieldSeparator' " .
                "align=left " . 
                "SIZE=10 " . 
                "VALUE='$FieldSeparator' " . 
                ">";
    echo "</td>";
    echo "</tr>";

    // if the user is a normal user, put in the user name for the currency fields
    if (authGetUserLevel(getUserName(), $auth["admin"]) <= $UserLevelNormal)
    {
        $MembersFromNameOfUser = getName();
        $MembersToNameOfUser = getName();
        echo "<tr><td class=CR><b>" . $lang["ExportName"] . "</b></td>";
        echo "<td class=CL>" . htmlentities($MembersFromNameOfUser) . "</td></tr>";
        echo "<input type=hidden name='MembersFromNameOfUser' value='" . $MembersFromNameOfUser . "'>";
        echo "<input type=hidden name='MembersToNameOfUser' value='" . $MembersToNameOfUser . "'>";
    }
    else 
    {
        // admin or super user
    
        // if it is a super user, make the default names the login name
        if (authGetUserLevel(getUserName(), $auth["admin"]) == $UserLevelSuper)
        {
            $MembersFromNameOfUser = getName();
            $MembersToNameOfUser = getName();
        }
         
        // member selection for export
        echo "<tr>";
        echo "<td class=CL>";
        echo $lang["ExportName"];
        echo "</td>";
        echo "<td class=CL>";
        BuildMemberSelector($MembersFromNameOfUser, false, "MembersFrom");
        echo $lang["ExportNameTo"];
        BuildMemberSelector($MembersToNameOfUser, false, "MembersTo");
        echo "</td>";
        echo "</tr>";
    }

    // finished with the table
    echo "</table>";
    echo "<br>";
    
    // build the export tables
    BuildExportTables();
            
    // save the goback parameters as hidden inputs so that they will
    // be retained after the submit
    if(isset($goback)) echo "<INPUT NAME='goback' TYPE='HIDDEN' VALUE='$goback'>\n";
    if(isset($GoBackParameters)) echo "<INPUT NAME='GoBackParameters' TYPE='HIDDEN' VALUE='$GoBackParameters'>\n";
   
    // generate the update and cancel buttons
    echo "<br>";
    echo "<table>";
    echo "<tr>";
    echo "<td><input name='ExportUserData' type=submit value='Download'></td>";
    echo "<td><input name='ExportCancel' type=submit value='Finished'></td>";
    echo "</tr>";
    echo "</table>";
    
    echo "</center>";
    
    // save the table selection for the actual export
    echo "<INPUT TYPE='hidden' NAME='SelectedTable' value='$SelectedTable'>";

    // end the form
    echo "</FORM>";
    
    include "trailer.inc";
?>

<!-- ############################### javascript procedures ######################### -->
<script type="text/javascript">
//<!--

//********************************************************************
// SetAllSchedule(form, Value)
//
// Purpose: Clear the checkboxes for the given controls.
//
// Inputs:
//   form - the form that contains the controls
//   Value - set to 0 to clear checkboxes, 1 to set
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function SetAllSchedule(form, Value)
{ 
    form.ScheduleName.checked = Value;
	form.create_by.checked = Value;
	form.create_time.checked = Value;
	form.description.checked = Value;
	form.end_time.checked = Value;
	form.name.checked = Value;
	form.Schedulephone_number.checked = Value;
	form.resource_id.checked = Value;
	form.start_time.checked = Value;
}

//********************************************************************
// SetAllUsers(form, Value, Privilege)
//
// Purpose: Clear the checkboxes for the given controls.
//
// Inputs:
//   form - the form that contains the controls
//   Value - set to 0 to clear checkboxes, 1 to set
//   Privilege - set to true if this is a privleged user
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function SetAllUsers(form, Value, Privilege)
{ 
    form.Birth_Date.checked = Value;
    form.Clearing_Authority.checked = Value;
    form.Contract_Expiration_Date.checked = Value;
    form.Contract_Number.checked = Value;
    form.Dues_Amount.checked = Value;
    form.Home_Phone.checked = Value;
    form.Manager_Message.checked = Value;
    form.Member_CFII_Instruction_Amount.checked = Value;
    form.Member_CFI_Instruction_Amount.checked = Value;
    form.Member_Commercial_Instruction_Amount.checked = Value;
    form.Member_Ground_Instruction_Amount.checked = Value;
    form.Member_Instrument_Instruction_Amount.checked = Value;
    form.Member_Notes.checked = Value;
    form.Member_Private_Instruction_Amount.checked = Value;
    form.Membership_Date.checked = Value;
    form.Notify_Address.checked = Value;
    form.Notify_City.checked = Value;
    form.Notify_First_Name.checked = Value;
    form.Notify_Last_Name.checked = Value;
    form.Notify_Middle_Initial.checked = Value;
    form.Notify_Phone1.checked = Value;
    form.Notify_Phone2.checked = Value;
    form.Notify_Relation.checked = Value;
    form.Notify_State.checked = Value;
    form.Notify_Zip.checked = Value;
    form.Organization.checked = Value;
    form.Password_Expires_Date.checked = Value;
    form.Resign_Date.checked = Value;
    form.address1.checked = Value;
    form.city.checked = Value;
    form.counter.checked = Value;
    form.email.checked = Value;
    form.first_name.checked = Value;
    form.last_login.checked = Value;
    form.last_name.checked = Value;
    form.middle_name.checked = Value;
    form.phone_number.checked = Value;
    form.state.checked = Value;
    form.user_level.checked = Value;
    form.username.checked = Value;
    form.zip.checked = Value;
        
    // if the user is privileged
    if (Privilege)
    {
        form.SSN.checked = Value;
        form.password.checked = Value;
        form.Credit_Card_Number.checked = Value;
        form.Credit_Card_Expiration.checked = Value;
    }
}

//********************************************************************
// SetAllAircraft(form, Value)
//
// Purpose: Clear the checkboxes for the given controls.
//
// Inputs:
//   form - the form that contains the controls
//   Value - set to 0 to clear checkboxes, 1 to set
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function SetAllAircraft(form, Value)
{ 
    form.n_number.checked = Value;
    form.serial_number.checked = Value;
    form.tach1.checked = Value;
    form.hourly_cost.checked = Value;
    form.rental_fee.checked = Value;
    form.empty_weight.checked = Value;
    form.max_gross.checked = Value;
    form.AircraftYear.checked = Value;
    form.code_id.checked = Value;
    form.make_id.checked = Value;
    form.model_id.checked = Value;
    form.ifr_cert.checked = Value;
    form.status.checked = Value;
    form.Aircraft_Color.checked = Value;
    form.Hrs_Till_100_Hr.checked = Value;
    form.Hundred_Hr_Tach.checked = Value;
    form.Annual_Due.checked = Value;
    form.Default_Fuel_Gallons.checked = Value;
    form.Full_Fuel_Gallons.checked = Value;
    form.Va_Max_Weight.checked = Value;
    form.Current_Hobbs.checked = Value;
    form.Current_User.checked = Value;
    form.CurrentKeycode.checked = Value;
    form.Aircraft_Owner_Name.checked = Value;
    form.Aircraft_Owner_Address.checked = Value;
    form.Aircraft_Owner_City.checked = Value;
    form.Aircraft_Owner_State.checked = Value;
    form.Aircraft_Owner_Zip.checked = Value;
    form.Aircraft_Owner_Contract.checked = Value;
    form.Aircraft_Owner_Phone1.checked = Value;
    form.Aircraft_Owner_Phone2.checked = Value;
    form.Aircraft_Remarks.checked = Value;
    form.Aircraft_Airspeed.checked = Value;
    form.Flight_ID.checked = Value;
    form.Oil_Type.checked = Value;
}

//********************************************************************
// SetAllFlights(form, Value)
//
// Purpose: Clear the checkboxes for the given controls.
//
// Inputs:
//   form - the form that contains the controls
//   Value - set to 0 to clear checkboxes, 1 to set
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function SetAllFlights(form, Value)
{ 
    form.FlightKeycode.checked = Value;
    form.FlightDate.checked = Value;
    form.FlightAircraft.checked = Value;
    form.FlightModelId.checked = Value;
    form.Begin_Hobbs.checked = Value;
    form.End_Hobbs.checked = Value;
    form.Hobbs_Elapsed.checked = Value;
    form.Aircraft_Rate.checked = Value;
    form.Aircraft_Cost.checked = Value;
    form.Begin_Tach.checked = Value;
    form.End_Tach.checked = Value;
    form.Day_Time.checked = Value;
    form.Night_Time.checked = Value;
    form.Instruction_Type.checked = Value;
    form.Dual_Time.checked = Value;
    form.Dual_PP_Time.checked = Value;
    form.Instruction_Rate.checked = Value;
    form.Instructor_Charge.checked = Value;
    form.Instructor_Keycode.checked = Value;
    form.Student_Keycode.checked = Value;
    form.Day_Landings.checked = Value;
    form.Night_Landings.checked = Value;
    form.Navigation_Intercepts.checked = Value;
    form.Holding_Procedures.checked = Value;
    form.Instrument_Approach.checked = Value;
    form.Fuel_Cost.checked = Value;
    form.Local_Fuel.checked = Value;
    form.Local_Fuel_Cost.checked = Value;
    form.Cross_Country_Fuel.checked = Value;
    form.Cross_Country_Fuel_Credit.checked = Value;
    form.Oil.checked = Value;
    form.Oil_Rate.checked = Value;
    form.Oil_Cost.checked = Value;
    form.Owner_Rate.checked = Value;
    form.Owner_Reimbursement.checked = Value;
    form.Cleared_By.checked = Value;
}

//********************************************************************
// SetAllCharges(form, Value)
//
// Purpose: Clear the checkboxes for the given controls.
//
// Inputs:
//   form - the form that contains the controls
//   Value - set to 0 to clear checkboxes, 1 to set
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function SetAllCharges(form, Value)
{ 
    form.ChargeKeyCode.checked = Value;
    form.ChargeDate.checked = Value;
    form.Part_Number.checked = Value;
    form.Part_Description.checked = Value;
    form.Quantity.checked = Value;
    form.Price.checked = Value;
    form.Total_Price.checked = Value;
    form.ChargeCategory.checked = Value;
    form.Unit_Price.checked = Value;
}

//********************************************************************
// SetAllInventory(form, Value)
//
// Purpose: Clear the checkboxes for the given controls.
//
// Inputs:
//   form - the form that contains the controls
//   Value - set to 0 to clear checkboxes, 1 to set
//
// Outputs:
//   none
//
// Returns:
//   none
//
// Notes:
//
//*********************************************************************
function SetAllInventory(form, Value)
{ 
	form.InventoryCategory.checked = Value;
	form.InventoryDate.checked = Value;
	form.InventoryDescription.checked = Value;
	form.InventoryPart_Number.checked = Value;
	form.InventoryPosition.checked = Value;
	form.InventoryQuantity_In_Stock.checked = Value;
	form.InventoryReorder_Quantity.checked = Value;
	form.InventoryRetail_Price.checked = Value;
	form.InventoryUnit_Price.checked = Value;
	form.Inventory_Type.checked = Value;
}

//-->
</script>
