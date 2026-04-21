document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login-form');

    loginForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Mencegah reload halaman secara default
        
        const usernameInput = document.getElementById('username').value;
        const passwordInput = document.getElementById('password').value;

        // Validasi dan Fetch API AJAX login
        if (usernameInput.trim() !== '' && passwordInput.trim() !== '') {
            // Animasi tombol saat proses login berjalan
            const btn = document.querySelector('.btn-login');
            const originalText = btn.innerText;
            btn.innerText = 'Authenticating...';
            btn.disabled = true;

            // Membuat Format Data Form Data untuk HTTP Request PHP
            const formData = new FormData();
            formData.append('username', usernameInput);
            formData.append('password', passwordInput);

            // Fetch request ke login.php (sekarang berisikan endpoint login)
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Semua user diarahkan ke dashboard setelah login sukses
                    window.location.href = 'dashboard.html';
                } else {
                    alert(data.message || 'Gagal login!');
                    btn.innerText = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terdapat masalah pada koneksi login.');
                btn.innerText = originalText;
                btn.disabled = false;
            });
            
        } else {
            alert('Please enter both username and password.');
        }
    });
});