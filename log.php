<?php

include_once 'common.inc';
include_once 'data.inc';
include_once 'log/log_list_form.php';
include_once 'log/query_log.php';
include_once 'user/query_users.php';

global $user, $user_id, $user_list, $log_date_list;
global $start_date, $end_date, $total_work_hours;
$start_date = '';
$end_date = '';
$total_work_hours = 0;

/*
 * Process query string from the URL
 */
function process_query_string() {
    global $user_id, $user_name, $start_date, $end_date;

    if (isset($_GET['id'])) {
        $user_id = $_GET['id'];
    } else if (isset($_GET['n'])) {
        $user_name = $_GET['n'];
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
/*
function process_form_data() {

}
 */

/*
 * Fetch page contents from the database
 */
function prepare_page_data() {
    global $user, $user_id, $user_name, $user_list, $log_date_list, $start_date, $end_date, $total_work_hours;
    
    if (connect_to_database_session()) {
        db_calculate_range_dates($start_date, $end_date);
        $user = find_user($user_list, $user_id, $user_name);
        if ($user) {
            $session_user_id = is_admin_session() ? $user['user_id'] : get_session_user_id();
            $log_date_list = query_user_log($user['user_id'], $start_date, $end_date, $total_work_hours, $session_user_id);
        }
    }
}

function get_stylesheets() {
    $stylesheets = array('log.css');
    return $stylesheets;
}

function get_page_id() {
    global $user_id;
    return "log-page-user-$user_id";
}

function get_page_class() {
    return 'log-page';
}

function show_sidebar() {
    global $user, $user_list, $start_date, $end_date;

    echo "
        <div class='sidebar-block'>";
    show_log_list_form($user_list, $user, $start_date, $end_date, is_admin_session());
    echo "
        </div>";
}

function show_content() {
    global $user, $log_date_list, $start_date, $end_date, $total_work_hours;
    
    if (! $user) {
        return;
    }

    echo "
        <h3><a class='object-ref' href='user.php?id=${user['user_id']}'>${user['login_name']}</a></h3>";
    if (! $log_date_list) {
        echo "
            <div>There are no entries in the current work log for ${user['login_name']}.</div>";
        return;
    }

    echo "
        <div class='work-log-details'>Total hours: $total_work_hours</div>
        <div class='work-log-dates'>$start_date &mdash; $end_date</div>
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
                        <div class='task-info'>
                            <div class='task-details'>Total hours: ${task_info['work-hours']}</div>
                            <div class='task-summary'>
                                <a class='object-ref' href='task.php?id=$task_id'>${task_info['task-summary']}</a>
                            </div>
                        </div>
                        <div class='log-entry-list'>";
            foreach ($task_info['log-list'] as $log_id => $log) {
                echo "
                            <div id='log-entry-$log_id' class='log-entry'>
                                <div class='log-entry-details'>${log['work-hours']} hrs.</div>
                                <div class='log-entry-description'>${log['description']}</div>
                            </div> <!-- /log-$log_id -->";
            }
            echo "
                        </div> <!-- /log-entry-list -->
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
