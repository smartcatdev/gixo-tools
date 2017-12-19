<?php
/**
 * Plugin name: Gixo Tools
 * Author: Smartcat
 * 
 * 
 * 
 */

namespace gixo;

if( !defined( 'ABSPATH' ) ) {
    die();
}

include_once dirname( __FILE__ ) . '/includes/trait-Singleton.php';
include_once dirname( __FILE__ ) . '/includes/functions-api-sync.php';


register_activation_hook(__FILE__, 'gixo\activate' );
register_deactivation_hook( __FILE__, 'gixo\deactivate' );


function activate() {
    
    wp_schedule_event( time(), 'hourly', 'gixo_get_sessions' );
    
}

function deactivate() {
    
    wp_clear_scheduled_hook( 'gixo_get_sessions' );
    
}
