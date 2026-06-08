function showError(msg) {
    var el = document.getElementById('error-msg');
    el.innerHTML = msg;
    el.classList.remove('hidden');
}

function hideError() {
    var el = document.getElementById('error-msg');
    el.classList.add('hidden');
}
 
function showSuccess(msg) {
    var el = document.getElementById('success-msg');
    el.innerHTML = msg;
    el.classList.remove('hidden');
}
 
 
var loginForm = document.getElementById('login-form');
 
if (loginForm) {
 
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        hideError();
 
        var email    = document.getElementById('email').value;
        var password = document.getElementById('password').value;
 
        if (email === '' || password === '') {
            showError('Παρακαλώ συμπληρώστε email και κωδικό.');
            return;
        }
 
        var formData = new FormData();
        formData.append('email', email);
        formData.append('password', password);
 
        fetch('api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                localStorage.setItem('role', data.role);
                localStorage.setItem('userId', data.id);
                localStorage.setItem('firstName', data.firstName);
                if (data.role === 'cook' || data.role === 'admin') {
                    window.location.href = 'dashboard.html';
                } else {
                    window.location.href = 'feed.html';
                }
            } else {
                showError(data.message);
            }
        });
 
    });
}
 
 
var registerForm = document.getElementById('register-form');
 
if (registerForm) {
 
    registerForm.addEventListener('submit', function(e) {
        e.preventDefault();
        hideError();
 
        var firstName = document.getElementById('firstName').value;
        var lastName = document.getElementById('lastName').value;
        var email = document.getElementById('email').value;
        var password = document.getElementById('password').value;
        var passwordConfirm = document.getElementById('passwordConfirm').value;
        var role = document.getElementById('role').value;
 
        if (firstName === '' || lastName === '' || email === '' ||
            password === '' || passwordConfirm === '' || role === '') {
            showError('Παρακαλώ συμπληρώστε όλα τα πεδία.');
            return;
        }
 
        if (password !== passwordConfirm) {
            showError('Οι κωδικοί δεν ταιριάζουν.');
            return;
        }
 
        var formData = new FormData();
        formData.append('firstName', firstName);
        formData.append('lastName',  lastName);
        formData.append('email',     email);
        formData.append('password',  password);
        formData.append('role',      role);
 
        fetch('api/register.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                showSuccess('Ο λογαριασμός δημιουργήθηκε!');
                window.location.href = 'login.html';
            } else {
                showError(data.message);
            }
        });
 
    });
}