<?php

namespace Classmarkets;

class JavaProperties implements \ArrayAccess {
    private $properties = array();

    private $lines = array();
    private $line = '';
    private $key = '';
    private $value = '';

    public function getAll() {
        return $this->properties;
    }

    public function loadResource($uri, $streamContext = null) {
        $string = @file_get_contents($uri, false, $streamContext);
        if($string === false) {
            throw new \InvalidArgumentException("failed to open stream: No such file or directory: '$uri'");
        }

        $this->loadString($string);
    }

    public function loadString($string) {
        if($string === '') return;

        $string = $this->normalizeInput($string);

        $this->lines = explode("\n", $string);
        $this->line = reset($this->lines);

        foreach($this->lines as $this->line) {
            if(preg_match('/^[!#]/', $this->line)) continue;
            if(preg_match('/^$/', $this->line)) continue;

            $this->parseLine();
        }

        reset($this->properties);
    }

    private function normalizeInput($string) {
        $string = $this->convertUnicodeEscapeSequences($string);
        $string = $this->normalizeLineEndings($string);
        $string = $this->removeLeadingWhitespaceFromEachLine($string);
        $string = $this->concatLogicalLines($string);

        return $string;
    }

    private function convertUnicodeEscapeSequences($string) {
        return preg_replace_callback('/\\\\u(\d{4})/s', function($match) {
            return chr(base_convert($match[1], 16, 10));
        }, $string);
    }

    private function normalizeLineEndings($string) {
        $string = str_replace("\r\n", "\n", $string);                
        $string = str_replace("\r", "\n", $string);
        return $string;
    }

    private function removeLeadingWhitespaceFromEachLine($string) {
        return preg_replace('/\n[ \t\f]+/', "\n", "\n$string");  
    }

    private function concatLogicalLines($string) {
        return preg_replace('/\\\\\n/s', '', $string);            
    }

    private function parseLine() {
        $groups = array();
        preg_match('/^([^=: \t\f]+?)[=: \t\f]+(.*)$/', $this->line, $groups);

        list(, $key, $value) = $groups;

        $this[$key] = $value;
    }

    /* ArrayAccess */

    public function offsetExists($offset) {
        return isset($this->properties[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->properties[$offset]) ? $this->properties[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if(is_null($offset)) {
            throw new \Exception("Values without keys are not supported");
        }

        $this->properties[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->properties[$offset]);
    }
}
