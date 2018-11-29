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

  $("#nav-browser-tab").on('shown.bs.tab', function (e) {
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

// Javascript to enable link to tab

  var hash = document.location.hash;
  if (hash) {
    $('.nav-tabs a[href="'+hash+'"]').tab('show');
  }

// With HTML5 history API, we can easily prevent scrolling!
  $('.nav-tabs a').on('shown.bs.tab', function (e) {
    if(history.pushState) {
      history.pushState(null, null, e.target.hash);
    } else {
      window.location.hash = e.target.hash; //Polyfill for old browsers
    }
  });

  $("#btnDeleteItem").on('click', function (e) {
    e.preventDefault();
    var result = confirm("Want to delete this item?");
    if (result) {
      var userid = $(this).data("userid"),
          projectid = $(this).data("projectid"),
          itemid = $(this).data("itemid");

      console.log(userid);
      $.get("/ajax/a_index.php?cmd=delete_item&ajax_token=" + AJAX_TOKEN + "&itemid=" + itemid + "&projectid=" + projectid + "&userid=" + userid)
          .done(function (result) {
            if (result.code === 0) {
              $("#okModalText").text(result.message);
              $("#okModal").modal('show').on('hidden.bs.modal', function (e) {
                document.location.replace(_REWRITE + "project/" + projectid + "/items");
              });
            } else {
              $("#errorModalText").text(result.message);
              $("#errorModal").modal('show');
            }
            $('#loading').hide();
          })
          .fail(function () {
            $('#loading').hide();
          })
    }

  });

  $("#btnDeleteOccurrence").on('click', function (e) {
    e.preventDefault();
    var result = confirm("Want to delete this item?");
    if (result) {
      var userid = $(this).data("userid"),
          projectid = $(this).data("projectid"),
          itemid = $(this).data("itemid"),
          occurrenceid = $(this).data("occurrenceid");

      console.log(userid);
      $.get("/ajax/a_index.php?cmd=delete_item&ajax_token=" + AJAX_TOKEN + "&itemid=" + itemid + "&projectid=" + projectid + "&userid=" + userid + "&occurrenceid=" + occurrenceid)
          .done(function (result) {
            if (result.code === 0) {
              $("#okModalText").text(result.message);
              $("#okModal").modal('show').on('hidden.bs.modal', function (e) {
                document.location.replace(_REWRITE + "project/" + projectid + "/item/" + itemid);
              });
            } else {
              $("#errorModalText").text(result.message);
              $("#errorModal").modal('show');
            }
            $('#loading').hide();
          })
          .fail(function () {
            $('#loading').hide();
          })
    }

  });




});
