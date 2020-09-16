<?php

declare(strict_types=1);

namespace Avasil\ClamAv;

/**
 * Interface ResultInterface.
 */
interface ResultInterface
{
    /**
     * Return true if no virus was found.
     *
     * @return bool
     */
    public function isClean();

    /**
     * Return true if a virus was found.
     *
     * @return bool
     */
    public function isInfected();

    /**
     * Returns the list of infected files and virus information.
     *
     * @return array
     */
    public function getInfected();

    /**
     * Returns the scanned resource.
     *
     * @return string
     */
    public function getTarget();
}
