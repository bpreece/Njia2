<?php

include_once 'common.inc';
include_once 'timebox/schedule_list_options_form.php';
include_once 'timebox/query_timeboxes.php';

global $show_closed_tasks;
global $timebox_end_date;
$show_closed_tasks = '';
$timebox_end_date = '';

function get_stylesheets() {
    $stylesheets = array('schedules.css');
    return $stylesheets;
}

function get_page_class() {
    return 'schedules-page';
}

global $timeboxes, $tasks;

function process_query_string() {
    global $show_closed_tasks;
    global $timebox_end_date;
    
    if (isset($_GET['tx'])) {
        $show_closed_tasks = TRUE;
    }
    
    if (isset($_GET['xx'])) {
        $timebox_end_date = $_GET['xx'];
    }
}

/*
function process_form_data() {
}
*/

function prepare_page_data() {
    global $show_closed_tasks;
    global $timebox_end_date;

    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }
    
    global $timeboxes;
    $timeboxes = query_timeboxes($connection, $show_closed_tasks, $timebox_end_date);
}

function show_sidebar() {
    global $show_closed_tasks;
    global $timebox_end_date;
    
    echo "
        <div class='sidebar-block'>";
    show_schedule_list_options_form($show_closed_tasks, $timebox_end_date);
    echo "
        </div>";
}

function show_content() {
    global $timeboxes, $user;
    
    echo "
        <h3>Schedules</h3>";
    if (! $timeboxes) {
        echo "
            <div>You currently have no scheduled tasks.</div>";
        return;
    }
    
    echo "
            <div id='schedules-list'>";
    foreach ($timeboxes as $timebox_id => $timebox) {
        $project_id = $timebox['project-id'];
        $timebox_css = 'timebox-header object-header';
        if ($timebox['timebox-expired']) {
            $timebox_css .= ' object-unscheduled';
        }
        echo "
                <div id='timebox-$timebox_id' class='timebox'>
                    <div class='$timebox_css'>
                        <div class='timebox-details'>
                            <div class='timebox-end-date'>
                                <a class='object-ref' href='timebox.php?id=$timebox_id'>${timebox['timebox-end-date']}</a>
                            </div>
                        </div>
                        <div class='timebox-id'>$timebox_id</div>
                        <div class='timebox-name'>
                            <a class='object-ref' href='timebox.php?id=$timebox_id'>${timebox['timebox-name']}</a>
                        </div>
                        <div class='project-info object-${timebox['project-status']}'>
                            <div class='project-id'>$project_id</div>
                            <div class='project-name'>
                                <a class='object-ref' href='project.php?id=$project_id'>${timebox['project-name']}</a>
                            </div>
                        </div> <!-- /project-info -->
                    </div> <!-- /timebox-info -->
                    <div class='task-list object-list'>";
        foreach ($timebox['task-list'] as $task_id => $task) {
            echo "
                        <div id='task-$task_id' class='task object-element'>
                            <div class='task-header object-header object-${task['task-status']}'>
                                <div class='task-details'>
                                     <div class='task-user'>
                                         <a class='object-ref' href='user.php?id=${task['user-id']}'>${task['user-name']}</a>
                                     </div>
                                 </div> <!-- /task-details -->
                                 <div class='task-id'>$task_id</div>
                                 <div class='task-summary'>
                                     <a class='object-ref' href='task.php?id=$task_id'>${task['task-summary']}</a>
                                 </div>
                             </div> <!-- /task-info -->
                        </div> <!-- /task-$task_id -->";
        }
        echo "
                    </div> <!-- /task-list -->
                </div> <!-- /timebox-$timebox_id -->";
    }
    echo "
            </div> <!-- /schedules-list -->";
}

include_once ('template.inc');

?>
