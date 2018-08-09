//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

$(document).ready(function () {
  $("#family").select2();
  $("#addAllFamilies").click(function () {
  var all = [];
      $("#family > option").each(function () {
          all.push(this.value);
      });
       $("#family").val(all).trigger("change");
  });
  $("#clearAllFamilies").click(function () {
        $("#family").val(null).trigger("change");
  });

  $("#classList").select2();
  $("#addAllClasses").click(function () {
  var all = [];
      $("#classList > option").each(function () {
          all.push(this.value);
      });
       $("#classList").val(all).trigger("change");
  });
  $("#clearAllClasses").click(function () {
        $("#classList").val(null).trigger("change");
  });

  $("#fundsList").select2();
  $("#addAllFunds").click(function () {
  var all = [];
      $("#fundsList > option").each(function () {
          all.push(this.value);
      });
       $("#fundsList").val(all).trigger("change");
  });
  $("#clearAllFunds").click(function () {
        $("#fundsList").val(null).trigger("change");
  });
});