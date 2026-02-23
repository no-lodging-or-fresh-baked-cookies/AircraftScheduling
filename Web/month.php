<?php

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: month.php,v 1.7 2001/12/19 00:36:43 mbarclay Exp $

# AircraftScheduling/month.php - Month-at-a-time view

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";
include "calendar.inc";
include "AircraftScheduling_sql.inc";
require_once("DatabaseFunctions.inc");

global $certificate;

// initialize variables
$all = '';
$InstructorResource = "";

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
if(isset($rdata["timetohighlight"])) $timetohighlight = $rdata["timetohighlight"];
if(isset($rdata["makemodel"])) $makemodel = $rdata["makemodel"];
if(isset($rdata["all"])) $all = $rdata["all"];
if(isset($rdata["InstructorResource"])) $InstructorResource = $rdata["InstructorResource"];

# 3-value compare: Returns result of compare as "< " "= " or "> ".
function cmp3($a, $b)
{
	if ($a < $b) return "< ";
	if ($a == $b) return "= ";
	return "> ";
}

if (empty($resource) || $resource == "None")
	$resource = get_default_resource();

if($make) $makemodel = "&make=".str_replace(" ","+",$make);
else if($model) $makemodel = "&model=".str_replace(" ","+",$model);
else if($certificate) $makemodel = "&certificate=$certificate";
else { $all=1; $makemodel = "&all=1"; }

# Default parameters:
if (empty($debug_flag)) $debug_flag = 0;
if (empty($month) || empty($year) || !checkdate($month, 1, $year))
{
	$month = date("m");
	$year  = date("Y");
}
$day = 1;
		
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

# Month view start time. This ignores morningstarts/eveningends because it
# doesn't make sense to not show all entries for the day, and it messes
# things up when entries cross midnight.
$month_start = mktime(0, 0, 0, $month, 1, $year);

# What column the month starts in: 0 means $weekstarts weekday.
$weekday_start = (date("w", $month_start) - $weekstarts + 7) % 7;

$days_in_month = date("t", $month_start);

$month_end = mktime(23, 59, 59, $month, $days_in_month, $year);

if ( $pview != 1 ) {
	# Table with areas, rooms, minicals.
	echo "<table width=\"100%\"><tr>";
	$this_resource_name = "";
	$this_room_name = "";

	# Show all areas
	echo "<td width=\"25%\">";

	$params = array( "make" => $make, "model" => $model, "all" => $all, "type" => $certificate);
	displayResourceMenu("MONTH", $year, $month, $day, $resource, $InstructorResource, $params, $makemodel);

	echo "</td>\n";

	# Show all items in the current area
    echo "<td width=\"35%\">";
}

$sql = '';
if($resource == "Aircraft")
{
	// get the schedule information for this resource
	$sql = "SELECT AircraftScheduling_resource.resource_id, n_number 
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
			FROM 
				AircraftScheduling_person a, 
				AircraftScheduling_resource b, 
				AircraftScheduling_instructors c
			WHERE 
				b.resource_id=c.resource_id AND 
				c.person_id=a.person_id ";
	$sql2 .= " ORDER BY 2";
	$res2 = sql_query($sql2);
}

// ?????
if($certificate) 
{
	$cert = expand_certificate_abreviation($certificate);
	$sql .= " AND AircraftScheduling_certificates.certificate = '$cert' AND AircraftScheduling_certificates.certificate_id=AircraftScheduling_pilot_certificates.certificate_id AND
				AircraftScheduling_pilot_certificates.pilot_id=a.person_id AND a.person_id=c.person_id";
	$sql .= " ORDER BY 2";
	$res = sql_query($sql);
}

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
     		"month.php?year=$year&month=$month&day=$day&resource=$resource$makemodel&resource_id=$row[0]";
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
     		"month.php?year=$year&month=$month&day=$day&resource=$resource$makemodel&resource_id=$row[0]";
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
	echo "	var LinkEntries = new Array;";

	// build the link entry array
	$LinkEntriesCount = 0;
	foreach($LinkArray as $Item)
	{
		echo "	LinkEntries[" . $LinkEntriesCount . "] = \"" . $Item . "\";";
		$LinkEntriesCount = $LinkEntriesCount + 1;
	}
		
	echo "	window.location.href = LinkEntries[document.forms['main'].DisplayResourceSelect.selectedIndex];";
	echo "}";
	echo "</SCRIPT>";
}

if ( $pview != 1 ) {
	echo "</td>\n";
	
	#Draw the three month calendars - Note they link to day view.
	minicals($year, $month, $day, $resource, $InstructorResource, $makemodel);
	echo "</tr></table>\n";
}

# Don't continue if this area has no items
if ($resource_id <= 0)
{
	echo "<h1>$lang[no_rooms_for_area]</h1>";
	include "trailer.inc";
	exit;
}

# Show area and item
echo "<h2 align=center>";
$CurrentMonth = strftime("%B %Y", $month_start);
$ResourceName = AircraftSchedulingGetResourceName($resource_id);
if($ResourceName == "Aircraft")
{
	// look up the aircraft type in the database
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
		        "&name=" . str_replace(" ", "+",$this_item_name).">$CurrentMonth - $AircraftMake - $this_item_name</a>";
	else
		echo "<a href=$display_page?goback=" . GetScriptName() . 
                "&year=$year&month=$month&day=$day" .
		        "&resource=$resource" .
		        "&resource_id=$resource_id" . 
		        "&name=" . str_replace(" ", "+",$this_item_name).">$CurrentMonth - $this_item_name</a>";
}
else
{
	if($ResourceName == "Aircraft")
		echo "$CurrentMonth - AircraftMake - $this_item_name";
	else
		echo "$CurrentMonth - $this_item_name";
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

# Show Go to month before and after links
#y? are year and month of the previous month.
#t? are year and month of the next month.

$i= mktime(0,0,0,$month-1,1,$year);
$yy = date("Y",$i);
$ym = date("n",$i);

$i= mktime(0,0,0,$month+1,1,$year);
$ty = date("Y",$i);
$tm = date("n",$i);
if ( $pview != 1 ) {
	echo "<table width=\"100%\"><tr><td>
	  <a href=\"month.php?year=$yy&month=$ym&resource=$resource&resource_id=$resource_id$makemodel\">
	  &lt;&lt; $lang[monthbefore]</a></td>
	  <td align=center><a href=\"month.php?resource=$resource&resource_id=$resource_id$makemodel\">$lang[gotothismonth]</a></td>
	  <td align=right><a href=\"month.php?year=$ty&month=$tm&resource=$resource&resource_id=$resource_id$makemodel\">
	  $lang[monthafter] &gt;&gt;</a></td></tr></table>";
}

if ($debug_flag)
	echo "<p>DEBUG: month=$month year=$year start=$weekday_start range=$month_start:$month_end\n";

# Used below: localized "all day" text but with non-breaking spaces:
$all_day = preg_replace("/ /", "/&nbsp;/", $lang["all_day"]);

#Get all meetings for this month in the room that we care about
# row[0] = Start time
# row[1] = End time
# row[2] = Entry ID
$sql = "SELECT start_time, end_time, entry_id
   FROM AircraftScheduling_entry
   WHERE resource_id=$resource_id
   AND start_time <= $month_end AND end_time > $month_start
   AND entry_type != $EntryTypeStandby
   ORDER by 1";

# Build an array of information about each day in the month.
# The information is stored as:
#  d[monthday]["id"][] = ID of each entry, for linking.
#  d[monthday]["data"][] = "start-stop" times of each entry.
$res = sql_query($sql);
if (! $res) echo sql_error();
else for ($i = 0; ($row = sql_row($res, $i)); $i++)
{
	if ($debug_flag)
		echo "<br>DEBUG: result $i, id $row[2], starts $row[0], ends $row[1]\n";

	# Fill in data for each day during the month that this meeting covers.
	# Note: int casts on database rows for min and max is needed for PHP3.
	$t = max((int)$row[0], $month_start);
	$end_t = min((int)$row[1], $month_end);
 	$day_num = date("j", $t);
	$midnight = mktime(0, 0, 0, $month, $day_num, $year);
	while ($t < $end_t)
	{
		if ($debug_flag) echo "<br>DEBUG: Entry $row[2] day $day_num\n";
		$d[$day_num]["id"][] = $row[2];

		$midnight_tonight = $midnight + 86400;

		# Describe the start and end time, accounting for "all day"
		# and for entries starting before/ending after today.
		# There are 9 cases, for start time < = or > midnight this morning,
		# and end time < = or > midnight tonight.
		# Use ~ (not -) to separate the start and stop times, because MSIE
		# will incorrectly line break after a -.

		switch (cmp3($row[0], $midnight) . cmp3($row[1], $midnight_tonight))
		{
			case "> < ":         # Starts after midnight, ends before midnight
			case "= < ":         # Starts at midnight, ends before midnight
				$d[$day_num]["data"][] = date("H:i", $row[0]) . "~" . date("H:i", $row[1]);
				break;
			case "> = ":         # Starts after midnight, ends at midnight
				$d[$day_num]["data"][] = date("H:i", $row[0]) . "~24:00";
				break;
			case "> > ":         # Starts after midnight, continues tomorrow
				$d[$day_num]["data"][] = date("H:i", $row[0]) . "~====&gt;";
				break;
			case "= = ":         # Starts at midnight, ends at midnight
				$d[$day_num]["data"][] = $all_day;
				break;
			case "= > ":         # Starts at midnight, continues tomorrow
				$d[$day_num]["data"][] = $all_day . "====&gt;";
				break;
			case "< < ":         # Starts before today, ends before midnight
				$d[$day_num]["data"][] = "&lt;====~" . date("H:i", $row[1]);
				break;
			case "< = ":         # Starts before today, ends at midnight
				$d[$day_num]["data"][] = "&lt;====" . $all_day;
				break;
			case "< > ":         # Starts before today, continues tomorrow
				$d[$day_num]["data"][] = "&lt;====" . $all_day . "====&gt;";
				break;
		}

		# Only if end time > midnight does the loop continue for the next day.
		if ($row[1] <= $midnight_tonight) break;
		$day_num++;
		$t = $midnight = $midnight_tonight;
	}
}
if ($debug_flag) 
{
	echo "<p>DEBUG: Array of month day data:<p><pre>\n";
	for ($i = 1; $i <= $days_in_month; $i++)
	{
		if (isset($d[$i]["id"]))
		{
			$n = count($d[$i]["id"]);
			echo "Day $i has $n entries:\n";
			for ($j = 0; $j < $n; $j++)
				echo "  ID: " . $d[$i]["id"][$j] .
					" Data: " . $d[$i]["data"][$j] . "\n";
		}
	}
	echo "</pre>\n";
}

echo "<table border=2 width=\"100%\">\n<tr>";
# Weekday name header row:
for ($weekcol = 0; $weekcol < 7; $weekcol++)
{
	echo "<th width=\"14%\">" . day_name(($weekcol + $weekstarts)%7) . "</th>";
}
echo "</tr><tr>\n";

# Skip days in week before start of month:
for ($weekcol = 0; $weekcol < $weekday_start; $weekcol++)
{
	echo "<td bgcolor=\"#cccccc\" height=100>&nbsp;</td>\n";
}

# Draw the days of the month:
for ($cday = 1; $cday <= $days_in_month; $cday++)
{
	if ($weekcol == 0) echo "</tr><tr>\n";
	echo "<td valign=top height=100 class=\"month\"><div class=\"monthday\"><a href=\"day.php?year=$year&month=$month&day=$cday&resource=$resource&InstructorResource=None\">$cday</a></div>\n";

	# Anything to display for this day?
	if (isset($d[$cday]["id"][0]))
	{
		echo "<font size=-2>";
		$n = count($d[$cday]["id"]);
		# Show the start/stop times, 2 per line, linked to view_entry.
		# If there are 12 or fewer, show them, else show 11 and "...".
		for ($i = 0; $i < $n; $i++)
		{
			if ($i == 11 && $n > 12)
			{
				echo " ...\n";
				break;
			}
			if ($i > 0 && $i % 2 == 0) echo "<br>"; else echo " ";
			echo "<a href=\"view_entry.php?"
				    . "goback=" . GetScriptName() 
			        . "&resource=$resource"
                    . "&resource_id=$resource_id"
			        . "&InstructorResource=$InstructorResource"
                    . "$makemodel"
			        . "&id=" . $d[$cday]["id"][$i]
					. "&day=$cday&month=$month&year=$year\">"
					. $d[$cday]["data"][$i] . "</a>";
		}
		echo "</font>";
	}
    echo "</td>\n";
	if (++$weekcol == 7) $weekcol = 0;
}

# Skip from end of month to end of week:
if ($weekcol > 0) for (; $weekcol < 7; $weekcol++)
{
	echo "<td bgcolor=\"#cccccc\" height=100>&nbsp;</td>\n";
}
echo "</tr></table>\n";

// end the form
echo "</FORM>";

include "trailer.inc";
?>
