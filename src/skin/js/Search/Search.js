$(document).ready(function () {
    var elements = [];
    var available_search_type = [];
    var buildMenu = false;

    $("#searchCombo").select2();
    $("#searchComboGroup").select2();

    $("#searchCombo").select2().on("change", function (e) {
        if (buildMenu == true) {
            return;
        }

        $("#searchComboGroup").parent().hide();

        var data = $(this).select2('data');

        elements.length = 0;

        $.each(available_search_type, function (index,val) {
            $('#'+val).prop('disabled', false);
        });

        if (data.length > 0) {
            for (i = 0; i < data.length; i++) {
                var element = data[i].id;
                var pos = element.indexOf("-");
                var option_Type = element.substr(0,pos);
                elements.push(element);

                $('#'+option_Type).prop('disabled', true);

                if (option_Type == 'GroupType') {
                    // we've to show the group belonging to the type
                    loadGroupByType(element);
                }
            }
        }
    });

    function loadGroupByType(GroupType) {
        var real_GroupType = GroupType.substr(GroupType.indexOf("-")+1);
 
        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/getGroupForTypeID/',
            data: JSON.stringify({"GroupType": real_GroupType})
        }).done(function (data) {
            if (real_GroupType >= 0) {
                $("#searchComboGroup").parent().show();
            }
        });
    }

    function loadSearchCombo() {
        // first we clean all the drop down
        buildMenu = true;

        window.CRM.APIRequest({
            method: 'POST',
            path: 'search/comboElements/'
        }).done(function (data) {
            $.each(data, function (index, value) {
                var optgroup = $('<optgroup>');
                optgroup.attr('id', value[0]);
                optgroup.attr('label', index);

                available_search_type.push(value[0])

                $.each(value[1], function (index2, value2) {
                    var option = new Option(value2, index2, false, false);
                    optgroup.append(option);

                });
                $("#searchCombo").append(optgroup).trigger('change');
            });

            buildMenu = false;
        });
    }

    $("#searchComboGroup").parent().hide();
    loadSearchCombo();
});
