$(document).ready(function () {
  const edit_icon = $(".edit.outline.icon");
  const edit_modal = $("#edit_modal");
  const edit_modal_content = $("#edit_modal > .content");
  const edit_confirm = $("#edit_confirm");

  edit_icon.click(function () {
    let id = $(this).data("id");

    $.ajax({
      url: "customers.infos.php",
      type: "post",
      data: { id: id },
      success: function (response) {
        edit_modal_content.html(response);

        $("#edit_form").form({
          fields: {
            first_name: ["empty", "regExp[[A-Za-z\\u00c0-\\u00ff\\- ']{1,45}]"],
            last_name: ["empty", "regExp[[A-Za-z\\u00c0-\\u00ff\\- ']{1,45}]"],
            role: "empty",
            email: "email",
            address_id: "empty",
            active: "empty",
          },
        });

        edit_modal
          .modal("attach events", ".ui.close.button", "hide")
          .modal("setting", "closable", false)
          .modal("show");
      },
    });
    // end ajax

    $(".coupled.modal").modal({ allowMultiple: true });

    edit_confirm.modal("attach events", "#edit_submit");

    edit_confirm.modal({
      onApprove: function () {
        $("#edit_form").submit();
      },
    });
  });
  // end onclick edit

});
