<?php

/*
 * REQUIRED.  Include standard functions which are used by all pages.
 */
include_once('common.inc');

/**
 * OPTIONAL.  The page template automatically includes the default templates
 * layout.css and styles.css.  If this page requires additional stylesheets,
 * they should be returned in an array in the order that they should be 
 * processed by the browser
 * 
 * @return array
 */
function get_stylesheets() {
    return array("njia.css");
}

/**
 * OPTIONAL.  Use this function to return the value of an 'id' attribute
 * for the HTML 'body' tag.
 * 
 * @return string
 */
/*
function get_page_id() {
    return 'stub-page';
}
 */

/**
 * OPTIONAL.  Use this function to return the value of a 'class' attribute
 * for the HTML 'body' tag.
 * 
 * @return string
 */
/*
function get_page_class() {
    return 'stub';
}
 */

/**
 * OPTIONAL.  Use this function to process any $_GET parameters.  This 
 * function is called before process_form_data().
 */
/*
function process_query_string() {
    
}
 */

/**
 * OPTIONAL.  Use this function to process any $_POST parameters.  This 
 * function is called after process_query_string().
 */
/*
function process_form_data() {
    
}
 */

/**
 * OPTIONAL.
 */
/*
function prepare_page_data() {

}
 */

/**
 * OPTIONAL.
 */
/*
function show_sidebar() {
    
}
 */

/**
 * REQUIRED.
 */
function show_content() {
    
}

/*
 * REQUIRED.
 */
include_once ('template.inc');

?>
