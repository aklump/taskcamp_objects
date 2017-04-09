<?php

namespace AKlump\Taskcamp;

use AKlump\LoftDataGrids\ExportData;

/**
 * Class Object
 */
abstract class Object implements ObjectInterface {

    const DATE_FORMAT_DATETIME = 'Y-m-d\TH:iO';
    const DATE_FORMAT_DATE = 'Y-m-d';
    const DATE_FORMAT_TIME = 'H:iO';

    protected $parsed, $flags;
    protected $cache = [];

    protected $title = '';
    protected $description = '';

    protected $data = array(
        'source'    => '',
        'config'    => array(),
        'questions' => array(),
    );

    /**
     * Constructor
     *
     * @param mixed  $source
     * @param  array $config key/value pairs
     */
    public function __construct($source = '', $config = array())
    {
        $this->parsed = new \stdClass;
        $this->flags = array();
        $this->setConfig($config);
        $source && $this->setSource($source);
    }

    public static function dateRegex()
    {
        return '(P[A-Z0-9]{2,})|(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}[+-]\d{4})|(\d{4}-\d{2}-\d{2})|(\d{2}:\d{2}[+-]\d{4})|(\d{2}:\d{2})';
    }

    public function setQuestions($questions)
    {
        $this->data['questions'] = array();
        foreach ($questions as $question) {
            $this->addQuestion($question);
        }

        return $this;
    }

    public function addQuestion($question)
    {
        $this->data['questions'][] = $question;

        return $this;
    }

    public function getQuestions()
    {
        return $this->data['questions'];
    }

    public function setSource($source)
    {
        $this->data['source'] = (string) $source;
        $this->parse();

        return $this;
    }

    public function getSource()
    {
        return $this->data['source'];
    }

    public function setConfig($config)
    {
        $this->data['config'] = new \stdClass;
        $config = (array) $config + array(
                'date_default' => 'now',
                'timezone'     => 'UTC',
                'milestone'    => 14 * 86400,
                'flag_prefix'  => '@',
                'weight'       => 1000,
            );
        foreach ($config as $key => $value) {
            $this->setConfigItem($key, $value);
        }

        return $this;
    }

    public function setConfigItem($key, $value)
    {
        $this->data['config']->{$key} = $value;

        return $this;
    }

    public function getConfig($key = null)
    {
        if ($key === null) {
            return $this->data['config'];
        }

        return isset($this->data['config']->{$key}) ? $this->data['config']->{$key} : null;
    }

    public function getParsed($key = null)
    {
        if (is_null($key)) {
            return $this->parsed;
        }

        return isset($this->parsed->{$key}) ? $this->parsed->{$key} : null;
    }

    public function setFlag($flag, $value)
    {
        $this->flags[$flag] = $value;

        return $this;
    }

    public function getFlag($flag, $typecast = false)
    {
        static $valid_flags, $types = null;
        if ($valid_flags === null) {
            $valid_flags = array();
            $types = array();
            foreach ($this->getFlagSchema() as $key => $value) {
                $valid_flags[$key] = $value->id;
                $types[$value->id] = $value->type;
            }
        }
        if (!in_array($flag, $valid_flags)) {
            return null;
        }
        $value = isset($this->flags[$flag]) ? $this->flags[$flag] : null;


        if ($typecast) {
            switch ($types[$flag]) {
                case 'datetime':
                    $value = $value ? $this->createDate($value) : $value;
                    break;

                case 'string':
                    $value = (string) $value;
                    break;
            }
        }

        return $value;
    }

    public function getFlags($implode = true)
    {
        $flags = array();
        foreach ($this->getFlagSchema() as $data) {
            $value = $this->getFlag($data->id, false);
            $show_zero = is_numeric($value) && !$value && !$data->hide_empty;

            if (!$value && !$show_zero) {
                continue;
            }
            if (!$value) {
                $value = $this->getFlag($data->id, true);
            }
            if ($value === true) {
                $value = '';
            }
            if (strpos($value, ' ') !== false) {
                $value = '"' . $value . '"';
            }
            $flags[$data->id] = $this->getConfig('flag_prefix') . $data->flag . $value;
        }

        return $implode ? implode(' ', $flags) : $flags;
    }

    /**
     * Return an array of flag definitions
     *
     * Note: ids and names, if verbs, should be present tense.
     *
     * @return array
     *   Keys are the flags.
     *   Each element is an object with these properties:
     *   - flag
     *   - type string for typecasting
     *   - id
     *   - name
     *   - regex
     *   - hide_empty bool Should we hide an empty flag, say for @id0 or @w0
     *   - dateContext array An array of ids to use as date context, if
     *   possible before relying on configuration and now.
     */
    public function getFlagSchema()
    {
        if (!isset($this->cache['schema'])) {
            $all_flags = array(
                (object) array(
                    'flag'        => 'id',
                    'type'        => 'string',
                    'description' => 'A unique ID.',
                    'id'          => 'id',
                    'name'        => 'Id',
                    'regex'       => '(id)("[^"]+"|[^\s]+)',
                    'hide_empty'  => false,
                ),
                (object) array(
                    'flag'        => 'w',
                    'type'        => 'float',
                    'description' => 'Lower numbers are ranked higher in priority.',
                    'id'          => 'weight',
                    'name'        => 'Priority',
                    'regex'       => '(w)(\-?[\d]+)',
                    'hide_empty'  => true,
                ),
                (object) array(
                    'flag'        => 'p',
                    'type'        => 'string',
                    'description' => 'The name of the person responsible.',
                    'id'          => 'person',
                    'name'        => 'Assigned To',
                    'regex'       => '(p)("[^"]+"|[^\s]+)',
                    'hide_empty'  => true,
                ),
                (object) array(
                    'flag'        => 'e',
                    'type'        => 'int|percent',
                    'description' => 'The estimated mins to done.',
                    'id'          => 'estimate',
                    'name'        => 'Estimate',
                    'regex'       => '(e)(\d+%?)',
                    'hide_empty'  => true,
                ),
                (object) array(
                    'flag'        => 'g',
                    'type'        => 'string',
                    'description' => 'The name of a group this belongs to, e.g. Wednesday.',
                    'id'          => 'group',
                    'name'        => 'Group',
                    'regex'       => '(g)("[^"]+"|[^\s]+)',
                    'hide_empty'  => true,
                ),
                (object) array(
                    'flag'         => 's',
                    'type'         => 'datetime',
                    'granularity'  => 'time',
                    'dateInterval' => true,
                    'description'  => 'The date to start work.',
                    'id'           => 'start',
                    'name'         => 'Start Time',
                    'regex'        => '(s)(' . static::dateRegex() . ')?',
                    'hide_empty'   => true,
                ),
                (object) array(
                    'flag'         => 'm',
                    'type'         => 'datetime',
                    'granularity'  => 'date',
                    'dateInterval' => true,
                    'description'  => 'The next milestone date.',
                    'id'           => 'milestone',
                    'name'         => 'Milestone',
                    'regex'        => '(m)(' . static::dateRegex() . ')?',
                    'hide_empty'   => true,
                ),
                (object) array(
                    'flag'         => 'f',
                    'type'         => 'datetime',
                    'granularity'  => 'date',
                    'dateInterval' => true,
                    'description'  => 'The date it needs to be finished.',
                    'id'           => 'finish',
                    'name'         => 'Finish',
                    'regex'        => '(f)(' . static::dateRegex() . ')?',
                    'hide_empty'   => true,
                ),
                (object) array(
                    'flag'        => 'd',
                    'type'        => 'datetime',
                    'granularity' => 'time',
                    'description' => 'The date it was done and complete.',
                    'id'          => 'done',
                    'name'        => 'Completed',
                    'regex'       => '(d)(' . static::dateRegex() . ')?',
                    'hide_empty'  => true,
                    'dateContext' => 'start',
                ),
                (object) array(
                    'flag'        => 'h',
                    'type'        => 'float',
                    'description' => 'Actual hours from start to finish.',
                    'id'          => 'hours',
                    'name'        => 'Hours',
                    'regex'       => '(h)([.\d]+)',
                    'hide_empty'  => true,
                ),

                // Third party integration
                (object) array(
                    'flag'        => 'bc',
                    'type'        => 'string',
                    'description' => 'The Basecamp unique ID.',
                    'id'          => 'basecamp',
                    'name'        => 'Basecamp Id',
                    'regex'       => '(bc)(\d{6,})',
                    'hide_empty'  => true,
                ),
                (object) array(
                    'flag'        => 'mt',
                    'type'        => 'string',
                    'description' => 'The Mantis unique ID.',
                    'id'          => 'mantis',
                    'name'        => 'Mantis Id',
                    'regex'       => '(man)(\d+)',
                    'hide_empty'  => true,
                ),
                (object) array(
                    'flag'        => 'qb',
                    'type'        => 'string',
                    'description' => 'The Quickbooks job.',
                    'id'          => 'quickbooks',
                    'name'        => 'Quickbooks Job',
                    'regex'       => '(qb)("[^"]+"|[^\s]+)',
                    'hide_empty'  => true,
                ),
            );

            $filtered = array();
            $available = $this->getAvailableFlags();
            foreach ($all_flags as $data) {
                if (in_array($data->flag, $available)) {
                    $filtered[$data->flag] = $data;
                }
            }

            $filtered2 = array();
            foreach ($available as $flag) {
                $filtered2[$flag] = $filtered[$flag];
            }

            $schema = $filtered2;

            $this->cache['schema'] = $schema;
        }

        return $this->cache['schema'];
    }

    /**
     * Exposes the parsed object properties as if on the object.
     *
     * @return null
     * @internal param $key
     *
     */
    //    public function __get($key)
    //    {
    //        return isset($this->parsed->{$key}) ? $this->parsed->{$key} : null;
    //    }

    public function __toString()
    {
        $build = array();
        $build[] = $this->getTitle();
        $build[] = $this->getDescription();
        $build[] = '';

        return implode(PHP_EOL, $build);
    }

    public function convertFlagsToParsed()
    {
        if (empty($this->flags)) {
            return;
        }
        foreach ($this->getFlagSchema() as $item) {
            $subject = &$this->parsed->{$item->id};
            if (($value = isset($this->flags[$item->id]) ? $this->flags[$item->id] : null)) {
                switch ($item->type) {
                    case 'string':
                        $value = strval($value);
                        break;
                    case 'float':
                        $value = floatval($value);
                        break;
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'datetime':
                        $method = 'getDateTime';
                        if ($item->granularity === 'time') {
                            $method = 'getTime';
                        }
                        elseif ($item->granularity === 'date') {
                            $method = 'getDate';
                        }
                        $value = new \DateTime($this->{$method}($value), new \DateTimeZone($this->getConfig('timezone')));
                        break;
                }
            }

            $subject = $value;
        }
    }

    /**
     * Return a formated DATE string of now (or from string)
     *
     * @param  string $string Optional. Defaults to now
     *
     * @return string
     *
     * @see  ObjectInterface::createDate()
     */
    public function getDate($string = null)
    {
        return $this->createDate($string)->format(static::DATE_FORMAT_DATE);
    }

    /**
     * Return a formated DATETIME string of now (or from string)
     *
     * @param  string $string Optional. Defaults to now
     *
     * @return string
     *
     * @see  ObjectInterface::createDate()
     */
    public function getDateTime($string = null)
    {
        return $this->createDate($string)->format(static::DATE_FORMAT_DATETIME);
    }

    /**
     * Return a formatted TIME string of now (or from string)
     *
     * @param  string $string Optional. Defaults to now
     *
     * @return string
     *
     * @see  ObjectInterface::createDate()
     */
    public function getTime($string = null)
    {
        return $this->createDate($string)->format(static::DATE_FORMAT_TIME);
    }

    /**
     * Create a DateTime object from a string using context with config
     * fallback.
     *
     * @param  string $string  DateTime string  Can be either one of our
     *                         supported string formats: DATE_FORMATE_DATE,
     *                         DATE_FORMATE_DATETIME, DATE_FORMATE_TIME or a
     *                         string representing a period, e.g. 'PT1H'.
     * @param  string $context DateTime string If provided this string will be
     *                         used to make up for missing elements in $string.
     *
     * @return DateTime
     */
    public function createDate($string = null, $context = null)
    {
        $now = new \DateTime($this->getConfig('date_default'), new \DateTimeZone($this->getConfig('timezone')));

        // Convert a DateInterval beginning with P
        if (substr($string, 0, 1) === 'P') {
            $string = date_add($now, new \DateInterval($string))->format(static::DATE_FORMAT_DATETIME);
        }
        $input = $this->parseDateString($string);
        $configuration = $this->parseDateString($now->format(static::DATE_FORMAT_DATETIME));
        $context = $this->parseDateString($context);
        $date = $this->datesMerge($input, $context, $configuration);
        $date = new \DateTime($this->dateFlatten($date));

        return $date;
    }

    /**
     * Return an ExportData object.
     */
    public function getExportData()
    {
        if (!isset($this->exportData)) {
            $this->exportData = new ExportData;
        }

        return $this->exportData;
    }

    protected function datesMerge()
    {
        $dates = func_get_args();
        $final = array_fill(0, 3, null);
        while ($candidate = array_shift($dates)) {
            foreach ($candidate as $key => $item) {
                if (empty($final[$key])) {
                    $final[$key] = $item;
                }
            }
        }

        return $final;
    }

    protected function dateFlatten(array $date)
    {
        return $date[0] . 'T' . $date[1] . $date[2];
    }

    protected function parseDateString($string)
    {
        $date_parts = '(?:(\d{4}-\d{2}-\d{2}))?T?(?:(\d{2}:\d{2}))?(?:([+-]\d{4}))?';
        $parts = array();
        if (!empty(trim($string)) && preg_match("/$date_parts/", $string, $parts)) {
            array_shift($parts);
        }
        $parts += array_fill(0, 3, null);

        return $parts;
    }

    protected function resetParsed()
    {
        $this->parsed = new \stdClass;

        // Setup the defaults
        $this->parsed->valid_syntax = false;
        $this->parsed->complete = false;

        foreach ($this->getFlagSchema() as $data) {
            $this->parsed->{$data->id} = null;
        }
    }

    /**
     * Transforms Object::source into Object::parsed
     */
    protected function parse()
    {
        //        $this->cache['date'] = [null, null, null];

        // Trim front/back whitespace
        $source = trim($this->getSource());

        $this->parsed = new \stdClass;
        $this->parsed->source = $source;

        $this->setQuestions(array());

        if (empty($source)) {
            return;
        }

        $this->parsed->lines = preg_split('/\n|\r\n?/', $source);

        // Grab all paragraphs and trim each
        $this->parsed->p = array_values(array_filter(preg_split('/\n\n|\r\n\r\n?/', $source)));
        foreach ($this->parsed->p as $key => $value) {
            $this->parsed->p[$key] = trim(str_replace("\r\n", "\n", $value));
        }

        // Extract the questions
        foreach ($this->parsed->p as $p) {
            if ($value = $this->isQuestion($p)) {
                $this->addQuestion($value);
            }
        }
    }

    /**
     * Test a string to see if it's a question
     *
     * @param  string $subject
     *
     * @return FALSE||string  The string is the value of the question.
     */
    protected function isQuestion($subject)
    {
        $is = preg_match('/^\?\s*(.*)/s', $subject, $matches);

        return $is ? $matches[1] : false;
    }

    /**
     * Test a string to see if it begins with an url
     *
     * @param  string $subject
     *
     * @return FALSE||string  The string is the extracted url.
     */
    protected function isUrl($subject)
    {
        $is = preg_match('/^(https?:\/\/[^\s]+)/', $subject, $matches);

        return $is ? $matches[1] : false;
    }

    /**
     * Given a string of text parse out and set flags
     *
     * @param  string $text
     *
     * @return  string The trimmed string with flags removed.
     *
     *
     * // TODO needs cleaning up. $this->parsed isn't really used anymore.
     */
    protected function parseFlags($text)
    {
        $flags = $this->getFlagSchema();
        foreach ($flags as $flag) {
            $this->flags[$flag->id] = null;
            $regex = '/(?<!\\\)\s+\\' . $this->getConfig('flag_prefix') . $flag->regex . '/';
            if (preg_match($regex, $text, $matches)) {
                $text = str_replace($matches[0], '', $text);
                $value = array_key_exists(2, $matches) ? trim($matches[2], ' "') : true;
                switch($flag->type){
                    case 'float':
                        $value = floatval($value);
                        break;
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'int|percent':
                        $value = substr($value, -1, 1) === '%' ? $value : intval($value);
                        break;
                }
                $this->flags[$flag->id] = $value;
            }
        }

        //
        //
        // Expand truthy values
        //
        if (isset($this->flags['milestone']) && $this->flags['milestone'] === true) {
            $this->flags['milestone'] = $this->getDate('PT' . $this->getConfig('milestone') . 'S');
        }
        if (isset($this->flags['start']) && $this->flags['start'] === true) {
            $this->flags['start'] = $this->getDateTime();
        }
        if (isset($this->flags['done']) && $this->flags['done'] === true) {
            $this->flags['done'] = $this->getDateTime();
        }

        foreach ($flags as $flag) {

            // Now for TRUE values we may need to insert time
            if ($this->flags[$flag->id] === true
                && in_array($flag->granularity, array(
                    'date',
                    'datetime',
                    'time',
                ))
            ) {
                switch ($flag->granularity) {
                    case 'datetime':
                        $this->flags[$flag->id] = $this->getDateTime();
                        break;

                    case 'date':
                        $this->flags[$flag->id] = $this->getDate();
                        break;

                    case 'time':
                        $this->flags[$flag->id] = $this->getTime();
                        break;
                }
                $this->parsed->{$flag->id} = $this->flags[$flag->id];
            }

            // Convert DateIntervals
            if (isset($flag->dateInterval) && substr($this->flags[$flag->id], 0, 1) === 'P') {
                switch ($flag->granularity) {
                    case 'datetime':
                        $this->flags[$flag->id] = $this->getDateTime($this->flags[$flag->id]);
                        break;

                    case 'date':
                        $this->flags[$flag->id] = $this->getDate($this->flags[$flag->id]);
                        break;

                    case 'time':
                        $this->flags[$flag->id] = $this->getTime($this->flags[$flag->id]);
                        break;
                }
            }

            //
            //
            // Setting the value on our ExportData object.
            //
            $method = "set{$flag->id}";
            if (isset($this->flags[$flag->id]) && method_exists($this, $method)) {
                $value = $this->flags[$flag->id];
                if (in_array($flag->type, ['int', 'float', 'string', 'int|percent'])) {
                    $this->{$method}($value);
                }
                elseif (in_array($flag->type, ['datetime'])) {

                    // Get the context
                    $context = !empty($flag->dateContext) && !empty($this->flags[$flag->dateContext]) ? $this->flags[$flag->dateContext] : null;
                    $date = $this->createDate($value, $context);
                    $this->{$method}($date);
                }
            }
        }

        return trim($text);
    }

    protected function get($key, $default = null)
    {
        $value = $this->getExportData()->getValue($key);

        return !is_null($value) ? $value : $default;
    }

    protected function set($key, $value = null)
    {
        //
        //
        // Setting null values for some types will auto-generate a value.
        //
        if (is_null($value)) {
            $schema = $this->getFlagSchema();
            foreach ($schema as $item) {
                if (strcasecmp($item->id, $key) === 0) {
                    break;
                }
                $item = null;
            };

            // For date's that are null use now
            if ($item && in_array($item->type, ['date', 'time', 'datetime'])) {
                $value = $this->createDate();
            }
        }

        $this->getExportData()->add($key, $value);

        return $this;
    }

    public function setTitle($title)
    {
        $title = (string) trim($title);
        $this->title = $title;

        return $this->set('title', $title);
    }


    public function getTitle()
    {
        return $this->get('title');
    }


    public function setDescription($description)
    {
        $this->description = (string) trim($description);
    }


    public function getDescription()
    {
        return $this->description;
    }


    public function getMarkdown()
    {
        return (string) $this;
    }


    public function getHTML($parents = 0)
    {
        $build = array();
        $parents += 1;
        $tag = "h{$parents}";
        $build[] = "<$tag>" . $this->getTitle() . "</$tag>";
        $build[] = '<p>' . $this->getDescription() . '</p>';
        $build[] = '';

        return implode(PHP_EOL, $build);
    }


    public function deleteLine($line_number)
    {
        $return = $this->parsed->lines[$line_number];

        // Remove todos if found
        foreach ($this->parsed->todos_by_line as $id => $line_numbers) {
            if (in_array($line_number, $line_numbers)) {
                $this->getTodos()->getList()->remove($id);
                unset($this->parsed->todos_by_line[$id]);
                break;
            }
        }

        // Remove the line
        unset($this->parsed->lines[$line_number]);

        return $return;
    }


}
