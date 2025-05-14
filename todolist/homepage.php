<?php
session_start();

// Jika belum login, redirect ke login page
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

require_once 'config.php';

// Fungsi untuk mendapatkan semua tugas dari database
function getAllTasks($conn, $userId) {
    $tasks = array();
    $query = "SELECT * FROM task WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    return $tasks;
}
function addTask($conn, $userId, $title, $description, $dueDate, $priority, $status, $category) {
    try {
        // Validation and insertion logic
        $query = "INSERT INTO task (user_id, title, description, due_date, priority, status, category, created_at, update_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issssss", $userId, $title, $description, $dueDate, $priority, $status, $category);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        return true;
    } catch (Exception $e) {
        error_log("Error adding task: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        return false;
    }
}

function getTaskById($conn, $taskId) {
    $query = "SELECT * FROM task WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Fungsi updateTask()
function updateTask($conn, $taskId, $title, $description, $dueDate, $priority, $status, $category) {
    $query = "UPDATE task SET title = ?, description = ?, due_date = ?, priority = ?, status = ?, category = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssi", $title, $description, $dueDate, $priority, $status, $category, $taskId);
    
    return $stmt->execute();
}

// Fungsi deleteTask()
function deleteTask($conn, $taskId) {
    $query = "DELETE FROM task WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $taskId);
    
    return $stmt->execute();
}

// Fungsi untuk mengubah status tugas (selesai/belum)
function toggleTaskStatus($conn, $taskId, $status) {
    $query = "UPDATE task SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $taskId);
    
    return $stmt->execute();
}

// Fungsi untuk mendapatkan semua kategori
function getCategories($conn, $userId) {
    $categories = array();
    $query = "SELECT * FROM categories WHERE user_id = ? OR user_id IS NULL";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    return $categories;
}

// Fungsi untuk menambahkan kategori baru
function addCategory($conn, $userId, $name, $color) {
    try {
        // Validasi input
        if (empty($name)) {
            throw new Exception("Nama kategori tidak boleh kosong");
        }

        // Cek apakah kategori sudah ada
        $checkQuery = "SELECT id FROM categories WHERE name = ? AND (user_id = ? OR user_id IS NULL)";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("si", $name, $userId);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            throw new Exception("Kategori dengan nama tersebut sudah ada");
        }

        $query = "INSERT INTO categories (user_id, name, color) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $userId, $name, $color);

        if (!$stmt->execute()) {
            throw new Exception("Gagal menambahkan kategori: " . $stmt->error);
        }

        return true;
    } catch (Exception $e) {
        error_log($e->getMessage());
        $_SESSION['error'] = $e->getMessage();
        return false;
    }
}

// Fungsi untuk menghapus kategori
function deleteCategory($conn, $categoryId) {
    // Pertama, update semua task yang menggunakan kategori ini ke kategori default
    $updateQuery = "UPDATE task SET category = 'uncategorized' WHERE category = (SELECT name FROM categories WHERE id = ?)";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $categoryId);
    $updateStmt->execute();
    
    // Kemudian hapus kategori
    $deleteQuery = "DELETE FROM categories WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $categoryId);
    
    return $deleteStmt->execute();
}

// Fungsi untuk mendapatkan statistik tugas
function getTaskStatistics($conn, $userId) {
    $stats = array(
        'total' => 0,
        'active' => 0,
        'completed' => 0,
        'urgent' => 0
    );
    
    // Total tugas
    $query = "SELECT COUNT(*) as total FROM task WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['total'] = $row['total'];
    
    // Tugas aktif (belum selesai)
    $query = "SELECT COUNT(*) as active FROM task WHERE user_id = ? AND status != 'selesai'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['active'] = $row['active'];
    
    // Tugas selesai
    $query = "SELECT COUNT(*) as completed FROM task WHERE user_id = ? AND status = 'selesai'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['completed'] = $row['completed'];
    
    // Tugas mendesak (prioritas tinggi)
    $query = "SELECT COUNT(*) as urgent FROM task WHERE user_id = ? AND priority = 'tinggi' AND status != 'selesai'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stats['urgent'] = $row['urgent'];
    
    return $stats;
}

// Fungsi untuk memfilter tugas berdasarkan kriteria
function filterTask($conn, $userId, $category = 'all', $priority = 'all') {
    $tasks = array();
    
    // Query dasar
    $query = "SELECT * FROM task WHERE user_id = ?";
    $params = array($userId);
    $types = "i";
    
    // Filter kategori
    if ($category != 'all') {
        $query .= " AND category = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    // Filter prioritas
    if ($priority != 'all') {
        $query .= " AND priority = ?";
        $params[] = $priority;
        $types .= "s";
    }
    
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    return $tasks;
}

// Mendapatkan ID user dari session
$userId = $_SESSION['user_id'] ?? 0;

// Handle form submission untuk tugas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_task':
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $dueDate = $_POST['due_date'] ?? '';
                $priority = $_POST['priority'] ?? 'sedang';
                $status = $_POST['status'] ?? 'belum_selesai';
                $category = $_POST['category'] ?? 'uncategorized';
                
                if (addTask($conn, $userId, $title, $description, $dueDate, $priority, $status, $category)) {
                    $_SESSION['message'] = 'Tugas berhasil ditambahkan!';
                    
                    // Debugging: Tampilkan semua tugas setelah penambahan
                    $allTasks = getAllTasks($conn, $userId);
                    error_log("Total tasks after addition: " . count($allTasks));
                    foreach ($allTasks as $task) {
                        error_log("Task: " . print_r($task, true));
                    }
                } else {
                    $_SESSION['error'] = 'Gagal menambahkan tugas.';
                }
                break;
                
            case 'update_task':
                $taskId = $_POST['task_id'] ?? 0;
                $title = $_POST['title'] ?? '';
                $description = $_POST['description'] ?? '';
                $dueDate = $_POST['due_date'] ?? '';
                $priority = $_POST['priority'] ?? 'sedang';
                $status = $_POST['status'] ?? 'belum_selesai';
                $category = $_POST['category'] ?? 'uncategorized';
                
                if (updateTask($conn, $taskId, $title, $description, $dueDate, $priority, $status, $category)) {
                    $_SESSION['message'] = 'Tugas berhasil diperbarui!';
                } else {
                    $_SESSION['error'] = 'Gagal memperbarui tugas.';
                }
                break;
                
            case 'delete_task':
                $taskId = $_POST['task_id'] ?? 0;
                
                if (deleteTask($conn, $taskId)) {
                    $_SESSION['message'] = 'Tugas berhasil dihapus!';
                } else {
                    $_SESSION['error'] = 'Gagal menghapus tugas.';
                }
                break;
                
            case 'toggle_status':
                $taskId = $_POST['task_id'] ?? 0;
                $currentStatus = $_POST['current_status'] ?? 'belum_selesai';
                $newStatus = ($currentStatus == 'selesai') ? 'belum_selesai' : 'selesai';
                
                if (toggleTaskStatus($conn, $taskId, $newStatus)) {
                    $_SESSION['message'] = 'Status tugas berhasil diubah!';
                } else {
                    $_SESSION['error'] = 'Gagal mengubah status tugas.';
                }
                break;
                
            case 'add_category':
                $name = $_POST['name'] ?? '';
                $color = $_POST['color'] ?? '#4CAF50';
                
                if (addCategory($conn, $userId, $name, $color)) {
                    $_SESSION['message'] = 'Kategori berhasil ditambahkan!';
                } else {
                    $_SESSION['error'] = 'Gagal menambahkan kategori.';
                }
                break;
                
            case 'delete_category':
                $categoryId = $_POST['category_id'] ?? 0;
                
                if (deleteCategory($conn, $categoryId)) {
                    $_SESSION['message'] = 'Kategori berhasil dihapus!';
                } else {
                    $_SESSION['error'] = 'Gagal menghapus kategori.';
                }
                break;
        }
        
        header("Location: homepage.php");
        exit;
    }
}

// Mendapatkan data untuk ditampilkan
$categories = getCategories($conn, $userId);
$stats = getTaskStatistics($conn, $userId);

// Filter tugas berdasarkan parameter GET
$categoryFilter = $_GET['category'] ?? 'all';
$priorityFilter = $_GET['priority'] ?? 'all';

$tasks = filterTask($conn, $userId, $categoryFilter, $priorityFilter);

function getCategoryColor($categoryName) {
    global $categories;
    foreach ($categories as $category) {
        if ($category['name'] == $categoryName) {
            return $category['color'];
        }
    }
    return '#4CAF50'; // Warna default jika tidak ditemukan
}

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskMaster - Aplikasi To-Do List</title>
    <link rel="stylesheet" href="homepage.css">
</head>
<body>
    <!-- Main App Screen -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">TaskManager</div>
                <div class="user-info">
                    <img src="https://ui-avatars.com/api/?name=User&background=random" alt="User Avatar">
                    <span><?= htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                    <button onclick="window.location.href='logout.php'">logout</button>
                </div>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="main-content">
            <div class="sidebar">
                <h3>Mata Pelajaran</h3>
                <ul class="category-list" id="category-list">
                    <?php foreach ($categories as $category): ?>
                        <li class="category-item" data-category="<?= htmlspecialchars($category['name']) ?>">
                            <span class="category-color" style="background-color: <?= $category['color'] ?>;"></span>
                            <?= htmlspecialchars($category['name']) ?>
                            <?php if ($category['user_id'] != null): ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <button id="add-category-btn" class="btn btn-secondary add-category">Tambah Mata Pelajaran</button>
            </div>

            <div class="content">
                <div class="summary">
                    <h3>Ringkasan</h3>
                    <div class="summary-stats">
                        <div class="stat-item">
                            <h4>Total Tugas</h4>
                            <div class="number" id="total-tasks"><?= $stats['total'] ?></div>
                        </div>
                        <div class="stat-item">
                            <h4>Tugas Aktif</h4>
                            <div class="number" id="active-tasks"><?= $stats['active'] ?></div>
                        </div>
                        <div class="stat-item">
                            <h4>Tugas Selesai</h4>
                            <div class="number" id="completed-tasks"><?= $stats['completed'] ?></div>
                        </div>
                        <div class="stat-item">
                            <h4>Mendesak</h4>
                            <div class="number" id="urgent-tasks"><?= $stats['urgent'] ?></div>
                        </div>
                    </div>
                </div>

                <div class="content-header">
                    <h2>Daftar Tugas</h2>
                    <button id="add-task-btn" class="btn">Tambah Tugas</button>
                </div>

                <form method="get" class="filter-bar">
                <select id="filter-kategori" class="form-control">
                    <option value="all">Semua Mapel</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['name']) ?>" <?= ($categoryFilter == $category['name']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                    
                <select id="filter-priority" class="form-control">
                    <option value="all">Semua Prioritas</option>
                    <option value="tinggi" <?= ($priorityFilter == 'tinggi') ? 'selected' : '' ?>>Prioritas Tinggi</option>
                    <option value="sedang" <?= ($priorityFilter == 'sedang') ? 'selected' : '' ?>>Prioritas Sedang</option>
                    <option value="rendah" <?= ($priorityFilter == 'rendah') ? 'selected' : '' ?>>Prioritas Rendah</option>
                </select>
                    
                </form>

                <div class="tasks-container">
                    <ul class="task-list" id="task-list">
                        <?php if (empty($tasks)): ?>
                            <li class="task-empty">Tidak ada tugas yang ditemukan</li>
                        <?php else: ?>
                            <?php foreach ($tasks as $task): ?>
                                <?php 
                                    
                                    // Tentukan class priority berdasarkan nilai dari database
                                    $priorityClass = '';
                                    switch ($task['priority']) {
                                        case 'tinggi':
                                            $priorityClass = 'priority-high';
                                            break;
                                        case 'sedang':
                                            $priorityClass = 'priority-medium';
                                            break;
                                        case 'rendah':
                                            $priorityClass = 'priority-low';
                                            break;
                                    }
                                    // Format tanggal
                                    $dueDate = date('d M Y', strtotime($task['due_date']));
                                    ?>
                                    <li class="task-item" data-task-id="<?= $task['id'] ?>">
                                        <div class="task-checkbox">
                                            <input type="checkbox" 
                                                <?= $task['status'] == 'selesai' ? 'checked' : '' ?>
                                                onchange="toggleTaskStatus(<?= $task['id'] ?>, this.checked)">
                                        </div>
                                        <div class="task-content">
                                            <div class="task-title <?= $task['status'] == 'selesai' ? 'completed' : '' ?>">
                                                <?= htmlspecialchars($task['title']) ?>
                                            </div>
                                            <div class="task-details">
                                                <span class="task-category" style="background-color: <?= getCategoryColor($task['category']) ?>;">
                                                    <?= htmlspecialchars($task['category']) ?>
                                                </span>
                                                <span class="task-priority <?= $priorityClass ?>">
                                                    <?= ucfirst($task['priority']) ?>
                                                </span>
                                                <span class="task-date">Tenggat: <?= $dueDate ?></span>
                                            </div>
                                        </div>
                                    <div class="task-actions">
                                        <button class="btn btn-small btn-info edit-task" 
                                                onclick="openEditTaskModal(<?= $task['id'] ?>)">Edit</button>
                                        <button class="btn btn-small btn-danger delete-task" 
                                                onclick="deleteTask(<?= $task['id'] ?>)">Hapus</button>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="task-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="task-modal-title">Tambah Tugas Baru</h3>
                <span class="close">&times;</span>
            </div>
            <form id="task-form" method="post" action="homepage.php">
                    <input type="hidden" name="action" id="form-action" value="add_task">
                    <input type="hidden" id="task-id" name="task_id">
                <div class="form-group">
                    <label for="task-title">Judul Tugas</label>
                    <input type="text" id="task-title" name="title" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="task-description">Deskripsi</label>
                    <textarea id="task-description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="task-due-date">Tenggat Waktu</label>
                    <input type="date" id="task-due-date" name="due_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="task-priority">Prioritas</label>
                    <select id="task-priority" name="priority" class="form-control" required>
                        <option value="rendah">Rendah</option>
                        <option value="sedang">Sedang</option>
                        <option value="tinggi">Tinggi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="task-status">Status</label>
                    <select id="task-status" name="status" class="form-control" required>
                        <option value="belum_selesai">Belum Selesai</option>
                        <option value="dalam_proses">Dalam Proses</option>
                        <option value="selesai">Selesai</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="task-category">Kategori</label>
                    <select id="task-category" name="category" class="form-control" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= htmlspecialchars($category['name']) ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancel-task">Batal</button>
                    <button type="submit" class="btn" id="save-task">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Category Modal -->
    <div id="category-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="category-modal-title">Tambah Kategori Baru</h3>
                <span class="close">&times;</span>
            </div>
            <form id="category-form" method="post" action="homepage.php">
                <input type="hidden" name="action" value="add_category">
                <input type="hidden" id="category-id" name="category_id">
                <div class="form-group">
                    <label for="category-name">Nama Kategori</label>
                    <input type="text" id="category-name" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="category-color">Warna</label>
                    <input type="color" id="category-color" name="color" class="form-control" value="#4CAF50">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn" id="save-category">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="function.js"></script>
</body>
</html>