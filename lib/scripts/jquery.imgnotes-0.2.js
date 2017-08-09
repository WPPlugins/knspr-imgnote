/**
 * imgnotes jQuery plugin
 * version 0.1
 *
 * Copyright (c) 2008 Dr. Tarique Sani <tarique@sanisoft.com>
 *
 * Dual licensed under the MIT (MIT-LICENSE.txt) 
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * @URL      http://www.sanisoft.com/blog/2008/05/26/img-notes-jquery-plugin/
 * @Example  example.html
 *
 **/

//Wrap in a closure
(function($) {

    $.fn.imgNotes = function(notes, imageId, isAdmin, alwaysShow, width, height) {

        if(typeof(noteCache) == "undefined") {
            noteCache = [];
        }
	
        noteCache[noteCache.length] = {
            'image': this,
            'notes': notes,
            'imageId': imageId,
            'isAdmin' : isAdmin,
            'alwaysShow': alwaysShow,
	    'width': width,
	    'height': height
        };
	

        addNotes(this, notes, imageId, isAdmin, alwaysShow, width, height)
        addNoteEvents(imageId);

        $(window).bind('resize', function(ev) {
            removeNotes();
            $.each(noteCache, function(imageId, obj) {
                addNotes(obj.image, obj.notes, imageId, obj.isAdmin, obj.alwaysShow, obj.width, obj.height);
                addNoteEvents(imageId);
            });
        })
    }

    function addNotes(image, notes, imageId, isAdmin, alwaysShow, width, height) {
        $(notes).each(function(i, a){
            appendNote(image, a, i, imageId, isAdmin, width, height);
        });

        if(alwaysShow == false) {
            $(image).hover(
                function(){
                    $('.note-' + imageId).show();
                },
                function(){
                    $('.note-' + imageId).hide();
                }
                );
        } else {
            $('.note-' + imageId).show();
        }
    }

    function removeNotes() {
        $('.notep').remove();
        $('.note').remove();
    }

    function appendNote(image, note_data, i, imageId, isAdmin, width, height){
        pure_element = $(image)[0];
	
	width_multi = $(image).width()/width;
	height_multi = $(image).height()/height;
	
        note_left  = parseInt(pure_element.offsetLeft) + (parseInt(note_data.x1) * width_multi);
        note_top   = parseInt(pure_element.offsetTop) + (parseInt(note_data.y1) * height_multi);
		
        note_p_top = note_top + parseInt(note_data.height)+5;
	
	note_width = note_data.width * width_multi;
	note_height = note_data.height * height_multi;

        note_inner_div = $('<div class="note-inner"></div>').css({
            width: (note_width-2) + 'px',
            height: (note_height-2) + 'px'
        });
	
	if(note_data.url == '' || isAdmin) {
		note_area_div = $('<div class="note note-' + imageId +'" id="imageNote-' + i + '"></div>');
	}
	else {
		note_area_div = $('<a href="' + note_data.url + '" class="note note-' + imageId +'" id="imageNote-' + i + '"></a>').addClass('note-url');
	}
	
	note_area_div.css({
		    left: note_left + 'px',
		    top: note_top + 'px',
		    width: note_width + 'px',
		    height: note_height + 'px'
		}).append(note_inner_div);

        note_text_div = $('<div class="notep" >'+note_data.note+'</div>').css({
            left: note_left + 'px',
            top: note_p_top + 'px'
        });
	
	

        if(isAdmin == true) {
            note_edit_div = $('<div class="noteEditPanel" id="noteEditPanel-' + imageId +'"></div>');

            note_edit_div.append($('<p>del</p>').bind('click', function(ev) {
                jQuery('#imageNote-' + i).animate({
                    borderColor: '#ff0000'
                }, 500);
                
                knsprImageNote.deleteNote(imageId, i, function(data) {
                    knsprImageNote.loadImage(imageId)
                });
            }));


            note_edit_div.append($('<p>edit</p>').bind('click', function(ev) {
                knsprImageNote.afterSave = function() {
                    knsprImageNote.deleteNote(imageId, i, function(data) {
                        knsprImageNote.loadImage(imageId)
                    });
                };

                knsprImageNote.noteText = note_data.note;
                knsprImageNote.noteUrl = note_data.url;

                knsprImageNote.editNote(image, note_data);
            }));

            note_inner_div.append(note_edit_div);
        }
	
        $(image).parent().append(note_area_div);
        $(image).parent().append(note_text_div);
    }

    function addNoteEvents(imageId) {
        $('.note-' + imageId).hover(
            function() {
                $('.note-' + imageId).show();
                $(this).next('.notep').show();
                $(this).next('.notep').css('z-index', 10000);
            },

            function() {
                $('.note-' + imageId).show();
                $(this).next('.notep').hide();
                $(this).next('.notep').css('z-index', 0);
            }
            );
    }


// End the closure
})(jQuery);