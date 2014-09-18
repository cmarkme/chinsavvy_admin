$(document).ready(function() {
    $('#language').bind('change', function() {
        $('input[name="lang"]').val($(this).val());
    });

    function update_categories() {
        var revisionselect = $(this);
        var revisionno = $(this).val();
        var projectid = $(this).parent().parent().parent().attr('id').match(/row_([0-9]*)/)[1];
        var match = $(this).attr('name').match(/revision_(product|qc|results)/);
        var type = 'product';
        if (match) {
            type = match[1];
        }

        $(this).siblings('input[name="revision_no"]').val(revisionno);

        $.getJSON('qc/document/update_spec_categories/'+projectid+'/'+revisionno+'/'+type, function(data) {
            var categoryselect = $(revisionselect).siblings('select:first');
            $(categoryselect).attr('multiple', 'multiple');

            $(categoryselect).empty();

            $.each(data, function(categoryid, categoryname) {
                newoption = document.createElement('option');
                $(newoption).attr({'value': categoryid, 'selected': 'selected'});
                $(newoption).html(categoryname);
                $(categoryselect).append(newoption).focus();
            });
        });

        update_procedures(projectid);
    }

    function update_procedures(projectid) {
        $.getJSON('qc/document/update_spec_procedures/'+projectid, function(data) {
            if (data.length == 0) {
                return false;
            }
            var procedureselect = $('#procedures_'+projectid);
            $(procedureselect).attr('multiple', 'multiple');

            $(procedureselect).empty();

            $.each(data, function(index, procedure) {
                newoption = document.createElement('option');
                $(newoption).attr({'value': procedure, 'selected': 'selected'}.id);
                $(newoption).html(procedure.number + ': ' + procedure.title);
                $(procedureselect).append(newoption).focus();
            });
        });
    }

    function add_events_to_selects() {
        $('[name="revision_product"]').each(function() {
            $(this).bind('change', update_categories);
        });

        $('[name="revision_qc"]').each(function() {
            $(this).bind('change', update_categories);
        });

        $('[name="revision_results"]').each(function() {
            $(this).bind('change', update_categories);
        });

        // Also resize table headers
        $('#ajaxtable').css('width', '100%');
        $('th.id').css('width', '40px');
        $('th.productcode').css('width', '90px');
        $('th.productname').css('width', '50%');
        $('th.productreport').css('width', '90px');
        $('th.qcreport').css('width', '90px');
        $('th.qcresults').css('width', '90px');
    };

    var ajaxtable = setup_ajax_table("/qc/document/browse",
                     [true, true, true, true, true, false, false, false, false],
                     {
                         code: 'text',
                         name: 'text'
                     },
                     false,
                     add_events_to_selects,
                     [[0, 'desc']]
                    );
});

