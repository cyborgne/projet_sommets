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
    $('#logoutBtn').on('click', function() {
        logout().always(function(){
            window.location.href = '/';
        });
    });

    // On vide les champ du formulaire de la modal quand on la cache
    $('#bankModal').on('hidden.bs.modal', function (event) {
        $('#bank-name').val('');
        $('#bank-description').val('');
    });
    
    // Soumission du formulaire de création de banque d'images
    $('#bank-form').on('submit', function(event) {
        event.preventDefault();
        const name = $('#bank-name').val();
        const description = $('#bank-description').val();
        $.ajax({
            url: 'bank.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                name: name,
                description: description
            })
        }).done(function(response) {
                $('#bankModal').modal('hide');
                loadBanks();
        })
        
    }); 
    loadBanks();
});

// Fonction pour supprimer une banque d'images
var deleteBank = function(bankId) {
    $.ajax({
        url: 'bank.php',
        type: 'DELETE',
        contentType: 'application/json',
        data: JSON.stringify({'bankId': bankId})
    }).always(loadBanks)
}

// Fonction pour charger dans le navigateur l'ensemble des banques d'images disponibles
function loadBanks() {
    $.ajax({
        url: 'banks.php',
        type: 'GET',
        contentType: 'application/json',
        success: function(data) {
            let html = '';
            for(var i in data.banks) {
                var bank = data.banks[i];
                html += `<div class='card mt-3'>
                            <div class='card-body'>
                                <strong>${bank.name}</strong>
                                <p class='card-text'>${bank.description}</p>
                            </div>
                            <div class='card-footer d-flex justify-content-between'>
                                <button id="${bank.id}"class='edit-${bank.id} btn btn-primary'>Éditer</button>
                                <button id="${bank.id}" class='suppr-${bank.id} btn btn-danger'>Supprimer</button>
                            </div>
                         </div>`;
            }
            $('#bank-list').html(html);
            for(var i in data.banks) {
                var bank = data.banks[i];
                $(`.edit-${bank.id}`).on('click', function(e) { 
                    e.preventDefault();
                    window.location.href = "bank.html?id=" + this.id;
                });
                $(`.suppr-${bank.id}`).on('click', function(e) { 
                    e.preventDefault();
                    deleteBank(this.id);
                });
            }
        }
    });
}