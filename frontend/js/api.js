/**
 * IRONCORE - Centralized API Service
 * Handles fetch calls, relative path resolution, and smart static cloud fallback (Netlify / GitHub Pages)
 */

const API = {
  isStaticHost() {
    const host = window.location.hostname.toLowerCase();
    return host.includes('netlify.app') || 
           host.includes('github.io') || 
           host.includes('vercel.app') || 
           window.location.protocol === 'file:';
  },

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
    // 1. If running on a static host (Netlify), use the client-side mock engine directly
    if (this.isStaticHost() && typeof MockDB !== 'undefined') {
      return new Promise(resolve => {
        setTimeout(() => {
          const res = MockDB.handle(endpoint, options);
          resolve(res);
        }, 150);
      });
    }

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
        // If 404/405 occurs (e.g. static server fallback), use MockDB
        if ((response.status === 404 || response.status === 405) && typeof MockDB !== 'undefined') {
          console.warn(`[IRONCORE] Backend not found (${response.status}), falling back to Demo Mock Engine.`);
          return MockDB.handle(endpoint, options);
        }

        if (response.status === 401 && !window.location.pathname.includes('login.html') && !window.location.pathname.includes('register.html') && !window.location.pathname.endsWith('index.html')) {
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
      // Fallback for network failures on static deployments
      if (typeof MockDB !== 'undefined') {
        console.warn(`[IRONCORE] Fetch failed, falling back to Demo Mock Engine for ${endpoint}`);
        return MockDB.handle(endpoint, options);
      }
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
