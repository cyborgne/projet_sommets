export function logout() {
    return $.ajax({
        type: 'POST',
        url: 'logout.php', // Assurez-vous que ce fichier détruit la session côté serveur
    }).fail(function(){
        // Message d'erreur en cas de problème avec le serveur
        alert('Erreur lors de la déconnexion.');
    }).always(function() {
        // Supprime la session côté client
        sessionStorage.removeItem('user');
    });
}

//$('#logoutBtn').on('click', logout);
