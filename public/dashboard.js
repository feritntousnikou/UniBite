
loadUserPoints();
loadMeals();
loadRequests();

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

document.getElementById('btn-cancel-edit').addEventListener('click', function() {
    document.getElementById('edit-form-container').classList.add('hidden');
});

document.getElementById('edit-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var formData = new FormData();
    formData.append('meal_id',        document.getElementById('edit-meal-id').value);
    formData.append('title',          document.getElementById('edit-title').value);
    formData.append('description',    document.getElementById('edit-description').value);
    formData.append('portions_total', document.getElementById('edit-portions').value);
    formData.append('pickup_location',document.getElementById('edit-location').value);
    formData.append('pickup_time',    document.getElementById('edit-time').value);
    formData.append('allergens',      document.getElementById('edit-allergens').value);

    fetch('api/update_meal.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            showMsg('alert-success', data.message);
            document.getElementById('edit-form-container').classList.add('hidden');
            loadMeals();
        } else {
            showMsg('alert-danger', data.message);
        }
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


function loadMeals() {
    fetch('api/get_cook_meals.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        document.getElementById('loading-meals').classList.add('hidden');

        if (!data.success) {
            showMsg('alert-danger', data.message);
            return;
        }

        renderMeals(data.meals);
    });
}

function renderMeals(meals) {
    var container = document.getElementById('meals-container');

    if (meals.length === 0) {
        container.innerHTML =
            '<div class="alert alert-info">Δεν έχεις αγγελίες ακόμα. ' +
            '<a href="create_meal.html">Δημιούργησε μία!</a></div>';
        return;
    }

    var html =
        '<table class="table table-bordered">' +
            '<thead>' +
                '<tr>' +
                    '<th>Τίτλος</th>' +
                    '<th>Μερίδες</th>' +
                    '<th>Παράδοση</th>' +
                    '<th>Ώρα</th>' +
                    '<th>Ενέργειες</th>' +
                '</tr>' +
            '</thead>' +
            '<tbody>';

    meals.forEach(function(meal) {
        html +=
            '<tr>' +
                '<td>' + meal.title + '</td>' +
                '<td>' + meal.portions_available + ' / ' + meal.portions_total + '</td>' +
                '<td>' + meal.pickup_location + '</td>' +
                '<td>' + meal.pickup_time + '</td>' +
                '<td>' +
                    '<button class="btn btn-warning btn-sm" id="edit-' + meal.id + '">Επεξεργασία</button> ' +
                    '<button class="btn btn-danger btn-sm"  id="delete-' + meal.id + '">Διαγραφή</button>' +
                '</td>' +
            '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;

    meals.forEach(function(meal) {
        attachMealButtons(meal);
    });
}

function attachMealButtons(meal) {
    var btnEdit   = document.getElementById('edit-'   + meal.id);
    var btnDelete = document.getElementById('delete-' + meal.id);

    if (btnEdit) {
        btnEdit.addEventListener('click', function() {
            document.getElementById('edit-meal-id').value     = meal.id;
            document.getElementById('edit-title').value       = meal.title;
            document.getElementById('edit-description').value = meal.description;
            document.getElementById('edit-portions').value    = meal.portions_total;
            document.getElementById('edit-location').value    = meal.pickup_location;
            document.getElementById('edit-time').value        = meal.pickup_time;
            document.getElementById('edit-allergens').value   = meal.allergens;

            document.getElementById('edit-form-container').classList.remove('hidden');
        });
    }

    if (btnDelete) {
        btnDelete.addEventListener('click', function() {
            var formData = new FormData();
            formData.append('meal_id', meal.id);

            fetch('api/delete_meal.php', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    showMsg('alert-success', data.message);
                    loadMeals();
                } else {
                    showMsg('alert-danger', data.message);
                }
            });
        });
    }
}


function loadRequests() {
    fetch('api/get_requests.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        document.getElementById('loading-requests').classList.add('hidden');

        if (!data.success) {
            showMsg('alert-danger', data.message);
            return;
        }

        renderRequests(data.requests);
    });
}

function renderRequests(requests) {
    var container = document.getElementById('requests-container');

    if (requests.length === 0) {
        container.innerHTML =
            '<div class="alert alert-info">Δεν υπάρχουν αιτήματα ακόμα.</div>';
        return;
    }

    var html =
        '<table class="table table-bordered">' +
            '<thead>' +
                '<tr>' +
                    '<th>Αγγελία</th>' +
                    '<th>Καταναλωτής</th>' +
                    '<th>Κατάσταση</th>' +
                    '<th>Ενέργειες</th>' +
                '</tr>' +
            '</thead>' +
            '<tbody>';

    requests.forEach(function(req) {
        html +=
            '<tr id="row-' + req.id + '">' +
                '<td>' + req.meal_title + '</td>' +
                '<td>' + req.consumer_firstName + ' ' + req.consumer_lastName + '</td>' +
                '<td>' + getStatusLabel(req.status) + '</td>' +
                '<td>' + getActions(req) + '</td>' +
            '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;

    requests.forEach(function(req) {
        attachButtons(req.id, req.status);
    });
}

function getActions(req) {
    if (req.status === 'pending') {
        return '<button class="btn btn-success btn-sm" id="approve-' + req.id + '">Approve</button> ' +
               '<button class="btn btn-danger btn-sm"  id="reject-'  + req.id + '">Reject</button>';
    }
    if (req.status === 'approved') {
        return '<button class="btn btn-primary btn-sm" id="collected-'     + req.id + '">Παρελήφθη</button> ' +
               '<button class="btn btn-warning btn-sm" id="notcollected-' + req.id + '">Δεν παρελήφθη</button>';
    }
    return '—';
}

function getStatusLabel(status) {
    if (status === 'pending')       return '<span class="label label-warning">Σε αναμονή</span>';
    if (status === 'approved')      return '<span class="label label-success">Εγκρίθηκε</span>';
    if (status === 'rejected')      return '<span class="label label-danger">Απορρίφθηκε</span>';
    if (status === 'collected')     return '<span class="label label-primary">Παρελήφθη</span>';
    if (status === 'not_collected') return '<span class="label label-default">Δεν παρελήφθη</span>';
    return status;
}

function attachButtons(reqId, status) {
    if (status === 'pending') {
        var btnApprove = document.getElementById('approve-' + reqId);
        var btnReject  = document.getElementById('reject-'  + reqId);
        if (btnApprove) {
            btnApprove.addEventListener('click', function() {
                updateStatus(reqId, 'approved');
            });
        }
        if (btnReject) {
            btnReject.addEventListener('click', function() {
                updateStatus(reqId, 'rejected');
            });
        }
    }
    if (status === 'approved') {
        var btnCollected    = document.getElementById('collected-'    + reqId);
        var btnNotCollected = document.getElementById('notcollected-' + reqId);
        if (btnCollected) {
            btnCollected.addEventListener('click', function() {
                updateStatus(reqId, 'collected');
            });
        }
        if (btnNotCollected) {
            btnNotCollected.addEventListener('click', function() {
                updateStatus(reqId, 'not_collected');
            });
        }
    }
}

function updateStatus(reqId, newStatus) {
    var formData = new FormData();
    formData.append('request_id', reqId);
    formData.append('status',     newStatus);

    fetch('api/update_request_status.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            showMsg('alert-success', data.message);
            loadRequests();
            loadUserPoints();
        } else {
            showMsg('alert-danger', data.message);
        }
    });
}

function showMsg(type, text) {
    var el = document.getElementById('msg');
    el.className = 'alert ' + type;
    el.innerHTML = text;
    el.classList.remove('hidden');
}