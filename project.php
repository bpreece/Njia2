<?php

include_once('common.inc');

global $show_closed_tasks;
global $past_timeboxes_date;
$show_closed_tasks = '';
$past_timeboxes_date = '';

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

function process_query_string() {
    global $show_closed_tasks;
    global $past_timeboxes_date;
    global $project_id, $project;
    
    if (isset($_GET['T'])) {
        $show_closed_tasks = 'checked';
    }
    if (isset($_GET['X'])) {
        $past_timeboxes_date = $_GET['X'];
    }
    if (isset($_GET['id'])) {
        $project_id = $_GET['id'];
    }
}

global $project, $project_id;

function process_form_data() {
    if (isset($_POST['update-button'])) {
        process_project_form();
    } else if (isset($_POST['add-task-button'])) {
        process_add_task_form();
    } else if (isset($_POST['add-timebox-button'])) {
        process_add_timebox_form();
    } else if (isset($_POST['new-project-button'])) {
        process_new_project_form();
    } else if (isset($_POST['close-project-button'])) {
        process_close_project_form();
    } else if (isset($_POST['reopen-project-button'])) {
        process_reopen_project_form();
    }
}

function process_project_form() {
    global $project;
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $project_name = mysqli_real_escape_string($connection, $_POST['project-name']);
    $project_discussion = mysqli_real_escape_string($connection, $_POST['project-discussion']);
    
    $query = "UPDATE `project_table` SET
        `project_name` = '$project_name' , 
        `project_discussion` = '$project_discussion' 
        WHERE `project_id` = '$project_id'";

    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }

    header("Location:project.php?id=${_POST['project-id']}");
}

function process_apply_list_options() {
    global $show_closed_tasks;
    global $past_timeboxes_date;

    if (isset($_POST['closed-tasks-option'])) {
        $show_closed_tasks = "checked";
    }
    if ($_POST['past-timeboxes-date']) {
        $past_timeboxes_date = $_POST['past-timeboxes-date'];
    }
}

function process_add_task_form() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $task_summary = mysqli_real_escape_string($connection, $_POST['task-summary']);
    
    $task_query = "INSERT INTO `task_table` (
        `task_summary` , `project_id` , `task_created_date` 
        ) VALUES ( 
        '$task_summary' , '$project_id' , CURRENT_TIMESTAMP() )";

    $task_results = mysqli_query($connection, $task_query);
    if (! $task_results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
    $new_task_id = mysqli_insert_id($connection);
    
    header("Location:task.php?id=$new_task_id");
}

function process_add_timebox_form() {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $timebox_name = mysqli_real_escape_string($connection, $_POST['timebox-name']);
    $timebox_end_date = mysqli_real_escape_string($connection, $_POST['timebox-end-date']);
    
    $query = "INSERT INTO `timebox_table` (
        `timebox_name` , `project_id` , `timebox_end_date` 
        ) VALUES ( 
        '$timebox_name' , '$project_id' , '$timebox_end_date')";

    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
    $new_timebox_id = mysqli_insert_id($connection);
    
    header("Location:timebox.php?id=$new_timebox_id");
}

function process_new_project_form() {
    global $session_user_id;
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $project_name = mysqli_real_escape_string($connection, $_POST['project-name']);
    
    $project_query = "INSERT INTO `project_table` ( `project_name` ) VALUES ( '$project_name' )";
    $project_results = mysqli_query($connection, $project_query);
    if (! $project_results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
    $new_project_id = mysqli_insert_id($connection);
    
    $access_query = "INSERT INTO `access_table` ( 
        `project_id` , `user_id` 
        ) VALUES (
        '$new_project_id' , '$session_user_id' )";
    $access_results = mysqli_query($connection, $access_query);
    if (! $access_results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
    
    header("Location:project.php?id=$new_project_id");
}

function process_close_project_form() {
    update_project_status('closed');
}

function process_reopen_project_form() {
    update_project_status('open');
}

function update_project_status($status) {
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $project_id = mysqli_real_escape_string($connection, $_POST['project-id']);
    $query = "UPDATE `project_table` 
        SET `project_status` = '$status' 
        WHERE `project_id` = '$project_id'";
    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return null;
    }    
    
    header("Location:project.php?id=$project_id");
}

function prepare_page_data() {
    global $project_id, $project;
    $connection = connect_to_database_session();
    if (!$connection) {
        return null;
    }

    $session_id = get_session_id();
    $user_id = get_session_user_id();
    $sql_project_id = mysqli_real_escape_string($connection, $project_id);
    $project_query = "SELECT P.* , 
            O.`user_id` AS `owner_id` , O.`login_name` AS `owner_name` 
        FROM `access_table` AS A 
        INNER JOIN `project_table` AS P ON A.`project_id` = P.`project_id` 
        INNER JOIN `user_table` AS O ON P.`project_owner` = O.`user_id`
        WHERE P.`project_id` = '$sql_project_id' AND A.`user_id` = $user_id";
    
    $project_result = mysqli_query($connection, $project_query);
    if (! $project_result) {
        set_user_message("Project ID $sql_project_id not recognized", 'warning');
        return null;
    }
    
    $project = mysqli_fetch_array($project_result);
    if ($project['project_status'] == 'closed') {
        set_user_message('This project has been closed', 'warning');
    } else {
        $project['can-close'] = TRUE;
    }
    $project_id = $project['project_id'];
    
    global $show_closed_tasks;
    $task_query = "SELECT T.`task_id` , T.`task_summary` , `task_status` ,  `timebox_id` 
        FROM `task_table` AS T
        WHERE T.`project_id` = '$sql_project_id' AND T.`parent_task_id` IS NULL";
    if (! $show_closed_tasks) {
        $task_query .= "
            AND T.`task_status` <> 'closed' ";
    }
    $task_query .= "
        ORDER BY T.`task_id`";
    
    $task_result = mysqli_query($connection, $task_query);
    if (mysqli_num_rows($task_result) > 0) {
        $task_list = array();
        while ($task = mysqli_fetch_array($task_result)) {
            $task_list[$task['task_id']] = $task;
            if ($task['task_status'] != 'closed') {
                $project['can-close'] = FALSE;
            }
        }
        $project['task_list'] = $task_list;
    }
    
    global $past_timeboxes_date;
    $timebox_query = "SELECT X.* FROM `timebox_table` AS X
        WHERE X.`project_id` = '$sql_project_id'";
    if ($past_timeboxes_date) {
        $timebox_query .= "
            AND X.`timebox_end_date` >= '$past_timeboxes_date'";
    } else {
        $timebox_query .= "
            AND X.`timebox_end_date` >= NOW()";
    }
    $timebox_query .= "
        ORDER BY X.`timebox_end_date`";
    
    $timebox_result = mysqli_query($connection, $timebox_query);
    if (mysqli_num_rows($timebox_result) > 0) {
        $timebox_list = array();
        while ($timebox = mysqli_fetch_array($timebox_result)) {
            $timebox_list[$timebox['timebox_id']] = $timebox;
        }
        $project['timebox_list'] = $timebox_list;
    }
    
    $user_query = "SELECT U.`user_id` , U.`login_name` 
        FROM `access_table` AS A
        INNER JOIN `user_table` AS U ON A.`user_id` = U.`user_id`
        WHERE A.`project_id` = '$sql_project_id'
        ORDER BY U.`login_name`";
    $user_result = mysqli_query($connection, $user_query);
    if (mysqli_num_rows($user_result) > 0) {
        $user_list = array();
        while ($user = mysqli_fetch_array($user_result)) {
            $user_list[$user['user_id']] = $user['login_name'];
        }
        $project['user_list'] = $user_list;
    }
    
    return $project;
}

function show_sidebar() {
    global $project_id, $project;

    if (! $project) {
        return;
    }

    if ($project['project_status'] == 'closed') {
        echo "
        <div class='sidebar-block'>
            <form id='close-project-form' method='post'>
                <div class='group'>
                    <input type='hidden' name='project-id' value='$project_id'>
                </div>
                <input type='submit' name='reopen-project-button' value='Reopen this project'></input>
            </form>
        </div>";
    } else {
        echo "
        <div class='sidebar-block'>
            <form id='add-task-form' method='post'>
                <input type='hidden' name='project-id' value='$project_id'>
                <div id='task-summary'>
                    <label for='task-summary'>Task Summary:</label>
                    <input style='width:100%' type='text' name='task-summary'></input>
                </div>
                <input type='submit' name='add-task-button' value='Add task'></input>
            </form>
        </div>";
        echo "
        <div class='sidebar-block'>
            <form id='add-timebox-form' method='post'>
                <input type='hidden' name='project-id' value='$project_id'>
                <div id='timebox-name'>
                    <label for='timebox-name'>Timebox name:</label>
                    <input style='width:100%' type='text' name='timebox-name'></input>
                </div>
                <div id='timebox-end-date'>
                    <label for='timebox-end-date'>Timebox end date:</label>
                    <input style='width:100%' type='text' name='timebox-end-date'></input>
                </div>
                <input type='submit' name='add-timebox-button' value='Add timebox'></input>
            </form>
        </div>";
        echo "
        <div class='sidebar-block'>
            <form id='new-project-form' method='post'>
                <input type='hidden' name='project-id' value='$project_id'>
                <div id='project-name'>
                    <label for='project-name'>Project name:</label>
                    <input style='width:100%' type='text' name='project-name'></input>
                </div>
                <input type='submit' name='new-project-button' value='Create new project'></input>
            </form>
        </div>";
        if ($project['can-close']) {
            echo "
        <div class='sidebar-block'>
            <form id='close-project-form' method='post'>
                <input type='hidden' name='project-id' value='$project_id'>
                <input type='submit' name='close-project-button' value='Close this project'></input>
            </form>
        </div>";
        }
    }
}

function show_content() 
{
    global $show_closed_tasks;
    global $past_timeboxes_date;
    global $project_id, $project;
    
    if (! $project) {
        show_user_message("There was an error retrieving the project information", 'warning');
        return;
    }
    
    echo "
        <h3>Project $project_id</h3>
        <form id='project-form' class='main-form' method='post'>
            <input type='hidden' name='project-id' value='$project_id'>
                
            <div id='project-name'>
                <label for='project-name'>Name:</label>
                <input style='width:50%' type='text' name='project-name' value='${project['project_name']}'></input>
            </div>
            
            <div id='project-discussion'>
                <label for='project-discussion' style='vertical-align:top'>Discussion:</label>
                <textarea name='project-discussion' rows='10' style='width:50%'>${project['project_discussion']}</textarea>
            </div>
                
            <div id='project-owner'>
                <label>Owner:</label>
                <a class='object-ref' href='user.php?id=${project['owner_id']}'>${project['owner_name']}</a>
            </div>

            <div id='project-created-date'>
                <label>Created:</label>
                ${project['project_created_date']}
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='update-button' value='Update'></input>
            </div> <!-- /form-controls -->
        </form>
            ";

    if (array_key_exists('task_list', $project)) {
        echo "
            <div id='tasks-header'>
                <div class='header-controls' style='float:right'>
                    <form method='GET'>
                        <input type='hidden' name='id' value='$project_id' />";
        if ($past_timeboxes_date) {
            echo"
                        <input type='hidden' name='X' value='$past_timeboxes_date' />";
        }
        echo "
                        <input type='checkbox' name='T' value='YES' $show_closed_tasks /><label>Show closed tasks</label>
                        <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                    </form>
                </div>
                <h4>Tasks</h4>
            </div>
            <div class='task-list'>";
        foreach ($project['task_list'] as $task_id => $task) {
            echo "
                <div id='task-$task_id' class='task'>
                    <div class='task-info task-${task['task_status']}'>
                        <div class='task-details'>";
            if ($task['task_status'] != 'open') {
                echo "
                            ${task['task_status']}";
            }
            echo "
                        </div> <!-- /task-details -->
                        <div class='task-id'>$task_id</div>
                        <div class='task-summary'>
                            <a class='object-ref' href='task.php?id=$task_id'>${task['task_summary']}</a>
                        </div> <!-- /task-summary -->";
            echo "
                    </div> <!-- /task-info -->
                </div> <!-- /task-$task_id -->";
        }
        echo "
            </div> <!-- /task-list -->";
    }

    if (array_key_exists('timebox_list', $project)) {
        echo "
            <div id='tasks-header'>
                <div class='header-controls' style='float:right'>
                    <form method='GET'>
                        <input type='hidden' name='id' value='$project_id' />";
        if ($show_closed_tasks) {
            echo "
                        <input type='hidden' name='T' value='YES' />";
        }
        echo "
                        <label for='X'>Show since</label>
                        <input type='text' size='12' style='font-size:small' name='X' value='$past_timeboxes_date' />
                        <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
                    </form>
                </div>
                <h4>Timeboxes</h4>
            </div>
            <div class='timebox-list'>";
        foreach ($project['timebox_list'] as $timebox_id => $timebox) {
            echo "
                <div id='timebox-$timebox_id' class='timebox'>
                    <div class='timebox-details'>${timebox['timebox_end_date']}</div>
                    <div class='timebox-info'>
                        <div class='timebox-id'>$timebox_id</div>
                        <div class='timebox-name'>
                            <a class='object-ref' href='timebox.php?id=$timebox_id'>${timebox['timebox_name']}</a>
                        </div> <!-- /timebox-name -->
                    </div> <!-- /timebox-info -->
                </div> <!-- /timebox-$timebox_id -->";
        }
        echo "
            </div> <!-- /timebox-list -->";
    }
    
    echo "
        <h4>People</h4>
        <div class='user-list'>";
    foreach ($project['user_list'] as $user_id => $user_name) {
        echo "
            <div id='user-$user_id' class='user'>
                <div class='user-info'>
                    <div class='user-id'>$user_id</div>
                    <div class='user-name'>
                        <a class='object-ref' href='user.php?id=$user_id'>$user_name</a>
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
