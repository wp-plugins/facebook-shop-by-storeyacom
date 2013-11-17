<?php
/*
Plugin Name: Storeya Feed
Plugin URI: http://www.storeya.com/
Description: StoreYa extension that allows you to get your products feed.
Author: StoreYa 
Version: 2.2
Author URI: http://www.storeya.com/
License: 
*/

if ( is_admin() ) {
	require_once ( 'woocommerce-storeya-common.php' );
	require_once ( 'woocommerce-storeya-admin.php' );
} else {
    if ( isset ( $_REQUEST['action'] ) && 'woocommerce_storeya' == $_REQUEST['action'] ) {
	    require_once ( 'woocommerce-storeya-common.php' );
	    require_once ( 'woocommerce-storeya-frontend.php' );
    }
}


