const getRender = (key, value, depth) => {
  var sr = $("<div>").addClass("JSONObjectDiv").data("nodeName", key).css("margin-left", (depth * 15) + "px");
  if (value instanceof Object) {
    $("<label>").text(key).appendTo(sr);
    $.each(value, function (key, value) {
      sr.append(getRender(key, value, depth + 1));
    });
  }
  else {
    $("<label>").text(key).css("margin-right", "15px").appendTo(sr);
    $("<input>").attr("type", "text").val(value).appendTo(sr);
  }
  return sr;
}

var cfgid = 0;
$(".jsonSettingsEdit").on("click", function (event) {
  event.preventDefault();
  cfgid = $(this).data("cfgid");
  var cfgvalue = jQuery.parseJSON($("input[name='new_value[" + cfgid + "]']").val());
  console.log(cfgvalue);
  $("#JSONSettingsDiv").html("");
  $.each(cfgvalue, function (key, value) {
    $("#JSONSettingsDiv").append(getRender(key, value, 0));
  });

  $("#JSONSettingsModal").modal("show");

});

$('.nav-link').on('click', function () {
  let mode = $(this).data('mode');
  $("#modeID").val(mode);
});


const getFormValue = (object) => {
  var tmp = {}
  if ($(object).children(".JSONObjectDiv").length > 0) {
    $(object).children(".JSONObjectDiv").each(function () {
      tmp[$(this).data("nodeName")] = getFormValue($(this));
    });
    return tmp;
  }
  else if ($(object).children("input").length > 0) {
    return $("input", object).val();
  }
}

const updateDropDrownFromAjax = (selectObj) => {
  window.CRM.APIRequest({
    method: 'GET',
    path: selectObj.data("url") // url : shoud be /system/....
  }, function (data) {
    $.each(data, function (index, config) {
      var optSelected = config.id == selectObj.data("value");
      var opt = new Option(config.value, config.id, optSelected, optSelected);
      selectObj.append(opt);
    });
  });
}


window.CRM.ElementListener('.jsonSettingsClose', 'click', function (event){
  var settings = getFormValue($("#JSONSettingsDiv"));
  $("input[name='new_value[" + cfgid + "]']").val(JSON.stringify(settings));
  $("#JSONSettingsModal").modal("hide");
  $("input[name=save]").on('click');
});

window.CRM.ElementListener('#save', 'click', function (event){
  const form = document.getElementById('form-save');

  const elements = form.elements;

  let new_value = [];
  let type = [];

  Array.from(elements).forEach(item => {
    let name = item.name;

    const match = name.match(/^([^\[\]]+)\[(.+)\]$/);

    if (match) {
      const key = match[1];
      const value = match[2];

      if (key == 'new_value') {
        new_value[value] = item.value;
      } else if (key == 'type') {
        type[value] = item.value;
      }
    }
  });

  window.CRM.APIRequest({
    method: 'POST',
    path: 'systemsettings/saveSettings',
    data: JSON.stringify({ "new_value": new_value, "type": type })
  }, function (data) {
    window.CRM.showGlobalMessage(i18next.t("Setting saved"), "success")
    var mode = $("#modeID").val();

    if (mode === 'enabledfeatures' || mode === 'gdpr' || mode === 'localization') {
      setTimeout(() => {
        if (mode !== '') {
          location.href = window.CRM.root + '/v2/systemsettings/' + mode;
        } else {
          location.href = window.CRM.root + '/v2/systemsettings';
        }
      }, 3000); // 2000 ms = 2 secondes
    }
  });
})
