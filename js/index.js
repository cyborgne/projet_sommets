$(document).ready(function() {
    var user = JSON.parse(sessionStorage.getItem('user'));
    
    if (!user) {
        // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
        window.location.href = 'login.html';
    } else {
        // Redirection basée sur le rôle de l'utilisateur, si nécessaire
        if (user.userRole == 'editor') {
            window.location.href = 'edit.html';
        } else {
            window.location.href = 'view.html';
        } 
    }
});
