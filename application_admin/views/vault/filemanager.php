<div id="container">
  <div id="menu">
    <a id="create" class="button" style="background-image: url('images/filemanager/folder.png');"><?php echo $button_folder; ?></a>
    <a id="delete" class="button" style="background-image: url('images/filemanager/edit-delete.png');"><?php echo $button_delete; ?></a>
    <a id="move" class="button" style="background-image: url('images/filemanager/edit-cut.png');"><?php echo $button_move; ?></a>
    <a id="copy" class="button" style="background-image: url('images/filemanager/edit-copy.png');"><?php echo $button_copy; ?></a>
    <a id="rename" class="button" style="background-image: url('images/filemanager/edit-rename.png');"><?php echo $button_rename; ?></a>
    <a id="upload" class="button" style="background-image: url('images/filemanager/upload.png');"><?php echo $button_upload; ?></a>
    <a id="download" class="button" style="background-image: url('images/admin/icons/compress.png');"><?php echo $button_download; ?></a>
    <a id="refresh" class="button" style="background-image: url('images/filemanager/refresh.png');"><?php echo $button_refresh; ?></a>
  </div>
  <div id="column-left" ></div>
  <div id="column-right">
    </div>
</div>
<script type="text/javascript">
/*<![CDATA[ */
function add() {
    window.location = '/vault/filemanager/add';
}

function build_files_table(html) {

    $('#column-right').html(html);

    $('#ajaxtable').dataTable({
        bPaginate: false,
        bScrollInfinite: true,
        bStateSave: false, // Set to true on production site
        bInfo: false,
        bJQueryUI: true,
        sDom: 'W<"clear">lfrtip',
        aoColumnDefs: [
            { bVisible: false, aTargets: [ 'file_size', 'revision_date' ] },
            { iDataSort: 1, aTargets: [ 'file_size_human' ] },
            { iDataSort: 6, aTargets: [ 'revision_date_human' ] }
            ],
        oColumnFilterWidgets: {
            aiExclude: [ 1, 2, 3, 6, 7 ],
            sSeparator: ',  ',
            bGroupTerms: true,
            aoColumnDefs: [
                { bSort: false, sSeparator: ' / ', aiTargets: [ 2 ] },
                { fnSort: function( a, b ) { return a-b; }, aiTargets: [ 3 ] }
            ]
        }
    });

    $('#ajaxtable tbody').selectable({
        filter: 'tr',
        selected: function(event, ui) {
            // Toggle action buttons on/off
        }
    });

    $('.file_version_select').bind('change', function() {
        var file_version_id = $(this).val();
        var file_id = $(this).parent().parent().attr('file_id');
        var this_select = this;

        $('tr[file_id='+file_id+']').each(function() {
            if (file_version_id != $(this).attr('file_version_id')) {
                // Reset the selected option to this file's true version (don't leave it to the new selected version)
                $(this_select).val($(this_select).parent().parent().attr('file_version_id'));
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });

    $('#column-right').trigger('scrollstop');
}

$(document).ready(function() {

	(function(){
		var special = jQuery.event.special,
			uid1 = 'D' + (+new Date()),
			uid2 = 'D' + (+new Date() + 1);

		special.scrollstart = {
			setup: function() {
				var timer,
					handler =  function(evt) {
						var _self = this,
							_args = arguments;

						if (timer) {
							clearTimeout(timer);
						} else {
							evt.type = 'scrollstart';
							jQuery.event.handle.apply(_self, _args);
						}

						timer = setTimeout( function(){
							timer = null;
						}, special.scrollstop.latency);

					};

				jQuery(this).bind('scroll', handler).data(uid1, handler);
			},
			teardown: function(){
				jQuery(this).unbind( 'scroll', jQuery(this).data(uid1) );
			}
		};

		special.scrollstop = {
			latency: 300,
			setup: function() {

				var timer,
						handler = function(evt) {

						var _self = this,
							_args = arguments;

						if (timer) {
							clearTimeout(timer);
						}

						timer = setTimeout( function(){

							timer = null;
							evt.type = 'scrollstop';
							jQuery.event.handle.apply(_self, _args);

						}, special.scrollstop.latency);

					};

				jQuery(this).bind('scroll', handler).data(uid2, handler);

			},
			teardown: function() {
				jQuery(this).unbind('scroll', jQuery(this).data(uid2));
			}
		};
	})();

    function load_foldertree() {
        return $('#column-left').tree({
            dataUrl: "vault/filemanager/directory",
            autoOpen: true,
            selectable: true,
            onCreateLi: function(node, $li) {
                $li.attr('id', node.name);
            },
        }).bind('tree.select', function(event) {
            var li = event.target;

            $.ajax({
                url: 'vault/filemanager/files',
                type: 'post',
                data: 'directory=' + encodeURIComponent($(li).attr('id')),
                dataType: 'html',
                success: build_files_table,
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        });
    }

    /*
    $('#column-left').jstree({
        plugins : ["themes","json_data","ui","crrm"],
		json_data: {
            ajax: {
                url: 'vault/filemanager/directory',
                method: 'post',
                data: function(n) {
                    return {
                        title: 'Blah',
                        attr: {
                            id: '3434'
                        }
                    }
                }
            }
        }
    }).bind('loaded.jstree', function(event, data) {

    }).bind('select_node.jstree', function(event, data) {
        $.ajax({
            url: 'vault/filemanager/files',
            type: 'post',
            data: 'directory=' + encodeURIComponent($(NODE).attr('directory')),
            dataType: 'html',
            success: build_files_table,
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    });
    */
    $foldertree = load_foldertree();
    $('#ajaxtable').selectable();
/*
	$('#column-right a').live('click', function() {
		if ($(this).attr('class') == 'selected') {
			$(this).removeAttr('class');
		} else {
			$('#column-right a').removeAttr('class');

			$(this).attr('class', 'selected');
		}
	});
 */

	$('#column-right a').live('dblclick', function() {
		<?php if ($fckeditor) { ?>
		window.opener.CKEDITOR.tools.callFunction(<?php echo $fckeditor; ?>, '<?php echo $directory; ?>' + $(this).find('input[name=\'document\']').attr('value'));

		self.close();
		<?php } else { ?>
		parent.$('#<?php echo $field; ?>').attr('value', 'data/' + $(this).find('input[name=\'document\']').attr('value'));
		parent.$('#dialog').dialog('close');

		parent.$('#dialog').remove();
		<?php } ?>
	});

	$('#create').bind('click', function() {

		if ($foldertree.tree('getSelectedNode') || true) { // If we allow nested folders, remove || true
			$('#dialog').remove();

			html  = '<div id="dialog">';
			html += '<?php echo $entry_folder; ?> <input type="text" name="name" value="" /> <input type="button" value="<?php echo $button_submit; ?>" />';
			html += '</div>';

			$('#column-right').prepend(html);

			$('#dialog').dialog({
				title: '<?php echo $button_folder; ?>',
				resizable: false
			});

			$('#dialog input[type=\'button\']').bind('click', function() {
				$.ajax({
					url: 'vault/filemanager/create',
					type: 'post',
					data: 'directory=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();

                            load_foldertree();

							alert(json.success);
						} else {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			});
		} else {
			alert('<?php echo $error_directory; ?>');
		}
	});

	$('#delete').bind('click', function() {
		path = $('#column-right a.selected').find('input[name=\'document\']').attr('value');

		if (path) {
			$.ajax({
				url: 'vault/filemanager/delete',
				type: 'post',
				data: 'path=' + encodeURIComponent(path),
				dataType: 'json',
				success: function(json) {
					if (json.success) {
						var tree = $.tree.focused();

						tree.select_branch(tree.selected);

						alert(json.success);
					}

					if (json.error) {
						alert(json.error);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		} else {
			var tree = $.tree.focused();

			if (tree.selected) {
				$.ajax({
					url: 'vault/filemanager/delete',
					type: 'post',
					data: 'path=' + encodeURIComponent($(tree.selected).attr('directory')),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							tree.select_branch(tree.parent(tree.selected));

							tree.refresh(tree.selected);

							alert(json.success);
						}

						if (json.error) {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			} else {
				alert('<?php echo $error_select; ?>');
			}
		}
	});

	$('#move').bind('click', function() {
		$('#dialog').remove();

		html  = '<div id="dialog">';
		html += '<?php echo $entry_move; ?> <select name="to"></select> <input type="button" value="<?php echo $button_submit; ?>" />';
		html += '</div>';

		$('#column-right').prepend(html);

		$('#dialog').dialog({
			title: '<?php echo $button_move; ?>',
			resizable: false
		});

		$('#dialog select[name=\'to\']').load('vault/filemanager/folders');

		$('#dialog input[type=\'button\']').bind('click', function() {
			path = $('#column-right a.selected').find('input[name=\'document\']').attr('value');

			if (path) {
				$.ajax({
					url: 'vault/filemanager/move',
					type: 'post',
					data: 'from=' + encodeURIComponent(path) + '&to=' + encodeURIComponent($('#dialog select[name=\'to\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();

							var tree = $.tree.focused();

							tree.select_branch(tree.selected);

							alert(json.success);
						}

						if (json.error) {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			} else {
				var tree = $.tree.focused();

				$.ajax({
					url: 'vault/filemanager/move',
					type: 'post',
					data: 'from=' + encodeURIComponent($(tree.selected).attr('directory')) + '&to=' + encodeURIComponent($('#dialog select[name=\'to\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();

							tree.select_branch('#top');

							tree.refresh(tree.selected);

							alert(json.success);
						}

						if (json.error) {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});

	$('#copy').bind('click', function() {
		$('#dialog').remove();

		html  = '<div id="dialog">';
		html += '<?php echo $entry_copy; ?> <input type="text" name="name" value="" /> <input type="button" value="<?php echo $button_submit; ?>" />';
		html += '</div>';

		$('#column-right').prepend(html);

		$('#dialog').dialog({
			title: '<?php echo $button_copy; ?>',
			resizable: false
		});

		$('#dialog select[name=\'to\']').load('vault/filemanager/folders');

		$('#dialog input[type=\'button\']').bind('click', function() {
			path = $('#column-right a.selected').find('input[name=\'document\']').attr('value');

			if (path) {
				$.ajax({
					url: 'vault/filemanager/copy',
					type: 'post',
					data: 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();

							var tree = $.tree.focused();

							tree.select_branch(tree.selected);

							alert(json.success);
						}

						if (json.error) {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			} else {
				var tree = $.tree.focused();

				$.ajax({
					url: 'vault/filemanager/copy',
					type: 'post',
					data: 'path=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();

							tree.select_branch(tree.parent(tree.selected));

							tree.refresh(tree.selected);

							alert(json.success);
						}

						if (json.error) {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});

    // Through AJAX, load the vault/fileinfo template
    function show_fileinfo_form() {
        $.ajax({
            url: 'vault/filemanager/fileinfo',
            type: 'post',
            dataType: 'html',
            success: function(html) {
                $('.plupload_header_title').html('Extra info')
                $('.plupload_header_text').html('Add info to uploaded file(s)')
                $('#uploader_container div.plupload_content').fadeOut(600);
                window.setTimeout(function() {
                    $('#uploader_container div.plupload_content').html(html);
                    $('#uploader_container div.plupload_content').fadeIn(600);

                    $('.type select').bind('change', function() {
                        var row_id = $(this).attr('name').match(/type\[([0-9]*)\]/)[1];
                        if ($(this).val() == constants.VAULT_FILE_TYPE_ORDER) {
                            $('input[name="enquiry_id['+row_id+']"]').attr('disabled', 'disabled');
                            $('input[name="customer_id['+row_id+']"]').removeAttr('disabled').focus();
                        } else if ($(this).val() == constants.VAULT_FILE_TYPE_ENQUIRY) {
                            $('input[name="customer_id['+row_id+']"]').attr('disabled', 'disabled');
                            $('input[name="enquiry_id['+row_id+']"]').removeAttr('disabled').focus();
                        }
                    });
                }, 600);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    $('#upload').bind('click', function() {
		var tree = $.tree.focused();
        $('#dialog').remove();

        var cancel_uploader = document.createElement('a');
        $(cancel_uploader).addClass('plupload_button');
        $(cancel_uploader).addClass('plupload_cancel');
        $(cancel_uploader).text('Cancel');
        $(cancel_uploader).bind('click', function() {
            $('#uploader').fadeToggle(1000, 'swing');
        });

        $("#uploader").pluploadQueue({
            // General settings
            runtimes : 'browserplus,html5',
            url : '/vault/filemanager/upload/'+encodeURIComponent($(tree.selected).attr('directory')),
            max_file_size : '2000mb',
            chunk_size : '1mb',
            unique_names : false,
            dragdrop: true,

            // Resize images on clientside if we can
            resize : {width : 320, height : 240, quality : 90},

            // Specify what files to browse for
            filters : [
                {title : "All allowed files", extensions :'pdf,doc,xls,gif,jpg,png,psd,csv,txt,sql,zip,dwg,PDF,DOC,XLS,GIF,JPG,PNG,PSD,CSV,TXT,SQL,ZIP,DWG'},
                {title : "Image files", extensions : "jpg,gif,png"},
                {title : "Zip files", extensions : "zip"},
                {title : "PDF files", extensions : "pdf"},
            ],

            // Flash settings
            flash_swf_url : '/includes/js/plupload/plupload.flash.swf',

            // Silverlight settings
            silverlight_xap_url : '/includes/js/plupload/plupload.silverlight.xap',
            preinit: {
                Init: function(up, info) {
                }
            },

            init : {
                StateChanged: function(up) {
                    if (up.state == plupload.STOPPED) {
                        $('.plupload_buttons').css('display', 'inline');
                        $('#upload_browse').hide();
                        $('.plupload_button').hide();
                        var addinfo_uploader = document.createElement('a');
                        $(addinfo_uploader).addClass('plupload_button');
                        $(addinfo_uploader).addClass('plupload_addinfo');
                        $(addinfo_uploader).text('Add file info');
                        $(addinfo_uploader).bind('click', show_fileinfo_form);
                        $('.plupload_buttons').append(addinfo_uploader);
                    }
                },
                Refresh: function(up) {
                    $('.plupload_buttons').append(cancel_uploader);
                }
            }
        });

        // Client side form validation
        $('form').submit(function(e) {
            var uploader = $('#uploader').pluploadQueue();
            // Files in queue upload them first
            if (uploader.files.length > 0) {
                // When all files are uploaded submit form
                uploader.bind('StateChanged', function() {
                    if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                        $('form')[0].submit();
                    }
                });

                uploader.start();
            } else {
                alert('You must queue at least one file.');
            }

            return false;
        });
        $('#uploader').fadeToggle(1000, 'swing');

    });

    $('.plupload_cancel').bind('click', function(event) {
        $('#uploader').fadeToggle(1000, 'swing');
        event.preventDefault();
    });

	$('#rename').bind('click', function() {
		$('#dialog').remove();

		html  = '<div id="dialog">';
		html += '<?php echo $entry_rename; ?> <input type="text" name="name" value="" /> <input type="button" value="<?php echo $button_submit; ?>" />';
		html += '</div>';

		$('#column-right').prepend(html);

		$('#dialog').dialog({
			title: '<?php echo $button_rename; ?>',
			resizable: false
		});

		$('#dialog input[type=\'button\']').bind('click', function() {
			path = $('#column-right a.selected').find('input[name=\'document\']').attr('value');

			if (path) {
				$.ajax({
					url: 'vault/filemanager/rename',
					type: 'post',
					data: 'path=' + encodeURIComponent(path) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();

							var tree = $.tree.focused();

							tree.select_branch(tree.selected);

							alert(json.success);
						}

						if (json.error) {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			} else {
				var tree = $.tree.focused();

				$.ajax({
					url: 'vault/filemanager/rename',
					type: 'post',
					data: 'path=' + encodeURIComponent($(tree.selected).attr('directory')) + '&name=' + encodeURIComponent($('#dialog input[name=\'name\']').val()),
					dataType: 'json',
					success: function(json) {
						if (json.success) {
							$('#dialog').remove();

							tree.select_branch(tree.parent(tree.selected));

							tree.refresh(tree.selected);

							alert(json.success);
						}

						if (json.error) {
							alert(json.error);
						}
					},
					error: function(xhr, ajaxOptions, thrownError) {
						alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		});
	});

    $('#refresh').bind('click', function() {
        var tree = $.tree.focused();

        tree.refresh(tree.selected);
    });

});
//]]>
</script>
</div>
</div>
<div id="uploader" style="display:none; height: 330px;"> </div>
<script type="text/javascript"><!--
//--></script>
</body>
</html>

