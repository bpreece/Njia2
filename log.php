<?php

include_once 'common.inc';
include_once 'data.inc';
include_once 'log/log_list_form.php';
include_once 'log/query_log.php';

global $user_id, $user_list, $log_date_list, $start_date, $end_date, $total_work_hours;
$start_date = '';
$end_date = '';

/*
 * Process query string from the URL
 */
function process_query_string() {
    global $user_id, $start_date, $end_date;

    if (isset($_GET['u'])) {
        $user_id = $_GET['u'];
    } else {
        $user_id = get_session_user_id();
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
    global $user_id, $user_list, $log_date_list, $start_date, $end_date, $total_work_hours;
    
    if (connect_to_database_session()) {
        // default to the session user
        $session_user_id = get_session_user_id();
        if (! $user_id) {
            $user_id = $session_user_id;
        }

        db_calculate_range_dates($start_date, $end_date);

        /*
         * Fetch list of users with common projects and verify that this user is
         * one of them
         */

        $user_list = fetch_user_list();
        if ($user_id != $session_user_id && !array_key_exists($user_id, $user_list)) {
            set_user_message("User $user_id was not found; displaying your information instead.", 'warning');
            $user_id = $session_user_id;
        }

        $log_date_list = query_user_log($user_id, $start_date, $end_date, $total_work_hours);
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
    global $user_id, $user_list, $start_date, $end_date;

    echo "
        <div class='sidebar-block'>";
    show_log_list_form($user_list, $user_id, $start_date, $end_date);
    echo "
        </div>";
}

function show_content() {
    global $user_id, $user_list, $log_date_list, $start_date, $end_date, $total_work_hours;
    
    if (! $log_date_list) {
        return;
    }

    $user_name = $user_list[$user_id]['login_name'];
    echo "
        <h3><a class='object-ref' href='user.php?id=$user_id'>$user_name</a></h3>
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
