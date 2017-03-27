<?php

namespace AKlump\Taskcamp;

/**
 * Represents a file as a Taskcamp object.
 *
 * self::parsed
 *   ->contents
 *   ->exists
 *   ->basename
 *   ->extension
 *   ->filename
 */
class File extends Object implements ObjectInterface {

    public static function getAvailableFlags()
    {
        return array('bc');
    }

    public function __toString()
    {
        return (string) $this->getParsed('contents');
    }

    public function parse()
    {
        $path = $this->parseFlags($this->getSource());
        $this->parsed = (object) ((array) $this->parsed + pathinfo($path));
        list($path) = explode('@', $path);
        if (($path = trim($path))) {
            $this->setTitle($this->parsed->basename);
            $this->setDescription($path);
        }
        $this->parsed->contents = is_readable($path) ? file_get_contents($path) : null;
        $this->parsed->exists = file_exists($path);
    }

    public function getHTML($parents = 0)
    {
        $html = parent::getHTML($parents);
        $contents = (string) $this;
        $html .= "<pre><code>$contents</code></pre>";

        return $html;
    }
}
