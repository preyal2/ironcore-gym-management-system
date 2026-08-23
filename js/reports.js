/**
 * IRONCORE - Reports & Analytics Controller
 */

const Reports = {
  currentReportData: [],
  currentReportType: 'members',

  async loadMembersReport() {
    this.currentReportType = 'members';
    const tbody = document.getElementById('reports-tbody');
    const thead = document.getElementById('reports-thead');
    if (!tbody || !thead) return;

    try {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/reports/members.php');

      if (res.success && res.data) {
        const d = res.data;
        document.getElementById('rep-total-members').textContent = d.total_members;
        document.getElementById('rep-active-members').textContent = d.active_members;
        document.getElementById('rep-expiring-soon').textContent = d.expiring_soon;
        document.getElementById('rep-expired-members').textContent = d.expired_members;

        this.currentReportData = d.members_list || [];

        thead.innerHTML = `
          <tr>
            <th>Member ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Active Plan</th>
            <th>Joined Date</th>
          </tr>
        `;

        tbody.innerHTML = this.currentReportData.map(m => `
          <tr>
            <td><strong>${escapeHtml(m.member_code)}</strong></td>
            <td>${escapeHtml(m.name)}</td>
            <td>${escapeHtml(m.email)}</td>
            <td>${escapeHtml(m.phone)}</td>
            <td><span class="badge badge-${m.membership_status || 'active'}">${escapeHtml(m.plan_name || 'Basic')}</span></td>
            <td>${formatDate(m.created_at)}</td>
          </tr>
        `).join('');
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Failed: ${err.message}</td></tr>`;
    }
  },

  async loadPaymentsReport() {
    this.currentReportType = 'payments';
    const tbody = document.getElementById('reports-tbody');
    const thead = document.getElementById('reports-thead');
    if (!tbody || !thead) return;

    try {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/reports/payments.php');

      if (res.success && res.data) {
        const d = res.data;
        this.currentReportData = d.recent_payments || [];

        thead.innerHTML = `
          <tr>
            <th>Receipt #</th>
            <th>Member</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        `;

        tbody.innerHTML = this.currentReportData.map(p => `
          <tr>
            <td><strong>${escapeHtml(p.receipt_number)}</strong></td>
            <td>${escapeHtml(p.member_name)} (${escapeHtml(p.member_code)})</td>
            <td><strong>${formatCurrency(p.amount)}</strong></td>
            <td><span class="badge badge-info">${escapeHtml(p.payment_method)}</span></td>
            <td><span class="badge badge-${p.payment_status.toLowerCase()}">${p.payment_status}</span></td>
            <td>${formatDate(p.payment_date)}</td>
          </tr>
        `).join('');
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Failed: ${err.message}</td></tr>`;
    }
  },

  async loadAttendanceReport() {
    this.currentReportType = 'attendance';
    const tbody = document.getElementById('reports-tbody');
    const thead = document.getElementById('reports-thead');
    if (!tbody || !thead) return;

    try {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/reports/attendance.php');

      if (res.success && res.data) {
        this.currentReportData = res.data.top_members || [];

        thead.innerHTML = `
          <tr>
            <th>Member Code</th>
            <th>Member Name</th>
            <th>Phone</th>
            <th>Total Gym Visits</th>
            <th>Status</th>
          </tr>
        `;

        tbody.innerHTML = this.currentReportData.map(m => `
          <tr>
            <td><strong>${escapeHtml(m.member_code)}</strong></td>
            <td>${escapeHtml(m.name)}</td>
            <td>${escapeHtml(m.phone)}</td>
            <td><span class="badge badge-success">${m.total_visits} Sessions</span></td>
            <td><span class="badge badge-present">Consistent</span></td>
          </tr>
        `).join('');
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">Failed: ${err.message}</td></tr>`;
    }
  },

  exportCSV() {
    if (!this.currentReportData || this.currentReportData.length === 0) {
      showToast('No data available to export', 'warning');
      return;
    }

    const headers = Object.keys(this.currentReportData[0]);
    let csv = headers.join(',') + '\n';

    this.currentReportData.forEach(row => {
      const values = headers.map(h => {
        let val = row[h] !== null && row[h] !== undefined ? String(row[h]) : '';
        val = val.replace(/"/g, '""');
        return `"${val}"`;
      });
      csv += values.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `ironcore_${this.currentReportType}_report_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV export downloaded', 'success');
  },

  printReport() {
    window.print();
  }
};
