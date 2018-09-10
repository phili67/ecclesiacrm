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
    var famID = $(this).val();
    
    if (famID > 0) {
      window.CRM.APIRequest({
         method: 'POST',
         path: 'families/info',
         data: JSON.stringify({"familyId":famID})
      }).done(function(data) {
         $('#famcountry-input').val(data.Country).trigger('change');
         $('#famstate-input').val(data.State).trigger('change');
         $('#FamName').val(data.Name);
         $('#FamAddress1').val(data.Address1);
         $('#FamAddress2').val(data.Address2);
         $('#FamCity').val(data.City);
         $('#FamZip').val(data.Zip);
         $('#FamStateTextbox').val(data.State);
      })
      
      $('#optionFamily').attr('size', '8');
      $("#familyAddress").fadeIn(1000);
      $("#personAddress").fadeOut(50);

    } else {    
      if (this.value == -1) {// we create a new family
        // we fields are blank
        $('#famcountry-input').val(window.CRM.sChurchCountry).trigger('change');
        $('#famstate-input').val('').trigger('change');
        $('#FamName').val('');
        $('#FamAddress1').val('');
        $('#FamAddress2').val('');
        $('#FamCity').val('');
        $('#FamZip').val('');
        $('#FamStateTextbox').val('');
         
        // next the fields will appear
        $('#optionFamily').attr('size', '8');
        $("#familyAddress").fadeIn(1000);
        $("#personAddress").fadeOut(50);
      }  else {
        $('#optionFamily').attr('size', '2');
        $("#familyAddress").fadeOut(50);
        $("#personAddress").fadeIn(1000);
      }
      
      $('#FamName').focus();
    }
  });

  $("#famcountry-input").select2();
  $("#famstate-input").select2();
  $("#country-input").select2();
  $("#state-input").select2();
  
});