<?php

function show_timebox_list_options_form($show_closed_tasks)
{
    $tx_checked = $show_closed_tasks ? 'checked' : '';
    echo "
        <form id='list-options-form' method='GET'>
            <div id='list-options' class='group'>
                <input type='checkbox' name='tx' $tx_checked> Show closed tasks</br>
            </div>
            <input type='submit' value='Apply these options'></input>
        </form>";
}

?>
