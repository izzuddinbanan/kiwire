$("button.save-button").on("click", function (e) {

    let data = $("form").serialize();

    $.ajax({
        url: "ajax/ajax_integration_social.php?action=update",
        method: "POST",
        data : data,
        success: function (data) {

            if (data['status'] === "success") {

                swal("Success", data['message'], "success");

            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function (data) {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }
    })

});


$(".btn-script-download").on("click", function (e) {


  // Start file download.
  download($(this).data("dev") + ".txt", scriptToDownload($(this).data("dev")));


});


function download(filename, text) {

    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
}
  

function scriptToDownload(type){

    switch(type) {
        case "Mikrotik":
            var data =  scriptMikrotik();
          break;
    }

    return  data;

}


function scriptMikrotik() {
    return '/ip hotspot walled-garden\n'+
    'add comment="place hotspot rules here" disabled=yes\n'+
    'add comment=SOCIALGATE dst-host=socialgate.synchroweb.com\n'+
    'add disabled=yes dst-host=socialgate.kiwire.net\n'+
    'add comment=FACEBOOK dst-host=*facebook*\n'+
    'add dst-host=*fbcdn*\n'+
    'add dst-host=*fbsbx*\n'+
    'add dst-host=*akamai*\n'+
    'add dst-host=*atdmt*\n'+
    'add dst-host=*doubleclick*\n'+
    'add comment=TWITTER dst-host=*twitter*\n'+
    'add dst-host=*twimg*\n'+
    'add dst-host=*.syndication.twimg.com\n'+
    'add dst-host=*syndication*\n'+
    'add dst-host=*ecdns*\n'+
    'add dst-host=*edgecastcdn*\n'+
    'add dst-host=*syndication.twitter.com\n'+
    'add dst-host=cdn.syndication.twimg.com\n'+
    'add disabled=yes dst-host=*t.co*\n'+
    'add dst-host=*platform.twitter.com*\n'+
    'add comment=WECHAT disabled=yes dst-host=*qq.com*\n'+
    'add disabled=yes dst-host=*weixin*\n'+
    'add disabled=yes dst-host=weixinsxy\n'+
    'add comment=VK dst-host=*vk.com\n'+
    'add dst-host=*oauth.vk.com\n'+
    'add dst-host=*vk.me\n'+
    'add comment=ZALO dst-host=*.zaloapp.com\n'+
    'add dst-host=*.zalo.me\n'+
    'add dst-host=*.sp.zdn.vn\n'+
    'add dst-host=*accounts.zingmp3.vn\n'+
    'add dst-host=*accounts.news.zing.vn\n'+
    'add dst-host=*accounts.zingtv.vn\n'+
    'add dst-host=*accounts.zalo.ai\n'+
    'add comment=KAKAO dst-host=*kakao.com\n'+
    'add dst-host=*cloudfront.net*\n'+
    'add dst-host=*accounts-ec2avpvy.kgslb.com*\n'+
    'add comment=LINE dst-host=*.line-apps.com\n'+
    'add dst-host=*.line.me\n'+
    'add dst-host=*access.line.me\n'+
    'add dst-host=*line.me*';
}