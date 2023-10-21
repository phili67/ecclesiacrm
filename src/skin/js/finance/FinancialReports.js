//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

$(function() {
  $("#family").select2();
  $("#addAllFamilies").on('click', function () {
  var all = [];
      $("#family > option").each(function () {
          all.push(this.value);
      });
       $("#family").val(all).trigger("change");
  });
  $("#clearAllFamilies").on('click', function () {
        $("#family").val(null).trigger("change");
  });

  $("#classList").select2();
  $("#addAllClasses").on('click', function () {
  var all = [];
      $("#classList > option").each(function () {
          all.push(this.value);
      });
       $("#classList").val(all).trigger("change");
  });
  $("#clearAllClasses").on('click', function () {
        $("#classList").val(null).trigger("change");
  });

  $("#fundsList").select2();
  $("#addAllFunds").on('click', function () {
  var all = [];
      $("#fundsList > option").each(function () {
          all.push(this.value);
      });
       $("#fundsList").val(all).trigger("change");
  });
  $("#clearAllFunds").on('click', function () {
        $("#fundsList").val(null).trigger("change");
  });
});