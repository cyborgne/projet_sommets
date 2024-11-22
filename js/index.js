$(document).ready(function() {
    var user = JSON.parse(sessionStorage.getItem('user'));
    
    if (!user) {
        // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
        console.log('redicrect vers login : ', user)
        window.location.href = 'login.html';
    } else {
        // Redirection basée sur le rôle de l'utilisateur, si nécessaire
        if (user.userRole == 'editor') {
            console.log('redicrect vers edit : ', user)
            window.location.href = 'edit.html';
        } else {
            console.log('redicrect vers view : ', user)
            window.location.href = 'view.html';
        } 
    }
});
