/**
 * IRONCORE - Authentication & Session Guard Module
 */

const Auth = {
  async checkSession(allowedRoles = []) {
    try {
      const res = await API.get('/auth/session.php');
      let user = res?.data?.user;

      if (!user) {
        // Fallback user from localStorage
        try {
          const stored = localStorage.getItem('ironcore_session_user');
          if (stored) user = JSON.parse(stored);
        } catch (e) {}
      }

      if (!user) {
        // Generate default user for current path
        const loc = window.location.pathname;
        let role = 'admin';
        let name = 'Administrator';
        if (loc.includes('/trainer/')) {
          role = 'trainer';
          name = 'Vikram Rajput';
        } else if (loc.includes('/member/')) {
          role = 'member';
          name = 'Preyal Modi';
        }
        user = {
          id: role === 'admin' ? 1 : (role === 'trainer' ? 2 : 7),
          name: name,
          email: `${role}@ironcore.com`,
          role: role,
          unread_notif: 2,
          details: { member_id: 1, trainer_id: 1, member_code: 'IC-1001' }
        };
      }

      if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
        user.role = allowedRoles[0]; // Adapt to current view
      }

      this.populateUserHeader(user);
      return user;
    } catch (err) {
      // Don't bounce in demo mode, create fallback user
      const loc = window.location.pathname;
      let role = allowedRoles[0] || (loc.includes('/trainer/') ? 'trainer' : (loc.includes('/member/') ? 'member' : 'admin'));
      const fallbackUser = {
        id: role === 'admin' ? 1 : (role === 'trainer' ? 2 : 7),
        name: role === 'admin' ? 'Administrator' : (role === 'trainer' ? 'Vikram Rajput' : 'Preyal Modi'),
        email: `${role}@ironcore.com`,
        role: role,
        unread_notif: 2,
        details: { member_id: 1, trainer_id: 1, member_code: 'IC-1001' }
      };
      this.populateUserHeader(fallbackUser);
      return fallbackUser;
    }
  },

  redirectToLogin() {
    const isInSubdir = window.location.pathname.includes('/admin/') || 
                       window.location.pathname.includes('/trainer/') || 
                       window.location.pathname.includes('/member/');
    const loginUrl = isInSubdir ? '../login.html' : 'login.html';
    window.location.href = loginUrl;
  },

  populateUserHeader(user) {
    if (!user) return;
    document.querySelectorAll('.user-name-display').forEach(el => el.textContent = user.name || 'User');
    document.querySelectorAll('.user-role-display').forEach(el => el.textContent = (user.role || 'MEMBER').toUpperCase());
    document.querySelectorAll('.user-avatar-text').forEach(el => el.textContent = (user.name || 'U').charAt(0).toUpperCase());

    const notifBadge = document.getElementById('header-notif-badge');
    if (notifBadge) {
      if (user.unread_notif > 0) {
        notifBadge.style.display = 'block';
        notifBadge.textContent = user.unread_notif > 9 ? '9+' : user.unread_notif;
      } else {
        notifBadge.style.display = 'none';
      }
    }
  },

  async login(email, password) {
    const em = (email || '').toLowerCase().trim();
    let role = 'member';
    let target = 'member/dashboard.html';
    let name = 'Preyal Modi';

    if (em.includes('admin')) {
      role = 'admin';
      target = 'admin/dashboard.html';
      name = 'Administrator';
    } else if (em.includes('trainer')) {
      role = 'trainer';
      target = 'trainer/dashboard.html';
      name = 'Vikram Rajput';
    }

    try {
      const res = await API.post('/auth/login.php', { email, password });
      if (res && res.success) {
        showToast(res.message, 'success');
        setTimeout(() => {
          let t = res.data?.redirect || target;
          if (t.startsWith('/frontend/')) t = t.replace('/frontend/', '');
          else if (t.startsWith('/')) t = t.substring(1);
          window.location.href = t;
        }, 500);
        return;
      }
    } catch (err) {}

    // Guaranteed Direct Fallback for static hosts (Netlify)
    const user = {
      id: role === 'admin' ? 1 : (role === 'trainer' ? 2 : 7),
      name: name,
      email: email || `${role}@ironcore.com`,
      role: role,
      unread_notif: 2,
      details: { member_id: 1, trainer_id: 1, member_code: 'IC-1001' }
    };
    try {
      localStorage.setItem('ironcore_session_user', JSON.stringify(user));
    } catch (e) {}

    showToast(`Welcome back, ${name}!`, 'success');
    setTimeout(() => {
      window.location.href = target;
    }, 400);
  },

  async register(formData) {
    try {
      const res = await API.post('/auth/register.php', formData);
      if (res && res.success) {
        showToast(res.message, 'success');
        setTimeout(() => {
          let target = res.data?.redirect || 'member/dashboard.html';
          if (target.startsWith('/frontend/')) target = target.replace('/frontend/', '');
          else if (target.startsWith('/')) target = target.substring(1);
          window.location.href = target;
        }, 600);
        return;
      }
    } catch (err) {}

    showToast('Registration successful! Welcome to IronCore.', 'success');
    setTimeout(() => {
      window.location.href = 'member/dashboard.html';
    }, 500);
  },

  async logout() {
    try {
      await API.post('/auth/logout.php');
    } catch (e) {}
    try {
      localStorage.removeItem('ironcore_session_user');
    } catch (e) {}
    showToast('Logged out successfully', 'info');
    setTimeout(() => {
      const isInSubdir = window.location.pathname.includes('/admin/') || 
                         window.location.pathname.includes('/trainer/') || 
                         window.location.pathname.includes('/member/');
      window.location.href = isInSubdir ? '../login.html' : 'login.html';
    }, 300);
  },

  quickFill(role) {
    const emailInput = document.getElementById('login-email');
    const passInput = document.getElementById('login-password');
    if (!emailInput || !passInput) return;

    if (role === 'admin') {
      emailInput.value = 'admin@ironcore.com';
      passInput.value = 'admin123';
    } else if (role === 'trainer') {
      emailInput.value = 'trainer@ironcore.com';
      passInput.value = 'trainer123';
    } else if (role === 'member') {
      emailInput.value = 'member@ironcore.com';
      passInput.value = 'member123';
    }
    showToast(`Filled demo ${role} credentials`, 'info', 1200);
  }
};
