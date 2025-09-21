$(document).ready(function(){
  $("#open-modal-transacions-save").click(function(){
    $('#modal').modal('show');

    $.ajax({
      'url':'/transactions/modal_save',
      'method':'GET',
      'success': function(response){
        $("#modal").append(response);
        $("#modal .modal-loading").hide();
      },
      'error': function(){
        console.log("erro");
      }
    })
  })
})
