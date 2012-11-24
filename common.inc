
<?php 

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
$global_title = "";
$logo_image = "image/NJIA-v.png";

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
    echo "<div id='copyright'>Njia &copy; Copyright 2012, Ben Preece</div>";
}

function connect_to_database() 
{
    $connection = mysqli_connect("localhost", "njia", "njia");
    if (! $connection) {
        show_user_message(mysqli_error($connection), 'failure');
        return;
    }
    
    $database = mysqli_select_db($connection, 'njia');
    if (! $database) {
        show_user_message(mysqli_error($connection), 'failure');
        return;
    }
    
    return $connection;
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
    return $session_user['user_permissions'] == 'admin';
}

function get_session_id_cookie() 
{
    global $session_id, $session_user_id;
    if (isset($_COOKIE['njia-session-id'])) {
        $session_id = $_COOKIE['njia-session-id'];
        return $session_id;
    } else {
        return;
    }
}

/**
 * 
 * @global type $session_id
 * @global type $session_user_id
 * @return type the user's record as an array
 */
function validate_user($connection)
{
    global $session_id, $session_user;
    
    $user_query = "SELECT U.*
        FROM `session_table` AS S 
        INNER JOIN `user_table` AS U on U.`user_id` = S.`user_id`
        WHERE S.`session_id` = '$session_id' AND 
            S.`session_expiration_date` >= NOW()";
    $user_results = mysqli_query($connection, $user_query);
    if (! $user_results) {
        set_user_message(mysqli_error($connection), "warning");
        return FALSE;
    }

    $session_user = mysqli_fetch_array($user_results);
    if (! $session_user) {
        return FALSE;
    }
    
    $expire_query = "UPDATE `session_table` 
        SET `session_expiration_date` = DATE_ADD(NOW(), INTERVAL 1 HOUR)
        WHERE `session_id` = '$session_id'";
    $expire_results = mysqli_query($connection, $expire_query);
    if (! $expire_results) {
        set_user_message(mysqli_error($connection), "warning");
    }

    return TRUE;
}

/**
 * Creates an entry in the session table for the given user, and returns  the
 * session ID of the new entry.
 * @param type $user_id
 * @param type $connection
 * @return string the value of the session cookie
 */
function set_session_id($user_id, $connection) 
{
    $session_id = md5(microtime());
    $query = "INSERT INTO `session_table` 
        ( `session_id` , `user_id` , `session_expiration_date`  ) 
        VALUES ( '$session_id' , '$user_id' , DATE_ADD(NOW(), INTERVAL 1 HOUR) )";
    if (! mysqli_query($connection, $query)) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    setcookie('njia-session-id', $session_id);
    return $session_id;
}

function connect_to_database_session() 
{
    $session = get_session_id_cookie();
    if (!$session) {
        header('Location: login.php?x');
        return;
    }

    $connection = connect_to_database();
    if (! $connection) {
        set_user_message("Failed connecting to database", 'failure');
        return;
    }
    
    if (! validate_user($connection)) {
        header('Location: login.php?x');
        return;
    }
    
    return $connection;
}

/**
 * Use the database to get start and end dates
 * Sets: $start_date, $end_date
 */
function calculate_range_dates($connection, &$start_date, &$end_date) {
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
    $date_results = mysqli_query($connection, $date_query);
    if (! $date_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return FALSE;
    } else {
        $date_result = mysqli_fetch_array($date_results);
        $start_date = $date_result['start_date'];
        $end_date = $date_result['end_date'];
        return TRUE;
    }
}

?>