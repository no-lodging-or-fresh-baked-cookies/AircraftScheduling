<?php
//-----------------------------------------------------------------------------
// 
// del_entry.php
// 
// PURPOSE: Deletes an entry or repeating entry from the schedule.
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
//      id - database ID of the entry to delete
//      all - select all resources
//      series - true if the entry to delete is a series
//      InstructorEntryID - database ID for an instructor for the delete
//      RecordType - record type for the journal desctiption
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
    include "$dbsys.inc";
    include "AircraftScheduling_auth.inc";
    include "functions.inc";
    include "AircraftScheduling_sql.inc";
    
    // initialize variables
    $InstructorResource = "";
    $InstructorEntryID = "";
    $GoBackParameters = "";
    
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
    if(isset($rdata["id"])) $id = $rdata["id"];
    if(isset($rdata["all"])) $all = $rdata["all"];
    if(isset($rdata["series"])) $series = $rdata["series"];
    if(isset($rdata["InstructorEntryID"])) $InstructorEntryID = $rdata["InstructorEntryID"];
    if(isset($rdata["pview"])) $pview = $rdata["pview"];
    if(isset($rdata["RecordType"])) $RecordType = $rdata["RecordType"];
    if(isset($rdata["goback"])) $goback = $rdata["goback"];
    if(isset($rdata["GoBackParameters"])) $GoBackParameters = $rdata["GoBackParameters"];
    
    #If we dont know the right date then make it up 
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
    
    // are we authorized to delete these records
    if (getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
    {
        // delete the aircraft entry
        if ($info = AircraftSchedulingGetEntryInfo($id))
        {
            $day   = strftime("%d", $info["start_time"]);
            $month = strftime("%m", $info["start_time"]);
            $year  = strftime("%Y", $info["start_time"]);
            $resourcename  = AircraftSchedulingGetResourceName($info["resource_id"]);
            if ($resourcename == "Aircraft")
                $ScheduleName = sql_query1("SELECT n_number FROM AircraftScheduling_aircraft WHERE resource_id=" . $info["resource_id"]);
            else
                $ScheduleName = sql_query1(
                                        "SELECT $DatabaseNameFormat 
                                        FROM AircraftScheduling_person LEFT JOIN AircraftScheduling_instructors USING (person_id) 
                                        WHERE resource_id=" . $info["resource_id"]);
    
            // log the delete action in the journal
            if ($series)
                $Description = "Deleting repeating entry ";
            else
                $Description = "Deleting ";
                $Description = $Description .
                        $RecordType . " " . $resourcename . " " . $ScheduleName . " " .
                        " for user " . $info["name"] .
                        " at " .
                        date("H:i", $info["start_time"]) . " - " . date("H:i", $info["end_time"]) . 
                        " on " . strftime('%m/%d/%y', $info["start_time"]);
            CreateJournalEntry(strtotime("now"), getUserName(), $Description);
            
            sql_begin();
            $result = AircraftSchedulingDelEntry(getName(), $id, $series, 1);
            sql_commit();
            if ($result)
            {
                // delete the instructor entry (if there is one)
                if ($info = AircraftSchedulingGetEntryInfo($InstructorEntryID))
                {
                    $day   = strftime("%d", $info["start_time"]);
                    $month = strftime("%m", $info["start_time"]);
                    $year  = strftime("%Y", $info["start_time"]);
                    $resourcename  = AircraftSchedulingGetResourceName($info["resource_id"]);
                    if ($resourcename == "Aircraft")
                        $ScheduleName = sql_query1("SELECT n_number FROM AircraftScheduling_aircraft WHERE resource_id=" . $info["resource_id"]);
                    else
                        $ScheduleName = sql_query1(
                                                "SELECT $DatabaseNameFormat 
                                                FROM AircraftScheduling_person LEFT JOIN AircraftScheduling_instructors USING (person_id) 
                                                WHERE resource_id=" . $info["resource_id"]);
    
                    // log the delete action in the journal
                    if ($series)
                        $Description = "Deleting repeating instructor entry ";
                    else
                        $Description = "Deleting instructor ";
                        $Description = $Description . 
                                $ScheduleName . " " .
                                " for user " . $info["name"] .
                                " at " .
                                date("H:i", $info["start_time"]) . " - " . date("H:i", $info["end_time"]) . 
                                " on " . strftime('%m/%d/%y', $info["start_time"]);
                    CreateJournalEntry(strtotime("now"), getUserName(), $Description);
                
                    sql_begin();
                    $result = AircraftSchedulingDelEntry(getName(), $InstructorEntryID, $series, 1);
                    sql_commit();
                    if ($result)
                    {
                        session_write_close();
                        Header("Location: index.php?day=$day&month=$month&year=$year&resource=$resource&InstructorResource=$InstructorResource$makemodel");
                        exit();
                    }
                    else
                    {
                        // delete failed, tell the user     
                        print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
                        ?>
                        <H1><?echo "Can't delete instructor entry.<BR>
                            This may be because the schedule has already passed or <BR>
                            you don't have permission."?></H1>
                        <P>
                        
                        <?php
                        // generate return URL
                        GenerateReturnURL(
                                            $goback, 
                                            CleanGoBackParameters($GoBackParameters));
                        ?>
                        </P>
                        </BODY>
                        </HTML>
                    
                        <?php
                    }
                }
                session_write_close();
                if(isset($goback))
                {
                    if (!empty($GoBackParameters))
                            header("Location: $goback" . CleanGoBackParameters($GoBackParameters));
                    else
                        header("Location: " . $goback . "?" .
                                    "day=$day&month=$month&year=$year" .
                                    "&resource=$resource" .
                                    "&resource_id=$resource_id" .
                                    "&InstructorResource=$InstructorResource" .
                                    "$makemodel");
                }
                else
                {
                    Header("Location: index.php?" .
                                "day=$day&month=$month&year=$year" .
                                "&resource=$resource" .
                                "&resource_id=$resource_id" .
                                "&InstructorResource=$InstructorResource" .
                                "$makemodel");
                }
                exit();
            }
            else
            {
                // delete failed, tell the user     
                print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");
                ?>
                <H1><?echo "Can't delete entry.<BR>
                    This may be because the schedule has already passed or <BR>
                    you don't have permission."?></H1>
                <P>
                
                <?php
                // generate return URL
                GenerateReturnURL(
                                    $goback, 
                                    CleanGoBackParameters($GoBackParameters));
                ?>
                </P>
                </BODY>
                </HTML>
                
                <?php
            }
        }
    }
    else
    {
        // If you got this far then we got an access denied.
        showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
    }
?>
