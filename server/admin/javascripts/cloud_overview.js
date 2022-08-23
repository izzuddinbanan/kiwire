
$(document).ready(function (){


    $.ajax({
        url: "/admin/ajax/ajax_cloud_overview.php",
        method: "get",
        data: {
            action: "tenant_list"
        },
        success: function (response){

            if (response['status'] === "success"){


                let kstring = "";
                let kcounter = 1;


                for(let kindex in response['data']){


                    kstring += "<tr>";
                    kstring += "<td>" + kcounter +"</td>";
                    kstring += "<td>" + response['data'][kindex]['tenant_id'] + "</td>";
                    kstring += "<td>" + response['data'][kindex]['tenant_name'] + "</td>";
                    kstring += "<td>" + response['data'][kindex]['tenant_account'] + "</td>";
                    kstring += "<td>" + response['data'][kindex]['tenant_active'] + "</td>";
                    kstring += "</tr>";

                    kcounter++;


                }


                $(".tenant-list > tbody").html(kstring);

                $(".tenant-list").dataTable({
                    dom: dt_position,
                    pageLength: dt_page,
                    buttons: dt_btn,
                    language: {
                        searchPlaceholder: "Search Records",
                        search: "",
                    },
                    "fnDrawCallback": function () {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

                    }
                });


            }

        }
    });


    $.ajax({
        url: "/admin/ajax/ajax_cloud_overview.php",
        method: "get",
        data: {
            action: "system_info"
        },
        success: function (response){

            if (response['status'] === "success"){


                $("#total-account").html(String(response['data']['accounts']).replace(/(.)(?=(\d{3})+$)/g,'$1,'));
                $("#total-voucher").html(String(response['data']['vouchers']).replace(/(.)(?=(\d{3})+$)/g,'$1,'));
                $("#total-email").html(String(response['data']['emails']).replace(/(.)(?=(\d{3})+$)/g,'$1,'));
                $("#total-sms").html(String(response['data']['smss']).replace(/(.)(?=(\d{3})+$)/g,'$1,'));
                $("#active-session").html(String(response['data']['active']).replace(/(.)(?=(\d{3})+$)/g,'$1,'));
                $("#active-controller").html(String(response['data']['controller_up']).replace(/(.)(?=(\d{3})+$)/g,'$1,'));
                $("#offline-controller").html(String(response['data']['controller_down']).replace(/(.)(?=(\d{3})+$)/g,'$1,'));

                if (response['data']['controller_down'] > 0){

                    $("#offline-controller-space").removeClass("bg-gradient-primary").addClass("bg-gradient-danger");

                }


            }

        }
    });


});