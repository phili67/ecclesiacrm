$(function() {
  // jQuery for v2/calendar/events/names
  $('.event-recurrance-patterns input[type=radio]').on('change',function() {
    $el = $(this);
    $container = $el.closest('.row');
    $input = $container.find('select, input[type=text]').prop({ disabled: false });
    $container.parent().find('select, input[type=text]').not($input).prop({ disabled: true });
  });
});
