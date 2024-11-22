$(document).ready(function() {
    var user = JSON.parse(sessionStorage.getItem('user'));
    if (!user) {
        window.location.href = 'login.html';
    } else {
        if (user.userRole == 'editor') {
            window.location.href = 'edit.html';
        } else {
            window.location.href = 'view.html';
        }
        $('#userFirstName').text(user.firstName);
        $('#userRole').text(user.userRole);
    }
});
