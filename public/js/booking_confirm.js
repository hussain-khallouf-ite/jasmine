document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const bookingId = urlParams.get('id');

    if (!bookingId) {
        window.location.href = 'properties.html';
        return;
    }

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
    const confirmContent = document.getElementById('confirmContent');
    const paymentAlert = document.getElementById('paymentAlert');
    const payBtn = document.getElementById('payBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
        }).format(price);
    }

    async function loadBooking() {
        try {
            const response = await fetch(`api/bookings.php?action=details&id=${bookingId}`);
            const data = await response.json();

            if (!response.ok || !data) {
                errorMessage.textContent = data.error || 'لم يتم العثور على الحجز.';
                errorMessage.classList.remove('d-none');
                loadingMessage.classList.add('d-none');
                return;
            }

            if (data.status !== 'pending') {
                errorMessage.textContent = 'هذا الحجز ليس في حالة انتظار الدفع. قد يكون مؤكداً أو ملغياً.';
                errorMessage.classList.remove('d-none');
                loadingMessage.classList.add('d-none');
                return;
            }

            document.getElementById('cPropTitle').textContent = data.property_title;
            document.getElementById('cType').textContent = data.type === 'sale' ? 'شراء' : 'إيجار';
            document.getElementById('cContractId').textContent = data.contract_id || '-';
            
            if (data.type === 'rent') {
                document.getElementById('cStartDate').textContent = data.start_date;
                document.getElementById('cContractType').textContent = data.contract_type === 'annual' ? 'سنوي' : 'شهري';
                document.getElementById('cOccupants').textContent = data.occupants;
            } else {
                document.getElementById('cRentFields1').classList.add('d-none');
                document.getElementById('cRentFields2').classList.add('d-none');
                document.getElementById('cOccupantsRow').classList.add('d-none');
            }
            
            document.getElementById('cTotal').textContent = formatPrice(parseFloat(data.total_amount));

            loadingMessage.classList.add('d-none');
            confirmContent.classList.remove('d-none');

        } catch (error) {
            errorMessage.textContent = 'حدث خطأ في الاتصال.';
            errorMessage.classList.remove('d-none');
            loadingMessage.classList.add('d-none');
        }
    }

    loadBooking();

    payBtn.addEventListener('click', async () => {
        payBtn.disabled = true;
        payBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> جاري معالجة الدفع...';
        
        try {
            const response = await fetch('api/bookings.php?action=confirm', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ booking_id: bookingId })
            });

            const data = await response.json();

            if (!response.ok) {
                paymentAlert.textContent = data.error || 'فشلت عملية الدفع.';
                paymentAlert.className = 'alert alert-danger mb-4';
                payBtn.disabled = false;
                payBtn.innerHTML = '<i class="bi bi-credit-card"></i> إتمام الدفع الآن';
            } else {
                paymentAlert.textContent = 'تم الدفع وتأكيد الحجز بنجاح! سيتم توجيهك إلى حجوزاتك.';
                paymentAlert.className = 'alert alert-success mb-4';
                cancelBtn.style.display = 'none';
                
                setTimeout(() => {
                    window.location.href = 'my_bookings.html';
                }, 3000);
            }

        } catch (error) {
            paymentAlert.textContent = 'حدث خطأ في الاتصال بالخادم.';
            paymentAlert.className = 'alert alert-danger mb-4';
            payBtn.disabled = false;
            payBtn.innerHTML = '<i class="bi bi-credit-card"></i> إتمام الدفع الآن';
        }
    });

    cancelBtn.addEventListener('click', async () => {
        if (!confirm('هل أنت متأكد من رغبتك في إلغاء هذا الحجز؟')) return;

        cancelBtn.disabled = true;
        
        try {
            const response = await fetch('api/bookings.php?action=cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ booking_id: bookingId })
            });

            if (response.ok) {
                window.location.href = 'my_bookings.html';
            } else {
                alert('فشل إلغاء الحجز.');
                cancelBtn.disabled = false;
            }
        } catch (error) {
            alert('حدث خطأ.');
            cancelBtn.disabled = false;
        }
    });
});
