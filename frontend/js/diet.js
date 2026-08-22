/**
 * IRONCORE - Diet & Nutrition Module
 */

const Diet = {
  plans: [],

  async loadPlans() {
    const container = document.getElementById('diet-plans-container');
    if (!container) return;

    try {
      container.innerHTML = `<div class="text-center w-100"><div class="spinner"></div></div>`;
      const res = await API.get('/diet/list.php');

      if (res.success && res.data) {
        this.plans = res.data.plans || [];
        this.renderPlansGrid(this.plans);
      }
    } catch (err) {
      container.innerHTML = `<div class="text-center text-danger">Failed to load diet plans: ${err.message}</div>`;
    }
  },

  renderPlansGrid(plans) {
    const container = document.getElementById('diet-plans-container');
    if (!container) return;

    if (plans.length === 0) {
      container.innerHTML = `<div class="empty-state w-100"><h3>No diet plans found</h3></div>`;
      return;
    }

    container.innerHTML = plans.map(p => `
      <div class="glass-card">
        <div class="d-flex justify-between align-center mb-2">
          <span class="badge badge-success">${escapeHtml(p.goal)}</span>
          <span class="text-muted" style="font-size:0.85rem;"><i class="fas fa-fire"></i> ${p.target_calories} kcal/day</span>
        </div>
        <h3 style="font-size:1.25rem;margin-bottom:6px;">${escapeHtml(p.name)}</h3>
        <p style="font-size:0.85rem;color:var(--text-muted);margin-bottom:14px;">Nutritionist: <strong>${escapeHtml(p.trainer_name || 'IronCore Staff')}</strong></p>
        <p style="font-size:0.9rem;line-height:1.5;margin-bottom:20px;">${escapeHtml(p.description || '')}</p>

        <div class="d-flex justify-between align-center" style="border-top:1px solid var(--border-color);padding-top:14px;">
          <span class="badge badge-info">${p.total_meals} Meals Mapped</span>
          <button class="btn btn-sm btn-outline" onclick="Diet.viewDietPlan(${p.id})">
            <i class="fas fa-utensils"></i> View Meal Plan
          </button>
        </div>
      </div>
    `).join('');
  },

  async viewDietPlan(dietId) {
    try {
      const res = await API.get('/diet/get.php', { id: dietId });
      if (res.success && res.data) {
        const p = res.data.plan;
        const meals = res.data.meals || [];

        document.getElementById('modal-diet-name').textContent = p.name;
        document.getElementById('modal-diet-goal').textContent = p.goal;
        document.getElementById('modal-diet-calories').textContent = `${p.target_calories} kcal/day`;
        document.getElementById('modal-diet-desc').textContent = p.description || '';

        const mealsContainer = document.getElementById('modal-diet-meals-list');
        if (mealsContainer) {
          mealsContainer.innerHTML = meals.map(m => `
            <div class="meal-card">
              <div class="meal-card-title">
                <span><i class="fas fa-cookie-bite"></i> ${escapeHtml(m.meal_type)}</span>
                <span class="badge badge-primary">${m.calories} kcal</span>
              </div>
              <div class="meal-card-items">${escapeHtml(m.food_items)}</div>
              ${m.notes ? `<p class="text-muted" style="font-size:0.8rem;font-style:italic;">Note: ${escapeHtml(m.notes)}</p>` : ''}
              <div class="meal-macros">
                <span>P: <strong>${m.protein_g}g</strong></span>
                <span>C: <strong>${m.carbs_g}g</strong></span>
                <span>F: <strong>${m.fats_g}g</strong></span>
              </div>
            </div>
          `).join('');
        }

        openModal('diet-detail-modal');
      }
    } catch (err) {
      showToast('Error loading diet plan: ' + err.message, 'error');
    }
  },

  async loadMemberDietPlan(memberId = null) {
    const container = document.getElementById('member-diet-display');
    if (!container) return;

    try {
      const res = await API.get('/members/get.php', { id: memberId });
      if (res.success && res.data && res.data.diet_plan) {
        const dp = res.data.diet_plan;
        document.getElementById('member-diet-plan-title').textContent = dp.name;
        document.getElementById('member-diet-goal-badge').textContent = dp.goal;
        document.getElementById('member-diet-calories-badge').textContent = `${dp.target_calories} kcal/day`;
        document.getElementById('member-diet-desc').textContent = dp.description;

        const mealsList = document.getElementById('member-diet-meals-container');
        if (mealsList) {
          mealsList.innerHTML = dp.meals.map(m => `
            <div class="meal-card">
              <div class="meal-card-title">
                <span>${escapeHtml(m.meal_type)}</span>
                <span class="badge badge-primary">${m.calories} kcal</span>
              </div>
              <div class="meal-card-items">${escapeHtml(m.food_items)}</div>
              ${m.notes ? `<p class="text-muted" style="font-size:0.8rem;margin-top:6px;">${escapeHtml(m.notes)}</p>` : ''}
              <div class="meal-macros">
                <span>Protein: ${m.protein_g}g</span>
                <span>Carbs: ${m.carbs_g}g</span>
                <span>Fats: ${m.fats_g}g</span>
              </div>
            </div>
          `).join('');
        }
      } else {
        container.innerHTML = `
          <div class="empty-state">
            <i class="fas fa-apple-alt empty-icon"></i>
            <h3>No Custom Diet Plan Assigned</h3>
            <p>Your trainer has not assigned a diet plan yet. Request a consultation with your coach!</p>
          </div>
        `;
      }
    } catch (err) {
      container.innerHTML = `<div class="text-center text-danger">Failed to load diet plan.</div>`;
    }
  }
};
