<?php

namespace homeserver\apiv1\Utilities;

class Convert
{

    /**
     * @param $time \DateTime the time object
     * @return string the date time as formatted string
     */
    public static function formatTime($time) {
        if ($time == null || $time->getTimestamp() <= 0) {
            return null;
        }
        return $time != null ? $time->format(\DateTime::ISO8601) : '';
    }

    /**
     * Converts a string or timestamp to a DateTime object
     *
     * @param $value string | int the dateTime as string or the timestamp
     * @return \DateTime the created DateTime object
     */
    public static function parseTime($value) {
        if (is_numeric($value)) {
            $dt = new \DateTime();
            $dt->setTimestamp($value);
            return $dt;
        } else {
            return new \DateTime($value);
        }
    }
}
