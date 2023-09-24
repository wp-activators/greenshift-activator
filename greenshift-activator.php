<?php

/**
 * @wordpress-plugin
 * Plugin Name:       GreenShift Activator
 * Plugin URI:        https://github.com/wp-activators/greenshift-activator
 * Description:       GreenShift Plugin Activator
 * Version:           1.0.0
 * Requires at least: 5.9.0
 * Requires PHP:      7.2
 * Author:            mohamedhk2
 * Author URI:        https://github.com/mohamedhk2
 **/

defined( 'ABSPATH' ) || exit;
const GREENSHIFT_ACTIVATOR_NAME   = 'GreenShift Activator';
const GREENSHIFT_ACTIVATOR_DOMAIN = 'greenshift-activator';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
if (
	activator_admin_notice_ignored()
	|| activator_admin_notice_plugin_install( 'greenshift-animation-and-page-builder-blocks/plugin.php', 'greenshift-animation-and-page-builder-blocks', 'Greenshift', GREENSHIFT_ACTIVATOR_NAME, GREENSHIFT_ACTIVATOR_DOMAIN )
	|| activator_admin_notice_plugin_activate( 'greenshift-animation-and-page-builder-blocks/plugin.php', GREENSHIFT_ACTIVATOR_NAME, GREENSHIFT_ACTIVATOR_DOMAIN )
) {
	return;
}
add_filter( 'pre_http_request', function ( $pre, $parsed_args, $url ) {
	$STORE_URL = defined( EDD_GSPB_STORE_URL ) ? EDD_GSPB_STORE_URL : 'https://shop.greenshiftwp.com/';
	if ( str_starts_with( $url, $STORE_URL ) ) {
		switch ( $parsed_args['body']['edd_action'] ?? false ) {
			case 'activate_license':
			case 'check_license':
				$data = [
					'status'        => 'valid',
					'expires'       => 'lifetime',
					'license_limit' => 0,
					'success'       => true,
					'license'       => 'free4all',
				];

				return activator_json_response( $data );
			case 'deactivate_license':

				return activator_json_response( [] );
		}
	}

	return $pre;
}, 99, 3 );
add_action( 'plugins_loaded', function () {
	$elp = new EddLicensePage;
	remove_action( 'admin_menu', [ $elp, 'edd_license_menu' ], 999 );
	remove_action( 'admin_init', [ $elp, 'edd_register_option' ] );
	remove_action( 'admin_init', [ $elp, 'edd_activate_license' ] );
	remove_action( 'admin_init', [ $elp, 'edd_deactivate_license' ] );
	remove_action( 'admin_notices', [ $elp, 'edd_admin_notices' ] );
	$licensesData = activator_private_property( $elp, 'licensesData' );
	try {
		foreach ( $licensesData as $plugin => $license ) {
			$licensesData[ $plugin ]['license']       = 'free4all';
			$licensesData[ $plugin ]['license_limit'] = 0;
			$licensesData[ $plugin ]['status']        = 'valid';
			$licensesData[ $plugin ]['expires']       = 'lifetime';
		}
		update_option( 'gspb_edd_licenses', $licensesData );
	} catch ( Exception $e ) {
	}
} );
if ( ( $_GET['page'] ?? null ) != 'greenshift_upgrade' ) {
	add_action( 'admin_menu', function () {
		remove_submenu_page( 'greenshift_dashboard', 'greenshift_upgrade' );
	}, 99 );
}
