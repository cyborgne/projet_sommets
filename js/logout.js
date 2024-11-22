$('#logoutBtn').on('click', function() {
    $.ajax({
        type: 'POST',
        url: 'logout.php',
        success: function() {
            sessionStorage.removeItem('user');
            window.location.href = 'login.html';
        },
        error: function() {
            alert('Erreur lors de la déconnexion.');
        }
    });
});