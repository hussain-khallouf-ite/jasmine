document.addEventListener('DOMContentLoaded', () => {
    const rentRevenueEl = document.getElementById('rentRevenue');
    const saleRevenueEl = document.getElementById('saleRevenue');
    const rentCountEl = document.getElementById('rentCount');
    const saleCountEl = document.getElementById('saleCount');
    const recentBookingsTable = document.getElementById('recentBookingsTable');
    const propertyStatsEl = document.getElementById('propertyStats');

    function formatPrice(price) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            maximumFractionDigits: 0
        }).format(price);
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'pending': return '<span class="badge bg-warning text-dark">معلق</span>';
            case 'confirmed': return '<span class="badge bg-success">مؤكد</span>';
            case 'cancelled': return '<span class="badge bg-danger">ملغي</span>';
            case 'completed': return '<span class="badge bg-primary">مكتمل</span>';
            default: return `<span class="badge bg-secondary">${status}</span>`;
        }
    }

    async function loadReportData() {
        try {
            const response = await fetch('/jasmine/public/api/admin/reports.php');
            const result = await response.json();

            if (!result.success) {
                alert('فشل تحميل بيانات التقارير: ' + result.message);
                return;
            }

            const { revenue, bookings_count, recent_bookings, property_stats } = result.data;

            // Update Summary Cards
            const rentRevenue = revenue.find(r => r.type === 'rent')?.revenue || 0;
            const saleRevenue = revenue.find(r => r.type === 'sale')?.revenue || 0;
            rentRevenueEl.textContent = formatPrice(rentRevenue);
            saleRevenueEl.textContent = formatPrice(saleRevenue);

            const rentCount = bookings_count.find(c => c.type === 'rent')?.count || 0;
            const saleCount = bookings_count.find(c => c.type === 'sale')?.count || 0;
            rentCountEl.textContent = rentCount;
            saleCountEl.textContent = saleCount;

            // Update Recent Bookings Table
            recentBookingsTable.innerHTML = '';
            if (recent_bookings.length === 0) {
                recentBookingsTable.innerHTML = '<tr><td colspan="6" class="text-center">لا توجد حجوزات حديثة.</td></tr>';
            } else {
                recent_bookings.forEach(booking => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${booking.user_name}</td>
                        <td>${booking.property_title}</td>
                        <td>${booking.type === 'rent' ? 'إيجار' : 'بيع'}</td>
                        <td>${formatPrice(booking.total_amount)}</td>
                        <td>${new Date(booking.created_at).toLocaleDateString('ar-SY')}</td>
                        <td>${getStatusBadge(booking.status)}</td>
                    `;
                    recentBookingsTable.appendChild(tr);
                });
            }

            // Update Property Stats
            propertyStatsEl.innerHTML = '';
            property_stats.forEach(stat => {
                const div = document.createElement('div');
                div.className = 'd-flex justify-content-between align-items-center mb-2';
                let statusLabel = '';
                switch(stat.status) {
                    case 'available': statusLabel = 'متاحة'; break;
                    case 'reserved': statusLabel = 'محجوزة'; break;
                    case 'unavailable': statusLabel = 'غير متاحة'; break;
                    default: statusLabel = stat.status;
                }
                div.innerHTML = `<span>${statusLabel}</span><span class="fw-bold">${stat.count}</span>`;
                propertyStatsEl.appendChild(div);
            });

        } catch (error) {
            console.error('Error fetching report data:', error);
            alert('حدث خطأ أثناء تحميل بيانات التقارير.');
        }
    }

    loadReportData();
});
