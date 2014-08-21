/**
 * Redraws the form to reflect current state of DB
 */

function redraw() {
    $('#userform div:first').loading({onAjax:true});
    if (user_id == 0) {
        return;
    }

    $.getJSON('users/user/get_data/' + user_id, function(data) {
        $('#pagetitle td.title').text(data.title);

        $('input[name=user_id]').val(data.user_id);
        $('#user_id').text(data.user_id);
        $('select[name=salutation]').val(data.salutation);
        if (isNull(data.salutation)) {
            $('select[name=salutation]').val(0);
        }
        $('input[name=first_name]').val(data.first_name);
        $('input[name=surname]').val(data.surname);
        $('input[name=first_name_chinese]').val(data.first_name_ch);
        $('input[name=surname_chinese]').val(data.surname_ch);
        $('input[name=username]').val(data.username);
        $('input[name=password]').val(data.password);
        if (data.disabled == '1') {
            $('input[name=disabled]').attr('checked', true);
        } else {
            $('input[name=disabled]').attr('checked', false);
        }
        $('textarea[name=signature]').val(data.signature);

        // Empty list first before redrawing
        $('#email').text('');
        $('#phone').text('');
        $('#mobile').text('');
        $('#fax').text('');

        // Contacts
        if (isUndefined(data.user_id) || data.user_id < 1) {
            $('#email').text('Enter and save the User details first');
            $('#phone').text('Enter and save the User details first');
            $('#mobile').text('Enter and save the User details first');
            $('#fax').text('Enter and save the User details first');
        } else {
            $('#email').append(get_contact_list(data.emails, 'email'));
            $('#phone').append(get_contact_list(data.phones, 'phone'));
            $('#mobile').append(get_contact_list(data.mobiles, 'mobile'));
            $('#fax').append(get_contact_list(data.faxes, 'fax'));
        }
    });
}

function change_default_contact(e) {
    $('#'+e.data.type).loading();
    $.post('users/user/update_default_contact/'+this.value, {}, function(data) {
        print_edit_message('contacts', data);
        redraw();
    });
}

function set_notification(e) {
    $('#'+e.data.type).loading();
    $.post('users/user/set_notification/'+this.value+'/'+this.checked, {}, function(data) {
        print_edit_message('contacts', data);
        redraw();
    });
}

function delete_contact(e) {
    $('#'+e.data.type).loading();
    $.post('users/user/delete_contact/'+e.data.contact_id, {}, function(data) {
        print_edit_message('contacts', data);
        redraw();
    });
}

function save_contact(input) {
    $.post('users/user/save_contact', { user_id: user_id, field_name: input.name, value: $(input).val() }, function(data) {
        print_edit_message('contacts', data);
        data = $.evalJSON(data);
        if (data.errors.length < 1) {
            redraw();
        } else {
            $.each(data.errors, function(field, error) {
                print_error(field, error);
            });
        }
    });
}

function get_contact_list(contacts, type) {
    var contactscount = contacts.length;
    var contactlist = document.createElement('ul');

    if (contacts.length > 0) {
        $.each(contacts, function(key, contact) {

            var listitem = document.createElement('li');
            var contactinput = document.createElement('input');
            var name = type+'['+contact.id+']';
            $(contactinput).attr('type', 'text');
            $(contactinput).attr('name', name);
            $(contactinput).attr('size', 40);
            $(contactinput).attr('id', type+'_'+contact.id);
            $(contactinput).attr('value', contact.contact);

            $(contactinput).bind('blur', function(e) {
                if ($(this).val() == '') {
                    $(this).val('New '+type+'...');
                } else {
                    $('#'+type).loading();
                    save_contact(this);
                }
            });

            $(contactinput).bind('keypress', function(e) {
                if (e.keyCode == 13) { // ENTER
                    $(this).blur();
                } else if (e.keyCode == 27) { // ESC
                    $(this).val(contact.detail);
                    $('#'+type+'_0').focus();
                }
            });

            $(listitem).append(contactinput);

            // Notification checkbox
            if (contact.type == 1) {
                notifylabel = document.createElement('label');
                notifycheckbox = document.createElement('input');
                $(notifylabel).attr('for', 'receive_notifications['+contact.id+']');
                $(notifylabel).text('Receive notifications');
                $(notifycheckbox).attr('type', 'checkbox');
                $(notifycheckbox).attr('id', 'receive_notifications['+contact.id+']');
                $(notifycheckbox).attr('name', 'receive_notifications['+contact.id+']');
                $(notifycheckbox).attr('value', contact.id);
                $(notifycheckbox).bind('click', {type: type}, set_notification);

                if (contact.receive_notifications == 1) {
                    $(notifycheckbox).attr('checked', 'checked');
                }
                $(listitem).append(notifycheckbox);
                $(listitem).append(notifylabel);
            }

            // Default checkbox
            defaultlabel = document.createElement('label');
            defaultradio = document.createElement('input');
            var iddefault = 'default_'+type+'_'+contact.id;
            $(defaultlabel).attr('for', iddefault);
            $(defaultlabel).text('Default');
            $(defaultradio).attr('type', 'radio');
            $(defaultradio).attr('id', iddefault);
            $(defaultradio).attr('name', 'default_'+type);
            $(defaultradio).attr('value', contact.id);
            $(defaultradio).bind('click', {type: type}, change_default_contact);

            if (contact.default_choice == 1) {
                $(defaultradio).attr('checked', 'checked');
            }
            $(listitem).append(defaultradio);
            $(listitem).append(defaultlabel);

            // Delete link
            if (contactscount > 1 || contact.type != 'Email') {
                deletelink = document.createElement('span');
                $(deletelink).addClass('delete');
                $(deletelink).attr('title', 'Delete this '+contact.type);
                $(deletelink).text('delete');
                $(deletelink).bind('click', {type: type, contact_id: contact.id}, delete_contact);
                $(listitem).append(deletelink);
            }

            $(contactlist).append(listitem);
        });
    }

    // New contact
    var listitem = document.createElement('li');
    var contactinput = document.createElement('input');
    $(contactinput).attr('type', 'text');
    $(contactinput).attr('name', type+'[0]');
    $(contactinput).attr('id', type+'_0');
    $(contactinput).attr('value', 'New '+type+'...');
    $(contactinput).bind('focus', function() {
        $(this).val('');
    });
    $(contactinput).bind('blur', function(e) {
        if ($(this).val() == '') {
            $(this).val('New '+type+'...');
        } else {
            $('#'+type).loading();
            save_contact(this);
        }
    });

    $(listitem).append(contactinput);
    $(contactlist).append(listitem);

    return contactlist;
}

function processJson(data) {
    // 'data' is the json object returned from the server

    $('textarea[name=signature]').val(data.first_name);
    $('.error').text('');
    $.each(data.errors, function(field, error) {
        print_error(field, error);
    });
    user_id = data.user_id;

    if (user_id > 0) {
        $('input[type=submit]').val('Update...');
    }

    print_edit_message('details', $.toJSON(data));
    redraw();
}


// prepare the form when the DOM is ready
$(document).ready(function() {
    // Add an id to each form element
    $('#userform input,select,textarea,password').each(function(index) {
        $(this).attr('id', $(this).attr('name'));
    });

    // Add a "reveal" checkbox next to password field
    // bind form using ajaxForm
    $('#userform').ajaxForm({
        // dataType identifies the expected content type of the server response
        dataType:  'json',
        beforeSerialize: function($form, options) {
            CKEDITOR.instances.signature.updateElement();
        },
        // success identifies the function to invoke when the server response
        // has been received
        success:   processJson
    });

    $.loading.classname = 'loading';

    redraw();

});
