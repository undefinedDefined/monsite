$(document).ready(function () {
  const create_button = $(".ui.create.button");
  const create_modal = $("#create_modal");
  const create_confirm = $('#create_confirm');

  create_button.click(function () {
    create_modal
      .modal("attach events", ".ui.close.button", "hide")
      .modal("setting", "closable", false)
      .modal("show");
  });

  $(".coupled.modal").modal({ allowMultiple: true });

  create_confirm.modal("attach events", "#create_submit");

  create_confirm.modal({
    onApprove: function () {
      $("#create_form").submit();
    },
  });

  $('.ui.clear.button').click(function(){
    $('#create_form').form('clear')
  });

  $("#create_form").form({
    fields: {
      prenom: {
        identifier: "first_name",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez indiquer un prénom",
          },
          {
            type: "regExp[[A-Za-z\\u00c0-\\u00ff\\- ']{1,45}]",
            prompt: "Le prénom doit contenir entre 2 et 45 caractères",
          },
        ],
      },
      nom: {
        identifier: "last_name",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez indiquer un nom",
          },
          {
            type: "regExp[[A-Za-z\\u00c0-\\u00ff- ']{1,45}]",
            prompt: "Le nom doit contenir entre 2 et 45 caractères",
          },
        ],
      },
      email: {
        identifier: "email",
        rules: [
          {
            type: "email",
            prompt: "Veuillez indiquer une adresse email valide",
          },
        ],
      },
      adresse: {
        identifier: "address_id",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez selectionner une adresse valide",
          },
        ],
      },
      magasin: {
        identifier: "store_id",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez selectionner une adresse de magasin valide",
          },
        ],
      },
      role: {
        identifier: "role",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez selectionner un role valide",
          },
        ],
      },
      active: {
        identifier: "active",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez selectionner un état d'activité valide",
          },
        ],
      },
      password: {
        identifier: "password",
        rules: [
          {
            type: "empty",
            prompt: "Veuillez indiquer un mot de passe",
          },
          {
            type: "regExp[/^(?=.*[A-Za-z])(?=.*\\d)[A-Za-z\\d]{8,20}$/]",
            prompt:
              "Le mot de passe doit contenir entre 8 et 20 caractères, au moins une lettre et un nombre",
          },
        ],
      },
      passwordCheck: {
        identifier: "confirm_password",
        rules: [
          {
            type: "match[password]",
            prompt: "Les mots de passe ne correspondent pas",
          },
        ],
      },
    },
  });
});
