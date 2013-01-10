
<?php 

global $admin_email, $njia_url;
$admin_email = 'admin@bpreece.com';
$njia_url = 'localhost/Njia2';

global $mysqli;
$mysqli = TRUE;

global $db_host, $db_name, $db_user, $db_password;
$db_host = 'localhost';
$db_name = 'njia';
$db_user = 'njia';
$db_password = 'njia';

// a user message type is expected to be one of "failure", "warning", "success",
// or "info";  these styles are defined in style.css
global $user_messages;
$user_messages = array();

function set_user_message($message_text, $message_type) {
    global $user_messages;
    $user_message = array(
        'text' => $message_text,
        'type' => $message_type
    );
    $user_messages[] = $user_message;
}

function show_user_messages() {
    global $user_messages;
    echo "<div class='user-messages'>";
    foreach ($user_messages as $message) {
        show_user_message($message['text'], $message['type']);
    }
    echo "</div> <!-- /user-messages -->";
}

function show_user_message($message, $message_type)  {
    echo "<div class='user-message $message_type'>$message</div> <!-- user-message -->";
}

global $global_title, $logo_image;
$global_title = "Njia-&beta;";
$logo_image = "image/NJIA-B.png";

function get_global_title() {
    global $global_title;
    return $global_title;
}

function get_logo_image() {
    global $logo_image;
    return $logo_image;
}


function show_main_menu() {
    echo "MAIN MENU";
}

function show_footer() {
    echo "<div id='copyright'>Njia Beta version 0.01-&beta;-1 &copy; Copyright 2012-2013, Ben Preece</div>";
}

global $_connection;

function db_connect() 
{
    global $mysqli, $_connection;
    global $db_host, $db_name, $db_user, $db_password;
    
    if ($mysqli) {
        $_connection = mysqli_connect($db_host, $db_user, $db_password);
    } else {
        $_connection = mysql_connect($db_host, $db_user, $db_password);
    }
    if (! $_connection) {
        set_user_message("The Njia database is not accessible.", 'failure');
        return FALSE;
    }
    
    if ($mysqli) {
        $database = mysqli_select_db($_connection, $db_name);
    } else {
        $database = mysql_select_db($db_name, $_connection);
    }
    if (! $database) {
        show_db_error();
        return FALSE;
    }
    
    return $_connection;
}

function show_db_error($connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }
    
    global $mysqli;
    if ($mysqli) {
        show_user_message(mysqli_error($_connection), 'failure');
    } else {
        show_user_message(mysql_error($_connection), 'failure');
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
        return mysqli_query($_connection, $query);
    } else {
        return mysql_query($query, $_connection);
    }
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

    $results = db_query($query);
    if (! $results) {
        show_db_error();
        return FALSE;
    } else {
        return TRUE;
    }
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

    $result = db_query($query);
    if (! $result) {
        show_db_error();
        return NULL;
    }
    
    return db_result_to_array($result);
}

/**
 * 
 * @global type $_connection
 * @param type $index
 * @param type $query
 * @param type $connection
 * @return array an empty array if the database query fails, or if
 */
function db_fetch_list($index, $query, $connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    $result = db_query($query);
    
    $result_list = array();
    if (! $result) {
        show_db_error();
    } else {
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

    $result = db_query($query);
    
    $result_list = array();
    if (! $result) {
        show_db_error();
    } else {
        while ($item = db_result_to_array($result)) {
            $result_list[] = $item;
        }
    }
    return $result_list;
}

/*
 * Gets the value of the session-id cookie.
 * Returns an array of two values: the user id, and session id
 */
global $session_id, $session_user;

function get_session_id() {
    global $session_id;
    return $session_id;
}

function get_session_user() {
    global $session_user;
    return $session_user;
}

function get_session_user_id() {
    global $session_user;
    return $session_user['user_id'];
}

function is_admin_session() {
    global $session_user;
    $permissions = explode(',', $session_user['user_permissions']);
    return in_array('admin', $permissions);
}

function get_session_id_cookie() 
{
    global $session_id;
    if (isset($_COOKIE['njia-session-id'])) {
        $session_id = $_COOKIE['njia-session-id'];
        return $session_id;
    } else {
        return FALSE;
    }
}

/**
 * 
 * @global type $session_id
 * @global type $session_user_id
 * @return type the user's record as an array
 */
function validate_user($connection = NULL)
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }
    
    global $session_id, $session_user;
    
    $user_query = "SELECT U.*
        FROM `session_table` AS S 
        INNER JOIN `user_table` AS U on U.`user_id` = S.`user_id`
        WHERE S.`session_id` = '$session_id' AND 
            S.`session_expiration_date` >= NOW()";
    $session_user = db_fetch($user_query);
    if (! $session_user) {
        return FALSE;
    }
    
    $expire_query = "UPDATE `session_table` 
        SET `session_expiration_date` = DATE_ADD(NOW(), INTERVAL 1 HOUR)
        WHERE `session_id` = '$session_id'";
    db_execute($expire_query, $connection);
    // ignore the results

    return TRUE;
}

/**
 * Check the access table to determine whether the session user has access to
 * the given project.
 * @global type $_connection
 * @param type $project_id
 * @param type $connection
 * @return boolean 
 */
function authorize_project($project_id, $connection = NULL) 
{
    if (is_admin_session()) {
        return TRUE;
    }
    
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }
    
    $session_user_id = get_session_user_id();
    $query = "SELECT * 
        FROM `access_table` AS A 
        WHERE A.`user_id` = '$session_user_id' 
            AND A.`project_id` = '$project_id' ";
    $access = db_fetch($query, $connection);
    if (! $access) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * Check the access table to determine whether the session user has access to
 * the given task.
 * @global type $_connection
 * @param type $project_id
 * @param type $connection
 * @return boolean 
 */
function authorize_task($task_id, $connection = NULL) 
{
    if (is_admin_session()) {
        return TRUE;
    }
    
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }
    
    $session_user_id = get_session_user_id();
    $query = "SELECT A.*  
        FROM `access_table` AS A 
        INNER JOIN `task_table` AS T ON A.`project_id` = T.`project_id` 
        WHERE A.`user_id` = '$session_user_id' 
            AND T.`task_id` = '$task_id' ";
    $access = db_fetch($query, $connection);
    if (! $access) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * Check the access table to determine whether the session user has access to
 * the given task.
 * @global type $_connection
 * @param type $project_id
 * @param type $connection
 * @return boolean 
 */
function authorize_timebox($timebox_id, $connection = NULL) 
{
    if (is_admin_session()) {
        return TRUE;
    }
    
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }
    
    $session_user_id = get_session_user_id();
    $query = "SELECT * 
        FROM `access_table` AS A 
        INNER JOIN `timebox_table` AS X ON A.`project_id` = X.`project_id` 
        WHERE A.`user_id` = '$session_user_id' 
            AND X.`timebox_id` = '$timebox_id' ";
    $access = db_fetch($query, $connection);
    if (! $access) {
        return FALSE;
    } else {
        return TRUE;
    }
}

/**
 * Creates an entry in the session table for the given user, and returns  the
 * session ID of the new entry.
 * @param type $user_id
 * @param type $connection
 * @return string the value of the session cookie
 */
function set_session_id($user_id, $connection = NULL) 
{
    global $_connection;
    if ($connection == NULL) {
        $connection = $_connection;
    }

    $session_id = md5(microtime());
    $query = "INSERT INTO `session_table` (
            `session_id` , `user_id` , `session_expiration_date` 
        ) VALUES ( 
            '$session_id' , '$user_id' , DATE_ADD( NOW(), INTERVAL 1 HOUR ) 
        )";
    if (! db_execute($query, $connection)) {
        return FALSE;
    }

    setcookie('njia-session-id', $session_id);
    return $session_id;
}

/**
 * 
 * @global type $_connection
 * @return boolean
 */
function connect_to_database_session() 
{
    $session = get_session_id_cookie();
    if (!$session) {
        header('Location: login.php?x');
        return FALSE;
    }

    if (! db_connect()) {
        return FALSE;
    }

    if (! validate_user()) {
        header('Location: login.php?x');
        return FALSE;
    }

    global $_connection;
    return $_connection;
}

/**
 * Use the database to get start and end dates
 * Sets: $start_date, $end_date
 */
function db_calculate_range_dates(&$start_date, &$end_date) 
{
    $date_query = "SELECT ";
    if ($end_date) {
        $date_query .= "'$end_date' AS `end_date` ";
    } else {
        $date_query .= "DATE( NOW() ) AS `end_date` ";
    }
    if ($start_date) {
        $date_query .= " , 
            '$start_date' AS `start_date` ";
    } else {
        if ($end_date) {
            $date_query .= " , 
                DATE( DATE_SUB( '$end_date', INTERVAL 13 DAY ) ) AS `start_date` ";
        } else {
            $date_query .= " , 
                DATE( DATE_SUB( NOW(), INTERVAL 13 DAY ) ) AS `start_date` ";
        }
    }
    $date_result = db_fetch($date_query);
    if (! $date_result) {
        return FALSE;
    } else {
        $start_date = $date_result['start_date'];
        $end_date = $date_result['end_date'];
        return TRUE;
    }
}

?>