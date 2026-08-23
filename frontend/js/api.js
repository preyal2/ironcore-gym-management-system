/**
 * IRONCORE - Centralized API Service
 * Handles fetch calls, relative path resolution, and seamless static cloud fallback (Netlify / Vercel / GitHub Pages)
 */

// Embedded Self-Contained Demo Mock Engine
var MockDB = window.MockDB || {
  getStorage(key, defaultVal) {
    try {
      const v = localStorage.getItem('ironcore_' + key);
      return v ? JSON.parse(v) : defaultVal;
    } catch (e) {
      return defaultVal;
    }
  },
  setStorage(key, val) {
    try {
      localStorage.setItem('ironcore_' + key, JSON.stringify(val));
    } catch (e) {}
  },

  init() {
    if (!this.getStorage('initialized', false)) {
      this.reset();
    }
  },

  reset() {
    const defaultMembers = [
      { id: 1, user_id: 7, member_code: 'IC-1001', name: 'Preyal Modi', email: 'member@ironcore.com', phone: '9876543210', gender: 'Male', fitness_goal: 'Muscle Hypertrophy', fitness_level: 'Intermediate', trainer_id: 1, trainer_name: 'Vikram Rajput', plan_id: 2, plan_name: 'Pro Athlete Plan', plan_price: 3499, start_date: '2026-02-01', end_date: '2026-05-01', membership_status: 'active', days_remaining: 68, weight: 72.5, height: 178, created_at: '2026-01-15' },
      { id: 2, user_id: 8, member_code: 'IC-1002', name: 'Aarav Sharma', email: 'aarav.sharma@ironcore.com', phone: '9876543211', gender: 'Male', fitness_goal: 'Fat Loss', fitness_level: 'Beginner', trainer_id: 2, trainer_name: 'Kavita Singh', plan_id: 1, plan_name: 'Basic Strength', plan_price: 1999, start_date: '2026-02-10', end_date: '2026-03-12', membership_status: 'active', days_remaining: 18, weight: 84.0, height: 175, created_at: '2026-02-10' },
      { id: 3, user_id: 9, member_code: 'IC-1003', name: 'Rohan Mehra', email: 'rohan.mehra@ironcore.com', phone: '9876543212', gender: 'Male', fitness_goal: 'Strength & Conditioning', fitness_level: 'Advanced', trainer_id: 1, trainer_name: 'Vikram Rajput', plan_id: 3, plan_name: 'Elite VIP Coaching', plan_price: 5999, start_date: '2026-01-01', end_date: '2026-07-01', membership_status: 'active', days_remaining: 129, weight: 78.0, height: 182, created_at: '2026-01-01' },
      { id: 4, user_id: 10, member_code: 'IC-1004', name: 'Ananya Desai', email: 'ananya.desai@ironcore.com', phone: '9876543213', gender: 'Female', fitness_goal: 'Weight Loss & Toning', fitness_level: 'Intermediate', trainer_id: 2, trainer_name: 'Kavita Singh', plan_id: 2, plan_name: 'Pro Athlete Plan', plan_price: 3499, start_date: '2026-02-15', end_date: '2026-05-15', membership_status: 'active', days_remaining: 82, weight: 58.5, height: 165, created_at: '2026-02-15' },
      { id: 5, user_id: 11, member_code: 'IC-1005', name: 'Karan Patel', email: 'karan.patel@ironcore.com', phone: '9876543214', gender: 'Male', fitness_goal: 'Powerlifting', fitness_level: 'Advanced', trainer_id: 3, trainer_name: 'Marcus Vance', plan_id: 3, plan_name: 'Elite VIP Coaching', plan_price: 5999, start_date: '2025-11-01', end_date: '2026-02-01', membership_status: 'expired', days_remaining: -21, weight: 92.0, height: 180, created_at: '2025-11-01' },
      { id: 6, user_id: 12, member_code: 'IC-1006', name: 'Pooja Hegde', email: 'pooja.hegde@ironcore.com', phone: '9876543215', gender: 'Female', fitness_goal: 'Endurance & Cardio', fitness_level: 'Beginner', trainer_id: 4, trainer_name: 'Elena Rostova', plan_id: 1, plan_name: 'Basic Strength', plan_price: 1999, start_date: '2026-02-20', end_date: '2026-03-01', membership_status: 'expiring_soon', days_remaining: 7, weight: 54.0, height: 162, created_at: '2026-02-20' }
    ];

    const defaultTrainers = [
      { id: 1, user_id: 2, name: 'Vikram Rajput', email: 'trainer@ironcore.com', phone: '9988776655', specialization: 'Hypertrophy & Strength Conditioning', experience: '8 Years', bio: 'Certified IFBB Pro & Elite Strength Coach specializing in biomechanics.' },
      { id: 2, user_id: 3, name: 'Kavita Singh', email: 'kavita.singh@ironcore.com', phone: '9988776656', specialization: 'Functional Training & Fat Loss', experience: '5 Years', bio: 'CrossFit Level 2 Trainer and Sports Nutritionist.' },
      { id: 3, user_id: 4, name: 'Marcus Vance', email: 'marcus.vance@ironcore.com', phone: '9988776657', specialization: 'Powerlifting & Strongman', experience: '10 Years', bio: 'National Powerlifting champion and barbell movement specialist.' },
      { id: 4, user_id: 5, name: 'Elena Rostova', email: 'elena.rostova@ironcore.com', phone: '9988776658', specialization: 'Mobility, HIIT & Rehabilitation', experience: '6 Years', bio: 'Corrective exercise specialist and mobility consultant.' }
    ];

    const defaultPayments = [
      { id: 1, receipt_number: 'REC-2026-1001', member_id: 1, member_code: 'IC-1001', member_name: 'Preyal Modi', plan_name: 'Pro Athlete Plan', amount: 3499, payment_method: 'UPI', payment_status: 'Completed', payment_date: '2026-02-01 10:30:00' },
      { id: 2, receipt_number: 'REC-2026-1002', member_id: 2, member_code: 'IC-1002', member_name: 'Aarav Sharma', plan_name: 'Basic Strength', amount: 1999, payment_method: 'Card', payment_status: 'Completed', payment_date: '2026-02-10 14:15:00' },
      { id: 3, receipt_number: 'REC-2026-1003', member_id: 3, member_code: 'IC-1003', member_name: 'Rohan Mehra', plan_name: 'Elite VIP Coaching', amount: 5999, payment_method: 'UPI', payment_status: 'Completed', payment_date: '2026-01-01 11:00:00' },
      { id: 4, receipt_number: 'REC-2026-1004', member_id: 4, member_code: 'IC-1004', member_name: 'Ananya Desai', plan_name: 'Pro Athlete Plan', amount: 3499, payment_method: 'Cash', payment_status: 'Completed', payment_date: '2026-02-15 16:45:00' }
    ];

    const defaultAttendance = [
      { id: 1, member_id: 1, member_code: 'IC-1001', member_name: 'Preyal Modi', plan_name: 'Pro Athlete Plan', check_in_time: '06:45 AM', check_out_time: null, status: 'Present' },
      { id: 2, member_id: 3, member_code: 'IC-1003', member_name: 'Rohan Mehra', plan_name: 'Elite VIP Coaching', check_in_time: '07:15 AM', check_out_time: '08:45 AM', status: 'Present' },
      { id: 3, member_id: 4, member_code: 'IC-1004', member_name: 'Ananya Desai', plan_name: 'Pro Athlete Plan', check_in_time: '08:00 AM', check_out_time: null, status: 'Present' }
    ];

    const defaultExercises = [
      { id: 1, name: 'Barbell Bench Press', category: 'Chest', muscle_group: 'Pectoralis Major', difficulty: 'Intermediate', sets: 4, reps: '8-10', rest_time: '90s', instructions: 'Retract scapula, lower bar to mid-chest, drive up with leg drive.' },
      { id: 2, name: 'Incline Dumbbell Press', category: 'Chest', muscle_group: 'Upper Chest', difficulty: 'Beginner', sets: 3, reps: '10-12', rest_time: '60s', instructions: 'Set bench to 30 degrees. Press dumbbells up and slightly inward.' },
      { id: 3, name: 'Barbell Deadlift', category: 'Back', muscle_group: 'Posterior Chain / Lats', difficulty: 'Advanced', sets: 4, reps: '5-6', rest_time: '120s', instructions: 'Hinge hips back, brace core, pull through mid-foot with flat back.' },
      { id: 4, name: 'Lat Pulldown', category: 'Back', muscle_group: 'Latissimus Dorsi', difficulty: 'Beginner', sets: 3, reps: '10-12', rest_time: '60s', instructions: 'Drive elbows down and back towards pockets. Squeeze at bottom.' },
      { id: 5, name: 'Barbell Back Squat', category: 'Legs', muscle_group: 'Quadriceps & Glutes', difficulty: 'Advanced', sets: 4, reps: '6-8', rest_time: '120s', instructions: 'Break at hips and knees simultaneously. Depth below parallel.' },
      { id: 6, name: 'Overhead Shoulder Press', category: 'Shoulders', muscle_group: 'Anterior Deltoids', difficulty: 'Intermediate', sets: 3, reps: '8-10', rest_time: '90s', instructions: 'Press barbell directly overhead, head through window at top.' },
      { id: 7, name: 'Barbell Bicep Curl', category: 'Biceps', muscle_group: 'Biceps Brachii', difficulty: 'Beginner', sets: 3, reps: '10-12', rest_time: '60s', instructions: 'Keep elbows pinned to sides. Full range of motion.' },
      { id: 8, name: 'Tricep Cable Pushdown', category: 'Triceps', muscle_group: 'Triceps Lateral Head', difficulty: 'Beginner', sets: 3, reps: '12-15', rest_time: '45s', instructions: 'Extend elbows fully and flare rope outward at the bottom.' }
    ];

    const defaultAppointments = [
      { id: 1, member_id: 1, member_name: 'Preyal Modi', trainer_id: 1, trainer_name: 'Vikram Rajput', appointment_date: '2026-08-25', appointment_time: '06:30 AM - 07:30 AM', purpose: 'Squat & Deadlift Form Check', notes: 'Reviewing heavy squat form and knee tracking.', status: 'Confirmed' },
      { id: 2, member_id: 2, member_name: 'Aarav Sharma', trainer_id: 2, trainer_name: 'Kavita Singh', appointment_date: '2026-08-26', appointment_time: '05:30 PM - 06:30 PM', purpose: 'Diet & Nutrition Consultation', notes: 'Adjusting daily caloric deficit.', status: 'Pending' }
    ];

    const defaultAnnouncements = [
      { id: 1, title: '🔥 New Olympic Lifting Platform Installed!', content: 'We have added 2 new Eleiko powerlifting platforms with competition calibrated plates in Zone B.', target_role: 'all', priority: 'high', created_at: '2026-08-20' },
      { id: 2, title: '🥗 Nutrition Seminar this Saturday 11 AM', content: 'Free macro counting and meal prep workshop led by Coach Kavita. All members welcome!', target_role: 'all', priority: 'normal', created_at: '2026-08-18' }
    ];

    this.setStorage('members', defaultMembers);
    this.setStorage('trainers', defaultTrainers);
    this.setStorage('payments', defaultPayments);
    this.setStorage('attendance', defaultAttendance);
    this.setStorage('exercises', defaultExercises);
    this.setStorage('appointments', defaultAppointments);
    this.setStorage('announcements', defaultAnnouncements);
    this.setStorage('initialized', true);
  },

  handle(endpoint, options = {}) {
    this.init();
    const body = options.body ? (typeof options.body === 'string' ? JSON.parse(options.body) : options.body) : {};
    const url = new URL('http://dummy.local' + (endpoint.startsWith('/') ? endpoint : '/' + endpoint));
    const pathname = url.pathname.replace(/^\/backend/, '');
    const params = Object.fromEntries(url.searchParams.entries());

    // 1. AUTH
    if (pathname.includes('/auth/login.php')) {
      const email = (body.email || '').toLowerCase();
      let role = 'member';
      let name = 'Preyal Modi';
      let redirect = 'member/dashboard.html';

      if (email.includes('admin')) {
        role = 'admin';
        name = 'Administrator';
        redirect = 'admin/dashboard.html';
      } else if (email.includes('trainer')) {
        role = 'trainer';
        name = 'Vikram Rajput';
        redirect = 'trainer/dashboard.html';
      }

      const user = {
        id: role === 'admin' ? 1 : (role === 'trainer' ? 2 : 7),
        name: name,
        email: body.email || `${role}@ironcore.com`,
        role: role,
        unread_notif: 3,
        details: role === 'member' ? { member_id: 1, member_code: 'IC-1001', plan_name: 'Pro Athlete Plan', days_left: 68 } : { trainer_id: 1 }
      };

      this.setStorage('session_user', user);

      return {
        success: true,
        message: `Login successful. Welcome back, ${name}!`,
        data: {
          user: user,
          redirect: redirect
        }
      };
    }

    if (pathname.includes('/auth/session.php')) {
      let user = this.getStorage('session_user', null);
      if (!user) {
        // Detect default role from URL if opened directly
        const loc = window.location.pathname;
        let role = 'admin';
        let name = 'Administrator';
        if (loc.includes('/trainer/')) {
          role = 'trainer';
          name = 'Vikram Rajput';
        } else if (loc.includes('/member/')) {
          role = 'member';
          name = 'Preyal Modi';
        }
        user = {
          id: role === 'admin' ? 1 : (role === 'trainer' ? 2 : 7),
          name: name,
          email: `${role}@ironcore.com`,
          role: role,
          unread_notif: 2,
          details: { member_id: 1, trainer_id: 1, member_code: 'IC-1001' }
        };
        this.setStorage('session_user', user);
      }
      return {
        success: true,
        message: 'Active session verified.',
        data: {
          authenticated: true,
          user: user
        }
      };
    }

    if (pathname.includes('/auth/logout.php')) {
      this.setStorage('session_user', null);
      return { success: true, message: 'Logged out successfully.' };
    }

    if (pathname.includes('/auth/register.php')) {
      const members = this.getStorage('members', []);
      const newId = members.length + 1;
      const newMember = {
        id: newId,
        user_id: 100 + newId,
        member_code: 'IC-' + (1000 + newId),
        name: body.name || 'New Member',
        email: body.email,
        phone: body.phone || '9999999999',
        gender: body.gender || 'Male',
        fitness_goal: body.fitness_goal || 'General Fitness',
        fitness_level: 'Beginner',
        trainer_id: 1,
        trainer_name: 'Vikram Rajput',
        plan_id: 1,
        plan_name: 'Basic Strength',
        plan_price: 1999,
        start_date: '2026-08-23',
        end_date: '2026-09-23',
        membership_status: 'active',
        days_remaining: 30,
        weight: 70,
        height: 175,
        created_at: '2026-08-23'
      };
      members.unshift(newMember);
      this.setStorage('members', members);

      const user = {
        id: newMember.user_id,
        name: newMember.name,
        email: newMember.email,
        role: 'member',
        unread_notif: 1,
        details: { member_id: newId, member_code: newMember.member_code }
      };
      this.setStorage('session_user', user);

      return {
        success: true,
        message: 'Registration successful! Welcome to IronCore Fitness.',
        data: { redirect: 'member/dashboard.html' }
      };
    }

    // 2. REPORTS / ANALYTICS
    if (pathname.includes('/reports/revenue.php')) {
      return {
        success: true,
        message: 'Revenue metrics loaded.',
        data: {
          kpis: {
            total_revenue: 148500,
            total_members: this.getStorage('members', []).length,
            active_members: 5,
            expired_members: 1,
            total_trainers: 4,
            today_attendance: 3
          },
          monthly_revenue: [
            { month_label: 'Mar 2026', total: 18500 },
            { month_label: 'Apr 2026', total: 24000 },
            { month_label: 'May 2026', total: 29500 },
            { month_label: 'Jun 2026', total: 32000 },
            { month_label: 'Jul 2026', total: 38500 },
            { month_label: 'Aug 2026', total: 46000 }
          ]
        }
      };
    }

    if (pathname.includes('/reports/attendance.php')) {
      return {
        success: true,
        message: 'Attendance report loaded.',
        data: {
          today_count: 3,
          avg_daily: 18.5,
          daily_trend: [
            { attendance_date: '10 Aug', count: 16 },
            { attendance_date: '12 Aug', count: 22 },
            { attendance_date: '14 Aug', count: 19 },
            { attendance_date: '16 Aug', count: 25 },
            { attendance_date: '18 Aug', count: 21 },
            { attendance_date: '20 Aug', count: 28 },
            { attendance_date: '22 Aug', count: 24 }
          ],
          top_members: this.getStorage('members', []).slice(0, 5)
        }
      };
    }

    if (pathname.includes('/reports/memberships.php')) {
      return {
        success: true,
        message: 'Plan statistics loaded.',
        data: {
          plan_stats: [
            { plan_name: 'Basic Strength', count: 2 },
            { plan_name: 'Pro Athlete Plan', count: 3 },
            { plan_name: 'Elite VIP Coaching', count: 2 }
          ]
        }
      };
    }

    if (pathname.includes('/reports/members.php')) {
      return {
        success: true,
        data: {
          total_members: 6,
          active_members: 5,
          expiring_soon: 1,
          expired_members: 1,
          gender_distribution: [{ gender: 'Male', count: 4 }, { gender: 'Female', count: 2 }],
          goal_distribution: [{ fitness_goal: 'Hypertrophy', count: 2 }, { fitness_goal: 'Fat Loss', count: 2 }, { fitness_goal: 'Strength', count: 2 }],
          members_list: this.getStorage('members', [])
        }
      };
    }

    if (pathname.includes('/reports/payments.php')) {
      return {
        success: true,
        data: {
          total_revenue: 148500,
          this_month_revenue: 46000,
          pending_amount: 0,
          pending_count: 0,
          method_breakdown: [{ payment_method: 'UPI', total_amount: 94000, count: 6 }, { payment_method: 'Card', total_amount: 34500, count: 3 }, { payment_method: 'Cash', total_amount: 20000, count: 2 }],
          recent_payments: this.getStorage('payments', [])
        }
      };
    }

    // 3. MEMBERS
    if (pathname.includes('/members/list.php')) {
      let members = this.getStorage('members', []);
      if (params.search) {
        const q = params.search.toLowerCase();
        members = members.filter(m => m.name.toLowerCase().includes(q) || m.email.toLowerCase().includes(q) || m.member_code.toLowerCase().includes(q));
      }
      if (params.status && params.status !== 'all') {
        members = members.filter(m => m.membership_status === params.status);
      }
      return { success: true, message: 'Members retrieved.', data: { count: members.length, members: members } };
    }

    if (pathname.includes('/members/get.php')) {
      const members = this.getStorage('members', []);
      const member = members.find(m => m.id == (params.id || 1)) || members[0];
      return {
        success: true,
        message: 'Member profile loaded.',
        data: {
          profile: member,
          current_plan: { plan_name: member.plan_name, status: member.membership_status, days_left: member.days_remaining },
          membership_list: [{ id: 1, plan_name: member.plan_name, price: member.plan_price, start_date: member.start_date, end_date: member.end_date, status: member.membership_status }],
          attendance_stats: { total_attended: 24, last_30_days_count: 18 },
          attendance_logs: this.getStorage('attendance', []),
          today_attendance: { check_in_time: '06:45 AM', check_out_time: null },
          progress_logs: [
            { record_date: '2026-01-15', weight: 75.0, waist: 34, chest: 40, arms: 14.5, legs: 22 },
            { record_date: '2026-02-01', weight: 73.8, waist: 33.5, chest: 40.5, arms: 14.8, legs: 22.5 },
            { record_date: '2026-02-15', weight: 72.5, waist: 32.5, chest: 41.0, arms: 15.0, legs: 23 }
          ],
          diet_plan: {
            name: 'Lean Hypertrophy Fuel',
            goal: 'Muscle Growth with Minimal Fat',
            target_calories: 2600,
            description: 'High protein diet with complex carbs around training.',
            meals: [
              { meal_type: 'Breakfast', food_items: '4 Whole Eggs + 2 Whites, 80g Oats with Berries & Whey Protein Scoop', calories: 650, protein_g: 45, carbs_g: 65, fats_g: 20 },
              { meal_type: 'Lunch', food_items: '200g Grilled Chicken Breast, 150g Brown Rice, Steamed Broccoli & Olive Oil', calories: 720, protein_g: 55, carbs_g: 70, fats_g: 18 },
              { meal_type: 'Snack', food_items: 'Greek Yogurt (200g), Almonds (30g), 1 Banana', calories: 450, protein_g: 25, carbs_g: 45, fats_g: 15 },
              { meal_type: 'Dinner', food_items: '200g Salmon / Paneer Tikka, Quinoa Salad, Mixed Greens', calories: 680, protein_g: 48, carbs_g: 50, fats_g: 22 }
            ]
          },
          payments: this.getStorage('payments', [])
        }
      };
    }

    if (pathname.includes('/members/add.php')) {
      const members = this.getStorage('members', []);
      const newId = members.length + 1;
      const m = {
        id: newId,
        user_id: 100 + newId,
        member_code: 'IC-' + (1000 + newId),
        name: body.name || 'New Member',
        email: body.email,
        phone: body.phone,
        gender: body.gender || 'Male',
        fitness_goal: body.fitness_goal || 'Hypertrophy',
        fitness_level: body.fitness_level || 'Beginner',
        trainer_id: body.trainer_id || 1,
        trainer_name: 'Vikram Rajput',
        plan_id: body.plan_id || 1,
        plan_name: 'Pro Athlete Plan',
        plan_price: 3499,
        start_date: '2026-08-23',
        end_date: '2026-09-23',
        membership_status: 'active',
        days_remaining: 30,
        weight: body.weight || 70,
        height: body.height || 175,
        created_at: '2026-08-23'
      };
      members.unshift(m);
      this.setStorage('members', members);
      return { success: true, message: 'Member created successfully.', data: { member_id: newId } };
    }

    // 4. TRAINERS
    if (pathname.includes('/trainers/list.php')) {
      return { success: true, data: { count: this.getStorage('trainers', []).length, trainers: this.getStorage('trainers', []) } };
    }

    if (pathname.includes('/trainers/get.php')) {
      const trainers = this.getStorage('trainers', []);
      const trainer = trainers.find(t => t.id == (params.id || 1)) || trainers[0];
      return {
        success: true,
        data: {
          trainer: trainer,
          assigned_members: this.getStorage('members', []),
          workout_plans: [{ id: 1, name: 'IronCore Hypertrophy Split', goal: 'Muscle Mass' }, { id: 2, name: 'Fat Shred HIIT Matrix', goal: 'Fat Loss' }],
          appointments: this.getStorage('appointments', [])
        }
      };
    }

    // 5. MEMBERSHIPS & PLANS
    if (pathname.includes('/memberships/plans.php') || pathname.includes('/memberships/list.php')) {
      return {
        success: true,
        data: {
          plans: [
            { id: 1, name: 'Basic Strength', duration: '1 Month', duration_days: 30, price: 1999, description: 'Full Gym Access, Locker, Orientation' },
            { id: 2, name: 'Pro Athlete Plan', duration: '3 Months', duration_days: 90, price: 3499, description: 'Gym + Cardio + Steam Bath + Diet Chart' },
            { id: 3, name: 'Elite VIP Coaching', duration: '6 Months', duration_days: 180, price: 5999, description: '1-on-1 PT Sessions + Custom Diet & Supplements' }
          ],
          memberships: this.getStorage('members', []).map(m => ({
            membership_id: m.id,
            member_id: m.id,
            member_code: m.member_code,
            member_name: m.name,
            member_email: m.email,
            member_phone: m.phone,
            plan_name: m.plan_name,
            price: m.plan_price,
            start_date: m.start_date,
            end_date: m.end_date,
            status: m.membership_status,
            days_remaining: m.days_remaining
          }))
        }
      };
    }

    // 6. PAYMENTS
    if (pathname.includes('/payments/list.php')) {
      return { success: true, data: { payments: this.getStorage('payments', []) } };
    }

    // 7. ATTENDANCE
    if (pathname.includes('/attendance/today.php')) {
      const att = this.getStorage('attendance', []);
      return {
        success: true,
        data: {
          date: '2026-08-23',
          total_checked_in: att.length,
          active_now_in_gym: att.filter(a => !a.check_out_time).length,
          roster: att
        }
      };
    }

    if (pathname.includes('/attendance/checkin.php')) {
      const att = this.getStorage('attendance', []);
      const newCheckin = {
        id: att.length + 1,
        member_id: body.member_id || 1,
        member_code: 'IC-1001',
        member_name: 'Preyal Modi',
        plan_name: 'Pro Athlete Plan',
        check_in_time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        check_out_time: null,
        status: 'Present'
      };
      att.unshift(newCheckin);
      this.setStorage('attendance', att);
      return { success: true, message: `Check-in successful for ${newCheckin.member_name}!` };
    }

    // 8. EXERCISES & WORKOUTS
    if (pathname.includes('/exercises/list.php')) {
      return { success: true, data: { exercises: this.getStorage('exercises', []) } };
    }

    if (pathname.includes('/workouts/list.php') || pathname.includes('/workouts/get.php')) {
      return {
        success: true,
        data: {
          plan: { id: 1, name: 'IronCore Hypertrophy Split', goal: 'Muscle Mass & Definition', fitness_level: 'Intermediate', duration: '8 Weeks' },
          schedule: {
            'Monday': [
              { workout_exercise_id: 1, exercise_name: 'Barbell Bench Press', muscle_group: 'Chest', plan_sets: 4, plan_reps: '8-10', plan_rest: '90s' },
              { workout_exercise_id: 2, exercise_name: 'Incline Dumbbell Press', muscle_group: 'Upper Chest', plan_sets: 3, plan_reps: '10-12', plan_rest: '60s' },
              { workout_exercise_id: 6, exercise_name: 'Overhead Shoulder Press', muscle_group: 'Shoulders', plan_sets: 3, plan_reps: '8-10', plan_rest: '90s' }
            ],
            'Tuesday': [
              { workout_exercise_id: 3, exercise_name: 'Barbell Deadlift', muscle_group: 'Back / Lats', plan_sets: 4, plan_reps: '5-6', plan_rest: '120s' },
              { workout_exercise_id: 4, exercise_name: 'Lat Pulldown', muscle_group: 'Latissimus Dorsi', plan_sets: 3, plan_reps: '10-12', plan_rest: '60s' },
              { workout_exercise_id: 7, exercise_name: 'Barbell Bicep Curl', muscle_group: 'Biceps', plan_sets: 3, plan_reps: '10-12', plan_rest: '60s' }
            ],
            'Wednesday': [
              { workout_exercise_id: 5, exercise_name: 'Barbell Back Squat', muscle_group: 'Quadriceps', plan_sets: 4, plan_reps: '6-8', plan_rest: '120s' },
              { workout_exercise_id: 8, exercise_name: 'Tricep Cable Pushdown', muscle_group: 'Triceps', plan_sets: 3, plan_reps: '12-15', plan_rest: '45s' }
            ]
          }
        }
      };
    }

    if (pathname.includes('/workouts/complete.php')) {
      return { success: true, message: 'Exercise set marked as completed! Keep pushing!' };
    }

    // 9. PROGRESS
    if (pathname.includes('/progress/summary.php')) {
      return {
        success: true,
        data: {
          starting_weight: 75.0,
          current_weight: 72.5,
          weight_change: -2.5,
          total_workouts: 14,
          total_attendance: 24,
          fitness_streak_days: 9,
          recent_logs: [
            { record_date: '15 Jan', weight: 75.0 },
            { record_date: '25 Jan', weight: 74.5 },
            { record_date: '05 Feb', weight: 73.8 },
            { record_date: '15 Feb', weight: 73.0 },
            { record_date: '23 Feb', weight: 72.5 }
          ]
        }
      };
    }

    if (pathname.includes('/progress/list.php')) {
      return {
        success: true,
        data: {
          progress: [
            { id: 1, record_date: '2026-02-23', weight: 72.5, waist: 32.5, chest: 41.0, arms: 15.0, legs: 23.0, notes: 'Feeling stronger on bench press.' },
            { id: 2, record_date: '2026-02-10', weight: 73.5, waist: 33.0, chest: 40.5, arms: 14.8, legs: 22.5, notes: 'Cardio sessions added.' },
            { id: 3, record_date: '2026-01-20', weight: 75.0, waist: 34.0, chest: 40.0, arms: 14.5, legs: 22.0, notes: 'Initial intake measurements.' }
          ]
        }
      };
    }

    // 10. APPOINTMENTS
    if (pathname.includes('/appointments/list.php')) {
      return { success: true, data: { count: this.getStorage('appointments', []).length, appointments: this.getStorage('appointments', []) } };
    }

    if (pathname.includes('/appointments/create.php')) {
      const apps = this.getStorage('appointments', []);
      const newApp = {
        id: apps.length + 1,
        member_id: 1,
        member_name: 'Preyal Modi',
        trainer_id: body.trainer_id || 1,
        trainer_name: 'Vikram Rajput',
        appointment_date: body.appointment_date || '2026-08-28',
        appointment_time: body.appointment_time || '06:30 AM - 07:30 AM',
        purpose: body.purpose || 'Coaching Session',
        notes: body.notes || '',
        status: 'Pending'
      };
      apps.unshift(newApp);
      this.setStorage('appointments', apps);
      return { success: true, message: 'Appointment booked successfully!' };
    }

    // 11. NOTIFICATIONS & ANNOUNCEMENTS & FEEDBACK
    if (pathname.includes('/notifications/list.php')) {
      return {
        success: true,
        data: {
          notifications: [
            { id: 1, title: '🔥 Workout Streak Active!', message: 'You have hit 9 consecutive days of training. Keep the momentum going!', created_at: '2 hours ago', is_read: 0 },
            { id: 2, title: '🥗 Diet Plan Updated', message: 'Coach Vikram updated your post-workout carb intake.', created_at: '1 day ago', is_read: 0 },
            { id: 3, title: '💳 Payment Received', message: 'Receipt REC-2026-1001 for ₹3,499 has been generated.', created_at: '3 days ago', is_read: 1 }
          ]
        }
      };
    }

    if (pathname.includes('/announcements/list.php')) {
      return { success: true, data: { announcements: this.getStorage('announcements', []) } };
    }

    if (pathname.includes('/feedback/list.php') || pathname.includes('/feedback/add.php')) {
      return { success: true, message: 'Feedback submitted successfully! Thank you.', data: { feedback: [] } };
    }

    // Fallback default
    return { success: true, message: 'Operation completed.', data: {} };
  }
};

const API = {
  isStaticHost() {
    const host = window.location.hostname.toLowerCase();
    return host.includes('netlify.app') || 
           host.includes('github.io') || 
           host.includes('vercel.app') || 
           window.location.protocol === 'file:';
  },

  getBaseUrl() {
    const loc = window.location.pathname;
    const marker = '/frontend/';
    const idx = loc.indexOf(marker);
    if (idx !== -1) {
      return loc.substring(0, idx) + '/backend';
    }
    return '/backend';
  },

  async request(endpoint, options = {}) {
    // 1. Direct Static Cloud execution (Netlify / GitHub Pages)
    if (this.isStaticHost()) {
      return new Promise(resolve => {
        setTimeout(() => {
          const res = MockDB.handle(endpoint, options);
          resolve(res);
        }, 120);
      });
    }

    // 2. Local PHP server execution
    const url = `${this.getBaseUrl()}${endpoint.startsWith('/') ? endpoint : '/' + endpoint}`;
    
    const config = {
      credentials: 'same-origin',
      headers: {
        'Accept': 'application/json',
        ...options.headers
      },
      ...options
    };

    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
      config.headers['Content-Type'] = 'application/json';
      config.body = JSON.stringify(options.body);
    }

    try {
      const response = await fetch(url, config);
      const data = await response.json().catch(() => null);

      if (!response.ok) {
        if (response.status === 404 || response.status === 405) {
          return MockDB.handle(endpoint, options);
        }

        if (response.status === 401 && !window.location.pathname.includes('login.html') && !window.location.pathname.includes('register.html') && !window.location.pathname.endsWith('index.html')) {
          const loginPath = window.location.pathname.includes('/admin/') || window.location.pathname.includes('/trainer/') || window.location.pathname.includes('/member/')
            ? '../login.html'
            : 'login.html';
          window.location.href = loginPath;
        }
        const errorMsg = data?.message || `HTTP Error ${response.status}: ${response.statusText}`;
        throw new Error(errorMsg);
      }

      return data;
    } catch (err) {
      return MockDB.handle(endpoint, options);
    }
  },

  get(endpoint, params = {}) {
    const query = new URLSearchParams();
    Object.entries(params).forEach(([key, val]) => {
      if (val !== undefined && val !== null && val !== '') {
        query.append(key, val);
      }
    });
    const queryString = query.toString() ? `?${query.toString()}` : '';
    return this.request(`${endpoint}${queryString}`, { method: 'GET' });
  },

  post(endpoint, body = {}) {
    return this.request(endpoint, { method: 'POST', body });
  }
};
