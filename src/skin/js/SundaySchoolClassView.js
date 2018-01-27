$("document").ready(function(){
   $(document).on("click",".AddToStudentGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addStudentGroup(clickedButton.data("cartstudentgroupid"),function()
      {
        $(clickedButton).addClass("RemoveFromStudentGroupCart");
        $(clickedButton).removeClass("AddToStudentGroupCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Remove Students from Cart"));
        }
      });
    });
    
    $(document).on("click",".RemoveFromStudentGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removeStudentGroup(clickedButton.data("cartstudentgroupid"),function()
      {
        $(clickedButton).addClass("AddToStudentGroupCart");
        $(clickedButton).removeClass("RemoveFromStudentGroupCart");
        $('i',clickedButton).removeClass("fa-remove");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Students to Cart"));
        }
      });
    });
    
    $(document).on("click",".AddToTeacherGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.addTeacherGroup(clickedButton.data("cartteachergroupid"),function()
      {
        $(clickedButton).addClass("RemoveFromTeacherGroupCart");
        $(clickedButton).removeClass("AddToTeacherGroupCart");
        $('i',clickedButton).addClass("fa-remove");
        $('i',clickedButton).removeClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Remove Teachers from Cart"));
        }
      });
    });
    
    $(document).on("click",".RemoveFromTeacherGroupCart", function(){
      clickedButton = $(this);
      window.CRM.cart.removeTeacherGroup(clickedButton.data("cartteachergroupid"),function()
      {
        $(clickedButton).addClass("AddToTeacherGroupCart");
        $(clickedButton).removeClass("RemoveFromTeacherGroupCart");
        $('i',clickedButton).removeClass("fa-remove");
        $('i',clickedButton).addClass("fa-cart-plus");
        text = $(clickedButton).find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Teachers to Cart"));
        }
      });
    });
    
    // newMessage event subscribers  : Listener CRJSOM.js
    $(document).on("emptyCartMessage", updateButtons);
    
    // newMessage event handler
    function updateButtons(e) {
      if (e.cartSize == 0) {
        $("#AddToTeacherGroupCart").addClass("AddToTeacherGroupCart");
        $("#AddToTeacherGroupCart").removeClass("RemoveFromTeacherGroupCart");
        $('i',"#AddToTeacherGroupCart").removeClass("fa-remove");
        $('i',"#AddToTeacherGroupCart").addClass("fa-cart-plus");
        text = $("#AddToTeacherGroupCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Teachers to Cart"));
        }
        
        $("#AddToStudentGroupCart").addClass("AddToStudentGroupCart");
        $("#AddToStudentGroupCart").removeClass("RemoveFromStudentGroupCart");
        $('i',"#AddToStudentGroupCart").removeClass("fa-remove");
        $('i',"#AddToStudentGroupCart").addClass("fa-cart-plus");
        text = $("#AddToStudentGroupCart").find("span.cartActionDescription")
        if(text){
          $(text).text(i18next.t("Add Students to Cart"));
        }
      }
    }
});