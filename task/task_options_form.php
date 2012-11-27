<?php

function show_task_options_form($task_id, $show_closed_subtasks)
{
    $sx_checked = $show_closed_subtasks ? 'checked' : '';

    echo "
        <form method='GET'>
            <input type='hidden' name='id' value='$task_id' />
            <input type='checkbox' name='sx' $sx_checked /><label>Show closed subtasks</label>
            <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
        </form>";
}

?>
