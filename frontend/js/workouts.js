/**
 * IRONCORE - Workout Plans & Routine Scheduler
 */

const Workouts = {
  plans: [],

  async loadPlans() {
    const container = document.getElementById('workouts-container');
    if (!container) return;

    try {
      container.innerHTML = `<div class="text-center w-100"><div class="spinner"></div></div>`;
      const res = await API.get('/workouts/list.php');

      if (res.success && res.data) {
        this.plans = res.data.plans || [];
        this.renderPlansGrid(this.plans);
      }
    } catch (err) {
      container.innerHTML = `<div class="text-center text-danger">Failed to load workout plans: ${err.message}</div>`;
    }
  },

  renderPlansGrid(plans) {
    const container = document.getElementById('workouts-container');
    if (!container) return;

    if (plans.length === 0) {
      container.innerHTML = `<div class="empty-state w-100"><h3>No workout plans found</h3></div>`;
      return;
    }

    container.innerHTML = plans.map(p => `
      <div class="glass-card">
        <div class="d-flex justify-between align-center mb-2">
          <span class="badge badge-primary">${escapeHtml(p.fitness_level)}</span>
          <span class="text-muted" style="font-size:0.8rem;"><i class="fas fa-calendar-alt"></i> ${escapeHtml(p.duration)}</span>
        </div>
        <h3 style="font-size:1.25rem;margin-bottom:6px;">${escapeHtml(p.name)}</h3>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:14px;">Coach: <strong>${escapeHtml(p.trainer_name || 'IronCore Staff')}</strong></p>
        <p style="font-size:0.9rem;line-height:1.5;margin-bottom:20px;">${escapeHtml(p.description || '')}</p>
        
        <div class="d-flex justify-between align-center" style="border-top:1px solid var(--border-color);padding-top:14px;">
          <span class="badge badge-active">${p.total_exercises} Exercises Included</span>
          <button class="btn btn-sm btn-outline" onclick="Workouts.viewPlanDetails(${p.id})">
            <i class="fas fa-eye"></i> View Routine
          </button>
        </div>
      </div>
    `).join('');
  },

  async viewPlanDetails(planId) {
    try {
      const res = await API.get('/workouts/get.php', { id: planId });
      if (res.success && res.data) {
        const d = res.data;
        document.getElementById('plan-detail-name').textContent = d.plan.name;
        document.getElementById('plan-detail-goal').textContent = d.plan.goal;
        document.getElementById('plan-detail-level').textContent = d.plan.fitness_level;
        document.getElementById('plan-detail-desc').textContent = d.plan.description || '';

        const schedContainer = document.getElementById('plan-schedule-container');
        if (schedContainer) {
          const days = Object.entries(d.schedule);
          schedContainer.innerHTML = days.map(([day, exercises]) => {
            if (exercises.length === 0) return '';
            return `
              <div class="workout-day-block">
                <div class="workout-day-header">
                  <h4><span class="day-badge">${day}</span> Routine</h4>
                  <span class="text-muted" style="font-size:0.85rem;">${exercises.length} Exercises</span>
                </div>
                <div class="workout-day-body">
                  ${exercises.map(e => `
                    <div class="workout-exercise-row">
                      <div>
                        <div class="name">${escapeHtml(e.exercise_name)}</div>
                        <span class="text-muted" style="font-size:0.8rem;">${escapeHtml(e.muscle_group)} (${escapeHtml(e.category)})</span>
                      </div>
                      <div class="specs">
                        <span><strong>${e.plan_sets}</strong> Sets × <strong>${e.plan_reps}</strong></span>
                        <span class="badge badge-info"><i class="fas fa-stopwatch"></i> ${e.plan_rest}</span>
                      </div>
                    </div>
                  `).join('')}
                </div>
              </div>
            `;
          }).join('');
        }

        openModal('workout-detail-modal');
      }
    } catch (err) {
      showToast('Error loading workout routine: ' + err.message, 'error');
    }
  },

  async markExerciseComplete(btn, workoutExerciseId) {
    try {
      const res = await API.post('/workouts/complete.php', {
        workout_exercise_id: workoutExerciseId,
        status: 'Completed'
      });
      if (res.success) {
        showToast(res.message, 'success');
        btn.classList.remove('btn-outline');
        btn.classList.add('btn-success');
        btn.innerHTML = `<i class="fas fa-check-double"></i> Done`;
        btn.disabled = true;
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
};
