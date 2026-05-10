document.addEventListener('DOMContentLoaded', () => {
    const propertiesList = document.getElementById('propertiesList');
    const errorMessage = document.getElementById('errorMessage');
    const emptyMessage = document.getElementById('emptyMessage');
    const template = document.getElementById('propertyCardTemplate');

    if (!propertiesList || !template) return;

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
        }).format(price) + '/شهر';
    }

    async function loadProperties() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            let apiUrl = 'api/properties.php?';
            
            if (urlParams.has('type') && urlParams.get('type') !== '') {
                apiUrl += `type=${urlParams.get('type')}&`;
            }
            if (urlParams.has('rooms') && urlParams.get('rooms') !== '') {
                apiUrl += `rooms=${urlParams.get('rooms')}&`;
            }

            const response = await fetch(apiUrl);
            const data = await response.json();

            if (!data.success) {
                errorMessage.textContent = data.message || 'تعذر تحميل الشقق في الوقت الحالي. يرجى المحاولة مرة أخرى لاحقاً.';
                errorMessage.classList.remove('d-none');
                return;
            }

            const properties = data.properties || [];

            if (properties.length === 0) {
                emptyMessage.classList.remove('d-none');
                return;
            }

            properties.forEach(property => {
                const clone = template.content.cloneNode(true);
                
                const imgDiv = clone.querySelector('.property-img');
                const image = property.image_url ? property.image_url : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="800" height="600" fill="%23f0f0f0"/><text x="50%" y="50%" font-family="Arial" font-size="24" fill="%23999" dominant-baseline="middle" text-anchor="middle">صورة غير متوفرة</text></svg>';
                imgDiv.style.backgroundImage = `url('${image}')`;
                
                clone.querySelector('.property-price').textContent = formatPrice(parseFloat(property.price_per_month));
                clone.querySelector('.property-title').textContent = property.title;
                clone.querySelector('.property-description').textContent = property.description;
                clone.querySelector('.property-location').textContent = property.location;
                clone.querySelector('.property-rooms').textContent = property.rooms;
                clone.querySelector('.property-size').textContent = property.size_m2;
                
                const detailsLink = clone.querySelector('a.btn');
                if (detailsLink) {
                    detailsLink.href = `property.html?id=${property.id}`;
                }
                
                propertiesList.appendChild(clone);
            });
            
        } catch (error) {
            console.error('Error fetching properties:', error);
            errorMessage.textContent = 'حدث خطأ في الاتصال بالخادم. يرجى المحاولة لاحقاً.';
            errorMessage.classList.remove('d-none');
        }
    }

    loadProperties();
});
