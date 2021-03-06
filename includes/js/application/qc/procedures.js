function add() {
    window.location = '/qc/procedure/add';
}

$(document).ready(function() {
    var setup_table = function() {
        $('#ajaxtable').css('width', '100%');
        $('th.procedure_id').css('width', '40px');
        $('th.procedure_number').css('width', '90px');
        $('th.procedure_title').css('width', '30%');
        $('th.procedure_version').css('width', '40px');
        $('th.procedure_updated_by').css('width', '90px');
        $('th.procedure_creation_date').css('width', '40px');
        $('th.actions').css('width', '60px');
    };

    var ajaxtable = setup_ajax_table("/qc/procedure/browse",
                     [true, true, true, true, true, true, false],
                     {
                         combo: 'combo',
                         procedure_updated_by: 'select'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );
});

