var new_file = false;
var action_icons = null;
var edit_values = {};

function make_rows_uneditable(cancel) {
    "use strict";
    $.each($('#filestable').children().children().has('input[type!="hidden"],textarea'), function (index, row) {
        // For new data row, delete it
        if ($(row).attr('id') == 'file_entry_row') {
            $(row).remove();
            new_file = false;
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
function save_file(event) {
    "use strict";
    event.preventDefault();
    $(event.currentTarget).unbind('click');

    var description = $('textarea[name="description"]');
    var file_id = event.data.id;

    $.post('qc/process/edit_file', { description: description.val(), job_id: job_id, file_id: file_id}, function (data) {
        print_message(data.message, data.type, 'filesmessage');

        if (data.type == 'success') {
            make_rows_uneditable();
        }
    }, 'json');

    return false;
}
function make_row_editable(event) {
    "use strict";
    make_rows_uneditable(true);
    var matches = $(event.currentTarget).attr('id').match('edit_file_([0-9]*)');
    var type = matches[1];
    var target_row = $('#edit_file_row_' + id);

    $.each(target_row.children('[id]'), function (index, cell) {
        if (matches = $(cell).attr('id').match('edit_file_([a-z]*)')) {
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
    $(target_row).children('[class="actions"]').html('<button id="save_file" >Save</button><button id="cancelfile" onclick="make_rows_uneditable(true);">Cancel</button>');

    $('#save_file').bind('click', { id: id }, save_file);
}
function delete_row(event) {
    "use strict";
    make_rows_uneditable(true);

    var matches = $(event.currentTarget).attr('id').match('delete_file_([0-9]*)');
    var id = matches[1];

    if (confirm('Really delete this file?')) {
        $.post('qc/process/delete_file', { id: id }, function (data) {
            print_message(data.message, data.type, 'filesmessage');
            if (data.type == 'success') {
                $('#edit_file_row_' + id).remove();
            }
        }, 'json');
    }
}
function download_pdf(event) {
    "use strict";
    var matches = $(event.currentTarget).attr('id').match('download_file_([0-9]*)');
    var id = matches[1];
    window.location ='/qc/process/download_file/' + id;
}

$(document).ready(function () {
    "use strict";
    // Bind onclick events to all edit and delete buttons
    $('.edit[id*="edit_"]').live('click', {}, make_row_editable);
    $('.delete[id*="delete_"]').live('click', {}, delete_row);
    $('.pdf[id*="download_"]').live('click', {}, download_pdf);
});

