<?php
require_once '../config.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - CareU</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom scrollbar untuk modal */
        #campaignModal .overflow-y-auto::-webkit-scrollbar {
            width: 8px;
        }
        #campaignModal .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        #campaignModal .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        #campaignModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-6">
                    <a href="../index.php" class="text-2xl font-bold text-indigo-600">CareU</a>
                    <span class="text-gray-500">|</span>
                    <span class="text-gray-700 font-medium">Dashboard Admin</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="../index.php" class="text-gray-700 hover:text-indigo-600">Lihat Website</a>
                    <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <button onclick="logout()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-gray-600 text-sm mb-2">Total Campaign Aktif</h3>
                <p id="totalCampaigns" class="text-3xl font-bold text-indigo-600">0</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-gray-600 text-sm mb-2">Total Donasi Masuk</h3>
                <p id="totalDonations" class="text-3xl font-bold text-green-600">Rp 0</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-gray-600 text-sm mb-2">Donasi Pending</h3>
                <p id="pendingDonations" class="text-3xl font-bold text-yellow-600">0</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-gray-600 text-sm mb-2">Total Donatur</h3>
                <p id="totalDonors" class="text-3xl font-bold text-purple-600">0</p>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="showTab('campaigns')" id="tab-campaigns" class="tab-button active px-6 py-4 text-sm font-medium border-b-2 border-indigo-600 text-indigo-600">
                        Manajemen Campaign
                    </button>
                    <button onclick="showTab('donations')" id="tab-donations" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Manajemen Donasi
                    </button>
                    <button onclick="showTab('statistics')" id="tab-statistics" class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Statistik
                    </button>
                </nav>
            </div>
        </div>

        <!-- Campaigns Tab -->
        <div id="content-campaigns" class="tab-content">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold">Daftar Campaign</h2>
                    <button onclick="openCampaignModal()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                        + Buat Campaign Baru
                    </button>
                </div>
                <div id="campaignsList" class="space-y-4">
                    <!-- Campaigns will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Donations Tab -->
        <div id="content-donations" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-4">Daftar Donasi</h2>
                <div id="donationsList" class="space-y-4">
                    <!-- Donations will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Statistics Tab -->
        <div id="content-statistics" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-4">Statistik Donasi</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <canvas id="donationChart"></canvas>
                    </div>
                    <div>
                        <canvas id="campaignChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaign Modal -->
    <div id="campaignModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto py-8">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 my-auto max-h-[90vh] overflow-y-auto">
            <h2 id="modalTitle" class="text-2xl font-bold mb-4">Buat Campaign Baru</h2>
            <form id="campaignForm" class="space-y-4" novalidate>
                <input type="hidden" id="campaignId" name="id">
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Judul Campaign</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Deskripsi</label>
                    <textarea name="description" rows="4" required
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Latar Belakang Masalah</label>
                    <textarea name="background" rows="3"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Target Dana (Rp)</label>
                        <input type="number" name="target_amount" min="100000" step="10000" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Deadline</label>
                        <input type="date" name="deadline" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Kategori</label>
                    <select name="category"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Pilih Kategori</option>
                        <option value="pendidikan">Pendidikan</option>
                        <option value="kesehatan">Kesehatan</option>
                        <option value="bencana">Bencana</option>
                        <option value="sosial">Sosial</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Gambar Campaign</label>
                    <input type="file" name="image_file" id="campaignImageFile" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF, WEBP (Maksimal 5MB)</p>
                    <div id="currentImagePreview" class="mt-2 hidden">
                        <p class="text-sm text-gray-600 mb-2">Gambar saat ini:</p>
                        <img id="currentImage" src="" alt="Current image" class="max-w-xs rounded-lg border border-gray-300">
                        <p class="text-xs text-gray-500 mt-1">Upload gambar baru untuk mengganti</p>
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Foto QRIS</label>
                    <input type="file" name="qris_file" id="campaignQrisFile" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF, WEBP (Maksimal 5MB)</p>
                    <div id="currentQrisPreview" class="mt-2 hidden">
                        <p class="text-sm text-gray-600 mb-2">QRIS saat ini:</p>
                        <img id="currentQris" src="" alt="Current QRIS" class="max-w-xs rounded-lg border border-gray-300">
                        <p class="text-xs text-gray-500 mt-1">Upload QRIS baru untuk mengganti</p>
                    </div>
                </div>
                
                <div class="flex space-x-4">
                    <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700">
                        Simpan
                    </button>
                    <button type="button" id="cancelCampaignBtn" onclick="closeCampaignModal(); return false;" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/admin.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboardStats();
            loadCampaigns();
            loadDonations();
        });

        function showTab(tab) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-indigo-600', 'text-indigo-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            
            // Show selected tab
            document.getElementById(`content-${tab}`).classList.remove('hidden');
            const button = document.getElementById(`tab-${tab}`);
            button.classList.add('active', 'border-indigo-600', 'text-indigo-600');
            button.classList.remove('border-transparent', 'text-gray-500');
            
            if (tab === 'statistics') {
                loadStatistics();
            }
        }

        async function logout() {
            const formData = new FormData();
            formData.append('action', 'logout');
            
            try {
                const response = await fetch('../auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (data.success) {
                    window.location.href = '../index.php';
                }
            } catch (error) {
                console.error('Logout error:', error);
            }
        }
    </script>
</body>
</html>
