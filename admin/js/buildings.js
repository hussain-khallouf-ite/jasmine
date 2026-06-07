let buildingModal;
let buildingsData = [];

document.addEventListener('DOMContentLoaded', () => {
    buildingModal = new bootstrap.Modal(document.getElementById('buildingModal'));
    fetchBuildings();

    const form = document.getElementById('buildingForm');
    if (form) {
        form.addEventListener('submit', handleBuildingSubmit);
    }

    const imageUrlInput = document.getElementById('image_url');
    if (imageUrlInput) {
        imageUrlInput.addEventListener('input', updateImagePreview);
    }
});

function updateImagePreview() {
    const url = document.getElementById('image_url').value;
    const preview = document.getElementById('imagePreview');
    if (url && url.trim() !== '') {
        preview.src = url;
        preview.classList.remove('d-none');
    } else {
        preview.classList.add('d-none');
        preview.src = '';
    }
}

async function fetchBuildings() {
    try {
        const response = await fetch('/jasmine/public/api/admin/buildings.php?action=index');
        
        if (response.status === 401 || response.status === 403) return;

        const data = await response.json();
        const tbody = document.getElementById('buildingsTableBody');
        
        if (data.success && data.buildings) {
            buildingsData = data.buildings;
            renderBuildings(buildingsData);
        } else {
            tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${data.message || 'فشل تحميل قائمة المباني.'}</td></tr>`;
        }
    } catch (error) {
        console.error('Error fetching buildings:', error);
        document.getElementById('buildingsTableBody').innerHTML = 
            '<tr><td colspan="7" class="text-center text-danger">حدث خطأ أثناء الاتصال بالخادم.</td></tr>';
    }
}

function renderBuildings(buildings) {
    const tbody = document.getElementById('buildingsTableBody');
    tbody.innerHTML = '';
    
    if (buildings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">لا يوجد مبانٍ لعرضها.</td></tr>';
        return;
    }
    
    buildings.forEach(b => {
        const tr = document.createElement('tr');
        
        const imgHtml = b.image_url 
            ? `<img src="${escapeHtml(b.image_url)}" alt="صورة المبنى" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">`
            : '<div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 5px;"><i class="bi bi-houses"></i></div>';

        const areaText = b.total_area_m2 ? `${Number(b.total_area_m2).toLocaleString()} م²` : 'غير محددة';

        tr.innerHTML = `
            <td>#${b.id}</td>
            <td>${imgHtml}</td>
            <td class="fw-bold text-primary-custom">${escapeHtml(b.name)}</td>
            <td>${b.floors} طوابق</td>
            <td>${escapeHtml(b.location)}</td>
            <td>${areaText}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" onclick="openEditModal(${b.id})" title="تعديل"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteBuilding(${b.id})" title="حذف"><i class="bi bi-trash"></i></button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
}

function openAddModal() {
    document.getElementById('buildingForm').reset();
    document.getElementById('buildingId').value = '';
    document.getElementById('buildingModalLabel').textContent = 'إضافة مبنى جديد';
    document.getElementById('modalMessage').innerHTML = '';
    updateImagePreview();
    buildingModal.show();
}

function openEditModal(id) {
    const building = buildingsData.find(b => b.id === id);
    if (!building) return;

    document.getElementById('buildingForm').reset();
    document.getElementById('modalMessage').innerHTML = '';
    document.getElementById('buildingModalLabel').textContent = 'تعديل بيانات المبنى';
    
    document.getElementById('buildingId').value = building.id;
    document.getElementById('name').value = building.name;
    document.getElementById('description').value = building.description || '';
    document.getElementById('floors').value = building.floors;
    document.getElementById('location').value = building.location || '';
    document.getElementById('total_area_m2').value = building.total_area_m2 || '';
    document.getElementById('image_url').value = building.image_url || '';

    updateImagePreview();
    buildingModal.show();
}

async function handleBuildingSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    const id = formData.get('id');
    const action = id ? 'update' : 'store';
    
    const btn = document.getElementById('saveBuildingBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> حفظ...';
    btn.disabled = true;

    try {
        const response = await fetch(`/jasmine/public/api/admin/buildings.php?action=${action}`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            buildingModal.hide();
            showMessage(document.getElementById('buildingsMessage'), data.message, 'success');
            fetchBuildings();
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

async function deleteBuilding(id) {
    if (!confirm('هل أنت متأكد أنك تريد حذف هذا المبنى؟ سيؤدي ذلك إلى فك ارتباط جميع الشقق التابعة له (ستصبح بدون مبنى)، ولكن لن يتم حذف الشقق نفسها.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('/jasmine/public/api/admin/buildings.php?action=destroy', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        const msgDiv = document.getElementById('buildingsMessage');
        
        if (data.success) {
            showMessage(msgDiv, data.message, 'success');
            fetchBuildings();
        } else {
            showMessage(msgDiv, data.message || 'فشل في حذف المبنى.', 'danger');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showMessage(document.getElementById('buildingsMessage'), 'حدث خطأ أثناء الاتصال بالخادم.', 'danger');
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
