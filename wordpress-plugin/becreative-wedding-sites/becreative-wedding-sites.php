<?php
/**
 * Plugin Name: BeCreative Wedding Sites
 * Description: Sistema reutilizable para crear y gestionar webs de boda de Be Creative Weddings.
 * Version: 0.2.0
 * Author: BeCreative Weddings
 * Text Domain: bcw-wedding-sites
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BCW_WEDDING_SITES_VERSION', '0.2.0' );
define( 'BCW_WEDDING_SITES_FILE', __FILE__ );
define( 'BCW_WEDDING_SITES_DIR', plugin_dir_path( __FILE__ ) );
define( 'BCW_WEDDING_SITES_URL', plugin_dir_url( __FILE__ ) );

require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-post-type.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-fields.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-data.php';
require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-router.php';

function bcw_wedding_sites_init() {
    BCW_Wedding_Post_Type::register();
    BCW_Wedding_Fields::init();
    BCW_Wedding_Router::init();
}
add_action( 'plugins_loaded', 'bcw_wedding_sites_init' );

function bcw_wedding_sites_activate() {
    BCW_Wedding_Post_Type::register();
    BCW_Wedding_Router::add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bcw_wedding_sites_activate' );

function bcw_wedding_sites_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bcw_wedding_sites_deactivate' );
