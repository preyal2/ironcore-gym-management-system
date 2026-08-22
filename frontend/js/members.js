/**
 * IRONCORE - Member Management Module
 */

const Members = {
  allMembers: [],

  async loadMembers(filters = {}) {
    const tbody = document.getElementById('members-tbody');
    if (!tbody) return;

    try {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/members/list.php', filters);
      
      if (res.success && res.data) {
        this.allMembers = res.data.members || [];
        this.renderMembersTable(this.allMembers);
        
        const countBadge = document.getElementById('members-count-badge');
        if (countBadge) countBadge.textContent = this.allMembers.length;
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">Failed to load members: ${err.message}</td></tr>`;
    }
  },

  renderMembersTable(members) {
    const tbody = document.getElementById('members-tbody');
    if (!tbody) return;

    if (members.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" class="text-center text-muted" style="padding:40px;">No members found matching criteria.</td></tr>`;
      return;
    }

    tbody.innerHTML = members.map(m => {
      const daysLeft = m.days_remaining !== null ? parseInt(m.days_remaining) : null;
      let statusBadge = `<span class="badge badge-${m.membership_status || 'none'}">${m.membership_status || 'No Plan'}</span>`;
      if (daysLeft !== null && daysLeft <= 14 && daysLeft >= 0 && m.membership_status === 'active') {
        statusBadge = `<span class="badge badge-expiring_soon">${daysLeft} Days Left</span>`;
      } else if (daysLeft !== null && daysLeft < 0) {
        statusBadge = `<span class="badge badge-expired">Expired</span>`;
      }

      return `
        <tr>
          <td><strong>${escapeHtml(m.member_code)}</strong></td>
          <td>
            <div class="table-user-cell">
              <div class="table-user-avatar">${m.name.charAt(0)}</div>
              <div class="table-user-info">
                <span class="name">${escapeHtml(m.name)}</span>
                <span class="sub">${escapeHtml(m.email)}</span>
              </div>
            </div>
          </td>
          <td>${escapeHtml(m.phone)}</td>
          <td>${escapeHtml(m.fitness_goal)}</td>
          <td>${escapeHtml(m.trainer_name || 'Unassigned')}</td>
          <td>${escapeHtml(m.plan_name || 'None')}</td>
          <td>${statusBadge}</td>
          <td>
            <div class="table-actions">
              <a href="member-profile.html?id=${m.member_id}" class="action-btn view" title="View Profile"><i class="fas fa-eye"></i></a>
              <button class="action-btn edit" onclick="Members.openEditModal(${m.member_id})" title="Edit Member"><i class="fas fa-edit"></i></button>
              <button class="action-btn delete" onclick="Members.deleteMember(${m.member_id}, '${escapeHtml(m.name)}')" title="Delete"><i class="fas fa-trash"></i></button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
  },

  async openAddModal() {
    // Populate trainers and plans dropdowns
    const [trainRes, planRes] = await Promise.all([
      API.get('/trainers/list.php'),
      API.get('/memberships/plans.php')
    ]);

    const trainSelect = document.getElementById('add-member-trainer');
    if (trainSelect && trainRes.data) {
      trainSelect.innerHTML = `<option value="">Select Trainer (Optional)</option>` +
        trainRes.data.trainers.map(t => `<option value="${t.trainer_id}">${escapeHtml(t.name)} (${escapeHtml(t.specialization)})</option>`).join('');
    }

    const planSelect = document.getElementById('add-member-plan');
    if (planSelect && planRes.data) {
      planSelect.innerHTML = `<option value="">Select Membership Plan</option>` +
        planRes.data.plans.map(p => `<option value="${p.id}">${escapeHtml(p.name)} - ${formatCurrency(p.price)} (${escapeHtml(p.duration)})</option>`).join('');
    }

    openModal('add-member-modal');
  },

  async submitAddMember(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      name: form.name.value.trim(),
      email: form.email.value.trim(),
      phone: form.phone.value.trim(),
      gender: form.gender.value,
      date_of_birth: form.date_of_birth.value,
      address: form.address.value.trim(),
      height: form.height.value,
      weight: form.weight.value,
      fitness_goal: form.fitness_goal.value,
      fitness_level: form.fitness_level.value,
      trainer_id: form.trainer_id.value,
      plan_id: form.plan_id.value,
      payment_method: form.payment_method?.value || 'UPI'
    };

    try {
      const res = await API.post('/members/add.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('add-member-modal');
        form.reset();
        this.loadMembers();
      } else {
        showToast(res.message || 'Failed to add member', 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async openEditModal(memberId) {
    try {
      const [memRes, trainRes] = await Promise.all([
        API.get('/members/get.php', { id: memberId }),
        API.get('/trainers/list.php')
      ]);

      if (memRes.success && memRes.data) {
        const m = memRes.data.profile;
        const form = document.getElementById('edit-member-form');
        if (!form) return;

        form.member_id.value = m.id;
        form.name.value = m.name;
        form.phone.value = m.phone;
        form.gender.value = m.gender;
        form.date_of_birth.value = m.date_of_birth || '';
        form.address.value = m.address || '';
        form.height.value = m.height || '';
        form.weight.value = m.weight || '';
        form.fitness_goal.value = m.fitness_goal || 'General Fitness';
        form.fitness_level.value = m.fitness_level || 'Beginner';

        const trainSelect = form.trainer_id;
        if (trainSelect && trainRes.data) {
          trainSelect.innerHTML = `<option value="">Unassigned</option>` +
            trainRes.data.trainers.map(t => `<option value="${t.trainer_id}" ${t.trainer_id == m.trainer_id ? 'selected' : ''}>${escapeHtml(t.name)}</option>`).join('');
        }

        openModal('edit-member-modal');
      }
    } catch (err) {
      showToast('Error opening member: ' + err.message, 'error');
    }
  },

  async submitEditMember(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      member_id: form.member_id.value,
      name: form.name.value.trim(),
      phone: form.phone.value.trim(),
      gender: form.gender.value,
      date_of_birth: form.date_of_birth.value,
      address: form.address.value.trim(),
      height: form.height.value,
      weight: form.weight.value,
      fitness_goal: form.fitness_goal.value,
      fitness_level: form.fitness_level.value,
      trainer_id: form.trainer_id.value
    };

    try {
      const res = await API.post('/members/update.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('edit-member-modal');
        this.loadMembers();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async deleteMember(memberId, memberName) {
    if (!confirm(`Are you sure you want to delete ${memberName}? This will also delete their membership and payment records.`)) {
      return;
    }

    try {
      const res = await API.post('/members/delete.php', { member_id: memberId });
      if (res.success) {
        showToast(res.message, 'success');
        this.loadMembers();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  filterTable(query) {
    const q = query.toLowerCase().trim();
    const filtered = this.allMembers.filter(m => 
      m.name.toLowerCase().includes(q) ||
      m.member_code.toLowerCase().includes(q) ||
      m.phone.toLowerCase().includes(q) ||
      (m.trainer_name && m.trainer_name.toLowerCase().includes(q))
    );
    this.renderMembersTable(filtered);
  }
};
