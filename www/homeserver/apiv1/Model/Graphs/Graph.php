<?php

namespace homeserver\apiv1\Model\Graphs;

use DateTime;
use homeserver\apiv1\Model\BaseModel;
use homeserver\apiv1\Repository\GraphDataRepository;
use homeserver\apiv1\Repository\GraphSignalEventRepository;
use homeserver\apiv1\Repository\GraphSignalRepository;

class Graph extends BaseModel
{

    /**
     * @var string $theme the name of the used theme
     */
    private $theme;

    /**
     * @var string $type todo: is not used?
     */
    private $type;

    /**
     * @var string $timeMode the time mode of the graph, e.g. days, minutes, fixed, ...
     */
    private $timeMode;

    /**
     * @var int $timeParam1 the first param according to the time mode
     */
    private $timeParam1;

    /**
     * @var int $timeParam2 the second param according to the time mode
     */
    private $timeParam2;

    /**
     * @var int $width the width of the graph
     */
    private $width;

    /**
     * @var int $height the height of the graph
     */
    private $height;

    /**
     * @var string $heightMode the height mode, e.g. percent
     */
    private $heightMode;

    /**
     * @var int $distValue the time value between two graph values
     */
    private $distValue;

    /**
     * @var string $distType the time unit according to $distValue
     */
    private $distType;

    /**
     * @Inject
     * @var GraphDataRepository $dataRepo to retrieve the data for this graph
     */
    private $dataRepo;

    /**
     * @Inject
     * @var GraphSignalRepository $signalRepo to get the signals for this graph
     */
    private $signalRepo;

    /**
     * @return string the name of the used theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @param string $theme the name of the used theme
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return string todo: is not used?
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type todo: is not used?
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string the time mode of the graph, e.g. days, minutes, fixed, ...
     */
    public function getTimeMode()
    {
        return $this->timeMode;
    }

    /**
     * @param string $timeMode the time mode of the graph, e.g. days, minutes, fixed, ...
     */
    public function setTimeMode($timeMode)
    {
        $this->timeMode = $timeMode;
    }

    /**
     * @return int the first param according to the time mode
     */
    public function getTimeParam1()
    {
        return $this->timeParam1;
    }

    /**
     * @param int $timeParam1 the first param according to the time mode
     */
    public function setTimeParam1($timeParam1)
    {
        $this->timeParam1 = $timeParam1;
    }

    /**
     * @return int the second param according to the time mode
     */
    public function getTimeParam2()
    {
        return $this->timeParam2;
    }

    /**
     * @param int $timeParam2 the second param according to the time mode
     */
    public function setTimeParam2($timeParam2)
    {
        $this->timeParam2 = $timeParam2;
    }

    /**
     * @return int the width of the graph
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width the width of the graph
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * @return int the height of the graph
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height the height of the graph
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return string the height mode, e.g. percent
     */
    public function getHeightMode()
    {
        return $this->heightMode;
    }

    /**
     * @param string $heightMode the height mode, e.g. percent
     */
    public function setHeightMode($heightMode)
    {
        $this->heightMode = $heightMode;
    }

    /**
     * @return int the time value between two graph values
     */
    public function getDistValue()
    {
        return $this->distValue;
    }

    /**
     * @param int $distValue the time value between two graph values
     */
    public function setDistValue($distValue)
    {
        $this->distValue = $distValue;
    }

    /**
     * @return string the time unit according to $distValue
     */
    public function getDistType()
    {
        return $this->distType;
    }

    /**
     * @param string $distType the time unit according to $distValue
     */
    public function setDistType($distType)
    {
        $this->distType = $distType;
    }

    /**
     * @return DateTime returns the dateTime from which point this graph shows the data
     */
    public function getTimeFrom()
    {
        $from = new DateTime();
        switch ($this->timeMode) {
            case "fixed":
                $from->setTimestamp($this->timeParam1);
                break;

            case "seconds":
                $from->sub(new \DateInterval('PT' . $this->timeParam1 . 'S'));
                break;

            case "minutes":
                $from->sub(new \DateInterval('PT' . $this->timeParam1 . 'M'));
                break;

            case "hours":
                $from->sub(new \DateInterval('PT' . $this->timeParam1 . 'H'));
                break;

            case "days":
                $from->sub(new \DateInterval('P' . $this->timeParam1 . 'D'));
                break;
        }
        return $from;
    }

    /**
     * @return DateTime returns the dateTime until this graph shows the data
     */
    public function getTimeUntil()
    {
        $until = new DateTime();
        if ($this->timeMode == "fixed") {
            $until->setTimestamp($this->timeParam2);
        }
        return $until;
    }

    /**
     * @return \DateInterval the interval between the signals or null, if undefined
     */
    public function getSignalInterval()
    {
        switch ($this->distType) {
            case 'd':
                return new \DateInterval("P" . $this->distValue . "D");
            case 'h':
                return new \DateInterval("PT" . $this->distValue . "H");
            case 'm':
                return new \DateInterval("PT" . $this->distValue . "M");
            case 's':
                return new \DateInterval("PT" . $this->distValue . "S");

            default:
                return null;
        }
    }

    /**
     * Returns the graph data in a given time span. If no time span is defined, the graph default time span will be used
     *
     * @param string | DateTime | int $from the start point for the data values as a string, DateTime object or timestamp
     * @param string | DateTime | int $until the end point for the data values as a string, DateTime object or timestamp
     * @return array the data in an associative array
     * @throws \Exception if the database access fails
     */
    public function getData($from = null, $until = null)
    {
        if ($from == null) {
            $from = $this->getTimeFrom()->getTimestamp();
        } else {
            if (is_string($from))
                $from = (new DateTime($from))->getTimestamp();
            elseif ($from instanceof DateTime)
                $from = $from->getTimestamp();
        }
        if ($until == null) {
            $until = $this->getTimeUntil()->getTimestamp();
        } else {
            if (is_string($until))
                $until = (new DateTime($until))->getTimestamp();
            elseif ($until instanceof DateTime)
                $until = $until->getTimestamp();
        }
        return $this->dataRepo->query(array(
            'graphId' => "= " . $this->id,
            'time' => [">= " . $from, "<= " . $until]
        ),
            true, array("signalId", "time", "value"));
    }

    /**
     * Returns the signals for this graph
     *
     * @return GraphSignal[] the signals for this graph
     * @throws \Exception if the database access fails
     */
    public function getSignals()
    {
        return $this->signalRepo->query(array('graphId' => "= " . $this->id));
    }

    public function jsonSerialize()
    {
        $baseArray = parent::jsonSerialize();
        $baseArray['theme'] = $this->theme;
        $baseArray['type'] = $this->type;
        $baseArray['timeMode'] = $this->timeMode;
        $baseArray['timeParam1'] = $this->timeParam1;
        $baseArray['timeParam2'] = $this->timeParam2;
        $baseArray['width'] = $this->width;
        $baseArray['height'] = $this->height;
        $baseArray['heightMode'] = $this->heightMode;
        $baseArray['distValue'] = $this->distValue;
        $baseArray['distType'] = $this->distType;
        return $baseArray;
    }
}
