document.addEventListener('DOMContentLoaded', () => {
    // We assume auth.js handles the initial auth check and redirect if not admin
    // So if we reach here and are still on the page, auth check is likely in progress or succeeded.
    fetchUsers();
});

async function fetchUsers() {
    try {
        const response = await fetch('/jasmine/public/api/admin/users.php?action=index', {
            method: 'GET'
        });
        
        if (response.status === 401 || response.status === 403) {
            // Unauthenticated or not admin, auth.js should handle redirect but just in case
            return;
        }

        const data = await response.json();
        const tbody = document.getElementById('usersTableBody');
        
        if (data.success && data.users) {
            renderUsers(data.users);
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">${data.message || 'فشل تحميل المستخدمين.'}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching users:', error);
        document.getElementById('usersTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center text-danger">حدث خطأ أثناء الاتصال بالخادم.</td></tr>';
    }
}

function renderUsers(users) {
    const tbody = document.getElementById('usersTableBody');
    tbody.innerHTML = '';
    
    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">لا يوجد مستخدمين لعرضهم.</td></tr>';
        return;
    }
    
    users.forEach(user => {
        const tr = document.createElement('tr');
        
        const statusBadge = user.status === 'active' 
            ? '<span class="badge bg-success">نشط</span>' 
            : '<span class="badge bg-danger">غير نشط</span>';
            
        const roleText = user.role === 'admin' ? 'مدير' : 'عميل';
        const date = new Date(user.created_at).toLocaleDateString('ar-SY');
        
        // Button to toggle status
        let actionBtn = '';
        if (user.role !== 'admin') {
            if (user.status === 'active') {
                actionBtn = `<button class="btn btn-sm btn-outline-danger" onclick="toggleUserStatus(${user.id}, 'inactive')">حظر</button>`;
            } else {
                actionBtn = `<button class="btn btn-sm btn-outline-success" onclick="toggleUserStatus(${user.id}, 'active')">تنشيط</button>`;
            }
        } else {
            actionBtn = '<span class="text-muted small">لا يمكن التعديل</span>';
        }

        tr.innerHTML = `
            <td>#${user.id}</td>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td dir="ltr" class="text-end">${escapeHtml(user.phone || '-')}</td>
            <td>${date}</td>
            <td>${roleText}</td>
            <td>${statusBadge}</td>
            <td>${actionBtn}</td>
        `;
        
        tbody.appendChild(tr);
    });
}

async function toggleUserStatus(userId, newStatus) {
    if (!confirm(`هل أنت متأكد أنك تريد ${newStatus === 'active' ? 'تنشيط' : 'حظر'} هذا المستخدم؟`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', userId);
    formData.append('status', newStatus);
    
    try {
        const response = await fetch('/jasmine/public/api/admin/users.php?action=updateStatus', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const msgDiv = document.getElementById('usersMessage');
        
        if (data.success) {
            showMessage(msgDiv, data.message, 'success');
            fetchUsers(); // Refresh the list
        } else {
            showMessage(msgDiv, data.message || 'فشل في تحديث حالة المستخدم.', 'danger');
        }
    } catch (error) {
        console.error('Error toggling status:', error);
        showMessage(document.getElementById('usersMessage'), 'حدث خطأ أثناء الاتصال بالخادم.', 'danger');
    }
}

function showMessage(element, message, type) {
    element.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    </div>`;
    
    setTimeout(() => {
        element.innerHTML = '';
    }, 5000);
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
