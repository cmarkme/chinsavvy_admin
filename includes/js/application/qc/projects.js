function add() {
    window.location = '/qc/project/add';
}

$(document).ready(function() {
    var setup_table = function() {
        $('#ajaxtable').css('width', '100%');
        $('th.project_id').css('width', '40px');
        $('th.productcode').css('width', '90px');
        $('th.productname').css('width', '30%');
        $('th.revision').css('width', '40px');
        $('th.projectstatus').css('width', '50px');
        $('th.approved_project_admin').css('width', '40px');
        $('th.approved_product_admin').css('width', '40px');
        $('th.approved_qc_admin').css('width', '40px');
        $('th.project_revision_date').css('width', '40px');
        $('th.project_creation_date').css('width', '40px');
        $('th.actions').css('width', '60px');
    };

    var ajaxtable = setup_ajax_table("/qc/project/browse",
                     [true, true, true, false, false, true, true, true, true, true, false],
                     {
                         code: 'text',
                         name: 'text',
                         projectstatus: 'select',
                         approvedprojectadmin: 'checkbox',
                         approvedproductadmin: 'checkbox',
                         approvedqcadmin: 'checkbox'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );
});

