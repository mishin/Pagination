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
 * AbstractPaginationOperations is the default implementation of {@link AbstractPaginationOperationsInterface} which
 * provides routine database methods that are commonly used in frameworks.
 *
 * Method noted as: (+) @api, (-) protected or private visibility.
 *
 * (+) PaginationInterface __construct();
 *
 * @author Daryl Eisner <deisner@ucsd.edu>
 *
 * @api
 */
abstract class AbstractPaginationOperations extends AbstractPagination
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
     * @param array $settings A associated list of page settings.
     *
     * @api
     */
    public function __construct(array $settings = null)
    {
        parent::__construct($settings);
    }

    abstract public function renderAsArray();
    abstract public function renderLargePaging();
    abstract public function renderCompactPaging();

    // --------------------------------------------------------------------------

    /**
     * Get the next page number.
     *
     * @return int
     *
     * @api
     */
    public function getCurrentPageFirstItem()
    {
        $first = ((int) $this->currentPageNumber - 1) * (int) $this->itemsPerPage + 1;

        return $first > (int) $this->totalItems ? null : $first;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the last item for the current page.
     *
     * @return int
     *
     * @api
     */
    public function getCurrentPageLastItem()
    {
        $first = $this->getCurrentPageFirstItem();

        if ($first === null) {
            return null;
        }

        $last = $first + (int) $this->itemsPerPage - 1;

        return ($last > (int) $this->totalItems) ? (int) $this->totalItems : $last;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the maximum pages to display.
     *
     * @param int $maxPagesToShow  A number of pages to display.
     *
     * @return PaginationInterface
     *
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     *
     * @api
     */
    public function setMaxPagesToShow(int $maxPagesToShow): PaginationInterface
    {
        if ((int) $maxPagesToShow < 3) {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');
        }

        $this->setProperty('maxPagesToShow', (int) $maxPagesToShow);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the maximum pages to display.
     *
     * @return int
     *
     * @api
     */
    public function getMaxPagesToShow(): int
    {
        return (int) $this->getProperty('maxPagesToShow');
    }

    // --------------------------------------------------------------------------

    /**
     * Set the current page number.
     *
     * @param int $currentPageNumber  A current page number.
     *
     * @return PaginationInterface
     *
     * @api
     */
    public function setCurrentPageNumber(int $currentPageNumber = null): PaginationInterface
    {
        if (null !== $currentPageNumber) {
            $this->setProperty('currentPageNumber', $currentPageNumber);
        }

        $this->normalizePageCounts();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the current page number.
     *
     * @return int
     *
     * @api
     */
    public function getCurrentPageNumber(): int
    {
        return $this->currentPageNumber > $this->pageCount ? static::BASE_PAGE : (int) $this->currentPageNumber;
    }

    // --------------------------------------------------------------------------

    /**
     * Set the number of items (records) per page.
     *
     * @param int $itemsPerPage  A number of items per page
     *
     * @return PaginationInterface
     *
     * @api
     */
    public function setItemsPerPage(int $itemsPerPage): PaginationInterface
    {
        $this->setProperty('itemsPerPage', $itemsPerPage);
        $this->updateNumPages();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the number of items (records) per page.
     *
     * @return int
     *
     * @api
     */
    public function getItemsPerPage(): int
    {
        return (int) $this->getProperty('itemsPerPage');
    }

    // --------------------------------------------------------------------------

    /**
     * Set the total number of records in total.
     *
     * @param int $totalItems  A number of total records in database
     *
     * @return PaginationInterface
     *
     * @api
     */
    public function setTotalItems(int $totalItems): PaginationInterface
    {
        $this->setProperty('totalItems', $totalItems);
        $this->updateNumPages();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the number of items in database.
     *
     * @return int
     *
     * @api
     */
    public function getTotalItems(): int
    {
        return (int) $this->getProperty('totalItems');
    }

    // --------------------------------------------------------------------------

    /**
     * Get the number of pages.
     *
     * @return int
     *
     * @api
     */
    public function getNumPages(): int
    {
        return (int) $this->getProperty('pageCount');
    }

    // --------------------------------------------------------------------------

    /**
     * Get the next page number.
     *
     * @return int
     *
     * @api
     */
    public function getNextPage()
    {
        return (int) $this->currentPageNumber < $this->pageCount
            ? (int) $this->currentPageNumber + 1
            : null;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the previous page number.
     *
     * @return int
     *
     * @api
     */
    public function getPrevPage()
    {
        return (int) $this->currentPageNumber > 1
            ? (int) $this->currentPageNumber - 1
            : null;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the next page url.
     *
     * @return string|null
     *
     * @api
     */
    public function getNextUrl()
    {
        return $this->getNextPage()
            ? $this->getPageUrl($this->getNextPage())
            : null;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the previous page url.
     *
     * @return string|null
     *
     * @api
     */
    public function getPrevUrl()
    {
        return $this->getPrevPage()
            ? $this->getPageUrl($this->getPrevPage())
            : null;
    }

    // --------------------------------------------------------------------------
}
