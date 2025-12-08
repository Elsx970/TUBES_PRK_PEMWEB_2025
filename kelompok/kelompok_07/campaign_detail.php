<?php
require_once 'config.php';
$campaign_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Campaign - CareU</title>
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
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600">← Kembali</a>
                    <?php if (isLoggedIn()): ?>
                        <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div id="campaignDetail" class="bg-white rounded-lg shadow-md p-8">
            <!-- Content will be loaded via AJAX -->
            <div class="text-center py-8">
                <p class="text-gray-600">Memuat detail campaign...</p>
            </div>
        </div>

        <!-- Donation Modal -->
        <div id="donationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
                <h2 class="text-2xl font-bold mb-4">Buat Donasi</h2>
                <form id="donationForm" class="space-y-4">
                    <input type="hidden" id="donationCampaignId" name="campaign_id">
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Nominal Donasi (Rp)</label>
                        <input type="number" id="donationAmount" name="amount" min="10000" step="1000" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Metode Pembayaran</label>
                        <select name="payment_method" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">Upload Bukti Pembayaran</label>
                        <input type="file" name="proof_image" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" name="is_anonymous" id="isAnonymous" class="mr-2">
                        <label for="isAnonymous" class="text-gray-700">Tampilkan sebagai Anonim</label>
                    </div>
                    
                    <div class="flex space-x-4">
                        <button type="submit" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700">
                            Donasi
                        </button>
                        <button type="button" onclick="closeDonationModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        const campaignId = <?php echo $campaign_id; ?>;
        
        document.addEventListener('DOMContentLoaded', () => {
            loadCampaignDetail(campaignId);
        });

        function openDonationModal() {
            <?php if (!isLoggedIn()): ?>
                alert('Silakan login terlebih dahulu untuk melakukan donasi');
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            document.getElementById('donationCampaignId').value = campaignId;
            document.getElementById('donationModal').classList.remove('hidden');
        }

        function closeDonationModal() {
            document.getElementById('donationModal').classList.add('hidden');
            document.getElementById('donationForm').reset();
        }

        document.getElementById('donationForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'create');
            
            try {
                const response = await fetch('api/donations.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Donasi berhasil! Menunggu verifikasi admin.');
                    closeDonationModal();
                    loadCampaignDetail(campaignId);
                } else {
                    alert(data.message || 'Donasi gagal');
                }
            } catch (error) {
                alert('Terjadi kesalahan');
            }
        });
    </script>
</body>
</html>