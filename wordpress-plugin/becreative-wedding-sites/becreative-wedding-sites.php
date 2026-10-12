<?php
/**
 * Plugin Name: BeCreative Wedding Sites
 * Description: Sistema reutilizable para crear y gestionar webs de boda de Be Creative Weddings.
 * Version: 0.1.0
 * Author: BeCreative Weddings
 * Text Domain: bcw-wedding-sites
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BCW_WEDDING_SITES_VERSION', '0.1.0' );
define( 'BCW_WEDDING_SITES_FILE', __FILE__ );
define( 'BCW_WEDDING_SITES_DIR', plugin_dir_path( __FILE__ ) );
define( 'BCW_WEDDING_SITES_URL', plugin_dir_url( __FILE__ ) );

require_once BCW_WEDDING_SITES_DIR . 'includes/class-bcw-wedding-post-type.php';

function bcw_wedding_sites_init() {
    BCW_Wedding_Post_Type::register();
}
add_action( 'init', 'bcw_wedding_sites_init' );

function bcw_wedding_sites_activate() {
    BCW_Wedding_Post_Type::register();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'bcw_wedding_sites_activate' );

function bcw_wedding_sites_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'bcw_wedding_sites_deactivate' );
