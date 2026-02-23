<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

# $Id: day.php,v 1.9 2001/12/20 07:02:27 mbarclay Exp $

//$start_time = getmicrotime();  // defined in functions.inc

include "global_def.inc";
include "config.inc";
include "AircraftScheduling_auth.inc";
include "$dbsys.inc";
include "functions.inc";
include "calendar.inc";
require_once("DatabaseFunctions.inc");

global $certificate;
global $morningstarts;
global $eveningends;
global $eveningends_minutes;

// initialize variables
$all = '';
$InstructorResource = 'None';
$today = NULL;
$debug_flag = 0;

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
if(isset($rdata["makemodel"])) $makemodel = $rdata["makemodel"];
if(isset($rdata["id"])) $id = $rdata["id"];
if(isset($rdata["all"])) $all = $rdata["all"];
if(isset($rdata["debug_flag"])) $debug_flag = $rdata["debug_flag"];
	
/* ProcessScheduleInformation
 * 
 * Process the schedule information from the database query into information for
 * the calendar.
 * 
 * $SQLResult - results from the SQL query
 * $today - information for producing the calendar
 * $resolution - number of seconds between bookings
 * $pm7 - ending time of the schedule
 * $am7 - beginning time of the schedule
 * 
 * Returns:
 *   none
 */	
function ProcessScheduleInformation($SQLResult, &$today, $resolution, $pm7, $am7)
{
	// process the scheduling information
	if ($SQLResult)
	{
		for ($i = 0; ($row = sql_row($SQLResult, $i)); $i++) 
		{
			# Each row we have here is an appointment.
			#Row[0] = Resource ID
			#row[1] = start time
			#row[2] = end time
			#row[3] = name of person
			#row[4] = id of this booking
			#row[5] = type (internal/external)
		
			# $today is a map of the screen that will be displayed
			# It looks like:
			#     $today[Room ID][Time][id]
			#                          [color]
			#                          [data]
		
			# Fill in the map for this meeting. Start at the meeting start time,
			# or the day start time, whichever is later. End one slot before the
			# meeting end time (since the next slot is for meetings which start then),
			# or at the last slot in the day, whichever is earlier.
			# Note: int casts on database rows for max may be needed for PHP3.
			$end_t = min($row[2] - $resolution, $pm7);
			$start_t = max((int)$row[1], $am7);
			# make start and end times fall on a resolution boundary
			$start_t = (int) ($start_t / $resolution) * $resolution;
			$end_t = (int) ($end_t / $resolution + 59.0/60.0) * $resolution;
			for ($t = $start_t; $t <= $end_t; $t += $resolution)
			{
				$today[$row[0]][$t]["id"]    = $row[4];
				$today[$row[0]][$t]["color"] = $row[5];
				$today[$row[0]][$t]["data"]  = "";
			}
		
			# Show the name of the booker in the first segment that the booking
			# happens in, or at the start of the day if it started before today.
			$today[$row[0]][$start_t]["data"] = $row[3];
			
			# if the start time does not start on a resolution boundary, make a note
			# of the start time in the first segment
			if ($start_t != max((int)$row[1], $am7))
			{
				# start time is not on a resolution boundary, add a note to the first entry
				$FractionEntry = date("H:i", max((int)$row[1], $am7));
				$today[$row[0]][$start_t]["data"] = $today[$row[0]][$start_t]["data"] . " $FractionEntry";			
			}
		}
	}
}

#If we dont know the right date then make it up 
if ($debug_flag)
	echo "DEBUG date: " . date('l jS \of F Y h:i:s A') ."<br>";
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
if ($debug_flag)
{
	echo "DEBUG day: $day month: $month year: $year<br>";
	echo "DEBUG day of the month: " . date("d") . "<br>";
}

if (empty($resource))
	$resource = get_default_resource();

if (empty($InstructorResource))
	$InstructorResource = "None";

// if everything is selected, select both instructors and aircraft
if($resource == "Everything") 
{
	$resource = "Aircraft";
	$InstructorResource = "Instructor";
} 

// catch resource set to instructor
if($resource == "Instructor" 
		|| $resource == "InstructorAG"
		|| $resource == "InstructorHM"
		|| $resource == "InstructorNS"
		|| $resource == "InstructorTZ") 
{
	$InstructorResource = $resource;
	$resource = "None";
} 

if($make) $makemodel = "&make=" . str_replace(" ", "+", $make);
else if($model) $makemodel = "&model=" . str_replace(" ", "+", $model);
else if($certificate) $makemodel = "&certificate=$certificate";
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

# print the page header
print_header($day, $month, $year, $resource, $resource_id, $makemodel, "");

// start the form
echo "<FORM NAME='main'>";

# Define the start and end of the day.
$am7=mktime($morningstarts,0,0,$month,$day,$year);
$pm7=mktime($eveningends,$eveningends_minutes,0,$month,$day,$year);

if ( $pview != 1 ) {
   echo "<table><tr><td width=\"100%\">";
   $params = array( "make" => $make, "model" => $model, "all" => $all, "type" => $certificate);
   #Show all schedulable resources
   displayResourceMenu("DAY", $year, $month, $day, $resource, $InstructorResource, $params, $makemodel);
   #Draw the three month calendars
   minicals($year, $month, $day, $resource, $InstructorResource, $makemodel);
   echo "</tr></table>";
}
#y? are year, month and day of yesterday
#t? are year, month and day of tomorrow

$i= mktime(0,0,0,$month,$day-1,$year);
$yy = date("Y",$i);
$ym = date("m",$i);
$yd = date("d",$i);

$i= mktime(0,0,0,$month,$day+1,$year);
$ty = date("Y",$i);
$tm = date("m",$i);
$td = date("d",$i);

#Show current date
echo "<h2 align=center>" . strftime("%A %d %B %Y", $am7) . "</h2>\n";

if ( $pview != 1 ) {
	#Show Go to day before and after links
	echo "<table width=\"100%\"><tr><td><a href=\"day.php?year=$yy&month=$ym&day=$yd&resource=$resource&InstructorResource=$InstructorResource$makemodel\">&lt;&lt; $lang[daybefore]</a></td>
	      <td align=center><a href=\"day.php?resource=$resource&InstructorResource=$InstructorResource$makemodel\">$lang[gototoday]</a></td>
	      <td align=right><a href=\"day.php?year=$ty&month=$tm&day=$td&resource=$resource&InstructorResource=$InstructorResource$makemodel\">$lang[dayafter] &gt;&gt;</a></td></tr></table>";
}

#We want to build an array containing all the data we want to show
#and then spit it out. 
#Get all appointments for today in the area that we care about
#Note: The predicate clause 'start_time <= ...' is an equivalent but simpler
#form of the original which had 3 BETWEEN parts. It selects all entries which
#occur on or cross the current day.
$res = 0;
if($resource == "Aircraft") 
{
	// get the resource information from the database
	$SchedulableID = sql_query1("
			SELECT schedulable_id
			FROM AircraftScheduling_schedulable 
			WHERE name='Aircraft' AND schedulable=1
			");
	
	// get the schedule information for this resource
	$sql = "SELECT 
	            AircraftScheduling_resource.resource_id, 
	            start_time, 
	            end_time, 
	            AircraftScheduling_entry.name, 
	            entry_id, 
	            type 
	      FROM AircraftScheduling_entry LEFT JOIN AircraftScheduling_resource USING (resource_id) 
	      WHERE AircraftScheduling_resource.schedulable_id=$SchedulableID 
	        AND start_time <= $pm7 AND end_time > $am7
	        AND entry_type != $EntryTypeStandby";
	$res = sql_query($sql);
	if (! $res) fatal_error(0, sql_error());
	
	// process the aircraft scheduling information
	ProcessScheduleInformation($res, $today, $resolution, $pm7, $am7);
}

if($InstructorResource == "Instructor" 
		|| $InstructorResource == "InstructorAG"
		|| $InstructorResource == "InstructorHM"
		|| $InstructorResource == "InstructorNS"
		|| $InstructorResource == "InstructorTZ") 
{
	// get the resource information from the database
	$SchedulableID = sql_query1("
			SELECT schedulable_id
			FROM AircraftScheduling_schedulable 
			WHERE name='Instructor' AND schedulable=1
			");
	
	// get the schedule information for this resource
	$sql = "SELECT AircraftScheduling_resource.resource_id, start_time, end_time, AircraftScheduling_entry.name, entry_id, type 
	      FROM AircraftScheduling_entry LEFT JOIN AircraftScheduling_resource USING (resource_id) 
	      WHERE AircraftScheduling_resource.schedulable_id=$SchedulableID 
	        AND start_time <= $pm7 AND end_time > $am7
	        AND entry_type != $EntryTypeStandby";
	$res = sql_query($sql);
	if (! $res) fatal_error(0, sql_error());

	// process the instructor scheduling information
	ProcessScheduleInformation($res, $today, $resolution, $pm7, $am7);
}

# We need to know what all the areas are called, so we can show them all
# pull the data from the db and store it. Convienently we can print the room 
# headings and capacities at the same time
$res = 0;
$res2 = 0;
if($resource == "Aircraft") 
{
	$sql = "
		SELECT DISTINCT n_number AS name, d.resource_id, e.name, e.display_page, a.status, c.model 
			FROM 
				AircraftScheduling_aircraft a, 
				AircraftScheduling_make b, 
				AircraftScheduling_model c, 
				AircraftScheduling_resource d, 
				AircraftScheduling_schedulable e 
			WHERE ";
	if($model)
		$sql .= "c.model='$model' AND ";
	if($make)
		$sql .= "b.make='$make' AND ";
	$sql .= "a.model_id=c.model_id AND 
				a.make_id=b.make_id AND 
				d.resource_id=a.resource_id AND 
				e.name='Aircraft' AND 
				e.schedulable=1 
			ORDER by 1";
	$res = sql_query($sql);
	if (!$res) fatal_error(0, sql_error());
}

if($InstructorResource == "Instructor") 
{
	$sql2 = "SELECT DISTINCT $DatabaseNameFormat, d.resource_id, e.name, e.display_page 
			FROM 
				AircraftScheduling_instructors a, 
				AircraftScheduling_person b, 
				AircraftScheduling_resource d, 
				AircraftScheduling_schedulable e 
			WHERE
				a.person_id=b.person_id AND 
				a.resource_id=d.resource_id AND
				e.name='Instructor' AND
				e.schedulable=1
			ORDER by last_name";
	$res2 = sql_query($sql2);
	if (!$res2) fatal_error(0, sql_error());
}
else if($InstructorResource == "InstructorAG") 
{
	$sql2 = "SELECT DISTINCT $DatabaseNameFormat, d.resource_id, e.name, e.display_page 
			FROM 
				AircraftScheduling_instructors a, 
				AircraftScheduling_person b, 
				AircraftScheduling_resource d, 
				AircraftScheduling_schedulable e 
			WHERE
				a.person_id=b.person_id AND 
				a.resource_id=d.resource_id AND
				e.name='Instructor' AND
				e.schedulable=1 AND
				ucase(left(last_name, 1)) >= 'A' AND 
				ucase(left(last_name, 1)) <= 'G'
			ORDER by last_name";
	$res2 = sql_query($sql2);
	if (!$res2) fatal_error(0, sql_error());
}
else if($InstructorResource == "InstructorHM") 
{
	$sql2 = "SELECT DISTINCT $DatabaseNameFormat, d.resource_id, e.name, e.display_page 
			FROM 
				AircraftScheduling_instructors a, 
				AircraftScheduling_person b, 
				AircraftScheduling_resource d, 
				AircraftScheduling_schedulable e 
			WHERE
				a.person_id=b.person_id AND 
				a.resource_id=d.resource_id AND
				e.name='Instructor' AND
				e.schedulable=1 AND
				ucase(left(last_name, 1)) >= 'H' AND 
				ucase(left(last_name, 1)) <= 'M'
			ORDER by last_name";
	$res2 = sql_query($sql2);
	if (!$res2) fatal_error(0, sql_error());
}
else if($InstructorResource == "InstructorNS") 
{
	$sql2 = "SELECT DISTINCT $DatabaseNameFormat, d.resource_id, e.name, e.display_page 
			FROM 
				AircraftScheduling_instructors a, 
				AircraftScheduling_person b, 
				AircraftScheduling_resource d, 
				AircraftScheduling_schedulable e 
			WHERE
				a.person_id=b.person_id AND 
				a.resource_id=d.resource_id AND
				e.name='Instructor' AND
				e.schedulable=1 AND
				ucase(left(last_name, 1)) >= 'N' AND 
				ucase(left(last_name, 1)) <= 'S'
			ORDER by last_name";
	$res2 = sql_query($sql2);
	if (!$res2) fatal_error(0, sql_error());
}
else if($InstructorResource == "InstructorTZ") 
{
	$sql2 = "SELECT DISTINCT $DatabaseNameFormat, d.resource_id, e.name, e.display_page 
			FROM 
				AircraftScheduling_instructors a, 
				AircraftScheduling_person b, 
				AircraftScheduling_resource d, 
				AircraftScheduling_schedulable e 
			WHERE
				a.person_id=b.person_id AND 
				a.resource_id=d.resource_id AND
				e.name='Instructor' AND
				e.schedulable=1 AND
				ucase(left(last_name, 1)) >= 'T' AND 
				ucase(left(last_name, 1)) <= 'Z'
			ORDER by last_name";
	$res2 = sql_query($sql2);
	if (!$res2) fatal_error(0, sql_error());
}

# It might be that there are no resouces defined for this area.
# If there are none then show an error and dont bother doing anything
# else
if ($res) $AircraftResultCount = sql_count($res);
else $AircraftResultCount = 0;
if ($res2) $InstructorResultCount = sql_count($res2);
else $InstructorResultCount = 0;
if ($AircraftResultCount == 0 && $InstructorResultCount == 0)
{
	echo "<h1>$lang[no_rooms_for_area]</h1>";
	if ($res) sql_free($res);
}
else
{
	// if we have a valid aircraft or instructor query, compute the column width from that
	if ($res && $res2)
		$room_column_width = (int)(95 / (sql_count($res) + sql_count($res2)));
	else if ($res)
		$room_column_width = (int)(95 / sql_count($res));
	else if ($res2)
		$room_column_width = (int)(95 / sql_count($res2));
	else
		$room_column_width = (int)(4);
	
	// if we have anything to display for the aircraft
	if ($res && sql_count($res) > 0)
	{
		for ($i = 0; ($row = sql_row($res, $i)); $i++)
		{
			// set the aircraft status
      		$AircraftStatus = LookupAircraftStatusString($row[4]);
        	
			if($row[3])
			{ 
				$HeaderNames[] = "<td align=\"center\" bgcolor=\"fde1dd\" width=\"$room_column_width%\">" . 
									"<A href='$row[3]?goback=" . GetScriptName() . "&name=" . preg_replace("/ /", "/+/", htmlspecialchars($row[0])) . "'>" . 
									htmlspecialchars($row[5]) . "<BR>" . 
									htmlspecialchars($AircraftStatus) . "<BR>" . 
									htmlspecialchars($row[0]) . "<BR>" . "</A>" . 
									"</TD>";
			}
			else
			{
				$HeaderNames[] = "<td align=\"center\" bgcolor=yellow width=\"$room_column_width%\">" . 
									htmlspecialchars($row[5]) . "<BR>" . 
									htmlspecialchars($AircraftStatus) . "<BR>" . 
									htmlspecialchars($row[0]) . "<BR>" .  
									"</TD>";
			}
			$rooms[] = $row[1];
		}
	}
	
	// if we have anything to display for the instructors
	if ($res2 && sql_count($res2) > 0)
	{
		for ($i = 0; ($row = sql_row($res2, $i)); $i++)
		{
			if($row[3]) 
			{
				$HeaderNames[] = "<td align=\"center\" bgcolor=\"fde1dd\" width=\"$room_column_width%\">" . 
									"<A href=\"$row[3]?goback=" . GetScriptName() . "&name=" . preg_replace("/ /", "/+/", htmlspecialchars($row[0])) . "\">" . 																								
									htmlspecialchars($row[0]) . "</A>" . 
									"</TD>";
			}
			else
			{
				$HeaderNames[] = "<td align=\"center\" bgcolor=\"fde1dd\" width=\"$room_column_width%\">" . 
									htmlspecialchars($row[0]) . 
									"</TD>";
			}
			$rooms[] = $row[1];
		}
	}
	
	#This is where we start displaying stuff
	echo "<table cellspacing=0 border=1 width=\"100%\">";
	echo "<tr><td align=\"center\" bgcolor=\"fde1dd\" width=\"1%\">$lang[time]</td>";
	
	// display the header values at the beginning of the table
	$tmpHeaderNames = $HeaderNames;
	foreach ($tmpHeaderNames as $key => $HeaderValue)
	{
		echo $HeaderValue;
	}
	
	echo "<td align=\"center\" bgcolor=\"fde1dd\" width=\"1%\">$lang[time]</td>";	
	echo "</tr>\n";
	
	# URL for highlighting a time. Don't use REQUEST_URI or you will get
	# the timetohighlight parameter duplicated each time you click.
	if($pview == 1)
		$hilite_url="day.php?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=$InstructorResource$makemodel&pview=1&timetohighlight";
	else
		$hilite_url="day.php?year=$year&month=$month&day=$day&resource=$resource&InstructorResource=$InstructorResource$makemodel&timetohighlight";

	# This is the main bit of the display
	# We loop through unixtime and then the times we just got

	for ($t = $am7; $t <= $pm7; $t += $resolution)
	{
		# Show the time linked to the URL for highlighting that time
		echo "<tr>";
		tdcell("red");
		echo "<a href=\"$hilite_url=$t\">" . date("H:i",$t) . "</a></td>";

		# Loop through the list of items we have for this area
		$EntryNumber = 0;
		foreach ($rooms as $key => $resource_id)
		{
			if(isset($today[$resource_id][$t]["id"]))
			{
				$id    = $today[$resource_id][$t]["id"];
				$color = $today[$resource_id][$t]["color"];
				$descr = htmlspecialchars($today[$resource_id][$t]["data"]);
			}
			else
				unset($id);
			
			# $c is the colour of the cell that the browser sees. White normally, 
			# red if were highlighting that line and a nice attractive green if the aircraft is booked.
			# We tell if its booked by $id having something in it
			if (isset($id))
				$c = $color;
			elseif (isset($timetohighlight) && ($t == $timetohighlight))
				$c = "red";
			else
				$c = "white";

			tdcell($c);

			echo "<center>";
			# If the aircraft isnt booked then allow it to be booked
			if(!isset($id))
			{
				$hour = date("H",$t);
				$minute  = date("i",$t);

				if ( $pview != 1 ) {
					echo "<a href='edit_entry.php" .
                        "?goback=" . GetScriptName() .
					    "&resource=$resource" .
					    "&InstructorResource=$InstructorResource" .
					    "&resource_id=$resource_id" .
					    "&hour=$hour" .
					    "&minute=$minute" .
					    "&year=$year&month=$month&day=$day" .
					    "$makemodel'>" .
					    "<img src=image.php?src=new.jpg width=10 height=10 border=0></a>";
				} else echo '&nbsp;';
			}
			elseif ($descr != "")
			{
				#if it is booked then show 
				echo " <a href='view_entry.php" .
                        "?goback=" . GetScriptName() .
					    "&id=$id" .
					    "&day=$day&month=$month&year=$year" .
					    "&resource=$resource" .
					    "&InstructorResource=$InstructorResource" .
					    "$makemodel'>$descr</a>";
			}
			else
				echo "&nbsp;\"&nbsp;";
			echo "</center></td>\n";
			
			$EntryNumber = $EntryNumber + 1;
		}
		# Show the time linked to the URL for highlighting that time
		tdcell("red");
		echo "<a href=\"$hilite_url=$t\">" . date("H:i",$t) . "</a></td>";
		echo "</tr>\n";
		reset($rooms);
	}
	
	// display the header values at the end of the table
	echo "<tr><td align=\"center\" bgcolor=\"fde1dd\" width=\"1%\">$lang[time]</td>";
	$tmpHeaderNames = $HeaderNames;
	foreach ($tmpHeaderNames as $key => $HeaderValue)
	{
		echo $HeaderValue;
	}
	
	echo "<td align=\"center\" bgcolor=\"fde1dd\" width=\"1%\">$lang[time]</td>";	
	echo "</tr>\n";

	echo "</table>";
	//if ( $pview != 1 ) show_colour_key();
}

// end the form
echo "</FORM>";

include "trailer.inc";

//echo getmicrotime() - $start_time . " seconds to process this document";

?>
