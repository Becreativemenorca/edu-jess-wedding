<?php
/**
 * Plugin Name: BeCreative Wedding Sites
 * Description: Sistema reutilizable para crear y gestionar webs de boda de Be Creative Weddings.
 * Version: 0.5.2
 * Author: BeCreative Weddings
 * Text Domain: bcw-wedding-sites
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BCW_WEDDING_SITES_VERSION', '0.5.2' );
define( 'BCW_WEDDING_SITES_FILE', __FILE__ );
define( 'BCW_WEDDING_SITES_DIR', plugin_dir_path( __FILE__ ) );
define( 'BCW_WEDDING_SITES_URL', plugin_dir_url( __FILE__ ) );

require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-post-type.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-fields.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-data.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-router.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-rsvp.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-partner-portal.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-sharing.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-rsvp-admin.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-transfer.php';

function bcw_wedding_sites_register_post_type() {
    BCW_Wedding_Post_Type::register();
}
add_action( 'init', 'bcw_wedding_sites_register_post_type', 5 );

BCW_Wedding_Fields::init();
BCW_Wedding_Router::init();
BCW_RSVP::init();
BCW_Partner_Portal::init();
BCW_Sharing::init();
BCW_RSVP_Admin::init();
BCW_Wedding_Transfer::init();

function bcw_wedding_sites_activate() {
    BCW_Wedding_Post_Type::register();
    BCW_Wedding_Router::add_rewrite_rules();
    BCW_RSVP::install_table();
    BCW_Partner_Portal::install_role();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bcw_wedding_sites_activate' );

function bcw_wedding_sites_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bcw_wedding_sites_deactivate' );
