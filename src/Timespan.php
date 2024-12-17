<?php

namespace WallaceMaxters\Timespan;

use DateTimeInterface;
use JsonSerializable;
use Stringable;

/**
 * The Timespan class
 *
 * @author Wallace de Souza Vizerra <wallacemaxters@gmail.com>
 * */

class Timespan implements JsonSerializable, Stringable
{
    public const DEFAULT_FORMAT        = '%r%h:%i:%s';
    public const TIME_WITH_SIGN_FORMAT = '%R%h:%i:%s';

    public float $seconds = 0;

    public function __construct(float $hours = 0, float $minutes = 0, float $seconds = 0)
    {
        $this->setTime($hours, $minutes, $seconds);
    }

    public function setTime(float $hours = 0, float $minutes = 0, float $seconds = 0): static
    {
        return $this->setSeconds(
            ($hours * 3600) + (60 * $minutes) + $seconds
        );
    }

    /**
     * Set Seconds
     *
     * @param integer $seconds
     * @return self
     */
    public function setSeconds(float $seconds): static
    {
        $this->seconds = $seconds;

        return $this;
    }

    public function setMinutes(float $minutes): static
    {
        return $this->setTime(0, $minutes, 0);
    }


    public function setHours(float $hours): static
    {
        return $this->setTime($hours, 0, 0);
    }

    public function addSeconds(float $seconds): static
    {
        $this->seconds += $seconds;
        
        return $this;
    }

    public function addMinutes(float $minutes): static
    {
        return $this->setTime(0, $minutes, $this->seconds);
    }

    public function addHours(float $hours): static
    {
        return $this->setTime($hours, 0, $this->seconds);
    }

    /**
     * Gets the time as minutes
     */
    public function asMinutes(): float
    {
        return $this->seconds / 60;
    }

    /**
     * Get as hours
     */
    public function asHours(): float
    {
        return $this->seconds / 3600;
    }

    /**
     * Turns the time into negative
     */
    public function negative()
    {
        return $this->setSeconds(-$this->seconds);
    }

    /**
     * Gets a formatted time 
     */
    public function format(string $format = self::DEFAULT_FORMAT): string
    {
        return strtr($format, Parser::replacementsFromTimespan($this));
    }

    /**
     * Returns a string from the default Timespan format 
     */
    public function __toString(): string
    {
        return $this->format();
    }

    /**
     * JsonSerialize interface implementation
     *
     * @return string
     */
    public function jsonSerialize(): string
    {
        return $this->format();
    }

    /**
     * Creates a new time based on diff with another Timespan
     */
    public function diff(self $time, bool $absolute = true): static
    {
        $seconds = $time->seconds - $this->seconds;

        return new static(0, 0, $absolute ? abs($seconds) : $seconds);
    }

    /**
     * Returns if the time is negative
     */
    public function isNegative(): bool
    {
        return $this->seconds < 0;
    }


    public function add(float $hours = 0, float $minutes = 0, float $seconds = 0): static
    {
        return $this->addHours($hours)->addMinutes($minutes)->addSeconds($seconds);
    }

    /**
     * Get all units of time as array
     *
     * @return array
     */
    public function getUnits(): array
    {
        $seconds = abs($this->seconds);

        $time['hours'] = floor($seconds / 3600);

        $time['minutes'] = floor(($seconds - ($time['hours'] * 3600)) / 60);

        $time['seconds'] = floor($seconds % 60);

        $time['total_minutes'] = ($time['hours'] * 60) + $time['minutes'];

        return $time;
    }

    /**
     * Checks if is a zero time
     */
    public function isEmpty(): bool
    {
        return $this->seconds == 0;
    }

    /**
     * Add amount of time to Timespan from a strtotime string
     */
    public function addFromString(string $strtime): static
    {
        return $this->addSeconds(strtotime($strtime, 0));
    }

    /**
     * Creates a Timespan instance from a strtotime string
     */
    public static function createFromString(string $strtime): static
    {
        return (new static)->addFromString($strtime);
    }

    /**
     * Create a Timespan from a specific format
     */
    public static function createFromFormat(string $format, string $value): static
    {
        return Parser::createTimespanFromFormat($format, $value);
    }

    /**
     * Creates Timespan from  a diff of DateTimes
     */
    public static function createFromDateDiff(DateTimeInterface $date1, DateTimeInterface $date2): static
    {
        $seconds = $date2->getTimestamp() - $date1->getTimestamp();

        return new static(0, 0, $seconds);
    }

    public function sum(self ...$timespans): static
    {
        foreach ($timespans as $timespan) {
            $this->seconds += $timespan->seconds;
        }

        return $this;
    }
}
