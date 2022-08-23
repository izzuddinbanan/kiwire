$(document).ready(function(){
    
    
    $('.change-icon').click(function () {


        $('#theme-icon').toggleClass('fa-sun-o fa-moon-o');

        console.log($("#theme-icon").attr("class"))


        let theme;

        if ($("#theme-icon").attr("class") == "ficon fa fa-sun-o") {

            theme = "default";
        
        } else {
        
            theme = "dark";
        
        }


        $.ajax({
            "url": "/admin/ajax/ajax_change_theme.php",
            "method": "post",
            "data": {
                "theme":  theme
            },
            "success": function (response) {

                if (response['status'] === "success") {

                    window.location.reload();

                } else {

                    swal("Error", response['message']);

                }

            },
            "error": function (response) {

                swal("Error", "There is an error. Please try again.");

            }

        });

    });  


    $(".select2").select2();


    $(".change-theme").on("click", function() {

        $('#theme_modal').modal();

    });


    $('body').on('click','.btn-login-impersonate', function(){

        let tenant      = $(this).data('tenant')
        let username    = $(this).data('username')
        let pass        = $(this).data('user')

        //send ajax to login other user
        swal({

            title: "Are you sure want to login as this user?",
            // text: "Tenant: " + tenant + " \nUsername: " + username,
            type: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes!",
            cancelButtonText: "Cancel"
    
        }).then(function(act){
    
            if (act['value'] === true) {
                
                $.ajax({
                    url: "ajax/ajax_cloud_impersonate_user.php",
                    method: "POST",
                    data: {
                        "action"    : "login",
                        tenant_id   : tenant,
                        username    : username,
                        pass        : pass,
                    },
    
                    success: function (response) {
    
                        if (response['status'] === "success"){


                            if (response['data']['next'] === "2factor"){
        
        
                                $("form.login").trigger("reset");
        
                                $("#2factor").modal();
        
        
                            } else {
        
        
                                if (response['data']['page'].length > 0) {
        
                                    window.location.href = response['data']['page'];
        
                                } else {
        
                                    window.location.href = "/admin/dashboard.php";
        
                                }
        
        
                            }
        
        
        
                        } else {
        
                            swal("Error", response['message'], "error");
        
                        }
    
                    },
    
                });
    
            }
    
        });


    })



    $(".change-theme-btn").on("change", function(){

        let theme_selected = $(this).val();

        $.ajax({
            "url": "/admin/ajax/ajax_change_theme.php",
            "method": "post",
            "data": {
                "theme":  theme_selected
            },
            "success": function (response) {

                if (response['status'] === "success") {

                    window.location.reload();

                } else {

                    swal("Error", response['message']);

                }

            },
            "error": function (response) {

                swal("Error", "There is an error. Please try again.");

            }

        });

    });


    $(".change-tenant").on("click", function () {


        let tenant = $(this).data("tenant");

        if (tenant.length > 2) {

            $.ajax({
                url: "/admin/ajax/ajax_change_tenant.php",
                method: "post",
                data: {
                    tenant_id: tenant
                },
                success: function (response) {

                    if (response['status'] === "success") {


                        window.location.reload();


                    } else {

                        swal("Error", response['message'], "error");

                    }

                },
                error: function (response) {

                    swal("Error", "There is an error. Please try again.");

                }
            });


        }


    });



    $(".filter-tenant").on("keyup", function () {


        let searchText = $(this).val();


        $(".change-tenant").each(function () {


            let currentElement = $(this);
            
            // if (currentElement.data("tenant").indexOf(searchText) > -1) currentElement.css("display", "block");
            if (new RegExp(`${searchText}`,'i').test(currentElement.data("tenant"))) currentElement.css("display", "block");
            else currentElement.attr("style", "display: none !important;");


        });


    });


    $.ajax({
        url: "/admin/ajax/ajax_nav_menu.php",
        method: "post",
        data: {},
        success: function (response) {

            if (response['status'] === "success"){

                $("li").each(function () {

                    let kmodule = $(this);

                    if (kmodule.data("module") !== undefined){

                        if (response['data'].includes(kmodule.data("module")) === false){

                            kmodule.remove();

                        }

                    }

                });

            }


            $(".has-sub").each(function(){

                let current_module = $(this);

                if(current_module.find("li").length === 0){

                    current_module.remove();

                }


            });


        }
    });


    // collapse or open menu

    $(".modern-nav-toggle").on("click", function () {


        $.ajax({
            "url": "/admin/ajax/ajax_toggle_menu.php",
            "method": "post",
            "data": {},
            "success": function (response) {},
            "error": function (response) {}
        });


    });


    setTimeout(function (){


        $.ajax({
            url: "/admin/ajax/ajax_read_notification.php",
            method: "get",
            data: {
                action: "list"
            },
            success: function (response){

                if (response['status'] === "success"){


                    let total_notification =0;
                    let notification_list = ""


                    for (kindex in response['data']){


                        notification_list += '<a class="d-flex justify-content-between notification-button" data-id="' + response['data'][kindex]['id'] + '" href="#">';
                        notification_list += '<div class="media d-flex align-items-start">';
                        notification_list += '<div class="media-left">';
                        notification_list += '<i class="feather icon-plus-square font-medium-5 primary"></i>';
                        notification_list += '</div>';
                        notification_list += '<div class="media-body">';
                        notification_list += '<h6 class="primary media-heading">' + response['data'][kindex]['sender'] + '</h6><small class="notification-text">' + response['data'][kindex]['title'] + '</small>';

                        notification_list += '</div>';
                        notification_list += '<small>';
                        notification_list += '<time class="media-meta" datetime="' + response['data'][kindex]['content'] + '"></time>';
                        notification_list += '</small>';
                        notification_list += '</div>';
                        notification_list += '</a>';

                        total_notification++;


                    }


                    if (notification_list.length > 0){


                        $(".notification-bell").append('<span class="badge badge-pill badge-primary badge-up notification-count">' + total_notification + '</span>');
                        $(".notification-list").html(notification_list);


                        $(".notification-button").off().on("click", function (){


                            let message_id = $(this).data("id");

                            $.ajax({
                                "url": "/admin/ajax/ajax_read_notification.php",
                                "method": "post",
                                "data": {
                                    message_id: message_id,
                                    action: "read"
                                },
                                "success": function (response) {

                                    if (response['status'] === "success"){


                                        let notification_count = $(".notification-count");

                                        notification_count.html(parseInt(notification_count.html()) - 1);


                                        $("a[data-id=" + response['data']['id'] + "]").remove();

                                        $(".notification-content").html(response['data']['message']);

                                        $("#message_modal").modal();


                                    }

                                },
                                "error": function (response) {}
                            });


                        });


                    }


                }

            }
        });


    }, 1000);


    $(".notification-all-read").on("click", function (){


        $.ajax({
            "url": "/admin/ajax/ajax_read_notification.php",
            "method": "post",
            "data": {
                action: "mark-all-read"
            },
            "success": function (response) {

                if (response['status'] === "success"){


                    $(".notification-count").remove();

                    $(".notification-list").html('<a class="d-flex justify-content-between" href="javascript:void(0)"><div class="media d-flex align-items-start text-center"><div data-i18n="msg_notification">There is no notification to display</div></div></a>');


                }

            },
            "error": function (response) {}
        });


    });


});