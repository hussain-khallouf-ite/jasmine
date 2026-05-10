document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const propertyId = urlParams.get('id');

    const loadingMessage = document.getElementById('loadingMessage');
    const errorMessage = document.getElementById('errorMessage');
    const propertyDetails = document.getElementById('propertyDetails');

    if (!propertyId) {
        showError('لم يتم تحديد الشقة.');
        return;
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
        }).format(price) + '/شهر';
    }

    function showError(message) {
        loadingMessage.classList.add('d-none');
        errorMessage.textContent = message;
        errorMessage.classList.remove('d-none');
    }

    async function loadPropertyDetails() {
        try {
            const response = await fetch(`api/properties.php?id=${propertyId}`);
            const data = await response.json();

            if (!data.success || !data.property) {
                showError(data.message || 'لم يتم العثور على الشقة.');
                return;
            }

            const property = data.property;

            // Hide loading, show details
            loadingMessage.classList.add('d-none');
            propertyDetails.classList.remove('d-none');

            // Populate text
            document.title = property.title + ' | الياسمين';
            document.getElementById('propTitle').textContent = property.title;
            document.getElementById('propDesc').textContent = property.description;
            document.getElementById('propLocation').innerHTML = `<i class="bi bi-geo-alt"></i> ${property.location}`;
            document.getElementById('propPrice').textContent = formatPrice(parseFloat(property.price_per_month));
            document.getElementById('propRooms').textContent = property.rooms;
            document.getElementById('propSize').textContent = property.size_m2 + ' م²';
            document.getElementById('propFloor').textContent = property.floor;
            document.getElementById('propType').textContent = property.type === 'commercial' ? 'مساحة تجارية' : 'شقة سكنية';

            // Status badge
            const statusBadge = document.getElementById('propStatus');
            if (property.status === 'available') {
                statusBadge.textContent = 'متاح';
                statusBadge.className = 'badge bg-success mb-2 fs-6';
            } else {
                statusBadge.textContent = 'غير متاح';
                statusBadge.className = 'badge bg-secondary mb-2 fs-6';
                document.getElementById('bookBtn').disabled = true;
            }

            // Image
            const imgDiv = document.getElementById('propImg');
            const image = property.image_url ? property.image_url : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="800" height="600" fill="%23f0f0f0"/><text x="50%" y="50%" font-family="Arial" font-size="24" fill="%23999" dominant-baseline="middle" text-anchor="middle">صورة غير متوفرة</text></svg>';
            imgDiv.style.backgroundImage = `url('${image}')`;

            // Amenities
            const amenitiesContainer = document.getElementById('propAmenities');
            if (property.amenities && property.amenities.length > 0) {
                property.amenities.forEach(amenity => {
                    const li = document.createElement('li');
                    li.className = 'col-md-6 mb-2';
                    li.innerHTML = `<i class="bi bi-check-circle-fill text-success me-2"></i> ${amenity}`;
                    amenitiesContainer.appendChild(li);
                });
            } else {
                amenitiesContainer.innerHTML = '<li>لا توجد مميزات إضافية مسجلة.</li>';
            }

        } catch (error) {
            console.error('Error fetching property details:', error);
            showError('حدث خطأ في الاتصال بالخادم. يرجى المحاولة لاحقاً.');
        }
    }

    loadPropertyDetails();

    // Booking Button
    document.getElementById('bookBtn').addEventListener('click', () => {
        // Here we could check auth status and either redirect to login or show booking modal
        // For now, redirect to a hypothetical booking page or login
        const token = localStorage.getItem('token');
        if (token) {
            alert('سيتم توجيهك إلى صفحة الحجز...');
        } else {
            window.location.href = `login.html?redirect=property.html?id=${propertyId}`;
        }
    });
});
