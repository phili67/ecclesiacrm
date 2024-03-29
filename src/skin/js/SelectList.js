$(function() {
  $("#AddAllToCart").on('click',function(){
    window.CRM.cart.addPerson(listPeople);
  });

  $("#AddAllPageToCart").on('click',function(){
    var listPagePeople  = [];
    $(".AddToPeopleCart").each(function(res) {
      var personId= $(this).data("cartpersonid");

      listPagePeople.push(personId);
    });

    if (listPagePeople.length > 0) {
        window.CRM.cart.addPerson(listPagePeople);
    } else {
        window.CRM.DisplayAlert(i18next.t("Add People"), i18next.t("This page is still in the cart."));
    }
  });


  $("#RemoveAllFromCart").on('click',function(){
    window.CRM.cart.removePerson(listPeople);
  });

  $("#RemoveAllPageFromCart").on('click',function(){
    var listPagePeople  = [];
    $(".RemoveFromPeopleCart").each(function(res) {
      var personId= $(this).data("cartpersonid");

      listPagePeople.push(personId);
    });

    window.CRM.cart.removePerson(listPagePeople);
  });
});
