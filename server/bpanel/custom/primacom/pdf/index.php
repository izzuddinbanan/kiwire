<html >
    <body>
        <div id="content">

            <h3>Please Login using this voucher</h3>
            <h4>Voucher Code : <?= $_REQUEST['code']  ?></h4>
        </div>
        
    </body>

    <div id='editor'></div>
    <script type='application/javascript' src='../jquery.js'></script>
    <script type='application/javascript' src='../jspdf.js'></script>
    <script>
        var doc = new jsPDF();
        var specialElementHandlers = {
            '#editor': function (element, renderer) {
                return true;
            }
        };

        pdf();

        function pdf() {
            var order_id = <?php echo json_encode($_REQUEST['order_id']) ?>;

            doc.fromHTML($('#content').html(), 15, 15, {
                'width': 170,
                    'elementHandlers': specialElementHandlers
            });
            doc.save('voucher-code-'+ order_id +'.pdf');

        }
        
    </script>


</html>


<?php 


