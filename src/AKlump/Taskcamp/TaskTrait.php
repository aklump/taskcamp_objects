<?php


namespace AKlump\Taskcamp;

trait TaskTrait {

    protected $exportData;

    public function getTask($default = null)
    {
        return $this->get('title', $default);
    }

    public function setTask($task)
    {
        return $this->setTitle($task);
    }

    public function getStart($default = null)
    {
        return $this->get('start', $default);
    }

    public function setStart(\DateTime $start = null)
    {
        return $this->set('start', $start);
    }

    public function setNotDone()
    {
        // TODO Not really sure what's up with this.  2017-03-27T16:10, aklump
        $weight = $this->get('weight');
        if (!empty($weight)) {
            $weight = $this->getConfig()->weight;
            $this->set('weight', $weight);
        }

        // Refactor this by removing parsed, etc.
        $this->flags['done'] = null;
        $this->parsed->complete = false;

        return $this->set('done', false);
    }

    public function getDone($default = null)
    {
        return $this->get('done', $default);
    }

    public function setDone(\DateTime $done = null)
    {
        return $this->set('done', $done);
    }

    public function getFinish($default = null)
    {
        return $this->get('finish', $default);
    }

    public function setFinish(\DateTime $finish = null)
    {
        return $this->set('finish', $finish);
    }

    public function getMilestone($default = null)
    {
        return $this->get('milestone', $default);
    }

    public function setMilestone(\DateTime $milestone)
    {
        return $this->set('milestone', $milestone);
    }

    public function getEstimate($default = null)
    {
        return $this->get('estimate', $default);
    }

    public function setEstimate($estimate)
    {
        return $this->set('estimate', $estimate);
    }

    public function getActual($default = null)
    {
        return $this->get('actual', $default);
    }

    public function isDone($default = null)
    {
        return boolval($this->get('done', $default));
    }

    public function getVariance($default = null)
    {
        $estimate = $this->get('estimate', null);
        $duration = $this->getDuration();

        return is_null($estimate) || is_null($duration) ? $default : $duration - $estimate;
    }

    public function getDuration($default = null)
    {
        $start = $this->get('start');
        $done = $this->get('done');

        return is_null($start) || is_null($done) ? $default : ($done->format('U') - $start->format('U')) / 60;
    }

    public function getPerson($default = null)
    {
        return $this->get('person', $default);
    }

    public function setPerson($person)
    {
        return $this->set('person', $person);
    }
}
