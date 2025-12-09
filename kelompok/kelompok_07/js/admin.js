// Load dashboard statistics
async function loadDashboardStats() {
    try {
        const response = await fetch('../api/admin.php?action=stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalCampaigns').textContent = data.stats.total_campaigns || 0;
            document.getElementById('totalDonations').textContent = 'Rp ' + (data.stats.total_donations || 0).toLocaleString('id-ID');
            document.getElementById('pendingDonations').textContent = data.stats.pending_donations || 0;
            document.getElementById('totalDonors').textContent = data.stats.total_donors || 0;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

// Load campaigns for admin
async function loadCampaigns() {
    const container = document.getElementById('campaignsList');
    
    try {
        const response = await fetch('../api/admin.php?action=list_campaigns');
        const data = await response.json();
        
        if (data.success && data.campaigns.length > 0) {
            container.innerHTML = data.campaigns.map(campaign => {
                const progress = (campaign.current_amount / campaign.target_amount * 100).toFixed(1);
                const daysLeft = Math.ceil((new Date(campaign.deadline) - new Date()) / (1000 * 60 * 60 * 24));
                
                return `
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold mb-2">${campaign.title}</h3>
                                <p class="text-gray-600 mb-2">${campaign.description.substring(0, 150)}...</p>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 mb-2">
                                    <span>Target: Rp ${parseInt(campaign.target_amount).toLocaleString('id-ID')}</span>
                                    <span>Terkumpul: Rp ${parseInt(campaign.current_amount).toLocaleString('id-ID')}</span>
                                    <span>Progress: ${progress}%</span>
                                    <span>⏰ ${daysLeft > 0 ? daysLeft + ' hari' : 'Berakhir'}</span>
                                </div>
                                <span class="px-2 py-1 rounded text-xs ${campaign.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                    ${campaign.status}
                                </span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="editCampaign(${campaign.id})" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 text-sm">
                                Edit
                            </button>
                            <button onclick="addUpdate(${campaign.id})" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 text-sm">
                                Update
                            </button>
                            <button onclick="addReport(${campaign.id})" class="bg-orange-500 text-white px-4 py-2 rounded hover:bg-orange-600 text-sm">
                                Laporan
                            </button>
                            ${campaign.status === 'active' ? 
                                `<button onclick="closeCampaign(${campaign.id})" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600 text-sm">Tutup</button>` :
                                ''
                            }
                            <button onclick="deleteCampaign(${campaign.id})" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
                                Hapus
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="text-center py-8 text-gray-600">Belum ada campaign</div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="text-center py-8 text-red-600">Error memuat campaign</div>';
    }
}

// Load donations for admin
async function loadDonations() {
    const container = document.getElementById('donationsList');
    
    try {
        const response = await fetch('../api/admin.php?action=list_donations');
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
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex-1">
                                <h3 class="font-semibold mb-2">${donation.campaign_title || 'Campaign'}</h3>
                                <p class="text-gray-600 mb-2">Nominal: <span class="font-semibold text-indigo-600">Rp ${parseInt(donation.amount).toLocaleString('id-ID')}</span></p>
                                <p class="text-sm text-gray-500 mb-2">${new Date(donation.created_at).toLocaleString('id-ID')}</p>
                                <p class="text-sm text-gray-600">Metode: ${donation.payment_method === 'qris' ? 'QRIS' : 'Transfer Bank'}</p>
                                ${donation.is_anonymous ? '<p class="text-sm text-gray-600">Anonim</p>' : ''}
                            </div>
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${statusColors[donation.status]}">
                                ${statusText[donation.status]}
                            </span>
                        </div>
                        ${donation.proof_image ? `
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 mb-2">Bukti Pembayaran:</p>
                                <img src="../${donation.proof_image}" class="max-w-xs rounded-lg border border-gray-200">
                            </div>
                        ` : ''}
                        ${donation.status === 'pending' ? `
                            <div class="flex space-x-2">
                                <button onclick="verifyDonation(${donation.id}, 'verified')" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-sm">
                                    Verifikasi
                                </button>
                                <button onclick="verifyDonation(${donation.id}, 'rejected')" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 text-sm">
                                    Tolak
                                </button>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        } else {
            container.innerHTML = '<div class="text-center py-8 text-gray-600">Belum ada donasi</div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="text-center py-8 text-red-600">Error memuat donasi</div>';
    }
}

// Campaign Modal Functions
function openCampaignModal(id = null) {
    document.getElementById('campaignModal').classList.remove('hidden');
    document.getElementById('campaignForm').reset();
    document.getElementById('campaignId').value = '';
    document.getElementById('modalTitle').textContent = id ? 'Edit Campaign' : 'Buat Campaign Baru';
    
    if (id) {
        editCampaign(id);
    }
}

function closeCampaignModal() {
    document.getElementById('campaignModal').classList.add('hidden');
}

async function editCampaign(id) {
    try {
        const response = await fetch(`../api/admin.php?action=get_campaign&id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            const campaign = data.campaign;
            document.getElementById('campaignId').value = campaign.id;
            document.getElementById('campaignForm').querySelector('[name="title"]').value = campaign.title;
            document.getElementById('campaignForm').querySelector('[name="description"]').value = campaign.description;
            document.getElementById('campaignForm').querySelector('[name="background"]').value = campaign.background || '';
            document.getElementById('campaignForm').querySelector('[name="target_amount"]').value = campaign.target_amount;
            document.getElementById('campaignForm').querySelector('[name="deadline"]').value = campaign.deadline;
            document.getElementById('campaignForm').querySelector('[name="category"]').value = campaign.category || '';
            document.getElementById('campaignForm').querySelector('[name="image_url"]').value = campaign.image_url || '';
            document.getElementById('campaignForm').querySelector('[name="video_url"]').value = campaign.video_url || '';
            openCampaignModal(id);
        }
    } catch (error) {
        alert('Error memuat data campaign');
    }
}

// Save campaign
document.getElementById('campaignForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', formData.get('id') ? 'update_campaign' : 'create_campaign');
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Campaign berhasil disimpan');
            closeCampaignModal();
            loadCampaigns();
            loadDashboardStats();
        } else {
            alert(data.message || 'Gagal menyimpan campaign');
        }
    } catch (error) {
        alert('Terjadi kesalahan');
    }
});

// Verify donation
async function verifyDonation(id, status) {
    if (!confirm(`Apakah Anda yakin ingin ${status === 'verified' ? 'memverifikasi' : 'menolak'} donasi ini?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'verify_donation');
    formData.append('id', id);
    formData.append('status', status);
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Status donasi berhasil diubah');
            loadDonations();
            loadDashboardStats();
        } else {
            alert(data.message || 'Gagal mengubah status');
        }
    } catch (error) {
        alert('Terjadi kesalahan');
    }
}

// Close campaign
async function closeCampaign(id) {
    if (!confirm('Apakah Anda yakin ingin menutup campaign ini?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'close_campaign');
    formData.append('id', id);
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Campaign berhasil ditutup');
            loadCampaigns();
        } else {
            alert(data.message || 'Gagal menutup campaign');
        }
    } catch (error) {
        alert('Terjadi kesalahan');
    }
}

// Delete campaign
async function deleteCampaign(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus campaign ini? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete_campaign');
    formData.append('id', id);
    
    try {
        const response = await fetch('../api/admin.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Campaign berhasil dihapus');
            loadCampaigns();
            loadDashboardStats();
        } else {
            alert(data.message || 'Gagal menghapus campaign');
        }
    } catch (error) {
        alert('Terjadi kesalahan');
    }
}

// Add update
function addUpdate(campaignId) {
    const title = prompt('Judul Update:');
    if (!title) return;
    
    const content = prompt('Isi Update:');
    if (!content) return;
    
    const imageUrl = prompt('URL Gambar (opsional):') || '';
    
    const formData = new FormData();
    formData.append('action', 'add_update');
    formData.append('campaign_id', campaignId);
    formData.append('title', title);
    formData.append('content', content);
    formData.append('image_url', imageUrl);
    
    fetch('../api/admin.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Update berhasil ditambahkan');
        } else {
            alert('Gagal menambahkan update');
        }
    });
}

// Add report
function addReport(campaignId) {
    const title = prompt('Judul Laporan:');
    if (!title) return;
    
    const description = prompt('Deskripsi:') || '';
    
    const expenseAmount = prompt('Jumlah Pengeluaran (Rp):');
    if (!expenseAmount) return;
    
    const receiptUrl = prompt('URL Nota/Struk (opsional):') || '';
    const distributionUrl = prompt('URL Foto Penyaluran (opsional):') || '';
    
    const formData = new FormData();
    formData.append('action', 'add_report');
    formData.append('campaign_id', campaignId);
    formData.append('title', title);
    formData.append('description', description);
    formData.append('expense_amount', expenseAmount);
    formData.append('receipt_image', receiptUrl);
    formData.append('distribution_image', distributionUrl);
    
    fetch('../api/admin.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Laporan berhasil ditambahkan');
        } else {
            alert('Gagal menambahkan laporan');
        }
    });
}

// Load statistics
async function loadStatistics() {
    try {
        const response = await fetch('../api/admin.php?action=statistics');
        const data = await response.json();
        
        if (data.success) {
            // Donation chart
            const donationCtx = document.getElementById('donationChart').getContext('2d');
            new Chart(donationCtx, {
                type: 'line',
                data: {
                    labels: data.stats.donation_labels || [],
                    datasets: [{
                        label: 'Donasi per Hari',
                        data: data.stats.donation_data || [],
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Campaign chart
            const campaignCtx = document.getElementById('campaignChart').getContext('2d');
            new Chart(campaignCtx, {
                type: 'bar',
                data: {
                    labels: data.stats.campaign_labels || [],
                    datasets: [{
                        label: 'Dana Terkumpul',
                        data: data.stats.campaign_data || [],
                        backgroundColor: 'rgba(99, 102, 241, 0.5)',
                        borderColor: 'rgb(99, 102, 241)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    } catch (error) {
        console.error('Error loading statistics:', error);
    }
}
