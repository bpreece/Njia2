<?php

/* 
 * Copyright © 2014 Ben Preece.
 * All rights reserved.
 */

/* 
 * File: db.php
 * Created on: May 27, 2014, 8:35:42 PM
 * Author: ben
 * Description:
 * 
 */


global $mysqli;
$mysqli = TRUE;

global $db_host, $db_name, $db_user, $db_password;
$db_host = 'localhost';
$db_name = 'thegither';
$db_user = 'thegither';
$db_password = 'thegither';


global $_connection;
$_connection = NULL;

function db_connect($db_host, $db_name, $db_user, $db_password) 
{
    global $mysqli, $_connection;
    
    if ($mysqli) {
        $_connection = mysqli_connect($db_host, $db_user, $db_password);
    } else {
        $_connection = mysql_connect($db_host, $db_user, $db_password);
    }
    
    if ($mysqli) {
        $database = mysqli_select_db($_connection, $db_name);
    } else {
        $database = mysql_select_db($db_name, $_connection);
    }
    if (! $database) {
        return false;
    }
    
    return $_connection;
}

function db_error($connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }
    
    global $mysqli;
    if ($mysqli) {
        return mysqli_error($connection);
    } else {
        return mysql_error($connection);
    }
}

/**
 * Escape the SQL characters in the string so that the result is safe to store
 * in the database
 * NOTE that db_escape requires a connection;  if one is not provided, then
 * the call to db_escape must come AFTER a previous call to db_connect() or 
 * connect_to_database_session() so that the default connection is available.
 * 
 * @global type $_connection
 * @global boolean $mysqli
 * @param type $string
 * @param type $connection
 * @return type
 */
function db_escape($string, $connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }
    
    global $mysqli;
    if ($mysqli) {
        return mysqli_real_escape_string($connection, $string);
    } else {
        return mysql_real_escape_string($string, $connection);
    }
}

function db_last_index($connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    global $mysqli;
    if ($mysqli) {
        return mysqli_insert_id($connection);
    } else {
        return mysql_insert_id($connection);
    }
}

function db_query($query, $connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    global $mysqli;
    if ($mysqli) {
        $results = mysqli_query($connection, $query);
    } else {
        $results = mysql_query($query, $connection);
    }
    
    return $results;
}

function db_result_to_array($result)
{
    global $mysqli;    
    if ($mysqli) {
        return mysqli_fetch_array($result);
    } else {
        return mysql_fetch_array($result);
    }
}

/**
 * 
 * @global type $_connection
 * @param type $query
 * @param type $connection
 * @return boolean
 */
function db_execute($query, $connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    $results = db_query($query, $connection);
    return ($results == NULL) ? FALSE : TRUE;
}

/**
 * 
 * @global type $_connection
 * @param type $query
 * @param type $connection
 * @return mixed NULL if the database query fails, or if the database query
 *               returns no result;  an array indexed by the database column
 *               names if the database query returns a result.  If the query
 *               returns more than one result, only the first is returned and
 *               the other results are ignored.
 */
function db_fetch($query, $connection = NULL) 
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    $result = db_query($query, $connection);
    return $result ? db_result_to_array($result) : NULL;
}

/**
 * 
 * @global type $_connection
 * @param type $index
 * @param type $query
 * @param type $connection
 * @return array an empty array if the database query fails, or if the 
 *               database query returns no result;  an array, indexed by the
 *               database index, of arrays indexed by the database column
 *               names if the database query returns a result.
 */
function db_fetch_list($index, $query, $connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    $result = db_query($query, $connection);
    
    $result_list = array();
    if ($result) {
        while ($item = db_result_to_array($result)) {
            $result_list[$item[$index]] = $item;
        }
    }
    
    return $result_list;
}

/**
 * 
 * @global type $_connection
 * @param type $query
 * @param type $connection
 * @return type
 */
function db_fetch_array($query, $connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    $result = db_query($query, $connection);
    
    $result_list = array();
    if ($result) {
        while ($item = db_result_to_array($result)) {
            $result_list[] = $item;
        }
    }
    return $result_list;
}


// initialize the connection to the database

if ($_connection == NULL) {
    $_connection = db_connect($db_host, $db_name, $db_user, $db_password);
}
if ($_connection == NULL) {
    $_SESSION['messages'][] = array(
        'text' => 'Could not connect to database: ' . db_error(),
        'type' => 'failure',
    );
}