let propertyModal;
let propertiesData = [];

document.addEventListener('DOMContentLoaded', () => {
    propertyModal = new bootstrap.Modal(document.getElementById('propertyModal'));
    fetchProperties();

    const form = document.getElementById('propertyForm');
    if (form) {
        form.addEventListener('submit', handlePropertySubmit);
    }
});

async function fetchProperties() {
    try {
        const response = await fetch('/jasmine/public/api/admin/properties.php?action=index');
        
        if (response.status === 401 || response.status === 403) return;

        const data = await response.json();
        const tbody = document.getElementById('propertiesTableBody');
        
        if (data.success && data.properties) {
            propertiesData = data.properties;
            renderProperties(propertiesData);
        } else {
            tbody.innerHTML = `<tr><td colspan="8" class="text-center text-danger">${data.message || 'فشل تحميل الشقق.'}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching properties:', error);
        document.getElementById('propertiesTableBody').innerHTML = 
            '<tr><td colspan="8" class="text-center text-danger">حدث خطأ أثناء الاتصال بالخادم.</td></tr>';
    }
}

function renderProperties(properties) {
    const tbody = document.getElementById('propertiesTableBody');
    tbody.innerHTML = '';
    
    if (properties.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">لا يوجد شقق لعرضها.</td></tr>';
        return;
    }
    
    properties.forEach(prop => {
        const tr = document.createElement('tr');
        
        let statusBadge = '';
        if (prop.status === 'available') statusBadge = '<span class="badge bg-success">متاحة</span>';
        else if (prop.status === 'reserved') statusBadge = '<span class="badge bg-warning text-dark">محجوزة</span>';
        else statusBadge = '<span class="badge bg-danger">غير متاحة</span>';
            
        const typeText = prop.type === 'residential' ? 'سكنية' : 'تجارية';
        
        const imgHtml = prop.image_url 
            ? `<img src="${escapeHtml(prop.image_url)}" alt="صورة الشقة" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">`
            : '<div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 5px;"><i class="bi bi-image"></i></div>';

        tr.innerHTML = `
            <td>#${prop.id}</td>
            <td>${imgHtml}</td>
            <td class="fw-bold">${escapeHtml(prop.title)}</td>
            <td>${typeText}</td>
            <td dir="ltr" class="text-end">${prop.size_m2} م² / ${prop.rooms} غرف</td>
            <td class="text-success fw-bold">${Number(prop.price_per_month).toLocaleString()} ل.س</td>
            <td>${statusBadge}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal(${prop.id})" title="تعديل"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteProperty(${prop.id})" title="حذف"><i class="bi bi-trash"></i></button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

function openAddModal() {
    document.getElementById('propertyForm').reset();
    document.getElementById('propertyId').value = '';
    document.getElementById('propertyModalLabel').textContent = 'إضافة شقة جديدة';
    document.getElementById('modalMessage').innerHTML = '';
    propertyModal.show();
}

function openEditModal(id) {
    const property = propertiesData.find(p => p.id === id);
    if (!property) return;

    document.getElementById('propertyForm').reset();
    document.getElementById('modalMessage').innerHTML = '';
    document.getElementById('propertyModalLabel').textContent = 'تعديل الشقة';
    
    document.getElementById('propertyId').value = property.id;
    document.getElementById('title').value = property.title;
    document.getElementById('description').value = property.description || '';
    document.getElementById('location').value = property.location || '';
    document.getElementById('image_url').value = property.image_url || '';
    document.getElementById('type').value = property.type;
    document.getElementById('rooms').value = property.rooms;
    document.getElementById('size_m2').value = property.size_m2;
    document.getElementById('floor').value = property.floor;
    document.getElementById('price_per_month').value = property.price_per_month;
    document.getElementById('status').value = property.status;
    
    // Convert amenities array to comma-separated string
    let amenitiesStr = '';
    if (Array.isArray(property.amenities)) {
        amenitiesStr = property.amenities.join(', ');
    } else if (property.amenities) {
        amenitiesStr = property.amenities;
    }
    document.getElementById('amenities').value = amenitiesStr;

    propertyModal.show();
}

async function handlePropertySubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const id = formData.get('id');
    const action = id ? 'update' : 'store';
    
    const btn = document.getElementById('savePropertyBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> حفظ...';
    btn.disabled = true;

    try {
        const response = await fetch(`/jasmine/public/api/admin/properties.php?action=${action}`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            propertyModal.hide();
            showMessage(document.getElementById('propertiesMessage'), data.message, 'success');
            fetchProperties();
        } else {
            let errorMsg = data.message || 'حدث خطأ أثناء الحفظ.';
            if (data.errors) {
                errorMsg += '<ul class="mb-0 mt-2 text-start">';
                for (const err of Object.values(data.errors)) {
                    errorMsg += `<li>${err}</li>`;
                }
                errorMsg += '</ul>';
            }
            showMessage(document.getElementById('modalMessage'), errorMsg, 'danger');
        }
    } catch (error) {
        console.error('Submit error:', error);
        showMessage(document.getElementById('modalMessage'), 'حدث خطأ أثناء الاتصال بالخادم.', 'danger');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function deleteProperty(id) {
    if (!confirm('هل أنت متأكد أنك تريد حذف هذه الشقة بشكل نهائي؟ لا يمكن التراجع عن هذا الإجراء.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('/jasmine/public/api/admin/properties.php?action=destroy', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const msgDiv = document.getElementById('propertiesMessage');
        
        if (data.success) {
            showMessage(msgDiv, data.message, 'success');
            fetchProperties();
        } else {
            showMessage(msgDiv, data.message || 'فشل في حذف الشقة.', 'danger');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showMessage(document.getElementById('propertiesMessage'), 'حدث خطأ أثناء الاتصال بالخادم.', 'danger');
    }
}

function showMessage(element, message, type) {
    element.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
    </div>`;
    
    if (element.id !== 'modalMessage') {
        setTimeout(() => {
            element.innerHTML = '';
        }, 5000);
    }
}

function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return String(unsafe)
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
