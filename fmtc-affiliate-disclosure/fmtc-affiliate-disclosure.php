<?php
/*
Plugin Name:    FMTC Affiliate Disclosure
Plugin URI:     https://toolkit.fmtc.co/ftc-disclosure-wordpress-plugin/
Description:    Add FTC-Compliant Disclosure statement to the beginning of your blog posts
Version:        2.0.3
Requires PHP:   5.3
Author:         Eric Nagel
Author URI:     http://www.fmtc.co/about-us/our-team/
License:        GPL2
*/

/*  Copyright 2016  FMTC eric@fmtc.co

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

namespace FMTC\AffiliateDisclosure;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No script kiddies please!' );
}

add_action( 'admin_init', __NAMESPACE__ . '\fmtcdisclose_register_settings' );

register_activation_hook( __FILE__, __NAMESPACE__ . '\fmtcdisclose_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__ . '\fmtcdisclose_deactivate' );
register_uninstall_hook( __FILE__, __NAMESPACE__ . '\fmtcdisclose_uninstall' );

function fmtcdisclose_register_settings() {
	//register settings
	register_setting( 'fmtc-settings-group', 'fmtcdisclose_options' );
}

// activating the default values
function fmtcdisclose_activate() {
	add_option( 'fmtc_affiliate_disclosure_text', '<p><em>We may earn money or products from the companies mentioned in this post.</em></p>' );
}

// deactivating
function fmtcdisclose_deactivate() {
	// Nothing to do on deactivation
}

// uninstalling
function fmtcdisclose_uninstall() {
	# delete all data stored
	delete_option( 'fmtc_affiliate_disclosure_text' );
}

add_action( 'admin_menu', __NAMESPACE__ . '\fmtcdisclose_menu' );
function fmtcdisclose_menu() {
	add_submenu_page( 'options-general.php', 'Affiliate Disclosure', 'Affiliate Disclosure', 0, 'fmtc-affiliate-disclosure', __NAMESPACE__ . '\fmtcdisclose_options' );
}

// Add settings link on plugin page
function fmtcdisclose_settings_link( $links ) {
	$settings_link = '<a href="options-general.php?page=fmtc-affiliate-disclosure">Settings</a>';
	array_unshift( $links, $settings_link );
	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", __NAMESPACE__ . '\fmtcdisclose_settings_link' );

function fmtcdisclose_options() {
	global $wpdb;
	?>
	<div class="wrap">
	<h2>FMTC Affiliate Disclosure</h2>
	<?php

	$post_data = filter_input_array(
		INPUT_POST,
		array(
			'submit'                          => FILTER_SANITIZE_STRING,
			'fmtc_affiliate_disclosure_nonce' => FILTER_SANITIZE_STRING,
			'fmtc_affiliate_disclosure_text'  => array(
				'filter' => FILTER_DEFAULT,
				'flags'  => array( FILTER_FLAG_STRIP_BACKTICK, FILTER_FLAG_ENCODE_LOW, FILTER_FLAG_ENCODE_HIGH, FILTER_FLAG_ENCODE_AMP ),
			),
		)
	);

	if ( $post_data['fmtc_affiliate_disclosure_nonce'] && wp_verify_nonce( $post_data['fmtc_affiliate_disclosure_nonce'], 'fmtc_affiliate_disclosure_nonce' ) && $post_data['fmtc_affiliate_disclosure_text'] ) {
		update_option( 'fmtc_affiliate_disclosure_text', wp_kses_post( trim( $post_data['fmtc_affiliate_disclosure_text'] ) ) );
		?>
		<div id="message" class="updated fade"><p><strong><?php esc_html_e( 'The options have been updated.' ); ?></strong></p></div>
			<?php
	}

	?>
	<form method="post" action="">
		<table class="form-table">

		<tr valign="top">
			<th scope="row">Disclosure Text (HTML OK)</th>
			<td><textarea name="fmtc_affiliate_disclosure_text" rows="6" cols="80"><?php echo esc_textarea( get_option( 'fmtc_affiliate_disclosure_text' ) ); ?></textarea><br />
				Default disclosure: <tt style="background-color: white;">&lt;p&gt;&lt;em&gt;We may earn money or products from the companies mentioned in this post.&lt;/em&gt;&lt;/p&gt;</tt></td>
		</tr>

		<tr>
			<?php wp_nonce_field( 'fmtc_affiliate_disclosure_nonce', 'fmtc_affiliate_disclosure_nonce' ); ?>
			<td><input type="hidden" name="action" value="update_fmtc_clipper" /></td>
			<td><?php submit_button( __( 'Save Changes' ) ); ?></td>
		</tr>
		</table>
	</form>
	</div>
	<?php
}

function fmtc_affiliate_disclosure( $content ) {
	if ( ! is_single() ) {
		return $content;
	}

	return get_option( 'fmtc_affiliate_disclosure_text' ) . $content;
}
add_action( 'the_content', __NAMESPACE__ . '\fmtc_affiliate_disclosure', 99 );
