/* File: dashboard.js
 * Deskripsi: Front-end logic yang menjembatani fetch ke endpoint backend proyek. Merender list-list Card 
 * serta menangani upload Multi-part form dari modal window.
 */
document.addEventListener('DOMContentLoaded', () => {
    // Cek auth, jika belum login, kembalikan ke login.html
    fetch('login_check_auth.php', { cache: 'no-store' })
        .then(res => res.json())
        .then(auth => {
            if(!auth.logged_in) {
                window.location.href = 'login.html';
            } else {
                window.userPrivilege = auth.privilege ? auth.privilege.toLowerCase().trim() : '';
                document.body.style.display = 'block'; // Tampilkan konten jika auth
                renderProjects();
            }
        })
        .catch(err => {
            console.error('Auth error:', err);
            window.location.href = 'login.html';
        });

    const projectContainer = document.getElementById('project-container');
    const uploadModal = document.getElementById('upload-modal');
    const closeModal = document.getElementById('close-modal');
    const uploadForm = document.getElementById('upload-form');

    // Buat kotak khusus "Add New Project" di awal list
    const createAddCard = () => {
        const addCard = document.createElement('div');
        addCard.className = 'project-card add-project-card';
        addCard.innerHTML = `
            <div class="plus-icon">+</div>
            <h3>New Project</h3>
        `;
        addCard.addEventListener('click', () => {
            // Tampilkan modal upload
            uploadModal.classList.remove('hidden');
        });
        projectContainer.appendChild(addCard);
    };

    // Fungsi untuk merender daftar project dari database
    const renderProjects = () => {
        projectContainer.innerHTML = '';
        if (window.userPrivilege === 'designer') {
            createAddCard();
        }

        fetch('dashboard_list_projects.php')
            .then(res => res.json())
            .then(response => {
                if (response.status === 'success') {
                    const projects = response.data;
                    projects.forEach(project => {
                        const card = document.createElement('div');
                        card.className = 'project-card';

                        card.innerHTML = `
                            <h3>${project.name}</h3>
                        `;

                        // Ketika kartu project diklik, arahkan user ke halaman web 3D viewer
                        card.addEventListener('click', () => {
                            const fileParam = project.project_file ? `&file=${encodeURIComponent(project.project_file)}` : '';
                            window.location.href = `information.html?project=${project.id}${fileParam}`;
                        });

                        projectContainer.appendChild(card);
                    });
                } else {
                    console.error('Gagal mengambil data:', response.message);
                }
            })
            .catch(err => console.error('Error fetching projects:', err));
    };

    // renderProjects dipanggil didalam login_check_auth.php promise
    
    // --- LOGIKA FORM & MODAL ---
    
    // Tutup modal ketika tombol X di klik
    closeModal.addEventListener('click', () => {
        uploadModal.classList.add('hidden');
    });

    // Menutup modal jika area gelap (overlay) di klik
    uploadModal.addEventListener('click', (event) => {
        if (event.target === uploadModal) {
            uploadModal.classList.add('hidden');
        }
    });

    // Menangani aksi upload/form di submit
    uploadForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Mencegah reload halaman
        
        const fileInput = document.getElementById('project-file');
        const projectName = document.getElementById('project-name').value;
        const btnUpload = document.getElementById('btn-upload');
        const file = fileInput.files[0];
        
        if (file && projectName) {
            const originalText = btnUpload.innerText;
            btnUpload.innerText = 'Creating...';
            btnUpload.disabled = true;

            const formData = new FormData();
            formData.append('projectName', projectName);
            formData.append('projectFile', file);

            fetch('dashboard_create_project.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text()) // Ambil respon sebagai text dulu untuk debugging
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.status === 'success') {
                        alert(`Upload berhasil! File tersimpan di folder: ${data.folder}`);
                        
                        // Sembunyikan form dan reset isinya
                        uploadModal.classList.add('hidden');
                        uploadForm.reset();

                        // Refresh daftar project
                        renderProjects();
                        
                    } else if (data.message === 'Unauthorized') {
                        // Sesi telah habis, kembalikan ke login
                        alert("Sesi Anda telah berakhir. Silakan login kembali.");
                        window.location.href = 'login.html';
                    } else {
                        alert("Gagal membuat project: " + (data.message || 'Terjadi kesalahan sistem.'));
                    }
                } catch (e) {
                    console.error("Gagal parsing JSON. Raw Response dari PHP:", text);
                    alert("Error dari Server PHP:\n" + text.substring(0, 200));
                }
            })
            .catch(err => {
                console.error('Error saat upload:', err);
                alert('Terdapat masalah pada koneksi jaringan atau server mati.');
            })
            .finally(() => {
                btnUpload.innerText = originalText;
                btnUpload.disabled = false;
            });
        }
    });
});
