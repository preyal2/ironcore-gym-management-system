/**
 * IRONCORE - Fitness Progress & Measurements Module
 */

const Progress = {
  async loadMemberProgress(memberId = null) {
    try {
      const [sumRes, logsRes] = await Promise.all([
        API.get('/progress/summary.php', { member_id: memberId }),
        API.get('/progress/list.php', { member_id: memberId })
      ]);

      if (sumRes.success && sumRes.data) {
        const s = sumRes.data;
        const curEl = document.getElementById('prog-current-weight');
        if (curEl) curEl.textContent = `${s.current_weight || '--'} kg`;

        const startEl = document.getElementById('prog-start-weight');
        if (startEl) startEl.textContent = `${s.starting_weight || '--'} kg`;

        const diffEl = document.getElementById('prog-weight-diff');
        if (diffEl) {
          const diff = s.weight_change || 0;
          diffEl.textContent = `${diff >= 0 ? '+' : ''}${diff} kg`;
          diffEl.className = diff <= 0 ? 'text-success' : 'text-primary';
        }

        const streakEl = document.getElementById('prog-streak-val');
        if (streakEl) streakEl.textContent = `${s.fitness_streak_days || 0} Days`;
      }

      if (logsRes.success && logsRes.data) {
        const logs = logsRes.data.logs || [];
        
        // Render Chart
        Charts.renderWeightProgressChart('progress-weight-chart', logs);

        // Render Table History
        const tbody = document.getElementById('progress-history-tbody');
        if (tbody) {
          if (logs.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted" style="padding:30px;">No body measurement entries logged yet.</td></tr>`;
          } else {
            tbody.innerHTML = logs.slice().reverse().map(l => `
              <tr>
                <td><strong>${formatDate(l.record_date)}</strong></td>
                <td><strong>${l.weight} kg</strong></td>
                <td>${l.waist ? l.waist + ' in' : '-'}</td>
                <td>${l.chest ? l.chest + ' in' : '-'}</td>
                <td>${l.arms ? l.arms + ' in' : '-'}</td>
                <td>${l.legs ? l.legs + ' in' : '-'}</td>
                <td style="font-size:0.85rem;color:var(--text-muted);">${escapeHtml(l.notes || '')}</td>
              </tr>
            `).join('');
          }
        }
      }

    } catch (err) {
      showToast('Error loading progress logs: ' + err.message, 'error');
    }
  },

  openLogModal() {
    const form = document.getElementById('log-progress-form');
    if (form) form.reset();
    openModal('log-progress-modal');
  },

  async submitLogProgress(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      weight: form.weight.value,
      waist: form.waist.value,
      chest: form.chest.value,
      arms: form.arms.value,
      legs: form.legs.value,
      notes: form.notes.value.trim(),
      record_date: form.record_date.value || undefined
    };

    try {
      const res = await API.post('/progress/add.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('log-progress-modal');
        this.loadMemberProgress();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
};
