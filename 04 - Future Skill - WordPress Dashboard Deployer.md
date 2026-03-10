---
title: "Future Skill — WordPress Dashboard Deployer"
type: "skill-spec"
created: "2026-03-08"
status: "ready-to-build"
triggers:
  - "deploy dashboard to WordPress"
  - "publish HTML to WordPress"
  - "create WordPress plugin from HTML"
  - "push dashboard to WordPress site"
---

# Future Skill: WordPress Dashboard Deployer

> A reusable Claude Code skill that takes any set of HTML dashboard files and deploys them as a WordPress plugin via GitHub Deployments.

---

## Why This Skill Exists

This session proved a repeatable pattern: take standalone HTML dashboards (with inline CSS/JS and a shared JSON data source) and publish them as a multi-page WordPress plugin that auto-deploys from GitHub. The pattern worked on the first attempt and can be generalized for any dashboard project.

---

## Skill Trigger Conditions

Activate this skill when:
- User has HTML dashboard files they want on a WordPress site
- User mentions "WordPress", "deploy", "publish", and "dashboard" together
- User has GitHub Deployments connected to their WordPress site
- User wants full-screen pages (bypassing the WordPress theme)

---

## Skill Inputs (What to Gather)

| Input | Example | Required |
|-------|---------|----------|
| HTML files to deploy | `triage.html`, `replies.html` | Yes |
| JSON data file path | `data/dashboard-data.json` | If applicable |
| WordPress site URL | `beardedfreedom.online` | Yes |
| GitHub repo name | `Beardedfreedom/deangeloslandservices-dashboard` | Yes |
| Deploy target path | `/wp-content/plugins/<name>` | Yes |
| Plugin name | `DeAngelos Land Services Dashboard` | Yes |
| Route structure | `/dashboard/`, `/dashboard/triage/` | Yes |
| WP Admin access | Browser tab logged in | Yes |

---

## Skill Execution Steps

### Phase 1: Scaffold Plugin
```
<plugin-name>.php          <- Auto-generate from template
templates/
  <page-1>.html            <- Copy from user's files
  <page-2>.html
  data/
    <data-file>.json       <- Copy if exists
```

**Plugin PHP template** generates:
- Plugin header (name, description, version, author)
- Rewrite rules for each page route
- Query var registration
- `template_redirect` handler that serves HTML via `readfile()` + `exit`
- JSON data endpoint (if data file exists)
- Activation/deactivation hooks with `flush_rewrite_rules()`
- Admin notice with link to dashboard

### Phase 2: Adapt Templates
For each HTML file:
- Update `DATA_URL` to absolute WordPress path (`/dashboard/data/...`)
- Update inter-page navigation links to WordPress routes
- Verify each page is a complete `<!DOCTYPE html>` document
- Ensure all CSS/JS is inline (no external dependencies)

### Phase 3: Deploy
- Commit all files on working branch
- Merge to `main` and push
- Wait 15-30 seconds for GitHub Deployment to complete

### Phase 4: Activate & Verify
- Navigate to WP Admin > Plugins
- Activate the plugin
- Test each route in browser
- If any 404: save Permalinks (Settings > Permalinks > Save Changes)

---

## Known Gotchas (Built Into Skill)

| Gotcha | Auto-Fix |
|--------|----------|
| Plugin deactivated after deploy | Always check plugin status post-deploy |
| New routes 404 | Flush rewrite rules via activation or Permalinks save |
| Relative data paths break | Always use absolute paths (`/dashboard/data/...`) |
| Theme wrapping custom pages | Ensure `exit;` after `readfile()` |
| Nav links point to `.html` files | Replace with WordPress routes |
| WordPress.com caching | Add cache-bust param `?t=Date.now()` to fetch calls |

---

## Reusable PHP Template

```php
<?php
/**
 * Plugin Name: {{PLUGIN_NAME}}
 * Description: {{PLUGIN_DESCRIPTION}}
 * Version: 1.0.0
 * Author: {{AUTHOR}}
 * License: Private
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( '{{PREFIX}}_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Register rewrite rules
add_action( 'init', function() {
    {{#each routes}}
    add_rewrite_rule( '^{{this.slug}}/?$', 'index.php?{{../prefix}}_page={{this.key}}', 'top' );
    {{/each}}
});

// Register query var
add_filter( 'query_vars', function( $vars ) {
    $vars[] = '{{prefix}}_page';
    return $vars;
});

// Serve templates
add_action( 'template_redirect', function() {
    $page = get_query_var( '{{prefix}}_page' );
    if ( ! $page ) return;

    $map = array(
        {{#each routes}}
        '{{this.key}}' => '{{this.template}}',
        {{/each}}
    );

    if ( ! isset( $map[ $page ] ) ) return;

    $file = {{PREFIX}}_PLUGIN_DIR . 'templates/' . $map[ $page ];
    if ( file_exists( $file ) ) {
        header( 'Content-Type: text/html; charset=utf-8' );
        readfile( $file );
        exit;
    }
});

// Flush rewrite rules on activation
register_activation_hook( __FILE__, function() {
    // Re-register rules then flush
    do_action( 'init' );
    flush_rewrite_rules();
});

register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
});
```

---

## Extension Points for Future Versions

1. **Authentication gate** — Require WP login before serving dashboard pages
2. **REST API data endpoint** — Replace static JSON with live GHL API calls
3. **Admin settings page** — Let user configure refresh interval, GHL API key, etc.
4. **Multi-site support** — Deploy same plugin across multiple WordPress sites
5. **Auto-activate after deploy** — WP-CLI command in GitHub Actions to auto-activate
6. **CSS theme support** — Allow theme switching (dark/light) via WP customizer

---

## Success Metrics

This skill is successful when:
- All HTML pages render at their WordPress routes
- JSON data loads correctly
- Navigation between pages works
- Auto-refresh is functional
- No WordPress theme chrome leaks into the pages
- Plugin survives redeployment (with manual reactivation)

---

*This skill spec was derived from the successful deployment of the DeAngelos Land Services GHL Triage Dashboard to beardedfreedom.online on 2026-03-08.*
