<?php

/*
 * This file is part of the UCSDMath package.
 *
 * Copyright 2016 UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace UCSDMath\Pagination\Exception;

use InvalidArgumentException;

/**
 * InvalidPageNumberException is the default implementation of {@link InvalidArgumentException} to
 * provide a base page number exception handling.
 *
 * @author Daryl Eisner <deisner@ucsd.edu>
 *
 * @api
 */
class InvalidPageNumberException extends \InvalidArgumentException
{
    // --------------------------------------------------------------------------
}
