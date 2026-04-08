$(function () {
    var $form = $('#batchWinnerForm');
    var $priceInputs = $('.price-input');

    // Format price input on blur (user finished typing)
    $priceInputs.on('blur', function () {
        var $input = $(this);
        var value = $input.val();

        if (value === '' || value === '0') {
            $input.val('');
            return;
        }

        // Parse decimal value
        var numValue = parseFloat(value.replace(/[^0-9.,]/g, '').replace(',', '.'));

        if (!isNaN(numValue) && numValue > 0) {
            // Format to 2 decimal places
            $input.val(numValue.toFixed(2));
            $input.removeClass('is-invalid');
        } else {
            $input.addClass('is-invalid');
        }
    });

    // Allow only digits and decimal point on keypress
    $priceInputs.on('keypress', function (event) {
        var char = String.fromCharCode(event.which);
        if (!/[0-9.,]/.test(char)) {
            event.preventDefault();
        }
    });

    // Validate form before submit
    $form.on('submit', function (event) {
        var hasAnyEntry = false;
        var hasInvalidEntry = false;

        // Check for valid entries (all three fields or none)
        for (var row = 0; row < 10; row++) {
            var itemVal = $('select[name="Item' + row + '"]').val();
            var paddleVal = $('select[name="Paddle' + row + '"]').val();
            var priceVal = $('input[name="SellPrice' + row + '"]').val().trim();

            var itemSelected = itemVal && itemVal !== '0';
            var paddleSelected = paddleVal && paddleVal !== '0';
            var priceEntered = priceVal !== '';

            // Count entries
            if (itemSelected || paddleSelected || priceEntered) {
                hasAnyEntry = true;

                // Validate complete entry: all three must be filled
                if (!itemSelected || !paddleSelected || !priceEntered) {
                    hasInvalidEntry = true;
                    break;
                }

                // Validate price format
                var numPrice = parseFloat(priceVal.replace(/[^0-9.,]/g, '').replace(',', '.'));
                if (isNaN(numPrice) || numPrice <= 0) {
                    hasInvalidEntry = true;
                    break;
                }
            }
        }

        if (!hasAnyEntry) {
            bootbox.alert(i18next.t('Please enter at least one winner.'));
            event.preventDefault();
            return false;
        }

        if (hasInvalidEntry) {
            bootbox.alert(i18next.t('Incomplete entries found. Please fill in all fields (Item, Winner, Price) for each row you want to save.'));
            event.preventDefault();
            return false;
        }
    });
});
