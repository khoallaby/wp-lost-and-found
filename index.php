<?php
/*
Plugin Name: Lost And Found
Plugin URI: http://www.andynguyen.net
Description: Lost And Found
Author: Andy Nguyen
Version: 1.0
Author URI: http://www.andynguyen.net
*/


#error_reporting(E_ALL);

if( !class_exists('base_plugin') )
    require_once( dirname( __FILE__ ) . '/lib/class.base.php' );
require_once( dirname( __FILE__ ) . '/lib/class.core.php' );
require_once( dirname( __FILE__ ) . '/lib/class.laf-wc.php' );

add_action( 'plugins_loaded', array(laf_core::get_instance(), 'init') );
add_action( 'plugins_loaded', array(laf_wc::get_instance(), 'init') );