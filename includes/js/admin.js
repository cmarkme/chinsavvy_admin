/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Static class to handle admin methods.
 *
 * @category  Admin
 * @package   javascript
 *
 * @todo We have two text counter functions please remove one if not needed
 * @todo Add all comments to the functions
 * @todo Should set the browser sniffer on start up as needed by other functions
 * @todo The NOLINK javascript in the tables still shows the POINTER cursor when
 *       over the cell, this should not happen.
 * @todo As javascript is clientside always try and make this file as small as
 *       possible - remove rubbish keep descriptions brief - clear Todos!
 */

/**
 * Make the site load up in a new frame if it is forced into a sub frame of
 * another site
 */
if (self != top) {
    top.location.href = self.location.href;
}

function addslashes(str) {
    str=str.replace(/\\/g,'\\\\');
    str=str.replace(/\'/g,'\\\'');
    str=str.replace(/\"/g,'\\"');
    str=str.replace(/\0/g,'\\0');
    return str;
}
function stripslashes(str) {
    str=str.replace(/\\'/g,'\'');
    str=str.replace(/\\"/g,'"');
    str=str.replace(/\\0/g,'\0');
    str=str.replace(/\\\\/g,'\\');
    return str;
}

/**
 * @todo Document this function
 */
function jumpMenu(targ, selObj, restore) {

    eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");

    if (restore) {
        selObj.selectedIndex = 0;
    }
}

/**
 * @todo Document this function
 */
function deletethis(link) {
    var name = confirm('Before we continue are you sure you want to delete this?');

    if (name == true && link != null) {
        window.location = link;
    } else if(name == true) {
        return true;
    } else {
        return false;
    }
}

/**
 * @todo Document this function
 */
function popwindow(popsrc, stuff) {
    if (!stuff) {
        videoWindow = window.open(popsrc, 'Popup', 'width=800, height=600, left=50, top=50, scrollbars=yes, toolbars=yes, menubar=yes, location=yes');
    } else {
        videoWindow = window.open(popsrc, 'Popup', stuff)
    }
}

/**
 * @todo Document this function
 */
function blocking(nr, type) {
    var browser = navigator.appName;
    var arrow = document.getElementById('arrow_' + nr);

    if (browser == 'Microsoft Internet Explorer') {
        type = 'block';
        arrow.style.background = 'black url(images/navigation/navopened.gif) center right no-repeat';
    }

    if (document.layers) {
        if (document.layers[nr].display == 'none' ||
            document.layers[nr].display == 'undefined') {
            var current = type;
            arrow.style.background = 'url(/images/admin/icons/arrow-up_16.gif) center top no-repeat';
        } else {
            var current = 'none';
            arrow.style.background = 'url(/images/admin/icons/arrow-down_16.gif) center top no-repeat';
        }

        document.layers[nr].display = current;
        var subdivs = document.layers[nr].hasChildNodes();
        alert(subdivs);

    } else if (document.getElementById) {

        var row = document.getElementById(nr);

        if (row.style.display == 'none' || row.style.display == 'undefined') {
            row.style.display = type;
            arrow.style.background = 'url(/images/admin/icons/arrow-up_16.gif) center top no-repeat';
        } else {
            row.style.display = 'none';
            arrow.style.background = 'url(/images/admin/icons/arrow-down_16.gif) center top no-repeat';
        }
    }
}

/**
 * Highlights a row when mouseover and converts the onclick behaviour to a link
 * based on the first <a> tag found.
 *
 * @param string xTableId the Table's id attribute
 */
function ConvertRowsToLinks(xTableId) {
    var rows = document.getElementById(xTableId).rows;

    var rowclickfunction = function() {

        inputs = this.getElementsByTagName('input');
        if (inputs.length > 0 && inputs[0].type == 'checkbox') {
            var checkbox = inputs[0];
            var id = checkbox.id;

            if (this.className == 'row_click') {
                checkbox.click();
                this.className = '';
            } else {
                checkbox.click();
                this.className = 'row_click';
            }
        } else if(this.innerHTML.match('no-link')) {

        } else {
            var link = this.getElementsByTagName('a');
            if (link.length != 0) {
                // this.className = 'row_click';
                document.location.href = link[0].href;

            }
        }
    }

    for (i = 0; i < rows.length; i++) {
        var event = '';

        // Browse through the table cells to see if a nolink class is set

        var cells = rows[i].cells;

        for (j = 0; j < cells.length; j++) {

            if (cells[j].className.match("nolink")) {

                // A nolink class is formatted like this: nolink_tablename_rownumber

                cells[j].onmouseover = function() {
                    parts = this.className.split("_");

                    tablename = parts[1];
                    rownumber = parts[2];

                    // In case another class is given, keep only the number

                    numbersplit = rownumber.split(" ");
                    rownumber = numbersplit[0];

                    document.getElementById(tablename + rownumber).onclick = null;
                }

                cells[j].onmouseout = function() {
                    parts = this.className.split("_");
                    tablename = parts[1];
                    rownumber = parts[2];

                    // In case another class is given, keep only the number

                    numbersplit = rownumber.split(" ");
                    rownumber = numbersplit[0];

                    document.getElementById(tablename + rownumber).onclick = rowclickfunction;
                }
            }
        }

        // If <!-- nohighlight -- > is found within the row, do not convert to link
        if(rows[i].innerHTML.match("nohighlight") == null) {

            // If the row is not already selected, highlight it when mouseover
            rows[i].onmouseover = function() {
                if (this.className != 'row_click') {
                    this.className = 'row_highlight';
                }
            }

            // If the row is not already selected, remove the highlight when mouseout
            rows[i].onmouseout = function() {
                if (this.className != 'row_click') {
                    this.className = '';
                }
            }

            rows[i].onclick = rowclickfunction;
        }
    }
}

/**
 * When a row is clicked, inverts any checkbox on that row.
 *
 * @param event
 * @param row
 */
function invertCheckBox(event, row) {
    if (!event) var event = alert(window.event);

    // Determine which element was clicked. If it is a checkbox, do nothing
    var targ;
    if (event.target) {
        targ = event.target;
    } else if (event.srcElement) {
        targ = event.srcElement;
    }
    if (targ.nodeType == 3) { // defeat Safari bug
        targ = targ.parentNode
    }
    var tname;
    tname = targ.tagName;
    if (tname != "INPUT") {
        var inputs = row.getElementsByTagName('input');
        for (i = 0; i < inputs.length; i++) {
            if (inputs[i].type == 'checkbox') {
                var checkbox = inputs[i];
                checkbox.click();
            }
        }
    }
}

/**
 *
 */
function check_max_chars(max_chars, element, list_id, event) {
    if (element.value.length > max_chars) {
        element.value = element.value.substring(0, max_chars);
        document.getElementById('max_' + list_id).style.display = 'block';
        document.getElementById('show_count_' + list_id).style.display = 'none';
    } else {
        document.getElementById('show_count_' + list_id).style.display = 'inline';
        document.getElementById('max_' + list_id).style.display = 'none';

        // Detect which key was pressed. If delete (unicode 8), use -1
        if (event.keyCode == '8') {
            document.getElementById('count_' + list_id).value = (max_chars - element.value.length + 1);
        } else {
            document.getElementById('count_' + list_id).value = (max_chars - element.value.length - 1);
        }

        if (document.getElementById('count_' + list_id).value < 0) {
            document.getElementById('count_' + list_id).value = 0;
        } else if (document.getElementById('count_' + list_id).value > max_chars) {
            document.getElementById('count_' + list_id).value = max_chars;
        }
    }
}

/**
 * In a given html <form>, ticks all the checkboxes.
 */
function check_all(form) {
    for (var c = 0; c < form.elements.length; c++) {
        if (form.elements[c].type == 'checkbox') {
            form.elements[c].checked = true;
        }
    }
}

/**
 * In a given html <form>, unticks all the checkboxes.
 */
function uncheck_all(form) {
    for (var c = 0; c < form.elements.length; c++) {
        if (form.elements[c].type == 'checkbox') {
            form.elements[c].checked = false;
        }
    }
}

/**
 * In a given html <form>, ticks all the unticked checkboxes and unticks all the
 * ticked ones: reverses the selection.
 */
function invert_selection(form) {
    for (var c = 0; c < form.elements.length; c++) {
        if (form.elements[c].type == 'checkbox') {
            if(form.elements[c].checked == true) {
                form.elements[c].checked = false;
            } else {
                form.elements[c].checked = true;
            }
        }
    }
}

/**
 * Checks whether any checkbox has been ticked before taking an action
 */
function check_ticks(form, element, select_obj) {
    for (var c = 0; c < form.elements.length; c++) {
        if (form.elements[c].type == 'checkbox') {
            if(form.elements[c].checked == true) {
                return true;
            }
        }
    }
    if (select_obj.selectedIndex == 1) {
        window.alert('You need to select at least one ' + element + ' before performing a global action on "selected" CLIs');
        return false;
    }
}

/**
 * Checks the state of a checkbox and updates a given input box accordingly
 */
function update_counter(checkbox, input, number) {
    var input = document.getElementById(input);
    var total = parseInt(input.value);
    var number = parseInt(number);
    if (checkbox.checked == true) {
        total += number;
    } else {
        total -= number;
    }
    input.value = total;
}

/**
 * This is an equivalent of PHP's in_array(), except that it takes an optional third
 * argument, the attribute. This way you can send an array of HTML elements and
 * search for a matching attribute like 'id' or 'class'. Otherwise it behaves just like PHP's in_array()
 */
function in_array(needle, haystack, attribute)
{
    for (var i = 0; i < haystack.length; i++) {
        if (undefined != attribute) {
            if (eval("haystack[i]." + attribute + ";") == needle) {
                return true;
            }
        } else {
            if (eval("haystack[i];") == needle) {
                return true;
            }
        }
    }
    return false;
}

/*
 * Function to strip out HTML from a given string
 */
function stripHtml(str)
{
    var re  = '';

    re  = /<\S[^>]*>/g;
    str = str.replace(re, '');
    re  = /&gt;/g;
    str = str.replace(re, '>');
    re  = /&lt;/g;
    str = str.replace(re, '<');
    re  = /&amp;/g;

    if (str == '-') {
        str = '';
    }

    return str.replace(re, '&');
}

/**
* The following are useful functions that should have been part of Javascript
*/
function isAlien(a) {
   return isObject(a) && typeof a.constructor != 'function';
}

function isArray(a) {
    return isObject(a) && a.constructor == Array;
}

function isBoolean(a) {
    return typeof a == 'boolean';
}

function isEmpty(o) {
    var i, v;
    if (isObject(o)) {
        for (i in o) {
            v = o[i];
            if (isUndefined(v) && isFunction(v)) {
                return false;
            }
        }
    }
    return true;
}

function isFunction(a) {
    return typeof a == 'function';
}

function isNull(a) {
    return typeof a == 'object' && !a;
}

function isNumber(a) {
    return typeof a == 'number' && isFinite(a);
}

function isObject(a) {
    return (a && typeof a == 'object') || isFunction(a);
}

function isString(a) {
    return typeof a == 'string';
}

function isUndefined(a) {
    return typeof a == 'undefined';
}

// If firebug console is undefined, define a fake one here
if (window.console) {
    console.vardump = function(data) {
        retval = '';
        for (key in data) {
            retval += key+' = '+data[key] + "\n";
        }
        console.log(retval);
    };
}

// This code is in the public domain. Feel free to link back to http://jan.moesen.nu/
function sprintf() {
    if (!arguments || arguments.length < 1 || !RegExp) {
        return;
    }

    var str = arguments[0];
    var re = /([^%]*)%('.|0|\x20)?(-)?(\d+)?(\.\d+)?(%|b|c|d|u|f|o|s|x|X)(.*)/;
    var a = b = [], numSubstitutions = 0, numMatches = 0;

    while (a = re.exec(str)) {
        var leftpart = a[1], pPad = a[2], pJustify = a[3], pMinLength = a[4];
        var pPrecision = a[5], pType = a[6], rightPart = a[7];

        //alert(a + '\n' + [a[0], leftpart, pPad, pJustify, pMinLength, pPrecision);

        numMatches++;
        if (pType == '%') {
            subst = '%';
        } else {
            numSubstitutions++;
            if (numSubstitutions >= arguments.length) {
                alert('Error! Not enough function arguments (' + (arguments.length - 1) + ', excluding the string)\nfor the number of substitution parameters in string (' + numSubstitutions + ' so far).');
            }

            var param = arguments[numSubstitutions];
            var pad = '';

            if (pPad && pPad.substr(0,1) == "'") {
                pad = leftpart.substr(1,1);
            } else if (pPad) {
                pad = pPad;
            }

            var justifyRight = true;
            if (pJustify && pJustify === "-") {
               justifyRight = false;
            }

            var minLength = -1;

            if (pMinLength) {
                minLength = parseInt(pMinLength);
            }

            var precision = -1;

            if (pPrecision && pType == 'f') {
                precision = parseInt(pPrecision.substring(1));
            }

            var subst = param;

            if (pType == 'b') {
                subst = parseInt(param).toString(2);
            } else if (pType == 'c') {
                subst = String.fromCharCode(parseInt(param));
            } else if (pType == 'd') {
                subst = parseInt(param) ? parseInt(param) : 0;
            } else if (pType == 'u') {
                subst = Math.abs(param);
            } else if (pType == 'f') {
                subst = (precision > -1) ? Math.round(parseFloat(param) * Math.pow(10, precision)) / Math.pow(10, precision): parseFloat(param);
            } else if (pType == 'o') {
                subst = parseInt(param).toString(8);
            } else if (pType == 's') {
                subst = param;
            } else if (pType == 'x') {
                subst = ('' + parseInt(param).toString(16)).toLowerCase();
            } else if (pType == 'X') {
                subst = ('' + parseInt(param).toString(16)).toUpperCase();
            }
        }
        str = leftpart + subst + rightPart;
    }
    return str;
}


function editnote(link) {
    window.location = 'admin.php?d=chinasavvy&p=edit_enquiry_note&enquiry_id='+link;
    return true;
}

function append_enquiry_note(enquiry_id, user_id) {
    var note = jQuery('#append_note');

    jQuery.ajax({
        type:"POST",
        url: 'enquiries/enquiry/append_note',
        data: {enquiry_id : enquiry_id, user_id: user_id, note: note.val()},
        success: function(updated_notes) {
            jQuery('#enquiry_notes').html(updated_notes);
            jQuery('#enquiry_notes').effect('highlight', {color: 'yellow'}, 1000);
        },
        error: function(XHR, textStatus, errorThrown) {
            jQuery('#enquiry_notes').html(errorThrown);
            jQuery('#enquiry_notes').effect('highlight', {color: 'red'}, 1000);
        }
    });
}

jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
    return regex.test(jQuery(elem)[attr.method](attr.property));
}

function print_message(message, type, id) {
    $('#'+id).stop(true, true);
    $('#'+id).load('admin/print_message', {'message': message, 'type': type}).fadeIn(1000);
    $('#'+id).pause(2000).fadeOut(2000);
}

function apply_loading_overlay(message1, message2, top) {
    $.create("div", {"id": "overlay"}).appendTo('body');
    $.create("div", {"id": "uploaddiv"}).appendTo('#overlay');
    $("#uploaddiv").css('top', top);
    $.create("p", '', message1).appendTo('#uploaddiv');
    $.create("img", {"id": "spinner", "src": "images/spinner.gif"}).appendTo('#uploaddiv');
    $.create('p', '', message2).appendTo('#uploaddiv');
}

/**
 * Global function for ajaxifying a html table, looking up and paginating data from a PHP page.
 * This supports text and select filters, as well as column sorts.
 * The function returns the ajaxtable object, so that it can be overridden in any way required.
 * Conventions used for this abstraction:
 * 1. filters are named (id) after the variable they send, so a variable "name" is sent by a "namefilter" input or select element.
 * TODO Remove the hard-coded page limit of 20 rows (iDisplayLength) and make it a configurable parameter
 */
function setup_ajax_table(ajaxsource, sortcolumns, serverdata, clickablerows, drawcallback, aaSorting) {

    // Apply the same class to each column's cell as the class of its heading (set up in CI view)
    var columnHeaders = [];

    $('#ajaxtable th').each(function() {
        columnHeaders.push($(this).attr('class'));
    });

    var aoColumns = new Array(sortcolumns.length);

    for (var i = 0; i < sortcolumns.length; i++) {
        if (sortcolumns[i]) {
            aoColumns[i] = {"sClass" : columnHeaders[i]};
        } else {
            aoColumns[i] = {"bSortable": false, "sClass" : columnHeaders[i] };
        }
    }

    var ajaxtable = $('#ajaxtable').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
        "bLengthChange": false,
        "asStripClasses": ['odd', 'even'],
        "iDisplayLength": 40,
        "sDom": '<"top"ip>rt',
        "bJQueryUI": false,
        "sPaginationType": 'input',
        "aoColumns": aoColumns,
        "sAjaxSource": ajaxsource,
        "fnServerData": function ( sSource, aoData, fnCallback ) {
			/* Add some extra data to the sender */
            for (var name in serverdata) {
                if (serverdata[name] == 'checkbox') {
                    var value = $('#'+name+'filter').attr('checked');
                    value = (value) ? '1' : '0';
                    aoData.push( {"name": name, "value": value });
                } else if (serverdata[name] == 'combo') {
                    var value = $('#combofilter').val();
                    var field = $('#search_field').val();
                    var operator = $('#operator').val();

                    aoData.push( {"name": "search_field", "value": field });
                    aoData.push( {"name": "search_value", "value": value });
                    aoData.push( {"name": "operator", "value": operator });
                } else {
                    aoData.push( {"name": name, "value": $('#'+name+'filter').val() });
                }
            }

            // Add or update hidden form fields for export icons
            $.each(aoData, function(index, field) {
                var attributes = { type: 'hidden', name: field.name, value: field.value };
                if (undefined != $('#export_to_pdf')) {
                    if (($("#export_to_pdf input[name='"+field.name+"']")).length == 0) {
                        $('<input>').attr(attributes).appendTo('#export_to_pdf');
                    } else {
                        $("#export_to_pdf input[name='"+field.name+"']").attr('value', field.value);
                    }
                }
                if (undefined != $('#export_to_xml')) {
                    if ($("#export_to_xml input[name='"+field.name+"']").length == 0) {
                        $('<input>').attr(attributes).appendTo('#export_to_xml');
                    } else {
                        $("#export_to_xml input[name='"+field.name+"']").attr('value', field.value);
                    }
                }
                if (undefined != $('#export_to_csv')) {
                    if ($("#export_to_csv input[name='"+field.name+"']").length == 0) {
                        $('<input>').attr(attributes).appendTo('#export_to_csv');
                    } else {
                        $("#export_to_csv input[name='"+field.name+"']").attr('value', field.value);
                    }
                }
            });

            // Modify the link to the PDF file, so it uses the same sorting/filtering as the table
            if ($('a[class="pdf"]')) {
                var currentpdflink = $.urlParser.parse($('a[class="pdf"]').attr('href')).path;
                currentpdflink += '?';
                for (var attr in aoData) {
                    currentpdflink += aoData[attr].name + '=' + aoData[attr].value + '&';
                }
                $('a[class="pdf"]').attr('href', currentpdflink)
            }

			$.post( sSource, aoData, function (oTable) {
				/* Do whatever additional processing you want on the callback, then tell DataTables */
				fnCallback(oTable)
			}, 'json' );
		},
        "fnRowCallback": function( nRow, aData, iDisplayIndex) {
            $(nRow).attr('id', 'row_'+aData[0]);
            if (clickablerows) {
                $(nRow).bind('mouseover', function() {
                    $(this).addClass('rowhover');
                });
                $(nRow).bind('mouseout', function() {
                    $(this).removeClass('rowhover');
                });

                // Instead of adding an onclick event for the entire row, do it per cell, making sure the actions cell doesn't have one
                $(nRow).children('td').each(function() {
                    if (!$(this).hasClass('actions')) {
                        $(this).bind('click', function() {
                            window.location = $('#'+$(nRow).attr('id')+" a[class='edit']").attr('href');
                        });
                    }
                });
            }

            // Convert delete links to AJAX requests
            $(nRow).find('a[class="delete"]').bind('click', function(event) {
                event.preventDefault();
                $.getJSON(this.href, {}, function(result) {
                    display_message(result.type, result.message);
                    if (result.type == 'success') {
                        $(nRow).hide(200);
                    }
                });
            });
            return nRow;
        },
        "fnDrawCallback": drawcallback,
        "aaSorting": aaSorting,
        "fnInitComplete": function() {
            // Set hidden inputs for export icons
            if (undefined != $('#export_to_pdf')) {

            }
        }
    });

    $('#ajaxtable tbody td').hover( function() {
		var iCol = $('td').index(this) % 5;
		var nTrs = ajaxtable.fnGetNodes();
		$('td:nth-child('+(iCol+1)+')', nTrs).addClass( 'highlighted' );
	}, function() {
		var nTrs = ajaxtable.fnGetNodes();
		$('td.highlighted', nTrs).removeClass('highlighted');
	} );

    // Handle filters
    for (name in serverdata) {
        var eventtype = 'keyup'; // Text input

        if (serverdata[name] == 'select' || serverdata[name] == 'checkbox') { // dropdowns and checkboxes
            eventtype = 'change';
        }

        afterDelayedEvent(eventtype, '#'+name+'filter', function() { ajaxtable.fnDraw(); }, 500);
    }

    // Combo elements
    if(!undefined == serverdata['combo']) {
        afterDelayedEvent('keyup', '#combofilter', function() { alert('test') }, 500);
    }

    return ajaxtable;
}

function afterDelayedEvent(eventtype, selector, action, delay) {
    $(selector).bind(eventtype, function() {
        if (typeof(window['inputTimeout']) != "undefined") {
            clearTimeout(inputTimeout);
        }
        inputTimeout = setTimeout(action, delay);
    });
}

function has_capability(capname) {
    for (cap in caps) {
        if (capname == caps[cap] || caps[cap] == 'site:doanything') {
            return true;
        }
    }
    return false;
}

function nl2br (str, is_xhtml) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Philip Peterson
    // +   improved by: Onno Marsman
    // +   improved by: Atli Þór
    // +   bugfixed by: Onno Marsman
    // +      input by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)

    var breakTag = '';

    breakTag = '<br />';
    if (typeof is_xhtml != 'undefined' && !is_xhtml) {
        breakTag = '<br>';
    }

    return (str + '').replace(/([^>]?)\n/g, '$1'+ breakTag +'\n');
}

// HTML-BUILDING FUNCTIONS

function make_percentage_dropdown(name, value, order) {
    if (undefined === order) {
        order = 'asc';
    }

    var dropdown = document.createElement('select');
    $(dropdown).attr('name', name);
    $(dropdown).attr('id', name);

    if (order === 'asc') {
        for (var i = 0; i <= 100; i++) {
            $(dropdown).addOption(i, i+'%');
        }
    } else {
        for (var i = 100; i >= 0; i--) {
            $(dropdown).addOption(i, i+'%');
        }
    }

    $(dropdown).val(value);
    return dropdown;
}

function make_label(forwho, text) {
    var label = document.createElement('label');
    $(label).attr('for', forwho);
    $(label).text(text);
    return label;
}

/**
 * Creates a percentage dropdown with label and optional callback function
 * @param element target The DOM element to which the dropdown and label will be appended
 * @param string name The name attribute of the dropdown (also used as the ID attribute)
 * @param string value The value of the select (will determine the selected option)
 * @param string label The text of the label
 * @param function callback The optional callback function to use when an option is selected
 */
function add_percentage_dropdown(target, name, value, label, callback) {
    var dropdown = make_percentage_dropdown(name, value);
    if (undefined !== callback) {
        $(dropdown).bind('change', callback);
    }
    var label = make_label(name, label);
    $(label).addClass('dropdown');
    $(target).append(label);
    $(target).append(dropdown);
}

function add_dropdown(target, name, options, value, callback) {
    var $dropdown = $('<select>');
        $.each(options, function(value, text) {
            var $option = $('<option>', {
                value: value,
                text: text
            });
            if (value) {
                $dropdown.append($option);
            } else {
                $dropdown.prepend($option);
            }
        });
    if (undefined !== callback) {
        $dropdown.bind('change', callback);
    }
    $dropdown.attr({'name': name, 'id': name}).val(value);
    $(target).append($dropdown);
}

/**
 * Shortcut function for adding rows of header/cell to a table, using jQuery
 * @param element table
 * @param mixed headertext Can be a string or a DOM/jQuery element
 * @param mixed celltext Can be a string or a DOM/jQuery element. If none given, headertext will be output in a td with colspan=2
 * @param mixed headertext2 Can be a string or a DOM/jQuery element. If given, will be output on the same row as headertext/celltext
 * @param mixed celltext2 Can be a string or a DOM/jQuery element. If none given, headertext2 will be output in a td with colspan=2
 */
function add_data_row(table, headertext, celltext, headertext2, celltext2) {
    var row = document.createElement('tr');

    // If no celltext is given, print a cell with colspan=2
    if (undefined === celltext) {
        var cell = document.createElement('td');
        $(cell).append(headertext);
        $(cell).attr('colspan', 2);
        if ($('#'+table).attr('colspan') == 2 && !headertext2) {
            $(cell).attr('colspan', 4);
        }
    } else {
        header = document.createElement('th');
        $(header).append(headertext);
        var cell = document.createElement('td');
        $(cell).append(celltext);

        if ($('#'+table).attr('colspan') == 2 && !headertext2) {
            $(cell).attr('colspan', 3);
        }

        $(row).append(header);
    }

    $(row).append(cell);

    if (headertext2) {
        if (undefined === celltext2) {
            cell = document.createElement('td');
            $(cell).append(headertext2);
            $(cell).attr('colspan', 2);
        } else {
            header = document.createElement('th');
            $(header).append(headertext2);
            cell = document.createElement('td');
            $(cell).append(celltext2);
            $(row).append(header);
        }
        $(row).append(cell);
        $('#'+table).attr('colspan', 2);
    }

    $('#'+table).append(row);
}

/**
 * Function to create a checkbox or radio button
 */
function make_choice(id, name, value, type, currentvalue) {
    var choice = document.createElement('input');
    $(choice).attr('type', type);
    $(choice).attr('name', name);
    $(choice).attr('value', value);
    $(choice).attr('id', id);

    if (value == currentvalue) {
        $(choice).attr('checked', 'checked');
    }

    return choice;
}

function add_checkbox(target, id, name, value, label, currentvalue, callback) {
    var checkbox = make_choice(id, name, value, 'checkbox', currentvalue);
    var checkboxlabel = make_label(id, label);
    $(checkboxlabel).addClass('checkbox');
    $(target).append(checkbox);
    $(target).append(checkboxlabel);
    if (undefined !== callback) {
        $(checkbox).bind('click', callback);
    }
    if (value == currentvalue) {
        $(checkbox).attr('checked', 'checked');
    }
    return checkbox;
}

function add_radio(target, id, name, value, label, currentvalue, callback) {
    var radio = make_choice(id, name, value, 'radio', currentvalue);
    var radiolabel = make_label(id, label);
    $(radiolabel).addClass('radio');
    $(target).append(radio);
    $(target).append(radiolabel);
    if (undefined !== callback) {
        $(radio).bind('click', callback);
    }
    if (value == currentvalue) {
        $(radio).attr('checked', 'checked');
    }
    return radio;
}

function make_autocomplete_input(id, name, value, labeltext, ajaxurl, callback, unselectedcallback, inputclass, resultsclass) {
    var cache = {}, lastXhr;
    var input = make_text_input(id, name, value);
    var label = make_label(id, labeltext);

    $(input).autocomplete({
        minLength: 2,
        delay: 100,
        max: 50,
        open: function(event, ui) {
            menudisplay = true;
        },
        close: function(event, ui) {
            menudisplay = false;
        },
        select: callback,
        source: function (request, response) {
            var term = request.term;
            if (term in cache) {
                response(cache[term]);
                return;
            }

            lastXhr = $.post(ajaxurl, request, function(data, status, xhr) {
                cache[term] = data;
                if (xhr === lastXhr) {
                    response(data);
                }
            }, 'json');
        }
    });
    return {input: input, label: label};
}

function make_text_input(id, name, value) {
    var input = document.createElement('input');
    $(input).attr('id', id);
    $(input).attr('type', 'text');
    $(input).attr('name', name);
    $(input).attr('value', value);
    $(input).attr('size', 60);
    return input;
}

function make_action_icon(type, text, callback, classname) {
    var icon = document.createElement('img');
    $(icon).attr('src', 'images/admin/icons/'+type+'_16.gif');
    $(icon).attr('alt', text);
    $(icon).attr('title', text);
    if (classname) {
        $(icon).addClass(classname);
    } else {
        $(icon).addClass('icon');
    }
    $(icon).css('margin-top', '3px');
    $(icon).bind('click', callback);
    return icon;
}

function print_error(element, message) {
    errorspan = document.getElementById(element+'_error');

    if (isNull(errorspan)) {
        var errorspan = document.createElement('span');
        $(errorspan).addClass('error');
        $(errorspan).attr('id', element+'_error');
        $(errorspan).text(message);
        $('#'+element).after(errorspan);
    } else {
        $(errorspan).text(message);
    }
}

function print_edit_message(section, data) {
    data = $.evalJSON(data);
    print_message(data.message, data.type, section+'message');
}

function display_message(type, message) {
    $('#message').hide();
    $('#message span').attr('class', type);
    $('#message span').html(message);
    $('#message').show(200);
}

// Setup datepicker defaults
$.datepicker.setDefaults({
    dateFormat: 'dd/mm/yy'
});

// Function for revealing the contents of a password field
function reveal_password(name, checkbox) {
    var reveal = $(checkbox).attr('checked');

    var text_password_name = 'text_' + $(password_field).attr('name');
    var password_field = $('input[name='+name+']');

    if ($('input[name='+text_password_name+']').length == 0) {
        var text_password = document.createElement('input');
        $(text_password).attr('type', 'text');
        $(text_password).attr('name', text_password_name);
        $(text_password).attr('value', $(password_field).attr('value'));
        $(text_password).attr('size', $(password_field).attr('size'));
        $(text_password).attr('style', $(password_field).attr('style'));
        $(password_field).after(text_password);
    } else {
        var text_password = $('input[name='+text_password_name+']');
    }

    if (reveal) {
        $(text_password).attr('value', $(password_field).attr('value'));
        $(text_password).show();
        $(password_field).hide();
    } else {
        $(password_field).attr('value', $(text_password).attr('value'));
        $(text_password).hide();
        $(password_field).show();
    }
}
