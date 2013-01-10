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
                <li>READ ME</li>
                <li><a href='quickstart.php'>QUICK START</a></li>
                <li><a href='faq.php'>FAQ</a></li>
            </ul>
        </div>";
}

function show_content() {
    echo "
        <h1>Overview</h1>
        <h2>Timeboxes</h2>
        <p>
            Njia is based on the concept of a <em>timebox</em>.
        </p>
        <p>
            A timebox <em>is not</em> a deliverable, although it might be.  A 
            timebox <em>is not</em> a milestone, although it might be.  A 
            timebox is a <em>project review cycle</em>. 
        </p>
        <p>
            Central to agile development is the need for a regular project 
            review.  Has the team made progress? Is the work actualy addressing 
            the customer's needs? Have any obstacles come up which need to be 
            handled?  Is the team on target for completing the project on time? 
            Have customer expectations changed?  Have priorities changed?
            The timebox is the development cycle between successive project 
            reviews.
        </p>
        <p>
            Part of the project review is judging whether the team has made 
            acceptable progress, but there is no way to judge progress unless 
            the team knows what progress was expected. This means that every 
            timebox requires a clear list of tasks which are to be completed 
            by the project review.  And of course, incomplete tasks need to be 
            rescheduled into another timebox, forcing the team to revisit the 
            schedule.
        </p>
        <p>
            There is no hard rule for how long a timebox should last. Different 
            projects may have different requirements.  If your team is pursuing  
            an aggressive schedule, or if your team is very inexperienced, you  
            may choose to make them one or two weeks long. More typical projects 
            will probably schedule one timebox per month, more or less.  As 
            with everything else agile, timeboxes are adaptable: they can and 
            should be reworked as necessary to meet the needs of the project.
        </p>
        <h2>Project Scheduling</h2>
        <p>
            Njia is not a glorified to-do list.  It assumes that you have 
            already performed responsible project planning before setting up 
            your project in Njia.  This includes defining the tasks, estimating 
            the work effort, and scheduling tasks in appropriate timeboxes.
        </p>
        <p>
            Njia allows tasks to be divided into subtasks. 
            For example, you might have a top-level task called <em>User 
            Interface</em>.  This might have subtasks called <em>First 
            Wireframe</em>, <em>Committed Wireframe</em>, <em>Design Graphics</em>, 
            <em>Page Template</em>, <em>Welcome Page</em>, and so on.  And
            maybe <em>Design Graphics</em> has further subtasks called 
            <em>Color Palette</em>, <em>Main Logo</em>, and so forth.  Use this 
            scheme to organize your tasks however best suits your project.
        </p>
        <p>
            Only leaf tasks can be scheduled or assigned to team members.
            Parent tasks are considered open as long as they have any open 
            subtasks.
        </p>
        <p>
            It true agile fashion, it should be assumed that tasks and 
            schedules will evolve as the project evolves, user expectations 
            become clearer, priorities are re-adjusted.  New tasks will be 
            created, some tasks will be closed without work, other tasks will 
            be moved between timeboxes.  The main goal is always simply to be 
            responsive to the customer's needs.
            
        </p>
        <h2>Task Lifecycle</h2>
        <p>
            Njia does not use the typical concept of task lifecycles that other 
            project tracking system use. There is no concept of  <em>new</em>, 
            <em>in progress</em>, <em>blocked</em>, <em>completed</em>, 
            <em>canceled</em> or any other typical status.  Either a 
            task is closed, or it is not.  Either it is scheduled in a timebox, 
            or it is not.  Either it is assigned to a team member, or it is 
            not.  That's as complicate as it gets.
        </p>
            Njia does provide a free-form, unlimited size <em>discussion</em> 
            field for providing information about a task. Typically, this 
            includes a description of the task, any unobvious requirements, and 
            so on.  However, if you need to record that a task is blocked, or 
            depends on another task, or if you need to explain why a task was 
            suspended, or canceled, or rescheduled, or if the task has been 
            reassigned and you need to explain why, then this is a good place 
            to capture that information.
        <p>
        </p>
        <h2>Project Permissions</h2>
        <p>
            Njia assumes project team members are mature adults.
        </p>
        <p>
            There is no complicated system of user permissions, roles, or 
            access control. Any team member can open or close or re-open a 
            task. Any team member can assign or re-assign a task to himself or 
            any other team member. Any team member can reschedule a task or 
            change a timebox's end date.
        </p>
        <p>
            If you don't trust your team members, you might want to consider 
            joining a different team.
        </p>
        ";
}

include_once ('template.inc');

?>
