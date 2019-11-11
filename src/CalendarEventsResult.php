<?php

namespace Reedware\NovaCalendarEventsMetric;

use JsonSerializable;

class CalendarEventsResult implements JsonSerializable
{
    /**
     * The event data of the result.
     *
     * @var array
     */
    public $events = [];

    /**
     * The metric value prefix.
     *
     * @var string
     */
    public $prefix;

    /**
     * The metric value suffix.
     *
     * @var string
     */
    public $suffix;

    /**
     * Whether to run inflection on the suffix.
     *
     * @var bool
     */
    public $suffixInflection = true;

    /**
     * The metric value formatting.
     *
     * @var string
     */
    public $format;

    /**
     * Creates a new calendar events result instance.
     *
     * @param  string|null  $events
     *
     * @return void
     */
    public function __construct($events = null)
    {
        $this->events = $events;
    }

    /**
     * Set the primary result amount for the trend.
     *
     * @param  string|null  $events
     *
     * @return $this
     */
    public function events($events = null)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Indicate that the metric represents a dollar value.
     *
     * @param  string  $symbol
     * @return $this
     */
    public function dollars($symbol = '$')
    {
        return $this->prefix($symbol);
    }

    /**
     * Indicate that the metric represents a euro value.
     *
     * @param  string  $symbol
     * @return $this
     */
    public function euros($symbol = '€')
    {
        return $this->prefix($symbol);
    }

    /**
     * Set the metric value prefix.
     *
     * @param  string  $prefix
     * @return $this
     */
    public function prefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set the metric value suffix.
     *
     * @param  string  $suffix
     * @return $this
     */
    public function suffix($suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }

    /**
     * Don't apply suffix inflections.
     *
     * @return $this
     */
    public function withoutSuffixInflection()
    {
        $this->suffixInflection = false;

        return $this;
    }

    /**
     * Set the metric value formatting.
     *
     * @param  string  $format
     * @return $this
     */
    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Prepare the metric result for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'events' => $this->events,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'suffixInflection' => $this->suffixInflection,
            'format' => $this->format,
        ];
    }
}
