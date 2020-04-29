<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Traces
 *
 * @author PFS
 */

class BRILogger {
    public $_FilePath = null;
    public $_Prefix = null;
    public $DebugLevel = 0;

    function __construct($prefix = 'Inconnu') {
    }

    private function prefix($prefix) {
        $this->_Prefix = $prefix;
        return;
    }


    private function DebugTrace($Message, $level) {
        return;
    }

    public function all($Message) {
        return $this->DebugTrace($Message, 10);
    }

    public function debug($Message) {
        return $this->DebugTrace($Message, 20);
    }

    public function fatal($Message) {
        return $this->DebugTrace($Message, 30);
    }

    public function debugtab($Message, $uneligne) {
        return;
    }
}
