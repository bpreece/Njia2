<?php

include_once 'common.inc';

function get_stylesheets() {
    return array('index.css');
}

function get_page_id() {
    return 'front-page';
}

function get_page_class() {
    return 'no-header';
}

/*
function process_query_string() {
}
 */

/*
function process_form_data() {
}
 */

function show_sidebar() {
    echo "
        <div class='sidebar-block'>
            <form method='GET' action='login.php'>
                <input type='submit' value='Log in to Njia'></input>
            </form>
        </div>";
    echo "<div class='sidebar-block'>
            <ul class='menu'>
                <li><a href='index.php'>NJIA &beta;</a></li>
                <li><a href='overview.php'>READ ME</a></li>
                <li>QUICK START</li>
            </ul>
        </div>";
}

function show_content() {
    echo "
        <h1>Njia Quickstart Guide</h1>
        <div class='quickstart-step' style='clear:right'>
            <img src='image/qs-create-account.png' />
        </div>
        <div class='quickstart-step'>
            <img src='image/qs-open-a-project.png' />
        </div>
        <div class='quickstart-step'>
            <img src='image/qs-create-a-timebox.png' />
        </div>
        <div class='quickstart-step'>
            <img src='image/qs-return-to-project.png' />
        </div>
        <div class='quickstart-step'>
            <img src='image/qs-create-a-task.png' />
        </div>
        <div class='quickstart-step'>
            <img src='image/qs-assign-to-timebox.png' />
        </div>
        <div class='quickstart-step'>
            <img src='image/qs-check-your-todos.png' />
        </div>
        ";
}

include_once ('template.inc');

?>
