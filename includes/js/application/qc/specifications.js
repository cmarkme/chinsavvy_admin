var menudisplay = false;

function get_revision_elements(projectid) {
    var elements = {};
    // Revision span
    elements.revisionnotext = document.createElement('span');
    $(elements.revisionnotext).html('Revision No.');
    $(elements.revisionnotext).attr('id', 'revisionnotext');

    // Save revision button
    if (has_capability('qc:editprojects')) {
        elements.revisionbutton = document.createElement('input');
        $(elements.revisionbutton).bind('click', function() {
            save_revision(projectid);
        });
        $(elements.revisionbutton).attr('value', 'Save a new version');
        $(elements.revisionbutton).attr('id', 'revisionbutton');
        $(elements.revisionbutton).attr('disabled', true);
        $(elements.revisionbutton).attr('type', 'button');
    } else {
        elements.revisionbutton = '';
    }
    return elements;
}

/**
 * Draws a Spec section of the requested type, using the provided data from AJAX request
 * If the type is Product, start with the special (and fake) "Dimensions" category, containing project parts, then the "files" category
 * @param string type qc|product|additional|observations
 * @param object data Data sent by ajax_specifications.php
 */
function draw_specs(type, data) {
    var catnumber = 1;
    var specnumbers = {};

    var categoryid;
    $('#'+type+'specstable').empty();

    if (type == 'product') {
        add_dimensions_row(catnumber, specnumbers, data);
        catnumber++;
    }

    add_files_row(catnumber, specnumbers, data, type);
    catnumber++;

    for (categoryname in data[type+'specs']) {
        add_category_row(type, categoryname, catnumber, specnumbers, data);
        catnumber++;
    }

    // If a new cat has just been created, we print it and print a new spec in it
    if (!isUndefined(data.newspec) && !isUndefined(data.categoryid) && !isUndefined(data.categoryname) && !isUndefined(data.newcat) && has_capability('qc:write'+type+'specs')) {
        add_category_row(type, data.categoryname, catnumber, specnumbers, data);
        catnumber++;
    }

    // If a newcat is being requested, print an autocomplete input field in a new row
    if (!isUndefined(data.newcat) && data.newcat && isUndefined(data.newspec) && has_capability('qc:write'+type+'specs')) {

        var row = document.createElement('tr');
        var newcatcell = document.createElement('td');
        var newcatelements = make_autocomplete_input('newcat_'+type, 'categoryname', '', 'New Specification Category: ', 'qc/specification/get_categories/'+type,
            function(event, ui) {
                redraw_specs(type, { newspec: true, categoryid : ui.item.value, categoryname : ui.item.label, newcat: true });
            });

        $(newcatelements.input).on('keydown', function(event) {
            if (event.keyCode == 13 && !menudisplay) {
                create_category(type, $(this).val());
            }
        });

        $(newcatcell).append(newcatelements.label);
        $(newcatcell).append(newcatelements.input);
        $(newcatcell).attr('colSpan', 3);

        $(row).append(newcatcell);
        $('#'+type+'specstable').append(row);
    }
}

/**
 * Adds a special "dimensions" category, containing an editable table with a row
 * per project part. This does NOT use the qc_spec_categories or qc_specs tables,
 * but the qc_project_parts table instead. It's a sort of hard-coded product specification.
 *
 * @param int catnumber
 * @param object specnumbers A hash of specification numbers indexed by category name, used to number the specs on the user Interface
 * @param object data Data obtained through AJAX request to ajax_specifications.php
 */
function add_dimensions_row(catnumber, specnumbers, data) {
    $('#dimensionstable').html('');
    var row = document.createElement('tr');
    $(row).attr('id', 'projectpartsheader');
    var categorytitlecell = document.createElement('th');
    $(categorytitlecell).attr('colSpan', 3);
    $(categorytitlecell).text(catnumber + '. Dimensions (in mm or g)');

    $(row).append(categorytitlecell);

    $('#dimensionstable').append(row);

    // Parts table
    var partsrow = document.createElement('tr');
    $(partsrow).attr('id', 'projectparts');
    var partscell = document.createElement('td');
    $(partscell).attr('colSpan', 3);
    $(partscell).addClass('tablewrapper');
    var partstable = document.createElement('table');

    var partsheaderrow = document.createElement('tr');
    var fields = {'name': 'Part','length':'Length','width':'Width','height':'Height','diameter':'Diameter','thickness':'Thickness','weight':'Weight','other': 'Other'};

    for (fieldname in fields) {
        var header = document.createElement('th');
        $(header).text(fields[fieldname]);
        $(partsheaderrow).append(header);
    }


    // Function to add a new row
    if (has_capability('qc:writeproductspecs')) {
        var header = document.createElement('th');
        $(header).addClass('speccatactions');
        $(header).append(make_action_icon('add', 'Add a new Part to this Product', function() {
            var newrow = document.createElement('tr');

            var editableoptions = {
                id: 'field',
                submitdata: {
                    id: projectid
                },
                callback: function() {
                    redraw_saverevision_button();
                    redraw_specs('product');
                    redraw_specs('qc');
                },
                onblur: 'submit'
            };

            for (fieldname in fields) {
                var cell = document.createElement('td');
                if (fieldname == 'name') {
                    $(cell).attr('id', 'newpart');
                    $(cell).editable('qc/specification/edit_value/'+projectid+'/', editableoptions);
                }
                $(newrow).append(cell);
            }
            $(partstable).append(newrow);
        }));
        $(partsheaderrow).append(header);
    }

    $(partstable).append(partsheaderrow);

    $.each(data.parts, function(key, part) {
        var partrow = document.createElement('tr');
        var editableoptions = {
            id: 'field',
            callback: function() {
                redraw_saverevision_button();
                redraw_specs('qc');
            },
            onblur: 'submit',
            tooltip: 'Click to edit...'
        };

        for (fieldname in fields) {
            var cell = document.createElement('td');
            $(cell).attr('id', 'edit_' + fieldname + '_' + part.id);
            if (fieldname != 'name' && fieldname != 'other') {
                $(cell).addClass('number');
            }
            $(cell).text(part[fieldname]);
            $(partrow).append(cell);

            if (has_capability('qc:editproductspecs')) {
                $(cell).editable('qc/specification/edit_value/'+part.id, editableoptions);
            }
        }

        if (has_capability('qc:deleteproductspecs')) {
            // Actions cell
            var deletecell = document.createElement('td');

            $(deletecell).append(make_action_icon('delete', 'Delete this part', function() {
                var answer = confirm('Do you really want to delete this product part? All its dimensions will be deleted.');
                if (answer) {
                    delete_part(part.id);
                }
                return false;
            }));
            $(partrow).append(deletecell);
        }

        $(partstable).append(partrow);
    });

    $(partscell).append(partstable);
    $(partsrow).append(partscell);
    $('#dimensionstable').append(partsrow);
}

/**
 * Adds a special "files" category, containing an editable table with a row
 * per project file. This does NOT use the qc_spec_categories or qc_specs tables,
 * but the qc_project_files table instead. It's a sort of hard-coded product/qc specification.
 *
 * @param int catnumber
 * @param object specnumbers A hash of specification numbers indexed by category name, used to number the specs on the user Interface
 * @param object data Data obtained through AJAX request to ajax_specifications.php
 * @param string type "product" or "qc"
 */
function add_files_row(catnumber, specnumbers, data, type) {
    var type_int = (type == 'qc') ? constants.QC_SPEC_CATEGORY_TYPE_QC : constants.QC_SPEC_CATEGORY_TYPE_PRODUCT;

    $('#'+type+'filestable').html('');
    var row = document.createElement('tr');
    $(row).attr('id', 'projectpartsheader');
    var categorytitlecell = document.createElement('th');
    $(categorytitlecell).attr('colSpan', 3);
    $(categorytitlecell).text(catnumber + '. Files');

    $(row).append(categorytitlecell);

    $('#'+type+'filestable').append(row);

    // Add a numbered row for each attached file, with pdf and delete icons in the actions cell
    var specnumber = 1;

    if (has_capability('qc:write'+type+'specs')) {
        $.each(data.files, function(key, file) {

            if (file.type == type_int) {
                var filerow = document.createElement('tr');
                var numbercell = document.createElement('td');
                $(numbercell).addClass('specnumber');
                $(numbercell).text(catnumber+'.'+specnumber);
                $(filerow).append(numbercell);

                var filenamecell = document.createElement('td');
                $(filenamecell).text(file.file);
                $(filerow).append(filenamecell);

                if (has_capability('qc:delete'+type+'specs')) {
                    // Actions cell
                    var actionscell = document.createElement('td');
                    $(actionscell).addClass('speccatactions');

                    $(actionscell).append(make_action_icon('pdf', 'View this file', function() {
                        window.location = '/qc/specification/view_file/' + file.id;
                    }));

                    $(actionscell).append(make_action_icon('delete', 'Delete this file', function() {
                        var answer = confirm('Do you really want to delete this file?');
                        if (answer) {
                            delete_file(file.id);
                        }
                        return false;
                    }));
                    $(filerow).append(actionscell);
                }

                specnumber++;
                $('#'+type+'filestable').append(filerow);
            }
        });

    }

    // Add a row for a new file attachment
    var uploadrow = document.createElement('tr');
    var uploadcell = document.createElement('td');
    var uploadform = document.createElement('form');
    var typeinput = document.createElement('input');
    var uploadinput = document.createElement('input');
    var uploadsubmit = document.createElement('input');

    $(typeinput).attr('type', 'hidden');
    $(typeinput).attr('name', 'type');
    $(typeinput).attr('value', type_int);

    $(uploadinput).attr('type', 'file');
    $(uploadinput).attr('name', 'project_file');
    $(uploadinput).attr('accept', 'pdf');

    $(uploadsubmit).attr('type', 'submit');
    $(uploadsubmit).attr('value', 'attach file');

    $(uploadform).attr('method', 'post');
    $(uploadform).attr('action', 'qc/project/edit/'+projectid);
    $(uploadform).attr('enctype', 'multipart/form-data');

    $(uploadform).append(typeinput);
    $(uploadform).append(uploadinput);
    $(uploadform).append(uploadsubmit);

    $(uploadcell).attr('colspan', 3);
    $(uploadcell).append(uploadform);

    $(uploadrow).append(uploadcell);

    $('#'+type+'filestable').append(uploadrow);
}

/**
 * Adds a specification category to one of the spec tables, with rows of data for each spec in that category
 * Note: Categories named Dimensions or Files are non-deletable, but you can add custom specs to them (don't know why you would anyway...)
 * @param string type qc|product|additional|observations
 * @param string categoryname
 * @param int catnumber
 * @param object specnumbers A hash of specification numbers indexed by category name, used to number the specs on the user Interface
 * @param object data Data obtained through AJAX request to ajax_specifications.php
 */
function add_category_row(type, categoryname, catnumber, specnumbers, data) {
    var row = document.createElement('tr');
    var categoryid;

    if (data.categoryid) {
        categoryid = data.categoryid;
    } else {
        categoryid = data[type+'specs'][categoryname][0][constants.QC_SPEC_LANGUAGE_EN].categoryid;
    }

    $(row).attr('id', 'category_'+categoryid);
    var categorytitlecell = document.createElement('th');

    $(categorytitlecell).attr('colSpan', 2);

    if (type == 'qc') {
        $(categorytitlecell).attr('colSpan', 3);
    }

    var extratitletext = '';
    var statusicon = document.createElement('img');
    $(statusicon).addClass('icon');
    var icon = 'traffic_slow';
    var title = 'Pending';

    if (data.jobs[categoryid]) {
        if (data.jobs[categoryid].result == constants.QC_RESULT_PASS) {
            icon = 'traffic_go';
            title = 'Pass';
        } else if (data.jobs[categoryid].result == constants.QC_RESULT_REJECT) {
            icon = 'traffic_stop';
            title = 'Reject';
        }
    }

    $(statusicon).attr('src', constants.PATH_IMAGES_ADMIN + '/icons/' + icon + '_16.gif');
    $(statusicon).attr('title', title);
    $(statusicon).attr('alt', title);

    if (type == 'qc' && data.suppliers[categoryid]) {
        extratitletext = ' (assigned to ' + data.suppliers[categoryid].name+')';
    }

    var titletextspan = document.createElement('span');
    $(titletextspan).text(catnumber + '. ' + categoryname + extratitletext);

    if (type == 'qc') {
        $(categorytitlecell).append(statusicon);
    }
    $(categorytitlecell).append(titletextspan);

    $(row).append(categorytitlecell);

    // Procedure header for QC specs
    if (type == 'qc') {
        var proceduretitlecell = document.createElement('th');
        $(proceduretitlecell).html('Procedures');
        $(row).append(proceduretitlecell);
    }

    // Actions cell
    var actionscell = document.createElement('th');
    $(actionscell).addClass('speccatactions');

    // Add a "Checks" icon and a QC result icon for QC type
    if (type == 'qc' && has_capability('qc:viewqcprocesses')) {
        $(actionscell).append(make_action_icon('confirm', 'Edit QC report data for this process', function() {
            window.location = '/qc/process/qc_data/'+categoryid+'/'+projectid;
        }));
        $(actionscell).append(make_action_icon('list', 'View QC Results for this process', function() {
            window.location = '/qc/export_pdf/qc_results/'+categoryid+'/'+projectid;
        }));
    }

    if (has_capability('qc:write'+type+'specs')) {
        $(actionscell).append(make_action_icon('add', 'Add a new specification to this category', function() {
            var parentrow = $(this).parent().parent();
            var matches = $(parentrow).attr('id').match(/category_([0-9]*)/);
            redraw_specs(type, { newspec: true, categoryid : matches[1], categoryname : categoryname });
            $(this).hide();
        }));
    }

    if (has_capability('qc:delete'+type+'specs') && categoryname != 'Dimensions' && categoryname != 'Files') {
        $(actionscell).append(make_action_icon('delete', 'Delete this specification category', function() {
            var answer = confirm('Do you really want to delete this specification category? It will only be deleted for this project, along with its specifications.');
            if (answer) {
                delete_speccat(data[type+'specs'][categoryname][0][constants.QC_SPEC_LANGUAGE_EN].categoryid, type);
            }
            return false;
        }));
    }

    $(row).append(actionscell);
    $('#'+type+'specstable').append(row);

    // Print specs in this category (unless it is a new category)
    if (undefined !== data[type+'specs'][categoryname] && data[type+'specs'][categoryname].length > 0) {
        var foundadditionalspec = false;
        var foundobservation = false;

        $.each(data[type+'specs'][categoryname], function(key, spec) {
            categoryid = spec[constants.QC_SPEC_LANGUAGE_EN].categoryid;

            if (!foundadditionalspec && spec[constants.QC_SPEC_LANGUAGE_EN].type == constants.QC_SPEC_TYPE_ADDITIONAL) {
                foundadditionalspec = true;
                specnumbers[categoryid] = 1;
            }

            if (!foundobservation && spec[constants.QC_SPEC_LANGUAGE_EN].type == constants.QC_SPEC_TYPE_OBSERVATION) {
                foundobservation = true;
                specnumbers[categoryid] = 1;
            }


            if (isUndefined(specnumbers[spec[constants.QC_SPEC_LANGUAGE_EN].categoryid])) {
                specnumbers[categoryid] = 1;
            }

            add_specs_row(type, spec, categoryid, catnumber, specnumbers[categoryid], data.editedspecid);
            specnumbers[categoryid]++;
        });
    }

    // A new spec requires a category id and name, we then show the input fields for english and chinese data
    if (!isUndefined(data.newspec) && data.newspec && data.categoryid == categoryid) {
        $('#'+type+'specstable').append(add_specs_row(type, null, data.categoryid, catnumber, specnumbers[categoryid]));
    }
}

/**
 * Associates a procedure to a QC spec
 */
function assign_procedure_to_spec(event) {
    $('#qcspecsmessage').loading();
    $.getJSON('qc/specification/assign_procedure/' + event.data.specid + '/' + $(event.currentTarget).val(), function(data) {
        redraw_specs('qc');
        print_edit_message('qcspecs', $.toJSON(data));
        $(event.currentTarget).focus();
    });
}

/**
 * Builds and appends a row of spec to the appropriate table
 *
 * @param string type qc|product|additional|observations the type of specification
 * @param object spec A JS object containing an object per language, itself containing specification data
 * @param int categoryid The id of the category this spec belongs to
 * @param int catnumber The visual number of the category (auto-incremented)
 * @param int specnumber The visual number of the spec (auto-incremented)
 * @param int editedspecid An optional specid which indicates which specification was last edited (for highlighting)
 */
function add_specs_row(type, spec, categoryid, catnumber, specnumber, editedspecid) {
    specnumberrow = document.createElement('tr');
    $(specnumberrow).attr('id', 'category_'+categoryid+'_spec_'+specnumber);

    if (isUndefined(specnumber)) {
        specnumber = 1;
    }

    var numbercell = document.createElement('td');
    if (!isNull(spec) && !(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0)) {
        $(numbercell).attr('rowspan', 2);
    }
    $(numbercell).addClass('specnumber');

    var subnumber = '';

    if (!isNull(spec) && spec[constants.QC_SPEC_LANGUAGE_EN].type == constants.QC_SPEC_TYPE_ADDITIONAL) {
        subnumber = 'A.';
        $(specnumberrow).addClass('additional');
    } else if (!isNull(spec) && spec[constants.QC_SPEC_LANGUAGE_EN].type == constants.QC_SPEC_TYPE_OBSERVATION) {
        subnumber = 'B.';
        $(specnumberrow).addClass('observation');
    }

    $(numbercell).text(catnumber+'.'+subnumber+specnumber);
    $(specnumberrow).append(numbercell);

    // For QA specs, importance cell
    if (type == 'qc' && !isNull(spec)) {
        var importancecell = document.createElement('td');
        if (!(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0)) {
            $(importancecell).attr('rowspan', 2);
        }
        $(importancecell).addClass('specimportance');

        // Plain text if no write capability
        if (has_capability('qc:writeqcspecs')) {
            var importancedropdown = document.createElement('select');
            $(importancedropdown).addClass('importance');

            var criticalimportance = document.createElement('option');
            $(criticalimportance).attr('value', constants.QC_SPEC_IMPORTANCE_CRITICAL);
            $(criticalimportance).text('CR');

            var majorimportance = document.createElement('option');
            $(majorimportance).attr('value', constants.QC_SPEC_IMPORTANCE_MAJOR);
            $(majorimportance).text('MA');

            var minorimportance = document.createElement('option');
            $(minorimportance).attr('value', constants.QC_SPEC_IMPORTANCE_MINOR);
            $(minorimportance).text('MI');

            if (spec[constants.QC_SPEC_LANGUAGE_EN].importance == constants.QC_SPEC_IMPORTANCE_CRITICAL) {
                $(criticalimportance).attr('selected', 'selected');
            }
            if (spec[constants.QC_SPEC_LANGUAGE_EN].importance == constants.QC_SPEC_IMPORTANCE_MAJOR) {
                $(majorimportance).attr('selected', 'selected');
            }
            if (spec[constants.QC_SPEC_LANGUAGE_EN].importance == constants.QC_SPEC_IMPORTANCE_MINOR) {
                $(minorimportance).attr('selected', 'selected');
            }

            $(importancedropdown).append(criticalimportance);
            $(importancedropdown).append(majorimportance);
            $(importancedropdown).append(minorimportance);

            $(importancedropdown).bind('change', function() {
                update_importance_level($(this).attr('value'), spec[constants.QC_SPEC_LANGUAGE_EN].specsid);
            });

            $(importancecell).append(importancedropdown);

        } else {
            $(importancecell).load('admin/get_lang_for_constant_value/QC_SPEC_IMPORTANCE_/'+spec[constants.QC_SPEC_LANGUAGE_EN].importance);
        }
        $(specnumberrow).append(importancecell);

    }

    var newenspectext = (has_capability('qc:write'+type+'specs')) ? 'Type English specification here' : 'No English spec entered yet...';
    var newchspectext = (has_capability('qc:write'+type+'specs')) ? 'Type Chinese specification here' : 'No Chinese spec entered yet...';

    var englishcell = document.createElement('td');

    if (type == 'qc' && isNull(spec)) {
        $(englishcell).attr('colspan', 2);
    }

    if (isNull(spec)) {
        spec = {};
        spec[constants.QC_SPEC_LANGUAGE_EN] = {data : newenspectext, specsid: null};
        spec[constants.QC_SPEC_LANGUAGE_CH] = {data : newchspectext, specsid: null};
    } else if (isUndefined(spec[constants.QC_SPEC_LANGUAGE_EN])) {
        spec[constants.QC_SPEC_LANGUAGE_EN] = { data: newenspectext, specsid: null };
    } else if (isUndefined(spec[constants.QC_SPEC_LANGUAGE_EN].data)) {
        spec[constants.QC_SPEC_LANGUAGE_EN].data = newenspectext;
        spec[constants.QC_SPEC_LANGUAGE_EN].specsid = null;
    }

    $(englishcell).html(spec[constants.QC_SPEC_LANGUAGE_EN].data);
    $(englishcell).attr('id', categoryid);

    var editableoptions = {
        id: 'categoryid',
        submitdata: {
            projectid: projectid,
            language: constants.QC_SPEC_LANGUAGE_EN,
            specid: spec[constants.QC_SPEC_LANGUAGE_EN].specsid
        },
        callback: function() {
            redraw_specs(type, { editedspecid: spec[constants.QC_SPEC_LANGUAGE_EN].specsid });
            redraw_saverevision_button();
        },
        onblur: 'submit'
    };

    // For a new spec, erase text before typing
    if (isNull(spec[constants.QC_SPEC_LANGUAGE_EN].specsid)) {
        editableoptions.loadurl = 'qc/specification/edit_speccategory/'+categoryid;
    }

    if (has_capability('qc:edit'+type+'specs') && !(spec[constants.QC_SPEC_LANGUAGE_EN].partid > 0) && !(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0)) {
        $(englishcell).addClass('edit');
        $(englishcell).editable('qc/specification/edit_speccategory/'+categoryid, editableoptions);
        $(englishcell).attr('title', 'Click to edit...');
    }
    $(specnumberrow).append(englishcell);

    // Actions row
    var actionscell = document.createElement('td');
    if (!(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0)) {
        $(actionscell).attr('rowspan', 2);
    }
    $(actionscell).addClass('specactions');

    var type_int = (type == 'qc') ? constants.QC_SPEC_CATEGORY_TYPE_QC : constants.QC_SPEC_CATEGORY_TYPE_PRODUCT;

    if (has_capability('qc:view'+type+'specphotos') && !(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0) && !(spec[constants.QC_SPEC_LANGUAGE_EN].partid > 0)) {
        if (specphotocounts[spec[constants.QC_SPEC_LANGUAGE_EN].specsid] > 0) {
            var photocount = document.createElement('span');
            $(photocount).addClass('photocount');
            $(photocount).html('('+specphotocounts[spec[constants.QC_SPEC_LANGUAGE_EN].specsid]+')');
            $(actionscell).append(photocount);
        }

        var photoicon = make_action_icon('camera', 'Specification photos', function() {
            window.location = '/qc/spec/photos/'+spec[constants.QC_SPEC_LANGUAGE_EN].specsid+'/'+type_int;
        });

        $(actionscell).append(photoicon);
    }

    if (has_capability('qc:delete'+type+'specs') && !(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0) && !(spec[constants.QC_SPEC_LANGUAGE_EN].partid > 0)) {
        $(actionscell).append(make_action_icon('delete', 'Delete this spec', function() {
            var answer = confirm('Do you really want to delete this specification?');
            if (answer) {
                delete_spec(spec[constants.QC_SPEC_LANGUAGE_EN].specsid, type);
            }
            return false;
        }));
    }

    // Procedure cell
    if (type == 'qc' && has_capability('qc:viewprocedures')) {
        var procedurecell = document.createElement('td');
        if (!(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0)) {
            $(procedurecell).attr('rowspan', 2);
        }
        $(specnumberrow).append(procedurecell);

        if (has_capability('qc:editqcspecs')) {
            var proceduredropdown = document.createElement('select');
            $.each(procedures, function(procedure_id, title) {
                var option = document.createElement('option');
                $(option).attr('value', procedure_id);
                $(option).html(title);
                $(proceduredropdown).append(option);
            });
            $(proceduredropdown).on('change', { specid: spec[constants.QC_SPEC_LANGUAGE_EN].specsid}, assign_procedure_to_spec);
            $(procedurecell).append(proceduredropdown);

        }

        if (undefined !== spec[constants.QC_SPEC_LANGUAGE_EN].procedures && spec[constants.QC_SPEC_LANGUAGE_EN].procedures.length != 0) { // If there ARE prcoedures for this spec, length will be undefined
            var procedureslist = document.createElement('ul');
            $.each(spec[constants.QC_SPEC_LANGUAGE_EN].procedures, function(procedure_id, title) {
                var procedurelink = document.createElement('a');
                $(procedurelink).attr('href', 'qc/procedure/edit/' + procedure_id);
                $(procedurelink).html(title);
                var procedureitem = document.createElement('li');
                $(procedureitem).append(procedurelink);

                if (has_capability('qc:editqcspecs')) {
                    $(procedureitem).append(make_action_icon('delete', 'Disassociate this procedure', function() {
                        var answer = confirm('Do you really want to disassociate this procedure from this spec?');
                        if (answer) {
                            delete_spec_procedure(spec[constants.QC_SPEC_LANGUAGE_EN].specsid, procedure_id);
                        }
                        return false;
                    }));
                }

                $(procedureslist).append(procedureitem);
            });
            // TODO Add a delete icon next to each procedure
            // TODO Add a PDF icon next to each procedure? Perhaps not necessary
            $(procedurecell).append(procedureslist);
        }
    }

    $(specnumberrow).append(actionscell);

    var specrow = document.createElement('tr');
    chinesecell = document.createElement('td');
    $(chinesecell).attr('id', 'editspec_category'+categoryid);

    // Don't show anything in the cell until English spec has been entered
    if (!isNull(spec[constants.QC_SPEC_LANGUAGE_EN].specsid)) {
        // Chinese row now by itself (first cell of english row has rowspan = 2)
        var chineseeditableoptions = {
            submitdata: {
                projectid: projectid,
                language: constants.QC_SPEC_LANGUAGE_CH,
                englishid: spec[constants.QC_SPEC_LANGUAGE_EN].specsid
            },
            callback: function() {
                redraw_specs(type, { editedspecid: spec[constants.QC_SPEC_LANGUAGE_CH].specsid });
                redraw_saverevision_button();
            },
            onblur: 'submit'
        };

        if (isUndefined(spec[constants.QC_SPEC_LANGUAGE_CH])) {
            spec[constants.QC_SPEC_LANGUAGE_CH] = { data: newchspectext, specsid: null };
            $(chinesecell).css('font-style', 'italic');
        } else if (isUndefined(spec[constants.QC_SPEC_LANGUAGE_CH].data)) {
            spec[constants.QC_SPEC_LANGUAGE_CH].data = newspectext;
            spec[constants.QC_SPEC_LANGUAGE_CH].specsid = null;
        }

        chineseeditableoptions.submitdata.specid = spec[constants.QC_SPEC_LANGUAGE_CH].specsid;

        // For a new spec, erase text before typing
        if (isNull(spec[constants.QC_SPEC_LANGUAGE_CH].specsid)) {
            chineseeditableoptions.loadurl = 'qc/specification/edit_speccategory/'+categoryid;
        } else {
            chineseeditableoptions.loadurl = null;
        }

        $(chinesecell).html(spec[constants.QC_SPEC_LANGUAGE_CH].data);
        if (has_capability('qc:edit'+type+'specs')) {
            $(chinesecell).addClass('edit');
            $(chinesecell).attr('title', 'Click to edit...');
            $(chinesecell).editable('qc/specification/edit_speccategory/'+categoryid, chineseeditableoptions);
        }
        if (spec[constants.QC_SPEC_LANGUAGE_EN].type == constants.QC_SPEC_TYPE_ADDITIONAL) {
            $(specrow).addClass('additional');
        } else if (spec[constants.QC_SPEC_LANGUAGE_EN].type == constants.QC_SPEC_TYPE_OBSERVATION) {
            $(specrow).addClass('observation');
        }
    }

    if (!(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0)) {
        $(specrow).append(chinesecell);
    }

    $('#'+type+'specstable').append(specnumberrow);
    if (!(spec[constants.QC_SPEC_LANGUAGE_EN].fileid > 0)) {
        $('#'+type+'specstable').append(specrow);
    }
}

/**
 * Performs an AJAX request to ajax_specifications.php, and appends optional data to the returned data, before calling draw_specs(type, data)
 * This makes it possible to update one of the specs table with transitory data, such as a new category or a new spec, before it gets saved in DB
 * @param string type qc|product|additional|observations
 * @param object extradata
 */
function redraw_specs(type, extradata) {
    $.getJSON('qc/specification/get_json_data/'+projectid, function(data) {
        $('#projectform td.title:first').html('Edit project');
        for (variable in extradata) {
            data[variable] = extradata[variable];
        }
        draw_specs(type, data);
    });
}

/**
 * This is used when a change has been made to the project and it needs a new version
 */
function redraw_saverevision_button() {
    if (has_capability('qc:saveprojectrevision')) {
        $('#revisionbutton').attr('disabled', false);
        var newrevisioncell = $('#revisionbutton').parent();
        $(newrevisioncell).css('background-color', '#F00');
        $('#versionwarning').remove();
        var warningspan = document.createElement('span');
        $(warningspan).addClass('versionwarning');
        $(warningspan).attr('id', 'versionwarning');
        $(warningspan).text('This project has been changed since its last saved version.');
        $(newrevisioncell).append(warningspan);
    }
}

/// CALLBACKS TO AJAX FILES

function save_revision(project_id) { // not projectid, which is the global variable
    $('#detailsmessage').loading();
    $.getJSON('qc/specification/save_revision/'+projectid, function(data) {
        // Redraw the revision number and revision button cells
        var revisionnumber = $('#revisionnotext').parent().siblings().filter(':first');
        $(revisionnumber).text(data.revisionnumber);
        var revisionbuttoncell = $('#revisionbutton').parent();
        $(revisionbuttoncell).css('background-color', '#FFF');
        $('#revisionbutton').attr('disabled', true);
        $(revisionbuttoncell).children('span').filter(':first').hide();
        print_edit_message('details', $.toJSON(data));
    });
}

function create_category(type, categoryname) {
    // It's possible that categoryname is actually categoryid, if the user selected a value from the autocomplete and pressed Enter. Don't create in this case, just redraw specs
    if ($.isNumeric(categoryname)) {
        redraw_specs(type, { newspec: true, categoryid : categoryname, categoryname : categoryname, newcat: true });
        return;
    }

    $('#'+type+'specsmessage').loading();
    var realtype = constants['QC_SPEC_CATEGORY_TYPE_'+type.toUpperCase()];
    $.getJSON('qc/specification/create_category/'+realtype+'/'+categoryname, function(data) {
        // Note that, unlike the onItemSelect callback for the autocomplete field, this adds the newcat attribute
        redraw_specs(type, { newspec: true, categoryid : data.categoryid, categoryname : categoryname, newcat: true });
        print_edit_message(type+'specs', $.toJSON(data));
    });
}

function update_importance_level(value, specid) {
    $('#qcspecsmessage').loading();
    $.getJSON('qc/specification/update_importance/'+specid+'/'+value, function(data) {
        redraw_specs('qc');
        redraw_saverevision_button();
        print_edit_message('qcspecs', $.toJSON(data));
    });
}

function delete_spec(specid, spectype) {
    $('#'+spectype+'specsmessage').loading();
    $.getJSON('qc/specification/delete_spec/'+specid, function(data) {
        redraw_specs(spectype);
        redraw_saverevision_button();
        print_edit_message(spectype+'specs', $.toJSON(data));
    });
}

function delete_spec_procedure(specid, procedureid) {
    $('#qcspecsmessage').loading();
    $.getJSON('qc/specification/delete_spec_procedure/'+specid+'/'+procedureid, function(data) {
        redraw_specs('qc');
        print_edit_message('qcspecs', $.toJSON(data));
    });
}

function delete_speccat(categoryid, spectype) {
    $('#'+spectype+'specsmessage').loading();
    $.getJSON('qc/specification/delete_speccategory/'+categoryid+'/'+projectid, function(data) {
        redraw_specs(spectype);
        redraw_saverevision_button();
        print_edit_message(spectype+'specs', $.toJSON(data));
    });
}

function delete_part(partid) {
    $('#productspecsmessage').loading();
    $.getJSON('qc/specification/delete_part/'+partid, function(data) {
        redraw_specs('product');
        redraw_specs('qc');
        redraw_saverevision_button();
        print_edit_message('productspecs', $.toJSON(data));
    });
}

function delete_file(fileid) {
    $('#productspecsmessage').loading();
    $.getJSON('qc/specification/delete_file/'+fileid, function(data) {
        redraw_specs('product');
        redraw_specs('qc');
        redraw_saverevision_button();
        print_edit_message('productspecs', $.toJSON(data));
    });
}

function add_cat(type) {
    redraw_specs(type, { newcat : true });
}

