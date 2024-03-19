$(function() {
    $("#myWish").on('click',function showAlert() {
        $("#Menu_Banner").alert();
        window.setTimeout(function () {
            $("#Menu_Banner").alert('close');
        }, window.CRM.timeOut);
    });

    $("#Menu_Banner").fadeTo(window.CRM.timeOut, 500).slideUp(500, function () {
        $("#Menu_Banner").slideUp(500);
    });

    $.widget.bridge('uibutton', $.ui.button);

    $('.connectedSortable').sortable({
        placeholder:'sort-highlight',
        connectWith:'.connectedSortable',
        handle:'.card-header, .nav-tabs, .card-body',
        forcePlaceholderSize:true,
        tolerance: 'pointer',// fix the drag in a small placeholder
        zIndex:999999,
        stop: function( ev ) {

            var res = [];

            var getChild_Left = $('.left-plugins').children();

            getChild_Left.each(function(i,v){
                // push in fruits array, an array of data-fruit
                if ($(v).data('name') != undefined) {
                    res.push(['left', i, $(v).data('name')])
                }
            });

            var getChild_Center = $('.center-plugins').children();

            getChild_Center.each(function(i,v){
                // push in fruits array, an array of data-fruit
                if ($(v).data('name') != undefined) {
                    res.push(['center', i, $(v).data('name')])
                }
            });

            var getChild_right = $('.right-plugins').children();

            getChild_right.each(function(i,v){
                if ($(v).data('name') != undefined) {
                    res.push(['right', i, $(v).data('name')])
                }
            })

            var getChild_top = $('.top-plugins').children();

            getChild_top.each(function(i,v){
                if ($(v).data('name') != undefined) {
                    res.push(['top', i, $(v).data('name')])
                }
            })

            window.CRM.APIRequest({
                method: 'POST',
                path: 'plugins/addDashboardPlaces',
                data: JSON.stringify({"dashBoardItems":res})
            },function (data) {
            });
        }})
    $('.connectedSortable .card-header').css('cursor','move');

    /*
    * Add remove events to boxes
    */
    $("[data-card-widget='remove']").on('click',function() {
        //Find the box parent
        var box = $(this).parents(".card").first();
        //Find the body and the footer
        var name = box.data("name");

       /*bootbox.confirm({
            title: i18next.t("Remove Plugin") + "?",
            message: i18next.t("You're about to remove the plugin from yout dashboard !!!"),
            buttons: {
                cancel: {
                    label: '<i class="fas fa-times"></i> ' + i18next.t("Cancel")
                },
                confirm: {
                    label: '<i class="fas fa-check"></i> ' + i18next.t("Confirm")
                }
            },
            callback: function (result) {
                if (result == true)// only event can be drag and drop, not anniversary or birthday
                {*/
                    window.CRM.APIRequest({
                        method: 'POST',
                        path: 'plugins/removeFromDashboard',
                        data: JSON.stringify({"name": name})
                    },function (data) {
                        window.CRM.DisplayAlert(i18next.t("Dashboard Item"), i18next.t("Removed from your dashboard !"));
                    });
                /*}
            }
        });*/
    });

    /*
    * Add remove events to boxes
    */
    $("[data-card-widget='collapse']").on('click',function() {
        //Find the box parent
        var box = $(this).parents(".card").first();
        //Find the body and the footer
        var name = box.data("name");

        window.CRM.APIRequest({
            method: 'POST',
            path: 'plugins/collapseFromDashboard',
            data: JSON.stringify({"name": name})
        },function (data) {
        });
    });
});
