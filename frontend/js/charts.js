/**
 * IRONCORE - Chart.js Visualizations Setup
 */

const Charts = {
  // Line Chart: Revenue Trend (Admin)
  renderRevenueChart(canvasId, monthlyData) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = monthlyData.map(d => d.month_label);
    const amounts = monthlyData.map(d => d.total);

    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Revenue (₹)',
          data: amounts,
          borderColor: '#FF3B30',
          backgroundColor: 'rgba(255, 59, 48, 0.12)',
          fill: true,
          tension: 0.35,
          pointBackgroundColor: '#FF3B30',
          pointBorderColor: '#FFFFFF',
          pointBorderWidth: 2,
          pointRadius: 5,
          pointHoverRadius: 7
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => ' ₹' + Number(context.parsed.y).toLocaleString('en-IN')
            }
          }
        },
        scales: {
          x: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: '#888' }
          },
          y: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: {
              color: '#888',
              callback: (val) => '₹' + Number(val).toLocaleString('en-IN')
            }
          }
        }
      }
    });
  },

  // Bar Chart: Attendance Trend (Admin & Trainer)
  renderAttendanceChart(canvasId, trendData) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = trendData.map(d => {
      const parts = d.attendance_date.split('-');
      return `${parts[2]}/${parts[1]}`;
    });
    const counts = trendData.map(d => d.count);

    return new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Daily Check-ins',
          data: counts,
          backgroundColor: '#FF6B35',
          borderRadius: 6,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { color: '#888' }
          },
          y: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: '#888', precision: 0 }
          }
        }
      }
    });
  },

  // Doughnut Chart: Membership Plans Distribution (Admin)
  renderPlanDistributionChart(canvasId, planStats) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = planStats.map(p => p.plan_name);
    const data = planStats.map(p => p.active_subscribers);
    const colors = ['#FF3B30', '#FF6B35', '#F59E0B', '#3B82F6', '#22C55E'];

    return new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: data,
          backgroundColor: colors.slice(0, labels.length),
          borderColor: '#141414',
          borderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: '#CCC', boxWidth: 12, padding: 16 }
          }
        },
        cutout: '70%'
      }
    });
  },

  // Line Chart: Member Weight Progress Trend (Member & Trainer)
  renderWeightProgressChart(canvasId, logs) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;

    const labels = logs.map(l => formatDate(l.record_date));
    const weights = logs.map(l => parseFloat(l.weight));

    return new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Weight (kg)',
          data: weights,
          borderColor: '#22C55E',
          backgroundColor: 'rgba(34, 197, 94, 0.15)',
          fill: true,
          tension: 0.3,
          pointBackgroundColor: '#22C55E',
          pointRadius: 5
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (context) => ` ${context.parsed.y} kg`
            }
          }
        },
        scales: {
          x: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: { color: '#888' }
          },
          y: {
            grid: { color: 'rgba(255, 255, 255, 0.05)' },
            ticks: {
              color: '#888',
              callback: (val) => `${val} kg`
            }
          }
        }
      }
    });
  }
};
