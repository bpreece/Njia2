<?php

// Define the function get_all_stylesheets().  This may be defined by the page,
// but typically, the page will instead define the function get_stylesheets(),
// which is called by get_all_stylesheets(), and returns a list of page-specific
// stylesheets that are added to the list of default stylesheets.
global $default_stylesheets;
$default_stylesheets = array('layout.css', 'style.css');
if (! function_exists('get_all_stylesheets')) {
    function get_all_stylesheets() {
        global $page, $default_stylesheets;
        $stylesheets = $default_stylesheets;
        if (function_exists('get_stylesheets')) {
            $stylesheets = array_merge($stylesheets, get_stylesheets());
        } else if (is_array($page) && isset($page['styles'])) {
            $stylesheets = array_merge($stylesheets, $page['styles']);
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
        global $page, $default_scripts;
        $scripts = $default_scripts;
        if (function_exists('get_scripts')) {
            $scripts = array_merge($scripts, get_scripts());
        } else if (is_array($page) && isset($page['scripts'])) {
            $scripts = array_merge($scripts, $page['scripts']);
        }
        return $scripts;
    }
}

if (! function_exists('show_sidebar')) {
    function show_sidebar() {
        global $page;
        echo "<div id='sidebar'>";
        if (is_array($page) && isset($page['sidebar-views'])) {
            foreach ($page['sidebar-views'] as $sidebar) {
                echo "<div class='sidebar-block'>";
                include $sidebar;
                echo "</div>";
            }
        } else if (is_array($page) && isset($page['sidebar-blocks'])) {
            foreach ($page['sidebar-blocks'] as $sidebar) {
                echo "<div class='sidebar-block'>$sidebar</div>";
            }
        }
        echo "</div><!-- sidebar -->";
    }
}

if (! function_exists('show_content')) {
    function show_content() {
        global $page;
        if (is_array($page) && isset($page['view'])) {
            include $page['view'];
        } else if (is_array($page) && isset($page['content'])) {
            echo $page['content'];
        } else {
            echo "<div style='color:red;font-weight:bold'>TODO: Define the content for this page.</div>";
        }
    }
}

// Call basic page-processing functions.  These are called before any HTML
// output, so that the functions can, for example, redirect to another page
// if necessary.

// Call the page's process_query_string() function, if it's defined.  This
// function handles the query strings in the $_GET paramemters.  Typically, it
// uses these parameters to load information for the page, or redirect to
// another page.
if (function_exists('process_query_string')) {
    process_query_string();
}

// Call the page's process_form_data() function, if it's defined.  This function
// handles form input in the $_POST parameters.  Typically, this will validate
// the values, sanitize it, and write it to the database.  It will probably also
// use the parameters to load information for the new page, or redirect to
// another page.
if (function_exists('process_form_data')) {
    process_form_data();
}

// Call the page's prepare_page_data() function, if it's defined.  This function
// typically retrieves data from the database, or calculates or otherwise 
// prepares information to be used by get_page_id(), get_page_class(), 
// show_sidebar(), show_content(), or show_footer().
if (function_exists('prepare_page_data')) {
    prepare_page_data();
}

$page_id = '';
if (function_exists('get_page_id')) {
    $page_id = get_page_id();
} else if (is_array($page) && isset($page['page-id'])) {
    $page_id = $page['page-id'];
}

$page_class = '';
if (function_exists('get_page_class')) {
    $page_class = get_page_class();
} else if (is_array($page) && isset($page['page-class'])) {
    $page_class = $page['page-class'];
}

$session_user = get_session_user();
    
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
    
    <body <?php echo "id='$page_id'"; ?> <?php echo "class='$page_class'"; ?> >
        <div id="logo-and-title">
            <div id="logo">
                <a href='index.php'><img src="<?php echo get_logo_image(); ?>" /></a>
            </div>
            <!--
            <div id="title"><?php echo get_global_title(); ?></div>
            -->
        </div> <!-- /logo-and-title -->
        <div id="page">
            <div id="header">
                <ul class='menu'>
                    <?php if ($page_class == 'todo-page') : ?>
                        <li class='selected'>TO-DO</li>
                    <?php else: ?>
                        <li><a href='todo.php'>TO-DO</a></li>
                    <?php endif; ?>
                    <?php if ($page_class == 'projects-page') : ?>
                        <li class='selected'>PROJECTS</li>
                    <?php else: ?>
                        <li><a href='projects.php'>PROJECTS</a></li>
                    <?php endif; ?>
                    <?php if ($page_class == 'schedules-page') : ?>
                        <li class='selected'>SCHEDULES</li>
                    <?php else: ?>
                        <li><a href='schedules.php'>SCHEDULES</a></li>
                    <?php endif; ?>
                    <?php if(is_admin_session()) : ?>
                        <?php if ($page_class == 'users-page') : ?>
                            <li class='selected'>USERS</li>
                        <?php else: ?>
                            <li><a href='users.php'>USERS</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($page_class == 'log-page') : ?>
                        <li class='selected'>LOG</li>
                    <?php else: ?>
                        <li><a href='log.php'>LOG</a></li>
                    <?php endif; ?>
                        <li><a style='font-variant: small-caps; font-size: 85%' href='logout.php'>Log out</a></li>
                </ul> <!-- /menu -->
            </div> <!-- /header -->
            
            <div id="content">
                <?php show_sidebar(); ?>            
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
