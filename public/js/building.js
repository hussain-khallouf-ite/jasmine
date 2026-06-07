document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const buildingId = urlParams.get('id');

    const loadingLayout = document.getElementById('loadingLayout');
    const errorLayout = document.getElementById('errorLayout');
    const buildingContent = document.getElementById('buildingContent');
    const floorVisualizerStack = document.getElementById('floorVisualizerStack');

    let previewModal;

    if (!buildingId) {
        showError('لم يتم تحديد المبنى المطلوب.');
        return;
    }

    // Initialize Bootstrap Modal
    const modalElement = document.getElementById('slotPreviewModal');
    if (modalElement) {
        previewModal = new bootstrap.Modal(modalElement);
    }

    function showError(message) {
        if (loadingLayout) loadingLayout.classList.add('d-none');
        if (errorLayout) {
            errorLayout.textContent = message;
            errorLayout.classList.remove('d-none');
        }
    }

    function formatPrice(price, listingType) {
        const formatted = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
        }).format(price);
        return listingType === 'rent' ? formatted + '/شهر' : formatted;
    }

    async function loadBuildingData() {
        try {
            const response = await fetch(`api/buildings.php?id=${buildingId}`);
            const data = await response.json();

            if (!data.success || !data.building) {
                showError(data.message || 'المبنى غير موجود في النظام.');
                return;
            }

            const building = data.building;
            const properties = data.properties || [];

            // Populate building headers
            document.title = building.name + ' | الياسمين';
            document.getElementById('buildingName').textContent = building.name;
            document.getElementById('buildingDesc').textContent = building.description || 'لا يوجد وصف متاح.';
            document.getElementById('buildingLocation').textContent = building.location;
            document.getElementById('buildingFloorsCount').textContent = building.floors;
            document.getElementById('buildingArea').textContent = parseFloat(building.total_area_m2 || 0).toLocaleString();

            // Populate status stats
            const totalCount = properties.length;
            const availableCount = properties.filter(p => p.status === 'available').length;
            const reservedCount = properties.filter(p => p.status === 'reserved').length;

            document.getElementById('countTotal').textContent = totalCount;
            document.getElementById('countAvailable').textContent = availableCount;
            document.getElementById('countReserved').textContent = reservedCount;

            // Render interactive visual stack
            renderFloorStack(building, properties);

            // Render 3D Visualizer
            init3DVisualizer(building, properties);

            // Hide loading, show content
            if (loadingLayout) loadingLayout.classList.add('d-none');
            if (buildingContent) buildingContent.classList.remove('d-none');

        } catch (error) {
            console.error('Error loading building details:', error);
            showError('حدث خطأ في الاتصال بالخادم. يرجى إعادة المحاولة لاحقاً.');
        }
    }

    function renderFloorStack(building, properties) {
        if (!floorVisualizerStack) return;
        floorVisualizerStack.innerHTML = '';

        // Group properties by floor
        const propertiesByFloor = {};
        properties.forEach(prop => {
            const floor = parseInt(prop.floor);
            if (!propertiesByFloor[floor]) {
                propertiesByFloor[floor] = [];
            }
            propertiesByFloor[floor].push(prop);
        });

        // Determine floors range (0 to building.floors - 1, or minimum/maximum floor found in database)
        const maxFloor = Math.max(building.floors - 1, ...Object.keys(propertiesByFloor).map(Number), 0);
        const minFloor = Math.min(0, ...Object.keys(propertiesByFloor).map(Number));

        // Generate each floor row from minFloor to maxFloor
        // We go from minFloor to maxFloor. Since container has `flex-column-reverse`,
        // appending floor 0 first and floor N last will display floor N at the top and 0 at the bottom.
        for (let f = minFloor; f <= maxFloor; f++) {
            const floorRow = document.createElement('div');
            floorRow.className = 'floor-row';

            // Floor Label (Left)
            const labelDiv = document.createElement('div');
            labelDiv.className = 'floor-label';
            
            let floorText = `الطابق ${f}`;
            if (f === 0) floorText = 'الطابق الأرضي';
            else if (f === 1) floorText = 'الطابق الأول';
            else if (f === 2) floorText = 'الطابق الثاني';
            else if (f === 3) floorText = 'الطابق الثالث';
            
            labelDiv.innerHTML = `<i class="bi bi-layers"></i> ${floorText}`;
            floorRow.appendChild(labelDiv);

            // Properties Slots Container (Right)
            const slotsContainer = document.createElement('div');
            slotsContainer.className = 'properties-container';

            const floorProperties = propertiesByFloor[f] || [];

            if (floorProperties.length === 0) {
                // If there are no properties configured on this floor
                const noSlots = document.createElement('span');
                noSlots.className = 'text-muted small py-2';
                noSlots.textContent = 'لا يوجد شقق مخصصة في هذا الطابق حالياً';
                slotsContainer.appendChild(noSlots);
            } else {
                // Render properties
                floorProperties.forEach(prop => {
                    const slot = document.createElement('div');
                    
                    let statusClass = 'slot-unavailable';
                    let statusLabel = 'غير متاح';
                    if (prop.status === 'available') {
                        statusClass = 'slot-available';
                        statusLabel = 'متاح';
                    } else if (prop.status === 'reserved') {
                        statusClass = 'slot-reserved';
                        statusLabel = 'محجوز';
                    }

                    slot.className = `interactive-slot ${statusClass}`;
                    
                    const typeText = prop.type === 'commercial' ? 'مساحة تجارية' : 'شقة سكنية';
                    const listTypeSuffix = prop.listing_type === 'sale' ? 'للبيع' : 'للإيجار';
                    
                    slot.innerHTML = `
                        <div>
                            <div class="slot-title">${prop.title}</div>
                            <div class="slot-meta">
                                <span>${prop.rooms} غرف</span>
                                <span>${prop.size_m2} م²</span>
                            </div>
                        </div>
                        <div class="slot-price">
                            <span class="small fw-bold">${formatPrice(parseFloat(prop.price), prop.listing_type)}</span>
                            <span class="badge bg-white text-dark border small py-1 px-2">${listTypeSuffix}</span>
                        </div>
                    `;

                    // Click event to show modal preview
                    slot.addEventListener('click', () => {
                        showSlotPreview(prop);
                    });

                    slotsContainer.appendChild(slot);
                });
            }

            floorRow.appendChild(slotsContainer);
            floorVisualizerStack.appendChild(floorRow);
        }
    }

    function showSlotPreview(property) {
        if (!previewModal) return;

        // Set text
        document.getElementById('modalSlotTitle').textContent = property.title;
        document.getElementById('modalSlotLocation').innerHTML = `<i class="bi bi-geo-alt"></i> ${property.location}`;
        document.getElementById('modalSlotSize').textContent = property.size_m2 + ' م²';
        document.getElementById('modalSlotRooms').textContent = property.rooms;
        document.getElementById('modalSlotFloor').textContent = property.floor === 0 ? 'الأرضي' : property.floor;
        document.getElementById('modalSlotDesc').textContent = property.description || 'لا يوجد وصف إضافي.';
        
        // Image
        const imgDiv = document.getElementById('modalSlotImg');
        const image = property.image_url ? property.image_url : 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="800" height="600" fill="%23f0f0f0"/><text x="50%" y="50%" font-family="Arial" font-size="24" fill="%23999" dominant-baseline="middle" text-anchor="middle">صورة غير متوفرة</text></svg>';
        imgDiv.style.backgroundImage = `url('${image}')`;

        // Price text & status badge
        const priceLabel = document.getElementById('modalListingType');
        priceLabel.textContent = property.listing_type === 'sale' ? 'سعر البيع' : 'سعر الإيجار';
        document.getElementById('modalSlotPrice').textContent = formatPrice(parseFloat(property.price), property.listing_type);

        const statusBadge = document.getElementById('modalSlotStatus');
        const bookBtn = document.getElementById('modalBookBtn');

        if (property.status === 'available') {
            statusBadge.textContent = 'متاح للحجز';
            statusBadge.className = 'badge bg-success fs-6 p-2 px-3';
            bookBtn.disabled = false;
            bookBtn.classList.remove('d-none');
            bookBtn.href = `booking.html?id=${property.id}`;
        } else {
            statusBadge.textContent = property.status === 'reserved' ? 'محجوز مؤقتاً' : 'غير متاح حالياً';
            statusBadge.className = 'badge bg-secondary fs-6 p-2 px-3';
            bookBtn.disabled = true;
            bookBtn.classList.add('d-none');
        }

        // Set details link
        document.getElementById('modalDetailsBtn').href = `property.html?id=${property.id}`;

        // Show Modal
        previewModal.show();
    }

    // --- 3D Visualizer Logic ---
    let scene, camera, renderer, controls;
    let propertyMeshes = [];
    let raycaster, mouse;
    let hoveredMesh = null;

    function init3DVisualizer(building, properties) {
        const container = document.getElementById('building3dCanvas');
        if (!container || typeof THREE === 'undefined') return;
        
        container.innerHTML = ''; // clear if re-rendering
        propertyMeshes = [];

        scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf8f9fa);

        // Add soft light
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
        scene.add(ambientLight);
        
        const dirLight = new THREE.DirectionalLight(0xffffff, 0.4);
        dirLight.position.set(10, 20, 10);
        scene.add(dirLight);

        camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1, 1000);
        
        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(container.clientWidth, container.clientHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.maxPolarAngle = Math.PI / 2 + 0.1; // Don't go too far below ground

        // Build the structure
        build3DModel(building, properties);

        // Setup interaction
        raycaster = new THREE.Raycaster();
        mouse = new THREE.Vector2();

        container.addEventListener('mousemove', onMouseMove);
        container.addEventListener('click', onMouseClick);
        window.addEventListener('resize', onWindowResize);

        animate();
    }

    function build3DModel(building, properties) {
        // Group properties by floor
        const propertiesByFloor = {};
        properties.forEach(prop => {
            const floor = parseInt(prop.floor);
            if (!propertiesByFloor[floor]) propertiesByFloor[floor] = [];
            propertiesByFloor[floor].push(prop);
        });

        const maxFloor = Math.max(building.floors - 1, ...Object.keys(propertiesByFloor).map(Number), 0);
        const minFloor = Math.min(0, ...Object.keys(propertiesByFloor).map(Number));

        const floorHeight = 3;
        const buildingWidth = 14;
        const buildingDepth = 10;
        
        // Ground
        const groundGeo = new THREE.PlaneGeometry(40, 40);
        const groundMat = new THREE.MeshLambertMaterial({ color: 0xcccccc });
        const ground = new THREE.Mesh(groundGeo, groundMat);
        ground.rotation.x = -Math.PI / 2;
        ground.position.y = -floorHeight/2;
        scene.add(ground);

        // Center building based on floors
        const totalHeight = (maxFloor - minFloor + 1) * floorHeight;
        camera.position.set(20, totalHeight, 25);
        controls.target.set(0, totalHeight / 2, 0);

        for (let f = minFloor; f <= maxFloor; f++) {
            const yPos = (f - minFloor) * floorHeight;
            const floorProps = propertiesByFloor[f] || [];
            
            // If no properties, draw a solid block for the floor
            if (floorProps.length === 0) {
                const geo = new THREE.BoxGeometry(buildingWidth, floorHeight * 0.9, buildingDepth);
                const mat = new THREE.MeshLambertMaterial({ color: 0xe0e0e0, transparent: true, opacity: 0.8 });
                const mesh = new THREE.Mesh(geo, mat);
                mesh.position.y = yPos;
                scene.add(mesh);
                continue;
            }

            // Draw properties, split space along the width
            const numProps = floorProps.length;
            const propWidth = buildingWidth / numProps;
            
            floorProps.forEach((prop, index) => {
                const geo = new THREE.BoxGeometry(propWidth * 0.95, floorHeight * 0.9, buildingDepth * 0.95);
                
                let color = 0x9e9e9e; // unavailable grey
                if (prop.status === 'available') color = 0x4caf50; // green
                else if (prop.status === 'reserved') color = 0xfbc02d; // yellow

                const mat = new THREE.MeshLambertMaterial({ color: color });
                const mesh = new THREE.Mesh(geo, mat);
                
                const xPos = -buildingWidth/2 + (propWidth/2) + (index * propWidth);
                mesh.position.set(xPos, yPos, 0);
                
                // Store property data on mesh for raycaster
                mesh.userData = {
                    property: prop,
                    originalColor: color
                };
                
                scene.add(mesh);
                propertyMeshes.push(mesh);
            });
        }
    }

    function onMouseMove(event) {
        event.preventDefault();
        const container = document.getElementById('building3dCanvas');
        if (!container) return;
        const rect = container.getBoundingClientRect();
        
        mouse.x = ((event.clientX - rect.left) / container.clientWidth) * 2 - 1;
        mouse.y = -((event.clientY - rect.top) / container.clientHeight) * 2 + 1;

        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(propertyMeshes);

        const tooltip = document.getElementById('tooltip3d');

        if (intersects.length > 0) {
            const object = intersects[0].object;
            
            if (hoveredMesh !== object) {
                if (hoveredMesh) hoveredMesh.material.color.setHex(hoveredMesh.userData.originalColor);
                hoveredMesh = object;
                hoveredMesh.material.color.offsetHSL(0, 0, 0.2); // Highlight
                container.style.cursor = 'pointer';
            }

            const prop = hoveredMesh.userData.property;
            let statusText = 'غير متاح';
            if (prop.status === 'available') statusText = '<span class="text-success fw-bold">متاح</span>';
            if (prop.status === 'reserved') statusText = '<span class="text-warning fw-bold">محجوز</span>';

            tooltip.innerHTML = `
                <div class="fw-bold">${prop.title}</div>
                <div class="small text-muted">${prop.size_m2} م² | ${prop.rooms} غرف</div>
                <div class="small mt-1">${statusText}</div>
            `;
            tooltip.style.display = 'block';
            tooltip.style.left = (event.clientX) + 'px';
            tooltip.style.top = (event.clientY - 20) + 'px';
        } else {
            if (hoveredMesh) {
                hoveredMesh.material.color.setHex(hoveredMesh.userData.originalColor);
                hoveredMesh = null;
                container.style.cursor = 'default';
            }
            if (tooltip) tooltip.style.display = 'none';
        }
    }

    function onMouseClick(event) {
        if (hoveredMesh) {
            showSlotPreview(hoveredMesh.userData.property);
        }
    }

    function onWindowResize() {
        const container = document.getElementById('building3dCanvas');
        if (!container || !camera || !renderer) return;
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    }

    function animate() {
        requestAnimationFrame(animate);
        if (controls) controls.update();
        if (renderer && scene && camera) renderer.render(scene, camera);
    }

    loadBuildingData();
});
