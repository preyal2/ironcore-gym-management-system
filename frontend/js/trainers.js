/**
 * IRONCORE - Trainer Management Module
 */

const Trainers = {
  allTrainers: [],

  async loadTrainers() {
    const tbody = document.getElementById('trainers-tbody');
    if (!tbody) return;

    try {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/trainers/list.php');

      if (res.success && res.data) {
        this.allTrainers = res.data.trainers || [];
        this.renderTrainersTable(this.allTrainers);
        
        const countBadge = document.getElementById('trainers-count-badge');
        if (countBadge) countBadge.textContent = this.allTrainers.length;
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load trainers: ${err.message}</td></tr>`;
    }
  },

  renderTrainersTable(trainers) {
    const tbody = document.getElementById('trainers-tbody');
    if (!tbody) return;

    if (trainers.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No trainers found.</td></tr>`;
      return;
    }

    tbody.innerHTML = trainers.map(t => `
      <tr>
        <td>
          <div class="table-user-cell">
            <div class="table-user-avatar">${t.name.charAt(0)}</div>
            <div class="table-user-info">
              <span class="name">${escapeHtml(t.name)}</span>
              <span class="sub">${escapeHtml(t.email)}</span>
            </div>
          </div>
        </td>
        <td>${escapeHtml(t.phone)}</td>
        <td><strong>${escapeHtml(t.specialization)}</strong></td>
        <td>${escapeHtml(t.experience)}</td>
        <td><span class="badge badge-info">${t.assigned_members_count} Members</span></td>
        <td><span class="badge badge-active">${t.workout_plans_count} Plans</span></td>
        <td>
          <div class="table-actions">
            <button class="action-btn edit" onclick="Trainers.openEditModal(${t.trainer_id})" title="Edit Trainer"><i class="fas fa-edit"></i></button>
            <button class="action-btn delete" onclick="Trainers.deleteTrainer(${t.trainer_id}, '${escapeHtml(t.name)}')" title="Delete"><i class="fas fa-trash"></i></button>
          </div>
        </td>
      </tr>
    `).join('');
  },

  openAddModal() {
    const form = document.getElementById('add-trainer-form');
    if (form) form.reset();
    openModal('add-trainer-modal');
  },

  async submitAddTrainer(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      name: form.name.value.trim(),
      email: form.email.value.trim(),
      phone: form.phone.value.trim(),
      specialization: form.specialization.value.trim(),
      experience: form.experience.value.trim(),
      bio: form.bio.value.trim(),
      password: form.password?.value.trim() || 'trainer123'
    };

    try {
      const res = await API.post('/trainers/add.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('add-trainer-modal');
        form.reset();
        this.loadTrainers();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async openEditModal(trainerId) {
    try {
      const res = await API.get('/trainers/get.php', { id: trainerId });
      if (res.success && res.data) {
        const t = res.data.trainer;
        const form = document.getElementById('edit-trainer-form');
        if (!form) return;

        form.trainer_id.value = t.trainer_id;
        form.name.value = t.name;
        form.phone.value = t.phone;
        form.specialization.value = t.specialization;
        form.experience.value = t.experience;
        form.bio.value = t.bio || '';

        openModal('edit-trainer-modal');
      }
    } catch (err) {
      showToast('Error loading trainer: ' + err.message, 'error');
    }
  },

  async submitEditTrainer(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      trainer_id: form.trainer_id.value,
      name: form.name.value.trim(),
      phone: form.phone.value.trim(),
      specialization: form.specialization.value.trim(),
      experience: form.experience.value.trim(),
      bio: form.bio.value.trim()
    };

    try {
      const res = await API.post('/trainers/update.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('edit-trainer-modal');
        this.loadTrainers();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async deleteTrainer(trainerId, name) {
    if (!confirm(`Are you sure you want to remove trainer ${name}?`)) return;

    try {
      const res = await API.post('/trainers/delete.php', { trainer_id: trainerId });
      if (res.success) {
        showToast(res.message, 'success');
        this.loadTrainers();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
};
