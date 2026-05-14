let paymentModal;

document.addEventListener('DOMContentLoaded', () => {
    paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    fetchPayments();

    const form = document.getElementById('paymentForm');
    if (form) {
        form.addEventListener('submit', handlePaymentSubmit);
    }
});

async function fetchPayments() {
    try {
        const response = await fetch('/jasmine/public/api/admin/payments.php?action=index');
        const data = await response.json();
        
        const tbody = document.getElementById('paymentsTableBody');
        
        if (data.success && data.payments) {
            renderPayments(data.payments);
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">${data.message || 'فشل تحميل الدفعات.'}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching payments:', error);
        document.getElementById('paymentsTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center text-danger">حدث خطأ أثناء الاتصال بالخادم.</td></tr>';
    }
}

function renderPayments(payments) {
    const tbody = document.getElementById('paymentsTableBody');
    tbody.innerHTML = '';
    
    if (payments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">لا يوجد دفعات مسجلة حالياً.</td></tr>';
        return;
    }
    
    payments.forEach(p => {
        const tr = document.createElement('tr');
        
        let methodBadge = '';
        if (p.method === 'cash') methodBadge = '<span class="badge bg-success">كاش</span>';
        else if (p.method === 'transfer') methodBadge = '<span class="badge bg-info text-dark">تحويل</span>';
        else methodBadge = '<span class="badge bg-primary">بطاقة</span>';

        tr.innerHTML = `
            <td>#${p.id}</td>
            <td>
                <div class="fw-bold">${p.contract_id || 'بدون عقد'}</div>
                <small class="text-muted">${p.property_title}</small>
            </td>
            <td>${p.customer_name}</td>
            <td class="text-success fw-bold">${Number(p.amount).toLocaleString()} ل.س</td>
            <td>${methodBadge}</td>
            <td>${new Date(p.created_at).toLocaleDateString('ar-SY')}</td>
            <td>${p.admin_name || 'آلي'}</td>
            <td><small>${p.notes || '-'}</small></td>
        `;
        
        tbody.appendChild(tr);
    });
}

async function openPaymentModal() {
    document.getElementById('paymentForm').reset();
    document.getElementById('modalMessage').innerHTML = '';
    
    const select = document.getElementById('booking_id');
    select.innerHTML = '<option value="">جاري تحميل الحجوزات...</option>';
    
    try {
        const response = await fetch('/jasmine/public/api/admin/payments.php?action=pending_bookings');
        const data = await response.json();
        
        if (data.success && data.bookings) {
            select.innerHTML = '<option value="">-- اختر الحجز --</option>';
            data.bookings.forEach(b => {
                const option = document.createElement('option');
                option.value = b.id;
                option.dataset.amount = b.total_amount;
                option.textContent = `${b.contract_id} - ${b.customer_name} (${b.property_title})`;
                select.appendChild(option);
            });
            
            // Auto-fill amount when booking is selected
            select.addEventListener('change', () => {
                const selectedOption = select.options[select.selectedIndex];
                if (selectedOption && selectedOption.dataset.amount) {
                    document.getElementById('amount').value = selectedOption.dataset.amount;
                }
            });
        } else {
            select.innerHTML = '<option value="">لا توجد حجوزات معلقة.</option>';
        }
    } catch (error) {
        console.error('Error fetching pending bookings:', error);
        select.innerHTML = '<option value="">خطأ في التحميل.</option>';
    }
    
    paymentModal.show();
}

async function handlePaymentSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const btn = document.getElementById('savePaymentBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري التسجيل...';
    btn.disabled = true;

    try {
        const response = await fetch('/jasmine/public/api/admin/payments.php?action=store', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            paymentModal.hide();
            fetchPayments();
            // Show success message on the main page
            const msgDiv = document.getElementById('paymentsMessage');
            msgDiv.innerHTML = `<div class="alert alert-success alert-dismissible fade show">
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        } else {
            document.getElementById('modalMessage').innerHTML = `<div class="alert alert-danger">
                ${data.message}
            </div>`;
        }
    } catch (error) {
        console.error('Submit error:', error);
        document.getElementById('modalMessage').innerHTML = '<div class="alert alert-danger">حدث خطأ أثناء الاتصال بالخادم.</div>';
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}
