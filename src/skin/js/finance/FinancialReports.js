//
//  This code is under copyright not under MIT Licence
//  copyright   : 2018 Philippe Logel all right reserved not MIT licence
//                This code can't be incoprorated in another software without any authorizaion
//
//  Updated : 2018/05/30
//

$(function() {
    const initMultiSelect = function (selectId, addAllId, clearAllId, placeholder) {
        const $select = $("#" + selectId);
        if ($select.length === 0) {
            return;
        }

        $select.select2({
            width: '100%',
            placeholder: placeholder,
            closeOnSelect: false
        });

        $("#" + addAllId).on('click', function () {
            const all = [];
            $select.find("option").each(function () {
                all.push(this.value);
            });
            $select.val(all).trigger("change");
        });

        $("#" + clearAllId).on('click', function () {
            $select.val(null).trigger("change");
        });
    };

    initMultiSelect('family', 'addAllFamilies', 'clearAllFamilies', i18next.t('Filter by Family'));
    initMultiSelect('classList', 'addAllClasses', 'clearAllClasses', i18next.t('Classification'));
    initMultiSelect('fundsList', 'addAllFunds', 'clearAllFunds', i18next.t('Filter by Fund'));
});