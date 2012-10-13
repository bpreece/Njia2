
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
$global_title = "Njia 2";
$logo_image = "image/logo.png";

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
    echo "<div id='copyright'>&copy; Copyright 2012, Ben Preece</div>";
}

function connect_to_database() 
{
    $connection = mysqli_connect("localhost", "njia", "njia");
    if (! $connection) {
        show_user_message(mysqli_error($connection), 'failure');
        return null;
    }
    
    $database = mysqli_select_db($connection, 'njia');
    if (! $database) {
        show_user_message(mysqli_error($connection), 'failure');
        return null;
    }
    
    return $connection;
}

/*
 * Gets the value of the session-id cookie.
 * Returns an array of two values: the user id, and session id
 */
global $session_id, $session_user_id;

function get_session_id() {
    global $session_id;
    return $session_id;
}

function get_session_id_cookie() 
{
    global $session_id, $session_user_id;
    if (isset($_COOKIE['njia-session-id'])) {
        $cookie = $_COOKIE['njia-session-id'];
        $session = $cookie ? explode('-', $cookie) : null;
        $session_user_id = $session[0];
        $session_id = $session[1];
        return $session;
    } else {
        return NULL;
    }
}

function set_session_id($user_id, $connection) 
{
    $session_id = md5(microtime());
    $query = "INSERT INTO `session_table` (`session_id`, `user_id`) VALUES ('$session_id', '$user_id')";
    if (! mysqli_query($connection, $query)) {
        send_error(mysqli_error($connection));
        return;
    }
    
    $cookie= $user_id . '-' . $session_id;
    setcookie('njia-session-id', $cookie);

    return $cookie;
}

function connect_to_database_session() {
    $session = get_session_id_cookie();
    if (!$session) {
        set_user_message("You must log in to access this page", "warning");
        return null;
    }

    return connect_to_database();
}

?>