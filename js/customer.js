$(document).ready(function(){

    // Selectors pour Update 
    const icon_Update = $('.icon.updateUser');
    const formModal_Update = $('#updateUser');
    const formModal_Update_Content = $('#updateUser > .content');
    const confirmModal_Update = $('#confirmUpdateUser');

    /**
     * 
     * @function On crée un évenement onclick sur nos icones de modification
     * 
     * @let userid: récupère la valeur de l'attribut 'data-id' de l'icône sur laquelle on a cliqué
     * Celle-ci correspond à l'id de l'utilisateur pour lequel on veut faire l'update
     * 
     * On fait ensuite une requête Ajax de type POST à customer_info.php
     * dans laquelle on envoi $_POST['id'] = userid
     * 
     * @returns customer_info.php renvoi le code html du formulaire contenant les informations de l'utilisateur
     * On ajoute ensuite le formulaire dans notre modal, puis on affiche ce dernier
     * 
     */

    icon_Update.click(function(){

      let userid = $(this).data('id');
      
      $.ajax({
        url: 'customer_info.php',
        type: 'post',
        data: {id: userid},
        success: function(response){
            // Ajouter la réponse (formulaire) dans le corps de notre modal principal
            formModal_Update_Content.html(response);

            // Règles de validation du formulaire de modification d'utilisateur
            $('#formUpdate').form({
                fields :{
                    prénom : {
                        identifier : 'first_name',
                        rules : [
                            {
                                type : 'empty',
                                prompt : 'Le prénom ne peut pas être vide'
                            },
                            {
                                type : 'regExp[[A-Za-z\\u00c0-\\u00ff\\- \']{1,45}]',
                                prompt : 'Le prénom doit contenir entre 1 et 45 caractères'
                            }
                        ]
                    },
                    nom : {
                        identifier : 'last_name',
                        rules : [
                            {
                                type : 'empty',
                                prompt : 'Le nom ne peut pas être vide'
                            },
                            {
                                type : 'regExp[[A-Za-z\\u00c0-\\u00ff\\- \']{1,45}]',
                                prompt : 'Le nom doit contenir entre 1 et 45 caractères'
                            }
                        ]
                    },
                    role: {
                        identifier : 'role',
                        rules: [
                            {
                                type : 'empty',
                                prompt : 'Le role ne peut pas être vide'
                            }
                        ]
                    },
                    email: {
                        identifier : 'login',
                        rules : [
                            {
                                type : 'email',
                                prompt : 'Veuillez définir une adresse email valide'
                            }
                        ]
                    },
                    adresse: {
                        identifier : 'adresse',
                        rules : [
                            {
                                type : 'empty',
                                prompt : 'L\'adresse ne peut pas être vide'
                            }
                        ]
                    },
                    active: {
                        identifier : 'active',
                        rules : [
                            {
                                type : 'empty',
                                prompt : 'Veuillez définir un état actif/ non actif'
                            }
                        ]
                    }
                }});

            // Afficher le modal principal
            formModal_Update.modal('attach events', '.ui.close.button', 'hide')
            .modal('setting', 'closable', false)
            .modal('show');
        }
      });
    });

    /**
     * La partie qui suit est nécessaire pour afficher le modal de confirmation rattaché au modal principal
     * contenant les informations renvoyées par customer_info.php
     */

    // Autoriser deux modaux l'un sur l'autre
    $('.coupled.modal').modal({
        allowMultiple: true
    });

    // Afficher le modal de confirmation lorsqu'on appuie sur le bouton de modification
    confirmModal_Update.modal('attach events', '#submitUpdateUser');

    // Envoyer le formulaire de modification si on confirme notre choix
    confirmModal_Update.modal({
      onApprove: function() {
          $( "#formUpdate" ).submit();
      }
    });

    // Selectors pour locations
    const icon_Location = $('.icon.viewLoc');
    const formModal_Location = $('#viewLoc');
    const formModal_Location_Content = $('#viewLoc > .content');

    /**
    * 
    * @function On crée un évenement onclick sur nos icones de locations
    * 
    * @let userid: récupère la valeur de l'attribut 'data-id' de l'icône sur laquelle on a cliqué
    * Celle-ci correspond à l'id de l'utilisateur pour lequel on veut faire l'update
    * 
    * On fait ensuite une requête Ajax de type POST à customer_film.php
    * dans laquelle on envoi $_POST['id'] = userid
    * 
    * @returns customer_film.php renvoi le code html d'un tableau contenant les locations de l'utilisateur
    * On ajoute ensuite le tableau dans notre modal, puis on affiche ce dernier
    * 
    */

    icon_Location.click(function(){

      let userid = $(this).data('id');

      // requête Ajax
      $.ajax({
        url: 'customer_film.php',
        type: 'post',
        data: {id: userid},
        success: function(response){ 
          // Ajouter la réponse dans le corps de notre modal
          formModal_Location_Content.html(response);

          // Afficher le modal
          formModal_Location.modal('attach events', '.ui.close.button', 'hide')
          .modal('setting', 'closable', false)
          .modal('show');
        }
      });
    });

  // Javascript pour permettre de fermer les messages d'alerte
  $('.message .close').click(function() {
      $(this).closest('.message').transition('fade');
      setTimeout(() => {
          $(this).closest('.row').remove();
      }, 1000);
  });
})