<?php
/*
	Plugin Name: Wanderlust HOP Envios
	Plugin URI: https://wanderlust-webdesign.com/
	Description: Wanderlust HOP Envios te permite cotizar el valor de un envío con una amplia cantidad de empresas de correo de una forma simple y estandarizada.
	Version: 1.0.9
	Author: Wanderlust Codes
	Author URI: https://wanderlust-webdesign.com
	WC tested up to: 9.2.0
    Requires Plugins: woocommerce
	Copyright: 2007-2024 wanderlust-webdesign.com.
*/

	add_action( 'before_woocommerce_init', function() {
 	        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
 	                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
 	        }
	} );


  add_filter( 'woocommerce_cart_needs_shipping', 'aa_filter_cart_needs_shipping', PHP_INT_MAX );
  function aa_filter_cart_needs_shipping( $needs_shipping ) {
    if( wp_doing_ajax() )
      return $needs_shipping;
    return false;
  }

	require_once( 'includes/functions.php' );
 
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
 
		function woocommerce_hopenvios_init() {
			include_once( 'includes/class-hop.php' );
		}
	  add_action( 'woocommerce_shipping_init', 'woocommerce_hopenvios_init' ); 
 
		function woocommerce_hopenvios_add_method( $methods ) {
			$methods[ 'hopenvios_wanderlust' ] = 'WC_Shipping_HOPEnvios';
			return $methods;
		}

		add_filter( 'woocommerce_shipping_methods', 'woocommerce_hopenvios_add_method' );
 
		function woocommerce_hopenvios_scripts() {
			wp_enqueue_script( 'jquery-ui-sortable' );
		}

		add_action( 'admin_enqueue_scripts', 'woocommerce_hopenvios_scripts' );
		
		$hopenvios_settings = get_option( 'woocommerce_hopenvios_settings', array() );
		
	}