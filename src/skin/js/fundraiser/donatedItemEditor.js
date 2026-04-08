$(function () {
    var $form = {
        Item: $('#Item'),
        Multibuy: $('#Multibuy'),
        Donor: $('#Donor'),
        Title: $('#Title'),
        EstPrice: $('#EstPrice'),
        MaterialValue: $('#MaterialValue'),
        MinimumPrice: $('#MinimumPrice'),
        Buyer: $('#Buyer'),
        SellPrice: $('#SellPrice'),
        Description: $('#Description'),
        PictureURL: $('#PictureURL'),
        NumberCopies: $('#NumberCopies'),
        Image: $('#image')
    };

    // Initialize Select2 for dropdowns
    $form.Donor.select2();
    $form.Buyer.select2();

    // Update image preview when URL changes
    $form.PictureURL.on('change paste keyup', function () {
        $form.Image.attr('src', $(this).val());
    });

    // Browse button for EDrive
    $('#donatedItemPicture').on('click', function () {
        var donatedItemId = $(this).data('donateditemid');
        window.open(window.CRM.root + '/browser/browse.php?type=publicDocuments&DonatedItemID=' + donatedItemId);
    });

    // Monitor window focus to check for picture updates from EDrive
    $(window).on('focus', function () {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/donateditem/currentpicture',
            data: JSON.stringify({ DonatedItemID: window.CRM.currentDonatedItemID })
        }, function (data) {
            if (data.status === 'success' && window.CRM.currentPicture !== data.picture) {
                $form.Image.attr('src', data.picture);
                $form.PictureURL.val(data.picture);
                window.CRM.currentPicture = data.picture;
            } else {
                $form.Image.attr('src', window.CRM.currentPicture);
            }
        });
    });

    // Replicate item handler
    $('#donatedItemGo').on('click', function () {
        var donatedItemId = $(this).data('donateditemid');
        var count = parseInt($form.NumberCopies.val(), 10) || 0;

        if (count <= 0) {
            bootbox.alert(i18next.t('Please enter a number greater than 0.'));
            return;
        }

        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/replicate',
            data: JSON.stringify({ DonatedItemID: donatedItemId, count: count })
        }, function (data) {
            if (data.status === 'success') {
                window.location.href = window.CRM.root + '/v2/fundraiser/editor/' + window.CRM.currentFundraiser;
            }
        });
    });

    // Gather form data helper
    function gatherFormData() {
        return {
            currentFundraiser: window.CRM.currentFundraiser,
            currentDonatedItemID: window.CRM.currentDonatedItemID,
            Item: $form.Item.val(),
            Multibuy: $form.Multibuy.is(':checked'),
            Donor: $form.Donor.val(),
            Title: $form.Title.val(),
            EstPrice: $form.EstPrice.val(),
            MaterialValue: $form.MaterialValue.val(),
            MinimumPrice: $form.MinimumPrice.val(),
            Buyer: $form.Buyer.val(),
            SellPrice: $form.SellPrice.val(),
            Description: $form.Description.val(),
            PictureURL: $form.PictureURL.val()
        };
    }

    // Submit handler (consolidated for both Save and Save & Add)
    function submitForm(andAdd) {
        var formData = gatherFormData();

        window.CRM.APIRequest({
            method: 'POST',
            path: 'fundraiser/donatedItemSubmit',
            data: JSON.stringify(formData)
        }, function (data) {
            if (data.status === 'success') {
                if (andAdd) {
                    window.location.href = window.CRM.root + '/v2/fundraiser/donatedItemEditor/0/' + window.CRM.currentFundraiser;
                } else {
                    window.location.href = window.CRM.root + '/v2/fundraiser/editor/' + window.CRM.currentFundraiser;
                }
            }
        });
    }

    // Save button
    $('#DonatedItemSubmit').on('click', function (event) {
        event.preventDefault();
        submitForm(false);
    });

    // Save and Add button
    $('#DonatedItemSubmitAndAdd').on('click', function (event) {
        event.preventDefault();
        submitForm(true);
    });

    // Cancel button
    $('#DonatedItemCancel').on('click', function (event) {
        event.preventDefault();
        window.location.href = window.CRM.root + '/v2/fundraiser/editor/' + window.CRM.currentFundraiser;
    });
});
