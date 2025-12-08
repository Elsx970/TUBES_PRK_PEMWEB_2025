<?php
require_once 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Donasi - CareU</title>
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
                    <a href="index.php" class="text-gray-700 hover:text-indigo-600">Beranda</a>
                    <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <button onclick="logout()" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">Logout</button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-bold mb-6">Riwayat Donasi Saya</h1>
        
        <div id="donationsList" class="space-y-4">
            <!-- Donations will be loaded here -->
            <div class="text-center py-8">
                <p class="text-gray-600">Memuat riwayat donasi...</p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadDonationHistory();
        });

        async function loadDonationHistory() {
            const container = document.getElementById('donationsList');
            
            try {
                const response = await fetch('api/donations.php?action=history');
                const data = await response.json();
                
                if (data.success && data.donations.length > 0) {
                    container.innerHTML = data.donations.map(donation => {
                        const statusColors = {
                            'pending': 'bg-yellow-100 text-yellow-800',
                            'verified': 'bg-green-100 text-green-800',
                            'rejected': 'bg-red-100 text-red-800'
                        };
                        
                        const statusText = {
                            'pending': 'Menunggu Verifikasi',
                            'verified': 'Terverifikasi',
                            'rejected': 'Ditolak'
                        };
                        
                        return `
                            <div class="bg-white rounded-lg shadow-md p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold mb-2">${donation.campaign_title}</h3>
                                        <p class="text-gray-600 mb-2">Nominal: <span class="font-semibold text-indigo-600">Rp ${parseInt(donation.amount).toLocaleString('id-ID')}</span></p>
                                        <p class="text-sm text-gray-500">${new Date(donation.created_at).toLocaleString('id-ID')}</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColors[donation.status]}">
                                        ${statusText[donation.status]}
                                    </span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span>Metode: ${donation.payment_method === 'qris' ? 'QRIS' : 'Transfer Bank'}</span>
                                    ${donation.is_anonymous ? '<span>Anonim</span>' : ''}
                                </div>
                                ${donation.proof_image ? `<img src="${donation.proof_image}" class="mt-4 max-w-xs rounded-lg">` : ''}
                            </div>
                        `;
                    }).join('');
                } else {
                    container.innerHTML = '<div class="text-center py-8 text-gray-600">Belum ada donasi</div>';
                }
            } catch (error) {
                container.innerHTML = '<div class="text-center py-8 text-red-600">Error memuat riwayat donasi</div>';
            }
        }

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