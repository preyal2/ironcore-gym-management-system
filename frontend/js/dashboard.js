/**
 * IRONCORE - Dashboard Controller (Admin / Trainer / Member)
 */

const Dashboard = {
  // 1. ADMIN DASHBOARD
  async loadAdminDashboard() {
    try {
      const [revRes, attRes, planRes, appRes] = await Promise.all([
        API.get('/reports/revenue.php'),
        API.get('/reports/attendance.php'),
        API.get('/reports/memberships.php'),
        API.get('/appointments/list.php', { status: 'Pending' })
      ]);

      if (revRes.success && revRes.data) {
        const kpis = revRes.data.kpis;
        document.getElementById('stat-total-members').textContent = kpis.total_members;
        document.getElementById('stat-active-members').textContent = kpis.active_members;
        document.getElementById('stat-expired-members').textContent = kpis.expired_members;
        document.getElementById('stat-total-trainers').textContent = kpis.total_trainers;
        document.getElementById('stat-monthly-revenue').textContent = formatCurrency(kpis.total_revenue);
        document.getElementById('stat-today-attendance').textContent = kpis.today_attendance;

        // Render Revenue Line Chart
        Charts.renderRevenueChart('admin-revenue-chart', revRes.data.monthly_revenue || []);
      }

      if (attRes.success && attRes.data) {
        Charts.renderAttendanceChart('admin-attendance-chart', attRes.data.daily_trend || []);
      }

      if (planRes.success && planRes.data) {
        Charts.renderPlanDistributionChart('admin-plan-chart', planRes.data.plan_stats || []);
      }

      if (appRes.success && appRes.data) {
        const pendingCount = appRes.data.count || 0;
        const pendingBadge = document.getElementById('stat-pending-appointments');
        if (pendingBadge) pendingBadge.textContent = pendingCount;
      }

      // Load Today's Live Attendance Table Snippet
      const todayRes = await API.get('/attendance/today.php');
      if (todayRes.success && todayRes.data) {
        const tbody = document.getElementById('today-attendance-tbody');
        if (tbody) {
          const list = todayRes.data.roster.slice(0, 6);
          if (list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No check-ins yet today.</td></tr>`;
          } else {
            tbody.innerHTML = list.map(r => `
              <tr>
                <td>
                  <div class="table-user-cell">
                    <div class="table-user-avatar">${r.member_name.charAt(0)}</div>
                    <div class="table-user-info">
                      <span class="name">${escapeHtml(r.member_name)}</span>
                      <span class="sub">${escapeHtml(r.member_code)}</span>
                    </div>
                  </div>
                </td>
                <td>${escapeHtml(r.plan_name || 'Basic')}</td>
                <td>${escapeHtml(r.check_in_time)}</td>
                <td>${r.check_out_time ? escapeHtml(r.check_out_time) : '<span class="badge badge-active">In Gym</span>'}</td>
                <td><span class="badge badge-present">Present</span></td>
              </tr>
            `).join('');
          }
        }
      }

    } catch (err) {
      showToast('Failed to load dashboard metrics: ' + err.message, 'error');
    }
  },

  // 2. TRAINER DASHBOARD
  async loadTrainerDashboard(user) {
    try {
      const trainerId = user.details?.trainer_id;
      const [trainRes, attRes] = await Promise.all([
        API.get('/trainers/get.php', { id: trainerId }),
        API.get('/appointments/list.php', { trainer_id: trainerId })
      ]);

      if (trainRes.success && trainRes.data) {
        const d = trainRes.data;
        document.getElementById('stat-my-members').textContent = d.assigned_members?.length || 0;
        document.getElementById('stat-my-plans').textContent = d.workout_plans?.length || 0;
        
        const pendingApps = (d.appointments || []).filter(a => a.status === 'Pending').length;
        document.getElementById('stat-pending-apps').textContent = pendingApps;

        // Populate Assigned Members Snippet
        const mList = document.getElementById('trainer-members-tbody');
        if (mList) {
          const members = d.assigned_members.slice(0, 5);
          if (members.length === 0) {
            mList.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No members assigned yet.</td></tr>`;
          } else {
            mList.innerHTML = members.map(m => `
              <tr>
                <td>
                  <div class="table-user-cell">
                    <div class="table-user-avatar">${m.name.charAt(0)}</div>
                    <div class="table-user-info">
                      <span class="name">${escapeHtml(m.name)}</span>
                      <span class="sub">${escapeHtml(m.member_code)}</span>
                    </div>
                  </div>
                </td>
                <td>${escapeHtml(m.fitness_goal)}</td>
                <td><span class="badge badge-${m.membership_status || 'active'}">${m.membership_status || 'Active'}</span></td>
                <td>
                  <a href="member-profile.html?id=${m.member_id}" class="btn btn-sm btn-outline">
                    <i class="fas fa-eye"></i> View
                  </a>
                </td>
              </tr>
            `).join('');
          }
        }

        // Populate Today's Sessions / Appointments
        const appList = document.getElementById('trainer-appointments-tbody');
        if (appList) {
          const apps = (d.appointments || []).slice(0, 5);
          if (apps.length === 0) {
            appList.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No upcoming appointments.</td></tr>`;
          } else {
            appList.innerHTML = apps.map(a => `
              <tr>
                <td>${escapeHtml(a.member_name)}</td>
                <td>${formatDate(a.appointment_date)}</td>
                <td>${escapeHtml(a.appointment_time)}</td>
                <td><span class="badge badge-${a.status.toLowerCase()}">${a.status}</span></td>
                <td>
                  ${a.status === 'Pending' ? `
                    <button class="action-btn view" onclick="Appointments.approve(${a.id})" title="Approve"><i class="fas fa-check"></i></button>
                    <button class="action-btn delete" onclick="Appointments.reject(${a.id})" title="Reject"><i class="fas fa-times"></i></button>
                  ` : `<span class="text-muted">-</span>`}
                </td>
              </tr>
            `).join('');
          }
        }
      }
    } catch (err) {
      showToast('Failed to load trainer stats: ' + err.message, 'error');
    }
  },

  // 3. MEMBER DASHBOARD
  async loadMemberDashboard(user) {
    try {
      const memberId = user.details?.member_id;
      const [memRes, sumRes, workRes] = await Promise.all([
        API.get('/members/get.php', { id: memberId }),
        API.get('/progress/summary.php', { member_id: memberId }),
        API.get('/workouts/get.php', { id: 1 }) // Default active hypertrophy plan
      ]);

      if (memRes.success && memRes.data) {
        const d = memRes.data;
        const currentPlan = d.current_plan;

        // Greeting
        const greetingEl = document.getElementById('member-greeting-name');
        if (greetingEl) greetingEl.textContent = user.name;

        // Plan details & countdown
        if (currentPlan) {
          const daysLeft = currentPlan.days_left !== undefined ? Math.max(0, currentPlan.days_left) : 30;
          const daysEl = document.getElementById('countdown-days-num');
          if (daysEl) daysEl.textContent = daysLeft;
          const planNameEl = document.getElementById('member-plan-name');
          if (planNameEl) planNameEl.textContent = currentPlan.plan_name;
          const planStatusEl = document.getElementById('member-plan-status');
          if (planStatusEl) {
            planStatusEl.className = `badge badge-${currentPlan.status}`;
            planStatusEl.textContent = currentPlan.status.toUpperCase();
          }
        }

        // Assigned Trainer
        const trainerNameEl = document.getElementById('member-trainer-name');
        if (trainerNameEl) trainerNameEl.textContent = d.profile.trainer_name || 'IronCore Staff';

        // Check-in status today
        const checkinBtn = document.getElementById('quick-checkin-btn');
        if (checkinBtn && d.today_attendance) {
          checkinBtn.innerHTML = `<i class="fas fa-sign-out-alt"></i> Check Out (${d.today_attendance.check_in_time})`;
          checkinBtn.onclick = () => Attendance.quickCheckOut(memberId);
          if (d.today_attendance.check_out_time) {
            checkinBtn.innerHTML = `<i class="fas fa-check-double"></i> Completed Today (${d.today_attendance.check_out_time})`;
            checkinBtn.disabled = true;
            checkinBtn.classList.remove('btn-primary');
            checkinBtn.classList.add('btn-secondary');
          }
        }
      }

      // Progress Summary
      if (sumRes.success && sumRes.data) {
        const s = sumRes.data;
        const weightEl = document.getElementById('member-current-weight');
        if (weightEl) weightEl.textContent = (s.current_weight || 70) + ' KG';
        
        const totalWorkoutsEl = document.getElementById('member-total-workouts');
        if (totalWorkoutsEl) totalWorkoutsEl.textContent = s.total_workouts || 0;

        const streakEl = document.getElementById('member-streak-days');
        if (streakEl) streakEl.textContent = (s.fitness_streak_days || 7) + ' Days';

        // Render progress chart
        if (s.recent_logs && s.recent_logs.length > 0) {
          Charts.renderWeightProgressChart('member-weight-trend-chart', s.recent_logs);
        }
      }

      // Today's Assigned Workout Routine
      if (workRes.success && workRes.data) {
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const todayDay = days[new Date().getDay()];
        const todayExercises = workRes.data.schedule[todayDay] || workRes.data.schedule['Monday'] || [];

        const workoutContainer = document.getElementById('member-today-workout-list');
        const workoutDayHeader = document.getElementById('member-today-workout-title');
        if (workoutDayHeader) workoutDayHeader.textContent = `TODAY'S WORKOUT (${todayDay})`;

        if (workoutContainer) {
          if (todayExercises.length === 0) {
            workoutContainer.innerHTML = `
              <div class="empty-state">
                <i class="fas fa-bed empty-icon"></i>
                <h3>Active Rest Day</h3>
                <p>No heavy training scheduled today. Take a walk, stretch, and stay hydrated!</p>
              </div>
            `;
          } else {
            workoutContainer.innerHTML = todayExercises.map(e => `
              <div class="workout-exercise-row">
                <div>
                  <div class="name">${escapeHtml(e.exercise_name)}</div>
                  <span class="text-muted" style="font-size:0.8rem;">${escapeHtml(e.muscle_group)}</span>
                </div>
                <div class="specs">
                  <span><strong>${e.plan_sets}</strong> Sets × <strong>${e.plan_reps}</strong></span>
                  <span class="badge badge-info"><i class="fas fa-stopwatch"></i> ${e.plan_rest}</span>
                  <button class="btn btn-sm btn-outline" onclick="Workouts.markExerciseComplete(this, ${e.workout_exercise_id})">
                    <i class="fas fa-check"></i> Done
                  </button>
                </div>
              </div>
            `).join('');
          }
        }
      }

    } catch (err) {
      showToast('Failed to load member dashboard: ' + err.message, 'error');
    }
  }
};
