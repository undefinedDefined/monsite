$(document).ready(function () {
  // Javascript pour permettre de fermer les messages d'alerte
  $(".message .close").click(function () {
    $(this).closest(".message").transition("fade");
    setTimeout(() => {
      $(this).closest(".row").remove();
    }, 1000);
  });
});
