import {logout} from '/js/logout.js';

// On déconnecte l'utilisateur si il est connecté
var user = JSON.parse(sessionStorage.getItem('user'));
if (user) { logout(); }

$(document).ready(function() {
    // Si un login par défaut est défini on prérempli le champ login
    var defaultLogin = sessionStorage.getItem('defaultLogin')
    if(defaultLogin) { $('#login').val(defaultLogin) };

    $('#loginForm').on('submit', function(event) {
        event.preventDefault();
        var formData = {
            login: $('#login').val(),
            password: $('#password').val()
        };

        $.ajax({
            type: 'POST',
            url: 'login.php',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    // On stocke les info de l'utilisateur connecté
                    sessionStorage.setItem('user', JSON.stringify(response.user));
                    // On set de login par défaut avec le login courant
                    sessionStorage.setItem('defaultLogin', response.user.login);
                    window.location.href = 'index.html';
                } else {
                    $('#loginError').text(response.message).show();
                }
            },
            error: function() {
                $('#loginError').text('Erreur lors de la connexion. Veuillez réessayer.').show();
            }
        });
    });
});
