/**
 * IRONCORE - Authentication & Session Guard Module
 */

const Auth = {
  async checkSession(allowedRoles = []) {
    try {
      const res = await API.get('/auth/session.php');
      if (!res.success || !res.data?.authenticated) {
        this.redirectToLogin();
        return null;
      }

      const user = res.data.user;

      if (allowedRoles.length > 0 && !allowedRoles.includes(user.role)) {
        showToast('Access denied for your user role.', 'error');
        setTimeout(() => {
          if (user.role === 'admin') window.location.href = '../admin/dashboard.html';
          else if (user.role === 'trainer') window.location.href = '../trainer/dashboard.html';
          else window.location.href = '../member/dashboard.html';
        }, 1200);
        return null;
      }

      this.populateUserHeader(user);
      return user;
    } catch (err) {
      this.redirectToLogin();
      return null;
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
    // Populate user display names and avatars across sidebar/header
    document.querySelectorAll('.user-name-display').forEach(el => el.textContent = user.name);
    document.querySelectorAll('.user-role-display').forEach(el => el.textContent = user.role.toUpperCase());
    document.querySelectorAll('.user-avatar-text').forEach(el => el.textContent = user.name.charAt(0).toUpperCase());

    // Notification Badge
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
    try {
      const res = await API.post('/auth/login.php', { email, password });
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => {
          window.location.href = res.data.redirect || 'index.html';
        }, 800);
      } else {
        showToast(res.message || 'Login failed', 'error');
      }
    } catch (err) {
      showToast(err.message || 'Login error', 'error');
    }
  },

  async register(formData) {
    try {
      const res = await API.post('/auth/register.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => {
          window.location.href = res.data.redirect || 'member/dashboard.html';
        }, 1000);
      } else {
        showToast(res.message || 'Registration failed', 'error');
      }
    } catch (err) {
      showToast(err.message || 'Registration error', 'error');
    }
  },

  async logout() {
    try {
      const res = await API.post('/auth/logout.php');
      showToast('Logged out successfully', 'info');
      setTimeout(() => {
        const isInSubdir = window.location.pathname.includes('/admin/') || 
                           window.location.pathname.includes('/trainer/') || 
                           window.location.pathname.includes('/member/');
        window.location.href = isInSubdir ? '../login.html' : 'login.html';
      }, 500);
    } catch (err) {
      window.location.href = 'login.html';
    }
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
    showToast(`Filled demo ${role} credentials`, 'info', 1500);
  }
};
