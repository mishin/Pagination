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

use UCSDMath\Functions\ServiceFunctions;
use UCSDMath\Functions\ServiceFunctionsInterface;

/**
 * AbstractPagination provides an abstract base class implementation of {@link PaginationInterface}.
 * Primarily, this services the fundamental implementations for all Pagination classes.
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
 * (+) void __destruct();
 * (+) string __toString();
 * (+) object __call($callback, $parameters);
 * (+) array renderAsArray();
 * (+) string renderLargePaging();
 * (+) string renderCompactPaging();
 * (+) string getPageUrl($pageNumber);
 * (+) bool isValidPageNumber($page);
 * (+) int     getCurrentPageLastItem();
 * (+) int     getCurrentPageFirstItem();
 * (+) PaginationInterface recalculate(array $settings);
 * (+) array createPage($pageNumber, $isCurrentPage = false);
 * (+) PaginationInterface setRenderAsJson(\Closure $renderAsJson);
 * (+) PaginationInterface setLimitPerPageOffset(\Closure $limitPerPageOffset);
 * (+) array getLimitPerPageOffset(\Closure $overridePerPageOffset = null, $newPage = null);
 * (+) mixed getPrevUrl();
 * (+) int     getNextUrl();
 * (+) int     getNumPages();
 * (+) int     getNextPage();
 * (+) int     getPrevPage();
 * (+) int     getPageCount();
 * (+) string getUrlPattern();
 * (+) int     getTotalItems();
 * (+) int     getPageOffset();
 * (+) int     getItemsPerPage();
 * (+) int     getMaxPagesToShow();
 * (+) int     getCurrentPageNumber();
 * (+) PaginationInterface setPageCount();
 * (+) PaginationInterface setPageOffset();
 * (+) PaginationInterface setUrlPattern($urlPattern);
 * (+) PaginationInterface setTotalItems($totalItems);
 * (+) PaginationInterface setItemsPerPage($itemsPerPage);
 * (+) PaginationInterface setMaxPagesToShow($maxPagesToShow);
 * (+) PaginationInterface setCurrentPageNumber($currentPageNumber = null);
 *
 * @author Daryl Eisner <deisner@ucsd.edu>
 */
abstract class AbstractPagination implements PaginationInterface, ServiceFunctionsInterface
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
     *
     * @var    int                 $pageCount            A number of pages to render (e.g., a calculation) (e.g., 780)
     * @var    int                 $totalItems           A total number of found records in table (e.g., 8500)
     * @var    int                 $pageOffset           A interger used to define our SQL OFFSET (e.g., 60)
     * @var    string              $urlPattern           A default url with placeholders (e.g., '/sso/1/news/(:page)/(:rows)/(:search)/')
     * @var    string              $sortPattern          A default sort url pattern (:sort) (e.g., 'group-lastname-firstname')
     * @var    int                 $itemsPerPage         A display setting showing a number of records per page (e.g., 15)
     * @var    int                 $maxPagesToShow       A maximum number of pages for the <select> menu (e.g., 10)
     * @var    string              $searchPattern        A search pattern used in the url (:search) (e.g., 'dillon-or-drop')
     * @var    int                 $currentPageNumber    A current page number (e.g., 8)
     * @var    \Closure            $renderAsJson         A closure callback for encoding json data
     * @var    \Closure            $limitPerPageOffset   A closure callback for the limit offset
     * @var    bool                $isUrlPatternUsed     A boolean option that enables the pattern type
     * @var    bool                $isSortPatternUsed    A boolean option that enables the pattern type
     * @var    bool                $isItemsPerPageUsed   A boolean option that enables the pattern type
     * @var    bool                $isSearchPatternUsed  A boolean option that enables the pattern type
     * @var    array               $storageRegister      A set of validation stored data elements
     * @static PaginationInterface $instance             A PaginationInterface
     * @static int                 $objectCount          A PaginationInterface count
     */
    protected $pageCount = null;
    protected $totalItems = null;
    protected $pageOffset = null;
    protected $urlPattern = null;
    protected $sortPattern = null;
    protected $itemsPerPage = null;
    protected $maxPagesToShow = 10;
    protected $renderAsJson = null;
    protected $searchPattern = null;
    protected $currentPageNumber = null;
    protected $limitPerPageOffset = null;
    protected $isUrlPatternUsed = false;
    protected $isSortPatternUsed = false;
    protected $isItemsPerPageUsed = false;
    protected $isSearchPatternUsed = false;
    protected $storageRegister = array();
    protected static $instance = null;
    protected static $objectCount = 0;
    protected $renderPageEllipsis = [
        'pageUrl' => null,
        'isCurrentPage' => false,
        'pageNumber' => self::NAVIGATION_ELLIPSES,
    ];

    // --------------------------------------------------------------------------

    /**
     * Constructor.
     *
     * Business rules expected on setup:
     *    $this->itemsPerPage can never be < 1
     *    $this->maxPagesToShow can never be < 3
     *    $this->currentPageNumber can never be < 1
     *
     * @param array $settings  A list of page settings.
     *
     * @throws \LogicException on incorrect settings
     *
     * @api
     */
    public function __construct(array $settings)
    {
        $this->loadStartupSettings($settings);
        /* Callback to {$limitPerPageOffset} for Page Override. */
        $this->setLimitPerPageOffset(function($currentPageNumber) use (&$settings) {
            $offset = $this->setPageOffset()->getPageOffset() + ($currentPageNumber * $this->itemsPerPage);
            return [$offset, $this->itemsPerPage];
        });
        $this->setPageCount();
        $this->setCurrentPageNumber();
        static::$instance = $this;
        static::$objectCount++;
    }

    // --------------------------------------------------------------------------

    /**
     * Load Settings.
     *
     * @param string $settings A startup configuration setting.
     *
     * @return PaginationInterface
     *
     * @api
     */
    public function loadStartupSettings(array $settings): PaginationInterface
    {
        foreach ($settings as $key => $value) {
            if (property_exists($this, $key)) {
                $this->setProperty($key, $value);
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Destructor.
     *
     * @api
     */
    public function __destruct()
    {
        static::$objectCount--;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setLimitPerPageOffset(\Closure $limitPerPageOffset): PaginationInterface
    {
        $this->setProperty('limitPerPageOffset', $limitPerPageOffset);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setRenderAsJson(\Closure $renderAsJson): PaginationInterface
    {
        $this->setProperty('renderAsJson', $renderAsJson);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the limit per page offset (for SQL LIMIT statement).
     *
     * @return array
     *
     * @api
     */
    public function getLimitPerPageOffset(\Closure $overridePerPageOffset = null, $newPage = null)
    {
        $offset = $this->setPageOffset()->getPageOffset();

        return ($overridePerPageOffset instanceof \Closure)
            ? $overridePerPageOffset($newPage)
            : [$offset, $this->itemsPerPage];
    }

    // --------------------------------------------------------------------------

    /**
     * Forward to any callable, including anonymous functions
     * (or any instances of \Closure).
     *
     * @param string $callback    A named callable to be called.
     * @param mixed  $parameters  A parameter set to be passed to the callback (as an indexed array).
     *
     * @return mixed  the return value of the callback, or false on error.
     *
     * @api
     */
    public function __call($callback, $parameters)
    {
        return call_user_func_array($this->$callback, $parameters);
    }

    // --------------------------------------------------------------------------

    /**
     * Recalculates any updated settings parameter.
     *
     * Buisiness rules expected on setup:
     *    $this->itemsPerPage can never be < 1
     *    $this->maxPagesToShow can never be < 3
     *    $this->currentPageNumber can never be < 1
     *
     * @param array $settings  A list of per page settings.
     *
     * @return PaginationInterface
     *
     * @throws \InvalidArgumentException if $settings is null.
     *
     * @api
     */
    public function recalculate(array $settings): PaginationInterface
    {
        $this->limitPerPageOffset instanceof \Closure
            ?: $this->throwExceptionError([__METHOD__, __CLASS__, 'LimitPerPageOffset callback not found, set it using Paginator::setLimitPerPageOffset()', 'A105']);
        foreach ($settings as $key => $value) {
            if (property_exists($this, $key)) {
                $this->setProperty($key, $value);
            }
        }
        $this->setPageCount();
        $this->setCurrentPageNumber();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the page url.
     *
     * @param int $pageNumber  A page number for the url pattern
     *
     * @return string
     *
     * @api
     */
    public function getPageUrl($pageNumber)
    {
        $url = str_replace(self::PAGE_PLACEHOLDER, (int) $pageNumber, $this->urlPattern);

        $url = $this->isItemsPerPageUsed
            ? str_replace(self::ROWS_PLACEHOLDER, (int) $this->itemsPerPage, $url)
            : str_replace(self::ROWS_PLACEHOLDER . '/', null, $url);

        $url = $this->isSortPatternUsed
            ? str_replace(self::SORT_PLACEHOLDER, (string) $this->sortPattern, $url)
            : str_replace(self::SORT_PLACEHOLDER . '/', null, $url);

        $url = $this->isSearchPatternUsed
            ? str_replace(self::SEARCH_PLACEHOLDER, (string) $this->searchPattern, $url)
            : str_replace(self::SEARCH_PLACEHOLDER . '/', null, $url);

        return $url;
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
     * Create a page data structure.
     *
     * @param int  $pageNumber     A page number for data structure
     * @param bool $isCurrentPage  A boolean if is the current page
     *
     * @return array
     */
    protected function createPage($pageNumber, $isCurrentPage = false)
    {
        return [
            'pageNumber'    => (int) $pageNumber,
            'pageUrl'       => $this->getPageUrl($pageNumber),
            'isCurrentPage' => (bool) $isCurrentPage,
        ];
    }

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
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->renderCompactPaging();
    }

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
     * Set the page offset.
     *
     * @return PaginationInterface
     *
     * @api
     */
    protected function setPageOffset(): PaginationInterface
    {
        $this->normalizePageCounts();
        $this->setProperty('pageOffset', abs(intval($this->currentPageNumber * $this->itemsPerPage - $this->itemsPerPage)));

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Normalize and check page counts.
     *
     * @return PaginationInterface
     *
     * @api
     */
    protected function normalizePageCounts(): PaginationInterface
    {
        if ($this->currentPageNumber > $this->pageCount
            || $this->currentPageNumber < static::BASE_PAGE
        ) {
            $this->setProperty('currentPageNumber', static::BASE_PAGE);
        }

        if ($this->itemsPerPage < 1) {
            $this->setProperty('itemsPerPage', static::BASE_PAGE);
        }

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the page offset.
     *
     * @return int
     *
     * @api
     */
    protected function getPageOffset()
    {
        return (int) $this->getProperty('pageOffset');
    }

    // --------------------------------------------------------------------------

    /**
     * Calculate the number of pages.
     *
     * @return bool
     */
    protected function setPageCount(): PaginationInterface
    {
        ((int) $this->itemsPerPage === 0)
            ? $this->setProperty('pageCount', 0)
            : $this->setProperty('pageCount', (int) ceil((int) $this->totalItems / (int) $this->itemsPerPage));

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the calculated page count.
     *
     * @return int
     *
     * @api
     */
    public function getPageCount(): int
    {
        return (int) $this->getProperty('pageCount');
    }

    // --------------------------------------------------------------------------

    /**
     * Determine if the given value is a valid page number.
     *
     * @param int  $page  A page number.
     *
     * @return bool
     */
    protected function isValidPageNumber($page)
    {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
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

    /**
     * Set the url pattern for rendering pagination (scheme).
     *
     * @param string $urlPattern  A base SEO url pattern
     *
     * @return PaginationInterface
     *
     * @api
     */
    public function setUrlPattern($urlPattern): PaginationInterface
    {
        $this->setProperty('urlPattern', (string) $urlPattern);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Get the assigned url pattern.
     *
     * @return string
     *
     * @api
     */
    public function getUrlPattern()
    {
        return $this->getProperty('urlPattern');
    }

    // --------------------------------------------------------------------------

    /**
     * Method implementations inserted:
     *
     * Method noted as: (+) @api, (-) protected or private visibility.
     *
     * (+) array all();
     * (+) object init();
     * (+) string version();
     * (+) bool isString($str);
     * (+) bool has(string $key);
     * (+) string getClassName();
     * (+) int getInstanceCount();
     * (+) bool isValidEmail($email);
     * (+) array getClassInterfaces();
     * (+) mixed getConst(string $key);
     * (+) bool isValidUuid(string $uuid);
     * (+) bool isValidSHA512(string $hash);
     * (+) mixed __call($callback, $parameters);
     * (+) bool doesFunctionExist($functionName);
     * (+) bool isStringKey(string $str, array $keys);
     * (+) mixed get(string $key, string $subkey = null);
     * (+) mixed getProperty(string $name, string $key = null);
     * (+) object set(string $key, $value, string $subkey = null);
     * (+) object setProperty(string $name, $value, string $key = null);
     * (-) \Exception throwExceptionError(array $error);
     * (-) \InvalidArgumentException throwInvalidArgumentExceptionError(array $error);
     */
    use ServiceFunctions;

    // --------------------------------------------------------------------------
}
