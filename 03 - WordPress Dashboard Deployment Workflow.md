---
title: "WordPress Dashboard Deployment — Workflow Summary"
type: "workflow-reference"
created: "2026-03-08"
status: "complete"
live_url: "https://beardedfreedom.online/dashboard/"
repo: "Beardedfreedom/deangeloslandservices-dashboard"
---

# WordPress Dashboard Deployment — Workflow Summary

> How we took the GHL Triage Dashboard from local HTML files to a live, multi-page WordPress site deployed via GitHub.

---

## The Problem

We had a fully functional single-page HTML dashboard (`index.html`) with tabbed views for Triage, Replies, Appointments, and Owners — but it was only accessible locally or via GitHub Pages. Steven needed it running on his WordPress site at `beardedfreedom.online` as a test environment before pushing to production.

---

## Approach Options Evaluated

| Approach | Pros | Cons | Verdict |
|----------|------|------|---------|
| **WPCode Plugin** (shortcode) | Easy install, no deploy pipeline | Single-page only, CSS conflicts with theme | Started here, pivoted away |
| **Custom WordPress Plugin** via GitHub Deployments | Full control, multi-page, auto-deploy on git push | More setup, needs rewrite rules | **Winner** |
| **iframe embed** | Simplest | No SEO, cross-origin issues, feels hacky | Rejected |

---

## Winning Workflow — Step by Step

### Step 1: Assess Environment
- WordPress.com Business plan (supports custom plugins)
- WP 6.9.1, PHP 8.4, Twenty Twenty-Two theme
- GitHub Deployments already connected: repo `Beardedfreedom/deangeloslandservices-dashboard` auto-deploys to `/wp-content/plugins/deangeloslandservices-dashboard` on push to `main`

### Step 2: Create Plugin Structure
Built a WordPress plugin that serves full-screen HTML pages **bypassing the theme entirely**:

```
deangeloslandservices-dashboard/
  deangeloslandservices-dashboard.php    <- Plugin entry point
  templates/
    command-center.html                  <- /dashboard/
    triage-dashboard.html                <- /dashboard/triage/
    agent-reply-hub.html                 <- /dashboard/replies/
    cancelled-appointments.html          <- /dashboard/appointments/
    owners.html                          <- /dashboard/owners/
    data/
      dashboard-data.json                <- /dashboard/data/dashboard-data.json
```

### Step 3: PHP Plugin Architecture

The plugin uses three WordPress hooks:
1. **`init`** — Registers rewrite rules (`^dashboard/triage/?$` -> `index.php?dals_page=triage`)
2. **`query_vars`** — Adds `dals_page` as a recognized query variable
3. **`template_redirect`** — Intercepts the request, maps the query var to an HTML file, serves it with `readfile()` + `exit` (bypasses theme)

Also serves JSON data through its own endpoint at `/dashboard/data/dashboard-data.json`.

### Step 4: Adapt Templates
- Changed all `DATA_URL` from relative (`./data/dashboard-data.json`) to absolute (`/dashboard/data/dashboard-data.json`)
- Changed all nav links from `.html` files to WordPress routes (`/dashboard/triage/`)
- Each page is a fully self-contained HTML document (inline CSS + JS)

### Step 5: Deploy
- Committed on working branch (`claude/crazy-keller`)
- Merged to `main` and pushed
- GitHub Deployments auto-deployed to WordPress within ~15-30 seconds

### Step 6: Activate & Flush Rewrite Rules
- Went to WP Admin > Plugins > Activated the plugin
- Activation hook triggers `flush_rewrite_rules()` which registers all routes
- **Critical**: After every GitHub deploy, check if the plugin is still active (deploys can deactivate it)

---

## Issues Encountered & Fixes

### Issue 1: Plugin Not Visible After First Push
**Cause:** Deployment takes ~15-30 seconds
**Fix:** Wait, then refresh the Plugins page

### Issue 2: New Routes Return 404
**Cause:** WordPress rewrite rules not flushed after adding new route
**Fix:** Deactivate/reactivate plugin, OR save Permalinks settings (Settings > Permalinks > Save Changes)

### Issue 3: Plugin Deactivated After Subsequent Deploys
**Cause:** WordPress.com GitHub Deployments can deactivate plugins during redeploy
**Fix:** Always check plugin status after every push to main. Reactivate if needed.

### Issue 4: Pages Show WordPress Theme Instead of Custom HTML
**Cause:** Missing `exit;` after `readfile()` — WordPress continues processing and wraps in theme
**Fix:** Always call `exit;` immediately after `readfile($file);`

---

## Final Result

| Route | Page | Status |
|-------|------|--------|
| `/dashboard/` | Command Center (KPIs + nav) | Live |
| `/dashboard/triage/` | Live Triage Dashboard | Live |
| `/dashboard/replies/` | Agent Reply Hub | Live |
| `/dashboard/appointments/` | Appointments & Reschedule | Live |
| `/dashboard/owners/` | Owner Assignments | Live |

All pages auto-refresh every 30 seconds from `dashboard-data.json` which is updated by cron every 6 hours.

---

## How to Add a New Page

1. Create `templates/<new-page>.html` (full HTML document, inline CSS/JS)
2. In `deangeloslandservices-dashboard.php`:
   - Add rewrite rule: `add_rewrite_rule('^dashboard/<slug>/?$', 'index.php?dals_page=<key>', 'top');`
   - Add to `$map`: `'<key>' => '<new-page>.html'`
3. Add nav card to `command-center.html`
4. Commit, push to `main`
5. After deploy: reactivate plugin or save Permalinks to flush rewrite rules
6. Verify the route works

---

*Generated: 2026-03-08 | Session: WordPress Dashboard Deployment*
