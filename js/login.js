$(document).ready(function() {
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
                    sessionStorage.setItem('user', JSON.stringify(response.user));
                    alert('Connexion réussie!');
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
