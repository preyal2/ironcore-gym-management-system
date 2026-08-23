/**
 * IRONCORE - Appointments Controller
 */

const Appointments = {
  async loadAppointments(params = {}) {
    const tbody = document.getElementById('appointments-tbody');
    if (!tbody) return;

    try {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/appointments/list.php', params);

      if (res.success && res.data) {
        const apps = res.data.appointments || [];
        this.renderTable(apps);

        const countBadge = document.getElementById('appointments-total-count');
        if (countBadge) countBadge.textContent = apps.length;
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load appointments: ${err.message}</td></tr>`;
    }
  },

  renderTable(apps) {
    const tbody = document.getElementById('appointments-tbody');
    if (!tbody) return;

    if (apps.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No appointments found.</td></tr>`;
      return;
    }

    tbody.innerHTML = apps.map(a => `
      <tr>
        <td><strong>${formatDate(a.appointment_date)}</strong></td>
        <td>${escapeHtml(a.appointment_time)}</td>
        <td>
          <div class="table-user-cell">
            <div class="table-user-avatar">${(a.member_name || 'M').charAt(0)}</div>
            <div class="table-user-info">
              <span class="name">${escapeHtml(a.member_name || '')}</span>
              <span class="sub">${escapeHtml(a.member_code || '')}</span>
            </div>
          </div>
        </td>
        <td>${escapeHtml(a.trainer_name || '')}</td>
        <td>${escapeHtml(a.purpose)}</td>
        <td><span class="badge badge-${a.status.toLowerCase()}">${a.status}</span></td>
        <td>
          <div class="table-actions">
            ${a.status === 'Pending' ? `
              <button class="action-btn view" onclick="Appointments.approve(${a.id})" title="Confirm"><i class="fas fa-check"></i></button>
              <button class="action-btn delete" onclick="Appointments.reject(${a.id})" title="Decline"><i class="fas fa-times"></i></button>
            ` : (a.status === 'Confirmed' ? `
              <button class="action-btn delete" onclick="Appointments.cancel(${a.id})" title="Cancel"><i class="fas fa-ban"></i></button>
            ` : `<span class="text-muted">-</span>`)}
          </div>
        </td>
      </tr>
    `).join('');
  },

  async openBookModal() {
    const trainRes = await API.get('/trainers/list.php');
    const select = document.getElementById('book-trainer-select');
    if (select && trainRes.data) {
      select.innerHTML = trainRes.data.trainers.map(t => 
        `<option value="${t.trainer_id}">${escapeHtml(t.name)} - ${escapeHtml(t.specialization)}</option>`
      ).join('');
    }
    openModal('book-appointment-modal');
  },

  async submitBookAppointment(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      trainer_id: form.trainer_id.value,
      appointment_date: form.appointment_date.value,
      appointment_time: form.appointment_time.value,
      purpose: form.purpose.value.trim(),
      notes: form.notes?.value.trim() || ''
    };

    try {
      const res = await API.post('/appointments/create.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('book-appointment-modal');
        this.loadAppointments();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async approve(appId) {
    try {
      const res = await API.post('/appointments/approve.php', { id: appId });
      if (res.success) {
        showToast(res.message, 'success');
        this.loadAppointments();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async reject(appId) {
    const reason = prompt('Reason for declining this appointment:');
    if (reason === null) return;

    try {
      const res = await API.post('/appointments/reject.php', { id: appId, reason });
      if (res.success) {
        showToast(res.message, 'info');
        this.loadAppointments();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async cancel(appId) {
    if (!confirm('Are you sure you want to cancel this appointment?')) return;
    try {
      const res = await API.post('/appointments/cancel.php', { id: appId });
      if (res.success) {
        showToast(res.message, 'info');
        this.loadAppointments();
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
};
