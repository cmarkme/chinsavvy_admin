function add() {
    window.location = '/enquiries/outbound/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.enquiries_outbound_quotations_id').css('width', '40px');
        $('th.enquiries_outbound_quotations_enquiry_id').css('width', '40px');
        $('th.company').css('width', '40px');
        $('th.product').css('width', '30%');
        $('th.enquiries_outbound_quotations_creation_date').css('width', '40px');
        $('th.staff').css('width', '120px');
        $('th.actions').css('width', '40px');
    };

    var ajaxtable = setup_ajax_table("/enquiries/outbound/browse",
                     [true, true, true, true, true, false, false],
                     {
                         combo: 'combo',
                         staff_id: 'select'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );

});

