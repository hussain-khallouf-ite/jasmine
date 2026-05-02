document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardStats();
});

async function fetchDashboardStats() {
    try {
        const response = await fetch('/jasmine/public/api/admin/dashboard.php');
        
        if (response.status === 401 || response.status === 403) return;

        const data = await response.json();
        
        if (data.success && data.stats) {
            updateDashboardUI(data.stats);
        } else {
            console.error('Failed to load stats:', data.message);
        }
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
}

function updateDashboardUI(stats) {
    const totalPropertiesEl = document.querySelector('.bg-primary h2');
    const activeUsersEl = document.querySelector('.bg-success h2');
    const newBookingsEl = document.querySelector('.bg-warning h2');

    if (totalPropertiesEl) totalPropertiesEl.textContent = stats.total_properties;
    if (activeUsersEl) activeUsersEl.textContent = stats.active_users;
    if (newBookingsEl) newBookingsEl.textContent = stats.new_bookings;
}
