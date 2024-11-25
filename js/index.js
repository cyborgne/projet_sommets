$(document).ready(function() {
    var user = JSON.parse(sessionStorage.getItem('user'));
    var currentPage = window.location.pathname.split('/').pop(); // Obtenir le nom de la page actuelle

    if (!user && currentPage !== 'login.html') {
        // Redirige uniquement si l'utilisateur n'est pas authentifié ET n'est pas déjà sur login.html
        window.location.href = 'login.html';
    } else if (user) {
        // Redirection basée sur le rôle de l'utilisateur, si nécessaire
        if (user.userRole == 'editor' && currentPage !== 'edit.html') {
            window.location.href = 'edit.html';
        } else if (user.userRole != 'editor' && currentPage !== 'view.html') {
            window.location.href = 'view.html';
        }
    }
});


