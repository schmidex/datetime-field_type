<?php namespace Anomaly\DatetimeFieldType;

use Anomaly\Streams\Platform\Addon\FieldType\FieldTypeModifier;
use Anomaly\Streams\Platform\Model\Variables\VariablesTestingEntryModel;
use Carbon\Carbon;


/**
 * Class DatetimeFieldTypeModifier
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class DatetimeFieldTypeModifier extends FieldTypeModifier
{

    /**
     * The datetime field type.
     * This is for IDE hinting.
     *
     * @var DatetimeFieldType
     */
    protected $fieldType;

    /**
     * Create a new DatetimeFieldTypeModifier instance.
     *
     * @param DatetimeFieldType $fieldType
     */
    public function __construct(DatetimeFieldType $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * Modify the value.
     *
     * @param $value
     * @return Carbon|null
     */
    public function modify($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof \DateTime) {
            $value = $this->toCarbon($value, array_get($this->fieldType->getConfig(), 'timezone'));
        }

        if ($this->fieldType->config('mode') !== 'date') {
            $value->setTimezone(config('streams.datetime.database_timezone'));
        }

        return $value;
    }

    /**
     * Return a carbon instance
     * based on the value.
     *
     * @param              $value
     * @param  null $timezone
     * @return Carbon|null
     * @throws \Exception
     */
    protected function toCarbon($value, $timezone = null)
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        if (is_numeric($value)) {
            return (new Carbon())->createFromTimestamp($value, $timezone);
        }

        try {
            return (new Carbon())->createFromFormat($this->fieldType->getDatetimeFormat(), $value, $timezone);
        } catch (\Exception $e) {
            return (new Carbon())->createFromTimestamp(strtotime($value), $timezone);
        }
    }

    /**
     * Restore the value.
     *
     * @param $value
     * @return Carbon
     */
    public function restore($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof \DateTime) {
            try {
                $value = (new Carbon())->createFromFormat(
                    $this->fieldType->getStorageFormat(),
                    $value,
                    config('streams.datetime.database_timezone')
                );
            } catch (\Exception $e) {
                $value = (new Carbon())->createFromTimestamp(
                    strtotime($value),
                    config('streams.datetime.database_timezone')
                );
            }
        }

        if ($this->fieldType->config('mode') !== 'date') {
            $value->setTimezone(array_get($this->fieldType->getConfig(), 'timezone'));
        }

        return $value;
    }
}
