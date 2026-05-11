document.addEventListener('DOMContentLoaded', async () => {
    try {
        const authResponse = await fetch('api/auth.php?action=check');
        const authData = await authResponse.json();
        if (!authData.success) {
            window.location.href = 'login.html';
            return;
        }
    } catch (e) {
        window.location.href = 'login.html';
        return;
    }

    const loadingMessage = document.getElementById('loadingMessage');
    const errorMessage = document.getElementById('errorMessage');
    const bookingsContent = document.getElementById('bookingsContent');
    const bookingsTableBody = document.getElementById('bookingsTableBody');
    const noBookingsMsg = document.getElementById('noBookingsMsg');
    
    let currentBookingToCancel = null;
    const cancelModal = new bootstrap.Modal(document.getElementById('cancelModal'));
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
        }).format(price);
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'pending': return '<span class="badge bg-warning text-dark">بانتظار الدفع</span>';
            case 'confirmed': return '<span class="badge bg-success">مؤكد</span>';
            case 'cancelled': return '<span class="badge bg-danger">ملغي</span>';
            case 'completed': return '<span class="badge bg-secondary">مكتمل</span>';
            default: return `<span class="badge bg-light text-dark">${status}</span>`;
        }
    }

    async function loadBookings() {
        try {
            const response = await fetch('api/bookings.php?action=list');
            const data = await response.json();

            loadingMessage.classList.add('d-none');

            if (!response.ok) {
                errorMessage.textContent = data.error || 'فشل تحميل الحجوزات.';
                errorMessage.classList.remove('d-none');
                return;
            }

            bookingsContent.classList.remove('d-none');
            bookingsTableBody.innerHTML = '';

            if (data.length === 0) {
                document.querySelector('.table-responsive').classList.add('d-none');
                noBookingsMsg.classList.remove('d-none');
                return;
            }

            data.forEach(booking => {
                const tr = document.createElement('tr');
                
                let actionsHtml = '';
                if (booking.status === 'pending') {
                    actionsHtml = `
                        <a href="booking_confirm.html?id=${booking.id}" class="btn btn-sm btn-primary-custom mb-1"><i class="bi bi-credit-card"></i> دفع</a>
                        <button class="btn btn-sm btn-outline-danger mb-1" onclick="promptCancel(${booking.id})"><i class="bi bi-x-circle"></i> إلغاء</button>
                    `;
                } else if (booking.status === 'confirmed') {
                    actionsHtml = `
                        <button class="btn btn-sm btn-outline-danger mb-1" onclick="promptCancel(${booking.id})"><i class="bi bi-x-circle"></i> إلغاء</button>
                    `;
                } else {
                    actionsHtml = `<a href="property.html?id=${booking.property_id}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> عرض الشقة</a>`;
                }

                tr.innerHTML = `
                    <td>#${booking.id}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="${booking.property_image || 'images/placeholder.jpg'}" class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='data:image/svg+xml;utf8,<svg xmlns=\\\'http://www.w3.org/2000/svg\\\' width=\\\'50\\\' height=\\\'50\\\'><rect width=\\\'50\\\' height=\\\'50\\\' fill=\\\'%23f0f0f0\\\'/></svg>'">
                            <div>
                                <a href="property.html?id=${booking.property_id}" class="text-decoration-none text-dark fw-bold">${booking.property_title}</a>
                                <div class="small text-muted">${booking.property_location}</div>
                            </div>
                        </div>
                    </td>
                    <td>${booking.start_date}</td>
                    <td>${booking.end_date}</td>
                    <td class="fw-bold">${formatPrice(parseFloat(booking.total_amount))}</td>
                    <td>${getStatusBadge(booking.status)}</td>
                    <td>${actionsHtml}</td>
                `;
                bookingsTableBody.appendChild(tr);
            });

        } catch (error) {
            loadingMessage.classList.add('d-none');
            errorMessage.textContent = 'حدث خطأ في الاتصال.';
            errorMessage.classList.remove('d-none');
        }
    }

    window.promptCancel = function(bookingId) {
        currentBookingToCancel = bookingId;
        cancelModal.show();
    };

    confirmCancelBtn.addEventListener('click', async () => {
        if (!currentBookingToCancel) return;
        
        confirmCancelBtn.disabled = true;
        confirmCancelBtn.textContent = 'جاري الإلغاء...';

        try {
            const response = await fetch('api/bookings.php?action=cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ booking_id: currentBookingToCancel })
            });

            if (response.ok) {
                cancelModal.hide();
                loadBookings(); // Reload list
            } else {
                const data = await response.json();
                alert(data.error || 'فشل إلغاء الحجز.');
            }
        } catch (error) {
            alert('حدث خطأ في الاتصال.');
        } finally {
            confirmCancelBtn.disabled = false;
            confirmCancelBtn.textContent = 'نعم، ألغِ الحجز';
            currentBookingToCancel = null;
        }
    });

    loadBookings();
});
