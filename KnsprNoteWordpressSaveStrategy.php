<?php

class KnsprNoteWordpressSaveStrategy implements IKnsprNoteSaveStrategy {
    private $cache = array();

    private $attachId = 0;
    private $key = 'imgNotes';

    public function getImageId() {
        return $this->attachId;
    }

    public function __construct($attachId) {
        $this->attachId = $attachId;

        $this->loadData();
    }

    private function loadData() {
        $tmp = get_post_meta($this->attachId, $this->key, true);
        $this->cache = unserialize(stripslashes($tmp));

        if($this->cache == false) {
            $this->cache = array();
        }
    }

    private function saveData() {
        add_post_meta($this->attachId, $this->key, addslashes(serialize($this->cache)), true) or update_post_meta($this->attachId, $this->key, addslashes(serialize($this->cache)));
    }

    public function load() {
        if(count($this->cache) == 0) {
            $this->loadData();
        }
        
        return $this->cache;
    }

    public function save(KnsprNote $note) {
        $this->cache[] = $note;
        $this->saveData();
    }

    public function delete($noteId) {
        if(isset($this->cache[$noteId])) {
            $tmp = array();
            foreach($this->cache as $id => $note) {
                if($id == $noteId) {
                    continue;
                }

                $tmp[] =  $note;
            }

            $this->cache = $tmp;
            $this->saveData();
        }
    }

    public function clear() {
        unset($this->cache);
        $this->saveData();
    }
}