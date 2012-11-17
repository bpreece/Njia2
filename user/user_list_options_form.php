<?php

/**
 * 
 * @param boolean $show_closed_accounts
 */
function show_user_list_options_form($show_closed_accounts)
{
    $x_checked = $show_closed_accounts ? 'checked' : '';
    echo"
        <form id='list-options-form' method='GET'>
            <div id='list-options'>
                <div class='group'>
                    <input type='checkbox' name='xa' $x_checked /> Show closed accounts
                </div>
            </div>
            <input type='submit' value='Apply these options'></input>
        </form>";
}

?>
