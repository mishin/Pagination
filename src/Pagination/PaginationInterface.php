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

namespace UCSDMath\Pagination;

/**
 * PaginationInterface is the interface implemented by all Pagination classes.
 *
 * Method list: (+) @api.
 *
 * @author Daryl Eisner <deisner@ucsd.edu>
 *
 * @api
 */
interface PaginationInterface
{
    /**
     * Constants.
     */
    const BASE_PAGE = 1;
    const CHARSET = 'UTF-8';
    const REQUIRED_PHP_VERSION = '7.0.0';
    const PAGE_PLACEHOLDER   = '(:page)';
    const ROWS_PLACEHOLDER   = '(:rows)';
    const SORT_PLACEHOLDER   = '(:sort)';
    const SEARCH_PLACEHOLDER = '(:search)';
    const TITLE_PREV = 'Select the next page';
    const TITLE_NEXT = 'Select the previous page';
    const NAVIGATION_ARROW_PREV = '&#10094;&#160;Prev';
    const NAVIGATION_ARROW_NEXT = 'Next&#160;&#10095;';
    const NAVIGATION_ELLIPSES   = '&#183;&#160;&#183;&#160;&#183;';

    //--------------------------------------------------------------------------

    /**
     * Recalculates any updated settings parameter.
     *
     * @param array $settings  A list of per page settings.
     *
     * @return PaginationInterface The current interface
     *
     * @throws \InvalidArgumentException if $settings is null.
     *
     * @api
     */
    public function recalculate(array $settings): PaginationInterface;

    //--------------------------------------------------------------------------

    /**
     * Get the calculated page count.
     *
     * @return int
     *
     * @api
     */
    public function getPageCount(): int;

    //--------------------------------------------------------------------------

    /**
     * Set the maximum pages to display.
     *
     * @param int $maxPagesToShow  A number of pages to display.
     *
     * @return PaginationInterface The current interface
     *
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     *
     * @api
     */
    public function setMaxPagesToShow(int $maxPagesToShow): PaginationInterface;

    //--------------------------------------------------------------------------

    /**
     * Get the maximum pages to display.
     *
     * @return int
     *
     * @api
     */
    public function getMaxPagesToShow(): int;

    //--------------------------------------------------------------------------

    /**
     * Get the limit per page offset (for SQL LIMIT statement).
     *
     * @return array
     *
     * @api
     */
    public function getLimitPerPageOffset(\Closure $overridePerPageOffset = null, int $newPage = null): array;

    //--------------------------------------------------------------------------

    /**
     * Set the current page number.
     *
     * @param int $currentPageNumber  A current page number.
     *
     * @return PaginationInterface The current interface
     *
     * @api
     */
    public function setCurrentPageNumber(int $currentPageNumber = null): PaginationInterface;

    //--------------------------------------------------------------------------

    /**
     * Get the current page number.
     *
     * @return int
     *
     * @api
     */
    public function getCurrentPageNumber(): int;

    //--------------------------------------------------------------------------

    /**
     * Set the number of items (records) per page.
     *
     * @param int $itemsPerPage  A number of items per page
     *
     * @return PaginationInterface The current interface
     *
     * @api
     */
    public function setItemsPerPage(int $itemsPerPage): PaginationInterface;

    //--------------------------------------------------------------------------

    /**
     * Get the number of items (records) per page.
     *
     * @return int
     *
     * @api
     */
    public function getItemsPerPage();

    //--------------------------------------------------------------------------

    /**
     * Set the total number of records in total.
     *
     * @param int $totalItems  A number of total records in database
     *
     * @return PaginationInterface The current interface
     *
     * @api
     */
    public function setTotalItems(int $totalItems): PaginationInterface;

    //--------------------------------------------------------------------------

    /**
     * Get the number of items in database.
     *
     * @return int
     *
     * @api
     */
    public function getTotalItems(): int;

    //--------------------------------------------------------------------------

    /**
     * Get the number of pages.
     *
     * @return int
     *
     * @api
     */
    public function getNumPages(): int;

    //--------------------------------------------------------------------------

    /**
     * Set the url pattern for rendering pagination (scheme).
     *
     * @param string $urlPattern  A base SEO url pattern
     *
     * @return PaginationInterface The current interface
     *
     * @api
     */
    public function setUrlPattern(string $urlPattern): PaginationInterface;

    //--------------------------------------------------------------------------

    /**
     * Get the assigned url pattern.
     *
     * @return string
     *
     * @api
     */
    public function getUrlPattern(): string;

    //--------------------------------------------------------------------------

    /**
     * Get the page url.
     *
     * @param int $pageNumber  A page number for the url pattern
     *
     * @return string
     *
     * @api
     */
    public function getPageUrl($pageNumber);

    //--------------------------------------------------------------------------

    /**
     * Get the next page number.
     *
     * @return int
     *
     * @api
     */
    public function getNextPage();

    //--------------------------------------------------------------------------

    /**
     * Get the previous page number.
     *
     * @return int
     *
     * @api
     */
    public function getPrevPage();

    //--------------------------------------------------------------------------

    /**
     * Get the next page url.
     *
     * @return string|null
     *
     * @api
     */
    public function getNextUrl();

    //--------------------------------------------------------------------------

    /**
     * Get the previous page url.
     *
     * @return string|null
     *
     * @api
     */
    public function getPrevUrl();

    //--------------------------------------------------------------------------

    /**
     * Render the pagination via data array.
     *
     * Example:
     *
     * array(
     *     array ('pageNumber' => 1,     'pageUrl' => '/personnel/page-1/',  'isCurrentPage' => false),
     *     array ('pageNumber' => '...', 'pageUrl' => null,                  'isCurrentPage' => false),
     *     array ('pageNumber' => 7,     'pageUrl' => '/personnel/page-7/',  'isCurrentPage' => false),
     *     array ('pageNumber' => 8,     'pageUrl' => '/personnel/page-8/',  'isCurrentPage' => false),
     *     array ('pageNumber' => 9,     'pageUrl' => '/personnel/page-9/',  'isCurrentPage' => true ),
     *     array ('pageNumber' => 10,    'pageUrl' => '/personnel/page-10/', 'isCurrentPage' => false),
     *     array ('pageNumber' => 11,    'pageUrl' => '/personnel/page-11/', 'isCurrentPage' => false),
     *     array ('pageNumber' => '...', 'pageUrl' => null,                  'isCurrentPage' => false),
     *     array ('pageNumber' => 18,    'pageUrl' => '/personnel/page-18/', 'isCurrentPage' => false),
     * );
     *
     * @return array
     *
     * @api
     */
    public function renderAsArray();

    //--------------------------------------------------------------------------

    /**
     * Render a small HTML pagination control.
     *
     * @return string
     *
     * @api
     */
    public function renderCompactPaging();

    //--------------------------------------------------------------------------

    /**
     * Render a long HTML pagination control.
     *
     * @return string
     *
     * @api
     */
    public function renderLargePaging();

    //--------------------------------------------------------------------------

    /**
     * Get the next page number.
     *
     * @return int
     *
     * @api
     */
    public function getCurrentPageFirstItem();

    //--------------------------------------------------------------------------

    /**
     * Get the last item for the current page.
     *
     * @return int
     *
     * @api
     */
    public function getCurrentPageLastItem();

    //--------------------------------------------------------------------------
}
