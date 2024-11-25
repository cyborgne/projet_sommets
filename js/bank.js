import {logout} from '/js/logout.js';

$(document).ready(function() {
    // Redirige vers la page de connexion si l'utilisateur n'est pas authentifié
    // ou si il n'est pas éditeur
    var user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || user.userRole != 'editor') {
        window.location.href = '/';
    } 

    // Déconnecte l'utilisateur et redirige vers la racine du site si le
    // bouton se déconnecter est cliqué
    $('#logoutBtn').on('click', function(event) {
        event.preventDefault();
        logout().always(function(){
            window.location.href = '/';
        });
    });
    
    // Détecte un champ du formulaire qui a changé
    $('#bank-name').on('input', function(event) {
        event.preventDefault();
        $(this).addClass('modified');
        $('#updateBtn').prop('disabled', false);
        $('#reloadBtn').prop('disabled', false);
    });
    $('#bank-description').on('input', function(event) {
        event.preventDefault();
        $(this).addClass('modified');
        $('#updateBtn').prop('disabled', false);
        $('#reloadBtn').prop('disabled', false);
    });

    // Soumission du formulaire de mise à jour du nom et/ou de la description
    $('#bank-update-form').on('submit', function(event) {
        event.preventDefault();
        const name = $('#bank-name').val();
        const description = $('#bank-description').val();
        const id = $('#bank-id').val();
        $.ajax({
            url: 'bank.php',
            type: 'PUT',
            contentType: 'application/json',
            data: JSON.stringify({
                name: name,
                description: description,
                id: id
            })
        }).fail(function () {
            alert('Echec de la mise à jour de la banque de données.');
        }).always(function(response) {
            loadBank(id);
        })
        
    }); 


    // On récupère l'id de la banque d'images dans l'url
    // et on initialise le formulaire
    const params = new URL(window.location.href).searchParams;
    const id = params.get('id');
    if (id) {
        loadBank(id);
    } else {
        window.location.href = 'banks.html';
    }
    
    // Rétablir les champs
    $('#reloadBtn').on('click', function(e) {
        e.preventDefault();
        loadBank(id);
    });

});

function loadBank(id) {
    return $.ajax({
        url: 'bank.php',
        type: 'GET',
        contentType: 'application/json',
        data : { 'id': id}
    }).done(function(result) {
        if (result.success) {
            var bank = result.bank;
            $('#bank-id').val(bank.id);
            $('#bank-dir').val(bank.dir);
            $('#bank-name').val(bank.name);
            $('#bank-description').val(bank.description);
            $('#bank-description').removeClass('modified');
            $('#bank-name').removeClass('modified');
            $('#updateBtn').prop('disabled', true);
            $('#reloadBtn').prop('disabled', true);
            var images = bank.images;
            $('#images-container').html('');
            images.forEach(function(image) {
                var imageElement = `
                    <div class="image-item">
                        <img src="${image.src}" alt="Image">
                        <div class="delete-icon img-${image.id}" id="${image.id}">×</div>
                    </div>
                `;
                $('#images-container').append(imageElement);
                $(`.img-${image.id}`).on('click', function(e) { 
                    e.preventDefault();
                    deleteImage(this.id, id);
                });
            });
        } else {
            // Si la banque d'image n'est pas trouvée on redirige
            window.location.href = 'banks.html';
        }
    });
}

function deleteImage(id, bankId) {
    return $.ajax({
        url: 'bank.php',
        type: 'DELETE',
        contentType: 'application/json',
        data : JSON.stringify({ 'imageId': id})
    }).always(function(response) {
        loadBank(bankId);
    })
}