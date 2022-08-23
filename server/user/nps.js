$(document).ready(function () {

    $(".btn-nps").on("click", function () {

        var nps_value = $(this).data("rate");

        $("input[name=nps_rate]").val(nps_value);

    });


});