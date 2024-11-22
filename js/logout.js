$('#logoutBtn').on('click', function() {
    $.ajax({
        type: 'POST',
        url: 'logout.php', // Assurez-vous que ce fichier détruit la session côté serveur
        success: function() {
            // Supprime la session côté client
            sessionStorage.removeItem('user');
            // Redirige vers la page de connexion
            window.location.href = 'login.html';
        },
        error: function() {
            // Message d'erreur en cas de problème avec le serveur
            alert('Erreur lors de la déconnexion.');
            // Supprime la session et redirige même en cas d'erreur
            sessionStorage.removeItem('user');
            window.location.href = 'login.html';
        }
    });
});
