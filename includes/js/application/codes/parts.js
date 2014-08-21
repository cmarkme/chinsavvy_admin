function add() {
    window.location = '/codes/part/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('td.product_number').css('width', '80px');
        $('td.part_number').css('width', '40px');
        $('td.company_code').css('width', '40px');
        $('td.codes_parts_name').css('width', '430px');
        $('td.codes_parts_description').css('width', '900px');
        $('td.actions').css('width', '120px');
    };

    var ajaxtable = setup_ajax_table("/codes/part/browse",
                     [true, true, true, true, true, true, true, true, true, true, true, true, true, true, true, false],
                     {
                         combo: 'combo',
                         completedstatus: 'checkbox'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );
});

