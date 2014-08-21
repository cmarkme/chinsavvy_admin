/*
 Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 For licensing, see LICENSE.html or http://ckeditor.com/license
*/
CKEDITOR.plugins.add("signature", {
    init: function(editor) {
        editor.addCommand('toggleSignature', {
            exec: function (editor) {
                var signature = editor.config.signature;
                var editor_html = editor.getData();
                if (this.state == CKEDITOR.TRISTATE_ON) {
                    var split = editor_html.split('\-\-\-\-\-\-\-\-\-\-\-\-\-\-\-'); // Windows carriage returns
                    editor.setData(split[0]);
                } else {
                    editor.setData(editor_html + signature);
                }
                this.toggleState();
            }
        });

        editor.ui.addButton('Signature', {
            label: 'Include signature',
            command: 'toggleSignature',
            icon: this.path + 'images/signature.png'
        });
    }
});

