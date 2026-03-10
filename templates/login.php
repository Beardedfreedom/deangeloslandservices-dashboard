<?php
/**
 * Dashboard login template.
 *
 * Expected variables:
 * - $error
 * - $redirect
 * - $configured
 * - $action_url
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Access | DeAngelos Land Services</title>
<style>
  :root {
    --bg: #080906;
    --surface: rgba(18, 20, 14, 0.94);
    --surface-strong: #11140d;
    --border: rgba(245, 197, 24, 0.18);
    --text: #f3f1e7;
    --muted: #a4a18f;
    --gold: #f5c518;
    --gold-deep: #a87f07;
    --danger: #ff6b57;
    --success: #4fc67d;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    color: var(--text);
    background:
      radial-gradient(circle at top, rgba(245, 197, 24, 0.14), transparent 38%),
      linear-gradient(160deg, #10130b 0%, #060704 52%, #101008 100%);
    display: grid;
    place-items: center;
    padding: 24px;
  }
  .shell {
    width: min(100%, 960px);
    display: grid;
    grid-template-columns: 1.05fr 0.95fr;
    border: 1px solid var(--border);
    border-radius: 24px;
    overflow: hidden;
    background: rgba(7, 8, 5, 0.92);
    box-shadow: 0 40px 80px rgba(0, 0, 0, 0.45);
  }
  .brand {
    padding: 56px 48px;
    background:
      linear-gradient(180deg, rgba(245, 197, 24, 0.08), transparent 48%),
      linear-gradient(145deg, rgba(17, 20, 13, 0.98), rgba(9, 10, 7, 0.95));
    border-right: 1px solid var(--border);
  }
  .eyebrow {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    padding: 8px 12px;
    border-radius: 999px;
    background: rgba(245, 197, 24, 0.1);
    color: var(--gold);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
  }
  .eyebrow::before {
    content: "";
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--gold);
    box-shadow: 0 0 0 6px rgba(245, 197, 24, 0.12);
  }
  .brand h1 {
    margin: 24px 0 12px;
    font-size: clamp(34px, 4.5vw, 52px);
    line-height: 0.95;
    letter-spacing: -0.04em;
  }
  .brand h1 span {
    display: block;
    color: var(--gold);
  }
  .brand p {
    max-width: 38ch;
    color: var(--muted);
    font-size: 15px;
    line-height: 1.65;
  }
  .checks {
    margin-top: 32px;
    display: grid;
    gap: 12px;
  }
  .check {
    display: grid;
    grid-template-columns: 20px 1fr;
    gap: 12px;
    align-items: start;
    font-size: 14px;
    color: #d9d4bf;
  }
  .check svg {
    margin-top: 2px;
    color: var(--success);
  }
  .form-wrap {
    padding: 56px 40px;
    background: var(--surface);
  }
  .form-wrap h2 {
    margin: 0 0 8px;
    font-size: 26px;
    letter-spacing: -0.03em;
  }
  .form-wrap p {
    margin: 0 0 24px;
    color: var(--muted);
    line-height: 1.55;
  }
  .alert {
    margin-bottom: 18px;
    padding: 14px 16px;
    border-radius: 14px;
    font-size: 14px;
    line-height: 1.5;
  }
  .alert.error {
    background: rgba(255, 107, 87, 0.1);
    border: 1px solid rgba(255, 107, 87, 0.28);
    color: #ffd7d1;
  }
  .alert.info {
    background: rgba(245, 197, 24, 0.1);
    border: 1px solid rgba(245, 197, 24, 0.24);
    color: #f5e8ac;
  }
  form {
    display: grid;
    gap: 16px;
  }
  label {
    display: grid;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #d8d0b0;
  }
  input {
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: var(--surface-strong);
    color: var(--text);
    border-radius: 14px;
    padding: 16px 18px;
    font-size: 16px;
    outline: none;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
  }
  input:focus {
    border-color: rgba(245, 197, 24, 0.72);
    box-shadow: 0 0 0 4px rgba(245, 197, 24, 0.12);
    transform: translateY(-1px);
  }
  button {
    margin-top: 4px;
    border: 0;
    border-radius: 14px;
    padding: 16px 18px;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    cursor: pointer;
    color: #111;
    background: linear-gradient(135deg, #f5c518, #d8a911);
    box-shadow: 0 18px 36px rgba(216, 169, 17, 0.22);
  }
  .footnote {
    margin-top: 14px;
    font-size: 12px;
    color: var(--muted);
    line-height: 1.5;
  }
  @media (max-width: 860px) {
    .shell { grid-template-columns: 1fr; }
    .brand { border-right: 0; border-bottom: 1px solid var(--border); }
    .brand, .form-wrap { padding: 36px 24px; }
  }
</style>
</head>
<body>
  <div class="shell">
    <section class="brand">
      <div class="eyebrow">Approved Access Only</div>
      <h1>Command Center <span>Entry Screen</span></h1>
      <p>Use an approved full name and phone number combination to open the DeAngelos dashboard. This gate protects both the pages and the live JSON feed behind them.</p>
      <div class="checks">
        <div class="check">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 7L10 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <div>Exact approved full name match</div>
        </div>
        <div class="check">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 7L10 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <div>Phone number normalized automatically</div>
        </div>
        <div class="check">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 7L10 17l-5-5" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
          <div>Signed cookie session for the dashboard and its data endpoint</div>
        </div>
      </div>
    </section>

    <section class="form-wrap">
      <h2>Sign in</h2>
      <p>Enter the same name and phone number pair listed in the approved dashboard access settings.</p>

      <?php if ( ! $configured ) : ?>
        <div class="alert info">No approved users have been added yet. Go to <strong>Settings → Dashboard Access</strong> in WordPress admin first.</div>
      <?php endif; ?>

      <?php if ( $error ) : ?>
        <div class="alert error"><?php echo esc_html( $error ); ?></div>
      <?php endif; ?>

      <form method="post" action="<?php echo esc_url( $action_url ); ?>">
        <?php wp_nonce_field( 'dals_dashboard_login', 'dals_login_nonce' ); ?>
        <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>">

        <label>
          Full Name
          <input type="text" name="full_name" placeholder="Anthony Siler" autocomplete="name" required>
        </label>

        <label>
          Phone Number
          <input type="tel" name="phone_number" placeholder="(407) 833-6857" autocomplete="tel" required>
        </label>

        <button type="submit">Open Dashboard</button>
      </form>

      <div class="footnote">
        Use exact approved names. Phone formatting can vary; the system compares digits only.
      </div>
    </section>
  </div>
</body>
</html>
