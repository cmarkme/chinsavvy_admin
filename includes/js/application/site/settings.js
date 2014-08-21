function add() {
    window.location = '/setting/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.setting_id').css('width', '40px');
        $('th.setting_name').css('width', '160px');
        $('th.setting_value').css('width', '40px');
        $('th.actions').css('width', '40px');
    };

    var ajaxtable = setup_ajax_table("/setting/index",
                     [true,  true, false, false],
                     {
                         combo: 'combo'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );

});

