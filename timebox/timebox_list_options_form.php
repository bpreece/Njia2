<?php

function show_timebox_options_form($timebox_id, $show_closed_tasks)
{
    $tx_checked = $show_closed_tasks ? 'checked' : '';

    echo "
        <form method='GET'>
            <input type='hidden' name='id' value='$timebox_id' />
            <input type='checkbox' name='tx' $tx_checked /><label>Show closed tasks</label>
            <input type='submit' style='font-size:85%' value='&#x2713;' title='Apply options' />
        </form>";
}

?>
