$(function () {
  var $form = $('#taxReportForm');
  var $year = $('#Year');

  if (!$form.length || !$year.length) {
    return;
  }

  var currentYear = new Date().getFullYear();
  var minYear = 1900;
  var maxYear = currentYear + 1;

  function normalizeYear() {
    var rawValue = String($year.val() || '').trim();
    var year = parseInt(rawValue, 10);

    if (!Number.isInteger(year) || rawValue.length !== 4 || year < minYear || year > maxYear) {
      $year.addClass('is-invalid');
      return false;
    }

    $year.removeClass('is-invalid');
    $year.val(year);
    return true;
  }

  $year.on('input blur', function () {
    if ($year.hasClass('is-invalid')) {
      normalizeYear();
    }
  });

  $form.on('submit', function (event) {
    if (!normalizeYear()) {
      event.preventDefault();
      $year.trigger('focus');
    }
  });
});
