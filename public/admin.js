loadStats();
loadLeaderboard();

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


function loadStats() {
    fetch('api/get_admin_stats.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        document.getElementById('loading-stats').classList.add('hidden');

        if (!data.success) {
            showError(data.message);
            return;
        }

        var html =
            '<table class="table table-bordered">' +
                '<thead>' +
                    '<tr>' +
                        '<th>Μερίδες που διαμοιράστηκαν (τελευταίος μήνας)</th>' +
                        '<th>Σύνολο αγγελιών</th>' +
                        '<th>Σύνολο χρηστών</th>' +
                    '</tr>' +
                '</thead>' +
                '<tbody>' +
                    '<tr>' +
                        '<td>' + data.portions_this_month + '</td>' +
                        '<td>' + data.total_meals + '</td>' +
                        '<td>' + data.total_users + '</td>' +
                    '</tr>' +
                '</tbody>' +
            '</table>';

        document.getElementById('stats-container').innerHTML = html;
    });
}


function loadLeaderboard() {
    fetch('api/get_leaderboard.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        document.getElementById('loading-donor').classList.add('hidden');
        document.getElementById('loading-ratings').classList.add('hidden');

        if (!data.success) {
            showError(data.message);
            return;
        }

        /* Top Donor */
        if (data.top_donor) {
            document.getElementById('donor-container').innerHTML =
                '<table class="table table-bordered">' +
                    '<thead>' +
                        '<tr><th>Όνομα</th><th>Μερίδες που πρόσφερε</th></tr>' +
                    '</thead>' +
                    '<tbody>' +
                        '<tr>' +
                            '<td>' + data.top_donor.firstName + ' ' + data.top_donor.lastName + '</td>' +
                            '<td>' + data.top_donor.total_collected + '</td>' +
                        '</tr>' +
                    '</tbody>' +
                '</table>';
        } else {
            document.getElementById('donor-container').innerHTML =
                '<div class="alert alert-info">Δεν υπάρχουν δεδομένα ακόμα.</div>';
        }

        if (data.top_meals.length === 0) {
            document.getElementById('ratings-container').innerHTML =
                '<div class="alert alert-info">Δεν υπάρχουν αξιολογήσεις ακόμα.</div>';
            return;
        }

        var html =
            '<table class="table table-bordered">' +
                '<thead>' +
                    '<tr><th>Γεύμα</th><th>Μέση Βαθμολογία</th></tr>' +
                '</thead>' +
                '<tbody>';

        data.top_meals.forEach(function(meal) {
            html +=
                '<tr>' +
                    '<td>' + meal.title + '</td>' +
                    '<td>' + meal.avg_rating + ' / 5</td>' +
                '</tr>';
        });

        html += '</tbody></table>';

        document.getElementById('ratings-container').innerHTML = html;
    });
}


function showError(msg) {
    var el = document.getElementById('error-msg');
    el.innerHTML = msg;
    el.classList.remove('hidden');
}