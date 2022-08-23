/*
    Background Changer Plugin
*/

(function () {


    var html = '<div class="is-modal backgroundchanger" style="z-index:10004;position:fixed;width:500px;height:350px;top:50%;left:50%;margin-top:-155px;margin-left:-250px;background:#fff;border: 1px solid rgb(199, 199, 199);box-shadow: 0px 5px 5px 5px rgba(0, 0, 0, 0.02);">' +
                    '<div class="is-modal-bar is-draggable" style="position: absolute;top: 0;left: 0;width: 100%;z-index:1;line-height:1.5;height:32px;">' + _cb.out('Background Manager') +
                        '<div class="is-modal-close" style="z-index:1;width:32px;height:32px;position:absolute;top:0px;right:0px;box-sizing:border-box;padding:0;line-height:32px;font-size: 12px;color:#777;text-align:center;cursor:pointer;">&#10005;</div>' +
                    '</div>' +
                    '<iframe style="position: absolute;top: 0;left: 0;width:100%;height:100%;border:none;border-top:32px solid transparent;margin:0;box-sizing:border-box;" src="about:blank"></iframe>' +
                '</div>';

    _cb.addHtml(html);

    var button = '<button class="backgroundchanger-button" title="Background Manager" style="font-size:15px;vertical-align:bottom;">' +
                    '<i class="ficon feather icon-image"></i>' +
                '</button>';

    _cb.addButton('backgroundchanger', button, '.backgroundchanger-button', function () {

        var $modal = jQuery('.is-modal.backgroundchanger');
        $modal.addClass('active');

        $modal.find('.is-modal-close').on('click', function () {
            $modal.removeClass('active')
        });

        var scriptPath = _cb.getScriptPath();
        $modal.find('iframe').attr('src', scriptPath + 'plugins/backgroundchanger/backgroundchanger.html');

    });

})();

