/**
 * IRONCORE - Notifications Module
 */

const Notifications = {
  async loadNotifications() {
    const container = document.getElementById('notifications-container');
    if (!container) return;

    try {
      container.innerHTML = `<div class="text-center"><div class="spinner"></div></div>`;
      const res = await API.get('/notifications/list.php');

      if (res.success && res.data) {
        const notifs = res.data.notifications || [];
        this.renderList(notifs);

        const unreadCount = res.data.unread_count || 0;
        const unreadBadge = document.getElementById('unread-notifs-badge');
        if (unreadBadge) {
          unreadBadge.textContent = unreadCount;
          unreadBadge.style.display = unreadCount > 0 ? 'inline-block' : 'none';
        }
      }
    } catch (err) {
      container.innerHTML = `<div class="text-center text-danger">Failed to load notifications: ${err.message}</div>`;
    }
  },

  renderList(notifs) {
    const container = document.getElementById('notifications-container');
    if (!container) return;

    if (notifs.length === 0) {
      container.innerHTML = `
        <div class="empty-state">
          <i class="fas fa-bell-slash empty-icon"></i>
          <h3>No Notifications</h3>
          <p>You are completely caught up! We will alert you when there are new updates.</p>
        </div>
      `;
      return;
    }

    const typeIcons = {
      workout: 'fa-dumbbell text-accent',
      diet: 'fa-apple-alt text-success',
      appointment: 'fa-calendar-check text-info',
      membership: 'fa-id-card text-warning',
      payment: 'fa-receipt text-success',
      general: 'fa-info-circle text-primary'
    };

    container.innerHTML = notifs.map(n => `
      <div class="glass-card mb-3 ${!n.is_read ? 'glow-border' : ''}" style="padding:18px 24px;">
        <div class="d-flex justify-between align-center mb-2">
          <div class="d-flex align-center gap-2">
            <i class="fas ${typeIcons[n.type] || 'fa-bell'}"></i>
            <h4 style="font-size:1.05rem;">${escapeHtml(n.title)}</h4>
          </div>
          <span class="text-muted" style="font-size:0.78rem;">${formatDate(n.created_at)}</span>
        </div>
        <p style="font-size:0.9rem;line-height:1.5;margin-bottom:10px;">${escapeHtml(n.message)}</p>
        <div class="d-flex justify-between align-center">
          <span class="badge badge-info">${escapeHtml(n.type)}</span>
          ${!n.is_read ? `
            <button class="btn btn-sm btn-outline" onclick="Notifications.markAsRead(${n.id})">
              <i class="fas fa-check"></i> Mark as Read
            </button>
          ` : `<span class="text-muted" style="font-size:0.75rem;"><i class="fas fa-check-double"></i> Read</span>`}
        </div>
      </div>
    `).join('');
  },

  async markAsRead(notifId) {
    try {
      const res = await API.post('/notifications/read.php', { id: notifId });
      if (res.success) {
        this.loadNotifications();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async markAllAsRead() {
    try {
      const res = await API.post('/notifications/read.php', { all: true });
      if (res.success) {
        showToast('All notifications marked as read', 'success');
        this.loadNotifications();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
};
