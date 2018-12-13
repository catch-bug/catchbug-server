/**
 * @Project: RollBugServer
 * @User: mira
 * @Date: 12.12.18
 * @Time: 19:20
 */

$(function() {
  console.log('ok');

  var validator = $("#formSettingsGeneral")
      .on("submit", function (e) {
        e.preventDefault();
        postData("settings_general", $(this).serialize(), "a_system_settings.php");
      })
      .validate();


  $("#btnSendTestMail").on("click", function (e) {
    e.preventDefault();
    if (($("#inpTestMail").val() === '') || !validator.element("#inpTestMail")){
      return;
    }
    var config = $("input[name*=config\\[smtp\\]]").serialize();
    postData("test_smtp_settings", config, "a_system_settings.php", $("#divErrorTestMail"));

  })
});
