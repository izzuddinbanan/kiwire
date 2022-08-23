/*
    Insert HTML Symbols Plugin
*/

(function () {
    var js1 = 'https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-core.min.js';
    var js2 = 'https://cdnjs.cloudflare.com/ajax/libs/rangy/1.3.0/rangy-textrange.min.js'

    //Get js1 first, then after loaded, get js2
    _cb.getScripts([js1], function () {

        _cb.getScripts([js2], function () {

        });

    });

    var html = '<div class="is-modal is-side' + (_cb.settings.sidePanel == 'right' ? '' : ' fromleft') + ' viewsymbols" style="width:338px;z-index:10004;">' +
                    '<div class="is-side-close" style="z-index:1;width:40px;height:40px;position:absolute;top:0px;right:0px;box-sizing:border-box;padding:0;line-height:40px;font-size: 12px;color:#777;text-align:center;cursor:pointer;"><svg class="is-icon-flex" style="fill:rgba(0, 0, 0, 0.47);width:40px;height:40px;"><use xlink:href="#ion-ios-close-empty"></use></svg></div>' +
                    '' +
                    '<iframe src="about:blank" style="width:100%;height:100%;position:absolute;top:0;left:0;border: none;"></iframe>' +
                    '' +
                '</div>';

    _cb.addHtml(html);

    var button = '<button class="insertsymbol-button" title="Symbol" style="font-size:15px;vertical-align:bottom;">' +
                    '&#8486;' +
                '</button>';

    _cb.addButton('symbols', button, '.insertsymbol-button', function () {

        var $modal = jQuery('.is-side.viewsymbols');

        _cb.showSidePanel($modal);

        var scriptPath = _cb.getScriptPath();
        $modal.find('iframe').attr('src', scriptPath + 'plugins/symbols/symbols.html');

        $modal.find('.is-side-close').off("click");
        $modal.find(".is-side-close").on('click', function () {
            _cb.hideSidePanel()
        });

    });

})();