<?php

function show_project_options_form($project_id, $focus, $show_closed_tasks, $show_subtasks, $timebox_end_date)
{
    $tx_checked = $show_closed_tasks ? 'checked' : '';
    $ts_checked = $show_subtasks ? 'checked' : '';

    echo "
        <form method='GET'>
            <input type='hidden' name='id' value='$project_id' />";
    
    if ($focus == 'tasks') {
        echo "
            <input type='checkbox' name='tx' $tx_checked /><label>Show closed tasks</label>
            <input type='checkbox' name='ts' $ts_checked /><label>Show subtasks</label>
            <input type='hidden' name='s' value='$timebox_end_date' />";
    } else if ($focus == 'timeboxes') {
        if ($show_closed_tasks) {
            echo "
                <input type='hidden' name='tx' value='on' />";
        }
        if ($show_subtasks) {
            echo "
                <input type='hidden' name='ts' value='on' />";
        }
        echo "
            <label for='s'>Show since</label>
            <input type='text' size='12' style='font-size:small' name='s' value='$timebox_end_date' />";
    }
    
    echo "
            <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
        </form>";
}

?>
