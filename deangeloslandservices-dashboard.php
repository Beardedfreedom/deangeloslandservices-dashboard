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
define( 'DALS_AUTH_COOKIE', 'dals_dashboard_auth' );
define( 'DALS_APPROVED_USERS_FILE', DALS_PLUGIN_DIR . 'config/dashboard-approved-users.php' );

/**
 * Dashboard pages configuration.
 * URL slug => template file
 */
function dals_get_pages() {
    return array(
        'dashboard/login'        => 'login',
        'dashboard/logout'       => 'logout',
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
    add_rewrite_rule( '^dashboard/login/?$',        'index.php?dals_page=login',       'top' );
    add_rewrite_rule( '^dashboard/logout/?$',       'index.php?dals_page=logout',      'top' );
    add_rewrite_rule( '^dashboard/triage/?$',       'index.php?dals_page=triage',       'top' );
    add_rewrite_rule( '^dashboard/replies/?$',      'index.php?dals_page=replies',      'top' );
    add_rewrite_rule( '^dashboard/appointments/?$', 'index.php?dals_page=appointments', 'top' );
    add_rewrite_rule( '^dashboard/owners/?$',       'index.php?dals_page=owners',       'top' );
    add_rewrite_rule( '^dashboard/meetings/?$',     'index.php?dals_page=meetings',     'top' );
    add_rewrite_rule( '^dashboard/?$',              'index.php?dals_page=home',         'top' );
}

add_filter( 'query_vars', 'dals_query_vars' );
function dals_query_vars( $vars ) {
    $vars[] = 'dals_page';
    $vars[] = 'dals_data';
    return $vars;
}

/**
 * Normalize a full name before matching it.
 */
function dals_normalize_name( $name ) {
    $name = strtolower( wp_strip_all_tags( (string) $name ) );
    $name = preg_replace( '/[^a-z0-9 ]+/', ' ', $name );
    $name = preg_replace( '/\s+/', ' ', trim( $name ) );
    return $name;
}

/**
 * Normalize a phone number before matching it.
 */
function dals_normalize_phone( $phone ) {
    $digits = preg_replace( '/\D+/', '', (string) $phone );
    if ( strlen( $digits ) > 10 ) {
        $digits = substr( $digits, -10 );
    }
    return $digits;
}

/**
 * Return the raw approved-users textarea value.
 */
function dals_get_approved_users_raw() {
    return (string) get_option( 'dals_approved_users_raw', '' );
}

/**
 * Return repo-managed approved users from the plugin config file.
 */
function dals_get_seed_approved_user_lines() {
    if ( ! file_exists( DALS_APPROVED_USERS_FILE ) ) {
        return array();
    }

    $entries = require DALS_APPROVED_USERS_FILE;
    if ( ! is_array( $entries ) ) {
        return array();
    }

    $lines = array();
    foreach ( $entries as $entry ) {
        $entry = trim( (string) $entry );
        if ( '' !== $entry ) {
            $lines[] = $entry;
        }
    }

    return $lines;
}

/**
 * Parse approved users from the saved dashboard access list.
 * One user per line: Full Name|4078336857
 */
function dals_get_approved_users() {
    $users = array();
    $seen  = array();
    $lines = array_merge(
        dals_get_seed_approved_user_lines(),
        preg_split( '/\r\n|\r|\n/', dals_get_approved_users_raw() )
    );

    foreach ( $lines as $line ) {
        $line = trim( (string) $line );
        if ( '' === $line || 0 === strpos( $line, '#' ) ) {
            continue;
        }

        if ( false !== strpos( $line, '|' ) ) {
            $parts = explode( '|', $line, 2 );
        } else {
            $parts = explode( ',', $line, 2 );
        }

        if ( 2 !== count( $parts ) ) {
            continue;
        }

        $label = trim( $parts[0] );
        $name  = dals_normalize_name( $parts[0] );
        $phone = dals_normalize_phone( $parts[1] );

        if ( '' === $label || '' === $name || '' === $phone ) {
            continue;
        }

        $key = $name . '|' . $phone;
        if ( isset( $seen[ $key ] ) ) {
            continue;
        }
        $seen[ $key ] = true;

        $users[] = array(
            'label' => $label,
            'name'  => $name,
            'phone' => $phone,
        );
    }

    return apply_filters( 'dals_approved_users', $users );
}

/**
 * Build a dashboard URL using the query-var fallback that does not rely on rewrite flushing.
 */
function dals_dashboard_query_url( $page = '', $args = array() ) {
    $query_args = array();
    if ( '' !== $page ) {
        $query_args['dals_page'] = $page;
    }

    foreach ( $args as $key => $value ) {
        if ( '' !== $value && null !== $value ) {
            $query_args[ $key ] = $value;
        }
    }

    return add_query_arg( $query_args, home_url( '/' ) );
}

/**
 * Return the login URL with an optional redirect target.
 */
function dals_login_url( $redirect = '' ) {
    return dals_dashboard_query_url(
        'login',
        array(
            'redirect' => $redirect,
        )
    );
}

/**
 * Return the logout URL.
 */
function dals_logout_url() {
    return dals_dashboard_query_url( 'logout' );
}

/**
 * Check that a redirect target stays inside the dashboard.
 */
function dals_is_safe_dashboard_redirect( $url ) {
    $redirect = wp_validate_redirect( $url, false );
    if ( ! $redirect ) {
        return false;
    }

    $path = wp_parse_url( $redirect, PHP_URL_PATH );
    return is_string( $path ) && 0 === strpos( $path, '/dashboard' );
}

/**
 * Return the current dashboard request URL for post-login redirects.
 */
function dals_current_request_url() {
    $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/dashboard/';
    return home_url( $request_uri );
}

/**
 * Return the cookie path for dashboard-only auth state.
 */
function dals_auth_cookie_path() {
    return '/dashboard/';
}

/**
 * Return a conservative per-IP rate limit key for login attempts.
 */
function dals_login_attempt_key() {
    $ip = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) : 'unknown';
    return 'dals_login_attempts_' . md5( $ip );
}

/**
 * Return true when the login form should be temporarily blocked.
 */
function dals_is_login_rate_limited() {
    return (int) get_transient( dals_login_attempt_key() ) >= 5;
}

/**
 * Record a failed login attempt for the current client.
 */
function dals_record_failed_login() {
    $key      = dals_login_attempt_key();
    $attempts = (int) get_transient( $key );
    set_transient( $key, $attempts + 1, 15 * MINUTE_IN_SECONDS );
}

/**
 * Clear failed login attempts after a successful login.
 */
function dals_clear_failed_logins() {
    delete_transient( dals_login_attempt_key() );
}

/**
 * Build and set the signed dashboard auth cookie.
 */
function dals_set_auth_cookie( $user ) {
    $expires = time() + DAY_IN_SECONDS;
    $payload = wp_json_encode(
        array(
            'name'  => $user['name'],
            'phone' => $user['phone'],
            'exp'   => $expires,
        )
    );

    $encoded   = base64_encode( $payload );
    $signature = hash_hmac( 'sha256', $encoded, wp_salt( 'auth' ) );
    $value     = $encoded . '.' . $signature;

    setcookie(
        DALS_AUTH_COOKIE,
        $value,
        array(
            'expires'  => $expires,
            'path'     => dals_auth_cookie_path(),
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        )
    );

    $_COOKIE[ DALS_AUTH_COOKIE ] = $value;
}

/**
 * Clear the dashboard auth cookie.
 */
function dals_clear_auth_cookie() {
    setcookie(
        DALS_AUTH_COOKIE,
        '',
        array(
            'expires'  => time() - HOUR_IN_SECONDS,
            'path'     => dals_auth_cookie_path(),
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        )
    );

    unset( $_COOKIE[ DALS_AUTH_COOKIE ] );
}

/**
 * Return the authenticated dashboard user or null.
 */
function dals_get_authenticated_user() {
    if ( current_user_can( 'manage_options' ) ) {
        $user = wp_get_current_user();
        return array(
            'label' => $user->display_name ? $user->display_name : 'Administrator',
            'role'  => 'wp_admin',
        );
    }

    if ( empty( $_COOKIE[ DALS_AUTH_COOKIE ] ) ) {
        return null;
    }

    $parts = explode( '.', wp_unslash( $_COOKIE[ DALS_AUTH_COOKIE ] ), 2 );
    if ( 2 !== count( $parts ) ) {
        return null;
    }

    $expected = hash_hmac( 'sha256', $parts[0], wp_salt( 'auth' ) );
    if ( ! hash_equals( $expected, $parts[1] ) ) {
        return null;
    }

    $payload = json_decode( base64_decode( $parts[0], true ), true );
    if ( ! is_array( $payload ) || empty( $payload['name'] ) || empty( $payload['phone'] ) || empty( $payload['exp'] ) ) {
        return null;
    }

    if ( time() > (int) $payload['exp'] ) {
        return null;
    }

    foreach ( dals_get_approved_users() as $user ) {
        if ( $user['name'] === $payload['name'] && $user['phone'] === $payload['phone'] ) {
            return $user;
        }
    }

    return null;
}

/**
 * Redirect unauthenticated dashboard requests to the login page.
 */
function dals_require_auth() {
    if ( dals_get_authenticated_user() ) {
        return true;
    }

    wp_safe_redirect( dals_login_url( dals_current_request_url() ) );
    exit;
}

/**
 * Render an HTML dashboard template and inject a logout link.
 */
function dals_render_template( $file ) {
    $html = file_get_contents( $file );
    if ( false === $html ) {
        status_header( 500 );
        exit;
    }

    $logout = sprintf(
        '<a href="%1$s" style="%2$s">Log out</a>',
        esc_url( dals_logout_url() ),
        esc_attr( 'position:fixed;top:18px;right:18px;z-index:9999;padding:10px 14px;border-radius:999px;background:rgba(10,10,8,.85);border:1px solid rgba(245,197,24,.22);color:#f5c518;text-decoration:none;font:600 13px/1.2 -apple-system,BlinkMacSystemFont,Segoe UI,sans-serif;backdrop-filter:blur(8px);' )
    );

    echo str_replace( '</body>', $logout . '</body>', $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Handle login POSTs. Returns an error string or redirects on success.
 */
function dals_handle_login_request() {
    if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
        return '';
    }

    if ( ! isset( $_POST['dals_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dals_login_nonce'] ) ), 'dals_dashboard_login' ) ) {
        return 'The login session expired. Try again.';
    }

    if ( dals_is_login_rate_limited() ) {
        return 'Too many login attempts. Wait 15 minutes and try again.';
    }

    $approved_users = dals_get_approved_users();
    if ( empty( $approved_users ) ) {
        return 'Dashboard access is not configured yet. Ask an administrator to add approved users.';
    }

    $name  = dals_normalize_name( wp_unslash( $_POST['full_name'] ?? '' ) );
    $phone = dals_normalize_phone( wp_unslash( $_POST['phone_number'] ?? '' ) );

    foreach ( $approved_users as $user ) {
        if ( $user['name'] === $name && $user['phone'] === $phone ) {
            dals_clear_failed_logins();
            dals_set_auth_cookie( $user );

            $redirect = isset( $_POST['redirect_to'] ) ? wp_unslash( $_POST['redirect_to'] ) : home_url( '/dashboard/' );
            if ( ! dals_is_safe_dashboard_redirect( $redirect ) ) {
                $redirect = home_url( '/dashboard/' );
            }

            wp_safe_redirect( $redirect );
            exit;
        }
    }

    dals_record_failed_login();
    return 'That name and phone number are not on the approved access list.';
}

/**
 * Render the dashboard login page.
 */
function dals_render_login_page( $error = '' ) {
    $file = DALS_PLUGIN_DIR . 'templates/login.php';
    if ( ! file_exists( $file ) ) {
        status_header( 500 );
        echo 'Missing login template.'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    $redirect   = isset( $_REQUEST['redirect'] ) ? wp_unslash( $_REQUEST['redirect'] ) : home_url( '/dashboard/' );
    $redirect   = dals_is_safe_dashboard_redirect( $redirect ) ? $redirect : home_url( '/dashboard/' );
    $configured = ! empty( dals_get_approved_users() );
    $error      = (string) $error;

    include $file;
    exit;
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

    if ( 'logout' === $page ) {
        dals_clear_auth_cookie();
        wp_safe_redirect( dals_login_url() );
        exit;
    }

    if ( 'login' === $page ) {
        if ( dals_get_authenticated_user() ) {
            wp_safe_redirect( home_url( '/dashboard/' ) );
            exit;
        }

        $error = dals_handle_login_request();
        dals_render_login_page( $error );
    }

    $map = array(
        'home'         => 'command-center.html',
        'triage'       => 'triage-dashboard.html',
        'replies'      => 'agent-reply-hub.html',
        'appointments' => 'cancelled-appointments.html',
        'owners'       => 'owners.html',
        'meetings'     => 'meeting-overview.html',
    );

    if ( ! isset( $map[ $page ] ) ) {
        return;
    }

    dals_require_auth();

    $file = DALS_PLUGIN_DIR . 'templates/' . $map[ $page ];
    if ( file_exists( $file ) ) {
        header( 'Content-Type: text/html; charset=utf-8' );
        dals_render_template( $file );
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

add_action( 'template_redirect', 'dals_serve_data' );
function dals_serve_data() {
    if ( ! get_query_var( 'dals_data' ) ) {
        return;
    }

    if ( ! dals_get_authenticated_user() ) {
        status_header( 403 );
        header( 'Content-Type: application/json; charset=utf-8' );
        echo wp_json_encode( array( 'error' => 'Authentication required.' ) );
        exit;
    }

    $file = DALS_PLUGIN_DIR . 'templates/data/dashboard-data.json';
    if ( file_exists( $file ) ) {
        header( 'Content-Type: application/json; charset=utf-8' );
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
    $settings_url = admin_url( 'options-general.php?page=dals-dashboard-access' );
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p><strong>DeAngelos Dashboard</strong> is active! ';
    echo '<a href="' . esc_url( $url ) . '" target="_blank">View Command Center &rarr;</a> | ';
    echo '<a href="' . esc_url( $settings_url ) . '">Manage Access List</a></p>';
    echo '</div>';
}

/**
 * Add the dashboard access settings page.
 */
add_action( 'admin_menu', 'dals_register_access_settings_page' );
function dals_register_access_settings_page() {
    add_options_page(
        'Dashboard Access',
        'Dashboard Access',
        'manage_options',
        'dals-dashboard-access',
        'dals_render_access_settings_page'
    );
}

/**
 * Render the access settings page.
 */
function dals_render_access_settings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $raw = dals_get_approved_users_raw();
    ?>
    <div class="wrap">
        <h1>Dashboard Access</h1>
        <p>Enter one approved user per line using this format:</p>
        <p><code>Full Name|4078336857</code></p>
        <p class="description">WordPress-saved entries from this page are merged with repo-managed entries from <code>config/dashboard-approved-users.php</code>.</p>
        <?php if ( isset( $_GET['updated'] ) ) : ?>
            <div class="notice notice-success"><p>Dashboard access list saved.</p></div>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'dals_save_access' ); ?>
            <input type="hidden" name="action" value="dals_save_access">
            <textarea name="dals_approved_users_raw" rows="12" class="large-text code"><?php echo esc_textarea( $raw ); ?></textarea>
            <p class="description">Use exact names and phone numbers. Phone formatting does not matter; the plugin normalizes digits before matching.</p>
            <?php submit_button( 'Save Access List' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Save the access settings page.
 */
add_action( 'admin_post_dals_save_access', 'dals_save_access_settings' );
function dals_save_access_settings() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized.' );
    }

    check_admin_referer( 'dals_save_access' );

    $raw = isset( $_POST['dals_approved_users_raw'] ) ? wp_unslash( $_POST['dals_approved_users_raw'] ) : '';
    $raw = preg_replace( "/\r\n?/", "\n", (string) $raw );
    $raw = trim( $raw );

    update_option( 'dals_approved_users_raw', $raw, false );

    wp_safe_redirect(
        add_query_arg(
            array(
                'page'    => 'dals-dashboard-access',
                'updated' => '1',
            ),
            admin_url( 'options-general.php' )
        )
    );
    exit;
}

/**
 * Set transient on activation for the admin notice.
 */
register_activation_hook( __FILE__, function() {
    set_transient( 'dals_activated', true, 30 );
});
