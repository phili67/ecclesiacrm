$(function() {
	// Automatically convert all items that want Bootstrap tooltips.
  // All they need is the `data-tooltip` attribute and a `title`.
  if (window.CRM.showTooltip) {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover({
      html : true,
      content: function() {
          var content = $(this).attr("data-popover-content");
          return $(content).children(".popover-body").html();
      },
      title: function() {
          var title = $(this).attr("data-popover-content");
          return $(title).children(".popover-heading").html();
      },
      trigger: "hover"}
      ); 
    } else {
      $('[data-toggle="tooltip"]').tooltip('disable');
      $('[data-toggle="popover"]').popover('disable'); 
    }
});
