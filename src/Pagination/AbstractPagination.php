<?php
declare(strict_types=1);

/*
 * This file is part of the UCSDMath package.
 *
 * (c) UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
 * (+) boolean isValidPageNumber($page);
 * (+) integer getCurrentPageLastItem();
 * (+) integer getCurrentPageFirstItem();
 * (+) PaginationInterface recalculate(array $settings);
 * (+) array createPage($pageNumber, $isCurrentPage = false);
 * (+) PaginationInterface setRenderAsJson(\Closure $renderAsJson);
 * (+) PaginationInterface setLimitPerPageOffset(\Closure $limitPerPageOffset);
 * (+) array getLimitPerPageOffset(\Closure $overridePerPageOffset = null, $newPage = null);
 * (+) mixed getPrevUrl();
 * (+) integer getNextUrl();
 * (+) integer getNumPages();
 * (+) integer getNextPage();
 * (+) integer getPrevPage();
 * (+) integer getPageCount();
 * (+) string getUrlPattern();
 * (+) integer getTotalItems();
 * (+) integer getPageOffset();
 * (+) integer getItemsPerPage();
 * (+) integer getMaxPagesToShow();
 * (+) integer getCurrentPageNumber();
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
    const VERSION = '1.6.0';

    // --------------------------------------------------------------------------

    /**
     * Properties.
     *
     * @var    integer             $pageCount            A number of pages to render (e.g., a calculation) (e.g., 780)
     * @var    integer             $totalItems           A total number of found records in table (e.g., 8500)
     * @var    integer             $pageOffset           A interger used to define our SQL OFFSET (e.g., 60)
     * @var    string              $urlPattern           A default url with placeholders (e.g., '/sso/1/news/(:page)/(:rows)/(:search)/')
     * @var    string              $sortPattern          A default sort url pattern (:sort) (e.g., 'group-lastname-firstname')
     * @var    integer             $itemsPerPage         A display setting showing a number of records per page (e.g., 15)
     * @var    integer             $maxPagesToShow       A maximum number of pages for the <select> menu (e.g., 10)
     * @var    string              $searchPattern        A search pattern used in the url (:search) (e.g., 'dillon-or-drop')
     * @var    integer             $currentPageNumber    A current page number (e.g., 8)
     * @var    \Closure            $renderAsJson         A closure callback for encoding json data
     * @var    \Closure            $limitPerPageOffset   A closure callback for the limit offset
     * @var    boolean             $isUrlPatternUsed     A boolean option that enables the pattern type
     * @var    boolean             $isSortPatternUsed    A boolean option that enables the pattern type
     * @var    boolean             $isItemsPerPageUsed   A boolean option that enables the pattern type
     * @var    boolean             $isSearchPatternUsed  A boolean option that enables the pattern type
     * @var    array               $storageRegister      A set of validation stored data elements
     * @static PaginationInterface $instance             A PaginationInterface instance
     * @static integer             $objectCount          A PaginationInterface instance count
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
     * @param array $settings  A list of page settings.
     *
     * @throws \LogicException on incorrect settings
     *
     * @api
     */
    public function __construct(array $settings)
    {
        /**
         * Buisiness rules expected on setup:
         *
         *    $this->itemsPerPage can never be < 1
         *    $this->maxPagesToShow can never be < 3
         *    $this->currentPageNumber can never be < 1
         */
        foreach ($settings as $key => $value) {
            if (property_exists($this, $key)) {
                $this->setProperty($key, $value);
            }
        }

        /**
         * Callback to {$limitPerPageOffset} for Page Override.
         */
        $this->setLimitPerPageOffset(function ($currentPageNumber) use (&$settings) {
            $offset = $this->setPageOffset()->getPageOffset() + ($currentPageNumber * $this->itemsPerPage);
            return [$offset, $this->itemsPerPage];
        });

        /**
         * Callback to {$renderAsJson} for JSON Encoded Data Structures.
         */
        $this->setRenderAsJson(function () use (&$settings) {
            $this->setProperty('totalItems', (int) $settings['totalItems']);
            $this->setProperty('itemsPerPage', (int) $settings['itemsPerPage']);
            $this->setPageCount();
            $this->setCurrentPageNumber();

            return json_encode($this->renderAsArray());
        });

        $this->setPageCount();
        $this->setCurrentPageNumber();

        static::$instance = $this;
        static::$objectCount++;
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
    public function setLimitPerPageOffset(\Closure $limitPerPageOffset): self
    {
        $this->setProperty('limitPerPageOffset', $limitPerPageOffset);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setRenderAsJson(\Closure $renderAsJson): self
    {
        $this->setProperty('renderAsJson', $renderAsJson);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function recalculate(array $settings)
    {
        if (!($this->limitPerPageOffset instanceof \Closure)) {
            throw new \Exception('LimitPerPageOffset callback not found, set it using Paginator::setLimitPerPageOffset()');
        }

        /**
         * Buisiness rules expected on setup:
         *
         *    $this->itemsPerPage can never be < 1
         *    $this->maxPagesToShow can never be < 3
         *    $this->currentPageNumber can never be < 1
         */
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * @param  integer $pageNumber     A page number for data structure
     * @param  boolean $isCurrentPage  A boolean if is the current page
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getCurrentPageFirstItem()
    {
        $first = ((int) $this->currentPageNumber - 1) * (int) $this->itemsPerPage + 1;

        return $first > (int) $this->totalItems ? null : $first;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
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
    protected function setPageOffset(): self
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
    protected function normalizePageCounts(): self
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
     * @return integer
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
    protected function setPageCount(): self
    {
        ((int) $this->itemsPerPage === 0)
            ? $this->setProperty('pageCount', 0)
            : $this->setProperty('pageCount', (int) ceil((int) $this->totalItems / (int) $this->itemsPerPage));

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getPageCount()
    {
        return (int) $this->getProperty('pageCount');
    }

    // --------------------------------------------------------------------------

    /**
     * Determine if the given value is a valid page number.
     *
     * @param  integer $page  A page number.
     *
     * @return Boolean
     */
    protected function isValidPageNumber($page)
    {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        if ((int) $maxPagesToShow < 3) {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');
        }

        $this->setProperty('maxPagesToShow', (int) $maxPagesToShow);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getMaxPagesToShow()
    {
        return (int) $this->getProperty('maxPagesToShow');
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setCurrentPageNumber($currentPageNumber = null): self
    {
        if (null !== $currentPageNumber) {
            $this->setProperty('currentPageNumber', (int) $currentPageNumber);
        }

        $this->normalizePageCounts();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getCurrentPageNumber()
    {
        return $this->currentPageNumber > $this->pageCount ? static::BASE_PAGE : $this->currentPageNumber;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setItemsPerPage($itemsPerPage): self
    {
        $this->setProperty('itemsPerPage', (int) $itemsPerPage);
        $this->updateNumPages();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage()
    {
        return (int) $this->getProperty('itemsPerPage');
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setTotalItems($totalItems): self
    {
        $this->setProperty('totalItems', (int) $totalItems);
        $this->updateNumPages();

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getTotalItems()
    {
        return (int) $this->getProperty('totalItems');
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getNumPages()
    {
        return (int) $this->getProperty('pageCount');
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getNextPage()
    {
        return (int) $this->currentPageNumber < $this->pageCount
            ? (int) $this->currentPageNumber + 1
            : null;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getPrevPage()
    {
        return (int) $this->currentPageNumber > 1
            ? (int) $this->currentPageNumber - 1
            : null;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getNextUrl()
    {
        return $this->getNextPage()
            ? $this->getPageUrl($this->getNextPage())
            : null;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getPrevUrl()
    {
        return $this->getPrevPage()
            ? $this->getPageUrl($this->getPrevPage())
            : null;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setUrlPattern($urlPattern): self
    {
        $this->setProperty('urlPattern', (string) $urlPattern);

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function getUrlPattern()
    {
        return $this->getProperty('urlPattern');
    }

    // --------------------------------------------------------------------------

    /**
     * Method implementations inserted:
     *
     * (+) all();
     * (+) init();
     * (+) get($key);
     * (+) has($key);
     * (+) version();
     * (+) getClassName();
     * (+) getConst($key);
     * (+) set($key, $value);
     * (+) isString($str);
     * (+) getInstanceCount();
     * (+) getClassInterfaces();
     * (+) __call($callback, $parameters);
     * (+) getProperty($name, $key = null);
     * (+) doesFunctionExist($functionName);
     * (+) isStringKey($str, array $keys);
     * (+) throwExceptionError(array $error);
     * (+) setProperty($name, $value, $key = null);
     * (+) throwInvalidArgumentExceptionError(array $error);
     */
    use ServiceFunctions;
}
