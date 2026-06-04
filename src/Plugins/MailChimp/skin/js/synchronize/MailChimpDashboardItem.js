window.CRM.synchronize.renderers.MailchimpDisplay = function (data) {
    if (data.isActive) {
        var len = data.MailChimpLists.length;

        // now we empty the menubar lists
        $(".lists_class_menu").removeClass("hidden");

        var real_listMenu = $(".lists_class_menu");

        real_listMenu.html('');
        var listItems = '<a href="#" class="nav-link"> <i class="fas fa-list"></i> <p>' + i18next.t('Email Lists') + '</p><i class="fas fa-angle-left right"></i></a>';
        listItems += '<ul class="nav nav-treeview" style="display: block;">';

        for (var i = 0; i < len; i++) {
            var list = data.MailChimpLists[i];

            listItems += '<li class="nav-item listName' + list.id + '">'
                + '<a href="' + window.CRM.root + '/v2/mailchimp/managelist/' + list.id + '" class="nav-link "> <i class="fas fa-mail-bulk"></i> '
                + '<p>' + list.name + '</p></a>'
                + '</li>';
        }

        listItems += '</ul>';

        real_listMenu.html(listItems);

        if (data.firstLoaded == true) {
            window.CRM.notify('fas fa-info-circle', i18next.t("Mailchimp"), i18next.t("All the lists are now loaded in the CRM.<br><b>If you want to manage them, click this notification !</b>"), window.CRM.root + '/v2/mailchimp/dashboard', 'success', "top", 50000);
        }
    }
};