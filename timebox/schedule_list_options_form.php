<?php

/**
 * 
 * @param string $show_closed_tasks either 'checked' or ''
 * @param string $past_timeboxes_date
 */
function show_schedule_list_options_form($show_closed_tasks, $past_timeboxes_date)
{
    $xt_checked = $show_closed_tasks ? 'checked' : '';
    
    echo "
        <form id='list-options-form' method='GET'>
            <div id='list-options'>
                <div class='group'>
                    <input type='checkbox' name='tx' $xt_checked /> Show closed tasks
                </div>
                <div class='group'>
                    <label for='xx'>Show timeboxes after:</label></br>
                    <input style='width:100%' type='text' name='xx' value='$past_timeboxes_date' />
                </div> <!-- /group -->
            </div>
            <input type='submit' value='Show timeboxes'></input>
        </form>";
}

?>
