<?php
/*
Plugin Name: Combat Comments Bot
Plugin URI: http://www.revood.com/
Author: Reuben Gunday
Author URI: http://www.revood.com/
Version: 1.0
Description: This plugin avoids bots posting comments directly on your site.
Licence: GPLv2
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

add_action( 'comment_form_after_fields', 'ccb_comment_fields' );
function ccb_comment_fields() {
	global $post;

	wp_nonce_field( get_ccb_nonce_secret() . $post->ID, '_nonce', true, true );
}

add_action( 'pre_comment_on_post', 'ccb_pre_comment_check' );
function ccb_pre_comment_check( $id ) {
	if ( is_user_logged_in() )
		return $id;

	if ( ! isset( $_POST['_nonce'] ) ) {
		wp_die( 'Security check fail' );
	}

	if ( ! wp_verify_nonce( $_POST['_nonce'], get_ccb_nonce_secret() . $_POST['comment_post_ID'] ) ) {
		wp_die( 'There seems to be some problem adding your comment. Please contact the administrator' );
	}

	return $id;
}

add_action( 'admin_init', 'ccb_settings' );
function ccb_settings() {
	register_setting( 'general', 'ccb-nonce', 'esc_attr' );
	add_settings_field( 'ccb-nonce', 'Combat Comments Bot Secret Key', 'ccb_field', 'general' );
}

function ccb_field() {
	$nonce_key = get_ccb_nonce_secret();
	echo '<input type="text" value="' . $nonce_key . '" class="regular-text" name="ccb-nonce" />';
}

function get_ccb_nonce_secret() {
	return ( get_option( 'ccb-nonce' ) ) ? get_option( 'ccb-nonce' ) : 'comment';
}
?>