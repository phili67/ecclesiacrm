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


  $(document).on("click",".AddToPeopleCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addPerson([clickedButton.data("cartpersonid")],function()
      {
        $(clickedButton).addClass("RemoveFromPeopleCart");
        $(clickedButton).removeClass("AddToPeopleCart");
        $('span i:nth-child(2)',clickedButton).addClass("fa-remove");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-cart-plus");
      });
  });
  
  $(document).on("click",".RemoveFromPeopleCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removePerson([clickedButton.data("cartpersonid")],function()
      {
        $(clickedButton).addClass("AddToPeopleCart");
        $(clickedButton).removeClass("RemoveFromPeopleCart");
        $('span i:nth-child(2)',clickedButton).removeClass("fa-remove");
        $('span i:nth-child(2)',clickedButton).addClass("fa-cart-plus");
      });
    });
    
      // newMessage event subscribers : Listener CRJSOM.js
    $(document).on("updateCartMessage", updateLittleButtons);
    
    function updateLittleButtons(e) {
      var cartPeople = e.people;
        
      if (cartPeople != null) {
        personButtons = $("a[data-cartpersonid]");
        $(personButtons).each(function(index,personButton){
          personID = $(personButton).data("cartpersonid")
          if (cartPeople.includes(personID)) {
            personPresent = true;
            $(personButton).addClass("RemoveFromPeopleCart");
            $(personButton).removeClass("AddToPeopleCart");
            fa = $(personButton).find("i.fa.fa-inverse");
            $(fa).addClass("fa-remove");
            $(fa).removeClass("fa-cart-plus");
            text = $(personButton).find("span.cartActionDescription")
            if(text){
              $(text).text(i18next.t("Remove from Cart"));
            }
          } else {
            $(personButton).addClass("AddToPeopleCart");
            $(personButton).removeClass("RemoveFromPeopleCart");
            fa = $(personButton).find("i.fa.fa-inverse");
            
            $(fa).removeClass("fa-remove");
            $(fa).addClass("fa-cart-plus");
            text = $(personButton).find("span.cartActionDescription")
            if(text){
              $(text).text(i18next.t("Add to Cart"));
            }
          }
        });
      }
      
    }

});