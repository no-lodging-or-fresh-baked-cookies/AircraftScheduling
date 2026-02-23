<?php
//-----------------------------------------------------------------------------
// 
// DatabaseConstants
// 
// PURPOSE: Contains constants to interface with the database.
// 
// PARAMETERS:
//      none
// 
// REQUREMENTS IMPLEMENTED:
//		none
//
// COMMENTS:
// 
// -----------------------------------------------------------------------------
 
// inventory recordset fields   
$FieldCounter = 0;
$InventoryDate_offset = $FieldCounter++;
$InventoryPart_Number_offset = $FieldCounter++;
$InventoryDescription_offset = $FieldCounter++;
$InventoryUnit_Price_offset = $FieldCounter++;
$InventoryRetail_Price_offset = $FieldCounter++;
$InventoryQuantity_In_Stock_offset = $FieldCounter++;
$InventoryReorder_Quantity_offset = $FieldCounter++;
$InventoryPosition_offset = $FieldCounter++;
$InventoryCategory_offset = $FieldCounter++;
$InventoryInventory_Type_offset = $FieldCounter++;
   
// database table field offsets within the user's record
$FieldCounter = 0;
$person_id_offset = $FieldCounter++;
$first_name_offset = $FieldCounter++;
$middle_name_offset = $FieldCounter++;
$last_name_offset = $FieldCounter++;
$title_offset = $FieldCounter++;
$email_offset = $FieldCounter++;
$username_offset = $FieldCounter++;
$password_offset = $FieldCounter++;
$user_level_offset = $FieldCounter++;
$counter_offset = $FieldCounter++;
$last_login_offset = $FieldCounter++;
$address1_offset = $FieldCounter++;
$address2_offset = $FieldCounter++;
$city_offset = $FieldCounter++;
$state_offset = $FieldCounter++;
$zip_offset = $FieldCounter++;
$phone_number_offset = $FieldCounter++;
$SSN_offset = $FieldCounter++;
$Organization_offset = $FieldCounter++;
$Work_Ext_offset = $FieldCounter++;
$Home_Phone_offset = $FieldCounter++;
$Dues_Amount_offset = $FieldCounter++;
$Ground_Instruction_Amount_offset = $FieldCounter++;
$Private_Instruction_Amount_Offset = $FieldCounter++;
$Instrument_Instruction_Amount_offset = $FieldCounter++;
$Commercial_Instruction_Amount_offset = $FieldCounter++;
$CFI_Instruction_Amount_offset = $FieldCounter++;
$CFII_Instruction_Amount_offset = $FieldCounter++;
$Contract_Number_offset = $FieldCounter++;
$Notify_First_Name_offset = $FieldCounter++;
$Notify_Middle_Initial_offset = $FieldCounter++;
$Notify_Last_Name_offset = $FieldCounter++;
$Notify_Relation_offset = $FieldCounter++;
$Notify_Address_offset = $FieldCounter++;
$Notify_City_offset = $FieldCounter++;
$Notify_State_offset = $FieldCounter++;
$Notify_Zip_offset = $FieldCounter++;
$Notify_Phone1_offset = $FieldCounter++;
$Notify_Phone2_offset = $FieldCounter++;
$Contract_Expiration_Date_offset = $FieldCounter++;
$Rules_Field_offset = $FieldCounter++;
$Member_Notes_offset = $FieldCounter++;
$Credit_Card_Number_offset = $FieldCounter++;
$Credit_Card_Expiration_offset = $FieldCounter++;
$Manager_Message_offset = $FieldCounter++;
$Membership_Date_offset = $FieldCounter++;
$Resign_Date_offset = $FieldCounter++;
$Clearing_Authority_offset = $FieldCounter++;
$Password_Expires_Date_offset = $FieldCounter++;
$Allow_Phone_Number_Display_offset = $FieldCounter++;
$OldUsername_offset = $FieldCounter++;
$InstructorOfRecord_offset = $FieldCounter++;

// aircraft database table field offsets within the aircraft record
$FieldCounter = 0;
$aircraft_id_offset = $FieldCounter++;
$n_number_offset = $FieldCounter++;
$serial_number_offset = $FieldCounter++;
$panel_picture_offset = $FieldCounter++;
$picture_offset = $FieldCounter++;
$hobbs_offset = $FieldCounter++;
$tach1_offset = $FieldCounter++;
$tach2_offset = $FieldCounter++;
$hourly_cost_offset = $FieldCounter++;
$rental_fee_offset = $FieldCounter++;
$empty_weight_offset = $FieldCounter++;
$max_gross_offset = $FieldCounter++;
$year_offset = $FieldCounter++;
$code_id_offset = $FieldCounter++;
$make_id_offset = $FieldCounter++;
$model_id_offset = $FieldCounter++;
$resource_id_offset = $FieldCounter++;
$description_offset = $FieldCounter++;
$ifr_cert_offset = $FieldCounter++;
$min_pilot_cert_offset = $FieldCounter++;
$status_offset = $FieldCounter++;
$Aircraft_Color_offset = $FieldCounter++;
$Hrs_Till_100_Hr_offset = $FieldCounter++;
$Hundred_Hr_Tach_offset = $FieldCounter++;
$Annual_Due_offset = $FieldCounter++;
$Fuel_Arm_offset = $FieldCounter++;
$Default_Fuel_Gallons_offset = $FieldCounter++;
$Full_Fuel_Gallons_offset = $FieldCounter++;
$Front_Seat_Arm_offset = $FieldCounter++;
$Rear_Seat_1_Arm_offset = $FieldCounter++;
$Rear_Seat_2_Arm_offset = $FieldCounter++;
$Front_Seat_Weight_offset = $FieldCounter++;
$Rear_Seat_1_Weight_offset = $FieldCounter++;
$Rear_Seat_2_Weight_offset = $FieldCounter++;
$Baggage_Area_1_Arm_offset = $FieldCounter++;
$Baggage_Area_2_Arm_offset = $FieldCounter++;
$Baggage_Area_3_Arm_offset = $FieldCounter++;
$Baggage_Area_1_Weight_offset = $FieldCounter++;
$Baggage_Area_2_Weight_offset = $FieldCounter++;
$Baggage_Area_3_Weight_offset = $FieldCounter++;
$Aux_Fuel_Arm_offset = $FieldCounter++;
$Aux_Fuel_Gallons_offset = $FieldCounter++;
$Aircraft_Arm_offset = $FieldCounter++;
$Aircraft_Weight_offset = $FieldCounter++;
$Va_Max_Weight_offset = $FieldCounter++;
$Current_Hobbs_offset = $FieldCounter++;
$Current_User_offset = $FieldCounter++;
$CurrentKeycode_offset = $FieldCounter++;
$Aircraft_Owner_Name_offset = $FieldCounter++;
$Aircraft_Owner_Address_offset = $FieldCounter++;
$Aircraft_Owner_City_offset = $FieldCounter++;
$Aircraft_Owner_State_offset = $FieldCounter++;
$Aircraft_Owner_Zip_offset = $FieldCounter++;
$Aircraft_Owner_Contract_offset = $FieldCounter++;
$Aircraft_Owner_Phone1_offset = $FieldCounter++;
$Aircraft_Owner_Phone2_offset = $FieldCounter++;
$Aircraft_Remarks_offset = $FieldCounter++;
$Aircraft_Airspeed_offset = $FieldCounter++;
$Flight_ID_offset = $FieldCounter++;
$Oil_Type_offset = $FieldCounter++;
$WB_Fields_offset = $FieldCounter++;
$AircraftCleared_By_offset = $FieldCounter++;
$TrackSquawks_offset = $FieldCounter++;
$ICAO_Equipment_Codes_offset = $FieldCounter++;
$ICAO_Wake_Turb_offset = $FieldCounter++;
$ICAO_Number_Aircraft_offset = $FieldCounter++;
$ICAO_Flight_Type_offset = $FieldCounter++;
$ICAO_ADSB_Type_offset = $FieldCounter++;
$ICAO_Transponder_offset = $FieldCounter++;

// CurrencyFields database table field offsets
$FieldCounter = 0;
$Currency_Field_Name_offset = $FieldCounter++;
$Currency_Field_Type_offset = $FieldCounter++;                
$Student_offset = $FieldCounter++;
$Private_Under_200_offset = $FieldCounter++;
$Private_Over_200_offset = $FieldCounter++;
$Instrument_offset = $FieldCounter++;
$CFI_offset = $FieldCounter++;

// CurrencyRules database table field offsets
$FieldCounter = 0;
$Currency_Rules_ID_offset = $FieldCounter++;
$Currency_Rules_Item_offset = $FieldCounter++;
$Currency_Rules_Pass_Criteria_offset = $FieldCounter++;
$Currency_Rules_Expires_Month_End_offset = $FieldCounter++;
$Currency_Rules_Student_offset = $FieldCounter++;
$Currency_Rules_Private_Under_200_offset = $FieldCounter++;
$Currency_Rules_Private_Over_200_offset = $FieldCounter++;
$Currency_Rules_Instrument_offset = $FieldCounter++;
$Currency_Rules_CFI_offset = $FieldCounter++;
    
// squawk database table field offsets within the squawk record
$FieldCounter = 0;
$SquawkAircraft_offset = $FieldCounter++;
$SquawkKeyCode_offset = $FieldCounter++;
$SquawkDate_offset = $FieldCounter++;
$SquawkRepair_Date_offset = $FieldCounter++;
$SquawkGrounding_offset = $FieldCounter++;
$SquawkDescription_offset = $FieldCounter++;
$SquawkRepair_Description_offset = $FieldCounter++;
$SquawkInitial_Tach_offset = $FieldCounter++;
$SquawkRepair_Tach_offset = $FieldCounter++;
$SquawkMechanic_offset = $FieldCounter++;

// flight recordset fields
$FieldCounter = 0;
$Keycode_offset = $FieldCounter++;
$Date_offset = $FieldCounter++;
$Aircraft_offset = $FieldCounter++;
$Flightmodel_id_offset = $FieldCounter++;
$Begin_Hobbs_offset = $FieldCounter++;
$End_Hobbs_offset = $FieldCounter++;
$Hobbs_Elapsed_offset = $FieldCounter++;
$Aircraft_Rate_offset = $FieldCounter++;
$Aircraft_Cost_offset = $FieldCounter++;
$Begin_Tach_offset = $FieldCounter++;
$End_Tach_offset = $FieldCounter++;
$Day_Time_offset = $FieldCounter++;
$Night_Time_offset = $FieldCounter++;
$Instruction_Type_offset = $FieldCounter++;
$Dual_Time_offset = $FieldCounter++;
$Dual_PP_Time_offset = $FieldCounter++;
$Instruction_Rate_offset = $FieldCounter++;
$Instructor_Charge_offset = $FieldCounter++;
$Instructor_Keycode_offset = $FieldCounter++;
$Student_Keycode_offset = $FieldCounter++;
$Day_Landings_offset = $FieldCounter++;
$Night_Landings_offset = $FieldCounter++;
$Navigation_Intercepts_offset = $FieldCounter++;
$Holding_Procedures_offset = $FieldCounter++;
$Instrument_Approach_offset = $FieldCounter++;
$Fuel_Cost_offset = $FieldCounter++;
$Local_Fuel_offset = $FieldCounter++;
$Local_Fuel_Cost_offset = $FieldCounter++;
$Cross_Country_Fuel_offset = $FieldCounter++;
$Cross_Country_Fuel_Credit_offset = $FieldCounter++;
$Oil_offset = $FieldCounter++;
$Oil_Rate_offset = $FieldCounter++;
$Oil_Cost_offset = $FieldCounter++;
$Owner_Rate_offset = $FieldCounter++;
$Owner_Reimbursement_offset = $FieldCounter++;
$Cleared_By_offset = $FieldCounter++;

// charges recordset fields   
$FieldCounter = 0;
$ChargesKeyCode_offset = $FieldCounter++;
$ChargesDate_offset = $FieldCounter++;
$Part_Number_offset = $FieldCounter++;
$Part_Description_offset = $FieldCounter++;
$Quantity_offset = $FieldCounter++;
$Price_offset = $FieldCounter++;
$Total_Price_offset = $FieldCounter++;
$Category_offset = $FieldCounter++;
$Unit_Price_offset = $FieldCounter++;

// catetory field names
$FieldCounter = 0;
$CategoryName_offset = $FieldCounter++;
$CategoryGLAC_offset = $FieldCounter++;
$CategoryCan_Be_Changed_offset = $FieldCounter++;
$CategoryRecord_ID_offset = $FieldCounter++;

// AircraftScheduling_model field names
$FieldCounter = 0;
$Modelmodel_id_offset = $FieldCounter++;
$Modelmodel_offset = $FieldCounter++;
$Modelhourly_cost_offset = $FieldCounter++;
$Modelrental_fee_offset = $FieldCounter++;

?>
