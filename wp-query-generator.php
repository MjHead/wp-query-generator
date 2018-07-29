<?php
/**
 * Plugin Name: WP Query Generator
 * Plugin URI:  
 * Description:
 * Version:     1.0.0
 * Author:      Zemez
 * Author URI:  
 * Text Domain: 
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die();
}

define( 'WPQG_VERSION', '1.0.0' );

define( 'WPQG__FILE__', __FILE__ );
define( 'WPQG_PATH', trailingslashit( plugin_dir_path( WPQG__FILE__ ) ) );
define( 'WPQG_URL', plugins_url( '/', WPQG__FILE__ ) );

require WPQG_PATH . 'includes/plugin.php';

add_action( 'after_setup_theme', array( 'WPQG_Plugin', 'get_instance' ) );
