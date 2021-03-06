function add() {
    window.location = '/codes/project/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.codes_projects_completed').css('width', '100px');
        $('th.codes_projects_description').css('width', '500px');
        $('th.actions').css('width', '100px');
    };

    var ajaxtable = setup_ajax_table("/codes/project/browse",
                     [true, false, true, true, true, true, true, true, false],
                     {
                         combo: 'combo',
                         completedstatus: 'checkbox'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );
});

