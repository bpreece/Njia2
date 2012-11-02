<?php

include_once('common.inc');
include_once('data.inc');

global $user_id, $user_list, $log_date_list, $start_date, $end_date;
$start_date = '';
$end_date = '';

/*
 * Process query string from the URL
 */
function process_query_string() {
    global $user_id, $start_date, $end_date;

    if (isset($_GET['u'])) {
        $user_id = $_GET['u'];
    }
    if (isset($_GET['e'])) {
        $end_date = $_GET['e'];
    }
    if (isset($_GET['s'])) {
        $start_date = $_GET['s'];
    }
}

/*
 * Process submitted form data
 */

function process_form_data() {
}

/*
 * Fetch page contents from the database
 */
function prepare_page_data() {
    global $user_id, $user_list, $log_date_list, $start_date, $end_date;
    $connection = connect_to_database_session();
    if (! $connection) {
        set_user_message("Failed accessing database", 'failure');
        return;
    }

    // default to the session user
    $session_user_id = get_session_user_id();
    if (! $user_id) {
        $user_id = $session_user_id;
    }
    
    /*
     * Use the database to get start and end dates
     */
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
        return;
    } else {
        $date_result = mysqli_fetch_array($date_results);
        $start_date = $date_result['start_date'];
        $end_date = $date_result['end_date'];
    }

    /*
     * Fetch list of users with common projects and verify that this user is
     * one of them
     */

    $user_list = fetch_user_list($connection);
    if (!array_key_exists($user_id, $user_list)) {
        $user_id = $session_user_id;
    }
    
    $log_query = "SELECT L.`log_id` , L.`description` , L.`work_hours` , 
        DATE( L.`log_time` ) AS `log_date` , 
        T.`task_id` , T.`task_summary` 
        FROM `project_table` AS P
        INNER JOIN  `access_table` AS A1 ON P.`project_id` = A1.`project_id` 
        INNER JOIN  `access_table` AS A2 ON P.`project_id` = A2.`project_id` 
        INNER JOIN  `user_table` AS U ON A1.`user_id` = U.`user_id` 
        INNER JOIN `task_table` AS T ON P.`project_id` = T.`project_id` 
        INNER JOIN `log_table` AS L ON L.`task_id` = T.`task_id` 
        WHERE A2.`user_id` = '$session_user_id' 
            AND L.`user_id` = '$user_id' 
            AND DATE( L.`log_time` ) <= '$end_date' 
            AND DATE( L.`log_time` ) >= '$start_date' 
        ORDER BY  L.`log_time` ASC , T.`task_id` ";

    // sort the log results by date first, then task, and log entry
    $log_results = mysqli_query($connection, $log_query);
    if (! $log_results) {
        set_user_message(mysqli_error($connection), 'failure');
        return;
    } else {
        $log_date_list = array();
        while ($log = mysqli_fetch_array($log_results)) {
            $date = $log['log_date'];
            $log_id = $log['log_id'];
            $task_id = $log['task_id'];
            $work_hours = $log['work_hours'];
            if (!array_key_exists($date, $log_date_list)) {
                $log_date_list[$date] = array();
                $log_date_list[$date]['work-hours'] = 0;
                $log_date_list[$date]['task-list'] = array();
            }

            $log_date_list[$date]['work-hours'] += $work_hours;
            if (!array_key_exists($task_id, $log_date_list[$date]['task-list'])) {
                $log_date_list[$date]['task-list'][$task_id] = array();
                $log_date_list[$date]['task-list'][$task_id]['work-hours'] = 0;
                $log_date_list[$date]['task-list'][$task_id]['task-summary'] = $log['task_summary'];
                $log_date_list[$date]['task-list'][$task_id]['log-list'] = array();
            }
            $log_date_list[$date]['task-list'][$task_id]['work-hours'] += $work_hours;
            $log_date_list[$date]['task-list'][$task_id]['log-list'][$log_id] = array(
                'work-hours'=> $log['work_hours'], 
                'description' => $log['description'],
            );
        }
    }
}

function get_stylesheets() {
    $stylesheets = array('log.css');
    return $stylesheets;
}

function get_page_id() {
    return 'log-page';
}

function get_page_class() {
    global $user_id;
    return "log-user-$user_id";
}

function show_sidebar() {
    global $user_id, $user_list, $start_date, $end_date;

    echo "
        <div class='sidebar-block'>
            <form id='user-id-form' method='GET'>
                <div id='user-id-field' class='group'>
                    <label for='user-id'>Show log for:</label>
                    <select name='u' style='width:100%'>";
            foreach ($user_list as $field_user_id => $field_user) {
                $selected = ($user_id == $field_user_id) ? "selected='selected'" : "";
                echo "
                        <option value='$field_user_id' $selected>${field_user['login_name']}</option>";
            }
            echo "
                    </select>
                </div>
                <div id='start-date-field' class='group'>
                    <label for='s'>From:</label>
                    <input type='text' name='s' style='width:100%' value='$start_date' />
                </div>
                <div id='end-date-field' class='group'>
                    <label for='e'>To:</label>
                    <input type='text' name='e' style='width:100%' value='$end_date' />
                </div>
                <input type='submit' value='Show log'></input>
            </form>
        </div>";
}

function show_content() {
    global $user_id, $user_list, $log_date_list, $start_date, $end_date;
    
    $user_name = $user_list[$user_id]['login_name'];
    echo "
        <div class='log-dates'>$start_date &mdash; $end_date</div>
        <h3><a class='object-ref' href='user.php?id=$user_id'>$user_name</a></h3>
        <div class='work-log-list'>";
    foreach ($log_date_list as $date => $date_info) {
        echo "
            <div class='date-log'>
                <div class='date-header'>
                    <div class='date-details'>Total hours: ${date_info['work-hours']}</div>
                    <div class='date-date'>$date</div>
                </div>
                <div class='task-log-list'>";
        foreach ($date_info['task-list'] as $task_id => $task_info) {
            echo "
                    <div id='task-$task_id-log' class='task-log'>
                        <div class='task-header'>
                            <div class='task-details'>Total hours: ${task_info['work-hours']}</div>
                            <div class='task-summary'>
                                <a class='object-ref' href='task.php?id=$task_id'>${task_info['task-summary']}</a>
                            </div>
                        </div>
                        <div class='log-list'>";
            foreach ($task_info['log-list'] as $log_id => $log) {
                echo "
                            <div id='log-$log_id' class='log'>
                                <div class='log-description'>${log['description']}</div>
                                <div class='log-details'>${log['work-hours']} hrs.</div>
                            </div> <!-- /log-$log_id -->";
            }
            echo "
                        </div> <!-- /log-list -->
                    </div> <!-- /task-$task_id-log -->";
        }
        echo "
                </div> <!-- /task-log-list -->
            </div> <!-- /date-log -->";
    }
    echo "
        </div> <!-- /work-log-list -->";
}

include_once ('template.inc');

?>