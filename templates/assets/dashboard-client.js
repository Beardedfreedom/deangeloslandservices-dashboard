(function () {
  const DEFAULT_DATA_URL = '/dashboard/data/dashboard-data.json/';
  const DEFAULT_LOGIN_URL = '/dashboard/login/';
  const DEFAULT_INTERVAL_MS = 30000;
  const DEFAULT_STALE_AFTER_MS = 40 * 60 * 1000;

  function parseDate(value) {
    if (!value) return null;
    const date = value instanceof Date ? value : new Date(value);
    return Number.isNaN(date.getTime()) ? null : date;
  }

  function formatClock(value) {
    const date = parseDate(value);
    return date ? date.toLocaleTimeString() : 'unknown';
  }

  function formatAge(value) {
    const date = parseDate(value);
    if (!date) return 'unknown';

    const diffMs = Math.max(0, Date.now() - date.getTime());
    if (diffMs < 30000) return 'just now';

    const minutes = Math.round(diffMs / 60000);
    if (minutes < 60) return `${minutes}m`;

    const hours = Math.round(minutes / 60);
    if (hours < 24) return `${hours}h`;

    const days = Math.round(hours / 24);
    return `${days}d`;
  }

  function formatInterval(intervalMs) {
    const ms = Number(intervalMs) || DEFAULT_INTERVAL_MS;
    const seconds = Math.round(ms / 1000);
    if (seconds < 60) return `${seconds}s`;

    const minutes = Math.round(seconds / 60);
    if (minutes < 60) return `${minutes}m`;

    const hours = Math.round(minutes / 60);
    return `${hours}h`;
  }

  function buildRefreshStatus(meta, fetchedAt, intervalMs) {
    const generated = parseDate(meta && meta.generated);
    const parts = [];

    if (generated) {
      const freshness = Date.now() - generated.getTime() <= DEFAULT_STALE_AFTER_MS ? 'Fresh' : 'Stale';
      parts.push(`${freshness} backend sync ${formatClock(generated)} (${formatAge(generated)} old)`);
    } else {
      parts.push('Backend sync unavailable');
    }

    if (fetchedAt) {
      parts.push(`browser check ${formatClock(fetchedAt)}`);
    }

    parts.push(`checks every ${formatInterval(intervalMs)}`);
    return parts.join(' | ');
  }

  function buildErrorStatus(error, lastSuccessAt, intervalMs) {
    if (error && error.code === 'AUTH') {
      return 'Session expired. Redirecting to login...';
    }

    const parts = ['Fetch failed'];
    if (lastSuccessAt) {
      parts.push(`last browser check ${formatClock(lastSuccessAt)}`);
    }
    parts.push(`retrying every ${formatInterval(intervalMs)}`);
    return parts.join(' | ');
  }

  function redirectToLogin(redirectTo) {
    const redirectTarget = redirectTo || window.location.href;
    const url = new URL(DEFAULT_LOGIN_URL, window.location.origin);
    url.searchParams.set('redirect', redirectTarget);
    window.location.assign(url.toString());
  }

  async function fetchDashboardData(options = {}) {
    const url = options.url || DEFAULT_DATA_URL;
    const response = await fetch(url, {
      cache: 'no-store',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Cache-Control': 'no-cache',
      },
      signal: options.signal,
    });

    if (response.status === 401 || response.status === 403) {
      const error = new Error('Authentication required.');
      error.code = 'AUTH';
      error.status = response.status;
      throw error;
    }

    if (!response.ok) {
      const error = new Error(`Dashboard fetch failed (${response.status}).`);
      error.code = 'HTTP';
      error.status = response.status;
      throw error;
    }

    const contentType = response.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      const error = new Error('Dashboard response was not JSON.');
      error.code = 'NON_JSON';
      throw error;
    }

    return {
      data: await response.json(),
      fetchedAt: new Date(),
    };
  }

  function createPoller(options = {}) {
    const intervalMs = Number(options.intervalMs || options.interval) || DEFAULT_INTERVAL_MS;
    let active = true;
    let timerId = null;
    let inFlightController = null;
    let inFlightPromise = null;
    let lastData = null;
    let lastSuccessAt = null;

    function schedule(delayMs = intervalMs) {
      window.clearTimeout(timerId);
      timerId = window.setTimeout(() => {
        void run('poll');
      }, delayMs);
    }

    async function run(reason) {
      if (!active) return lastData;
      if (inFlightPromise) return inFlightPromise;

      inFlightController = new AbortController();
      inFlightPromise = fetchDashboardData({
        url: options.url || DEFAULT_DATA_URL,
        signal: inFlightController.signal,
      })
        .then(({ data, fetchedAt }) => {
          lastData = data;
          lastSuccessAt = fetchedAt;
          if (typeof options.onData === 'function') {
            options.onData({ data, fetchedAt, lastSuccessAt, reason });
          }
          return data;
        })
        .catch((error) => {
          if (error && error.name === 'AbortError') {
            return lastData;
          }

          if (typeof options.onError === 'function') {
            options.onError({ error, lastData, lastSuccessAt, reason });
          }

          if (error && error.code === 'AUTH') {
            redirectToLogin(options.redirectTo || window.location.href);
          }

          return lastData;
        })
        .finally(() => {
          inFlightPromise = null;
          inFlightController = null;
          if (active && !document.hidden) {
            schedule();
          }
        });

      return inFlightPromise;
    }

    function start() {
      void run('initial');
    }

    function refresh() {
      return run('manual');
    }

    function stop() {
      active = false;
      window.clearTimeout(timerId);
      if (inFlightController) {
        inFlightController.abort();
      }
    }

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        window.clearTimeout(timerId);
        if (inFlightController) {
          inFlightController.abort();
        }
        return;
      }

      if (active) {
        void run('visible');
      }
    });

    window.addEventListener('beforeunload', stop, { once: true });

    return {
      start,
      refresh,
      stop,
      getLastData: () => lastData,
      getLastSuccessAt: () => lastSuccessAt,
    };
  }

  window.DalsDashboard = {
    buildErrorStatus,
    buildRefreshStatus,
    createPoller,
    DEFAULT_DATA_URL,
    DEFAULT_INTERVAL_MS,
    fetchDashboardData,
    formatInterval,
  };
})();
