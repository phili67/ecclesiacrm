$(document).ready(function () {
  $("#AddAllToCart").click(function(){
    window.CRM.cart.addPerson(listPeople);
  });

  $("#AddAllPageToCart").click(function(){
    var listPagePeople  = [];
    $(".AddToPeopleCart").each(function(res) {
      var personId= $(this).data("cartpersonid");

      listPagePeople.push(personId);
    });

    window.CRM.cart.addPerson(listPagePeople);
  });


  $("#RemoveAllFromCart").click(function(){
    window.CRM.cart.removePerson(listPeople);
  });

  $("#RemoveAllPageFromCart").click(function(){
    var listPagePeople  = [];
    $(".RemoveFromPeopleCart").each(function(res) {
      var personId= $(this).data("cartpersonid");

      listPagePeople.push(personId);
    });

    window.CRM.cart.removePerson(listPagePeople);
  });
});