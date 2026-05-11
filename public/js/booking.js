document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const propertyId = urlParams.get('id');

    if (!propertyId) {
        window.location.href = 'properties.html';
        return;
    }

    try {
        const authResponse = await fetch('api/auth.php?action=check');
        const authData = await authResponse.json();
        if (!authData.success) {
            window.location.href = `login.html`;
            return;
        }
    } catch (e) {
        window.location.href = `login.html`;
        return;
    }

    const loadingMessage = document.getElementById('loadingMessage');
    const errorMessage = document.getElementById('errorMessage');
    const bookingContent = document.getElementById('bookingContent');
    const bookingForm = document.getElementById('bookingForm');
    const formAlert = document.getElementById('formAlert');
    const submitBtn = document.getElementById('submitBtn');

    // Set min date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('startDate').min = today;

    let currentPropertyType = 'rent';

    function formatPrice(price, listingType) {
        const formatted = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
        }).format(price);
        return listingType === 'rent' ? formatted + '/شهر' : formatted;
    }

    async function loadProperty() {
        try {
            const response = await fetch(`api/properties.php?id=${propertyId}`);
            const data = await response.json();

            if (!data.success || !data.property) {
                errorMessage.textContent = 'لم يتم العثور على الشقة.';
                errorMessage.classList.remove('d-none');
                loadingMessage.classList.add('d-none');
                return;
            }

            const property = data.property;
            currentPropertyType = property.listing_type;

            document.getElementById('propTitle').textContent = property.title;
            document.getElementById('propLocation').innerHTML = `<i class="bi bi-geo-alt"></i> ${property.location}`;
            document.getElementById('propPrice').textContent = formatPrice(parseFloat(property.price), property.listing_type);
            
            if (currentPropertyType === 'sale') {
                document.getElementById('rentFields').classList.add('d-none');
                document.getElementById('occupantsField').classList.add('d-none');
                
                document.getElementById('startDate').removeAttribute('required');
                document.getElementById('contractType').removeAttribute('required');
                document.getElementById('occupants').removeAttribute('required');
            }
            
            const image = property.image_url ? property.image_url : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="800" height="600" fill="%23f0f0f0"/><text x="50%" y="50%" font-family="Arial" font-size="24" fill="%23999" dominant-baseline="middle" text-anchor="middle">صورة</text></svg>';
            document.getElementById('propImg').style.backgroundImage = `url('${image}')`;

            loadingMessage.classList.add('d-none');
            bookingContent.classList.remove('d-none');

        } catch (error) {
            errorMessage.textContent = 'حدث خطأ في الاتصال.';
            errorMessage.classList.remove('d-none');
            loadingMessage.classList.add('d-none');
        }
    }

    loadProperty();

    bookingForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const startDate = document.getElementById('startDate').value;
        const contractType = document.getElementById('contractType').value;
        const occupants = document.getElementById('occupants').value;

        formAlert.classList.add('d-none');
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري التحقق...';

        try {
            const response = await fetch('api/bookings.php?action=create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    property_id: propertyId,
                    type: currentPropertyType,
                    start_date: startDate,
                    contract_type: contractType,
                    occupants: occupants
                })
            });

            const data = await response.json();

            if (!response.ok) {
                formAlert.textContent = data.error || 'حدث خطأ أثناء طلب الحجز.';
                formAlert.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.textContent = 'تأكيد مبدئي والمتابعة للدفع';
            } else {
                // Success, redirect to confirmation page
                window.location.href = `booking_confirm.html?id=${data.booking.id}`;
            }

        } catch (error) {
            formAlert.textContent = 'حدث خطأ في الاتصال بالخادم.';
            formAlert.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.textContent = 'تأكيد مبدئي والمتابعة للدفع';
        }
    });
});
