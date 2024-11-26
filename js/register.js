import {logout} from '/js/logout.js';

// On déconnecte l'utilisateur si il est connecté
var user = JSON.parse(sessionStorage.getItem('user'));
if (user) { logout(); }

$(document).ready(function() {
    $('#cancelButton').on('click', function(event) {
        event.preventDefault();
        window.location.href = 'index.html';
    });

    $('#registerForm').on('submit', function(event) {
        event.preventDefault();
        var formData = {
            login: $('#login').val(),
            email: $('#email').val(),
            firstName: $('#firstName').val(),
            lastName: $('#lastName').val(),
            password: $('#password').val(),
            userRoleId: $('#userRoleId').val()
        };

        $.ajax({
            type: 'POST',
            url: 'register.php',
            data: JSON.stringify(formData),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    sessionStorage.setItem('defaultLogin', response.login);
                    window.location.href = 'login.html';
                } else {
                    $('#registerError').text(response.message).show();
                }
            },
            error: function() {
                $('#registerError').text('Erreur lors de l\'inscription. Veuillez réessayer.').show();
            }
        });
    });
});
