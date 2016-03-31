<?php

/*
 * This file is part of the UCSDMath package.
 *
 * (c) UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace UCSDMath\Pagination;

/**
 * Pagination is the default implementation of {@link PaginationInterface} which
 * provides routine Paginator methods that are commonly used throughout the framework.
 *
 * Paginator provides a process of dividing (content) into discrete pages that are
 * acceptable or desirable to the enduser.
 *
 * Important considerations in writing this class are:
 *    - SEO Friendly URLS
 *    - Dynamic search results (sticky or hold state)
 *    - Standard scheme for Front Controllers
 *    - Provide options for template generator (e.g., Twig, Plates, Smarty)
 *    - Provided via a data structure (a raw data option)
 *
 * Technically, for pagination to work, all is needed is the page number of the current set.
 *
 *    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
 *    $itemsPerPage = 4;
 *
 *    $totalItems = "SELECT COUNT(*) FROM personnel;
 *    $rowCount   = "SELECT COUNT(*) FROM personnel where group = 'faculty';
 *
 *    if ($totalItems === 0) { print 'No records exist in the database.';}
 *    if ($rowCount === 0)   { print 'No records found in database with you exact match.';}
 *
 *    $pageCount = (int) ceil($rowCount / $itemsPerPage);
 *
 *    // range error; we could just set page = 1
 *    if ($page > $pageCount) {$page = 1;}
 *
 *    $offset = ($page - 1) * $itemsPerPage;
 *    $sql = "SELECT * FROM personnel where (group = 'faculty') (ORDER BY lastname, firstname) LIMIT " . $offset . "," . $itemsPerPage;
 *
 *    SQL looks like:  SELECT * FROM personnel LIMIT 4,4
 *
 * Consider some common url patterns:
 *    - /sso/1/personnel/(:page)/(:rows)/(:sort)/
 *    - /sso/1/personnel/quick-search/(:page)/(:rows)/(:search)/(:sort)/
 *    - /sso/1/personnel/edit-search/page-(:page)/show-(:rows)/(:search)/(:sort)/
 *    - /sso/1/personnel/edit-record/page-(:page)/
 *
 * Method list: (+) @api, (-) protected or private visibility.
 *
 * The notation below illustrates visibility: (+) @api, (-) protected or private.
 *
 * (+) PaginationInterface __construct();
 *
 * @author Daryl Eisner <deisner@ucsd.edu>
 *
 * @api
 */
class Paginator extends AbstractPagination implements PaginationInterface
{
    /**
     * Constants.
     *
     * @var string VERSION  A version number
     *
     * @api
     */
    const VERSION = '1.7.0';

    // --------------------------------------------------------------------------

    /**
     * Properties.
     */

    // --------------------------------------------------------------------------

    /**
     * Constructor.
     *
     * @param array  $settings  A associated list of page settings.
     *
     * @api
     */
    public function __construct(array $settings = null)
    {
        parent::__construct($settings);
    }

    // --------------------------------------------------------------------------
}
