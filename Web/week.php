<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: week.php,v 1.9 2001/12/20 07:02:27 mbarclay Exp $

# AircraftScheduling/week.php - Week-at-a-time view

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";
include "calendar.inc";
include "AircraftScheduling_sql.inc";
require_once("DatabaseFunctions.inc");

// initialize variables
$all = '';
$area = '';
$sql = '';
$InstructorResource = "";
$res = 0;
$res2 = 0;

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
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["timetohighlight"])) $timetohighlight = $rdata["timetohighlight"];
if(isset($rdata["resource_id"])) $resource_id = $rdata["resource_id"];
if(isset($rdata["all"])) $all = $rdata["all"];
if(isset($rdata["area"])) $area = $rdata["area"];
if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];

if (empty($debug_flag)) $debug_flag = 0;

# If we don't know the right date then use today:
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

# Set the date back to the previous $weekstarts day (Sunday, if 0):
$time = mktime(0, 0, 0, $month, $day, $year);
if (($weekday = (date("w", $time) - $weekstarts + 7) % 7) > 0)
{
    $time -= $weekday * 86400;
    $day   = date("d", $time);
    $month = date("m", $time);
    $year  = date("Y", $time);
}

if($make) $makemodel = "&make=" . str_replace(" ", "+", $make);
else if($model) $makemodel = "&model=" . str_replace(" ", "+", $model);
else if($certificate) $makemodel = "&certificate=$certificate";
else { $all=1; $makemodel = "&all=1"; }

if (empty($resource) || $resource == "None")
    $resource = get_default_resource();

# Note $resource_id will be -1 if there are no rooms; this is checked for below.
        
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

# print the page header
print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");

// start the form
echo "<FORM NAME='main'>";

# Define the start of day and end of day (default is 7-7)
$am7=mktime($morningstarts, 00 ,0 ,$month ,$day ,$year);
$pm7=mktime($eveningends, $eveningends_minutes, 0, $month, $day, $year);

# Start at end of week:
$week_midnight = mktime(0, 0, 0, $month, $day, $year);
$week_start = $am7;
$week_end = mktime($eveningends, $eveningends_minutes, 0, $month, $day+6, $year);

if ( $pview != 1 ) 
{
    # Table with areas, rooms, minicals.
    echo "<table width=\"100%\"><tr>";
    $this_resource_name = "";
    $this_item_name = "";

    # Show all areas
    echo "<td width=\"25%\">";
    $params = array( "make" => $make, "model" => $model, "all" => $all, "type" => $certificate);
    displayResourceMenu("WEEK", $year, $month, $day, $resource, $InstructorResource, $params, $makemodel);
    echo "</td>";

    # Show all items in the current area
    echo "<td width=\"35%\">";
}

$sql = '';
if($resource == "Aircraft")
{
    // get the schedule information for this resource
    $sql = "SELECT AircraftScheduling_resource.resource_id, n_number, status 
            FROM AircraftScheduling_resource LEFT JOIN AircraftScheduling_aircraft 
            USING (resource_id)
            WHERE n_number != '' ";
    if($make)
    {
        // get the resource information from the database
        $sql_resource = "SELECT make_id
              FROM AircraftScheduling_make 
              WHERE make='$make'";
        $res = sql_query($sql_resource);
        if (! $res) fatal_error(0, sql_error());
        $row = sql_row($res, 0);
        $SchedulableID = $row[0];

        $sql .= " AND AircraftScheduling_aircraft.make_id=$SchedulableID";
    }
    if($model)
    {
        // get the resource information from the database
        $sql_resource = "SELECT model_id
              FROM AircraftScheduling_model 
              WHERE model='$model' ";
        $res = sql_query($sql_resource);
        if (! $res) fatal_error(0, sql_error());
        $row = sql_row($res, 0);
        $SchedulableID = $row[0];

        $sql .= " AND AircraftScheduling_aircraft.model_id=$SchedulableID";
    }
    $sql .= " ORDER BY 2";
    $res = sql_query($sql);
    $res2 = 0;
}
else if($resource == "Instructor")
{
    $sql = "SELECT b.resource_id, $DatabaseNameFormat 
            FROM 
                AircraftScheduling_person a, 
                AircraftScheduling_resource b, 
                AircraftScheduling_instructors c
            WHERE 
                b.resource_id=c.resource_id AND 
                c.person_id=a.person_id ";
    $sql .= " ORDER BY last_name";
    $res = sql_query($sql);
    $res2 = 0;
}
else if($resource == "InstructorAG")
{
    $sql = "SELECT b.resource_id, $DatabaseNameFormat 
            FROM 
                AircraftScheduling_person a, 
                AircraftScheduling_resource b, 
                AircraftScheduling_instructors c
            WHERE 
                b.resource_id=c.resource_id AND 
                c.person_id=a.person_id AND 
                ucase(left(last_name, 1)) >= 'A' AND 
                ucase(left(last_name, 1)) <= 'G'";
    $sql .= " ORDER BY last_name";
    $res = sql_query($sql);
    $res2 = 0;
}
else if($resource == "InstructorHM")
{
    $sql = "SELECT b.resource_id, $DatabaseNameFormat 
            FROM 
                AircraftScheduling_person a, 
                AircraftScheduling_resource b, 
                AircraftScheduling_instructors c
            WHERE 
                b.resource_id=c.resource_id AND 
                c.person_id=a.person_id AND 
                ucase(left(last_name, 1)) >= 'H' AND 
                ucase(left(last_name, 1)) <= 'M'";
    $sql .= " ORDER BY last_name";
    $res = sql_query($sql);
    $res2 = 0;
}
else if($resource == "InstructorNS")
{
    $sql = "SELECT b.resource_id, $DatabaseNameFormat 
            FROM 
                AircraftScheduling_person a, 
                AircraftScheduling_resource b, 
                AircraftScheduling_instructors c
            WHERE 
                b.resource_id=c.resource_id AND 
                c.person_id=a.person_id AND 
                ucase(left(last_name, 1)) >= 'N' AND 
                ucase(left(last_name, 1)) <= 'S'";
    $sql .= " ORDER BY last_name";
    $res = sql_query($sql);
    $res2 = 0;
}
else if($resource == "InstructorTZ")
{
    $sql = "SELECT b.resource_id, $DatabaseNameFormat 
            FROM 
                AircraftScheduling_person a, 
                AircraftScheduling_resource b, 
                AircraftScheduling_instructors c
            WHERE 
                b.resource_id=c.resource_id AND 
                c.person_id=a.person_id AND 
                ucase(left(last_name, 1)) >= 'T' AND 
                ucase(left(last_name, 1)) <= 'Z'";
    $sql .= " ORDER BY last_name";
    $res = sql_query($sql);
    $res2 = 0;
}
else if($resource == "Everything") 
{
    // get the schedule information for this resource
    $sql = "SELECT AircraftScheduling_resource.resource_id, n_number 
            FROM AircraftScheduling_resource LEFT JOIN AircraftScheduling_aircraft 
            USING (resource_id)
            WHERE n_number != '' ";
    $res = sql_query($sql);
    $sql2 = "SELECT b.resource_id, $DatabaseNameFormat 
            FROM AircraftScheduling_person a, AircraftScheduling_resource b, AircraftScheduling_instructors c
            WHERE b.resource_id=c.resource_id AND c.person_id=a.person_id ";
    $sql2 .= " ORDER BY 2";
    $res2 = sql_query($sql2);
}

// ?????
if($certificate) 
{
    $cert = expand_certificate_abreviation($certificate);
    $sql .= " AND AircraftScheduling_certificates.certificate = '$cert' AND AircraftScheduling_certificates.certificate_id=AircraftScheduling_pilot_certificates.certificate_id AND
            AircraftScheduling_pilot_certificates.pilot_id=a.person_id AND a.person_id=c.person_id";
    $sql .= " order by 2";
}
if (! $res) echo sql_error();
if (! $res2) echo sql_error();

// if we have anything to display, start the drop down box
if ($res || $res2)
{
    if ( $pview != 1 )
        echo "<SELECT NAME='DisplayResourceSelect' onChange=SelectDisplayEntity()>";
}

// add the results of the first query to the drop down box
if ($res)
{
    $LinkEntriesCount = 0;
    for ($i = 0; ($row = sql_row($res, $i)); $i++)
    {
        if ($debug_flag)
            echo "<br>DEBUG: row[0]: $row[0] row[1]: $row[1]\n";
        
        if(empty($resource_id))
        {
                // only use the resource ID if we have a valid tail number
                if (!empty($row[1])) $resource_id = $row[0];
        }

        if ($row[0] == $resource_id)
        {
            $this_item_name = htmlspecialchars($row[1]);
            if ( $pview != 1 ) echo "<OPTION SELECTED" . ">$row[1]";
        }
        else 
            if ( $pview != 1 ) echo "<OPTION" . ">$row[1]";
            
        // save the link entry for building the java script later
        $LinkArray[$LinkEntriesCount] = 
            "week.php?year=$year&month=$month&day=$day&resource=$resource$makemodel&resource_id=$row[0]";
        $LinkEntriesCount = $LinkEntriesCount + 1;
   }
}
    
// add the results of the second query to the drop down box
if ($res2)
{
    for ($i = 0; ($row = sql_row($res2, $i)); $i++)
    {
        if ($debug_flag)
            echo "<br>DEBUG: row[0]: $row[0] row[1]: $row[1]\n";
        
        if(empty($resource_id))
        {
                // only use the resource ID if we have a valid tail number
                if (!empty($row[1])) $resource_id = $row[0];
        }

        if ($row[0] == $resource_id)
        {
            $this_item_name = htmlspecialchars($row[1]);
            if ( $pview != 1 ) echo "<OPTION SELECTED" . ">$row[1]";
        }
        else 
            if ( $pview != 1 ) echo "<OPTION" . ">$row[1]";
            
        // save the link entry for building the java script later
        $LinkArray[$LinkEntriesCount] = 
            "week.php?year=$year&month=$month&day=$day&resource=$resource$makemodel&resource_id=$row[0]";
        $LinkEntriesCount = $LinkEntriesCount + 1;
    }
}

// if we had anything to display, end the drop down box
if ($res || $res2)
{
    if ( $pview != 1 ) echo "</SELECT>";
    
    // setup script to select link when the aircraft selection changes
    echo "<SCRIPT LANGUAGE=\"JavaScript\">";
    echo "function SelectDisplayEntity()";
    echo "{";
    echo "  var LinkEntries = new Array;";

    // build the link entry array
    $LinkEntriesCount = 0;
    foreach($LinkArray as $Item)
    {
        echo "  LinkEntries[" . $LinkEntriesCount . "] = \"" . $Item . "\";";
        $LinkEntriesCount = $LinkEntriesCount + 1;
    }
        
	echo "	window.location.href = LinkEntries[document.forms['main'].DisplayResourceSelect.selectedIndex];";
    echo "}";
    echo "</SCRIPT>";
}

if ( $pview != 1 ) {
    echo "</td>\n";

    #Draw the three month calendars - Note they link to day view, not week.
    minicals($year, $month, $day, $resource, $InstructorResource, $makemodel);
    echo "</tr></table>";
    # Don't continue if this area has no rooms:
    if ($resource_id <= 0)
    {
        echo "<h1>$lang[no_rooms_for_area]</h1>";
        include "trailer.inc";
        exit;
    }
}

$display_page = sql_query1("SELECT display_page FROM AircraftScheduling_schedulable WHERE name='$resource'");

# Show area and item
echo "<h2 align=center>";
$ResourceName = AircraftSchedulingGetResourceName($resource_id);
if($ResourceName == "Aircraft")
{
    // look up the aircraft display page in the database
    $AircraftMake = sql_query1(
                        "SELECT resource_model 
                        FROM AircraftScheduling_resource 
                        WHERE resource_name='$this_item_name'");
    $display_page = sql_query1("SELECT display_page FROM AircraftScheduling_schedulable WHERE name='Aircraft'");
}
else
{
    $display_page = sql_query1("SELECT display_page FROM AircraftScheduling_schedulable WHERE name='Instructor'");
}

// put a link to display the aircraft or instructor information
if($display_page != -1)
{
    if($ResourceName == "Aircraft")
        echo "<a href=$display_page?goback=" . GetScriptName() . 
                "&year=$year&month=$month&day=$day" .
		        "&resource=$resource" .
		        "&resource_id=$resource_id" . 
                "&name=".str_replace(" ", "+",$this_item_name).">$AircraftMake - $this_item_name</a>";
    else
        echo "<a href=$display_page?goback=" . GetScriptName() . 
                "&year=$year&month=$month&day=$day" .
		        "&resource=$resource" .
		        "&resource_id=$resource_id" . 
                "&name=".str_replace(" ", "+",$this_item_name).">$ResourceName - $this_item_name</a>";
}
else
{
    if($ResourceName == "Aircraft")
        echo "$AircraftMake - $this_item_name";
    else if($ResourceName == "Instructor")
        echo "$ResourceName - $this_item_name";
    else if($ResourceName == "InstructorAG")
        echo "Instructor - $this_item_name";
    else if($ResourceName == "InstructorHM")
        echo "Instructor - $this_item_name";
    else if($ResourceName == "InstructorNS")
        echo "Instructor - $this_item_name";
    else if($ResourceName == "InstructorTZ")
        echo "Instructor - $this_item_name";
    else
        echo "<td width=\"20%\"><u>$ResourceName</u><br>";
}

// if this is an aircraft, show the status
if($ResourceName == "Aircraft")
{
    // aircraft, show the status
    $status = sql_query1("SELECT status FROM AircraftScheduling_aircraft WHERE n_number='$this_item_name'");
	$AircraftStatus = LookupAircraftStatusString($status);
    echo " ($AircraftStatus)";
}

echo "</h2>\n";

#y? are year, month and day of the previous week.
#t? are year, month and day of the next week.

$i= mktime(0,0,0,$month,$day-7,$year);
$yy = date("Y",$i);
$ym = date("m",$i);
$yd = date("d",$i);

$i= mktime(0,0,0,$month,$day+7,$year);
$ty = date("Y",$i);
$tm = date("m",$i);
$td = date("d",$i);

if ( $pview != 1 ) {
    #Show Go to week before and after links
    echo "<table width=\"100%\"><tr><td>
      <a href=\"week.php?year=$yy&month=$ym&day=$yd&resource=$resource&resource_id=$resource_id$makemodel\">
      &lt;&lt; $lang[weekbefore]</a></td>
      <td align=center><a href=\"week.php?resource=$resource&resource_id=$resource_id$makemodel\">$lang[gotothisweek]</a></td>
      <td align=right><a href=\"week.php?year=$ty&month=$tm&day=$td&resource=$resource&resource_id=$resource_id$makemodel\">
      $lang[weekafter] &gt;&gt;</a></td></tr></table>";
}

#Get all appointments for this week in the room that we care about
# row[0] = Start time
# row[1] = End time
# row[2] = Entry type
# row[3] = Entry name
# row[4] = Entry ID
# The range predicate (starts <= week_end && ends > week_start) is
# equivalent but more efficient than the original 3-BETWEEN clauses.
$sql = "SELECT start_time, end_time, type, name, entry_id
   FROM AircraftScheduling_entry
   WHERE resource_id=$resource_id
   AND start_time <= $week_end AND end_time > $week_start
   AND entry_type != $EntryTypeStandby";

# Each row returned from the query is a meeting. Build an array of the
# form:  d[weekday][slot][x], where x = id, color, data.
# [slot] is based at 0 for midnight, but only slots within the hours of
# interest (morningstarts : eveningends) are filled in.
# [id] and [data] are only filled in when the meeting should be labeled,
# which is once for each meeting on each weekday.
# Note: weekday here is relative to the $weekstarts configuration variable.
# If 0, then weekday=0 means Sunday. If 1, weekday=0 means Monday.

$first_slot = $morningstarts * 3600 / $resolution;
$last_slot = ($eveningends * 3600 + $eveningends_minutes * 60) / $resolution;

if ($debug_flag) echo "<br>DEBUG: query=$sql <br>slots=$first_slot:$last_slot\n";
$res = sql_query($sql);
if (! $res) echo sql_error();
else for ($i = 0; ($row = sql_row($res, $i)); $i++)
{
    if ($debug_flag)
        echo "<br>DEBUG: result $i, id $row[4], starts $row[0], ends $row[1]\n";

    # Fill in slots for the meeting. Start at the meeting start time or
    # week start (which ever is later), and end one slot before the meeting
    # end time or week end (which ever is earlier).
    # Note: int casts on database rows for min and max is needed for PHP3.

    $t = max((int)$row[0], $week_start);
    $end_t = min((int)$row[1], $week_end+1);
    # make start and end times fall on a resolution boundary
    $t = (int) ($t / $resolution) * $resolution;
    $end_t = (int) ($end_t / $resolution + 59.0/60.0) * $resolution;
    $weekday = (date("w", $t) + 7 - $weekstarts) % 7;
    $prev_weekday = -1; # Invalid value to force initial label.
    $slot = (strftime("%H", $t) * 60 + strftime("%M", $t)) * 60 / $resolution;
    if ($debug_flag) echo "<br>DEBUG: t=$t, week_midnight=$week_midnight, slot=$slot\n";
    do
    {
        if ($debug_flag) echo "<br>DEBUG: t=$t, weekday=$weekday, slot=$slot\n";

        if ($slot < $first_slot)
        {
            # This is before the start of the displayed day; skip to first slot.
            $slot = $first_slot;
            $t = $weekday * 86400 + $am7;
            continue;
        }

        if ($slot <= $last_slot)
        {
            # This is within the working day; color it.
            $d[$weekday][$slot]["color"] = $row[2];
            # Only label it if it is the first time on this day:
            if ($prev_weekday != $weekday)
            {
                $prev_weekday = $weekday;
                $d[$weekday][$slot]["data"] = $row[3];
                $d[$weekday][$slot]["id"] = $row[4];
            
                # if the start time does not start on a resolution boundary, make a note
                # of the start time in the first segment
                if ($t != max((int)$row[0], $am7))
                {
                    # start time is not on a resolution boundary, add a note to the first entry
                    $FractionEntry = date("H:i", max((int)$row[0], $am7));
                    $d[$weekday][$slot]["data"] = $d[$weekday][$slot]["data"] . " $FractionEntry";          
                }
            }
        }
        # Step to next time period and slot:
        $t += $resolution;
        $slot++;

        if ($slot > $last_slot)
        {
            # Skip to first slot of next day:
            $weekday++;
            $slot = $first_slot;
            $t = $weekday * 86400 + $am7;
        }
    } while ($t < $end_t);
}
if ($debug_flag) 
{
    echo "<p>DEBUG:<p><pre>\n";
    if (gettype($d) == "array")
	foreach ($d as $w_k => $w_v)
		foreach ($w_v as $t_k => $t_v)
			foreach ($t_v as $k_k => $k_v)
                echo "d[$w_k][$t_k][$k_k] = '$k_v'\n";
    else echo "d is not an array!\n";
    echo "</pre><p>\n";
}

#This is where we start displaying stuff
echo "<table cellspacing=0 border=1 width=\"100%\">";

# The header row contains the weekday names and short dates.
echo "<tr><th width=\"1%\"><br>$lang[time]</th>";
if (empty($dateformat))
    $dformat = "%a<br>%b %d";
else
    $dformat = "%a<br>%d %b";
for ($t = $week_start; $t < $week_end; $t += 86400)
    echo "<th width=\"14%\">" . strftime($dformat, $t) . "</th>\n";
echo "</tr>\n";


# This is the main bit of the display. Outer loop is for the time slots,
# inner loop is for days of the week.

# URL for highlighting a time. Don't use REQUEST_URI or you will get
# the timetohighlight parameter duplicated each time you click.
$hilite_url="week.php?year=$year&month=$month&day=$day&area=$area&resource_id=$resource_id&timetohighlight";

# $t is the date/time for the first day of the week (Sunday, if $weekstarts=0).
# $wt is for the weekday in the inner loop.
$t = $am7;
for ($slot = $first_slot; $slot <= $last_slot; $slot++)
{
    # Show the time linked to the URL for highlighting that time:
    echo "<tr>";
    tdcell("red");
    echo "<a href=\"$hilite_url=$t\">" . date("H:i",$t) . "</a></td>";

    $wt = $t;

    # Color to use for empty cells: white, unless highlighting this row:
    if (isset($timetohighlight) && $timetohighlight == $t)
        $empty_color = "red";
    else
        $empty_color = "white";

    # See note above: weekday==0 is day $weekstarts, not necessarily Sunday.
    for ($weekday = 0; $weekday < 7; $weekday++)
    {
        # Three cases:
        # color:  id:   Slot is:   Color:    Link to:
        # -----   ----- --------   --------- -----------------------
        # unset   -     empty      white,red add new entry
        # set     unset used       by type   none (unlabelled slot)
        # set     set   used       by type   view entry

        $wday = date("d", $wt);
        $wmonth = date("m", $wt);
        $wyear = date("Y", $wt);
        if(!isset($d[$weekday][$slot]["color"]))
        {
            tdcell($empty_color);
            $hour = date("H",$wt);
            $minute  = date("i",$wt);
            echo "<center>";
            if ( $pview != 1 ) {
                echo "<a href='edit_entry.php?"
                . "goback=" . GetScriptName() 
                . "&resource=$resource"
                . "&resource_id=$resource_id"
                . "&InstructorResource=$InstructorResource"
                . "$makemodel"
                . "&hour=$hour&minute=$minute&year=$wyear&month=$wmonth"
                . "&day=$wday'>"
                . "<img src=image.php?src=new.jpg width=10 height=10 border=0>";
            } else echo '&nbsp;';
            echo "</a></center>";

        } else {
            tdcell($d[$weekday][$slot]["color"]);
            echo "<center>";
            if (!isset($d[$weekday][$slot]["id"])) {
                echo "&nbsp;\"&nbsp;";
            } else {
                echo " <a href=\"view_entry.php?"
                    . "goback=" . GetScriptName() 
                    . "&resource=$resource"
                    . "&resource_id=$resource_id"
                    . "&InstructorResource=$InstructorResource"
                    . "$makemodel"
                    . "&id=" . $d[$weekday][$slot]["id"]
                    . "&day=$wday&month=$wmonth&year=$wyear\">"
                    . htmlspecialchars($d[$weekday][$slot]["data"]) . "</a>";
            }
            echo "</center>";
        }
        echo "</td>\n";
        $wt += 86400;
    }
    echo "</tr>\n";
    $t += $resolution;
}
echo "</center></table>";

//if ( $pview != 1 ) show_colour_key();

// end the form
echo "</FORM>";

include "trailer.inc"; 
?>
