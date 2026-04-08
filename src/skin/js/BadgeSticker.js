$(function() {
    $(".my-colorpicker-back").colorpicker({
      color:window.CRM.back,
      inline:false,
      horizontal:true,
      right:true
    });

    // Fallback preview renderer for cart badge page.
    // On group badge page, groupbadge.js defines its own reloadLabel implementation.
    if (typeof window.CRM.reloadLabel !== 'function') {
      window.CRM.reloadLabel = function () {
        var preview = document.getElementById('previewImage');
        if (!preview) {
          return;
        }

        var mainTitle = $("#mainTitle").val() || window.CRM.mainTitle || "";
        var secondTitle = $("#secondTitle").val() || window.CRM.secondTitle || "";
        var thirdTitle = $("#thirdTitle").val() || window.CRM.thirdTitle || "";
        var imageName = $("#image").val() || window.CRM.image || "";
        var imagePosition = $("#imagePosition").val() || window.CRM.imagePosition || "Left";
        var titleColor = window.CRM.title || "#3A3";
        var backColor = window.CRM.back || "#F99";
        var rootPath = window.CRM.rootPath || "";
        var labeltype = $("#labeltype").val() || window.CRM.labeltype || "Tractor";
        var labelfontsize = $("#labelfontsize").val() || window.CRM.labelfontsize || "24";
        var labelfont = "Courier";
        if ($("#labelfont").length > 0) {
          var selected = $("#labelfont option:selected");
          labelfont = selected.length ? selected.text().trim() : (window.CRM.labelfont || "Courier");
        } else if (window.CRM.labelfont) {
          labelfont = window.CRM.labelfont;
        }

        // Real preview via server-side badge renderer (includes actual person data from cart).
        window.CRM.APIRequest({
          method: 'POST',
          path: 'cart/render/badge',
          data: JSON.stringify({
            mainTitle: mainTitle,
            secondTitle: secondTitle,
            thirdTitle: thirdTitle,
            title: titleColor,
            back: backColor,
            labelfont: labelfont,
            labeltype: labeltype,
            labelfontsize: labelfontsize,
            imageName: imageName,
            imagePosition: imagePosition
          })
        }, function (data) {
          if (data && data.success && data.imgData) {
            preview.src = data.imgData;
            return;
          }

          // Fallback local SVG preview if API fails.
          var labelTypeSizes = {
            Tractor: { width: 120, height: 26.5 },
            Badge: { width: 70, height: 40 },
            Badge2: { width: 77, height: 48 },
            "3670": { width: 64, height: 34 },
            "5160": { width: 66.675, height: 25.4 },
            "5161": { width: 101.6, height: 25.4 },
            "5162": { width: 100.807, height: 34 },
            "5163": { width: 101.6, height: 50.8 },
            "5164": { width: 4.0, height: 3.33 },
            "8600": { width: 66.6, height: 25.4 },
            "74536": { width: 102, height: 76 },
            L7163: { width: 99.1, height: 38.1 },
            C32019: { width: 85, height: 54 }
          };

          var baseWidth = 420;
          var baseHeight = 260;
          var sizeDef = labelTypeSizes[labeltype] || labelTypeSizes.Tractor;
          var ratio = sizeDef.width / sizeDef.height;
          var renderWidth = baseWidth;
          var renderHeight = Math.round(renderWidth / ratio);
          if (renderHeight > baseHeight) {
            renderHeight = baseHeight;
            renderWidth = Math.round(renderHeight * ratio);
          }

          var sx = function (v) {
            return Math.round((v * renderWidth) / baseWidth);
          };
          var sy = function (v) {
            return Math.round((v * renderHeight) / baseHeight);
          };

          var safeMain = mainTitle.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
          var safeSecond = secondTitle.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
          var safeThird = thirdTitle.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

          var imgHref = imageName ? (rootPath + "/Images/background/" + imageName) : "";

          var imageMarkup = '';
          if (imgHref) {
            if (imagePosition === 'Cover') {
              imageMarkup = '<image href="' + imgHref + '" x="0" y="0" width="' + renderWidth + '" height="' + renderHeight + '" preserveAspectRatio="xMidYMid slice" />';
            } else {
              var xPos = sx(14);
              var yPos = sy(24);
              var imgWidth = sx(130);
              var imgHeight = sy(90);
              var imgRatio = 'xMidYMid meet';
              var stripeWidth = Math.max(1, Math.round(renderWidth * (14 / sizeDef.width)));

              if (imagePosition === 'Left') {
                xPos = 0;
                yPos = 0;
                imgWidth = stripeWidth;
                imgHeight = renderHeight;
                imgRatio = 'none';
              } else if (imagePosition === 'Right') {
                xPos = Math.max(0, renderWidth - stripeWidth);
                yPos = 0;
                imgWidth = stripeWidth;
                imgHeight = renderHeight;
                imgRatio = 'none';
              }
              if (imagePosition === 'Center') {
                xPos = Math.max(0, Math.round((renderWidth - imgWidth) / 2));
              }
              imageMarkup = '<image href="' + imgHref + '" x="' + xPos + '" y="' + yPos + '" width="' + imgWidth + '" height="' + imgHeight + '" preserveAspectRatio="' + imgRatio + '" />';
            }
          }

          var radius = Math.max(2, Math.round(18 * Math.min(renderWidth / baseWidth, renderHeight / baseHeight)));
          var cx = Math.round(renderWidth / 2);

          var svg = '' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="' + renderWidth + '" height="' + renderHeight + '" viewBox="0 0 ' + renderWidth + ' ' + renderHeight + '">' +
            '<rect x="0" y="0" width="' + renderWidth + '" height="' + renderHeight + '" rx="' + radius + '" ry="' + radius + '" fill="' + backColor + '" />' +
            imageMarkup +
            '<text x="' + cx + '" y="' + sy(150) + '" text-anchor="middle" font-size="' + Math.max(10, sy(28)) + '" font-weight="700" fill="' + titleColor + '" font-family="Arial, sans-serif">' + safeMain + '</text>' +
            '<text x="' + cx + '" y="' + sy(185) + '" text-anchor="middle" font-size="' + Math.max(8, sy(20)) + '" fill="' + titleColor + '" font-family="Arial, sans-serif">' + safeSecond + '</text>' +
            '<text x="' + cx + '" y="' + sy(214) + '" text-anchor="middle" font-size="' + Math.max(8, sy(18)) + '" fill="' + titleColor + '" font-family="Arial, sans-serif">' + safeThird + '</text>' +
            '</svg>';

          preview.src = 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
        });
      };
    }

    window.CRM.reloadLabel();

    document.addEventListener('input', function (event) {
      switch (event.target.id) {
        case 'mainTitle':
        case 'secondTitle':
        case 'thirdTitle':
        case 'image':
        case 'imagePosition':
        case 'labeltype':
        case 'labelfont':
        case 'labelfontsize':
          window.CRM.reloadLabel();
          break;
        default:
          break;
      }
    }, false);

    $(".my-colorpicker-title").colorpicker({
      color:window.CRM.title,
      inline:false,
      horizontal:true,
      right:true
    });

    $(".delete-file").on('click', function () {
      var name = $(this).data("name");

      bootbox.confirm(i18next.t("Are you sure, you want to delete this image ?"), function(result){
        if (result) {
          window.CRM.APIRequest({
            method: 'POST',
            path: 'system/deletefile',
            data: JSON.stringify({"name": name})
          },function(data) {
            location.reload();
          });
        }
      });
    });

    $(".add-file").on('click', function () {
      var name = $(this).data("name");

      $("#image").val(name);

      window.CRM.image = name;

      window.CRM.reloadLabel();
    });

    $("#imagePosition").on('change', function () {
      window.CRM.imagePosition = this.value;
      window.CRM.reloadLabel();
    });

    $("#mainTitle,#secondTitle,#thirdTitle").on('keyup change', function () {
      window.CRM.reloadLabel();
    });

    $(".my-colorpicker-title").on('changeColor', function () {
      window.CRM.title = $(this).data('colorpicker').color.toHex();
      window.CRM.reloadLabel();
    });

    $(".my-colorpicker-back").on('changeColor', function () {
      window.CRM.back = $(this).data('colorpicker').color.toHex();
      window.CRM.reloadLabel();
    });
});
