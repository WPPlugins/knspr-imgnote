<?php

interface IKnsprNoteSaveStrategy {

/**
 * Loads and returns an array with all notes associated with an image
 */
    public function load();

    /**
     * Saves a new note
     */
    public function save(KnsprNote $note);

    public function delete($noteId);

    /**
     * Clear all
     */
    public function clear();

}

class KnsprNote {
    public $x1 = 0;
    public $y1 = 0;

    public $width = 0;
    public $height = 0;

    public $imageId = 0;

    public $note = '';

    public $url = '';

    public function __construct($x, $y, $width, $height, $text, $url) {
        $this->x1 = $x;
        $this->y1 = $y;
        $this->width = $width;
        $this->height = $height;
        $this->note = $text;
        $this->url = $url;

    }
}

class KnsprNoteManager {
    private $strategy = null;

    public function __construct() {
    }

    public function setStrategy(IKnsprNoteSaveStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function addNote($postData) {
        $note = new KnsprNote(intval($postData['x']), intval($postData['y']), intval($postData['width']), intval($postData['height']), htmlentities(urldecode($postData['text'])), $postData['url']);
        $this->strategy->save($note);
    }

    public function deleteNote($postData) {
        $this->strategy->delete(intval($postData['noteId']));
    }

    public function getNotes() {
        return $this->strategy->load();
    }

    public function clearAll() {
        $this->strategy->clear();
    }

    public function getCount() {
        return count($this->getNotes($this->strategy->getImageId()));
    }

    public function printNoteList() {
        echo '<ul class="noteList">';

        $imageId = $this->strategy->getImageId();
        foreach($this->getNotes() as $id => $note) {
            echo '<li class="noteText" id="note-'. $id .'">'. $note->note .' <a href="javascript:void(0);" onclick="knsprImageNote.deleteNote('. $imageId .', '. $id .', function(data) { knsprImageNote.loadImage('. $imageId .'); });">Delete</a></li>';
        }

        echo '</ul>';
    }

    public function printNoteJson($return = false) {
        $array = array();
        $notes = $this->getNotes();

        if(count($notes) == 0) {
            return '[]';
        }

        foreach($notes as $note) {
            $note->imageId = $this->strategy->getImageId();
            $note->note = nl2br($note->note);
            $array[] = $note;
        }

        return array_to_json($array);
    }

}

function array_to_json( $a ) {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a)) {
        if (is_float($a)) {
        // Always use "." for floats.
            return floatval(str_replace(",", ".", strval($a)));
        }

        if (is_string($a)) {
            static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"', "'"), array('\\\\', '/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"', "\'"));
            return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
        }
        else
            return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
        if (key($a) !== $i) {
            $isList = false;
            break;
        }
    }
    $result = array();
    if ($isList) {
        foreach ($a as $v) $result[] = array_to_json($v);
        return '[' . join(',', $result) . ']';
    }
    else {
        foreach ($a as $k => $v) $result[] = array_to_json($k).':'.array_to_json($v);
        return '{' . join(',', $result) . '}';
    }
}