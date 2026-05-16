document.addEventListener('DOMContentLoaded', () => {
    fetchBookings();
});

async function fetchBookings() {
    try {
        const response = await fetch('/jasmine/public/api/admin/bookings.php?action=index');
        const data = await response.json();
        
        const tbody = document.getElementById('bookingsTableBody');
        
        if (data.success && data.bookings) {
            renderBookings(data.bookings);
        } else {
            tbody.innerHTML = `<tr><td colspan="9" class="text-center text-danger">${data.message || 'فشل تحميل الحجوزات.'}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching bookings:', error);
        document.getElementById('bookingsTableBody').innerHTML = 
            '<tr><td colspan="9" class="text-center text-danger">حدث خطأ أثناء الاتصال بالخادم.</td></tr>';
    }
}

function renderBookings(bookings) {
    const tbody = document.getElementById('bookingsTableBody');
    tbody.innerHTML = '';
    
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">لا توجد حجوزات حالياً.</td></tr>';
        return;
    }
    
    bookings.forEach(b => {
        const tr = document.createElement('tr');
        
        const statusBadge = getStatusBadge(b.status);
        const typeBadge = b.type === 'rent' ? '<span class="badge bg-info text-dark">إيجار</span>' : '<span class="badge bg-primary">بيع</span>';

        tr.innerHTML = `
            <td>#${b.id}</td>
            <td class="fw-bold text-primary">${b.contract_id || '-'}</td>
            <td>${b.property_title}</td>
            <td>
                <div>${b.customer_name}</div>
                <small class="text-muted">${b.customer_email}</small>
            </td>
            <td>${typeBadge}</td>
            <td class="fw-bold">${Number(b.total_amount).toLocaleString()} ل.س</td>
            <td>${statusBadge}</td>
            <td>${new Date(b.created_at).toLocaleDateString('ar-SY')}</td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        تعديل الحالة
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="updateBookingStatus(${b.id}, 'confirmed')">تأكيد</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateBookingStatus(${b.id}, 'completed')">مكتمل</a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateBookingStatus(${b.id}, 'cancelled')">إلغاء</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="deleteBooking(${b.id})">حذف</a></li>
                    </ul>
                </div>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

function getStatusBadge(status) {
    switch (status) {
        case 'pending': return '<span class="badge bg-warning text-dark">قيد الانتظار</span>';
        case 'confirmed': return '<span class="badge bg-success">مؤكد</span>';
        case 'completed': return '<span class="badge bg-primary">مكتمل</span>';
        case 'cancelled': return '<span class="badge bg-danger">ملغي</span>';
        default: return `<span class="badge bg-secondary">${status}</span>`;
    }
}

async function updateBookingStatus(id, status) {
    if (!confirm('هل أنت متأكد من تغيير حالة هذا الحجز؟')) return;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('status', status);

    try {
        const response = await fetch('/jasmine/public/api/admin/bookings.php?action=update_status', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            fetchBookings();
            showGlobalMessage(data.message, 'success');
        } else {
            showGlobalMessage(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error updating status:', error);
    }
}

async function deleteBooking(id) {
    if (!confirm('هل أنت متأكد من حذف هذا الحجز نهائياً؟')) return;

    const formData = new FormData();
    formData.append('id', id);

    try {
        const response = await fetch('/jasmine/public/api/admin/bookings.php?action=delete', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            fetchBookings();
            showGlobalMessage(data.message, 'success');
        } else {
            showGlobalMessage(data.message, 'danger');
        }
    } catch (error) {
        console.error('Error deleting booking:', error);
    }
}

function showGlobalMessage(message, type) {
    const msgDiv = document.getElementById('bookingsMessage');
    msgDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;
    
    setTimeout(() => {
        msgDiv.innerHTML = '';
    }, 5000);
}
