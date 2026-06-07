document.addEventListener('DOMContentLoaded', () => {
    // Load Project Statistics
    async function loadProjectStats() {
        try {
            const response = await fetch('api/buildings.php?action=stats');
            const data = await response.json();

            if (data.success && data.stats) {
                const stats = data.stats;
                document.getElementById('statArea').textContent = stats.total_area_m2.toLocaleString() + ' م²';
                document.getElementById('statBuildings').textContent = stats.buildings_count + ' مبانٍ';
                document.getElementById('statParks').textContent = stats.parks_count + ' حدائق عائلية';
                document.getElementById('statProperties').textContent = stats.available_properties_count + ' / ' + stats.properties_count;
            } else {
                console.error('Failed to load project stats:', data.message);
                fallbackStats();
            }
        } catch (error) {
            console.error('Error loading project stats:', error);
            fallbackStats();
        }
    }

    function fallbackStats() {
        document.getElementById('statArea').textContent = '50,000 م²';
        document.getElementById('statBuildings').textContent = '3 مبانٍ';
        document.getElementById('statParks').textContent = 'حديقتان كبيرتان';
        document.getElementById('statProperties').textContent = 'نشط';
    }

    // Load Buildings List
    async function loadBuildings() {
        const buildingsList = document.getElementById('buildingsList');
        const template = document.getElementById('buildingCardTemplate');
        const loader = document.getElementById('buildingsLoading');
        const errorDiv = document.getElementById('buildingsError');

        if (!buildingsList || !template) return;

        try {
            const response = await fetch('api/buildings.php');
            const data = await response.json();

            if (loader) loader.classList.add('d-none');

            if (!data.success || !data.buildings) {
                errorDiv.textContent = data.message || 'فشل تحميل قائمة المباني.';
                errorDiv.classList.remove('d-none');
                return;
            }

            const buildings = data.buildings;

            if (buildings.length === 0) {
                buildingsList.innerHTML = '<div class="col-12 text-center text-muted">لا يوجد مبانٍ مسجلة حالياً.</div>';
                return;
            }

            buildingsList.innerHTML = '';
            buildings.forEach(building => {
                const clone = template.content.cloneNode(true);
                
                const imgContainer = clone.querySelector('.building-img-container');
                const image = building.image_url ? building.image_url : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="800" height="600" fill="%23e9ecef"/><text x="50%" y="50%" font-family="Arial" font-size="24" fill="%23999" dominant-baseline="middle" text-anchor="middle">صورة المبنى غير متوفرة</text></svg>';
                imgContainer.style.backgroundImage = `url('${image}')`;
                
                clone.querySelector('.building-floors').textContent = building.floors;
                clone.querySelector('.building-name').textContent = building.name;
                clone.querySelector('.building-desc').textContent = building.description || 'لا يوجد وصف متاح.';
                clone.querySelector('.building-location').textContent = building.location;
                
                const link = clone.querySelector('.building-link');
                if (link) {
                    link.href = `building.html?id=${building.id}`;
                }
                
                buildingsList.appendChild(clone);
            });
        } catch (error) {
            console.error('Error fetching buildings:', error);
            if (loader) loader.classList.add('d-none');
            errorDiv.textContent = 'حدث خطأ أثناء الاتصال بالخادم. يرجى المحاولة لاحقاً.';
            errorDiv.classList.remove('d-none');
        }
    }

    loadProjectStats();
    loadBuildings();
});
