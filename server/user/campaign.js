
var campaign_showed = [], campaign_current = "";
var campaign_time = 0;


$.ajax({
    url: "/user/campaign/?session=" + session_id,
    method: "post",
    data: {
        position: "pre",
        source: "internal"
    },
    success: function (response) {


        if (response['status'] === "success"){


            for (var campaign_c = 1; campaign_c < 6; campaign_c++){


                if (response['data']['campaign-' + campaign_c] !== undefined && response['data']['campaign-' + campaign_c] !== null) {
                    var campaign = 'campaign-' + campaign_c;


                    // only process campaign data

                    if (response['data'][campaign].length > 0) {


                        var campaign_ads = false;


                        $("#" + campaign).append($("<div class='frame'><div class='slide_element'></div></div>")).addClass("mightyslider_modern_skin").css("min-height", "350px").css("min-width", "100%");


                        for (var kindex = 0; kindex < response['data'][campaign].length; kindex++) {


                            var slide_element = $('<div class="slide"></div>');

                            var slide_setting = '';


                            // set general setting to each slide

                            slide_setting += "source: '" + response['data'][campaign][kindex]['source'] + "', ";
                            slide_setting += "name: '" + response['data'][campaign][kindex]['name'] + "', ";
                            slide_setting += "viewport: '" + response['data'][campaign][kindex]['viewport'] + "', ";
                            slide_setting += "description: '" + response['data'][campaign][kindex]['desc'] + "', ";

                            slide_setting += "start: '" + response['data'][campaign][kindex]['start'] + "', ";
                            slide_setting += "end: '" + response['data'][campaign][kindex]['end'] + "', ";

                            // set specific setting

                            if (response['data'][campaign][kindex]['type'] === "img") {


                                slide_setting += "cover: '" + response['data'][campaign][kindex]['image'] + "', ";
                                slide_setting += "link: {url: '/user/campaign/click/?session=" + session_id + "&load=" + response['data'][campaign][kindex]['url'] + "&name=" + btoa(encodeURIComponent(response['data'][campaign][kindex]['name'])) + "&source=" + response['data'][campaign][kindex]['source'] + "', target: '_self'},";


                            } else if (response['data'][campaign][kindex]['type'] === "youtube") {


                                slide_setting += "video: '" + decodeURIComponent(response['data'][campaign][kindex]['url']) + "', ";


                            } else if (response['data'][campaign][kindex]['type'] === "vid") {


                                slide_setting += "type: 'video', ";

                                slide_setting += "mp4: '" + decodeURIComponent(response['data'][campaign][kindex]['url']) + "', ";

                                slide_setting += "videoFrame: '/libs/mightyslider/src/videoframes/mediaelementjs.php', ";


                                if (response['data'][campaign][kindex]['image'].length > 0) {

                                    slide_setting += "cover: '" + decodeURIComponent(response['data'][campaign][kindex]['image']) + "'";

                                } else slide_setting += "cover: '" + decodeURIComponent(response['data'][campaign][kindex]['url']) + "'";


                                slide_setting = slide_setting.trim(",");


                            } else if (response['data'][campaign][kindex]['type'] === "redirect") {


                                // if got redirection, then redirect

                                window.location.href = response['data'][campaign][kindex]['url'];


                            }


                            slide_element.data("mightyslider", slide_setting);

                            $(".slide_element").append(slide_element);


                            slide_setting = null;

                            slide_element = null;

                            campaign_ads = true;


                        }


                        if (campaign_ads === true) {

                            $frame = $('.frame', $('#' + campaign));

                            $frame.mightySlider({

                                speed: 1000,
                                startAt: 0,
                                autoScale: 0,
                                autoResize: 0,
                                easing: 'easeOutExpo',
                                videoFrame: '/lib/mightyslider/src/videoframes/mediaelementjs.php',

                                navigation: {
                                    horizontal: 1,
                                    slideSize: '100%'
                                },

                                dragging: {
                                    touchDragging: 1
                                },

                                cycling: {
                                    cycleBy: 'slides',
                                    loop: 1,
                                    pauseTime: 6000
                                },

                                commands: {
                                    pages: 0,
                                    buttons: 0
                                }

                            }, {

                                active: function (name, index) {


                                    $("." + campaign + "-name").html(this.slides[index].options.name.split(" || ")[1]);
                                    $("." + campaign + "-description").html(this.slides[index].options.description);

                                    // formatting start date

                                    var date_space = $("." + campaign + "-startdate");
                                    var date_format = date_space.data("date-format");

                                    date_space.html(Date.parse(this.slides[index].options.start).toString((date_format === undefined ? "dd-MMM-yyyy" : date_format)));


                                    // formatting end date

                                    date_space = $("." + campaign + "-enddate");
                                    date_format = date_space.data("date-format");

                                    date_space.html(Date.parse(this.slides[index].options.end).toString((date_format === undefined ? "dd-MMM-yyyy" : date_format)));


                                    // send data about impression

                                    if (campaign_showed.indexOf(this.slides[index].options.name) === -1) {


                                        campaign_current = this.slides[index].options.name;


                                        $.ajax({
                                            url: "/user/campaign/?session=" + session_id,
                                            method: "get",
                                            data: {
                                                position: "impress",
                                                source: this.slides[index].options.source,
                                                name: btoa(encodeURIComponent(this.slides[index].options.name))
                                            },
                                            success: function () {

                                                campaign_showed.push(campaign_current);

                                            }
                                        });


                                    }


                                }

                            });


                            $(".mSSlide, .mSCover").css("height", "500px");


                        } else {


                            window.location.href = "/user/pages/?session=" + session_id;


                        }


                    }


                }


            }


            if (response['data']['second'] > 0 && $("button.campaign-btn").length > 0){


                var campaign_time = response['data']['second'];

                $('button.campaign-btn').html("Please wait " + campaign_time + " seconds..").attr("disabled", true);


                // set timer

                setInterval(function () {

                    campaign_time -= 1;

                    if (campaign_time > 0) {

                        $('.campaign-btn').html("Please wait " + campaign_time + " " + (campaign_time === 1  ? "second" : "seconds") + "..");

                    } else {

                        $('button.campaign-btn').html("Continue..").attr("disabled", false).off().on("click", function () {

                            $.ajax({
                                url: "/user/next/?session=" + session_id,
                                method: "post",
                                data: {},
                                success: function (response) {

                                    window.location.href = "/user/pages/?session=" + session_id;

                                },
                                error: function (response) {

                                    swal("Error", "Something went wrong. Please try again.", "error");

                                }
                            });

                        });

                    }


                }, 1000);


            }


            if (response['captcha'] !== null && response['captcha'] !== undefined){


                if (response['captcha'].length > 0) {

                    $("input.campaign-captcha").on("keyup", function () {

                        var captcha_text = $(this).val();

                        if (captcha_text.length > 3) {


                            $.ajax({
                                url: "/user/campaign/?session=" + session_id,
                                method: "post",
                                data: {
                                    captcha_text: captcha_text,
                                    position: "captcha",
                                    source: "internal",
                                    campaign_name: btoa(response['captcha'])
                                },
                                success: function (response) {

                                    if (response['status'] === "success") {

                                        $('button.campaign-btn').html("Continue..").attr("disabled", false).off().on("click", function () {

                                            $('button.campaign-btn').html("Continue..").attr("disabled", false).off().on("click", function () {

                                                $.ajax({
                                                    url: "/user/next/?session=" + session_id,
                                                    method: "post",
                                                    data: {},
                                                    success: function (response) {

                                                        window.location.href = "/user/pages/?session=" + session_id;

                                                    },
                                                    error: function (response) {

                                                        swal("Error", "Something went wrong. Please try again.", "error");

                                                    }
                                                });

                                            });

                                        });

                                    }


                                },
                                error: function () {

                                }

                            });


                        }


                    });

                }


            }


        }

    },
    error: function () {

        $.ajax({
            url: "/user/message/?session=" + session_id,
            method: "post",
            data: {
                message: "[999] There is unexpected error. Please try again.",
                source: "next"
            },
            success: function (response) {

                window.location.href = "/user/pages/?session=" + session_id;

            },
            error: function (response) {

                window.alert("There is an error. Please try again.")

            }
        });

    }
});


$(document).ready(function () {

    $('.next-page-btn').on("click", function () {

        $.ajax({
            url: "/user/next/?session=" + session_id,
            method: "post",
            data: {},
            success: function (response) {

                window.location.href = "/user/pages/?session=" + session_id;

            },
            error: function (response) {

                swal("Error", "Something went wrong. Please try again.", "error");

            }
        });

    });

});

