$(function() {

    $("#Donor").select2();
    $("#Buyer").select2();

    $("#donatedItemPicture").on('click', function () {
        var donatedItem = $(this).data('donateditemid');

        window.open(window.CRM.root + '/browser/browse.php?DonatedItemID=' + donatedItem);
    });

    $("#PictureURL").on("change paste keyup",function () {
        $("#image").attr("src",$(this).val());
    });

    $(window).on('focus', function () {
        window.CRM.APIRequest({
            method: "POST",
            path: "fundraiser/donateditem/currentpicture",
            data: JSON.stringify({"DonatedItemID": window.CRM.currentDonatedItemID})
        },function (data) {
            if (data.status == "success" && window.CRM.currentPicture != data.picture) {
                $("#image").attr("src",data.picture);
                $("#PictureURL").val(data.picture);
                window.CRM.currentPicture = data.picture;
                //location.reload();
            } else {
                $("#image").attr("src",window.CRM.currentPicture);
            }
        });
    });

    $("#donatedItemGo").on('click', function () {
        var donatedItem = $(this).data('donateditemid');
        var count = $("#NumberCopies").val();

        // TODO : test if count = 0 and if donatedItem exist

        window.CRM.APIRequest({
            method: "POST",
            path: "fundraiser/replicate",
            data: JSON.stringify({"DonatedItemID": donatedItem, "count": count})
        },function (data) {
            if (data.status == "success") {
                window.location.href = window.CRM.root + "/v2/fundraiser/editor/" + window.CRM.currentFundraiser;
            }
        });

    })

    $("#DonatedItemSubmit").on('click', function () {
        var Item = $("#Item").val();
        var Multibuy = $("#Multibuy").is(':checked');
        var Donor = $("#Donor").val();
        var Title = $("#Title").val();
        var EstPrice = $("#EstPrice").val();
        var MaterialValue = $("#MaterialValue").val();
        var MinimumPrice = $("#MinimumPrice").val();
        var Buyer = $("#Buyer").val();
        var SellPrice = $("#SellPrice").val();
        var Description = $("#Description").val();
        var PictureURL = $("#PictureURL").val();


        window.CRM.APIRequest({
            method: "POST",
            path: "fundraiser/donatedItemSubmit",
            data: JSON.stringify({
                "currentFundraiser": window.CRM.currentFundraiser, "currentDonatedItemID": window.CRM.currentDonatedItemID,
                "Item": Item, "Multibuy": Multibuy,
                "Donor": Donor, "Title": Title,
                "EstPrice": EstPrice, "MaterialValue": MaterialValue,
                "MinimumPrice": MinimumPrice, "Buyer": Buyer,
                "SellPrice": SellPrice, "Description": Description,
                "PictureURL": PictureURL
            })
        },function (data) {
            if (data.status == "success") {
                window.location.href = window.CRM.root + "/v2/fundraiser/editor/" + window.CRM.currentFundraiser;
            }
        });
    });

    $("#DonatedItemSubmitAndAdd").on('click', function () {
        var Item = $("#Item").val();
        var Multibuy = $("#Multibuy").is(':checked');
        var Donor = $("#Donor").val();
        var Title = $("#Title").val();
        var EstPrice = $("#EstPrice").val();
        var MaterialValue = $("#MaterialValue").val();
        var MinimumPrice = $("#MinimumPrice").val();
        var Buyer = $("#Buyer").val();
        var SellPrice = $("#SellPrice").val();
        var Description = $("#Description").val();
        var PictureURL = $("#PictureURL").val();


        window.CRM.APIRequest({
            method: "POST",
            path: "fundraiser/donatedItemSubmit",
            data: JSON.stringify({
                "currentFundraiser": window.CRM.currentFundraiser, "currentDonatedItemID": window.CRM.currentDonatedItemID,
                "Item": Item, "Multibuy": Multibuy,
                "Donor": Donor, "Title": Title,
                "EstPrice": EstPrice, "MaterialValue": MaterialValue,
                "MinimumPrice": MinimumPrice, "Buyer": Buyer,
                "SellPrice": SellPrice, "Description": Description,
                "PictureURL": PictureURL
            })
        },function (data) {
            if (data.status == "success") {
                window.location.href = window.CRM.root + "/v2/fundraiser/donatedItemEditor/0/" + window.CRM.currentFundraiser;
            }
        });
    });

    $("#DonatedItemCancel").on('click', function () {
        window.location.href = window.CRM.root + "/v2/fundraiser/editor/" + window.CRM.currentFundraiser;
    })
});
