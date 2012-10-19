<?php

// Define the function get_all_stylesheets().  This may be defined by the page,
// but typically, the page will instead define the function get_stylesheets(),
// which is called by get_all_stylesheets(), and returns a list of page-specific
// stylesheets that are added to the list of default stylesheets.
global $default_stylesheets;
$default_stylesheets = array('layout.css', 'style.css');
if (! function_exists('get_all_stylesheets')) {
    function get_all_stylesheets() {
        global $default_stylesheets;
        $stylesheets = $default_stylesheets;
        if (function_exists('get_stylesheets')) {
            $page_stylesheets = get_stylesheets();
            $stylesheets = array_merge($stylesheets, $page_stylesheets);
        }
        return $stylesheets;
    }
}

// Define the function get_all_scripts().  This may be defined by the page,
// but typically, the page will instead define the function get_scripts(),
// which is called by get_all_scripts(), and returns a list of page-specific
// scripts that are added to the list of default scripts.
global $default_scripts;
$default_scripts = array();
if (! function_exists('get_all_scripts')) {
    function get_all_scripts() {
        global $default_scripts;
        $scripts = $default_scripts;
        if (function_exists('get_scripts')) {
            $page_scripts = get_scripts();
            $scripts = array_merge($scripts, $page_scripts);
        }
        return $scripts;
    }
}

// Call basic page-processing functions.  These are called before any HTML
// output, so that the functions can, for example, redirect to another page
// if necessary.

// Call the page's process_form_data() function, if it's defined.  This function
// handles form input in the $_POST parameters.  Typically, this will validate
// the values, sanitize it, and write it to the database.  It will probably also
// use the parameters to load information for the new page, or redirect to
// another page.
if (function_exists('process_form_data')) {
    process_form_data();
}

// Call the page's process_query_string() function, if it's defined.  This
// function handles the query strings in the $_GET paramemters.  Typically, it
// uses these parameters to load information for the page, or redirect to
// another page.
if (function_exists('process_query_string')) {
    process_query_string();
}

$page_id = '';
if (function_exists('get_page_id')) {
    $page_id = "id='" . get_page_id() . "'";
}

$page_class = '';
if (function_exists('get_page_class')) {
    $page_class = "class='" . get_page_class() . "'";
}

$user = get_session_user();
    
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        
        <?php $stylesheets = get_all_stylesheets(); foreach($stylesheets as $stylesheet): ?>
            <link rel='stylesheet' href='<?php echo $stylesheet ?>' type='text/css' media='all' />
        <?php endforeach; ?>
            
        <?php $scripts = get_all_scripts(); foreach($scripts as $script): ?>
            <script type='text/javascript' src='<?php echo $script ?>'></script>
        <?php endforeach; ?>
    
        <title><?php echo get_global_title(); ?></title>
    </head>
    
    <body <?php echo $page_id?> <?php echo $page_class ?> >
        <div id="page">
            <div id="header">
                <div id="user-name">
                    <a class='object-ref' href='user.php?id=<?php echo $user['user_id']; ?>'><?php echo $user['login_name']; ?></a>
                    <a href='logout.php'>Log out</a>
                </div>
                <div id="logo-and-title">
                    <div id="logo"><img src="<?php get_logo_image(); ?>" /></div>
                    <div id="title"><?php echo get_global_title(); ?></div>
                </div> <!-- /logo-and-title -->
                
                <?php if (function_exists('show_main_menu')): ?>
                <div id="main-menu">
                    <ul class='main-menu'>
                        <?php if ($page_id == 'todo-page') : ?>
                            <li class='selected'>To-do</li>
                        <?php else: ?>
                            <li><a href='todo.php'>To-do</a></li>
                        <?php endif; ?>
                        <?php if ($page_id == 'projects-page') : ?>
                            <li class='selected'>Projects</li>
                        <?php else: ?>
                            <li><a href='projects.php'>Projects</a></li>
                        <?php endif; ?>
                        <?php if ($page_id == 'timeboxes-page') : ?>
                            <li class='selected'>Schedules</li>
                        <?php else: ?>
                            <li><a href='schedules.php'>Schedules</a></li>
                        <?php endif; ?>
                    </ul>
                </div> <!-- /main-menu -->
                <?php endif; ?>
            </div> <!-- /header -->
            
            <div id="content">
                <?php if (function_exists('show_sidebar')): ?>
                <div id="sidebar">
                    <?php show_sidebar(); ?>
                </div> <!-- /sidebar -->
                <?php endif; ?>
            
                <div id="main">
                    <?php show_user_messages(); ?>
                    <?php show_content(); ?>
                </div> <!-- /main -->
            </div> <!-- /content -->
            
            <div id="footer">
                <?php show_footer(); ?>
            </div> <!-- /footer -->
        </div> <!-- /page -->
    </body>
</html>
