<?php
// This file based on Meeting Room Booking System http://mrbs.sourceforge.net
// It has been modified by Matt Barclay (mbarclay@users.sourceforge.net on 
// 12/19/2001 and released under the terms of the GNU Public License

// AircraftScheduling created from OpenFBO by Jim Covington. OpenFBO aspires to be
// a lot more than AircraftScheduling so I renamed it to keep from causing confusion.

// $Id: mysql.inc,v 1.2 2001/12/20 07:05:57 mbarclay Exp $

// mysql.inc - Simple PHP database support for MySQL.
// Include this file after defining the following variables:
//   $db_host = The hostname of the database server
//   $db_login = The username to use when connecting to the database
//   $db_password = The database account password
//   $db_database = The database name.
// Including this file connects you to the database, or exits on error.

// Free a results handle. You need not call this if you call sql_row or
// sql_row_keyed until the row returns 0, since sql_row frees the results
// handle when you finish reading the rows.
function sql_free ($r)
{
	mysqli_free_result($r);
}

// Execute a non-SELECT SQL command (insert/update/delete).
// Returns the number of tuples affected if OK (a number >= 0).
// Returns -1 on error; use sql_error to get the error message.
function sql_command ($sql)
{
	global $db_c;
	
	if (mysqli_query($db_c, $sql)) return mysqli_affected_rows($db_c);
	return -1;
}
// return the first row of a result
function sql_result ($r)
{
	mysqli_data_seek($r, 0);
	$result = mysqli_fetch_row($r);
	return $result[0];
}

// Execute an SQL query which should return a single non-negative number value.
// This is a lightweight alternative to sql_query, good for use with count(*)
// and similar queries. It returns -1 on error or if the query did not return
// exactly one value, so error checking is somewhat limited.
// It also returns -1 if the query returns a single NULL value, such as from
// a MIN or MAX aggregate function applied over no rows.
function sql_query1 ($sql)
{
	global $db_c;
	
	$r = mysqli_query($db_c, $sql);
	if (! $r) return -1;
	if (mysqli_num_rows($r) != 1 || mysqli_num_fields($r) != 1
		|| ($result = sql_result($r)) == "") $result = -1;
	mysqli_free_result($r);
	return $result;
}

// Execute an SQL query. Returns a database-dependent result handle,
// which should be passed back to sql_row or sql_row_keyed to get the results.
// Returns 0 on error; use sql_error to get the error message.
function sql_query ($sql)
{
	global $db_c;
	
	$r = mysqli_query($db_c, $sql);
	
	return $r;
}

// Return a row from a result. The first row is 0.
// The row is returned as an array with index 0=first column, etc.
// When called with i >= number of rows in the result, cleans up from
// the query and returns 0.
// Typical usage: $i = 0; while ((a = sql_row($r, $i++))) { ... }
function sql_row ($r, $i)
{
	if ($i >= mysqli_num_rows($r))
	{
		mysqli_free_result($r);
		return 0;
	}
	mysqli_data_seek($r, $i);
	return mysqli_fetch_row($r);
}

// Return a row from a result as an associative array keyed by field name.
// The first row is 0.
// This is actually upward compatible with sql_row since the underlying
// routing also stores the data under number indexes.
// When called with i >= number of rows in the result, cleans up from
// the query and returns 0.
function sql_row_keyed ($r, $i)
{
	if ($i >= mysqli_num_rows($r))
	{
		mysqli_free_result($r);
		return 0;
	}
	mysqli_data_seek($r, $i);
	return mysqli_fetch_array($r);
}

// Return the number of rows returned by a result handle from sql_query.
function sql_count ($r)
{
	return mysqli_num_rows($r);
}

// Return the value of an autoincrement field from the last insert.
// Must be called right after an insert on that table!
function sql_insert_id($table, $field)
{
	global $db_c;
	
	return mysqli_insert_id($db_c);
}

// Return the text of the last error message.
function sql_error()
{
	global $db_c;
	
	return mysqli_error($db_c);
}

// Begin a transaction. Rolls back any leftover transaction first
// (safety cleanup for persistent connections that may have been
// returned to the pool dirty after a script died mid-transaction).
function sql_begin()
{
	global $db_c;
	mysqli_rollback($db_c);
	mysqli_begin_transaction($db_c);
}

// Commit a transaction. See sql_begin().
function sql_commit()
{
	global $db_c;
	mysqli_commit($db_c);
}

// Roll back a transaction. Call this on error after sql_begin().
function sql_rollback()
{
	global $db_c;
	mysqli_rollback($db_c);
}

// Acquire a mutual-exclusion lock on the named table. For portability:
// This will not lock out SELECTs.
// It may lock out DELETE/UPDATE/INSERT or not, depending on the implementation.
// It will lock out other callers of this routine with the same name argument.
// It may timeout in 20 seconds and return 0, or may wait forever.
// It returns 1 when the lock has been acquired.
// Caller must release the lock with sql_mutex_unlock().
// Caller must not have more than one mutex at any time.
// Do not mix this with sql_begin()/sql_end() calls.
//
// In MySQL, we avoid table locks, and use low-level locks instead.
function sql_mutex_lock($name)
{
	global $sql_mutex_shutdown_registered, $sql_mutex_unlock_name;
	if (!sql_query1("SELECT GET_LOCK('$name', 20)")) return 0;
	$sql_mutex_unlock_name = $name;
	if (empty($sql_mutex_shutdown_registered))
	{
		register_shutdown_function("sql_mutex_cleanup");
		$sql_mutex_shutdown_registered = 1;
	}
	return 1;
}

// Release a mutual-exclusion lock on the named table. See sql_mutex_unlock.
function sql_mutex_unlock($name)
{
	global $db_c;	
	global $sql_mutex_unlock_name;
	
	sql_query1("SELECT RELEASE_LOCK('$name')");
	$sql_mutex_unlock_name = "";
}

// Shutdown function to clean up a forgotten lock. For internal use only.
function sql_mutex_cleanup()
{
	global $sql_mutex_shutdown_registered, $sql_mutex_unlock_name;
	
	if (!empty($sql_mutex_unlock_name))
	{
		sql_mutex_unlock($sql_mutex_unlock_name);
		$sql_mutex_unlock_name = "";
	}
}


// Return a string identifying the database version:
function sql_version()
{
	$r = sql_query("select version()");
	$v = sql_row($r, 0);
	sql_free($r);
	return "MySQL $v[0]";
}


// Generate non-standard SQL for LIMIT clauses:
function sql_syntax_limit($count, $offset)
{
	return " LIMIT $offset,$count ";
}

// Generate non-standard SQL to output a TIMESTAMP as a Unix-time:
function sql_syntax_timestamp_to_unix($fieldname)
{
	return " UNIX_TIMESTAMP($fieldname) ";
}

// Generate non-standard SQL to match a string anywhere in a field's value
// in a case insensitive manner. $s is the un-escaped/un-slashed string.
// In MySQL, REGEXP seems to be case sensitive, so use LIKE instead. But this
// requires quoting of % and _ in addition to the usual.
function sql_syntax_caseless_contains($fieldname, $s)
{
	$s = str_replace("\\", "\\\\", $s);
	$s = str_replace("%", "\\%", $s);
	$s = str_replace("_", "\\_", $s);
	$s = str_replace("'", "''", $s);
	return " $fieldname LIKE '%$s%' ";
}

// Establish a database connection.
// On connection error, the message will be output without a proper HTML
// header. There is no way I can see around this; if track_errors isn't on
// there seems to be no way to supress the automatic error message output and
// still be able to access the error text.
global $db_c;

if (empty($db_nopersist))
	$db_c = mysqli_connect("p:".$db_host, $db_login, $db_password);
else
	$db_c = mysql_connect($db_host, $db_login, $db_password);

if (!$db_c || !mysqli_select_db ($db_c, $db_database))
{
	echo "\n<p>\nFatal Error: Failed to connect to database\n";
	exit;
}
mysqli_autocommit($db_c, true);
?>
