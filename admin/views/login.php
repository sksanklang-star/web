<?php if (!defined('ACCESS_ALLOWED')) exit; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .brand-header { text-align: center; margin-bottom: 30px; color: #4a148c; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-header">
            <h3>Admin Portal</h3>
            <small>ระบบจัดการค่าธรรมเนียมขยะ</small>
        </div>
        
        <input type="hidden" id="csrf_token" value="<?php echo $_SESSION['csrf_admin_token']; ?>">

        <form onsubmit="handleLogin(event)">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" id="username" class="form-control" value="admin">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" id="password" class="form-control" value="1234">
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background:#4a148c; border:none;">เข้าสู่ระบบ</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function handleLogin(e) {
            e.preventDefault();
            const user = document.getElementById('username').value;
            const pass = document.getElementById('password').value;
            const token = document.getElementById('csrf_token').value;

            fetch('/api.php?action=admin_login', {
                method: 'POST',
                body: JSON.stringify({ 
                    username: user, 
                    password: pass,
                    csrf_token: token // ส่ง Token ไปด้วย
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    window.location.href = '?page=dashboard';
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    </script>
</body>
</html>