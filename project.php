<?php

include_once 'common.inc';
include_once 'task/new_task_form.php';
include_once 'task/tasks_list.php';
include_once 'timebox/new_timebox_form.php';
include_once 'project/close_project_form.php';
include_once 'project/reopen_project_form.php';
include_once 'project/add_user_to_project_form.php';
include_once 'project/remove_user_from_project_form.php';
include_once 'project/project_options_form.php';
include_once 'project/project_form.php';
include_once 'project/query_projects.php';

global $project_id, $project;
global $show_closed_tasks, $show_subtasks;
global $timebox_end_date;
$show_closed_tasks = FALSE;
$show_subtasks = FALSE;
$timebox_end_date = '';

function process_query_string() {
    global $show_closed_tasks, $show_subtasks;
    global $timebox_end_date;
    global $project_id, $project;
    
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
    } else {
        header ('Location: projects.php');
    }
    
    if (isset($_GET['tx'])) {
        $show_closed_tasks = TRUE;
    }
    
    if (isset($_GET['ts'])) {
        $show_subtasks = TRUE;
    }
    
    if (isset($_GET['s'])) {
        $timebox_end_date = $_GET['s'];
    }
}

global $project, $project_id;

function process_form_data() {
    process_project_form()
    || process_new_task_form()
    || process_close_project_form()
    || process_reopen_project_form()
    || process_new_timebox_form()
    || process_add_user_to_project_form()
    || process_remove_user_from_project_form();
}

function prepare_page_data() {
    global $project_id, $project;
    global $show_closed_tasks, $show_subtasks, $timebox_end_date;
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $user_id = get_session_user_id();
    $project = query_project($project_id, $user_id, $show_closed_tasks, $show_subtasks);
}

function get_stylesheets() {
    $stylesheets = array('project.css');
    return $stylesheets;
}

function get_page_id() {
    return 'project-page';
}

function get_page_class() {
    global $project;
    if (! $project) {
        return '';
    }
    $page_class = "project-${project['project_id']}";
    if ($project['project_status'] == 'closed') {
        $page_class .= " project-closed";
    }
    return $page_class;
}

function show_sidebar() {
    global $project_id, $project;

    if (! $project) {
        return;
    }

    if ($project['project_status'] == 'closed') {
        echo "
            <div class='sidebar-block'>";
        show_reopen_project_form($project_id);
        echo "
            </div>";
    } else {
        echo "
            <div class='sidebar-block'>";
        show_new_task_form($project_id);
        echo "
            </div>";
        
        echo "
            <div class='sidebar-block'>";
        show_new_timebox_form($project_id);
        echo "
            </div>";
        
        if ($project['project_owner'] == get_session_user_id()) {
            echo "
                <div class='sidebar-block'>";
            show_add_user_to_project_form($project_id);
            echo "
                </div>";
        }
        
        if ($project['project_owner'] == get_session_user_id() && $project['can-close']) {
            echo "
                <div class='sidebar-block'>";
            show_close_project_form($project_id);
            echo "
                </div>";
        }
    }
}

function show_content() 
{
    global $show_closed_tasks, $show_subtasks;
    global $timebox_end_date, $end_date;
    global $project_id, $project;
    
    echo "
        <h3>Project $project_id</h3>";
    
    if (! $project) {
        return;
    }
    
    show_project_form($project_id, $project);

    echo "
        <div id='tasks-header'>
            <div class='header-controls'>";
    show_project_options_form($project_id, 'tasks', $show_closed_tasks, $show_subtasks, $timebox_end_date);
    echo "
            </div>
            <h4>Tasks</h4>
        </div>
        <div class='task-list object-list'>";
    if (count($project['task-list']) == 0) {
        echo "
            <div>There are no tasks to display.</div>";
    } else {
        show_tasks_list($project['task-list']);
    }
    echo "
        </div> <!-- /task-list -->";

    echo "
        <div id='timeboxes-header'>
            <div class='header-controls'>";
    show_project_options_form($project_id, 'timeboxes', $show_closed_tasks, $show_subtasks, $timebox_end_date);
    echo "
            </div>
            <h4>Timeboxes</h4>
        </div>
        <div class='timebox-list object-list'>";
    foreach ($project['timebox-list'] as $timebox_id => $timebox) {
        echo "
            <div id='timebox-$timebox_id' class='timebox object-element'>
                <div class='timebox-details'>${timebox['timebox_end_date']}</div>
                <div class='timebox-header object-header'>
                    <div class='timebox-id'>$timebox_id</div>
                    <div class='timebox-name'>
                        <a class='object-ref' href='timebox.php?id=$timebox_id'>${timebox['timebox_name']}</a>
                    </div> <!-- /timebox-name -->
                </div> <!-- /timebox-info -->
            </div> <!-- /timebox-$timebox_id -->";
    }
    echo "
        </div> <!-- /timebox-list -->";
    
    echo "
        <h4>People</h4>
        <div class='user-list object-list'>";
    foreach ($project['user-list'] as $user_id => $user) {
        echo "
            <div id='user-$user_id' class='user object-element'>
                <div class='user-header object-header'>
                    <div class='user-id'>$user_id</div>";
        $project_owner = $project['project_owner'];
        if ($project_owner == get_session_user_id() && $project_owner != $user_id) {
            echo "
                    <div style='float:right'>";
            show_remove_user_from_project_form($project_id, $user_id, $user_name);
            echo "
                    </div> <!-- /remove-user-$user_id-form -->";
        }
        echo "
                    <div class='user-name'>
                        <a class='object-ref' href='user.php?id=$user_id'>${user['user_name']}</a>
                    </div> <!-- /user-name -->
                </div> <!-- /user-info -->
            </div> <!-- /user-$user_id -->";
    }
    echo "
        </div> <!-- /user-list -->
        ";
}

include_once ('template.inc');

?>
