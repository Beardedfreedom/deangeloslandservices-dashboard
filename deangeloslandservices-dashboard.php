<?php
/**
 * Plugin Name: DeAngelos Land Services Dashboard
 * Description: GHL Triage Dashboard for DeAngelos Land Services. Visit /dashboard/ for the Command Center.
 * Version: 1.0.0
 * Author: Steven Sanders
 * License: Private
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'DALS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DALS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Dashboard pages configuration.
 * URL slug => template file
 */
function dals_get_pages() {
    return array(
        'dashboard'              => 'command-center.html',
        'dashboard/triage'       => 'triage-dashboard.html',
        'dashboard/replies'      => 'agent-reply-hub.html',
        'dashboard/appointments' => 'cancelled-appointments.html',
    );
}

/**
 * Register rewrite rules for all dashboard pages.
 */
add_action( 'init', 'dals_register_rewrites' );
function dals_register_rewrites() {
    add_rewrite_rule( '^dashboard/triage/?$',       'index.php?dals_page=triage',       'top' );
    add_rewrite_rule( '^dashboard/replies/?$',      'index.php?dals_page=replies',      'top' );
    add_rewrite_rule( '^dashboard/appointments/?$', 'index.php?dals_page=appointments', 'top' );
    add_rewrite_rule( '^dashboard/?$',              'index.php?dals_page=home',         'top' );
}

add_filter( 'query_vars', 'dals_query_vars' );
function dals_query_vars( $vars ) {
    $vars[] = 'dals_page';
    return $vars;
}

/**
 * Serve the correct HTML template based on the route.
 * Full-screen: bypasses the WordPress theme entirely.
 */
add_action( 'template_redirect', 'dals_serve_page' );
function dals_serve_page() {
    $page = get_query_var( 'dals_page' );
    if ( ! $page ) {
        return;
    }

    $map = array(
        'home'         => 'command-center.html',
        'triage'       => 'triage-dashboard.html',
        'replies'      => 'agent-reply-hub.html',
        'appointments' => 'cancelled-appointments.html',
    );

    if ( ! isset( $map[ $page ] ) ) {
        return;
    }

    $file = DALS_PLUGIN_DIR . 'templates/' . $map[ $page ];
    if ( file_exists( $file ) ) {
        header( 'Content-Type: text/html; charset=utf-8' );
        readfile( $file );
        exit;
    }
}

/**
 * Serve the JSON data file at /dashboard/data/dashboard-data.json
 */
add_action( 'init', 'dals_register_data_rewrite' );
function dals_register_data_rewrite() {
    add_rewrite_rule(
        '^dashboard/data/dashboard-data\.json$',
        'index.php?dals_data=1',
        'top'
    );
}

add_filter( 'query_vars', 'dals_data_query_var' );
function dals_data_query_var( $vars ) {
    $vars[] = 'dals_data';
    return $vars;
}

add_action( 'template_redirect', 'dals_serve_data' );
function dals_serve_data() {
    if ( ! get_query_var( 'dals_data' ) ) {
        return;
    }
    $file = DALS_PLUGIN_DIR . 'templates/data/dashboard-data.json';
    if ( file_exists( $file ) ) {
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Access-Control-Allow-Origin: *' );
        readfile( $file );
        exit;
    }
    status_header( 404 );
    exit;
}

/**
 * Flush rewrite rules on activation / deactivation.
 */
register_activation_hook( __FILE__, 'dals_activate' );
function dals_activate() {
    dals_register_rewrites();
    dals_register_data_rewrite();
    flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'dals_deactivate' );
function dals_deactivate() {
    flush_rewrite_rules();
}

/**
 * Add a quick-links admin notice after activation.
 */
add_action( 'admin_notices', 'dals_admin_notice' );
function dals_admin_notice() {
    if ( ! get_transient( 'dals_activated' ) ) {
        return;
    }
    delete_transient( 'dals_activated' );
    $url = home_url( '/dashboard/' );
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p><strong>DeAngelos Dashboard</strong> is active! ';
    echo '<a href="' . esc_url( $url ) . '" target="_blank">View Command Center &rarr;</a></p>';
    echo '</div>';
}

/**
 * Set transient on activation for the admin notice.
 */
register_activation_hook( __FILE__, function() {
    set_transient( 'dals_activated', true, 30 );
});
