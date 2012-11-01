<?php

include_once('common.inc');

global $user, $task_id, $start_date, $end_date;

function process_query_string() {
    global $user;
    if (isset($_GET['u'])) {
        $user = query_user($_GET['u']);
    } else {
        $user = get_session_user();
    }
    if (isset($_GET['e'])) {
        $end_date = $_GET['e'];
    }
    if (isset($_GET['s'])) {
        $start_date = $_GET['s'];
    }
}

function process_form_data() {
    if (isset($_POST['show-log-button']))  {
        header("Location: log.php?id=${_POST['user-id']}");
    }
}

function query_log($user_id) {
    global $user, $user_list;

    $connection = connect_to_database_session();
    if (!$connection) {
        return;
    }
    
    $session_user_id = get_session_user_id();
    $log_query = "SELECT L.`log_id` , L.`description` , L.`work_hours` , 
        DATE( L.`log_time` ) AS `log_date` , 
        T.`task_id` , T.`task_summary` 
        FROM  `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `task_table` AS T ON P.`project_id` = T.`project_id` 
        INNER JOIN `log_table` AS L ON L.`task_id` = T.`task_id` 
        WHERE A.`user_id` = '$session_user_id' 
            AND L.`user_id` = '$user_id' ";
    if ($end_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) <= '$end_date' ";
    }
    if ($start_date) {
        $log_query .= "
            AND DATE( L.`log_time` ) >= '$start_date' ";
    } else {
        if ($end_date) {
            $log_query .= "
                AND DATE( L.`log_time` ) >= DATE_SUB( '$end_date', INTERVAL 13 DAY ) ";
        } else {
            $log_query .= "
                AND DATE( L.`log_time` ) >= DATE_SUB( NOW(), INTERVAL 13 DAY ) ";
        }
    }
    $log_query .= "
        ORDER BY  L.`log_time`";
    set_user_message($log_query, 'debug');
    
    $log_results = mysqli_query($connection, $log_query);
    if (! $log_results) {
        return;
    }
    
    $log_date_list = array();
    while ($log = mysqli_fetch_array($log_results)) {
        $date = $log['log_date'];
        $log_id = $log['log_id'];
        $task_id = $log['task_id'];
        $work_hours = $log['work_hours'];
        $log_date_list[$date]['work_hours'] += $work_hours;
        $log_date_list[$date][$task_id]['work_hours'] += $work_hours;
        $log_date_list[$date][$task_id][$log_id] = array(
            'work-hours'=> $log['work_hours'], 
            'description' => $log['description'],
        );
    }
    set_user_message(var_export($log_date_list, TRUE), 'debug');
}

function get_stylesheets() {
    $stylesheets = array('log.css');
    return $stylesheets;
}

function get_page_id() {
    return 'log-page';
}

function get_page_class() {
    global $user;
    if (! $user) {
        $user = get_session_user();
    }
    return "log-user-${user['user_id']}";
}

function show_sidebar() {
    global $user, $user_list;

    set_user_message(var_export($user, TRUE), 'debug');
    if (! $user) {
        return;
    }
    echo "
        <div class='sidebar-block'>
            <form id='user-id-form' method='post'>
                <div id='user-id-field' class='group'>
                    <label for='user-id'>Show log for:</label>
                    <select name='user-id' style='width:100%'>";
            foreach ($user_list as $log_user_id => $login_name) {
                $selected = ($user['user_id'] == $log_user_id) ? "selected='selected'" : "";
                echo "
                        <option value='$log_user_id' $selected>$login_name</option>";
            }
            echo "
                    </select>
                </div>
                <input type='submit' name='show-log-button' value='Show log'></input>
            </form>
        </div>";
}

function show_content() {
    global $user;
    
    if (! $user) {
        return;
    }
    
    query_log($user['user_id']);
    
    echo "
        <h3><a class='object-ref' href='user.php?id=${user['user_id']}'>${user['login_name']}</a></h3>";
    if (! $projects) {
        echo "
            <div>There are no tasks in your current to-do list.</div>";
        return;
    }
}

include_once ('template.inc');

?>
