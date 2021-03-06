var new_item = false;
var new_file = false;
var new_photo = false;
var action_icons = null;
var edit_values = {};

function make_rows_uneditable(cancel) {
    "use strict";
    $.each($('#itemstable,#filestable,#photostable').children().children().has('input[type!="hidden"],textarea'), function (index, row) {
        // For new data row, delete it
        if ($(row).attr('id') == 'data_entry_row') {
            $(row).remove();
            new_item = false;
        } else if ($(row).attr('id') == 'file_entry_row') {
            $(row).remove();
            new_file = false;
        } else if ($(row).attr('id') == 'photo_entry_row') {
            $(row).remove();
            new_photo = false;
        } else {
            // If we are cancelling, restore the edit_values. Otherwise, keep what's in the input fields
            if (cancel) {
                $.each($(row).children().has('input,textarea'), function (j, cell) {
                    var field = $(cell).children('input,textarea').attr('name');
                    $(cell).html(edit_values[field]);
                    delete edit_values[field];
                });
            } else {
                $.each($(row).children().has('input,textarea'), function (j, cell) {
                    $(cell).html($(cell).children('input,textarea').val());
                });
            }

            // Restore action icons
            $(row).children('[class="actions"]').html(action_icons);
            action_icons = null;
        }
    });
}

/**
 * Used for saving a new item or updating an existing item. Existing items have the item's id in the row's DOM id attribute
 */
function save_item(event) {
    "use strict";
    event.preventDefault();
    // Some basic validation
    var number = $('input[name="number"]');
    var item = $('textarea[name="item"]');
    var itemch = $('textarea[name="itemch"]');
    var item_id = 0;
    var method = 'add';
    var matches = {};

    // Determine if we are adding or updating
    if (matches = number.parent().parent().attr('id').match('edit_item_row_([0-9]*)')) {
        item_id = matches[1];
        method = 'edit';
    }

    if (!number.val().match(new RegExp('^[0-9]*$')) || number.val().length < 1) {
        number.effect('highlight', {color: 'red'}, 1000);
        print_message('Item number must be a number!', 'error', 'itemsmessage');
        return false;
    }

    $.post('qc/procedure/edit_item', { number: number.val(), item: item.val(), item_ch: itemch.val(), procedure_id: procedureid, item_id: item_id}, function (data) {
        print_message(data.message, data.type, 'itemsmessage');

        if (data.type == 'success') {
            if (method == 'add') {
                $('#itemstable tr:last').after('<tr id="edit_item_row_' + data.item_id + '">' +
                                               '<td class="tiny" id="edit_item_number_' + data.item_id + '">' + number.val() + '</td>' +
                                               '<td class="textarea" id="edit_item_item_' + data.item_id + '">' + item.val() + '</td>' +
                                               '<td class="textarea" id="edit_item_itemch_' + data.item_id + '">' + itemch.val() + '</td>' +
                                               '<td class="actions" ><div id="edit_item_' + data.item_id + '" class="edit icon"></div>' +
                                               '<div id="delete_item_' + data.item_id + '" class="delete icon"></div></td></tr>');
                new_item = false;
            }
            make_rows_uneditable();
        }
    }, 'json');
}

function save_file(event) {
    "use strict";
    event.preventDefault();
    $(event.currentTarget).unbind('click');

    var description = $('textarea[name="description"]');
    var file_id = event.data.id;

    $.post('qc/procedure/edit_file', { description: description.val(), procedure_id: procedureid, file_id: file_id}, function (data) {
        print_message(data.message, data.type, 'filesmessage');

        if (data.type == 'success') {
            make_rows_uneditable();
        }
    }, 'json');

    return false;
}

function save_photo(event) {
    "use strict";
    event.preventDefault();
    $(event.currentTarget).unbind('click');

    var photo_id = event.data.id;
    var description = $('textarea[name="description"]');

    $.post('qc/procedure/edit_file/photo', { description: description.val(), procedure_id: procedureid, file_id: photo_id}, function (data) {
        print_message(data.message, data.type, 'photosmessage');

        if (data.type == 'success') {
            make_rows_uneditable();
        }
    }, 'json');
    return false;
}

function make_row_editable(event) {
    "use strict";
    make_rows_uneditable(true);
    var matches = $(event.currentTarget).attr('id').match('edit_(item|file|photo)_([0-9]*)');
    var type = matches[1];
    var id = matches[2];
    var target_row = $('#edit_' + type + '_row_' + id);

    $.each(target_row.children('[id]'), function (index, cell) {
        if (matches = $(cell).attr('id').match('edit_' + type + '_([a-z]*)')) {
            var field = matches[1];
            var value = $(cell).html();
            edit_values[field] = value;

            if ($(cell).attr('class') == 'tiny') {
                $(cell).html('<input class="tiny" type="text" name="' + field + '" value="' + value +
                    '" size="' + (value.length + value.length * 0.5) + '" />');
            } else if ($(cell).attr('class') == 'textarea') {
                $(cell).html('<textarea class="textarea" cols="90" rows="3" name="' + field + '">' + value + '</textarea>');
            } else {
                $(cell).html('<input type="text" name="' + field + '" value="' + value + '" size="90" />');
            }

            if (index == 0) {
                $(cell).children().focus().select();
            }
        }
    });

    // Change actions cell to Save/Cancel buttons
    action_icons = $(target_row).children('[class="actions"]').html();
    $(target_row).children('[class="actions"]').html('<button id="save_' + type + '" >Save</button><button id="cancel' + type + '" onclick="make_rows_uneditable(true);">Cancel</button>');

    var callback = null;
    if (type == 'item') {
        callback = save_item;
    } else if (type == 'file') {
        callback = save_file;
    } else if (type == 'photo') {
        callback = save_photo
    }
    $('#save_'+type).bind('click', { id: id }, callback);
}

function delete_row(event) {
    "use strict";
    make_rows_uneditable(true);
    var matches = $(event.currentTarget).attr('id').match('delete_(item|file|photo)_([0-9]*)');
    var type = matches[1];
    var id = matches[2];

    if (confirm('Really delete this ' + type + '?')) {
        $.post('qc/procedure/delete_' + type, { id: id }, function (data) {
            print_message(data.message, data.type, type + 'smessage');
            if (data.type == 'success') {
                $('#edit_' + type + '_row_' + id).remove();
            }
        }, 'json');
    }
}

function add_item() {
    "use strict";
    make_rows_uneditable(true);

    if (!new_item) {
        new_item = true;
        $('#itemstable tr:last').after('<tr id="data_entry_row"><td id="number"><input type="text" size="3" name="number" /></td>' +
                                            '<td id="item"><textarea cols="90" rows="3" name="item"></textarea></td>' +
                                            '<td id="itemch"><textarea cols="90" rows="3" name="itemch"></textarea></td>' +
                                            '<td><button id="save_item">Save</button><button id="cancelitem" onclick="make_rows_uneditable(true);">Cancel</button></td></tr>');
    }
    $('#data_entry_row').effect('highlight', {color: 'yellow'}, 1000);
    $('#save_item').bind('click', { }, save_item);
    $('#data_entry_row input:first').focus();
}

function add_file() {
    "use strict";
    make_rows_uneditable(true);

    if (!new_file) {
        new_file = true;
        $('#filestable tr:last').after('<tr id="file_entry_row"><td id="file"><input type="file" name="newfile" /></td>' +
                                       '<td>?</td><td id="description"><textarea name="description" cols="60" rows="3"></textarea></td>' +
                                       '<td></td><td><input type="submit" value="Upload" /><button id="cancelfile" onclick="make_rows_uneditable(true);">Cancel</button></td></tr>');
    }
}

function add_photo() {
    "use strict";
    make_rows_uneditable(true);

    if (!new_photo) {
        new_photo = true;
        $('#photostable tr:last').after('<tr id="photo_entry_row"><td id="photo"><input type="file" name="newphoto" /></td>' +
                                       '<td></td><td id="description"><textarea name="description" cols="60" rows="3"></textarea><td>?</td></td>' +
                                       '<td></td><td><input type="submit" value="Upload" /><button id="cancelphoto" onclick="make_rows_uneditable(true);">Cancel</button></td></tr>');
    }
}

function download_pdf(event) {
    "use strict";
    matches = $(event.currentTarget).attr('id').match('download_file_([0-9]*)');
    var id = matches[1];
    window.location ='/qc/procedure/download_file/' + id;
}

$(document).ready(function () {
    "use strict";
    // Bind onclick events to all edit and delete buttons
    $('.edit[id*="edit_"]').live('click', {}, make_row_editable);
    $('.delete[id*="delete_"]').live('click', {}, delete_row);
    $('.pdf[id*="download_"]').live('click', {}, download_pdf);

    $('#photostable a').lightBox();
    $.each($('#projects table tr'), function (index) {
        if (undefined !== $(this).attr('id')) {
            var matches = $(this).attr('id').match('^qcproject_([0-9]*)$');
            var projectid = matches[1];
            $(this).on('mouseover', { }, function(event) {
                $(event.currentTarget).addClass('rowhover');
            });
            $(this).on('mouseout', { }, function(event) {
                $(event.currentTarget).removeClass('rowhover');
            });
            $(this).on('click', { }, function(event) {
                window.location = 'qc/project/edit/' + projectid;
            });
        }
    });
});

