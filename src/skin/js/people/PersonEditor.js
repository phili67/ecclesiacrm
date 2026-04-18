$(function() {
  $("[data-mask]").inputmask();

  var familyValueInput = $("#familySelectionValue");
  var familyPicker = $("#existingFamilyPicker");
  var familySelect = $("#optionFamily");

  function showPersonAddress() {
    familyPicker.hide();
    $("#familyAddress").fadeOut(50);
    $("#personAddress").fadeIn(1000);
  }

  function prepareNewFamily() {
    familyPicker.hide();
    $('#famcountry-input').val(window.CRM.sEntityCountry).trigger('change');
    $('#famstate-input').val('').trigger('change');
    $('#FamName').val('');
    $('#FamAddress1').val('');
    $('#FamAddress2').val('');
    $('#FamCity').val('');
    $('#FamZip').val('');
    $('#FamStateTextbox').val('');

    $("#familyAddress").fadeIn(1000);
    $("#personAddress").fadeOut(50);
    $('#FamName').focus();
  }

  function loadExistingFamily(famID) {
    if (!(famID > 0)) {
      return;
    }

    familyPicker.show();

    window.CRM.APIRequest({
       method: 'POST',
       path: 'families/info',
       data: JSON.stringify({"familyId": famID})
    }, function(data) {
       $('#famcountry-input').val(data.Country).trigger('change');
       $('#famstate-input').val(data.State).trigger('change');
       $('#FamName').val(data.Name);
       $('#FamAddress1').val(data.Address1);
       $('#FamAddress2').val(data.Address2);
       $('#FamCity').val(data.City);
       $('#FamZip').val(data.Zip);
       $('#FamStateTextbox').val(data.State);
    });

    $("#familyAddress").fadeIn(1000);
    $("#personAddress").fadeOut(50);
  }

  function applyFamilyMode(mode) {
    if (mode === 'existing') {
      familyPicker.show();

      if (!(parseInt(familySelect.val(), 10) > 0)) {
        var firstFamilyOption = familySelect.find('option:first').val();
        if (firstFamilyOption) {
          familySelect.val(firstFamilyOption);
        }
      }

      familyValueInput.val(familySelect.val() || '0');
      if (parseInt(familyValueInput.val(), 10) > 0) {
        loadExistingFamily(parseInt(familyValueInput.val(), 10));
      }

      return;
    }

    if (mode === 'new') {
      familyValueInput.val('-1');
      prepareNewFamily();
      return;
    }

    familyValueInput.val('0');
    showPersonAddress();
  }

  // we hide by default the familyAddress
  if (!window.CRM.bShowAddress) {
    $("#familyAddress").hide();
  } else {
    $("#personAddress").hide();
  }

  $('input[name="familyAssignmentMode"]').on('change', function() {
    applyFamilyMode($(this).val());
  });

  familySelect.on('change', function() {
    var famID = parseInt($(this).val(), 10) || 0;
    familyValueInput.val(famID);

    if (famID > 0) {
      loadExistingFamily(famID);
    }
  });

  applyFamilyMode($('input[name="familyAssignmentMode"]:checked').val());

  $("#famcountry-input").select2();
  $("#famstate-input").select2();
  $("#country-input").select2();
  $("#state-input").select2();
  familySelect.select2({
    width: '100%',
    placeholder: window.i18next ? i18next.t('Search for a family or address') : 'Search for a family or address'
  });

});
