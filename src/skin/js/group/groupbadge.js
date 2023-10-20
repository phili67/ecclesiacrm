$(function() {
    
    window.CRM.reloadLabel = function (callback) {
        window.CRM.APIRequest({
            method: 'POST',
            path: 'groups/render/sundayschool/badge',
            data: JSON.stringify({
                "title": window.CRM.title,
                "titlePosition": window.CRM.titlePosition,
                "back": window.CRM.back,
                "sundaySchoolName": window.CRM.sundaySchoolName,
                "sundaySchoolNamePosition":window.CRM.sundaySchoolNamePosition,
                "labelfont": window.CRM.labelfont, 
                "labeltype": window.CRM.labeltype, 
                "labelfontsize": window.CRM.labelfontsize, 
                "useQRCode": window.CRM.useQRCode,
                "groupID": window.CRM.groupID,
                /*"startrow": window.CRM.startrow, 
                "startcol": window.CRM.startcol, */
                "imageName": window.CRM.image,
                "imagePosition": window.CRM.imagePosition
            })
        }, function (data) {
            // we reload all the events
            document.getElementById('myimage').src = data.imgData;
            if (callback) {
                callback(data);
            }
        });
    }

    // we load the datas by default
    window.CRM.reloadLabel();

    document.addEventListener('input', function (event) {

        //alert (event.target.id);
        
        switch (event.target.id) {
            case 'titlePosition':
                window.CRM.titlePosition = event.target.options[event.target.selectedIndex].value;
                break;
            case 'sundaySchoolName':
                window.CRM.sundaySchoolName = event.target.value;
                break;
            case 'sundaySchoolNamePosition':
                window.CRM.sundaySchoolNamePosition = event.target.options[event.target.selectedIndex].value;
                break;
            case 'imagePosition':
                window.CRM.imagePosition = event.target.options[event.target.selectedIndex].value;
                break;
            case 'labelfont':
                window.CRM.labelfont = event.target.options[event.target.selectedIndex].innerText;
                break;
            case 'labeltype':
                window.CRM.labeltype = event.target.options[event.target.selectedIndex].value;
                break;
            case 'labelfontsize':
                window.CRM.labelfontsize = event.target.options[event.target.selectedIndex].value;
                break;
            case 'startrow':
                window.CRM.startrow = parseInt(event.target.value);
                break;
            case 'startcol':
                window.CRM.startcol = parseInt(event.target.value);
                break;
            case 'useQRCode':
                window.CRM.useQRCode = (event.target.checked)?1:0;
                break;
        }    

        window.CRM.reloadLabel();

    }, false);


    $(".my-colorpicker-title").on('changeColor', function (e) {
        window.CRM.title = $(this).data('colorpicker').color.toHex();//.toString('hex');

        window.CRM.reloadLabel();
    });

    $(".my-colorpicker-back").on('changeColor', function (e) {
        window.CRM.back = $(this).data('colorpicker').color.toHex();//.toString('hex');

        window.CRM.reloadLabel();        
    });
});