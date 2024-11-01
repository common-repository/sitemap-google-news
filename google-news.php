<?php
 /* 
	Plugin Name: Sitemap Google News 
	Plugin URI: http://www.agenciabwm.com/
	Description: Automatically generates a news sitemap used by Google News.
	Version: 0.0.2
	Author: Lucas Milanez
	Author URI: https://br.linkedin.com/in/milanezlucas
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
require_once( plugin_dir_path( __FILE__ ) . '/google-news-utils.php' );
require_once( plugin_dir_path( __FILE__ ) . '/google-news-admin.php' );
require_once( plugin_dir_path( __FILE__ ) . '/google-news-metabox.php' );
require_once( plugin_dir_path( __FILE__ ) . '/google-news-generate.php' );

$gns_admin 	= new GNS_Admin( 'create' );

add_action( 'admin_init',   array( 'GNS_Posts', 'gns_metabox' ) );
add_action( 'save_post',    array( 'GNS_Posts', 'gns_metabox_save' ) );

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	@unlink( ABSPATH . 'news-sitemap.xml' );
}

?>