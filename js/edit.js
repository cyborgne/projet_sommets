import {logout} from '/js/logout.js';


$(document).ready(function() {
    // Déconnecte l'utilisateur et redirige vers la racine du site si le
    // bouton se déconnecter est cliqué
    $('#logoutBtn').on('click', function() {
        logout().always(function(){
            window.location.href = '/';
        });
    });

    // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    // ou si il n'est pas éditeur
    var user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || user.userRole != 'editor') {
        window.location.href = '/';
    } 
    
    // Met à jour les informations affichées sur la page
    $('#userFirstName').text(user.firstName);
    $('#userRole').text(user.userRole);
        

});
