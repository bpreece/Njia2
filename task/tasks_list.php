<?php

function show_tasks_list($tasks_list) {
    foreach ($tasks_list as $task_id => $task) {
        $task_header_css = "task-header object-header object-${task['task_status']}";
        if ( (! isset($task['subtask-list']) || count($task['subtask-list']) == 0 ) && $task['task_status'] != 'closed') { 
            if (! $task['timebox_id']) {
                $task_header_css .= " object-unscheduled";
            } else {
                $task_header_css .= " object-scheduled";
            }
        }
        echo "
        <div id='task-$task_id' class='task'>
            <div class='task-header $task_header_css'>
                <div class='task-details'>";
        if ($task['task_status'] == 'closed') {
            echo "
                    <div class='task-status'>
                        ${task['task_status']}
                    </div>";
        } else {
            echo "
                    <div class='task-user'>
                        <a class='object-ref' href='user.php?id=${task['user_id']}'>${task['user_name']}</a>
                    </div>
                    <div class='task-timebox'>
                        <a class='object-ref' href='timebox.php?id=${task['timebox_id']}'>${task['timebox_end_date']}</a>
                    </div>";
        }
        echo "
                </div> <!-- /task-details -->
                <div class='task-id'>$task_id</div>
                <div class='task-summary'>
                    <a class='object-ref' href='task.php?id=$task_id'>${task['task_summary']}</a>
                </div>
            </div> <!-- /task-info -->";
        if (isset($task['subtask-list']) && count($task['subtask-list']) > 0) {
            echo "
            <div class='task-list'>";
            show_tasks_list($task['subtask-list']);
            echo "
            </div>";
        }
        echo "
        </div> <!-- /task-$task_id -->";
    }
}

?>
