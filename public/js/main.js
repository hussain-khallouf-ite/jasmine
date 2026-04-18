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
});
