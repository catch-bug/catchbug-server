/**
 * @Project: RollbarServer
 * @User: mira
 * @Date: 21.11.18
 * @Time: 18:43
 */

$(function() {
  // Prevent dropdown menu from closing when click inside the form
  $(document).on("click", ".navbar-right .dropdown-menu", function(e){
    e.stopPropagation();
  });

  $('[role="tab"]').on('click', function(e) {
    $(this).blur();
  });

  $("#nav-browser-tab").on('click', function (e) {
    e.preventDefault();
    $(this).tab('show');
    console.log("prdel");
    chartB.flush();
    chartO.flush();
  });

  $("#formLogin").submit(function (e) {
    e.preventDefault();

    var b = $(this).serialize();
    $('div#loading').show();
    $.post("/ajax/a_index.php?cmd=login&ajax_token=" + AJAX_TOKEN, b)
        .done(function (result) {

          if (result.code === 0) {
            document.location.reload(true);


          } else {
            $("#errorModalText").text(result.message);
            $("#errorModal").modal('show');

          }
          $('#loading').hide();
        })
        .fail(function () {
          $('#loading').hide();
        })
  });

  $('*[data-toggle="tooltip"]').tooltip();

});
