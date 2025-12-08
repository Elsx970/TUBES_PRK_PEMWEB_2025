// Load campaigns list
async function loadCampaigns() {
    const container = document.getElementById('campaignsContainer');
    const loading = document.getElementById('loading');
    const sort = document.getElementById('sortBy')?.value || 'latest';
    const category = document.getElementById('filterCategory')?.value || '';
    
    container.innerHTML = '';
    loading.classList.remove('hidden');
    
    try {
        const response = await fetch(`api/campaigns.php?action=list&sort=${sort}&category=${category}`);
        const data = await response.json();
        
        loading.classList.add('hidden');
        
        if (data.success && data.campaigns.length > 0) {
            data.campaigns.forEach(campaign => {
                const progress = (campaign.current_amount / campaign.target_amount * 100).toFixed(1);
                const daysLeft = Math.ceil((new Date(campaign.deadline) - new Date()) / (1000 * 60 * 60 * 24));
                
                const card = document.createElement('div');
                card.className = 'bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-200';
                card.innerHTML = `
                    ${campaign.image_url ? `<img src="${campaign.image_url}" alt="${campaign.title}" class="w-full h-48 object-cover">` : '<div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">No Image</div>'}
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 text-gray-800">${campaign.title}</h3>
                        <p class="text-gray-600 mb-4 line-clamp-2">${campaign.description.substring(0, 100)}...</p>
                        
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Terkumpul</span>
                                <span class="font-semibold">Rp ${parseInt(campaign.current_amount).toLocaleString('id-ID')}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: ${Math.min(progress, 100)}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>Target: Rp ${parseInt(campaign.target_amount).toLocaleString('id-ID')}</span>
                                <span>${progress}%</span>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center text-sm text-gray-600 mb-4">
                            <span>⏰ ${daysLeft > 0 ? daysLeft + ' hari lagi' : 'Telah berakhir'}</span>
                            <span>👥 ${campaign.donor_count || 0} donatur</span>
                        </div>
                        
                        <a href="campaign_detail.php?id=${campaign.id}" 
                           class="block w-full bg-indigo-600 text-white text-center py-2 rounded-lg hover:bg-indigo-700 transition duration-200">
                            Lihat Detail
                        </a>
                    </div>
                `;
                container.appendChild(card);
            });
        } else {
            container.innerHTML = '<div class="col-span-full text-center py-8 text-gray-600">Tidak ada campaign ditemukan</div>';
        }
    } catch (error) {
        loading.classList.add('hidden');
        container.innerHTML = '<div class="col-span-full text-center py-8 text-red-600">Error memuat campaign</div>';
    }
}

// Load campaign detail
async function loadCampaignDetail(id) {
    const container = document.getElementById('campaignDetail');
    
    try {
        const response = await fetch(`api/campaigns.php?action=detail&id=${id}`);
        const data = await response.json();
        
        if (data.success && data.campaign) {
            const campaign = data.campaign;
            const progress = (campaign.current_amount / campaign.target_amount * 100).toFixed(1);
            const daysLeft = Math.ceil((new Date(campaign.deadline) - new Date()) / (1000 * 60 * 60 * 24));
            
            container.innerHTML = `
                ${campaign.image_url ? `<img src="${campaign.image_url}" alt="${campaign.title}" class="w-full h-96 object-cover rounded-lg mb-6">` : ''}
                
                <h1 class="text-3xl font-bold mb-4 text-gray-800">${campaign.title}</h1>
                
                <div class="mb-6">
                    <div class="flex justify-between text-lg mb-2">
                        <span class="text-gray-600">Terkumpul</span>
                        <span class="font-bold text-indigo-600">Rp ${parseInt(campaign.current_amount).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 mb-2">
                        <div class="bg-indigo-600 h-4 rounded-full flex items-center justify-end pr-2" style="width: ${Math.min(progress, 100)}%">
                            <span class="text-white text-xs font-semibold">${progress}%</span>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Target: Rp ${parseInt(campaign.target_amount).toLocaleString('id-ID')}</span>
                        <span>⏰ ${daysLeft > 0 ? daysLeft + ' hari lagi' : 'Telah berakhir'}</span>
                    </div>
                </div>
                
                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-2">Deskripsi</h2>
                    <p class="text-gray-700 whitespace-pre-line">${campaign.description}</p>
                </div>
                
                ${campaign.background ? `
                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-2">Latar Belakang Masalah</h2>
                    <p class="text-gray-700 whitespace-pre-line">${campaign.background}</p>
                </div>
                ` : ''}
                
                ${campaign.updates && campaign.updates.length > 0 ? `
                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-4">Update Terbaru</h2>
                    ${campaign.updates.map(update => `
                        <div class="border-l-4 border-indigo-600 pl-4 mb-4">
                            <h3 class="font-semibold mb-1">${update.title}</h3>
                            <p class="text-gray-700 text-sm mb-2">${update.content}</p>
                            ${update.image_url ? `<img src="${update.image_url}" class="max-w-md rounded-lg mb-2">` : ''}
                            <p class="text-xs text-gray-500">${new Date(update.created_at).toLocaleDateString('id-ID')}</p>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                
                ${campaign.reports && campaign.reports.length > 0 ? `
                <div class="mb-6">
                    <h2 class="text-xl font-bold mb-4">Laporan Penggunaan Dana</h2>
                    ${campaign.reports.map(report => `
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <h3 class="font-semibold mb-2">${report.title}</h3>
                            <p class="text-gray-700 text-sm mb-2">${report.description || ''}</p>
                            <p class="text-indigo-600 font-semibold mb-2">Pengeluaran: Rp ${parseInt(report.expense_amount).toLocaleString('id-ID')}</p>
                            ${report.receipt_image ? `<img src="${report.receipt_image}" class="max-w-md rounded-lg mb-2">` : ''}
                            ${report.distribution_image ? `<img src="${report.distribution_image}" class="max-w-md rounded-lg mb-2">` : ''}
                            <p class="text-xs text-gray-500">${new Date(report.created_at).toLocaleDateString('id-ID')}</p>
                        </div>
                    `).join('')}
                </div>
                ` : ''}
                
                <div class="mt-8">
                    <button onclick="openDonationModal()" 
                            class="w-full bg-indigo-600 text-white py-3 px-6 rounded-lg hover:bg-indigo-700 text-lg font-semibold">
                        Donasi Sekarang
                    </button>
                </div>
            `;
        } else {
            container.innerHTML = '<div class="text-center py-8 text-red-600">Campaign tidak ditemukan</div>';
        }
    } catch (error) {
        container.innerHTML = '<div class="text-center py-8 text-red-600">Error memuat detail campaign</div>';
    }
}
