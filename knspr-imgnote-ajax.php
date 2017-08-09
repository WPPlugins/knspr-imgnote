<?php

define('DOING_AJAX', true);
define('WP_ADMIN', true);

require_once('../../../wp-load.php');
require_once('../../../wp-admin/includes/admin.php');
@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

if(!current_user_can('edit_posts')) {
	die('You have insufficient rights to edit posts. (Or something else is terribly wrong)');
}

require_once('knspr-findpaths.php');
require_once('lib/knspr-imgnote.php');
require_once('KnsprNoteWordpressSaveStrategy.php');

$ajaxUrl = WP_PLUGIN_URL . '/knspr-imgnote/knspr-imgnote-ajax.php';

$action = isset($_POST['action']) ? $_POST['action'] : '';
$attachId = isset($_POST['imageId']) ? intval($_POST['imageId']) : 0;
$postId  = isset($_POST['postId']) ? intval($_POST['postId']) : 0;

$manager = new KnsprNoteManager();
$manager->setStrategy(new KnsprNoteWordpressSaveStrategy($attachId));

if($action == 'noteEditor') {
    $image = wp_get_attachment_url($attachId);

    $image = wp_get_attachment_image_src($attachId, 'full');
    
    $json = $manager->printNoteJson(true);
    $noteCount = $manager->getCount();

    echo <<<EOF
    <script type="text/javascript">
        knsprImageNote.initializeNoteDisplay('#imgNotebigImage', $json, $attachId, true, true, $image[1], $image[2]);
    </script>

    <ul class="imgNote-controls">
    <li><strong>Controls</strong>:</li>
    <li><a href="javascript:void(0)" class="button" onclick="knsprImageNote.initializeAnnotator('#imgNotebigImage'); knsprImageNote.ajaxPostUrl = '$ajaxUrl'; knsprImageNote.ajaxImageId = '$attachId'; knsprImageNote.noteText = '';knsprImageNote.noteUrl = ''; knsprImageNote.afterSave = function() { knsprImageNote.loadImage('$attachId') }">Add a note</a></li>
    <li><a href="javascript:void(0)" class="button" onclick="if(confirm('Really delete all notes on this image?')) { knsprImageNote.ajaxPostUrl = '$ajaxUrl';knsprImageNote.ajaxImageId = '$attachId'; knsprImageNote.clearNotes($attachId, function() { knsprImageNote.loadImage('$attachId') }) }">Clear all notes</a></li>
    <li>Notes: $noteCount</li>
</ul>
EOF;

    echo '<div class="imgNote-image" style="background-image: url(\''.  $image[0] .'\'); width: '. $image[1] .'px; height: '. $image[2] .'px;"  id="imgNotebigImage"></div>';
    return;
} elseif($action == 'addNote') {
    $manager->addNote($_POST);
} elseif($action == 'deleteNote') {
    $manager->deleteNote($_POST);
} elseif($action == 'loadThumbs') {
    $arrImages = get_posts(array('post_type' => 'attachment',
    'numberposts' => -1,
    'post_status' => null,
    'post_parent' => $postId
     ));


    if($arrImages == false || !count($arrImages)) {
        echo '<p>No images associated with this post</p>';
        return;
    }
 
    echo '<ul class="imgNote-list">';
    foreach($arrImages as $image) {
	if($image->post_parent == 0) continue;
    
        $thumb = wp_get_attachment_image_src($image->ID, 'thumbnail');
        echo '<li onclick="knsprImageNote.loadImage('. $image->ID .');"><img src="'. $thumb[0] .'" alt="thumb" class="imgNote-preview"/></li>';
    }
    echo '</ul>';
} elseif($action == 'clear') {
    $manager->clearAll();
}


return;
