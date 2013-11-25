<?php
/*
Plugin Name: WooCommerce shop to Facebook
Plugin URI: http://www.storeya.com/
Description: StoreYa is a leading Social commerce platform designed for automatically importing web stores onto Facebook, having them fully customized to fit both the Facebook arena and the original brand's look & feel. StoreYa can also automatically import all of your social networks activities from Twitter, Pinterest, Instagram and YouTube onto Facebook.
Author: StoreYa 
Version: 2.4
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

