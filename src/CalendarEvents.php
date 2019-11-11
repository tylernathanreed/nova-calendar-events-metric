<?php

namespace Reedware\NovaCalendarEventsMetric;

use DateTime;
use Cake\Chronos\Chronos;
use InvalidArgumentException;
use Laravel\Nova\Metrics\RangedMetric;
use Illuminate\Database\Eloquent\Builder;

abstract class CalendarEvents extends RangedMetric
{
    /**
     * The element's component.
     *
     * @var string
     */
    public $component = 'calendar-events-metric';

    /**
     * Whether or not the date range is futuristic.
     *
     * @var boolean
     */
    public $futuristic = false;

    /**
     * Indicated whether the metric should be refreshed when filters are changed.
     *
     * @var boolean
     */
    public $refreshWhenFilterChanged = false;

    /**
     * Create a new trend metric result.
     *
     * @param  string|null  $value
     *
     * @return \Reedware\NovaCalendarEventsCard\CalendarEventsResult
     */
    public function result($value = null)
    {
        return new CalendarEventsResult($value);
    }

    /**
     * Return a value result showing a aggregate over time.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Builder|string  $model
     * @param  string  $dateColumn
     * @param  \Closure|string  $subjectColumn
     * @param  \Closure|string  $descriptionColumn
     * @param  \Closure|string  $valueColumn
     *
     * @return \Reedware\NovaCalendarEventsCard\CalendarEventsResult
     */
    protected function list($request, $model, $dateColumn, $subjectColumn, $descriptionColumn = null, $valueColumn = null)
    {
        // Initialize the query
        $query = $model instanceof Builder ? $model : (new $model)->newQuery();

        // Determine the default values
        $dateColumn = $dateColumn ?? $query->getModel()->getCreatedAtColumn();

        // Determine the request values
        $timezone = $request->timezone;

        // Determine the date range
        $startingDate = $this->getCalendarStartingDate($request);
        $endingDate = $this->getCalendarEndingDate($request);

        // Apply the date range
        if(!is_null($startingDate) && !is_null($endingDate)) {
            $query->whereBetween($dateColumn, [$startingDate, $endingDate]);
        }

        // Determine the model results
        $results = $query->orderBy($dateColumn)->get();

        // Determine the resulting events
        $events = $results->map(function($result) use ($request, $dateColumn, $subjectColumn, $descriptionColumn, $valueColumn) {
            return [
                'key' => $result->getKey(),
                'date' => $this->resolveValue($result, $dateColumn)->format('Y-m-d'),
                'subject' => $this->resolveValue($result, $subjectColumn),
                'description' => $this->resolveValue($result, $descriptionColumn),
                'value' => $this->resolveValue($result, $valueColumn),
            ];
        })->all();

        return $this->result($events);
    }

    /**
     * Resolves the value from the specified resource using the given callback.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $resource
     * @param  \Closure|string|null                 $resolveCallback
     *
     * @return mixed
     */
    protected function resolveValue($resource, $resolveCallback)
    {
        // If the resolve callback is not defined, return null
        if(is_null($resolveCallback)) {
            return null;
        }

        // If the resolve callback is a string, resolve the value as an attribute
        if(is_string($resolveCallback)) {
            return $resource->getAttribute($resolveCallback);
        }

        // Invoke the resolve callback
        return $resolveCallback($resource);
    }

    /**
     * Determine the proper aggregate strating date.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Cake\Chronos\Chronos|null
     */
    protected function getCalendarStartingDate($request)
    {
        if(is_null($request->range)) {
            return null;
        }

        $now = Chronos::now();

        if($this->futuristic) {
            return $now->subDays(1);
        }

        return $now->subDays($request->range - 1)->setTime(0, 0);
    }

    /**
     * Determine the proper aggregate ending date.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Cake\Chronos\Chronos|null
     */
    protected function getCalendarEndingDate($request)
    {
        if(is_null($request->range)) {
            return null;
        }

        $now = Chronos::now();

        if(!$this->futuristic) {
            return $now->subDays(1);
        }

        return $now->addDays($request->range - 1)->setTime(0, 0);
    }

    /**
     * Sets whether or not the date range is future-looking.
     *
     * @param  boolean  $futuristic
     *
     * @return $this
     */
    public function futuristic($futuristic = true)
    {
        $this->futuristic = $futuristic;

        return $this;
    }

    /**
     * Prepare the metric for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_merge(parent::jsonSerialize(), [
            'refreshWhenFilterChanged' => $this->refreshWhenFilterChanged
        ]);
    }
}
