import * as THREE from 'three';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';

// Setup basic elements
const container = document.getElementById('canvas-container');
const infoPanel = document.getElementById('info-panel');
const panelTitle = document.getElementById('panel-title');
const closeBtn = document.getElementById('close-btn');

let userPrivilege = '';

// Cek Auth sebelum merender canvas 3D
fetch('check_auth.php', { cache: 'no-store' })
    .then(res => res.json())
    .then(auth => {
        if(!auth.logged_in) {
             window.location.href = 'login.html';
        } else {
             document.body.style.display = 'block'; // Tampilkan usai terverifikasi
             userPrivilege = auth.privilege;
             
             // Pastikan elemen role-label ada dan diset text-nya
             const roleLabel = document.getElementById('role-label');
             if(roleLabel) {
                 roleLabel.innerText = `Login as ${auth.privilege}`;
                 roleLabel.style.display = 'block'; // Tampilkan
             }
        }
    })
    .catch(err => {
        console.error('Auth error:', err);
        window.location.href = 'login.html';
    });

let projectData = {}; // Menyimpan data overview project
let partData = []; // Menyimpan let data parts

// Ambil Project ID dan File dari URL
const urlParams = new URLSearchParams(window.location.search);
const projectId = urlParams.get('project');
const projectFileParam = urlParams.get('file');

if (projectId) {
    // Ambil data DB
    fetch(`get_project_data.php?project_id=${projectId}`)
        .then(res => res.json())
        .then(json => {
            if (json.status === 'success') {
                partData = json.data;
                projectData = json.project || {};
                showEmptyPartInfo(); // Langsung render part list ke panel kanan
            } else {
                console.error("Gagal load dari DB:", json.message);
            }
        })
        .catch(err => console.error("Error validasi DB:", err));
}

// Akhir blok project data fetch

const scene = new THREE.Scene();
scene.background = new THREE.Color(0x2a2a2a);

// Camera Setup
const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
camera.position.set(5, 5, 5); // Sesuaikan default posisi kamera

// Renderer Setup
const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
renderer.setSize(window.innerWidth, window.innerHeight);
renderer.setPixelRatio(window.devicePixelRatio);
renderer.shadowMap.enabled = true; // optional
container.appendChild(renderer.domElement);

// Orbit Controls (sehingga user bisa putar, zoom, dan pan modelnya)
const controls = new OrbitControls(camera, renderer.domElement);
controls.enableDamping = true; 
controls.dampingFactor = 0.05;

// Pencahayaan (Lighting)
const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
scene.add(ambientLight);

const directionalLight = new THREE.DirectionalLight(0xffffff, 1);
directionalLight.position.set(10, 20, 10);
scene.add(directionalLight);

const hemiLight = new THREE.HemisphereLight(0xffffff, 0x444444, 0.6);
hemiLight.position.set(0, 20, 0);
scene.add(hemiLight);

// Group untuk menaruh model biar mudah dimanipulasi rotasinya
const modelGroup = new THREE.Group();
scene.add(modelGroup);

// Raycaster Setup buat mendeteksi klik (Raycasting)
const raycaster = new THREE.Raycaster();
const mouse = new THREE.Vector2();

// Load GLB Model
const loader = new GLTFLoader();

// Ganti 'assets/model.glb' dengan parameter path file atau path fallback
const MODEL_PATH = projectFileParam ? projectFileParam : 'assets/model.glb';

loader.load(
    MODEL_PATH,
    (gltf) => {
        const model = gltf.scene;

        // Traverse mesh-nya dan masukkan default value
        let partIndex = 1;
        model.traverse((child) => {
            if (child.isMesh) {
                // Mengaktifkan kemampuan agar model bisa memantulkan cahaya dsb
                child.castShadow = true;
                child.receiveShadow = true;

                // Coba cocokkan terlebih dahulu dengan data base (partData)
                let dbPart = null;
                if (Array.isArray(partData)) {
                    dbPart = partData.find(p => p.part_name === child.name);
                }

                if (dbPart) {
                    // Gabungkan data DB ke dalam userData Mesh
                    Object.assign(child.userData, dbPart);
                } else if (!child.userData.part_number) {
                    // Jika tidak ada di DB, berikan default value
                    Object.assign(child.userData, {
                        project_id: projectId || '',
                        part_number: `PN-${projectId || '0'}-${1000 + partIndex}`,
                        part_name: child.name || `Part ${partIndex}`,
                        created_by: '',
                        checked_by: '',
                        approved_by: '',
                        revision: '',
                        material: '',
                        manufacturing_process: '',
                        progress: '',
                        note: ''
                    });
                }
                
                // Simpan material asli untuk keperluan highlight
                child.userData.originalMaterial = child.material;
                
                partIndex++;
            }
        });

        // Pastikan model berada di pusat
        const box = new THREE.Box3().setFromObject(model);
        const center = box.getCenter(new THREE.Vector3());
        model.position.sub(center); // Meminimalisir posisi yang jauh 
        
        // Auto adjust kemera supaya melihat sekeliling model dengan proper (optional)
        const size = box.getSize(new THREE.Vector3()).length();
        camera.position.set(center.x, center.y + size * 0.5, center.z + size);
        camera.lookAt(center);

        modelGroup.add(model);
        console.log('Model loaded successfully!');
    },
    (xhr) => {
        console.log(`Loading model... ${Math.round((xhr.loaded / xhr.total) * 100)}%`);
    },
    (error) => {
        console.error('An error happened while loading GLB:', error);
        // Fallback: kita tampilkan kubus untuk test jika file .glb belum ada
        const geom = new THREE.BoxGeometry(2, 2, 2);
        const mat = new THREE.MeshStandardMaterial({ color: 0x00ff00 });
        const cube = new THREE.Mesh(geom, mat);
        cube.userData = {
            project_id: projectId || '',
            part_number: 'PN-9999',
            part_name: 'Fallback Cube',
            created_by: '',
            checked_by: '',
            approved_by: '',
            revision: '',
            material: '',
            manufacturing_process: '',
            progress: '',
            note: ''
        };
        modelGroup.add(cube);
    }
);

// Window Resize Handling
window.addEventListener('resize', () => {
    renderer.setSize(window.innerWidth, window.innerHeight);
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
});

// Penanganan Klik (Click Event Listener)
window.addEventListener('pointerdown', onPointerDown, false);

let highlightedObject = null;
// Buat material highlight khusus (contoh: warna oranye cerah transparan/emissive)
const highlightMaterial = new THREE.MeshStandardMaterial({
    color: 0xffaa00,
    emissive: 0x552200,
    roughness: 0.2,
    metalness: 0.8
});

function applyHighlight(object) {
    // Reset object sebelumnya jika ada
    if (highlightedObject) {
        highlightedObject.material = highlightedObject.userData.originalMaterial;
    }
    // Set material baru ke object yang di klik (jika itu adalah tipe mesh)
    if (object.isMesh) {
        object.material = highlightMaterial;
        highlightedObject = object;
    }
}

function removeHighlight() {
    if (highlightedObject) {
        highlightedObject.material = highlightedObject.userData.originalMaterial;
        highlightedObject = null;
    }
}

function onPointerDown(event) {
    // Mencegah klik dari elemen UI/panel terdeteksi sebagai klik di area kosong
    if (event.target !== renderer.domElement) return;

    // Hitung posisi mouse normalized (-1 sampai +1)
    mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
    mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;

    // Lakukan raycast dari kamera ke posisi kursor
    raycaster.setFromCamera(mouse, camera);

    // Hitung intersect pada modelGroup
    const intersects = raycaster.intersectObjects(modelGroup.children, true);

    if (intersects.length > 0) {
        // Objek pertama yang disentuh raycaster
        const selectedObject = intersects[0].object;

        if (selectedObject) {
            applyHighlight(selectedObject);
            
            let matchedPart = null;
            if (Array.isArray(partData)) {
                // Selalu pastikan data dari DB ter-update, jika ada
                matchedPart = partData.find(p => p.part_name === selectedObject.name);
            }

            // Gunakan gabungan userData bawaan dengan up-to-date data di DB
            const finalData = Object.assign({}, selectedObject.userData, matchedPart || {});
            
            showInfo(finalData);
        }
    } else {
        // Jika klik area kosong di kanvas, sembunyikan panel
        removeHighlight();
        showEmptyPartInfo();
    }
}

// Menampilkan Sidebar/Panel Tabel
function showInfo(data, isProject = false) {
    if (panelTitle) {
        panelTitle.innerText = isProject ? 'Project Information' : 'Part Information';
    }

    const tbody = document.getElementById('info-table-body');
    if (!tbody) { return; }

    tbody.innerHTML = ''; // Kosongkan isi table
    
    if (isProject) {
        const fields = [
            { key: 'project_id', label: 'Project ID' },
            { key: 'project_name', label: 'Project Name' },
            { key: 'project_leader', label: 'Project Leader' },
            { key: 'revision', label: 'Revision' }
        ];
        fields.forEach(f => {
            tbody.innerHTML += `<tr><th>${f.label}</th><td>${data[f.key] || '-'}</td></tr>`;
        });
        
        // Mode Project -> Tombol kembali jadi Close
        if (userPrivilege === 'Operator') {
            closeBtn.style.display = 'none'; // Sembunyikan untuk Operator
        } else {
            closeBtn.style.display = 'inline-block';
            closeBtn.innerText = 'Close';
            closeBtn.onclick = () => {
                removeHighlight();
                showEmptyPartInfo();
            };
        }

    } else {
        const fields = [
            { key: 'project_id', label: 'Project ID' },
            { key: 'part_number', label: 'Part Number' },
            { key: 'part_name', label: 'Part Name' },
            { key: 'created_by', label: 'Created By' },
            { key: 'checked_by', label: 'Checked By' },
            { key: 'approved_by', label: 'Approved By' },
            { key: 'revision', label: 'Revision' },
            { key: 'manufacturing_process', label: 'Manufacturing Process' },
            { key: 'material', label: 'Material' },
            { key: 'progress', label: 'Progress' }
        ];
        
        fields.forEach(f => {
            if (userPrivilege === 'Designer' && f.key !== 'project_id') {
                if (f.key === 'part_name' || f.key === 'part_number') {
                    // Buat part_name dan part_number secara tegas disable agar identitas kaitan tak diubah user
                    tbody.innerHTML += `<tr><th>${f.label}</th><td><input type="text" id="edit-${f.key}" data-key="${f.key}" value="${data[f.key] || ''}" style="width:100%; box-sizing:border-box; background-color:#eee; color:#666; cursor:not-allowed;" disabled></td></tr>`;
                } else if (f.key === 'progress') {
                    // Jadikan progress sebagai teks flat / label untuk agar tidak diubah oleh Designer
                    tbody.innerHTML += `<tr><th>${f.label}</th><td>${data[f.key] || '-'}</td></tr>`;
                } else {
                    const inputType = (f.key === 'revision') ? 'date' : 'text';
                    tbody.innerHTML += `<tr><th>${f.label}</th><td><input type="${inputType}" id="edit-${f.key}" data-key="${f.key}" value="${data[f.key] || ''}" style="width:100%; box-sizing:border-box;"></td></tr>`;
                }
            } else if (userPrivilege === 'Operator' && f.key === 'progress') {
                // Operator hanya bisa edit Progress
                tbody.innerHTML += `<tr><th>${f.label}</th><td><input type="text" id="edit-${f.key}" data-key="${f.key}" value="${data[f.key] || ''}" style="width:100%; box-sizing:border-box;"></td></tr>`;
            } else {
                tbody.innerHTML += `<tr><th>${f.label}</th><td>${data[f.key] || '-'}</td></tr>`;
            }
        });

        // Tambahan input multiline untuk Note khusus Operator & Manager
        if (userPrivilege === 'Operator' || userPrivilege === 'Manager') {
            const isNoteReadonly = (userPrivilege === 'Operator') ? 'readonly' : ''; // Jika manager, bebas edit note; Operator read-only atau sesuai konteks sebelumnya? User bilang Manager *hanya* catatan.
            // Sesuai dialog sebelumnya Operator bisa edit note. Saya bebaskan untuk keduanya.
            
            tbody.innerHTML += `<tr><th>Note</th><td><textarea id="edit-note" data-key="note" style="width:100%; box-sizing:border-box; resize:vertical;" rows="3">${data.note || ''}</textarea></td></tr>`;
        }

        closeBtn.style.display = 'inline-block'; // Selalu tampilkan jika berada di part info
        
        if (userPrivilege === 'Designer' || userPrivilege === 'Operator' || userPrivilege === 'Manager') {
            closeBtn.innerText = 'Update';
            closeBtn.onclick = () => {
                const payload = {
                    project_id: data.project_id,
                    part_name: data.part_name,
                    part_number: document.getElementById('edit-part_number') ? document.getElementById('edit-part_number').value : data.part_number,
                    created_by: document.getElementById('edit-created_by') ? document.getElementById('edit-created_by').value : data.created_by,
                    checked_by: document.getElementById('edit-checked_by') ? document.getElementById('edit-checked_by').value : data.checked_by,
                    approved_by: document.getElementById('edit-approved_by') ? document.getElementById('edit-approved_by').value : data.approved_by,
                    revision: document.getElementById('edit-revision') ? document.getElementById('edit-revision').value : data.revision,
                    manufacturing_process: document.getElementById('edit-manufacturing_process') ? document.getElementById('edit-manufacturing_process').value : data.manufacturing_process,
                    material: document.getElementById('edit-material') ? document.getElementById('edit-material').value : data.material,
                    progress: document.getElementById('edit-progress') ? document.getElementById('edit-progress').value : data.progress,
                    note: document.getElementById('edit-note') ? document.getElementById('edit-note').value : data.note
                };

                fetch('update_part_data.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(out => {
                    if(out.status === 'success') {
                        alert('Data successfully updated!');
                        // Update object lokal dan masukkan kembali ke `partData` cache
                        Object.assign(data, payload);
                        if (Array.isArray(partData)) {
                            let idx = partData.findIndex(p => p.part_name === data.part_name);
                            if (idx > -1) {
                                partData[idx] = Object.assign({}, partData[idx], payload);
                            } else {
                                partData.push(Object.assign({}, data));
                            }
                        }
                        showInfo(data, false);
                    } else {
                        alert('Update failed: ' + out.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error updating data.');
                });
            };
        } else {
            closeBtn.innerText = 'Close';
            closeBtn.onclick = () => {
                removeHighlight();
                showEmptyPartInfo();
            };
        }
    }

    infoPanel.classList.remove('hidden');
}

function showEmptyPartInfo() {
    if (panelTitle) {
        panelTitle.innerText = 'Parts List';
    }

    const tbody = document.getElementById('info-table-body');
    if (!tbody) { return; }

    tbody.innerHTML = ''; // Kosongkan isi table
    
    // Tampilkan data_table dalam bentuk list pada tabel info (karena 3D element belum diklik)
    if (partData && partData.length > 0) {
        tbody.innerHTML += `<tr><th style="width: auto;">Part Name</th><th style="width: auto;">Material</th><th style="width: auto;">Progress</th></tr>`;
        partData.forEach(p => {
            tbody.innerHTML += `<tr>
                <td style="font-weight: 500">${p.part_name || '-'}</td>
                <td>${p.material || '-'}</td>
                <td>${p.progress || '-'}</td>
            </tr>`;
        });
    } else {
        tbody.innerHTML += `<tr><td colspan="3" style="text-align:center;">No parts found in DB or Loading...</td></tr>`;
    }

    closeBtn.style.display = 'none'; // Sembunyikan tombol Update / Close saat kosong
    infoPanel.classList.remove('hidden');
}

// Interactivity pada tombol di Panel dikendalikan via closeBtn.onclick di showInfo()

// Panggil showProjectInfo saat awal mula
window.addEventListener('DOMContentLoaded', () => {
    showEmptyPartInfo();
});

// Main Game/Render Loop
function animate() {
    requestAnimationFrame(animate);
    controls.update();
    renderer.render(scene, camera);
}

// Mulai Loop Animasi
animate();





