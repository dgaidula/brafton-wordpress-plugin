<?php
/**
 * 
 * Plugin error handling and import failure debugging methods. 
 * Developer option Errors must be enabled to use these.
 * @author Ali <techsupport@brafton.com>
 * @package Brafton Importer
 * 
 */
/**
 * Use of this method assumes the existance of a log object stored in a wp options field 
 * Initialize a new log object with brafton_initialize_log()
 * 
 * Adds a new report object to a pre-existing log object serialized in wp database. 
 * 
 * @param Array $report 
 */
function brafton_log( $report ) {
    //Store all reports in brafton_error_log by default
    $brafton_default_report = array(
                        'option' => 'brafton_error_log', //option name errors are stored in wp
                        'notice' => '',  //admin notice message
                        'priority' => 0, //0 - log only when brafton errors enabled; 1- log regardless if errors are enabled
                        'message' => '',  //log entry message.
                    );
    // Parse and merge given $report with $defaults
    $report = wp_parse_args($report, $brafton_default_report);
	//retrieve log from wp options table.
	$log =  get_option( 'brafton_error_log', $report );
    $brafton_options = Brafton_Options::get_instance();
	// $report expected to be an array or an object. 
	if ( is_array( $report ) || is_object( $report ) ) {
        switch( $report['priority'] ){
            // store messages indefinately or until log limit is reached if brafton error reporting is enabled
            case 0: 
                //if brafton error reporting is enabled or priority is 1
                if ( $brafton_options->options['brafton_enable_errors'] == 'on' )
                    add_brafton_log_entry( $log, $report );
                break;
            //store messages indefinately or until log option limit is reached regardless of brafton error reporting.
            case 1: 
                add_brafton_log_entry( $log, $report );
                break;
        }
    }    
	else 
        //Blame web server. Send the error report to appropriate error handling routine defined by web server.
    	error_log( $report ); 
}
/**
 * Not intended to be used directly. Exists to avoid repetitive code and 
 * excessive nested if statements in brafton_log function.
 * @param Array $log
 * @param Array $report 
 */
function add_brafton_log_entry($log, $report) {
    $report['message'] = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) ) . " - " . $report['message'] . "\n";
    if( $log['limit'] == NULL || $log['limit'] >= $log['count'] )
    {
        //push new message to front of old log array.
        array_unshift( $log['entries'], $report );
        $log['count']++;
        //update the log option in wp database  
        update_option( $report['option'], $log ); 
    }
     //log report capacity has been reached
    else{
        //overwrite old previous log entries with empty array
        $log['entries'] = array();
        //reset counter
        $log['count'] = 0;
        //push new message to front of old log array.
        array_unshift( $log['entries'], $report );
        $log['count']++;
        //update the log option in wp database  
        update_option( $report['option'], $log ); 
    }
}
/**
 * Initializes a new log object. Can also be used to overwrite an existing log. 
 * 
 * @param String $option
 * @param Array $log
 */
function brafton_initialize_log($option, $log = NULL ){
    //retrieve old log if one exists.
    $log = get_option( $option , $log );
    $brafton_default_log = array(
                            'count' => 0, //number of reports stored. Empty initially.
                            'limit' => 2000, //ingeger -limit log entries capacity
                            'priority' => 0, //0 - log entries only when brafton errors enabled; 1 -log entries always
                            'entries' => array() //array of report objects
                        );
    //parse $log and merge into default log array.
    $log = wp_parse_args( $log, $brafton_default_log );
    //initialize log field. 
    $option_value =  update_option( $option, $log );
}
/**
 * Display notice in admin area. 
 */
 add_action( 'admin_notices', 'brafton_admin_notice' );
 /**
  * Displays admin notices
  */
function brafton_admin_notice( $messages ) {
    global $current_user;
    $user_id = $current_user->ID;
    $brafton_options = Brafton_Options::get_instance();
    $product = $brafton_options->brafton_get_product();
    $notices = array();
    $feed_url = $brafton_options->get_feed_url();
   
    //We need DOMDocument to parse XML feed.
    if ( !class_exists( 'DOMDocument' ) )
       $notices[] =  array( 
                        'message' => sprintf( "%s plugin requires <strong>DOM XML</strong> to import articles. Please ensure DOM XML is installed and enabled on your server.", $product ), 
                        'class' => 'error', 
                        'ignore' => true 
                    );
    //Video importer is disabled.
    if( $brafton_options->options['brafton_video_secret'] === ""  && $brafton_options->options['brafton_enable_video'] === "off" )
        $notices[] = array(
                    'message' => sprintf( "%s video importing is disabled.", $product ), 
                    'class' => 'error', 
                    'ignore' => true
                );   
    //Overwrite is enabled.
    if( $brafton_options->options['brafton_overwrite'] === "on" && ! $brafton_options->options['brafton_api_key'] === "" )
        $notices[] = array(
                    'message' => sprintf( 'Overwrite is enabled. Articles still on your <a href="%s">%s feed</a> will be updated to reflect feed content.', $feed_url, $product ), 
                    'class' => 'update-nag', 
                    'ignore' => true
                );
    //Brafton settings page notice
    if( isset( $_GET['page'] ) && $_GET['page'] == 'WP_Brafton_Article_Importer' ) { 
        $next_scheduled_import = $brafton_options->next_scheduled_import();
        if( $brafton_options->options['brafton_import_articles'] == "on" || $brafton_options->options['brafton_enable_video'] === "on" )
            $notices[] = array(
                        'message' => $next_scheduled_import['message'], 
                        'class' => $next_scheduled_import['class'], 
                        'ignore' => true
                    );
        //Error Logging is enabled.
        if( $brafton_options->options['brafton_enable_errors'] === "on" )
            $notices[] = array(
                        'message' => sprintf( "%s error reporting is enabled.", $product ), 
                        'class' => 'updated', 
                        'ignore' => true
                    );
       //Article importer is disabled.
        if( $brafton_options->options['brafton_import_articles'] === 'off' )
            $notices[] = array(
                        'message' => sprintf( '%s article importing is disabled. Please, enable article importing to automatically publish your %s content hourly.', $product,  $product ) , 
                        'class' => 'error', 
                        'ignore' => true
                    );
    }    
    //Brafton import page notices.
    if( isset( $_GET['page'] ) && $_GET['page'] == 'brafton_archives' ) { 
         //We need curl to upload via archives.
        if ( !function_exists( 'curl_init' ) )
            $notices[] = array( 
                            'message' => sprintf( "%s plugin's import feature requires <strong>cURL</strong> to import articles. Please ensure <b>cURL</b> is installed and enabled on your server.", $product ), 
                            'class' => 'error', 
                            'ignore' => true 
                        );
        //We need to raise limit when php safe mode is disabled. Article imports take that long!
        //if( ini_get('safe_mode') ){
            $php_execution_limit  = ini_get('max_execution_time'); 
            $notices[] = array( 
                            'message' => sprintf( "%s article imports can exceed your <strong>php execution limit</strong> %s seconds</strong>. Please disable PHP safe mode or raise your servers max execution time limit.", $product, $php_execution_limit ), 
                            'class' => 'error', 
                            'ignore' => false // currently can ignore only one message in notices array. Need to add new meta_keys for usermeta table.
                        );
        //}
        //Article importer is disabled.
        if( $brafton_options->options['brafton_import_articles'] === 'off' )
            $notices[] = array(
                        'message' => sprintf( '%s article importing is disabled. Enable article importing in <a href="%s">%s settings</a> before uploading an xml archive file.', $product,  menu_page_url( 'WP_Brafton_Article_Importer', false ), $product ) , 
                        'class' => 'error', 
                        'ignore' => true
                    );    
    }
   
    foreach( $notices as $n )
    {
        if( ! $n['ignore'] ) {
            if ( ! get_user_meta($user_id, 'brafton_ignore_notice') ) {
                echo sprintf( '<div id="brafton-error" class="%s"><p>%s', $n['class'], $n['message']);
                printf(__(  ' <a href="%1$s">Hide Notice</a>'), '?brafton_nag_ignore=0');
                echo "</p></div>";
            //echo printf(  '<div id="brafton_error" class="%s"><p>%s  | <a href="%1$s">Hide Notice</a> </p></div>', $n['class'], $n['message'], '?brafton_nag_ignore=0' );
            }
        }
        else 
            echo sprintf( '<div id="brafton_error" class="%s"><p>%s</p></div>', $n['class'], $n['message'] );
    }
}
add_action('admin_init', 'brafton_nag_ignore');
/**
 * Helper method for hiding PHP Safe Mode notification.
 */
function brafton_nag_ignore() {
    global $current_user;
    $user_id = $current_user->ID;
    /* If user clicks to ignore the notice, add that to their user meta */
    if ( isset($_GET['brafton_nag_ignore']) && '0' == $_GET['brafton_nag_ignore'] ) {
         add_user_meta($user_id, 'brafton_ignore_notice', 'true', true);
         //Redirect to previous page.
         header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
}
?>