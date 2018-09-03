$(document).ready(function () {

  $(function() {
    $("[data-mask]").inputmask();
  });

  // we hide by default the familyAddress
  if (window.CRM.iFamily == 0) {
    $("#familyAddress").hide();
  } else {
    $("#personAddress").hide();
  }
  
  // This scroll the family at the right place
  var selectedItem = $("#optionFamily option:selected").val();
  
  $('#optionFamily').val(1).change();
  $('#optionFamily').val(selectedItem).change();
  
  
  $('#optionFamily').change(function(data) {
    if (this.value == -1) {
      $('#optionFamily').attr('size', '2');
      $("#familyAddress").fadeIn(1000);
      $("#personAddress").fadeOut(50);
    }  else {
      $('#optionFamily').attr('size', '8');
      $("#familyAddress").fadeOut(50);
      $("#personAddress").fadeIn(1000);
    }
  });

  $("#famcountry-input").select2();
  $("#famstate-input").select2();
  $("#country-input").select2();
  $("#state-input").select2();
  
});