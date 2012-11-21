<?php

function show_task_form($task_id, $task)
{
    echo "
        <form id='task-form' class='main-form' method='post'>
            <input type='hidden' name='task-id' value='$task_id'>
            <input type='hidden' name='project-id' value='${task['project_id']}'>                
            
            <div id='project_name'>
                <label>Project:</label>
                <a class='object-ref' href='project.php?id=${task['project_id']}'>${task['project_name']}</a>
            </div>";
            
    if (isset($task['parent_task_summary'])) {
        echo "
            <div id='parent-task'>
                <label>Parent task:</label>
                <a class='object-ref' href='task.php?id=${task['parent_task_id']}'>${task['parent_task_summary']}</a>
            </div>";
    }

    echo "
            <div id='task-summary'>
                <label for='task-summary'>Summary:</label>
                <input style='width:50%' type='text' name='task-summary' value='${task['task_summary']}'></input>
            </div>
            
            <div id='task-discussion'>
                <label for='task-discussion'>Discussion:</label>
                <textarea name='task-discussion' rows='10' style='width:50%'>${task['task_discussion']}</textarea>
            </div>";

    if (! array_key_exists('subtask_list', $task)) {
        if (array_key_exists('users_list', $task)) {
            echo "
            <div id='task-user'>
                <label for='task-user'>Assigned to:</label>
                <select name='task-user'>
                    <option value=''></option>";
            foreach ($task['users_list'] as $user) {
                $selected = ($task['user_id'] == $user['user_id']) ? "selected='selected'" : "";
                echo "
                    <option value='${user['user_id']}' $selected>${user['login_name']}</option>";
            }
            echo "
                </select>
            </div>";
        }

        if (array_key_exists('timebox_list', $task)) {
            echo "
            <div id='task-timebox'>
                <label for='task-timebox'>Timebox:</label>
                <select name='task-timebox'>
                    <option value=''></option>";
            foreach ($task['timebox_list'] as $timebox) {
                $selected = ($task['timebox_id'] == $timebox['timebox_id']) ? "selected='selected'" : "";
                echo "
                    <option value='${timebox['timebox_id']}' $selected>
                        ${timebox['timebox_name']} (${timebox['timebox_end_date']})
                    </option>";
            }
            echo "
                </select>
            </div>";
        }
    }
            
    echo "
            <div id='task-created-date'>
                <label>Created:</label>
                ${task['task_created_date']}
            </div>
            
            <div id='task-modified-date'>
                <label>Modified:</label>
                ${task['task_modified_date']}
            </div>
                
            <div id='form-controls'>
                <input type='submit' name='task-form-button' value='Update'></input>
            </div> <!-- /form-controls -->
        </form>";
}

function process_task_form()
{
    if (! isset($_POST['task-form-button'])) {
        return FALSE;
    }
    
    $connection = connect_to_database_session();
    if (!$connection) {
        return TRUE;
    }

    $task_id = mysqli_real_escape_string($connection, $_POST['task-id']);
    $task_summary = mysqli_real_escape_string($connection, $_POST['task-summary']);
    $task_discussion = mysqli_real_escape_string($connection, $_POST['task-discussion']);

    $query = "UPDATE `task_table` SET ";
    if (isset($_POST['task-user'])) {
        $user = mysqli_real_escape_string($connection, $_POST['task-user']);
        $query .= "`user_id`='$user', ";
    }
    if (isset($_POST['task-timebox'])) {
        $timebox = mysqli_real_escape_string($connection, $_POST['task-timebox']);
        $query .= "`timebox_id`='$timebox', ";
    }
    $query .= "`task_discussion`='$task_discussion', 
        `task_summary`='$task_summary' ,
        `task_modified_date` = CURRENT_TIMESTAMP() 
        WHERE `task_id` = '$task_id'";

    $results = mysqli_query($connection, $query);
    if (! $results) {
        set_user_message(mysqli_error($connection), "warning");
        return TRUE;
    }

    set_user_message("The changes have been applied", 'success');
    return TRUE;
}

?>
