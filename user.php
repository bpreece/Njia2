<?php


include_once('common.inc');

global $show_closed_member_projects;
global $show_closed_owned_projects;
global $work_log_start_date;
global $work_log_end_date;

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

global $query_user, $user_id;

function process_query_string() {
    global $show_closed_member_projects;
    global $show_closed_owned_projects;
    global $work_log_start_date;
    global $work_log_end_date;
    global $query_user, $user_id;

    if (isset($_GET['po'])) {
        $show_closed_owned_projects = 'checked';
    }
    if (isset($_GET['pm'])) {
        $show_closed_member_projects = 'checked';
    }
    if (isset($_GET['ls'])) {
        $work_log_start_date = $_GET['ls'];
    }
    if (isset($_GET['le'])) {
        $work_log_end_date = $_GET['le'];
    }
    
    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    } else {
        $user_id = get_session_user_id();
    }
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

function prepare_page_data() {
    global $query_user, $user_id;
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }

    // fetch the user information only if we share a common project
    $session_user_id = get_session_user_id();
    $user_id = mysqli_real_escape_string($connection, $user_id);
    $user_query = "SELECT U.`user_id` , U.`login_name` AS  `user_name` 
        FROM  `project_table` AS P
        INNER JOIN  `access_table` AS A1 ON P.`project_id` = A1.`project_id` 
        INNER JOIN  `access_table` AS A2 ON P.`project_id` = A2.`project_id` 
        INNER JOIN  `user_table` AS U ON A1.`user_id` = U.`user_id` 
        WHERE A1.`user_id` =  '$user_id'
            AND A2.`user_id` =  '$session_user_id'";
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    if ( ($query_user = mysqli_fetch_array($user_result)) == NULL) {
        set_user_message("User $user_id was not found", 'warning');
        return;
    }

    global $show_closed_member_projects;
    global $show_closed_owned_projects;
    
    // projects which are owned by $user_id, and which are accessible to
    // get_session_user_id();
    $query_user['owned-project-list'] = array();
    $owner_query = "SELECT P.`project_id` , P.`project_name` , P.`project_status` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        WHERE P.`project_owner` = '$user_id' AND A.`user_id` = '$session_user_id' ";
    if (! $show_closed_owned_projects) {
        $owner_query .= "
            AND P.`project_status` <> 'closed'";
    }
    $owner_query .= "
        ORDER BY P.`project_id`";
    $owner_results = mysqli_query($connection, $owner_query);
    if (! $owner_results) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($project = mysqli_fetch_array($owner_results)) {
            $query_user['owned-project-list'][$project['project_id']] = $project;
        }
    }
    
    // projects which are accessible to both $user_id and get_session_user_id();
    $query_user['project-member-list'] = array();
    $member_query = "SELECT P.`project_id` , P.`project_name` , P.`project_status` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        WHERE P.`project_owner` <> '$user_id' AND A.`user_id` = '$user_id' ";
    if (! $show_closed_member_projects) {
        $member_query .= "
            AND P.`project_status` <> 'closed'";
    }
    $member_query .= "
        ORDER BY P.`project_id`";
    $member_results = mysqli_query($connection, $member_query);
    if (! $member_results) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($project = mysqli_fetch_array($member_results)) {
            $query_user['project-member-list'][$project['project_id']] = $project;
        }
    }
    
    global $work_log_start_date;
    global $work_log_end_date;
    
    $log_query = "SELECT P.`project_id` , P.`project_name` , 
            T.`task_id` , T.`task_summary` , 
            L.`log_id` , L.`description` , L.`work_hours` , L.`log_time` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `task_table` AS T ON P.`project_id` = T.`project_id` 
        INNER JOIN `log_table` AS L ON L.`task_id` = T.`task_id` 
        WHERE A.`user_id`= '$session_user_id' 
            AND L.`user_id` = $user_id ";
    if ($work_log_end_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) <= '$work_log_end_date' ";
    }
    if ($work_log_start_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) >= '$work_log_start_date' ";
    } else {
        if ($work_log_end_date) {
            $log_query .= "
            AND DATE( L.`log_time` ) >= DATE_SUB( '$work_log_end_date', INTERVAL 13 DAY ) ";
        } else {
            $log_query .= "
            AND DATE( L.`log_time` ) >= DATE_SUB( DATE( NOW() ), INTERVAL 13 DAY ) ";
        }
    }
    $log_query .= "
        ORDER BY P.`project_id` , T.`task_id` , L.`log_time` ";
    $log_results = mysqli_query($connection, $log_query);
    $query_user['log-list'] = array();
    if (! $log_results) {
        set_user_message(mysqli_error($connection), 'failure');
    } else {
        while ($log = mysqli_fetch_array($log_results)) {
            $query_user['log-list'][$log['log_id']] = $log;
        }
    }
    
    return $query_user;
}

function show_sidebar() {
    global $query_user;
    if (! $query_user) {
        return;
    }

    if ($query_user == get_session_user_id()) {
        echo "
        <div class='sidebar-block'>
            <form id='close-account-form' method='post'>
                <input type='hidden' name='user-id' value='${query_user['user_id']}'>
                <input type='submit' name='close-account-button' value='Close this account'></input>
            </form>
        </div>";
    }        
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
    global $show_closed_member_projects;
    global $show_closed_owned_projects;
    global $work_log_start_date;
    global $work_log_end_date;
    global $query_user;
    if (!$query_user) {
        return;
    }
    
    $query_id = $query_user['user_id'];
    echo "
        <div class='user-header object-header'>
            <h3>User $query_id</h3>
        </div>
        <form id='user-form' class='main-form' method='post'>
            <input type='hidden' name='user-id' value='$query_id'>

            <div id='login-name'>
                <label for='login-name'>Login name:</label>
                <input style='width:15em' type='text' name='login-name' value='${query_user['user_name']}'></input>
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
        <div id='owned-projects-header'>
            <div class='header-controls' style='float:right'>
                <form method='GET'>
                    <input type='hidden' name='id' value='$query_id' />";
    if ($show_closed_member_projects) {
        echo "
                    <input type='hidden' name='pm' value='$show_closed_member_projects' />";
    }
    echo "
                    <input type='hidden' name='ls' value='$work_log_start_date' />
                    <input type='hidden' name='le' value='$work_log_end_date' />
                    <input type='checkbox' name='po' value='YES' $show_closed_owned_projects /><label>Show closed projects</label>
                    <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                </form>
            </div>
            <h4>Project owner</h4>
        </div>
        <div class='project-list'>";
    foreach ($query_user['owned-project-list'] as $project_id => $project) {
        echo "
                <div id='project-$project_id' class='project object-element'>";
        if ($project['project_status'] != 'open') {
            echo "
                    <div class='project-details'>${project['project_status']}</div>";
        }
        echo "
                    <div class='project-header object-header object-${project['project_status']}'>
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
    
    echo "
        <div id='project-member-header'>
            <div class='header-controls' style='float:right'>
                <form method='GET'>
                    <input type='hidden' name='id' value='$query_id' />";
    if ($show_closed_owned_projects) {
        echo"
                    <input type='hidden' name='po' value='$show_closed_owned_projects' />";
    }
    echo "
                    <input type='hidden' name='ls' value='$work_log_start_date' />
                    <input type='hidden' name='le' value='$work_log_end_date' />
                    <input type='checkbox' name='pm' value='YES' $show_closed_member_projects /><label>Show closed projects</label>
                    <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                </form>
            </div>
            <h4>Project member</h4>
        </div>
        <div class='project-list'>";
    foreach ($query_user['project-member-list'] as $project_id => $project) {
        echo "
                <div id='project-$project_id' class='project object-element'>";
        if ($project['project_status'] != 'open') {
            echo "
                    <div class='project-details'>${project['project_status']}</div>";
        }
        echo "
                    <div class='project-header object-header object-${project['project_status']}'>
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
    
    echo "
        <div id='work-log-header'>
            <div class='header-controls' style='float:right'>
                <form method='GET'>
                    <input type='hidden' name='id' value='$query_id' />";
    if ($show_closed_member_projects) {
        echo"
                    <input type='hidden' name='pm' value='$show_closed_member_projects' />";
    }
    if ($show_closed_owned_projects) {
        echo"
                    <input type='hidden' name='po' value='$show_closed_owned_projects' />";
    }
    echo "
                    <label for='work-log-start-date'>Show from</label>
                    <input type='text' size='12' value='$work_log_start_date' />
                    <label for='work-log-end-date'>to</label>
                    <input type='text' size='12' value='$work_log_end_date' />
                    <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                </form>
            </div>
            <h4>Work log</h4>
        </div>
        <div class='work-log-list'>";
    foreach($query_user['log-list'] as $log_id => $log) {
        echo "
            <div id='log-$log_id' class='log-entry'>
                <div class='log-time'>${log['log_time']}</div>
                <div class='log-description'>
                    ${log['description']}
                </div> <!-- /log-description -->
                <div class='log-details'>";
            if ($log['work_hours']) {
                echo "${log['work_hours']} hours";
            }
            echo "
                </div>
            </div> <!-- /log-$log_id -->";
    }
    echo "
        </div> <!-- /work-log-list -->";
}

include_once ('template.inc');

?>
