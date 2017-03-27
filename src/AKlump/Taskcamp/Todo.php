<?php
/**
 * @file
 * Defines the todo object
 *
 * @ingroup taskcamp_objects
 * @{
 */

namespace AKlump\Taskcamp;

/**
 * Class Todo
 *
 * @code
 * $config = [
 *     'timezone'     => 'America/Los_Angeles',
 * ];
 *
 * $item = new Todo('- ensure the file exists @e45 @s07:45 @d08:15 @pAaron');
 * // $item->estimate === 45
 * // $item->start === \DateTime object
 * // $item->done === \DateTime object
 * // $item->person === Aaron
 * // $item->getDuration() === 1800
 * // $item->getCarryOver === 900
 * @endcode
 *
 * @package AKlump\Taskcamp
 */
class Todo extends Object implements TaskInterface, SortableInterface {

    use TaskTrait {
        TaskTrait::setDone as traitSetDone;
    }
    use SortableTrait;

    public function setDone(\DateTime $done = null)
    {
        $this->traitSetDone($done);
        $done = $this->getDone();
        if ($this->parsed->complete !== true) {
            $time = $done->format(static::DATE_FORMAT_DATETIME);
            $this->flags['done'] = $time;
            $this->parsed->complete = true;
            $this->flags['weight'] += $this->getConfig('weight');
        }

        return $this;
    }

    /**
     * Add in todo specific 'show_ids' config key.
     *
     * @param array||object $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $config = (array) $config + array(

                // Set this to true and id flags will be included in the string
                'show_ids' => true,
            );

        return parent::setConfig($config);
    }

    public function getFlags($implode = true)
    {
        $flags = parent::getFlags(false);
        if (!$this->getConfig('show_ids')) {
            unset($flags['id']);
        }

        return $implode ? implode(' ', $flags) : $flags;
    }

    public function __toString()
    {
        $output = '';
        if (!$this->getParsed('valid_syntax')) {
            $output .= $this->getSource();
        }
        else {
            $output .= '- ';
            $output .= '[' . ($this->getParsed('complete') ? 'x' : ' ') . '] ';
            $output .= $this->getTitle();

            $temp = clone $this;

            // Reduce the start date down to hours only, if possible
            $flags = $temp->getFlags(false);

            // These are strait values, so we use them, as opposed to $flags['start...']
            $start = $temp->getFlag('start');
            $done = $temp->getFlag('done');
            if ($start && $done) {

                // Strip the date
                if (substr($start, 0, 11) == substr($done, 0, 11)) {
                    $start = substr($start, 11);
                    $temp->setFlag('start', $start);
                    $flags['start'] = $temp->getFlag('start');
                }

                // Strip timezone
                if (substr($start, -5) == substr($done, -5)) {
                    $start = substr($start, 0, -5);
                    $temp->setFlag('start', $start);
                    $flags['start'] = $temp->getFlag('start');
                }
            }

            $output .= ' ' . $temp->getFlags();
            unset($temp);
        }

        return trim($output);
    }

    public function getMarkdown()
    {
        return $this->__toString();
    }

    public function getHTML($parents = 0)
    {
        $output = '';
        // @todo Need to build the js to support this
        $output .= '<span class="taskcamp__todo">' . PHP_EOL;
        $output .= '<input type="checkbox" onclick="Taskcamp.Todo.Toggle(); return false" /> ';
        $output .= '<a href="javascript:Taskcamp.Todo.Toggle(); return false">';
        $output .= $this->getTitle();
        $output .= '</a> ' . $this->getFlags();
        $output .= '</span>' . PHP_EOL;

        return trim($output);
    }

    public static function getAvailableFlags()
    {
        return array('id', 'p', 'bc', 'mt', 'e', 's', 'm', 'f', 'd', 'h', 'w');
    }

    /**
     * Parse the raw text
     *
     * Sets the value of $this->parsed
     *
     * @return bool
     *   FALSE means the todo couldn't be parsed
     */
    protected function parse()
    {
        $parsed = $source = $this->getSource();

        // Expand lazy prefixes
        if (preg_match('/^- (\[ \]) (.*)(?:x| )x$/i', $parsed, $matches)
            || preg_match('/^- ?\[ *(x)? *\] ?(.*)/i', $parsed, $matches)
            || preg_match('/^-(x) ?(.*)/i', $parsed, $matches)
            || preg_match('/^-{1}\s+()([^\-].*)/', $parsed, $matches)
        ) {
            $parsed = '- [' . (trim($matches[1]) ? 'x' : ' ') . '] ' . $matches[2];
        }


        // // Do not allow '---' to be construed as a todo
        // if ($source === '---') {
        //   return FALSE;
        // }

        $this->resetParsed();

        // First parse to see if it's valid
        if (preg_match('/^- \[(x| )\]\s*(.*)/', $parsed, $found)) {
            $this->parsed->valid_syntax = true;

            // Complete based on presence of an X or not; no date involved here.
            $hasX = (bool) trim($found[1]);
            $this->parsed->complete = $hasX;
            $hasX && $this->setDone($this->createDate());

            // Parse out flags, setting them and returning title.
            $title = $this->parseFlags($found[2]);
            $this->convertFlagsToParsed();

            $this->setTitle($title);

            $this->setDescription($title . ' ' . $this->getFlags());
        }

        return $this->parsed->valid_syntax;
    }

    public function getDuration()
    {
        $start = $this->getFlag('start');
        $done = $this->getFlag('done');
        if (empty($start) || empty($done)) {
            return false;
        }

        $startObject = $this->createDate($start, $done);
        $doneObject = $this->createDate($done, $start);

        return $doneObject->format('U') - $startObject->format('U');
    }

    public function getCarryover()
    {
        $estimate = $this->getFlag('estimate');
        $duration = $this->getDuration();
        if (empty($estimate) || empty($duration)) {
            return false;
        }

        // Convert minutes (@e) to seconds (duration)
        $estimate *= 60;

        return $estimate - $duration;
    }
}
