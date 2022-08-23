String.prototype.capitalize = function() {

    return this.charAt(0).toUpperCase() + this.slice(1);

};


$(document).ready(function () {

    pull_data();

    

});


function pull_data() {

    $.ajax({
        url: "ajax/ajax_cloud_impersonate_user.php",
        method: "GET",
        data: {
            "action": "get_all"
        },
        success: function (data) {

            if (data['status'] === "success") {

                if($.fn.dataTable.isDataTable('.table-data')){

                    $(".table-data").DataTable().destroy();

                }

                let table_str = "";

                for (let x = 0; x < data['data'].length; x++) {

                    let lastlogin
                    if(data['data'][x]['lastlogin'] == null) lastlogin = 'Never';
                    else lastlogin =  data['data'][x]['lastlogin']

                    table_str += "<tr>";

                    table_str += "<td>" + (x + 1) + "</td>";
                    table_str += "<td>" + data['data'][x]['tenant_id'] + "</td>";
                    table_str += "<td>" + data['data'][x]['username'] + "</td>";
                    table_str += "<td>" + data['data'][x]['fullname'] + "</td>";
                    table_str += "<td>" + data['data'][x]['email'] + "</td>";
                    table_str += "<td>" + lastlogin + "</td>";

                    table_str += "<td><button class='btn btn-icon btn-success btn-sm btn-login-impersonate' data-tenant='"+ data['data'][x]['tenant_id'] +"' data-username='"+ data['data'][x]['username'] +"' data-user='"+ data['data'][x]['password'] +"'>Change to this user</button></td>";

                    table_str += "</tr>";

                }

                $(".table-data>tbody").html(table_str);

                $(".table-data").dataTable({
                    dom: dt_position,
                    pageLength: dt_page,
                    buttons: dt_btn,
                    language: {
                        searchPlaceholder: "Search Records",
                        search: "",
                    },
                    "fnDrawCallback": function() {
                        if($('.dataTables_filter').find('input').hasClass('form-control-sm')){
                            $('.dataTables_filter').find('input').removeClass('form-control-sm')
                        }

                    }
                });

            } else {

                swal("Error", data['message'], "error");

            }

        },
        error: function () {

            swal("Error", "There is unexpected error. Please try again.", "error");

        }


    })

}


