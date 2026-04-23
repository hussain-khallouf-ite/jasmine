document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const profileForm = document.getElementById('profileForm');
    const authAlert = document.getElementById('authAlert');
    const logoutLink = document.getElementById('logoutLink');

    function showAlert(message, type = 'danger') {
        if (!authAlert) {
            return;
        }
        authAlert.className = `alert alert-${type}`;
        authAlert.textContent = message;
        authAlert.classList.remove('d-none');
    }

    function clearAlert() {
        if (!authAlert) {
            return;
        }
        authAlert.classList.add('d-none');
        authAlert.textContent = '';
    }

    async function submitForm(event, url) {
        event.preventDefault();
        clearAlert();

        const form = event.target;
        const formData = new FormData(form);

        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
            });
            const data = await response.json();

            if (!data.success) {
                if (data.errors) {
                    showAlert(Object.values(data.errors)[0] || 'Please correct the highlighted fields.');
                } else {
                    showAlert(data.message || 'Request failed.');
                }
                return;
            }

            if (form.id === 'profileForm') {
                showAlert(data.message || 'Profile updated successfully.', 'success');
                return;
            }

            window.location.href = 'profile.html';
        } catch (error) {
            showAlert('Unable to connect to the server. Please try again later.');
            console.error(error);
        }
    }

    async function fetchProfile() {
        try {
            const response = await fetch('api/user.php', { method: 'GET' });
            const data = await response.json();

            if (!data.success) {
                window.location.href = 'login.html';
                return;
            }

            const user = data.user;
            document.getElementById('profileId').value = user.id ?? '';
            document.getElementById('profileRole').value = user.role ?? '';
            document.getElementById('name').value = user.name ?? '';
            document.getElementById('email').value = user.email ?? '';
            document.getElementById('phone').value = user.phone ?? '';
            document.getElementById('profileStatus').value = user.status ?? '';
            document.getElementById('createdAt').value = user.created_at ?? '';
            document.getElementById('updatedAt').value = user.updated_at ?? '';
        } catch (error) {
            showAlert('Unable to load profile data.');
            console.error(error);
        }
    }

    async function handleLogout(event) {
        event.preventDefault();

        try {
            const response = await fetch('api/auth.php?action=logout', {
                method: 'POST',
            });
            const data = await response.json();
            if (data.success) {
                window.location.href = 'login.html';
                return;
            }
            showAlert(data.message || 'Unable to log out.');
        } catch (error) {
            showAlert('Unable to connect to the server.');
            console.error(error);
        }
    }

    if (loginForm) {
        loginForm.addEventListener('submit', (event) => submitForm(event, loginForm.action));
    }

    if (registerForm) {
        registerForm.addEventListener('submit', (event) => submitForm(event, registerForm.action));
    }

    if (profileForm) {
        profileForm.addEventListener('submit', (event) => submitForm(event, profileForm.action));
        fetchProfile();
    }

    if (logoutLink) {
        logoutLink.addEventListener('click', handleLogout);
    }
});
