$(document).ready(function () {
    // useful for for the QueryView.php and PersonView.php

    // newMessage event subscribers : Listener CRJSOM.js
    function updateLittleButtons(e) {
        var cartPeople = e.people;
        var cartFamilies = e.families;
        var cartGroups = e.groups;

        personButtons = $("a[data-cartpersonid]");
        $(personButtons).each(function (index, personButton) {
            personID = $(personButton).data("cartpersonid")
            if (cartPeople != undefined && cartPeople.length > 0 && cartPeople.includes(personID)) {
                personPresent = true;
                $(personButton).addClass("RemoveFromPeopleCart");
                $(personButton).removeClass("AddToPeopleCart");
                fa = $(personButton).find("i.fa.fa-inverse");
                $(fa).addClass("fa-remove");
                $(fa).removeClass("fa-cart-plus");
                text = $(personButton).find("span.cartActionDescription")
                if (text) {
                    $(text).text(i18next.t("Remove from Cart"));
                }
            } else {
                $(personButton).addClass("AddToPeopleCart");
                $(personButton).removeClass("RemoveFromPeopleCart");
                fa = $(personButton).find("i.fa.fa-inverse");

                $(fa).removeClass("fa-remove");
                $(fa).addClass("fa-cart-plus");
                text = $(personButton).find("span.cartActionDescription")
                if (text) {
                    $(text).text(i18next.t("Add to Cart"));
                }
            }
        });

        familyButtons = $("a[data-cartfamilyid]");
        $(familyButtons).each(function (index, familyButton) {
            familyID = $(familyButton).data("cartfamilyid")
            if (cartFamilies != undefined && cartFamilies.length > 0 && cartFamilies.includes(familyID)) {
                personPresent = true;
                $(familyButton).addClass("RemoveFromFamilyCart");
                $(familyButton).removeClass("AddToFamilyCart");
                fa = $(familyButton).find("i.fa.fa-inverse");
                $(fa).addClass("fa-remove");
                $(fa).removeClass("fa-cart-plus");
                text = $(familyButton).find("span.cartActionDescription")
                if (text) {
                    $(text).text(i18next.t("Remove from Cart"));
                }
            } else {
                $(familyButton).addClass("AddToFamilyCart");
                $(familyButton).removeClass("RemoveFromFamilyCart");
                fa = $(familyButton).find("i.fa.fa-inverse");

                $(fa).removeClass("fa-remove");
                $(fa).addClass("fa-cart-plus");
                text = $(familyButton).find("span.cartActionDescription")
                if (text) {
                    $(text).text(i18next.t("Add to Cart"));
                }
            }
        });

        groupsButtons = $("a[data-cartgroupid]");
        $(groupsButtons).each(function (index, groupsButtons) {
            groupID = $(groupsButtons).data("cartgroupid")
            if (cartGroups != undefined &&  cartGroups.length > 0 && cartGroups.includes(groupID)) {
                personPresent = true;
                $(groupsButtons).addClass("RemoveFromGroupCart");
                $(groupsButtons).removeClass("AddToGroupCart");
                fa = $(groupsButtons).find("i.fa.fa-inverse");
                $(fa).addClass("fa-remove");
                $(fa).removeClass("fa-cart-plus");
                text = $(groupsButtons).find("span.cartActionDescription")
                if (text) {
                    $(text).text(i18next.t("Remove from Cart"));
                }
            } else {
                $(groupsButtons).addClass("AddToGroupCart");
                $(groupsButtons).removeClass("RemoveFromGroupCart");
                fa = $(groupsButtons).find("i.fa.fa-inverse");

                $(fa).removeClass("fa-remove");
                $(fa).addClass("fa-cart-plus");
                text = $(groupsButtons).find("span.cartActionDescription")
                if (text) {
                    $(text).text(i18next.t("Add to Cart"));
                }
            }
        });
    }

    $(document).on("updateCartMessage", updateLittleButtons);
    // end of the listener

    // add or remove people
    $(document).on("click", ".AddToPeopleCart", function () {
        clickedButton = $(this);
        window.CRM.cart.addPerson([clickedButton.data("cartpersonid")], function () {
            $(clickedButton).addClass("RemoveFromPeopleCart");
            $(clickedButton).removeClass("AddToPeopleCart");
            $('span i:nth-child(2)', clickedButton).addClass("fa-remove");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-cart-plus");
        });
    });

    $(document).on("click", ".RemoveFromPeopleCart", function () {
        clickedButton = $(this);
        window.CRM.cart.removePerson([clickedButton.data("cartpersonid")], function () {
            $(clickedButton).addClass("AddToPeopleCart");
            $(clickedButton).removeClass("RemoveFromPeopleCart");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-remove");
            $('span i:nth-child(2)', clickedButton).addClass("fa-cart-plus");
        });
    });

    // add or remove family
    $(document).on("click", ".AddToFamilyCart", function () {
        clickedButton = $(this);
        window.CRM.cart.addFamily(clickedButton.data("cartfamilyid"), function () {
            $(clickedButton).addClass("RemoveFromFamilyCart");
            $(clickedButton).removeClass("AddToFamilyCart");
            $('span i:nth-child(2)', clickedButton).addClass("fa-remove");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-cart-plus");
        });
    });

    $(document).on("click", ".RemoveFromFamilyCart", function () {
        clickedButton = $(this);
        window.CRM.cart.removeFamily(clickedButton.data("cartfamilyid"), function () {
            $(clickedButton).addClass("AddToFamilyCart");
            $(clickedButton).removeClass("RemoveFromFamilyCart");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-remove");
            $('span i:nth-child(2)', clickedButton).addClass("fa-cart-plus");
        });
    });

    // add or remove family
    $(document).on("click", ".AddToGroupCart", function () {
        clickedButton = $(this);
        window.CRM.cart.addGroup(clickedButton.data("cartgroupid"), function () {
            $(clickedButton).addClass("RemoveFromGroupCart");
            $(clickedButton).removeClass("AddToGroupCart");
            $('span i:nth-child(2)', clickedButton).addClass("fa-remove");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-cart-plus");
        });
    });

    $(document).on("click", ".RemoveFromGroupCart", function () {
        clickedButton = $(this);
        window.CRM.cart.removeGroup(clickedButton.data("cartgroupid"), function () {
            $(clickedButton).addClass("AddToGroupCart");
            $(clickedButton).removeClass("RemoveFromGroupCart");
            $('span i:nth-child(2)', clickedButton).removeClass("fa-remove");
            $('span i:nth-child(2)', clickedButton).addClass("fa-cart-plus");
        });
    });


});
