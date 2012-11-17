<?php


include_once 'common.inc';
include_once 'user/user_form.php';
include_once 'user/close_account_form.php';
include_once 'user/reopen_account_form.php';

global $show_closed_member_projects;
global $show_closed_owned_projects;
global $work_log_start_date;
global $work_log_end_date;
global $account_closed;

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
    process_user_form()
    || process_close_account_form()
    || process_reopen_account_form();
}

function prepare_page_data() {
    global $query_user, $user_id, $account_closed;
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }

    // if we're not admin, then fetch the user information only if we share 
    // a common project
    
    $session_user_id = get_session_user_id();
    $user_id = mysqli_real_escape_string($connection, $user_id);
    if (is_admin_session()) {
        $user_query = "SELECT U.`user_id` , U.`login_name` AS  `user_name` , 
                U.`account_closed_date` 
            FROM `user_table` AS U
            WHERE `user_id` = '$user_id'";
    } else {
        $user_query = "SELECT U.`user_id` , U.`login_name` AS  `user_name` , 
                U.`account_closed_date` 
            FROM  `project_table` AS P
            INNER JOIN  `access_table` AS A1 ON P.`project_id` = A1.`project_id` 
            INNER JOIN  `access_table` AS A2 ON P.`project_id` = A2.`project_id` 
            INNER JOIN  `user_table` AS U ON A1.`user_id` = U.`user_id` 
            WHERE A1.`user_id` =  '$user_id'
                AND A2.`user_id` =  '$session_user_id'";
    }
    $user_result = mysqli_query($connection, $user_query);
    if (! $user_result) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    }
    if ( ($query_user = mysqli_fetch_array($user_result)) == NULL) {
        set_user_message("User $user_id was not found", 'warning');
        return;
    }
    if (isset($query_user['account_closed_date'])) {
        $account_closed = TRUE;
        set_user_message('This account has been closed', 'warning');
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
    global $user_id, $account_closed;
    
    if (! $account_closed && $user_id != 1 && ($user_id == get_session_user_id() || is_admin_session())) {
        echo "
            <div class='sidebar-block'>";
        show_close_account_form($user_id);
        echo "
            </div>";
    }
    
    if ($account_closed && is_admin_session()) {
        echo "
            <div class='sidebar-block'>";
        show_reopen_account_form($user_id);
        echo "
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
        <h3>User $query_id</h3>";

    show_user_form($query_id, $query_user['user_name']);
    
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
