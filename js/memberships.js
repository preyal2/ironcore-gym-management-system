/**
 * IRONCORE - Membership Plans & Subscriptions Controller
 */

const Memberships = {
  plans: [],
  memberships: [],

  async loadPlans() {
    try {
      const res = await API.get('/memberships/plans.php');
      if (res.success && res.data) {
        this.plans = res.data.plans || [];
        this.renderPlansCards(this.plans);
      }
    } catch (err) {
      showToast('Failed to load plans: ' + err.message, 'error');
    }
  },

  renderPlansCards(plans) {
    const container = document.getElementById('plans-container');
    if (!container) return;

    container.innerHTML = plans.map(p => {
      const features = (p.features || '').split(',').filter(f => f.trim() !== '');
      return `
        <div class="pricing-card ${p.name.toLowerCase().includes('pro') ? 'featured' : ''}">
          ${p.name.toLowerCase().includes('pro') ? '<div class="pricing-ribbon">Best Value</div>' : ''}
          <div>
            <h3 class="plan-name">${escapeHtml(p.name)}</h3>
            <p style="font-size:0.85rem;color:var(--text-muted);">${escapeHtml(p.description || '')}</p>
            <div class="plan-price-box">
              <span class="currency">₹</span>
              <span class="amount">${parseFloat(p.price).toLocaleString('en-IN')}</span>
              <span class="period">/ ${escapeHtml(p.duration)}</span>
            </div>
            <ul class="plan-features-list">
              ${features.map(f => `<li><i class="fas fa-check"></i> ${escapeHtml(f)}</li>`).join('')}
            </ul>
          </div>
          <button class="btn btn-primary w-100" onclick="Memberships.openAssignModal(${p.id})">
            <i class="fas fa-bolt"></i> Assign Plan
          </button>
        </div>
      `;
    }).join('');
  },

  async loadMemberships(status = '') {
    const tbody = document.getElementById('memberships-tbody');
    if (!tbody) return;

    try {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/memberships/list.php', { status });

      if (res.success && res.data) {
        this.memberships = res.data.memberships || [];
        this.renderMembershipsTable(this.memberships);
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load memberships: ${err.message}</td></tr>`;
    }
  },

  renderMembershipsTable(list) {
    const tbody = document.getElementById('memberships-tbody');
    if (!tbody) return;

    if (list.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No membership records found.</td></tr>`;
      return;
    }

    tbody.innerHTML = list.map(m => {
      const daysLeft = m.days_remaining !== null ? parseInt(m.days_remaining) : null;
      let badge = `<span class="badge badge-${m.status}">${m.status}</span>`;
      if (daysLeft !== null && daysLeft <= 14 && daysLeft >= 0 && m.status === 'active') {
        badge = `<span class="badge badge-expiring_soon">${daysLeft} Days Left</span>`;
      } else if (daysLeft !== null && daysLeft < 0) {
        badge = `<span class="badge badge-expired">Expired</span>`;
      }

      return `
        <tr>
          <td>
            <div class="table-user-cell">
              <div class="table-user-avatar">${m.member_name.charAt(0)}</div>
              <div class="table-user-info">
                <span class="name">${escapeHtml(m.member_name)}</span>
                <span class="sub">${escapeHtml(m.member_code)}</span>
              </div>
            </div>
          </td>
          <td><strong>${escapeHtml(m.plan_name)}</strong></td>
          <td>${formatCurrency(m.price)}</td>
          <td>${formatDate(m.start_date)}</td>
          <td>${formatDate(m.end_date)}</td>
          <td>${badge}</td>
          <td>
            <button class="btn btn-sm btn-primary" onclick="Memberships.openRenewModal(${m.member_id}, '${escapeHtml(m.member_name)}', ${m.plan_id})">
              <i class="fas fa-sync-alt"></i> Renew
            </button>
          </td>
        </tr>
      `;
    }).join('');
  },

  async openAssignModal(planId) {
    const memRes = await API.get('/members/list.php');
    const select = document.getElementById('assign-plan-member');
    if (select && memRes.data) {
      select.innerHTML = `<option value="">Select Member</option>` +
        memRes.data.members.map(m => `<option value="${m.member_id}">${escapeHtml(m.name)} (${escapeHtml(m.member_code)})</option>`).join('');
    }
    document.getElementById('assign-plan-id').value = planId;
    openModal('assign-plan-modal');
  },

  async submitAssignPlan(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      member_id: form.member_id.value,
      plan_id: form.plan_id.value,
      payment_method: form.payment_method.value
    };

    try {
      const res = await API.post('/memberships/add.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('assign-plan-modal');
        this.loadMemberships();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async openRenewModal(memberId, memberName, currentPlanId) {
    const planRes = await API.get('/memberships/plans.php');
    const select = document.getElementById('renew-plan-select');
    if (select && planRes.data) {
      select.innerHTML = planRes.data.plans.map(p => 
        `<option value="${p.id}" ${p.id === currentPlanId ? 'selected' : ''}>${escapeHtml(p.name)} - ${formatCurrency(p.price)} (${escapeHtml(p.duration)})</option>`
      ).join('');
    }
    document.getElementById('renew-member-id').value = memberId;
    document.getElementById('renew-member-name').textContent = memberName;
    openModal('renew-modal');
  },

  async submitRenew(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      member_id: form.member_id.value,
      plan_id: form.plan_id.value,
      payment_method: form.payment_method.value
    };

    try {
      const res = await API.post('/memberships/renew.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('renew-modal');
        this.loadMemberships();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
};
