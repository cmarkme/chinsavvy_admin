function add() {
    window.location = '/enquiries/enquiry/add';
}

$(document).ready(function() {
    var setup_table = function(object) {
        $('#ajaxtable').css('width', '100%');
        $('th.enquiries_id').css('width', '40px');
        $('th.enquiries_status').css('width', '120px');
        $('th.enquiries_priority').css('width', '50px');
        $('th.country').css('width', '70px');
        $('th.enquiries_creation_date').css('width', '40px');
        $('th.enquiries_due_date').css('width', '40px');
        $('th.company').css('width', '40px');
        $('th.product').css('width', '30%');
        $('th.stafflist').css('width', '120px');
        $('th.actions').css('width', '50px');
        // Notes column invisible

        // Grab contents of Notes column and output as overlib for the row
        oTable = object.oInstance;
        trs = oTable.fnGetNodes();

        $(trs).each(function(event) {
            data = oTable.fnGetData(this);
            $(this).qtip( {
                content: data[9],
                position: {
                    target: 'mouse',
                    adjust: {
                        screen: true
                    }
                },
                style: {
                    name: 'cream'
                },
                show: {
                    when: {
                        target: $(this).children(':not(td.actions)')
                    }
                }
            });
        });
    };

    var ajaxtable = setup_ajax_table(window.location.pathname,
                     [true, true, true, true, true, true, true, true, false, false, false],
                     {
                         archivedstatus: 'checkbox',
                         customerorderedstatus: 'checkbox',
                         declinedstatus: 'checkbox',
                         enquirystatus: 'text',
                         enquiries_priority: 'select',
                         staff_id: 'select',
                         combo: 'combo'
                     },
                     true,
                     setup_table,
                     [[0, 'desc']]
                    );

    // Hide notes, they will be output through overlib with mouseover each row
    ajaxtable.fnSetColumnVis(9, false);

    // Do not send staff_id
});

