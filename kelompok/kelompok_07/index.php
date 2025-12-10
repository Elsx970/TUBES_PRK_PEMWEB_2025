<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CareU - Platform Crowdfunding</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-bold text-indigo-600">CareU</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isLoggedIn()): ?>
                        <a href="donation_history.php" class="text-gray-700 hover:text-indigo-600">Riwayat Donasi</a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="text-gray-700 hover:text-indigo-600">Dashboard Admin</a>
                        <?php endif; ?>
                        <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <button onclick="logout()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Logout</button>
                    <?php else: ?>
                        <a href="login.php" class="text-gray-700 hover:text-indigo-600">Login</a>
                        <a href="register.php" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl font-bold mb-4">Bersama Membantu, Bersama Peduli</h1>
            <p class="text-xl mb-8">Platform crowdfunding untuk membantu sesama</p>
        </div>
    </div>

    <!-- Filter & Sort -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Urutkan</label>
                    <select id="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="latest">Terbaru</option>
                        <option value="urgent">Paling Mendesak</option>
                        <option value="nearly_reached">Hampir Mencapai Target</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Kategori</label>
                    <select id="filterCategory" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Semua Kategori</option>
                        <option value="pendidikan">Pendidikan</option>
                        <option value="kesehatan">Kesehatan</option>
                        <option value="bencana">Bencana</option>
                        <option value="sosial">Sosial</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button onclick="loadCampaigns()" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Campaign List -->
        <div id="campaignsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Campaigns will be loaded here via AJAX -->
        </div>

        <div id="loading" class="text-center py-8">
            <p class="text-gray-600">Memuat campaign...</p>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        // Load campaigns on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCampaigns();
        });

        async function logout() {
            const formData = new FormData();
            formData.append('action', 'logout');
            
            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = 'index.php';
                }
            } catch (error) {
                console.error('Logout error:', error);
            }
        }
    </script>
</body>
</html>