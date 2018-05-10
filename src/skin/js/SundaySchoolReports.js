function setGroupsIdsAttendees(selectControl)
{
  var res = '';
  
  $('#GroupID :selected').each(function(){
     //selected[$(this).val()]=$(this).text();
     res = $(selectControl).val()+',';
  });
  
  res = res.slice(0, -1);
  
  $('#exportCheckOutPDF').attr( 'data-makecheckoutgroupid', res )
}
  
$( "#GroupID" ).click(function() {
  setGroupsIdsAttendees(this);
});

$( "#GroupID" ).change(function() {
  setGroupsIdsAttendees(this);
});