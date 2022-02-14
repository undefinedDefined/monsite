<!-- Modal pour modifier les utilisateurs -->

<div class="ui coupled edit user modal" id="edit_modal">
  <i class="close icon"></i>
  <div class="header">
    Informations utilisateur
  </div>
  <div class="content">
    <!-- <form action="customer_update_ajax.php" method="post" class="ui edit user form container"> -->
    <!-- Informations récupérées par ajax -->
    </form>
  </div>
  <div class="actions">
    <div class="ui close button">Annuler</div>
    <button class="ui primary right labeled icon submit button" id="edit_submit">
      <i class="right arrow icon"></i>
      Modifier
    </button>
  </div>
</div>

<!-- Modal confirmation modification utilisateur -->

<div class="ui coupled mini confirm edit modal" id="edit_confirm">
  <div class="header">Modification utilisateur</div>
  <div class="content">
    <p>Etes vous sûr de vouloir modifier cet utilisateur ?</p>
  </div>
  <div class="actions">
    <div class="ui negative cancel button">Annuler</div>
    <div class="ui positive approve button">Confirmer</div>
  </div>
</div>