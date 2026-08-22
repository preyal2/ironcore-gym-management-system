/**
 * IRONCORE - Centralized API Service
 * Handles fetch calls, relative path resolution, JSON parsing, error toasts
 */

const API = {
  // Automatically detects if we are running in XAMPP subdirectory or standalone
  getBaseUrl() {
    const loc = window.location.pathname;
    const marker = '/frontend/';
    const idx = loc.indexOf(marker);
    if (idx !== -1) {
      return loc.substring(0, idx) + '/backend';
    }
    return '/backend';
  },

  async request(endpoint, options = {}) {
    const url = `${this.getBaseUrl()}${endpoint.startsWith('/') ? endpoint : '/' + endpoint}`;
    
    const config = {
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        ...options.headers
      },
      ...options
    };

    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json';
      config.body = JSON.stringify(options.body);
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json().catch(() => null);

      if (!response.ok) {
        if (response.status === 401 && !window.location.pathname.includes('login.html') && !window.location.pathname.includes('register.html') && !window.location.pathname.endsWith('index.html')) {
          // Redirect to login if unauthorized
          const loginPath = window.location.pathname.includes('/admin/') || window.location.pathname.includes('/trainer/') || window.location.pathname.includes('/member/')
            ? '../login.html'
            : 'login.html';
          window.location.href = loginPath;
        }
        const errorMsg = data?.message || `HTTP Error ${response.status}: ${response.statusText}`;
        throw new Error(errorMsg);
      }

      return data;
    } catch (err) {
      console.error(`API Error [${endpoint}]:`, err);
      throw err;
    }
  },

  get(endpoint, params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, val]) => {
      if (val !== undefined && val !== null && val !== '') {
        query.append(key, val);
      }
    });
    const queryString = query.toString() ? `?${query.toString()}` : '';
    return this.request(`${endpoint}${queryString}`, { method: 'GET' });
  },

  post(endpoint, body = {}) {
    return this.request(endpoint, { method: 'POST', body });
  }
};
