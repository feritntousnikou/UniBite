loadUserPoints();

document.getElementById('btn-logout').addEventListener('click', function() {
    fetch('api/logout.php')
    .then(function(res) { return res.json(); })
    .then(function() {
        localStorage.removeItem('role');
        localStorage.removeItem('userId');
        localStorage.removeItem('firstName');
        window.location.href = 'login.html';
    });
});


function loadUserPoints() {
    fetch('api/get_user_points.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            document.getElementById('user-name').textContent =
                data.firstName + ' ' + data.lastName;
            document.getElementById('user-points').textContent =
                data.points + ' πόντοι';
        } else {
            window.location.href = 'login.html';
        }
    });
}


var form = document.getElementById('create-meal-form');

form.addEventListener('submit', function(e) {
    e.preventDefault();

    hideMessages();

    var title           = document.getElementById('title').value;
    var description     = document.getElementById('description').value;
    var portions        = document.getElementById('portions').value;
    var pickup_location = document.getElementById('pickup_location').value;
    var pickup_time     = document.getElementById('pickup_time').value;

    if (title === '' || portions === '' || pickup_location === '' || pickup_time === '') {
        showError('Παρακαλώ συμπληρώστε όλα τα υποχρεωτικά πεδία.');
        return;
    }

    var checkedAllergens = document.querySelectorAll('input[name="allergens"]:checked');
    var allergensList = [];
    checkedAllergens.forEach(function(cb) {
        allergensList.push(cb.value);
    });

    var formData = new FormData();
    formData.append('title',           title);
    formData.append('description',     description);
    formData.append('portions',        portions);
    formData.append('pickup_location', pickup_location);
    formData.append('pickup_time',     pickup_time);
    formData.append('allergens',       allergensList.join(','));

    var photoInput = document.getElementById('photo');
    if (photoInput.files.length > 0) {
        formData.append('photo', photoInput.files[0]);
    }

    fetch('api/insert_meal.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            showSuccess('Η αγγελία δημοσιεύτηκε επιτυχώς!');
            form.reset();
        } else {
            showError(data.message);
        }
    });
});


function showError(msg) {
    var el = document.getElementById('error-msg');
    el.innerHTML = msg;
    el.classList.remove('hidden');
}

function showSuccess(msg) {
    var el = document.getElementById('success-msg');
    el.innerHTML = msg;
    el.classList.remove('hidden');
}

function hideMessages() {
    document.getElementById('error-msg').classList.add('hidden');
    document.getElementById('success-msg').classList.add('hidden');
}