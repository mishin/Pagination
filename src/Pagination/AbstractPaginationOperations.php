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

    abstract public function getCurrentPageFirstItem();
    abstract public function getCurrentPageLastItem();


    // --------------------------------------------------------------------------

    /**
     * Render a small HTML pagination control.
     *
     * @return string
     *
     * @api
     */
    public function renderCompactPaging()
    {
        $html = '';

        if ($this->getNumPages() > 1) {
            $html .= $this->isItemsPerPageUsed
                ? sprintf('%s<!-- paging controls -->%s<div class="%s">%s', "\n", "\n", 'paging-container', "\n")
                : sprintf('%s<!-- paging controls -->%s<div class="%s">%s', "\n", "\n", 'paging-container-no-show-records', "\n");

            $html .= $this->getPrevUrl()
                ? sprintf('<span class="fl"><a class="btn btn-default" href="%s" tabindex="90" title="Select the next page" type="button">%s</a></span>%s', str_replace(['"'], ['%22'], $this->getPrevUrl()), static::NAVIGATION_ARROW_PREV, "\n")
                : sprintf('<span class="fl"><a class="btn btn-default" href="%s" tabindex="90" title="Select the previous page" type="button">%s</a></span>%s', '#', static::NAVIGATION_ARROW_PREV, "\n");

            $html .= sprintf('<select class="form-control paging-select" tabindex="91" title="Jump to a selected page">%s', "\n");

            foreach ($this->renderAsArray() as $render) {
                if ($render['pageUrl']) {
                    $html .= '    <option value="' . str_replace(['"'], ['%22'], $render['pageUrl']) . '"';
                    $html .= $render['isCurrentPage'] ? ' selected="selected">' : '>';
                    $html .= 'Page ' . $render['pageNumber'] . '</option>' . "\n";

                } else {
                    $html .= '    <option disabled>' . $render['pageNumber'] . '</option>' . "\n";
                }
            }

            $html .= '</select>' . "\n";

            $html .= $this->getNextUrl()
                ? sprintf('<span class="fl"><a class="btn btn-default" href="%s" tabindex="92" title="Select the next page" type="button">%s</a></span>%s', str_replace(['"'], ['%22'], $this->getNextUrl()), static::NAVIGATION_ARROW_NEXT, "\n")
                : sprintf('<span class="fl"><a class="btn btn-default" href="%s" tabindex="92" title="Select the next page" type="button">%s</a></span>%s', '#', static::NAVIGATION_ARROW_NEXT, "\n");

            if ($this->isItemsPerPageUsed) {
                $html .= '<button class="button secondary" id="button-pagination-show" name="button" type="button" tabindex="93" title="Show records per page" value="pagination-show">Show</button>' . "\n";
                $html .= sprintf('<input class="input-paginator-items-per-page" id="paginator-items-per-page" name="paginator-items-per-page" type="text" maxlength="5" tabindex="94" title="Provide the number of records per page" value="%s">', $this->itemsPerPage);
            }

            $html .= "</div>\n<!-- /paging controls -->";

        } else {
            $html .= $this->isItemsPerPageUsed
                ? sprintf('%s<!-- paging controls -->%s<div class="%s">%s', "\n", "\n", 'paging-container', "\n")
                : sprintf('%s<!-- paging controls -->%s<div class="%s">%s', "\n", "\n", 'paging-container-no-show-records', "\n");

            $html .= sprintf('<span class="fl"><a class="btn btn-default no-pe" href="%s" tabindex="90" title="Select the previous page" type="button">%s</a></span>%s', '#', static::NAVIGATION_ARROW_PREV, "\n");
            $html .= sprintf('<select class="form-control paging-select" tabindex="91" title="Jump to a selected page">%s    <option value="%s" %s>Page 1</option>%s</select>', "\n", str_replace(['"'], ['%22'], $this->getPageUrl(1)), 'selected="selected"', "\n", "\n");
            $html .= sprintf('<span class="fl"><a class="btn btn-default no-pe" href="%s" tabindex="92" title="Select the next page" type="button">%s</a></span>%s', '#', static::NAVIGATION_ARROW_NEXT, "\n");

            if ($this->isItemsPerPageUsed) {
                $html .= '<button class="button secondary" id="button-pagination-show" name="button" type="button" tabindex="93" value="pagination-show">Show</button>' . "\n";
                $html .= sprintf('<input class="input-paginator-items-per-page" id="paginator-items-per-page" name="paginator-items-per-page" type="text" maxlength="5" tabindex="95" title="Provide the number of records per page" value="%s">', $this->itemsPerPage);
            }

            $html .= "</div>\n<!-- /paging controls -->";
        }

        /* comment: jQuery pagination in /sso/1/assets/js/vendor/ucsdmath-functions.min.js */

        return $html;
    }

    // --------------------------------------------------------------------------

    /**
     * Render a long HTML pagination control.
     *
     * @return string
     *
     * @api
     */
    public function renderLargePaging()
    {
        if ($this->pageCount <= 1) {
            return '';
        }

        $html = '<ul class="pagination">';

        if ($this->getPrevUrl()) {
            $html .= sprintf('<li><a href="%s">%s</a></li>%s', $this->getPrevUrl(), static::NAVIGATION_ARROW_PREV, "\n");
        }

        foreach ($this->renderAsArray() as $render) {
            if ($render['pageUrl']) {
                $html .= '<li' . ($render['isCurrentPage'] ? ' class="active"' : '') . '><a href="' . $render['pageUrl'] . '">' . $render['pageNumber'] . '</a></li>' . "\n";
            } else {
                $html .= sprintf('<li class="disabled"><span>%s</span></li>%s', $render['pageNumber'], "\n");
            }
        }

        if ($this->getNextUrl()) {
            $html .= sprintf('<li><a href="%s">%s</a></li>%s', $this->getNextUrl(), static::NAVIGATION_ARROW_NEXT, "\n");
        }

        $html .= "</ul>\n";

        return $html;
    }

    // --------------------------------------------------------------------------

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
    public function renderAsArray()
    {
        $pages = array();

        if ($this->pageCount <= 1) {
            return $pages;
        }

        if ($this->pageCount <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $this->pageCount; $i++) {
                $pages[] = $this->createPage($i, $i === (int) $this->currentPageNumber);
            }
        } else {
            /* Determine the sliding range, centered around the current page */
            $numberAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);

            if ((int) $this->currentPageNumber + $numberAdjacents > $this->pageCount) {
                $slidingStart = $this->pageCount - $this->maxPagesToShow + 2;

            } else {
                $slidingStart = (int) $this->currentPageNumber - $numberAdjacents;
            }

            if ($slidingStart < 2) {
                $slidingStart = 2;
            }

            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;

            if ($slidingEnd >= $this->pageCount) {
                $slidingEnd = $this->pageCount - 1;
            }

            /* Build the list of pages */
            $pages[] = $this->createPage(1, (int) $this->currentPageNumber === 1);

            if ($slidingStart > 2) {
                $pages[] = $this->renderPageEllipsis;
            }

            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i === (int) $this->currentPageNumber);
            }

            if ($slidingEnd < $this->pageCount - 1) {
                $pages[] = $this->renderPageEllipsis;
            }

            $pages[] = $this->createPage($this->pageCount, (int) $this->currentPageNumber === $this->pageCount);
        }

        return $pages;
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
