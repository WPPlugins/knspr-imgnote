<?php

/*
Plugin Name: Knspr-ImgNote
Plugin URI: http://knuspermagier.de
Description:  Put flickr-like notes on images.
Author: knuspermagier
Version: 1.0-rc8
Author URI: http://knuspermagier.de
*/

/*
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

define('KNSPR_FEED_MESSAGE', '<p>This image contains notes, please open the website directly</p>');
$noteShowNoteCount = true;
$noteTheme = 'default';

function vdbg($v) {
    echo '<pre>';
    var_dump($v);
    echo '</pre>';
}

require_once('knspr-findpaths.php');
require_once('lib/knspr-imgnote.php');
require_once('KnsprNoteWordpressSaveStrategy.php');

$ajaxUrl = WP_PLUGIN_URL . '/knspr-imgnote/knspr-imgnote-ajax.php';

function knspr_add_header_script() {
    global $noteTheme;

    wp_enqueue_script('jquery');
    wp_enqueue_script('imgnotes', WP_PLUGIN_URL .'/knspr-imgnote/lib/scripts/jquery.imgnotes-0.2.js', array('jquery'));
    wp_enqueue_script('knsprimgnotes',WP_PLUGIN_URL .'/knspr-imgnote/lib/scripts/knspr-imageNote.js', array('jquery', 'imgnotes'));

    if(is_admin()) {
        wp_enqueue_script('imgareaselect', WP_PLUGIN_URL . '/lib/scripts/jquery.imgareaselect.js', array('jquery'));
    }
}

function knspr_add_header_styles() {
    global $noteTheme;

    if(is_admin()) {
        echo '<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/knspr-imgnote/lib/css/knspr-imagenote.css" />';
        echo '<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/knspr-imgnote/lib/css/imgareaselect-animated.css" />';
    }

    echo '<link rel="stylesheet" type="text/css" href="'. WP_PLUGIN_URL .'/knspr-imgnote/themes/' . $noteTheme .'/imgnotes.css" />';
}

function knspr_add_image_choose_thingy() {
    global $post, $ajaxUrl;
    
    if(function_exists('add_meta_box')) {
	add_meta_box('knsprImgNote', 'Edit image notes', 'knspr_create_image_chooser', 'post');
	add_meta_box('knsprImgNote', 'Edit image notes', 'knspr_create_image_chooser', 'page');
    } else {
	add_action('dbx_post_advanced', 'knspr_create_image_chooser_with_box');
	add_action('dbx_page_advanced', 'knspr_create_image_chooser_with_box');
    }

}

function knspr_create_image_chooser() {
    global $post, $ajaxUrl;

    $blogUrl = WP_PLUGIN_URL;
    
echo <<<EOF
			<script type="text/javascript">
            knsprImageNote.ajaxPostUrl = '$ajaxUrl';
            knsprImageNote.loadThumbs({$post->ID});
	</script>
	
		
		<p class="reload-thumbs-link"><a href="javascript:void(0);"  onclick="knsprImageNote.loadThumbs({$post->ID});"><img src="$blogUrl/knspr-imgnote/lib/css/reload.png" alt="reload" title="Refresh thumbnails"/> Reload thumbnails</a></p><div id="imageThumbs"></div>
			
		<div id="imgNoteAjaxResponse"></div>
		<img src="$blogUrl/knspr-imgnote/lib/css/spinner.gif" alt="loading" id="ajaxSpinner" style="display: none;"/>

EOF;

}

function knspr_create_image_chooser_with_box() {
    global $post, $ajaxUrl;

    $blogUrl = get_bloginfo('siteurl');
    
echo <<<EOF

	<div id="imageNoteManager" class="postbox">
		<h3 class="hndle"><span>Edit image notes</span></h3>
		<div class="inside">
EOF;

	knspr_create_image_chooser();

echo <<<EOF
		
		</div>
	</div>
EOF;
}

function knspr_get_frontend_code(KnsprNoteManager $manager, $imageId) {
    global $noteShowNoteCount;
    
    $json =  $manager->printNoteJson(true);
    $notes = $manager->getCount();

    if($notes == 0) {
        return;
    }
    
    $content = '';


    if(preg_match('/iphone/i', $_SERVER['HTTP_USER_AGENT'])) {
        $content .= <<<EOF
<p class="note-iphonebutton"><a href="javascript:void(0)" onclick="knsprImageNote.showAllNotes($imageId);">Show all notes</a></p>
EOF;
    }
    
    if($noteShowNoteCount == true) {
        $content .= <<<EOF
	<p class="numberOfNotes">Notes: $notes</p>
EOF;
    }
    
    
    $imgData = wp_get_attachment_image_src($imageId, 'full');
    
    $content .= <<<EOF
        <script type="text/javascript">
jQuery(function() {
    knsprImageNote.initializeNoteDisplay('.wp-image-$imageId', $json, $imageId, false, false, $imgData[1], $imgData[2]);
});
</script>
EOF;

    return $content;
}

function knspr_user_view_thingy($content) {
    preg_match_all('/wp-image-([0-9]+)/i', $content, $matches);
    $count = count($matches[0]);

    $manager = new KnsprNoteManager();

    for($i = 0; $i < $count; $i++) {
        $manager->setStrategy(new KnsprNoteWordpressSaveStrategy($matches[1][$i]));

        if(is_feed()) {
            if($manager->getCount() != 0) {
		$content = knspr_find_image_and_append_text('wp-image-' . $matches[1][$i], $content, KNSPR_FEED_MESSAGE);
            }
        }

	$content = knspr_find_image_and_append_text('wp-image-' . $matches[1][$i], $content, knspr_get_frontend_code($manager, $matches[1][$i]));
    }

    return $content;

}

function knspr_find_image_and_append_text($class, $content, $text) {
	$pos = strpos($content, $class);
	
	if($pos === false) {
		return $content;
	}
	
	for($i = $pos; $i < strlen($content); $i++) {
		if($content[$i] == '>') {
			return substr($content, 0, $i+1) . $text . substr($content, $i+1);
		}
	}
}

add_filter('the_content', 'knspr_user_view_thingy');

add_action('init', 'knspr_add_header_script');
add_action('wp_print_styles', 'knspr_add_header_styles');
add_action('admin_print_styles', 'knspr_add_header_styles');

add_action('admin_menu', 'knspr_add_image_choose_thingy');
