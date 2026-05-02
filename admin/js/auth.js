document.addEventListener('DOMContentLoaded', () => {
    checkAdminAuth();

    const loginForm = document.getElementById('adminLoginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleAdminLogin);
    }

    const logoutBtn = document.getElementById('adminLogoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleAdminLogout);
    }
});

async function checkAdminAuth() {
    try {
        const response = await fetch('/jasmine/public/api/admin/auth.php?action=check', {
            method: 'POST'
        });
        const data = await response.json();

        const isLoginPage = window.location.pathname.includes('login.html');

        if (data.success && data.user && data.user.role === 'admin') {
            if (isLoginPage) {
                window.location.href = 'index.html';
            } else {
                updateAdminUI(data.user);
            }
        } else {
            if (!isLoginPage) {
                window.location.href = 'login.html';
            }
        }
    } catch (error) {
        console.error('Error checking admin auth:', error);
    }
}

async function handleAdminLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('/jasmine/public/api/admin/auth.php?action=login', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            window.location.href = 'index.html';
        } else {
            showError(data.message || 'فشل تسجيل الدخول. يرجى التحقق من بياناتك.');
            if (data.errors) {
                for (const [field, error] of Object.entries(data.errors)) {
                    showFieldError(form, field, error);
                }
            }
        }
    } catch (error) {
        console.error('Login error:', error);
        showError('حدث خطأ أثناء الاتصال بالخادم.');
    }
}

async function handleAdminLogout(e) {
    e.preventDefault();
    try {
        const response = await fetch('/jasmine/public/api/admin/auth.php?action=logout', {
            method: 'POST'
        });
        const data = await response.json();
        if (data.success) {
            window.location.href = 'login.html';
        }
    } catch (error) {
        console.error('Logout error:', error);
    }
}

function updateAdminUI(user) {
    const adminNameSpan = document.getElementById('adminName');
    if (adminNameSpan && user) {
        adminNameSpan.textContent = user.name;
    }
}

function showError(message) {
    let errorDiv = document.getElementById('loginError');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'loginError';
        errorDiv.className = 'alert alert-danger';
        const form = document.getElementById('adminLoginForm');
        if (form) {
            form.prepend(errorDiv);
        }
    }
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function showFieldError(form, fieldName, message) {
    const input = form.elements[fieldName];
    if (input) {
        input.classList.add('is-invalid');
        let feedback = input.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            input.parentNode.insertBefore(feedback, input.nextSibling);
        }
        feedback.textContent = message;
    }
}

document.addEventListener('input', (e) => {
    if (e.target.classList.contains('is-invalid')) {
        e.target.classList.remove('is-invalid');
    }
});
