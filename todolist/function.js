document.addEventListener('DOMContentLoaded', function() {
    // Inisialisasi modal task
    const taskModal = document.getElementById('task-modal');
    const closeTaskModal = document.querySelector('#task-modal .close');
    
    // Inisialisasi modal kategori
    const categoryModal = document.getElementById('category-modal');
    const closeCategoryModal = document.querySelector('#category-modal .close');
    
    // Event listener untuk tombol tambah task
    document.getElementById('add-task-btn').addEventListener('click', function() {
        openTaskModal();
    });
    
    // Event listener untuk tombol edit task
    document.querySelectorAll('.edit-task').forEach(btn => {
        btn.addEventListener('click', function() {
            const taskId = this.closest('.task-item').getAttribute('data-task-id');
            openTaskModal(taskId);
        });
    });
    
    // Event listener untuk tombol tambah kategori
    document.getElementById('add-category-btn').addEventListener('click', function() {
        document.getElementById('category-modal-title').textContent = 'Tambah Kategori Baru';
        document.querySelector('#category-form input[name="action"]').value = 'add_category';
        document.getElementById('category-id').value = '';
        document.getElementById('category-form').reset();
        categoryModal.style.display = 'block';
    });
    
    // Event listener untuk tombol close
    closeTaskModal.addEventListener('click', closeModal);
    closeCategoryModal.addEventListener('click', closeModal);
    
    // Event listener untuk tombol cancel
    document.getElementById('cancel-task').addEventListener('click', closeModal);
    document.getElementById('cancel-category').addEventListener('click', closeModal);
    
    // Close modal ketika klik di luar
    window.addEventListener('click', function(event) {
        if (event.target === taskModal) {
            closeModal();
        }
        if (event.target === categoryModal) {
            closeModal();
        }
    });
    
    // Handle form submission
    document.getElementById('task-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this);
    });
    
    document.getElementById('category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm(this);
    });
});

function openTaskModal(taskId = null) {
    const modal = document.getElementById('task-modal');
    const form = document.getElementById('task-form');
    const modalTitle = document.getElementById('task-modal-title');
    const actionInput = document.querySelector('#task-form input[name="action"]');
    
    if (taskId) {
        // Mode edit
        fetch(`get_task.php?id=${taskId}`)
            .then(response => response.json())
            .then(task => {
                if (task.error) {
                    alert(task.error);
                    return;
                }
                
                modalTitle.textContent = 'Edit Tugas';
                actionInput.value = 'update_task';
                document.getElementById('task-id').value = task.id;
                document.getElementById('task-title').value = task.title;
                document.getElementById('task-description').value = task.description;
                document.getElementById('task-due-date').value = task.due_date;
                document.getElementById('task-priority').value = task.priority;
                document.getElementById('task-status').value = task.status;
                document.getElementById('task-category').value = task.category;
                
                modal.style.display = 'block';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat data tugas');
            });
    } else {
        // Mode tambah baru
        modalTitle.textContent = 'Tambah Tugas Baru';
        actionInput.value = 'add_task';
        document.getElementById('task-id').value = '';
        form.reset();
        modal.style.display = 'block';
    }
}

function closeModal() {
    document.getElementById('task-modal').style.display = 'none';
    document.getElementById('category-modal').style.display = 'none';
}

function submitForm(form) {
    const formData = new FormData(form);
    
    fetch('homepage.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        } else {
            alert('Gagal menyimpan data');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    });
}

function toggleTaskStatus(taskId, isChecked) {
    const status = isChecked ? 'selesai' : 'belum_selesai';
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('task_id', taskId);
    formData.append('current_status', isChecked ? 'belum_selesai' : 'selesai');
    
    fetch('homepage.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        }
    });
}

function deleteTask(taskId) {
    if (confirm('Apakah Anda yakin ingin menghapus tugas ini?')) {
        const formData = new FormData();
        formData.append('action', 'delete_task');
        formData.append('task_id', taskId);
        
        fetch('homepage.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
}

function deleteCategory(categoryId) {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
        const formData = new FormData();
        formData.append('action', 'delete_category');
        formData.append('category_id', categoryId);
        
        fetch('homepage.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                window.location.reload();
            }
        });
    }
}

document.getElementById('filter-kategori').addEventListener('change', function() {
    applyFilters();
});

document.getElementById('filter-priority').addEventListener('change', function() {
    applyFilters();
});

function applyFilters() {
    const category = document.getElementById('filter-kategori').value;
    const priority = document.getElementById('filter-priority').value;
    
    
    window.location.href = `homepage.php?category=${category}&priority=${priority}`;
}

// Fungsi untuk menerapkan semua filter
function applyFilters() {
    const category = document.getElementById('filter-kategori').value;
    const priority = document.getElementById('filter-priority').value;
    
    // Redirect dengan parameter filter
    window.location.href = `homepage.php?category=${category}&priority=${priority}`;
}

// Hapus duplikasi event listener dan fungsi applyFilters
document.addEventListener('DOMContentLoaded', function() {

    // Handle form submission
    const filterForm = document.querySelector('.filter-bar');
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault(); // Penting: cegah form submit default
        applyFilters();
    });

    // Fungsi applyFilters yang diperbarui
    window.applyFilters = function() {
        const category = document.getElementById('filter-kategori').value;
        const priority = document.getElementById('filter-priority').value;
        
        // Bangun URL dengan parameter yang ada
        const url = new URL(window.location.href);
        url.searchParams.set('category', category);
        url.searchParams.set('priority', priority);
        
        // Pertahankan hash jika ada
        window.location.href = url.toString();
    };
});