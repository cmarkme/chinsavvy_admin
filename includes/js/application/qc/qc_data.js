function select_supplier() {
    var value = $(this).val();
    window.location = '/qc/process/qc_data/'+category_id+'/'+project_id+'/'+value;
}

/**
 * By adding a supplier, we effectively create a new qc_job record, and
 * redirect the user to this new job
 */
function add_supplier() {
    var value = $(this).val();
    $.post('qc/process/add_supplier', { project_id : project_id, category_id : category_id, supplier_id : value }, function(data) {
        window.location = '/qc/process/qc_data/'+category_id+'/'+project_id;
    },
    'json');
}

/**
 * Sends an AJAX request to update a field in the qc_jobs table
 */
function update_job_data() {
    var matches = $(this).attr('id').match(/job_([a-zA-Z_]*)/);
    var field = matches[1];
    var value = $(this).val();
    $.post('qc/process/update_job_data', { field : field, value : value, job_id: job_id }, function(data) {
        print_edit_message('report', data);
    });
}

function get_result_string(job_result) {
    switch (job_result) {
        case constants.QC_RESULT_PASS: return 'Pass';
        case constants.QC_RESULT_HOLD: return 'Pending';
        case constants.QC_RESULT_REJECT: return 'Rejected';
        case constants.QC_RESULT_CONCESSION_CUSTOMER: return 'Concessioned by Customer';
        case constants.QC_RESULT_CONCESSION_CHINASAVVY: return 'Concessioned by ChinaSavvy';
    }
}

function update_spec_data() {
    var matches = $(this).attr('id').match(/(checked|defects)_([0-9]*)/);
    var field = matches[1];
    var spec_id = matches[2];
    var value;

    if ($(this).attr('type') == 'checkbox') {
        value = $(this).attr('checked');
    } else {
        value = $(this).val();
    }

    $.post('qc/process/update_spec_data', { field : field, value : value, spec_id: spec_id, job_id: job_id }, function(data) {
        print_edit_message('checks', data);
        data = $.evalJSON(data);
        // For the defect field, also update the defectpercentage cell
        $('#result_'+spec_id).attr('class', 'result-' + data.check_result);

        $dropdown = $('#job_result');
        if ( ! $dropdown.length || data.job_result == constants.QC_RESULT_PASS || data.job_result == constants.QC_RESULT_HOLD) {
            $('#readonly-result').text(get_result_string(data.job_result));
            $dropdown.hide();
        } else {
            $('#readonly-result').text('');
            $dropdown.val(data.job_result).show();
        }
    });
}

$(document).ready(function() {
    // Setup date pickers
    var reportdate, inspectiondate;
    $.datepicker.setDefaults({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        defaultDate: null
    });

    if ($('#job_report_date').val() > 0) {
        reportdate = new Date();
        reportdate.setTime($('#job_report_date').val()*1000);
    }
    if ($('#job_inspection_date').val() > 0) {
        inspectiondate = new Date();
        inspectiondate.setTime($('#job_inspection_date').val()*1000);
    }
    $('#job_report_date').datepicker({defaultDate: reportdate, dateFormat: 'dd/mm/yy'});
    $('#job_inspection_date').datepicker({defaultDate: inspectiondate});

    if ($('#job_report_date').val() > 0) {
        $('#job_report_date').val(reportdate.format('dd/mm/yyyy'));
    }

    if ($('#job_inspection_date').val() > 0) {
        $('#job_inspection_date').val(inspectiondate.format('dd/mm/yyyy'));
    }

    // Attach event handlers to elements
    $('#job_report_date').bind('change', update_job_data);
    $('#job_inspection_date').bind('change', update_job_data);
    $('#job_user_id').bind('change', update_job_data);
    $('#job_result').bind('change', update_job_data);

    $('input[name=checked]').bind('change', update_spec_data);
    $('input[name=defects]').bind('blur', update_spec_data);

    var revisionelements = get_revision_elements(project_id);
    $('#revisionbuttontd').append(revisionelements.revisionbutton);

    if ($('#job_result').val() == constants.QC_RESULT_REJECT || undefined == $('#job_result').val()) {
        $.post('qc/process/get_suppliers', { project_id : project_id, category_id : category_id, job_id : job_id } , function(data) {
            if (data.assigned) {
                var currentsupplierdropdown = document.createElement('select');
                $(currentsupplierdropdown).attr('name', 'currentsupplier');

                $.each(data.assigned, function (supplier_id, supplier) {
                    supplieroption = document.createElement('option');
                    $(supplieroption).attr('value', supplier_id);
                    $(supplieroption).html(supplier.name);
                    if (supplier.selected == 1) {
                        $(supplieroption).attr('selected', 'selected');
                    }
                    $(currentsupplierdropdown).append(supplieroption);
                });

                $(currentsupplierdropdown).bind('change', select_supplier);
                $('#supplierselector').append(currentsupplierdropdown);
            }

            var supplierdropdown = document.createElement('select');
            $(supplierdropdown).attr('name', 'newsupplier');
            var nooption = document.createElement('option');
            $(nooption).attr('value', 0);
            $(nooption).html('Select a supplier...');
            $(supplierdropdown).append(nooption);

            $.each(data.available, function (supplier_id, supplier) {
                supplieroption = document.createElement('option');
                $(supplieroption).attr('value', supplier_id);
                $(supplieroption).html(supplier.name);
                $(supplierdropdown).append(supplieroption);
            });

            $(supplierdropdown).bind('change', add_supplier);
            $('#newsupplierselector').append(supplierdropdown);
        }, 'json');
    } else {
        $.post('qc/process/get_supplier', { job_id : job_id }, function(data) {
            $('#supplierselector').text(data.name);
        }, 'json');
    }

});
