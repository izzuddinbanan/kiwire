$(document).ready(function () {
    // pull data from database
    pull_data();

    //onClick() function for btn-create
    $(".btn-create").on("click", function () {

        // serialize create-form data in url encoded text
        let data = $(".create-form").serialize();

        // store value of input for reference
        let create_or_update = $("input[name=reference]").val();

        // if the name field is not empty,
        if (create_or_update !== ""){
            // that means user is updating, so add action=update in data
            data += "&action=update";

        } else {
            // name field is initially empty, so user is adding/creating new entry. so add action=create in data
            data += "&action=create";
        }


        $.ajax({
            url: "/admin/ajax/ajax_devices_project_mapping.php",
            method: "get",
            data: data,
            success: function (response) {

                if (response['status'] === "success"){

                    // if ajax is successful, show success message
                    swal("Success", response['message'], "success");

                    // hide the input form
                    $("#inlineForm").modal("hide");

                    // reload data from server (to show the updated list as soon as success message is closed)
                    pull_data();


                }
                // but if response['status'] is not === success,
                else {
                    // show error message
                    swal("Error", response['message'], "error");

                }

            },
            error: function (response) {
                // If error, show error message.
                swal("Error", "There is an error. Please try again.", "error");

            }
        });


    });

    // onHide() function for the input form modal
    $("#inlineForm").on("hide.bs.modal", function () {

        // reset the form (empty the fields)
        $("form.create-form").trigger("reset");

        // make sure name input is empty
        $("input[name=reference]").val("");

        // untick all ticked boxes
        $("input[type=checkbox]").attr("checked", false);

        // change the text on the btn-create to "Create"
        $(".btn-create").html("Create");


    });


});


// function used to get all data
function pull_data(){

    $.ajax({
        url: "/admin/ajax/ajax_devices_project_mapping.php",
        method: "get",
        data: {
            action: "get_all"   // action variable tells ajax file which function to run
        },
        success: function (response) {

            if (response['status'] === "success")
            {

                // check if it is a data table
                if ($.fn.dataTable.isDataTable('.table-data')) {
                    // if yes, destroy existing data table
                    $(".table-data").DataTable().destroy();

                }


                let table_str = "";

                // if length is more than 0
                if (response['data'].length > 0){

                    let counter = 1;
                    // output the values in the array as tables
                    for(let kindex in response['data']){

                        // add a new row
                        table_str += "<tr>";
                        // for column "No", fill with counter values
                        table_str += "<td>" + counter + "</td>";
                        // for column "Project Name", fill with corresponding project name
                        table_str += "<td>" + response['data'][kindex]['name'] + "</td>";

                        // for column "Action"
                        table_str += "<td>";
                        // Add the pencil edit button, with the approppriate project name
                        table_str += "<button type='button' data-project='" + response['data'][kindex]['name'] + "' class='btn btn-success btn-icon fa fa-pencil btn-project-edit'></button>";
                        // Add the 'x' delete button, with the approppriate project name
                        table_str += "<button type='button' data-project='" + response['data'][kindex]['name'] + "' class='btn btn-danger btn-icon fa fa-times ml-75 btn-project-remove'></button>";
                        table_str += "</td>";

                        // close 1 row
                        table_str += "</tr>";

                        counter++;


                    }


                } else {
                    // if no data in the database, do nothing
                    table_str = "";

                }

                // formatting the table-data body with table_str
                $(".table-data > tbody").html(table_str);


                $(".table-data").dataTable({
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

                        // if click on edit button
                        $(".btn-project-edit").off().on("click", function () {
                            // run project_edit function
                            project_edit($(this).data("project"));

                        });
                        // if click on remove button
                        $(".btn-project-remove").off().on("click", function () {
                            // run project_remove function
                            project_remove($(this).data("project"));

                        });

                    }
                });


            }
            else
            {
                // error executing ajax, show error message
                swal("Error", response['message'], "error");

            }

        }
    });


}

// function that is called to edit a project
function project_edit(name){


    $.ajax({
        url: "/admin/ajax/ajax_devices_project_mapping.php",
        method: "get",
        data: {
            action: "get_data", // tells ajax to run get_update, which returns the info of the specified project ONLY
            name: name
        },
        success: function (response) {


            if (response['status'] === "success"){

                // show whatever the stored project name is in the name input field
                $("input[name=name]").val(response['data']['name']);

                // update the reference value with retrieved id from database
                $("input[name=reference]").val(response['data']['id']);

                // split zone list data by ","
                response['data']['zone_list'] = response['data']['zone_list'].split(",");

                for (let kindex in response['data']['zone_list']){
                    // make sure the correct boxes are checked based on info in database
                    $("input[value='" + response['data']['zone_list'][kindex] + "']").attr("checked", true);

                }


                // Change btn-create's text to "Update" from "Create"
                $(".btn-create").html("Update");


                $("#inlineForm").modal();


            } else {

                swal("Error", response['message'], "error");

            }


        },
        error: function (response) {

            swal("Error", "There is an error. Please try again.", "error");

        }
    });


}



// function that is called to delete a project
function project_remove(name) {

  swal({

      title: "Are you sure?",
      text: "You will not able to reverse this action.",
      type: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, delete it!",
      cancelButtonText: "Cancel"

  }).then(function (x) {

      if (x['value'] === true) {

          $.ajax({
              url: "ajax/ajax_devices_project_mapping.php",
              method: "POST",
              data: {
                  "action": "remove",
                  name: name,
                  "token": $("input[name=token]").val()
              },

              success: function (data) {

                  if (data['status'] === "success") {

                      pull_data();

                      toastr.info("Success", data['message'], "success");

                  } else {

                      toastr.info("Error", data['message'], "error");

                  }

              },

          });

      }

  });


}
