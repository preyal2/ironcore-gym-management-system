/**
 * IRONCORE - Exercise Library Controller
 */

const Exercises = {
  allExercises: [],
  currentCategory: 'All',

  async loadExercises(params = {}) {
    const container = document.getElementById('exercises-grid');
    if (!container) return;

    try {
      container.innerHTML = `<div class="text-center w-100"><div class="spinner"></div></div>`;
      const res = await API.get('/exercises/list.php', params);

      if (res.success && res.data) {
        this.allExercises = res.data.exercises || [];
        this.renderExercisesGrid(this.allExercises);

        const countBadge = document.getElementById('exercises-total-count');
        if (countBadge) countBadge.textContent = this.allExercises.length;
      }
    } catch (err) {
      container.innerHTML = `<div class="text-center text-danger">Failed to load exercises: ${err.message}</div>`;
    }
  },

  renderExercisesGrid(exercises) {
    const container = document.getElementById('exercises-grid');
    if (!container) return;

    if (exercises.length === 0) {
      container.innerHTML = `
        <div class="empty-state w-100" style="grid-column: 1 / -1;">
          <i class="fas fa-dumbbell empty-icon"></i>
          <h3>No exercises found</h3>
          <p>Try searching for a different muscle group or clear filters.</p>
        </div>
      `;
      return;
    }

    container.innerHTML = exercises.map(e => `
      <div class="exercise-card">
        <div class="exercise-card-header">
          <span class="exercise-category-tag">${escapeHtml(e.category)}</span>
          <span class="badge badge-info">${escapeHtml(e.difficulty)}</span>
        </div>
        <div class="exercise-card-body">
          <h4 class="exercise-card-title">${escapeHtml(e.name)}</h4>
          <p class="exercise-target"><i class="fas fa-bullseye"></i> ${escapeHtml(e.muscle_group)}</p>
          
          <div class="exercise-metrics-pills">
            <div class="metric-pill-item">
              <span class="lbl">Sets</span>
              <span class="val">${e.sets}</span>
            </div>
            <div class="metric-pill-item">
              <span class="lbl">Reps</span>
              <span class="val">${escapeHtml(e.reps)}</span>
            </div>
            <div class="metric-pill-item">
              <span class="lbl">Rest</span>
              <span class="val">${escapeHtml(e.rest_time)}</span>
            </div>
          </div>
        </div>
        <div class="exercise-card-footer">
          <button class="btn btn-sm btn-outline w-100" onclick="Exercises.viewExercise(${e.id})">
            <i class="fas fa-info-circle"></i> Instructions
          </button>
        </div>
      </div>
    `).join('');
  },

  async viewExercise(exerciseId) {
    try {
      const res = await API.get('/exercises/get.php', { id: exerciseId });
      if (res.success && res.data) {
        const e = res.data.exercise;
        document.getElementById('modal-ex-title').textContent = e.name;
        document.getElementById('modal-ex-category').textContent = e.category;
        document.getElementById('modal-ex-muscle').textContent = e.muscle_group;
        document.getElementById('modal-ex-difficulty').textContent = e.difficulty;
        document.getElementById('modal-ex-sets').textContent = `${e.sets} Sets`;
        document.getElementById('modal-ex-reps').textContent = `${e.reps} Reps`;
        document.getElementById('modal-ex-rest').textContent = e.rest_time;
        document.getElementById('modal-ex-instructions').textContent = e.instructions || 'Perform with controlled tempo, proper breathing, and full range of motion.';

        openModal('exercise-info-modal');
      }
    } catch (err) {
      showToast('Error loading exercise: ' + err.message, 'error');
    }
  },

  setCategory(category, btn) {
    this.currentCategory = category;
    document.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('btn-primary'));
    btn.classList.add('btn-primary');
    
    if (category === 'All') {
      this.renderExercisesGrid(this.allExercises);
    } else {
      const filtered = this.allExercises.filter(e => e.category === category);
      this.renderExercisesGrid(filtered);
    }
  },

  filterSearch(query) {
    const q = query.toLowerCase().trim();
    const filtered = this.allExercises.filter(e => 
      (this.currentCategory === 'All' || e.category === this.currentCategory) &&
      (e.name.toLowerCase().includes(q) || e.muscle_group.toLowerCase().includes(q) || e.instructions.toLowerCase().includes(q))
    );
    this.renderExercisesGrid(filtered);
  },

  openAddModal() {
    const form = document.getElementById('add-exercise-form');
    if (form) form.reset();
    openModal('add-exercise-modal');
  },

  async submitAddExercise(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      name: form.name.value.trim(),
      category: form.category.value,
      muscle_group: form.muscle_group.value.trim(),
      difficulty: form.difficulty.value,
      sets: form.sets.value,
      reps: form.reps.value.trim(),
      rest_time: form.rest_time.value.trim(),
      instructions: form.instructions.value.trim()
    };

    try {
      const res = await API.post('/exercises/add.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('add-exercise-modal');
        this.loadExercises();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  }
};
