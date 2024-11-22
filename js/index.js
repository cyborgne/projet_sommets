$(document).ready(function() {
    var user = JSON.parse(sessionStorage.getItem('user'));
    
    if (!user) {
        // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
        window.location.href = 'login.html';
    } else {
        // Obtenir le nom de la page actuelle (exemple : "edit.html")
        var currentPage = window.location.pathname.split('/').pop();

        // Redirection basée sur le rôle de l'utilisateur, si nécessaire
        if (user.userRole == 'editor' && currentPage !== 'edit.html') {
            window.location.href = 'edit.html';
        } else if (user.userRole !== 'editor' && currentPage !== 'view.html') {
            window.location.href = 'view.html';
        } else {
            // Met à jour les informations affichées sur la page
            $('#userFirstName').text(user.firstName);
            $('#userRole').text(user.userRole);
        }
    }
});
