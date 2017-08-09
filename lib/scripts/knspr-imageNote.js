
var knsprImageNote = {}

knsprImageNote.ajaxPostUrl = '';
knsprImageNote.ajaxImageId = 0;

knsprImageNote.afterSave = null;
knsprImageNote.noteText = '';
knsprImageNote.noteUrl = '';

knsprImageNote.noteTextButtonId = 'imageNoteButton';
knsprImageNote.noteTextContainerId = 'imageNoteInput';
knsprImageNote.noteTextInputFieldId = 'imageNoteText';
knsprImageNote.noteUrlInputFieldId = 'imageNoteUrl';
knsprImageNote.noteUrlDefault = 'URL (optional)';

knsprImageNote.noteTextInputField = '<form class="image-note-input" id="' + knsprImageNote.noteTextContainerId + '">' +
    '<label for="' + knsprImageNote.noteTextInputFieldId + '">Text for this note</label>' +
    '<textarea name="' + knsprImageNote.noteTextInputFieldId +'" id="' + knsprImageNote.noteTextInputFieldId + '"></textarea>' +
    '<input type="text" id="' + knsprImageNote.noteUrlInputFieldId + '" value="' + knsprImageNote.noteUrlDefault +'" onfocus="if(this.value == \'' + knsprImageNote.noteUrlDefault + '\') this.value = \'http://\';"  />' +
    '<input type="button" id="' +  knsprImageNote.noteTextButtonId + '" value="Save note" class="button" />' +
    '</form>'

knsprImageNote.lastImage = null;

knsprImageNote.initializeAnnotator = function(selector) {
    jQuery(selector).imgAreaSelect({
        handles: true,
        show: true,
        enable: true,
        onSelectEnd: knsprImageNote.showAddNote
    });

    knsprImageNote.lastImage = jQuery(selector);
}

knsprImageNote.initializeNoteDisplay = function(selector, json, imageId, isAdmin, alwaysShow, width, height) {
    jQuery(selector).imgNotes(json, imageId, isAdmin, alwaysShow, width, height);
}

knsprImageNote.clearNoteDisplay = function(imageId) {
    jQuery('.note-' + imageId).next('.notep').remove();
    jQuery('.note-' + imageId).remove();
}


// Show the input box
knsprImageNote.showAddNote = function(img, selection) {
    // remove old input boxes
    jQuery('.image-note-input').remove();

    // put a new input box in
    jQuery(img).after(knsprImageNote.noteTextInputField);

    jQuery('#' + knsprImageNote.noteTextButtonId).bind('click', function(ev) {
        // save the note
        knsprImageNote.saveNote(knsprImageNote.ajaxImageId, selection, function(data) {

            if(knsprImageNote.afterSave != null) {
                eval('knsprImageNote.afterSave()');
            }

            // disable the select
            jQuery('#' + jQuery(img).attr('id')).imgAreaSelect({
                hide:true,
                disable:true
            });

            // remove box
            jQuery('.image-note-input').remove();
        });
    });

    pos = jQuery(img).position();
    jQuery('#' + knsprImageNote.noteTextContainerId).css('position', 'absolute')
    .css('top', pos.top + selection.y1)
    .css('left', pos.left + selection.x2)
    .css('z-index', 1000);

    jQuery('#' + knsprImageNote.noteTextInputFieldId).unbind('keyup').bind('keyup', function(ev) {
        knsprImageNote.noteText = jQuery('#' + knsprImageNote.noteTextInputFieldId).val();
    }).html(knsprImageNote.noteText).focus();

    if(knsprImageNote.noteUrl != '') {
        jQuery('#' + knsprImageNote.noteUrlInputFieldId).val(knsprImageNote.noteUrl);
    } else {
        jQuery('#' + knsprImageNote.noteUrlInputFieldId).val(knsprImageNote.noteUrlDefault);
    }
}

knsprImageNote.editNote = function(image, data) {
    knsprImageNote.clearNoteDisplay(data.imageId);
    knsprImageNote.ajaxImageId = data.imageId;
    
    jQuery(image).imgAreaSelect({
        handles: true,
        show: true,
        enable: true,
        x1: data.x1,
        x2: data.x1 + data.width,
        y1: data.y1,
        y2: data.y1 + data.height,
        onSelectEnd: knsprImageNote.showAddNote
    });

    knsprImageNote.showAddNote(image, {
        x1: data.x1,
        x2: data.x1 + data.width,
        y1: data.y1,
        y2: data.y1 + data.height,
        width: data.width,
        height: data.height
    });
}

knsprImageNote.clearNotes = function(imageId, callback) {
    jQuery.post(knsprImageNote.ajaxPostUrl, {
        action: 'clear',
        imageId: imageId
    }, callback);
}

knsprImageNote.showAllNotes = function(imageId) {
    jQuery('.note-' + imageId).show().next('.notep').show();
}

knsprImageNote.loadThumbs = function(postId) {
    jQuery('#ajaxSpinner').show();
    jQuery.post(knsprImageNote.ajaxPostUrl, {
        action: 'loadThumbs',
        postId: postId
    }, function(data) {
        jQuery('#imageThumbs').html(data)
        jQuery('#ajaxSpinner').hide();
    });
}

knsprImageNote.loadImage = function(imageId) {
    if(knsprImageNote.lastImage != null) {
        knsprImageNote.lastImage.imgAreaSelect({
            hide:true,
            disable:true
        });
    }

    jQuery('#ajaxSpinner').show();
    jQuery.post(knsprImageNote.ajaxPostUrl, {
        action: 'noteEditor',
        imageId: imageId
    }, function(data) {
        jQuery('#imgNoteAjaxResponse').html(data)
        jQuery('#ajaxSpinner').hide();
    });
}

knsprImageNote.saveNote = function(imageId, selection, callback) {
    url =  jQuery('#' + knsprImageNote.noteUrlInputFieldId).val();

    if(url == knsprImageNote.noteUrlDefault) {
        url = '';
    }

    jQuery.post(knsprImageNote.ajaxPostUrl, {
        action: 'addNote',
        imageId: imageId,
        x: selection.x1,
        y: selection.y1,
        width: selection.width,
        height: selection.height,
        text: escape(jQuery('#' + knsprImageNote.noteTextInputFieldId).val()),
        url: url
    }, callback);
}

knsprImageNote.deleteNote = function(imageId, noteId, callback) {
    jQuery.post(knsprImageNote.ajaxPostUrl, {
        action: 'deleteNote',
        imageId: imageId,
        noteId: noteId
    }, callback);
}