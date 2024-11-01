<?php
/**
 * Plugin Name: Spoddano - Cardano for Woocommerce
 * Plugin URI: https://spiraloutdesigns.com/plugins-and-development.html
 * Author: Steven Mihelakis
 * Author URI: https://spiraloutdesigns.com
 * Description: Cardano (ADA) payment gateway for Woocommerce.
 * Version: 1.0.0
 * License: GPL2
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: spod-ada-txt
 * 
 * Cardano for Woocommerce Plugin
 * Copyright (C) 2021, Steven Mihelakis
 * Cardano and its branding are copyrighted to the Cardano Foundation, All Rights Reserved.
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * Class WC_Gateway_spoddano file.
 *  
 * @package Woocommerce/spoddano
 * 
*/ 

//Security - exit file if accessed directly
if (!defined('ABSPATH')) {
    exit;
}
//Check if woocommerce is active
if (! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins') ) ) ) return;

//load custom css
add_action('wp_enqueue_scripts', 'spod_wooada_styles', 9999);
function spod_wooada_styles() {
    wp_enqueue_style( 'spod_spoddano_style',  plugin_dir_url( __FILE__ ) . 'css/custom.css' );
}

//If the Woocommerce Payment Gateway function exists load our gateway class
add_action('plugins_loaded', 'spod_payment_init',11);
function spod_payment_init(){
    if (class_exists('WC_Payment_Gateway')) {
    
        require_once plugin_dir_path( __FILE__ ) . '/inc/class-wc-payment-gateway-spoddano.php';
        require_once plugin_dir_path( __FILE__ ) . '/inc/spoddano-checkout-description.php';
    
    }
}

//extend the payment gateways to include our new Cardano gateway
add_filter ('woocommerce_payment_gateways', 'add_to_spoddano_payment_gateway');
function add_to_spoddano_payment_gateway($gateways){
    $gateways[] = 'WC_Gateway_spoddano';
    return $gateways;
}