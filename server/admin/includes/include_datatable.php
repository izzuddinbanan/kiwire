<link rel="stylesheet" href="/app-assets/vendors/css/tables/datatable/datatables.min.css">
<link rel="stylesheet" href="/app-assets/vendors/css/tables/datatable/select.dataTables.min.css">

<style>
    .dataTables_filter>label>input{
        margin-left: auto !important;
    }

    .dt-buttons>button{
        background-color: #fff !important;
        color: #000 !important;
        padding: 14px 17px 14px 17px!important;
        border: 1px solid #ababab !important;
        font-size: 13px;
        margin-top: 9px;
    }
    .dt-buttons>button:hover{
        color: #ababab !important;
    }

    
    @media screen and (max-width: 767px){
        div.dataTables_wrapper div.dataTables_length, div.dataTables_wrapper div.dataTables_filter, div.dataTables_wrapper div.dataTables_info, div.dataTables_wrapper div.dataTables_paginate {
            text-align: right;
        }
    }
</style>

<script src="/app-assets/vendors/js/tables/datatable/pdfmake.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/vfs_fonts.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/datatables.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/datatables.buttons.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/buttons.html5.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/buttons.print.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/dataTables.select.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/buttons.bootstrap.min.js"></script>
<script src="/app-assets/vendors/js/tables/datatable/datatables.bootstrap4.min.js"></script>

<script>
    var dt_position = "<'row'<'col-xs-1 col-sm-1 col-md-1 pull-right'f><'col-xs-11 col-sm-11 col-md-11'<'pull-right'B>>><'row'<'col-sm-12'tr>><'row'l><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    var dt_page = 10;
    var dt_btn =    [
                        {
                            // text: '<i class="fa fa-files-o"></i>',
                            titleAttr: 'Copy',
                            extend: 'copyHtml5',
                            exportOptions: {
                                columns: [ 0, ':visible' ]
                            }
                        },
                        {
                            // text: '<i class="fa fa-file-excel-o"></i>',
                            titleAttr: 'CSV',
                            extend: 'csvHtml5',
                            
                        },
                        {
                            // text: '<i class="fa fa-file-pdf-o"></i>',
                            titleAttr: 'PDF',
                            extend: 'pdfHtml5',
                            exportOptions: {
                                columns: ':visible'
                            }
                        },
                        {
                            // text: '<i class="fa fa-print"></i>',
                            titleAttr: 'Print',
                            extend: 'print',
                            exportOptions: {
                                columns: ':visible'
                            }
                        }
                    ]

</script>

