/**
 * @Project: RollbarServer
 * @User: mira
 * @Date: 21.11.18
 * @Time: 18:43
 */

$(function() {
  // Prevent dropdown menu from closing when click inside the form
  $(document).on("click", ".navbar-right .dropdown-menu", function (e) {
    e.stopPropagation();
  });

  // unfocus tab link after click
  $('[role="tab"]').on('click', function (e) {
    $(this).blur();
  });

  // show charts on right position when showing tab
  $("#nav-browser-tab").on('shown.bs.tab', function (e) {
    chartB.flush();
    chartO.flush();
  });

  // form login
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

  // form register
  $("#formRegister")
      .submit(function (e) {
        e.preventDefault();
        if (!$(this).valid()) {
          //    $login_validator.errorList[0].element.focus();  // when sending not by intput submit
          return;
        }

        var b = $(this).serialize();
        $('div#loading').show();
        $.post("/ajax/a_index.php?cmd=register&ajax_token=" + AJAX_TOKEN, b)
            .done(function (result) {

              if (result.code === 0) {
                $("#okModalText").text(result.message);
                $("#okModal").modal('show').on('hidden.bs.modal', function (e) {
                  document.location.replace(_REWRITE + "user/welcome");
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
      })
      .validate({
        errorPlacement: function ( error, element ) {
          // Add the `invalid-feedback` class to the error element
          error.addClass( "invalid-feedback" );
          if ( element.prop( "type" ) === "checkbox" ) {
            error.insertAfter( element.next( "label" ) );
          } else {
            error.insertAfter( element );
          }
        },
        highlight: function ( element, errorClass, validClass ) {
          $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
        unhighlight: function (element, errorClass, validClass) {
          $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        }

      });


  // run all tooltips
  $('*[data-toggle="tooltip"]').tooltip();

// Javascript to enable link to tab
  var hash = document.location.hash;
  if (hash) {
    $('.nav-tabs a[href="' + hash + '"]').tab('show');
  }

// With HTML5 history API, we can easily prevent scrolling!
  $('.nav-tabs a').on('shown.bs.tab', function (e) {
    if (history.pushState) {
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

      $.get("/ajax/a_index.php?cmd=delete_occurrence&ajax_token=" + AJAX_TOKEN + "&itemid=" + itemid + "&projectid=" + projectid + "&userid=" + userid + "&occurrenceid=" + occurrenceid)
          .done(function (result) {
            if (result.code === 0) {
              $("#okModalText").text(result.message);
              $("#okModal").modal('show').on('hidden.bs.modal', function (e) {
                document.location.replace(_REWRITE + "project/" + projectid + "/item/" + itemid + '#nav-occurrences');
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

  $("#selectLevel").on('change', function () {
    var userid = $(this).data("userid"),
        projectid = $(this).data("projectid"),
        itemid = $(this).data("itemid"),
        level = this.value,
        type = 'success';

    $.get("/ajax/a_index.php?cmd=change_level&ajax_token=" + AJAX_TOKEN + "&itemid=" + itemid + "&projectid=" + projectid + "&userid=" + userid + "&level=" + level)
        .done(function (result) {
          if (result.code === 0) {

          } else {
            type = 'danger';
          }
          $.notify({
            // http://bootstrap-notify.remabledesigns.com/
            message: result.message
          }, {
            delay: 2000,
            type: type
          });
          $('#loading').hide();
        })
        .fail(function () {
          $('#loading').hide();
        })
  });

  jQuery.validator.setDefaults({
    errorElement: "em",
    errorPlacement: function ( error, element ) {
      // Add the `invalid-feedback` class to the error element
      error.addClass( "invalid-feedback" );

      $( element ).closest( "form" ).find( "label[for='" + element.attr( "id" ) + "']" ).append( error );
    },

    highlight: function ( element, errorClass, validClass ) {
      $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
    },
    unhighlight: function (element, errorClass, validClass) {
      $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
    },
    showErrors: function(errorMap, errorList) {
      // scroll down if element under top nav bar
      if (typeof errorList[0] !== "undefined") {
        var position = $(window).scrollTop();
        var top = $(errorList[0].element).offset().top;
        if ((top - position) < 109){
          $(window).scrollTop(top - 110);
        }
      }
      this.defaultShowErrors();
    }
  });


  $("#formProjectSettings")
      .submit(function (e) {
        e.preventDefault();
        if (!$(this).valid()) {
          //    $login_validator.errorList[0].element.focus();  // when sending not by intput submit
          return;
        }

        var type = 'success',
            b = $(this).serialize();

        $('div#loading').show();
        $.post("/ajax/a_index.php?cmd=project_settings&ajax_token=" + AJAX_TOKEN, b)
            .done(function (result) {

              if (result.code === 0) {
                //need reload to changes show on page
                if (result.forceReload) {
                  document.location.reload(true);
                }
              } else {
                type = 'danger';
              }
              $.notify({
                // http://bootstrap-notify.remabledesigns.com/
                message: result.message
              }, {
                delay: 2000,
                type: type
              });
              $('#loading').hide();
            })
            .fail(function () {
              $('#loading').hide();
            })
      })
      .validate();



});
