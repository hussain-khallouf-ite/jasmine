// Vanilla JS for Al-Yasmin Frontend

document.addEventListener('DOMContentLoaded', () => {
    // Add scroll effect on navbar
    const navbar = document.querySelector('.navbar-custom');
    
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 10px 30px rgba(0,0,0,0.1)';
            } else {
                navbar.style.background = 'var(--glass-bg)';
                navbar.style.boxShadow = 'var(--box-shadow)';
            }
        });
    }

    // Interactive button effect using delegated events
    document.body.addEventListener('mousedown', (e) => {
        if (e.target.classList.contains('btn-primary-custom')) {
            e.target.style.transform = 'scale(0.95)';
        }
    });

    document.body.addEventListener('mouseup', (e) => {
        if (e.target.classList.contains('btn-primary-custom')) {
            e.target.style.transform = 'translateY(-2px)'; // Return to hover state
        }
    });

    // Load featured properties
    const featuredList = document.getElementById('featuredPropertiesList');
    const template = document.getElementById('propertyCardTemplate');
    const errorMessage = document.getElementById('errorMessage');

    if (featuredList && template) {
        function formatPrice(price) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                maximumFractionDigits: 0
            }).format(price) + '/شهر';
        }

        async function loadFeaturedProperties() {
            try {
                const response = await fetch('api/properties.php?limit=3');
                const data = await response.json();

                if (!data.success) {
                    if (errorMessage) {
                        errorMessage.textContent = data.message || 'تعذر تحميل الشقق في الوقت الحالي.';
                        errorMessage.classList.remove('d-none');
                    }
                    return;
                }

                const properties = data.properties || [];

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
                    
                    featuredList.appendChild(clone);
                });
            } catch (error) {
                console.error('Error fetching featured properties:', error);
                if (errorMessage) {
                    errorMessage.textContent = 'حدث خطأ في الاتصال بالخادم.';
                    errorMessage.classList.remove('d-none');
                }
            }
        }

        loadFeaturedProperties();
    }
});
