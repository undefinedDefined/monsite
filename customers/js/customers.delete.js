$(document).ready(function(){
    const delete_icon = $('.trash.alternate.icon');
    const delete_modal = $('#delete_modal');
    const delete_modal_content = $('#delete_modal > .content');

    delete_icon.click(function(){
        let id = $(this).data('id');
        delete_modal_content.html(`<p>Confirmez-vous la suppression de l'utilisateur ${id} ?</p>`);
        delete_modal.modal("show");
        delete_modal.modal({
            onApprove : function(){
                $.ajax({
                    url: 'customers.delete.php',
                    method: 'post',
                    data : {id: id},
                    success: function(){
                        // location.reload();
                    }
                });
                //end ajax
            }
        })
    });

})