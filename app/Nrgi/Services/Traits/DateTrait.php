<?php
namespace App\Nrgi\Services\Traits;

/**
 * Class DateTrait
 */
trait DateTrait
{
    /**
     * Get contract Create Date
     *
     * @param string $format
     *
     * @return string
     */
    public function createdDate($format = '')
    {
        if ($format) {
            return translate_date($this->created_at->format($format));
        }

        return $this->created_at;
    }

    /**
     * Get contract Updated Date
     *
     * @param string $format
     *
     * @return string
     */
    public function updatedDate($format = '')
    {
        if ($format) {
            return translate_date($this->updated_at->format($format));
        }

        return $this->updated_at;
    }
}