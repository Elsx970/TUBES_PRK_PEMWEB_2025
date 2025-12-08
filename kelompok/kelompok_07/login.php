<?php
require_once 'config.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CareU</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-indigo-600">CareU</h1>
            <p class="text-gray-600 mt-2">Sistem Crowdfunding Mahasiswa</p>
        </div>
        
        <div id="alert" class="hidden mb-4 p-4 rounded-lg"></div>
        
        <form id="loginForm" class="space-y-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Username/Email</label>
                <input type="text" name="username" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-gray-700 font-medium mb-2">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            
            <button type="submit" 
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition duration-200 font-medium">
                Login
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <p class="text-gray-600">Belum punya akun? 
                <a href="register.php" class="text-indigo-600 hover:underline">Daftar disini</a>
            </p>
        </div>
        
        <div class="mt-4 text-center">
            <a href="index.php" class="text-gray-500 hover:text-gray-700 text-sm">Kembali ke Beranda</a>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'login');
            
            const alert = document.getElementById('alert');
            alert.classList.add('hidden');
            
            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert.className = 'mb-4 p-4 rounded-lg bg-green-100 text-green-700';
                    alert.textContent = 'Login berhasil! Mengalihkan...';
                    alert.classList.remove('hidden');
                    
                    setTimeout(() => {
                        if (data.role === 'admin') {
                            window.location.href = 'admin/dashboard.php';
                        } else {
                            window.location.href = 'index.php';
                        }
                    }, 1000);
                } else {
                    alert.className = 'mb-4 p-4 rounded-lg bg-red-100 text-red-700';
                    alert.textContent = data.message || 'Login gagal';
                    alert.classList.remove('hidden');
                }
            } catch (error) {
                alert.className = 'mb-4 p-4 rounded-lg bg-red-100 text-red-700';
                alert.textContent = 'Terjadi kesalahan';
                alert.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>