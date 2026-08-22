/**
 * IRONCORE - Attendance Controller
 */

const Attendance = {
  async quickCheckIn(memberId = null) {
    try {
      const res = await API.post('/attendance/checkin.php', { member_id: memberId });
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(res.message || 'Check-in failed', 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async quickCheckOut(memberId = null) {
    try {
      const res = await API.post('/attendance/checkout.php', { member_id: memberId });
      if (res.success) {
        showToast(res.message, 'success');
        setTimeout(() => location.reload(), 1000);
      } else {
        showToast(res.message || 'Check-out failed', 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  // QR Scanner Terminal Simulation (Admin / Staff Check-in terminal)
  async scanQrCheckIn(code) {
    if (!code) return;
    try {
      const res = await API.post('/attendance/checkin.php', { member_code: code.trim() });
      if (res.success) {
        showToast(res.message, 'success');
        const input = document.getElementById('qr-terminal-input');
        if (input) input.value = '';
        this.loadTodayAttendance();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async loadTodayAttendance() {
    const tbody = document.getElementById('attendance-today-tbody');
    if (!tbody) return;

    try {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/attendance/today.php');

      if (res.success && res.data) {
        const d = res.data;
        const totalEl = document.getElementById('today-total-checkins');
        if (totalEl) totalEl.textContent = d.total_checked_in;

        const activeEl = document.getElementById('today-active-inside');
        if (activeEl) activeEl.textContent = d.active_now_in_gym;

        if (d.roster.length === 0) {
          tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted" style="padding:40px;">No check-ins recorded today.</td></tr>`;
          return;
        }

        tbody.innerHTML = d.roster.map(r => `
          <tr>
            <td><strong>${escapeHtml(r.member_code)}</strong></td>
            <td>
              <div class="table-user-cell">
                <div class="table-user-avatar">${r.member_name.charAt(0)}</div>
                <div class="table-user-info">
                  <span class="name">${escapeHtml(r.member_name)}</span>
                  <span class="sub">${escapeHtml(r.member_phone)}</span>
                </div>
              </div>
            </td>
            <td>${escapeHtml(r.plan_name || 'Standard')}</td>
            <td>${escapeHtml(r.check_in_time)}</td>
            <td>${r.check_out_time ? escapeHtml(r.check_out_time) : '<span class="badge badge-active">Inside Gym</span>'}</td>
            <td>
              ${!r.check_out_time ? `
                <button class="btn btn-sm btn-outline" onclick="Attendance.quickCheckOut(${r.member_id})">
                  <i class="fas fa-sign-out-alt"></i> Check Out
                </button>
              ` : `<span class="badge badge-completed"><i class="fas fa-check"></i> Completed</span>`}
            </td>
          </tr>
        `).join('');
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Failed to load attendance: ${err.message}</td></tr>`;
    }
  },

  async loadMemberHistory(memberId = null) {
    const tbody = document.getElementById('attendance-history-tbody');
    if (!tbody) return;

    try {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/attendance/history.php', { member_id: memberId });

      if (res.success && res.data) {
        const stats = res.data.stats;
        const totalVisitsEl = document.getElementById('member-total-visits');
        if (totalVisitsEl) totalVisitsEl.textContent = stats.total_visits || 0;

        const streakEl = document.getElementById('member-streak');
        if (streakEl) streakEl.textContent = `${stats.current_streak || 0} Days`;

        const pctEl = document.getElementById('member-attendance-pct');
        if (pctEl) pctEl.textContent = `${stats.attendance_pct || 0}%`;

        const history = res.data.history;
        if (history.length === 0) {
          tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted" style="padding:40px;">No attendance history found.</td></tr>`;
          return;
        }

        tbody.innerHTML = history.map(h => `
          <tr>
            <td><strong>${formatDate(h.attendance_date)}</strong></td>
            <td>${escapeHtml(h.check_in_time)}</td>
            <td>${h.check_out_time ? escapeHtml(h.check_out_time) : '<span class="text-muted">Not recorded</span>'}</td>
            <td><span class="badge badge-present">Present</span></td>
          </tr>
        `).join('');
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="4" class="text-center text-danger">Failed to load history: ${err.message}</td></tr>`;
    }
  }
};
