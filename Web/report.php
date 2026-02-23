<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: report.php,v 1.4 2001/12/20 07:02:27 mbarclay Exp $

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";

// initialize variables
$InstructorResource = "";
$goback = "";

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
if(isset($rdata["summarize"])) $summarize = $rdata["summarize"];
if(isset($rdata["sumby"])) $sumby = $rdata["sumby"];
if(isset($rdata["MakeMatch"])) $MakeMatch = $rdata["MakeMatch"];
if(isset($rdata["ModelMatch"])) $ModelMatch = $rdata["ModelMatch"];
if(isset($rdata["NNumberMatch"])) $NNumberMatch = $rdata["NNumberMatch"];
if(isset($rdata["InstructorMatch"])) $InstructorMatch = $rdata["InstructorMatch"];
if(isset($rdata["namematch"])) $namematch = $rdata["namematch"];
if(isset($rdata["descrmatch"])) $descrmatch = $rdata["descrmatch"];
if(isset($rdata["From_day"])) $From_day = $rdata["From_day"];
if(isset($rdata["From_month"])) $From_month = $rdata["From_month"];
if(isset($rdata["From_year"])) $From_year = $rdata["From_year"];
if(isset($rdata["To_time"])) $To_time = $rdata["To_time"];
if(isset($rdata["To_day"])) $To_day = $rdata["To_day"];
if(isset($rdata["To_month"])) $To_month = $rdata["To_month"];
if(isset($rdata["To_year"])) $To_year = $rdata["To_year"];
if(isset($rdata["pview"])) $pview = $rdata["pview"];
if(isset($rdata["goback"])) $goback = $rdata["goback"];

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

if(empty($resource))
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

if(!getAuthorised(getUserName(), getUserPassword(), $UserLevelNormal))
{
	showAccessDenied($day, $month, $year, $resource, $resource_id, $makemodel, " ", " ", " ");
	exit();
}

# Convert a start time and end time to a plain language description.
# This is similar but different from the way it is done in view_entry.
function describe_span($starts, $ends)
{
	global $lang;
	global $AircraftScheduleType, $InstructorScheduleType;
	global $EntryTypeStandby, $EntryTypeNormal, $EntryTypeRepeating, $EntryTypeModified;
	
	$start_date = strftime('%A %d %B %Y', $starts);
	$start_time = strftime('%H:%M:%S', $starts);
	$duration = $ends - $starts;
	if ($start_time == "00:00:00" && $duration == 60*60*24)
		return $start_date . " - " . $lang["all_day"];
	toTimeString($duration, $dur_units);
	return $start_date . " " . $start_time . " - " . $duration . " " . $dur_units;
}

# Report on one entry. See below for columns in $row[].
# $last_area_room remembers the current area/room.
function reporton(&$row, &$last_area_room)
{
	global $lang, $typel;
	global $AircraftScheduleType, $InstructorScheduleType;
	global $EntryTypeStandby, $EntryTypeNormal, $EntryTypeRepeating, $EntryTypeModified;
	global $day, $month, $year;
	global $resource, $resource_id, $InstructorResource;
	global $goback, $GoBackParameters;
	
	# Display Area/Room, but only when it changes:
    if ($row[11] == $AircraftScheduleType)
		$area_room = htmlspecialchars($row[8]) . " - " . htmlspecialchars($row[9]);
	else
		$area_room = htmlspecialchars("Flight Instruction");
	if ($area_room != $last_area_room)
	{
		echo "<hr><h2>$lang[room] $area_room</h2>\n";
		$last_area_room = $area_room;
	}

	echo "<hr><table width='100%'>\n";

	# Brief Description (title), linked to view_entry:
	if ($row[12] == $EntryTypeStandby)
	{
		echo "<tr><td class='BL'><a href='StandbyRequestView.php?StandbyID=$row[0]" .
		            "&goback=" . GetScriptName() .
                    "&GoBackParameters=$GoBackParameters" . 
                    "'>"
			. htmlspecialchars($row[3]) . "</a></td>\n";
	}
	else
	{
		echo "<tr><td class='BL'><a href='view_entry.php?id=$row[0]" .
		            "&goback=" . GetScriptName() .
                    "&GoBackParameters=$GoBackParameters" .
                    "'>"
			. htmlspecialchars($row[3]) . "</a></td>\n";
    }
    
	# From date-time and duration:
	echo "<td class='BR' align=right>" . describe_span($row[1], $row[2]) . "</td></tr>\n";
	# Description:
	echo "<tr><td class='BL' colspan=2><b>$lang[description]</b> " .
		nl2br(htmlspecialchars($row[4])) . "</td></tr>\n";

	# Entry Type:
	# $et = empty($typel[$row[5]]) ? "?$row[5]?" : $typel[$row[5]];
	# aircraft tail number or instructor name
	if ($row[12] == $EntryTypeStandby)
	{
	    if ($row[11] == $AircraftScheduleType)
			$et = "Aircraft - " . $row[10] . " (Standby)";
		else
			$et = "Instructor - " . $row[10] . " (Standby)";
	}
	else
	{
	    if ($row[11] == $AircraftScheduleType)
			$et = "Aircraft - " . $row[10];
		else
			$et = "Instructor - " . $row[10];
	}
	echo "<tr><td class='BL' colspan=2><b>$lang[type]</b> $et</td></tr>\n";
	# Created by and last update timestamp:
	echo "<tr><td class='BL' colspan=2><small><b>$lang[createdby]</b> " .
		htmlspecialchars($row[6]) . ", <b>$lang[lastupdate]</b> " .
		strftime("%A %d %B %Y %H:%M:%S", $row[7] - TimeZoneAdjustment()) . "</small></td></tr>\n";

	echo "</table>\n";
}

# Collect summary statistics on one entry. See below for columns in $row[].
# $sumby selects grouping on name (d) or created by (c).
# This also builds hash tables of all unique names and rooms. When sorted,
# these will become the column and row headers of the summary table.
function accumulate(&$row, &$count, &$hours, $report_start, $report_end,
	&$room_hash, &$name_hash)
{
	global $sumby;
	global $AircraftScheduleType, $InstructorScheduleType;
	
	# Use name or created by as the name:
	$name = htmlspecialchars($row[($sumby == "d" ? 3 : 6)]);
    # Area and room separated by break:
    if ($row[11] == $AircraftScheduleType)
		$room = htmlspecialchars($row[8]) . "<br>" . htmlspecialchars($row[9]);
	else
		$room = htmlspecialchars("Flight Instruction");
	# Accumulate the number of bookings for this room and name:
	@$count[$room][$name]++;
	# Accumulate hours used, clipped to report range dates:
	@$hours[$room][$name] += (min((int)$row[2], $report_end)
		- max((int)$row[1], $report_start)) / 3600.0;
	$room_hash[$room] = 1;
	$name_hash[$name] = 1;
}

# Output a table cell containing a count (integer) and hours (float):
function cell($count, $hours)
{
	echo "<td class='BR' align=right>($count) "
	. sprintf("%.2f", $hours) . "</td>\n";
}

# Output the summary table (a "cross-tab report"). $count and $hours are
# 2-dimensional sparse arrays indexed by [area/room][name].
# $room_hash & $name_hash are arrays with indexes naming unique rooms and names.
function do_summary(&$count, &$hours, &$room_hash, &$name_hash)
{
	global $lang;
	global $AircraftScheduleType, $InstructorScheduleType;
	global $summarize;
	global $sumby;
    global $MakeMatch;
    global $ModelMatch;
    global $NNumberMatch;
    global $InstructorMatch;
    global $namematch;
    global $descrmatch;
    global $From_day;
    global $From_month;
    global $From_year;
    global $To_time;
    global $To_day;
    global $To_month;
    global $To_year;

	# Make a sorted array of area/rooms, and of names, to use for column
	# and row indexes. Use the rooms and names hashes built by accumulate().
	# At PHP4 we could use array_keys().
	reset($room_hash);
	foreach ($room_hash as $room_key => $room_value) $rooms[] = $room_key;
	ksort($rooms);
	reset($name_hash);
	foreach ($name_hash as $name_key => $name_value) $names[] = $name_key;
	ksort($names);
	$n_rooms = sizeof($rooms);
	$n_names = sizeof($names);

	echo "<hr><h1>$lang[summary_header]</h1><table border=2 cellspacing=4>\n";
	echo "<tr><td>&nbsp;</td>\n";
	for ($c = 0; $c < $n_rooms; $c++)
	{
		echo "<td class='BL' align=left><b>$rooms[$c]</b></td>\n";
		$col_count_total[$c] = 0;
		$col_hours_total[$c] = 0.0;
	}
	echo "<td class='BR' align=right><br><b>$lang[total]</b></td></tr>\n";
	$grand_count_total = 0;
	$grand_hours_total = 0;

	for ($r = 0; $r < $n_names; $r++)
	{
		$row_count_total = 0;
		$row_hours_total = 0.0;
		$name = $names[$r];
		echo "<tr><td class='BR' align=right><b>$name</b></td>\n";
		for ($c = 0; $c < $n_rooms; $c++)
		{
			$room = $rooms[$c];
			if (isset($count[$room][$name]))
			{
				$count_val = $count[$room][$name];
				$hours_val = $hours[$room][$name];
				cell($count_val, $hours_val);
				$row_count_total += $count_val;
				$row_hours_total += $hours_val;
				$col_count_total[$c] += $count_val;
				$col_hours_total[$c] += $hours_val;
			} else {
				echo "<td>&nbsp;</td>\n";
			}
		}
		cell($row_count_total, $row_hours_total);
		echo "</tr>\n";
		$grand_count_total += $row_count_total;
		$grand_hours_total += $row_hours_total;
	}
	echo "<tr><td class='BR' align=right><b>$lang[total]</b></td>\n";
	for ($c = 0; $c < $n_rooms; $c++)
		cell($col_count_total[$c], $col_hours_total[$c]);
	cell($grand_count_total, $grand_hours_total);
	echo "</tr></table>\n";
}

# print the page header
print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");

if (isset($MakeMatch))
{
	# Resubmit - reapply parameters as defaults.
	# Make sure these are not escape-quoted:
	$MakeMatch = unslashes($MakeMatch);
	$ModelMatch = unslashes($ModelMatch);
	$NNumberMatch = unslashes($NNumberMatch);
	$InstructorMatch = unslashes($InstructorMatch);
	$namematch = unslashes($namematch);
	$descrmatch = unslashes($descrmatch);

	# Make default values when the form is reused.
	$MakeMatch_default = htmlspecialchars($MakeMatch);
	$ModelMatch_default = htmlspecialchars($ModelMatch);
	$NNumberMatch_default = htmlspecialchars($NNumberMatch);
	$InstructorMatch_default = htmlspecialchars($InstructorMatch);
	$namematch_default = htmlspecialchars($namematch);
	$descrmatch_default = htmlspecialchars($descrmatch);
	
	// make sure we have valid data for the remaining parameters
    if (!isset($From_day)) $From_day = "";
    if (!isset($From_month)) $From_month = "";
    if (!isset($From_year)) $From_year = "";
    if (!isset($To_time)) $To_time = "";
    if (!isset($To_day)) $To_day = "";
    if (!isset($To_month)) $To_month = "";
    if (!isset($To_year)) $To_year = "";
    if (!isset($pview)) $pview = "";

    // set the goback parameters so that a return to this page will 
    // generate the same report
    $GoBackParameters = BuildGoBackParameters(
                    "?summarize=$summarize" .
                    "&sumby=$sumby" .
                    "&MakeMatch=$MakeMatch" .
                    "&ModelMatch=$ModelMatch" .
                    "&NNumberMatch=$NNumberMatch" .
                    "&InstructorMatch=$InstructorMatch" .
                    "&namematch=$namematch" .
                    "&descrmatch=$descrmatch" .
                    "&From_day=$From_day" .
                    "&From_month=$From_month" .
                    "&From_year=$From_year" .
                    "&To_time=$To_time" .
                    "&To_day=$To_day" .
                    "&To_month=$To_month" .
                    "&To_year=$To_year" .
                    "&pview=$pview");
}
else
{
	# New report - use defaults.
	$MakeMatch_default = "";
	$ModelMatch_default = "";
	$NNumberMatch_default = "";
	$InstructorMatch_default = "";
	$namematch_default = "";
	$descrmatch_default = "";
	$From_day = $day;
	$From_month = $month;
	$From_year = $year;
	$To_time = mktime(0, 0, 0 - TimeZoneAdjustment(), $month, $day + $default_report_days, $year);
	$To_day   = date("d", $To_time);
	$To_month = date("m", $To_time);
	$To_year  = date("Y", $To_time);
    $GoBackParameters = "";
}

# $summarize: 1=report only, 2=summary only, 3=both.
if (empty($summarize)) $summarize = 1;
# $sumby: d=by brief description, c=by creator.
if (empty($sumby)) $sumby = "d";

# Upper part: The form.
?>
<h1><?php echo $lang["report_on"];?></h1>
<form name="main" method=post action=report.php>
<table>
<tr><td class="CR"><?php echo $lang["report_start"];?></td>
    <td class="CL"> <font size="-1">
    <?php genDateSelector("From_", "main", $From_day, $From_month, $From_year); ?>
    </font></td></tr>
<tr><td class="CR"><?php echo $lang["report_end"];?></td>
    <td class="CL"> <font size="-1">
    <?php genDateSelector("To_", "main", $To_day, $To_month, $To_year); ?>
    </font></td></tr>
<tr><td class="CR"><?php echo $lang["match_area"];?></td>
    <td class="CL"><input type=text name=MakeMatch size=18
    value="<?php echo $MakeMatch_default; ?>">
    </td></tr>
<tr><td class="CR"><?php echo $lang["match_room"];?></td>
    <td class="CL"><input type=text name=ModelMatch size=18
    value="<?php echo $ModelMatch_default; ?>">
    </td></tr>
<tr><td class="CR"><?php echo $lang["match_nnumber"];?></td>
    <td class="CL"><input type=text name=NNumberMatch size=18
    value="<?php echo $NNumberMatch_default; ?>">
    </td></tr>
<tr><td class="CR"><?php echo $lang["match_instructor"];?></td>
    <td class="CL"><input type=text name=InstructorMatch size=18
    value="<?php echo $InstructorMatch_default; ?>">
    </td></tr>
<tr><td class="CR"><?php echo $lang["match_entry"];?></td>
    <td class="CL"><input type=text name=namematch size=18
    value="<?php echo $namematch_default; ?>">
    </td></tr>
<tr><td class="CR"><?php echo $lang["match_descr"];?></td>
    <td class="CL"><input type=text name=descrmatch size=18
    value="<?php echo $descrmatch_default; ?>">
    </td></tr>
<tr><td class="CR"><?php echo $lang["include"];?></td>
    <td class="CL">
      <input type=radio name=summarize value=1<?php if ($summarize==1) echo " checked";
        echo ">" . $lang["report_only"];?>
      <input type=radio name=summarize value=2<?php if ($summarize==2) echo " checked";
        echo ">" . $lang["summary_only"];?>
      <input type=radio name=summarize value=3<?php if ($summarize==3) echo " checked";
        echo ">" . $lang["report_and_summary"];?>
    </td></tr>
<tr><td class="CR"><?php echo $lang["summarize_by"];?></td>
    <td class="CL">
      <input type=radio name=sumby value=d<?php if ($sumby=="d") echo " checked";
        echo ">" . $lang["sum_by_descrip"];?>
      <input type=radio name=sumby value=c<?php if ($sumby=="c") echo " checked";
        echo ">" . $lang["sum_by_creator"];?>
    </td></tr>
<tr><td colspan=2 align=center><input type=submit>
</td></tr>
</table>
<input type=hidden name=pview value=<?php echo $pview; ?>>

<?php
    BuildHiddenInputs("year=$year&month=$month&day=$day"
              . "&resource=$resource"
              . "&goback=$goback"
              . "&resource_id=$resource_id"
              . "&InstructorResource=$InstructorResource");
?>

</form>

<?php

# Lower part: Results, if called with parameters:
if (isset($MakeMatch))
{
	# Make sure these are not escape-quoted:
	$MakeMatch = unslashes($MakeMatch);
	$ModelMatch = unslashes($ModelMatch);
	$NNumberMatch = unslashes($NNumberMatch);
	$InstructorMatch = unslashes($InstructorMatch);
	$namematch = unslashes($namematch);
	$descrmatch = unslashes($descrmatch);

	# Start and end times are also used to clip the times for summary info.
	$report_start = mktime(0, 0, 0 - TimeZoneAdjustment(), $From_month, $From_day, $From_year);
	$report_end = mktime(0, 0, 0 - TimeZoneAdjustment(), $To_month, $To_day+1, $To_year);

#   SQL result will contain the following columns:
# Col Index  Description:
#   1  [0]   Entry ID, not displayed -- used for linking to View script.
#   2  [1]   Start time as Unix time_t
#   3  [2]   End time as Unix time_t
#   4  [3]   Entry name or short description, must be HTML escaped
#   5  [4]   Entry description, must be HTML escaped
#   6  [5]   Type, single char mapped to a string
#   7  [6]   Created by (user name or IP addr), must be HTML escaped
#   8  [7]   Modification timestamp, converted to Unix time_t by the database
#   9  [8]   Resource make, must be HTML escaped
#  10  [9]   Resource model, must be HTML escaped
#  11  [10]  Resource name, must be HTML escaped
#  12  [11]  Resource type
#  13  [12]  entry_type

	$sql = "SELECT e.entry_id, e.start_time, e.end_time, e.name, e.description, "
		. "e.type, e.create_by, "
		.  sql_syntax_timestamp_to_unix("e.timestamp")
		. ", r.resource_make, r.resource_model, r.resource_name, r.schedulable_id, e.entry_type"
		. " FROM 
				AircraftScheduling_entry e, 
				AircraftScheduling_resource r"
		. " WHERE e.resource_id = r.resource_id"
		. " AND e.start_time < $report_end AND e.end_time > $report_start";

	if (!empty($MakeMatch))
		$sql .= " AND" .  sql_syntax_caseless_contains("r.resource_make", $MakeMatch);
	if (!empty($ModelMatch))
		$sql .= " AND" .  sql_syntax_caseless_contains("r.resource_model", $ModelMatch);
	if (!empty($NNumberMatch))
		$sql .= " AND" .  sql_syntax_caseless_contains("r.resource_name", $NNumberMatch);
	if (!empty($InstructorMatch))
		$sql .= " AND" .  sql_syntax_caseless_contains("r.resource_name", $InstructorMatch);
	if (!empty($namematch))
		$sql .= " AND" .  sql_syntax_caseless_contains("e.name", $namematch);
	if (!empty($descrmatch))
		$sql .= " AND" .  sql_syntax_caseless_contains("e.description", $descrmatch);

	# Order by make, model, Start date/time:
	$sql .= " ORDER BY 9,10,2";

	$res = sql_query($sql);
	if (! $res) fatal_error(0, sql_error());
	$nmatch = sql_count($res);
	if ($nmatch == 0)
	{
		echo "<P><B>" . $lang["nothing_found"] . "</B>\n";
		echo "<BR>\n";
		sql_free($res);
	}
	else
	{
		$last_area_room = "";
		echo "<P><B>" . $nmatch . " "
					. ($nmatch == 1 ? $lang["entry_found"] : $lang["entries_found"])
					.  "</B>\n";

		for ($i = 0; ($row = sql_row($res, $i)); $i++)
		{
			if ($summarize & 1)
				reporton($row, $last_area_room);

			if ($summarize & 2)
				accumulate($row, $count, $hours, $report_start, $report_end,
					$room_hash, $name_hash);
		}
		if ($summarize & 2)
			do_summary($count, $hours, $room_hash, $name_hash);
	}
}

// generate return URL
GenerateReturnURL(
                    $goback, 
                    "?day=$day&month=$month&year=$year$makemodel" . 
                    "&resource=$resource" .
                    "&resource_id=$resource_id" .
                    "&InstructorResource=$InstructorResource"
                    );

include "trailer.inc";
