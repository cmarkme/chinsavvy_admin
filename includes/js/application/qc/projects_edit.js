/**
 * Redraws the form to reflect current state of DB
 */

var inspection_levels;

function draw_project() {
    // $('#project').loading({onAjax:true});
    $.getJSON('qc/project/get_json_data/'+projectid, function(data) {
        $('#projectform td.title:first').html('Edit project');
        inspection_levels = data.inspection_levels;
        draw_details(data);
        draw_specs('product', data);
        draw_specs('qc', data);
        draw_inspectors_table(data);
    });
}

/**
 * Draws the Project details section of the page
 * @param object data Data sent by ajax_projects_edit.php
 */
function draw_details(data) {
    // Details
    $('#detailstable').html('');

    // Inspection level
    var inspectionlevel = document.createElement('span');
    $(inspectionlevel).attr('id', 'inspectionlevelspan');

    if (has_capability('qc:editprojects')) {
        add_dropdown(inspectionlevel, 'inspectionlevel', inspection_levels, data.inspectionlevel, update_details);
    } else {
        $(inspectionlevel).text(inspection_levels[data.inspectionlevel]);
    }

    // Customer code
    var customercode = document.createElement('span');
    if (has_capability('qc:editprojects')) {
        $(customercode).addClass('edit');
    }
    $(customercode).attr('id', 'customerproductcode');
    $(customercode).text(data.customerproductcode);

    // Batch size
    var batchsize = document.createElement('span');
    if (has_capability('qc:editprojects')) {
        $(batchsize).addClass('edit');
    }
    $(batchsize).attr('id', 'batchsize');
    $(batchsize).text(data.batchsize);

    // Sample size
    var samplesize = document.createElement('input');
    if ( ! has_capability('qc:editprojects') || data.inspectionlevel != 4) {
        $(samplesize).prop('disabled', true);
    }
    $(samplesize).attr('id', 'samplesize');
    $(samplesize).attr('name', 'samplesize');
    $(samplesize).val(data.samplesize);
    $(samplesize).bind('change', update_details);

    $(inspectionlevel).change(function() {
        var disabled = $(this).find('option:selected').val() != 4;
        $(samplesize).prop('disabled', disabled);
    });

    // Related products
    var relatedproductsspan = document.createElement('span');
    var relatedproductslist = document.createElement('ul');
    $(relatedproductslist).attr('id', 'relatedproducts');

    $.each(data.related, function(relatedid, relatedname) {
        $(relatedproductslist).append(make_related_item(relatedid, relatedname));
    });

    $(relatedproductsspan).append(relatedproductslist);

    if (has_capability('qc:editprojects')) {
        var relatedproductinput = make_autocomplete_input('relatedproduct', 'related_product_id', '', 'Add a related product: ', '/qc/project/related_project_suggest/'+projectid, add_related);
        var relatedlabel = document.createElement('label');
        $(relatedlabel).attr('for', 'relatedproduct');
        $(relatedlabel).html(relatedproductinput.label);
        $(relatedproductsspan).append(relatedlabel);
        $(relatedproductsspan).append(relatedproductinput.input);
    }

    // Shipping marks
    var shippingmarks = document.createElement('span');
    $(shippingmarks).attr('id', 'shippingmarks');

    if (has_capability('qc:editprojects')) {
        $(shippingmarks).editable('qc/project/update_standard_variable/'+projectid, {
            id: 'field',
            type: 'textarea',
            cols: 60,
            rows: 7,
            callback: function() {
                redraw_saverevision_button();
            },
            onblur: 'submit',
            loadurl: 'qc/project/get_shipping_marks/'+projectid,
            loadtype: 'POST'
        });
    }
    if (!data.shippingmarks) {
        data.shippingmarks = constants.QC_DEFAULT_SHIPPING_MARKS_LINE_1 + "\n"
            + constants.QC_DEFAULT_SHIPPING_MARKS_LINE_2 + "\n"
            + constants.QC_DEFAULT_SHIPPING_MARKS_LINE_3 + "\n"
            + constants.QC_DEFAULT_SHIPPING_MARKS_LINE_4 + "\n"
            + constants.QC_DEFAULT_SHIPPING_MARKS_LINE_5 + "\n"
            + constants.QC_DEFAULT_SHIPPING_MARKS_LINE_6 + "\n"
            + constants.QC_DEFAULT_SHIPPING_MARKS_LINE_7;
    }

    data.shippingmarks = nl2br(data.shippingmarks);
    $(shippingmarks).html(data.shippingmarks);

    // Approved dates
    var approvedproductspan = document.createElement('span');
    var approvedqcspan = document.createElement('span');

    var approvedproductinput = document.createElement('input');
    var approvedqcinput = document.createElement('input');

    $(approvedproductinput).attr('id', 'approved_product_customer');
    $(approvedproductinput).attr('name', 'approved_product_customer');
    $(approvedproductinput).attr('type', 'text');
    $(approvedqcinput).attr('id', 'approved_qc_customer');
    $(approvedqcinput).attr('name', 'approved_qc_customer');
    $(approvedqcinput).attr('type', 'text');

    var approvedproductdate, approvedqcdate;

    $.datepicker.setDefaults({
        dateFormat: 'dd/mm/yy',
        changeMonth: true,
        changeYear: true,
        defaultDate: null
    });

    approvedproductdate = new Date();
    if (data.approvedproductcustomer > 0) {
        approvedproductdate.setTime(data.approvedproductcustomer*1000);
    }

    approvedqcdate = new Date();
    if (data.approvedqccustomer > 0) {
        approvedqcdate.setTime(data.approvedqccustomer*1000);
    }

    $(approvedproductinput).datepicker({defaultDate: approvedproductdate});
    $(approvedqcinput).datepicker({defaultDate: approvedqcdate});

    $(approvedproductinput).bind('change', update_details);
    $(approvedqcinput).bind('change', update_details);

    $(approvedproductspan).append(approvedproductinput);
    $(approvedqcspan).append(approvedqcinput);

    if (data.approvedproductcustomer > 0) {
        $(approvedproductinput).val(approvedproductdate.format('dd/mm/yyyy'));
        $(approvedproductspan).append(make_action_icon('delete', 'Record as non-approved', function() {
                var answer = confirm('Record this project\'s product specifications as non-approved?');
                if (answer) {
                    $(approvedproductinput).val('0');
                    $(approvedproductinput).trigger('change');
                }
                return false;

            }, 'inlineicon'));
    }

    if (data.approvedqccustomer > 0) {
        $(approvedqcinput).val(approvedqcdate.format('dd/mm/yyyy'));
        $(approvedqcspan).append(make_action_icon('delete', 'Record as non-approved', function() {
                var answer = confirm('Record this project\'s QA specifications as non-approved?');
                if (answer) {
                    $(approvedqcinput).val('0');
                    $(approvedqcinput).trigger('change');
                }
                return false;

            }, 'inlineicon'));
    }

    // Permitted Defect
    var permitteddefectspan = document.createElement('span');
    $(permitteddefectspan).attr('id', 'permitteddefect');
    if (has_capability('qc:editprojects')) {
        add_percentage_dropdown(permitteddefectspan, 'defectcriticallimit', data.defectcriticallimit, 'Cri', update_details);
        add_percentage_dropdown(permitteddefectspan, 'defectmajorlimit', data.defectmajorlimit, 'Maj', update_details);
        add_percentage_dropdown(permitteddefectspan, 'defectminorlimit', data.defectminorlimit, 'Min', update_details);
    } else {
        $(permitteddefectspan).text('Cri['+data.defectcriticallimit+'%] Maj['+data.defectmajorlimit+'%] Min['+data.defectminorlimit+'%]');
    }

    // Project status
    var projectstatus = document.createElement('span');
    var projectstatustext = document.createElement('span');
    var projectstatusicon = document.createElement('img');

    $(projectstatusicon).addClass('icon');
    var icon = 'traffic_slow';
    var title = 'Pending';

    if (data.result) {
        if (data.result == constants.QC_RESULT_PASS) {
            icon = 'traffic_go';
            title = 'Passed';
        } else if (data.result == constants.QC_RESULT_REJECT) {
            icon = 'traffic_stop';
            title = 'Rejected';
        }
    }

    $(projectstatusicon).attr('src', constants.PATH_IMAGES_ADMIN + '/icons/' + icon + '_16.gif');
    $(projectstatusicon).attr('title', title);
    $(projectstatusicon).attr('alt', title);
    $(projectstatustext).text(title);
    $(projectstatus).append(projectstatusicon);
    $(projectstatus).append(projectstatustext);

    // Revision elements
    var revisionelements = get_revision_elements(data.projectid);

    // Fill details table
    add_data_row('detailstable', 'CS Job No.', data.jobno, 'Product Name', data.productname);
    add_data_row('detailstable', 'CS Product Code', data.productcode, 'Customer Prod Code', customercode);
    add_data_row('detailstable', revisionelements.revisionnotext, data.revisionstring, 'Last revision date', data.lastrevisiondate);
    add_data_row('detailstable', 'Last udpated by', data.lastupdatedby, 'Batch Size', batchsize);
    add_data_row('detailstable', 'Inspection level', inspectionlevel, 'Sample Size', samplesize);
    add_data_row('detailstable', 'Related Products', relatedproductsspan, 'Shipping marks', shippingmarks);
    add_data_row('detailstable', 'Product specifications customer approved date', approvedproductspan, 'QA specifications customer approved date', approvedqcspan);
    add_data_row('detailstable', 'Permitted Defect', permitteddefectspan, 'Result', projectstatus);
    add_data_row('detailstable', 'Versions', revisionelements.revisionbutton);
    $('#detailstable').removeAttr('colSpan');

    /*
    link = document.createElement('a');
    $(link).attr('href', 'admin.php?d=qc&p=revisions');
    $(link).text(' (View all revisions)');
    $(link).insertAfter(revisionelements.revisionnotext);
    */
    $(document.createElement('br')).insertAfter(revisionelements.revisionnotext);

    // Highlight cell of "Save a new version" button if project contains changes
    if (data.containschanges == 1) {
        redraw_saverevision_button();
    }

    $('.edit').attr('title', 'Click to edit');
    $('.edit').editable('qc/project/update_standard_variable/'+projectid, {
        id: 'field',
        width: '160px',
        callback: function() {
            redraw_saverevision_button();
            var name = this.id;
            get_sample_size(name);
        },
        onblur: 'submit'
    });
}

function get_sample_size(name) {
    // Only update the sample size if the batchsize or
    // inspectionlevel have been changed.
    if (name !== 'batchsize' && name !== 'inspectionlevel') {
        return false;
    }
    $.get('qc/project/get_sample_size/'+projectid, function(data) {
        $('#samplesize').val(data);
    });
}

function draw_inspectors_table(data) {
    var signatures = [],
        label, textarea;

    for (i = 1; i <= 4; i++) {
        signatures[i] = document.createElement('span');
        label = $('<label>', {
            'for': 'inspector_'+i+'_user_id',
        }).text('Name');
        $(signatures[i]).append(label);
        add_dropdown(signatures[i], 'inspector_'+i+'_user_id', i < 3 ? qcinspectors : qcmanagers, data['inspector_'+i+'_user_id'], update_details);
        textarea = $('<textarea>', {
            'id': 'inspector_'+i+'_comments',
            'name': 'inspector_'+i+'_comments',
            'placeholder': 'Comments'
            })
            .height(100)
            .width('99%')
            .css('resize', 'none')
            .text(data['inspector_'+i+'_comments'])
            .bind('change', update_details);
        $(signatures[i]).append(textarea);
    }

    if (has_capability('qc:approveprojects')) {

    }

    add_data_row('inspectorstable', 'Inspector 1', signatures[1], 'Inspector 2', signatures[2]);
    add_data_row('inspectorstable', 'Inspector 3', signatures[3], 'QA Manager', signatures[4]);
}

function make_related_item(relatedid, relatedname) {
    relateditem = document.createElement('li');
    $(relateditem).attr('id', 'related_'+relatedid);
    relatedlink = document.createElement('a');
    $(relatedlink).attr('href', '/qc/project/edit/'+relatedid);
    $(relatedlink).text(relatedname);
    $(relateditem).append(relatedlink);

    if (has_capability('qc:editprojects')) {
        $(relateditem).append(make_action_icon('delete', 'Remove this related product', function() {
            var answer = confirm('Do you really want to this related product? Only the association with this project will be removed, not the product itself.');
            if (answer) {
                delete_related(relatedid, relatedname);
            }
            return false;

        }, 'inlineicon'));
    }
    return relateditem;
}

/// CALLBACKS TO AJAX FILES

function add_related(event, ui) {
    $('#detailsmessage').loading();
    var relatedid = ui.item.value;
    var relatedname = ui.item.label;

    $.getJSON('qc/project/add_related/'+projectid+'/'+relatedid, function(data) {
        print_edit_message('details', $.toJSON(data));
        if (data.type == 'success') {
            $('#relatedproducts').append(make_related_item(relatedid, relatedname));
            redraw_saverevision_button();
        }
    });

}

function delete_related(relatedid, relatedname) {
    $('#detailsmessage').loading();
    $.getJSON('qc/project/delete_related/'+projectid+'/'+relatedid, function(data) {
        print_edit_message('details', $.toJSON(data));
        $('#related_'+relatedid).remove();
        var relatedoption = document.createElement('option');
        $(relatedoption).attr('value', relatedid);
        $(relatedoption).text(relatedname);
        $('#relatedproductsselect').append(relatedoption);
        redraw_saverevision_button();
    });
}

function update_details(data) {
    $('#detailsmessage').loading();
    var value = $(this).val();

    if ($(this).attr('type') == 'checkbox') {
        value = $(this).attr('checked');
        value = (value) ? '1' : '0';
    }

    if (value.length < 1) {
        value = '0';
    }

    var name = $(this).attr('name');
    $.post('qc/project/update_standard_variable/'+projectid, {field: name, value: value}, function(data) {
        if (data.message) {
            print_edit_message('details', $.toJSON(data));
        }
        if (name != 'approved_project_admin' && name != 'approved_product_admin' && name != 'approved_qc_admin') {
            redraw_saverevision_button();
        }
        get_sample_size(name);
    }, 'json');
}

function processJson(data) {
    // 'data' is the json object returned from the server
    $('.error').text('');
    $.each(data.errors, function(field, error) {
        print_error(field, error);
    });
    print_edit_message('details', $.toJSON(data));
    redraw();
}

function draw_part_selector() {
    var duplicateinput = make_autocomplete_input('duplicate', 'duplicate', '', 'QC Project to duplicate', '/qc/project/project_suggest', select_project_to_duplicate);
    var partsinput = make_autocomplete_input('part', 'partid', '', 'Product', '/qc/project/part_suggest', create_project);
    var row1 = document.createElement('tr');
    var row2 = document.createElement('tr');
    var cell1 = document.createElement('td');
    $(cell1).attr('id', 'duplicate_cell');
    var duplicatehidden = document.createElement('input');
    $(duplicatehidden).attr('type', 'hidden');
    $(duplicatehidden).attr('name', 'duplicate_project_id');

    var header1 = document.createElement('th');
    var header2 = document.createElement('th');
    var cell2 = document.createElement('td');

    $(header1).html(duplicateinput.label);
    $(cell1).html(duplicateinput.input);
    $(cell1).append(duplicatehidden);
    $(row1).append(header1);
    $(row1).append(cell1);
    $(header2).html(partsinput.label);
    $(cell2).html(partsinput.input);
    $(row2).append(header2);
    $(row2).append(cell2);
    $('#detailstable').append(row1);
    $('#detailstable').append(row2);
}

function select_project_to_duplicate(event, ui) {
    $('input[name="duplicate_project_id"]').val(ui.item.value);
    $('#duplicate').remove();
    var duplicate_text = document.createElement('span');
    $(duplicate_text).html(ui.item.label);
    $('#duplicate_cell').prepend(duplicate_text);
}

function create_project(event, ui) {
    $('#part').loading();
    $.getJSON('qc/project/save_project/'+ui.item.value+'/'+$('input[name="duplicate_project_id"]').val(), function(data) {
        print_edit_message('project', $.toJSON(data));
        projectid = data.projectid;
        draw_details(data);
        draw_specs('product', data); // For "Dimensions"
        $('a [title=\'pdf\']').each(function(data) {
            var link = $(this).parent();
            $(link).attr('href', $(link).attr('href') + projectid);
        });
    });
}

// prepare the form when the DOM is ready
$(document).ready(function() {
    $.loading.classname = 'loading';

    // load photo counts
    $.getJSON('qc/project/get_spec_photo_counts/'+projectid, function(data) {
        specphotocounts = data;

        // If no projectid given, show the product dropdown
        if (projectid == 0) {
            $('#projectform td.title:first').html('New project');
            draw_part_selector();
        } else {
            // $('#detailsmessage').loading();
            draw_project();
        }
    });



});
