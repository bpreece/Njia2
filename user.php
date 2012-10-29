<?php


include_once('common.inc');

function get_stylesheets() {
    $stylesheets = array('user.css');
    return $stylesheets;
}

function get_page_id() {
    global $query_user;
    $page_class = "user-${query_user['user_id']}";
    return $page_class;
}

function get_page_class() {
    return 'user-page';
}

global $query_user;

function process_query_string() {
    global $query_user;
    $session_user = get_session_user();
    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    }
    $query_user = query_user($user_id);
}

function process_form_data() {
    if (isset($_POST['update-button'])) {
        process_user_form();
    } else if (isset($_POST['close-account-button'])) {
        process_close_account();
    } else if (isset($_POST['new-account-button'])) {
        process_new_account();
    }
}

function process_user_form() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }
    
    $user_id = get_session_user_id();
    if ($user_id != $_POST['user-id']) {
        header ("Location: user.php");
        return;
    }
    
    if (isset($_POST['new-password']) && $_POST['new-password'] != $_POST['repeat-password']) {
        set_user_message("The new passwords do not match.  Please try again.", 'warning');
        return;
    }

    $login_name = mysqli_real_escape_string($connection, $_POST['login-name']);
    $old_password = mysqli_real_escape_string($connection, $_POST['old-password']);
    
    if (isset($_POST['new-password'])) {
        $new_password = mysqli_real_escape_string($connection, $_POST['new-password']);
        $query = "UPDATE `user_table` SET 
            `login_name` = '$login_name' , 
            `password` =  MD5(CONCAT(`password_salt`,'$new_password'))
            WHERE `user_id` = '$user_id' AND 
                `password` = MD5(CONCAT(`password_salt`,'$old_password')) ";
    } else {
        $query = "UPDATE `user_table` SET 
            `login_name` = '$login_name' 
            WHERE `user_id` = '$user_id' AND 
                `password` = MD5(CONCAT(`password_salt`,'$old_password')) ";
    }
    $result = mysqli_query($connection, $query);
    if (! $result) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    if (mysqli_affected_rows($connection) == 0) {
        set_user_message("The changes could not be applied.  Please check your password and try again.", 'warning');
        return;
    }
    
    set_user_message("The changes have been applied", 'success');
}

function process_close_account() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }

    $user_id = mysqli_real_escape_string($connection, $_POST['user-id']);
    if (!is_admin_session() && $user_id != get_session_user_id()) {
        header('Location: user.php');
        return;
    }
    
    $query = "UPDATE `user_table`
        SET `account_closed_date` = NOW()
        WHERE `user_id` = '$user_id'";
    $result = mysqli_query($connection, $query);
    if (! $result) {
        set_user_message(mysqli_errno($connection), 'failure');
        return;
    }
    
    header("Location: login.php");
}

function process_new_account() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }
    
    if (!is_admin_session()) {
        header('Location: user.php');
        return;
    }
    
    $login_name = mysqli_real_escape_string($connection, $_POST['login-name']);
    $password = mysqli_real_escape_string($connection, $_POST['login-password']);
    $password_salt = md5($login_name . date('Y-m-d'));
    $query = "INSERT INTO `user_table` 
        ( `login_name` , `password` , `password_salt` )
        VALUES 
        ( '$login_name' , MD5(CONCAT('$password_salt','$password')) , '$password_salt')";
    $result = mysqli_query($connection, $query);
    if (! $result) {
        $error = mysqli_error($connection);
        if (substr($error, 0, 15) == 'Duplicate entry') {
            set_user_message("The name '$login_name' is already used.  Please select another.", 'warning');
        } else {
            set_user_message($error, 'failure');
        }
        return;
    }
}

function query_user($user_id) {
    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }
    
    if (! $user_id || ! is_admin_session()) {
        $user_id = get_session_user_id();
    }

    $user_id = mysqli_real_escape_string($connection, $user_id);
    $user_query = "SELECT U.`user_id` , U.`login_name` 
                FROM `user_table` AS U 
                WHERE U.`user_id` = '$user_id'";
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    $num_users = mysqli_num_rows($user_result);
    if ($num_users == 0) {
        return;
    } else {
        $query_user = mysqli_fetch_array($user_result);
    }
    
    $query_user['project-list'] = array();
    $project_query = "SELECT P.* , 
            O.`user_id` AS `owner_id` , O.`login_name` AS `owner_name` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `user_table` AS O ON P.`project_owner` = O.`user_id`
        WHERE P.`project_owner` = '$user_id'
        ORDER BY P.`project_id`";
    $project_results = mysqli_query($connection, $project_query);
    if (! $project_results) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($project = mysqli_fetch_array($project_results)) {
            $query_user['project-list'][$project['project_id']] = $project;
        }
    }
    
    return $query_user;
}

function show_sidebar() {
    global $query_user;
    echo "
        <h3>Options</h3>";
    if (! $query_user) {
        return;
    }

    echo "
        <div class='sidebar-block'>
            <form id='close-account-form' method='post'>
                <input type='hidden' name='user-id' value='${query_user['user_id']}'>
                <input type='submit' name='close-account-button' value='Close this account'></input>
            </form>
        </div>";
        
    if (is_admin_session()) {
        echo "
        <div class='sidebar-block'>
            <form id='new-account-form' method='post'>
                <div id='login-name'>
                    <label for='login-name'>Login name:</label>
                    <input style='width:100%' type='text' name='login-name'></input>
                </div>
                <div id='login-password'>
                    <label for='login-password'>Login password:</label>
                    <input style='width:100%' type='password' name='login-password'></input>
                </div>
                <input type='submit' name='new-account-button' value='Create new account'></input>
            </form>
        </div>";
    }
}

function show_content() 
{    
    global $query_user;
    if (!$query_user) {
        show_user_message("There was an error retrieving the information", 'warning');
        return;
    }
    
    echo "
        <h3>User ${query_user['user_id']} &mdash; ${query_user['login_name']}</h3>
        <form id='user-form' class='main-form' method='post'>
            <input type='hidden' name='user-id' value='${query_user['user_id']}'>

            <div id='login-name'>
                <label for='login-name'>Login name:</label>
                <input style='width:15em' type='text' name='login-name' value='${query_user['login_name']}'></input>
            </div>

            <div id='old-password'>
                <label for='old-password'>Old password:</label>
                <input style='width:15em' type='password' name='old-password'></input>
            </div>

            <div id='new-password'>
                <label for='new-password'>New password:</label>
                <input style='width:15em' type='password' name='new-password'></input>
            </div>

            <div id='repeat-password'>
                <label for='repeat-password'>Repeat password:</label>
                <input style='width:15em' type='password' name='repeat-password'></input>
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='update-button' value='Update'></input>
            </div> <!-- /form-controls -->

        </form>";
    
    echo "
        <h4>Projects</h4>
        <div class='project-list'>";
    foreach ($query_user['project-list'] as $project_id => $project) {
        echo "
                <div id='project-$project_id' class='project'>";
        if ($project['project_status'] != 'open') {
            echo "
                    <div class='project-details'>${project['project_status']}</div>";
        }
        echo "
                    <div class='project-info project-${project['project_status']}'>
                        <div class='project-id'>$project_id</div>
                        <div class='project-name'>
                            <a class='object-ref' href='project.php?id=$project_id'>${project['project_name']}</a>
                        </div> <!-- /project-name -->
                    </div> <!-- /project-info -->
                </div> <!-- /project-$project_id -->";
    }
    echo "
        </div> <!-- /user-list -->
        ";
}

include_once ('template.inc');

?>
