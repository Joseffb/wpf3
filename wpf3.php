<?php

/*
    Plugin Name: Fat Free Framework for WordPress
    Plugin URI: http://github.com/joseffb/wpf3
    Description: The FatFreeFramework development library dependency for wordpress development. Other plugins may require this plugin.
    Version: 0.2
    Author: Joseff Betancourt
    Author URI: http://joseffb.com
    License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Access denied.' );
}

const WPF3_NAME = 'Fat Free Framework for WordPress';
const WPF3_REQUIRED_PHP_VERSION = '7.4';                          //
const WPF3_REQUIRED_WP_VERSION = '4.8';                          //
const WPF3_REQUIRED_F3_VERSION = '3.8.1';                          //
$f3 = base::instance();

function wpf3_requirements_met(): bool
{
    global $wp_version;
    global $f3;
    //require_once( ABSPATH . '/wp-admin/includes/plugin.php' );		// to get is_plugin_active() early

    if ( version_compare( PHP_VERSION, WPF3_REQUIRED_PHP_VERSION, '<' ) ) {
        return false;
    }
    if ( version_compare( $wp_version, WPF3_REQUIRED_WP_VERSION, '<' ) ) {
        return false;
    }
    $f3_version = explode("-",$f3->VERSION)[0];
    if ( version_compare( $f3_version, WPF3_REQUIRED_F3_VERSION, '<' ) ) {
        return false;
    }

    return true;
}

function wpf3_requirements_error(): void
{
    global $f3;
    $f3_version = explode("-",$f3->VERSION)[0]; //used in the error file.
    require_once( __DIR__ . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( wpf3_requirements_met() ) {
    if ( class_exists( 'WPF3' ) ) {
        $GLOBALS['f3']['lib'] = base::instance();
        $GLOBALS['f3']['plugin'] = new wpf3\main();
        register_activation_hook(   __FILE__, array( $GLOBALS['f3']['plugin'], 'activate' ) );
        register_deactivation_hook( __FILE__, array( $GLOBALS['f3']['plugin'], 'deactivate' ) );
    }
} else {
    add_action( 'admin_notices', 'wpf3_requirements_error' );
}
