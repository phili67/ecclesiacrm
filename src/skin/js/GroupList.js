/* Copyright Philippe Logel 2018 all right reserved */

$(document).ready(function () {
  window.CRM.groupsInCart = 0;
  $.ajax({
    method: "GET",
    url: window.CRM.root + "/api/groups/groupsInCart",
    dataType: "json"
  }).done(function (data) {
    window.CRM.groupsInCart = data.groupsInCart;
  });

  $("#addNewGroup").click(function (e) {
    var groupName = $("#groupName").val(); // get the name of the group from the textbox
    if (groupName) // ensure that the user entered a group name
    {      
      window.CRM.APIRequest({
        method: 'POST',
        path: 'groups/',
        data: JSON.stringify({'groupName': groupName})
      }).done(function(data) {    
        window.CRM.dataTableList.row.add(data);                                //add the group data to the existing window.CRM.dataTableListable
        window.CRM.dataTableList.rows().invalidate().draw(true);               //redraw the window.CRM.dataTableListable
        $("#groupName").val(null);
        window.CRM.dataTableList.ajax.reload();// PL : We should reload the table after we add a group so the button add to group is disabled
      });
    }
    else {

    }
  });

  window.CRM.dataTableList = $("#groupsTable").DataTable({
    "initComplete": function( settings, json ) {
        if (window.groupSelect != null)
        {
          window.CRM.dataTableList.search(window.groupSelect).draw();
        }
    },
    "language": {
      "url": window.CRM.plugin.dataTable.language.url
    },
    responsive: true,
    ajax: {
      url: window.CRM.root + "/api/groups/",
      type: 'GET',
      dataSrc: "Groups"
    },
    columns: [
      {
        width: 'auto',
        title:i18next.t('Group Name'),
        data: 'Name',
        render: function (data, type, full, meta) {
            return '<a href=\'GroupView.php?GroupID=' + full.Id + '\'><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-search-plus fa-stack-1x fa-inverse"></i></span></a><a href=\'GroupEditor.php?GroupID=' + full.Id + '\'><span class="fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span></a>' + data;
        }
      },
      {
        width: 'auto',
        title:i18next.t('Members'),
        data: 'memberCount',
        searchable: false,
        defaultContent: "0"
      },
      {
        width: 'auto',
        title:i18next.t('Group Cart Status'),
        searchable: false,
        render: function (data, type, full, meta) {
          // we add the memberCount, so we could disable the button Add All
          
          var activLink = '';
          if (full.memberCount == 0){
            activLink=' disabled'; // PL : We disable the button Add All when there isn't any member in the group
          }
      
          if ($.inArray(full.Id, window.CRM.groupsInCart) > -1) {
            return "<span>"+i18next.t("All members of this group are in the cart")+"</span>&nbsp;<a class=\"btn btn-xs btn-danger \" id=\"removeGroupFromCart\" data-groupid=\"" + full.Id + "\">" + i18next.t("Remove all") + "</a>";
          } else if (window.CRM.showCart){
            return "<span>"+i18next.t("Not all members of this group are in the cart")+"</span>&nbsp;<a id=\"AddGroupToCart\" class=\"btn btn-xs btn-primary"+activLink+"\" data-groupid=\"" + full.Id + "\">" + i18next.t("Add all") + "</a>";
          } else {
            return i18next.t("Cart isn't showable");
          }
        }
      },
      {
        width: 'auto',
        title:i18next.t('Group Type'),
        data: 'groupType',
        defaultContent: "",
        searchable: true,
        render: function (data, type, full, meta) {
          if (data)
          {
            return data;
          }
          else
          {
            return i18next.t('Unassigned');
          }
        }
      }
    ]
  });
  
  $("#groupsTable").on( 'search.dt', function () {              
    var info = window.CRM.dataTableList.page.info();       
    $('#numberOfGroups').html(info.recordsDisplay);
  });
  
  $('#table-filter').on('change', function(){
       window.CRM.dataTableList.search(this.value).draw();
       localStorage.setItem("groupSelect",this.selectedIndex);
       
       var info = window.CRM.dataTableList.page.info();       
       $('#numberOfGroups').html(info.recordsDisplay);
  });
  
  $(document).on("click","#AddGroupToCart",function(link){
    var groupid = $(this).data("groupid");
    var parent = $(this).parent().find("span");
    window.CRM.cart.addGroup(groupid,function(data){
        link.target.id = "removeGroupFromCart";
        link.target.className = "btn btn-xs btn-danger";
        link.target.innerText = i18next.t("Remove all");
        parent.text(i18next.t("All members of this group are in the cart"));
    });
  });
  
  $(document).on("click","#removeGroupFromCart",function(link){
    var groupid = $(this).data("groupid");
    var parent = $(this).parent().find("span");
    window.CRM.cart.removeGroup(groupid,function(data){
        link.target.id = "AddGroupToCart";    
        link.target.className = "btn btn-xs btn-primary";
        link.target.innerText = i18next.t("Add all");
        parent.text(i18next.t("Not all members of this group are in the cart"));
    });
  });

});
