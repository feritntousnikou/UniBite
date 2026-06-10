loadUserPoints();
loadRequests();

document.getElementById(`btn-Logout`).addEventListener(`click`, function() {
    fetch(`api/logout.php`)
    .then(function(res) { res.json(); })
    .then(function() {
        localStorage.removeItem(`role`);
        localStorage.removeItem(`userId`);
        localStorage.removeItem(`firstName`);
        window.location.href = `login.html`;
        });
});

function loadUserPoints() {
    fetch(`api/get_user_points.php`)
    .then(function(data) {
        if (data.success){
            document.getElementById(`user-name`).textContent = data.firstName + ' ' + data.lastName;
            document.getElementById(`user-points`).textContent = data.points + ' πόντοι';
        } else {
            window.location.href = "login.html";
        }
    });
}

function loadRequests(){
    fetch(`api/get_requests.php`)
    .then(function(res) {return res.json(); })
    .then(function(data) {
        document.getElementById(`loading`).classList.add(`hidden`);

        if (!data.success) {
            showMsg(`alert`, data.message);
            return;
        }

        renderRequests(data.requests);
    });
}

function loadRenderRequests(requests){
    var container = document.getElementById(`requests-container`);

    if (requests.length === 0) {
        container.innerHTML = 
        `<div class="alert alert-info">Δεν υπάρχουν αιτήματα ακόμα.</div>`;
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
        var actions = getActions(req);
 
        html +=
            '<tr id="row-' + req.id + '">' +
                '<td>' + req.meal_title + '</td>' +
                '<td>' + req.consumer_firstName + ' ' + req.consumer_lastName + '</td>' +
                '<td>' + getStatusLabel(req.status) + '</td>' +
                '<td>' + actions + '</td>' +
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
        return '<button class="btn btn-success btn-sm btn-approve" id="approve-' + req.id + '">Approve</button> ' +
               '<button class="btn btn-danger btn-sm btn-reject"  id="reject-'  + req.id + '">Reject</button>';
    }
    if (req.status === 'approved') {
        return '<button class="btn btn-primary btn-sm btn-collected"     id="collected-'     + req.id + '">Παρελήφθη</button> ' +
               '<button class="btn btn-warning btn-sm btn-notcollected" id="notcollected-' + req.id + '">Δεν παρελήφθη</button>';
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