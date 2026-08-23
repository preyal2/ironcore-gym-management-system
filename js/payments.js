/**
 * IRONCORE - Payments & Receipts Module
 */

const Payments = {
  allPayments: [],

  async loadPayments(filters = {}) {
    const tbody = document.getElementById('payments-tbody');
    if (!tbody) return;

    try {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center"><div class="spinner"></div></td></tr>`;
      const res = await API.get('/payments/list.php', filters);

      if (res.success && res.data) {
        this.allPayments = res.data.payments || [];
        this.renderPaymentsTable(this.allPayments);

        const revEl = document.getElementById('total-revenue-stat');
        if (revEl) revEl.textContent = formatCurrency(res.data.total_revenue || 0);

        const compEl = document.getElementById('completed-payments-stat');
        if (compEl) compEl.textContent = res.data.completed_count || 0;

        const penEl = document.getElementById('pending-payments-stat');
        if (penEl) penEl.textContent = res.data.pending_count || 0;
      }
    } catch (err) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Failed to load payments: ${err.message}</td></tr>`;
    }
  },

  renderPaymentsTable(payments) {
    const tbody = document.getElementById('payments-tbody');
    if (!tbody) return;

    if (payments.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No payments found.</td></tr>`;
      return;
    }

    tbody.innerHTML = payments.map(p => `
      <tr>
        <td><strong>${escapeHtml(p.receipt_number)}</strong></td>
        <td>
          <div class="table-user-cell">
            <div class="table-user-avatar">${p.member_name.charAt(0)}</div>
            <div class="table-user-info">
              <span class="name">${escapeHtml(p.member_name)}</span>
              <span class="sub">${escapeHtml(p.member_code)}</span>
            </div>
          </div>
        </td>
        <td><strong>${formatCurrency(p.amount)}</strong></td>
        <td><span class="badge badge-info">${escapeHtml(p.payment_method)}</span></td>
        <td><span class="badge badge-${p.payment_status.toLowerCase()}">${p.payment_status}</span></td>
        <td>${formatDate(p.payment_date)}</td>
        <td>
          <button class="action-btn receipt" onclick="Payments.viewReceipt(${p.payment_id})" title="Print Receipt">
            <i class="fas fa-print"></i>
          </button>
        </td>
      </tr>
    `).join('');
  },

  async openRecordModal() {
    const memRes = await API.get('/members/list.php');
    const select = document.getElementById('payment-member-select');
    if (select && memRes.data) {
      select.innerHTML = `<option value="">Select Member</option>` +
        memRes.data.members.map(m => `<option value="${m.member_id}">${escapeHtml(m.name)} (${escapeHtml(m.member_code)})</option>`).join('');
    }
    openModal('record-payment-modal');
  },

  async submitRecordPayment(e) {
    e.preventDefault();
    const form = e.target;
    const formData = {
      member_id: form.member_id.value,
      amount: form.amount.value,
      payment_method: form.payment_method.value,
      payment_status: form.payment_status.value,
      notes: form.notes.value.trim()
    };

    try {
      const res = await API.post('/payments/add.php', formData);
      if (res.success) {
        showToast(res.message, 'success');
        closeModal('record-payment-modal');
        form.reset();
        this.loadPayments();
      } else {
        showToast(res.message, 'error');
      }
    } catch (err) {
      showToast(err.message, 'error');
    }
  },

  async viewReceipt(paymentId) {
    try {
      const res = await API.get('/payments/receipt.php', { id: paymentId });
      if (res.success && res.data) {
        const r = res.data.receipt;
        const g = res.data.gym;

        const receiptArea = document.getElementById('printable-receipt-area');
        if (receiptArea) {
          receiptArea.innerHTML = `
            <div class="receipt-container">
              <div class="receipt-header">
                <h2>${escapeHtml(g.name)}</h2>
                <p>${escapeHtml(g.tagline)}</p>
                <p style="font-size:0.8rem;margin-top:4px;">${escapeHtml(g.address)} | Phone: ${escapeHtml(g.phone)}</p>
              </div>

              <div class="receipt-details-grid">
                <span class="lbl">Receipt Number:</span>
                <span class="val">${escapeHtml(r.receipt_number)}</span>

                <span class="lbl">Member Name:</span>
                <span class="val">${escapeHtml(r.member_name)} (${escapeHtml(r.member_code)})</span>

                <span class="lbl">Date:</span>
                <span class="val">${formatDate(r.payment_date)}</span>

                <span class="lbl">Payment Method:</span>
                <span class="val">${escapeHtml(r.payment_method)}</span>

                <span class="lbl">Txn Reference:</span>
                <span class="val">${escapeHtml(r.transaction_reference || 'N/A')}</span>

                <span class="lbl">Item / Purpose:</span>
                <span class="val">${escapeHtml(r.item_description)}</span>

                <span class="lbl">Status:</span>
                <span class="val" style="color:#16A34A;font-weight:700;">${escapeHtml(r.payment_status)}</span>
              </div>

              <div class="receipt-amount-box">
                <span>TOTAL AMOUNT PAID</span>
                <span>${formatCurrency(r.amount)}</span>
              </div>

              <div class="receipt-footer">
                <p>This is a computer-generated receipt. Thank you for training with IronCore!</p>
              </div>
            </div>
          `;
        }

        openModal('receipt-modal');
      }
    } catch (err) {
      showToast('Error loading receipt: ' + err.message, 'error');
    }
  },

  printReceipt() {
    window.print();
  }
};
