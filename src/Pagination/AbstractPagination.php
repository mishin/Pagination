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
 * This service groups a common code base implementation that Pagination extends.
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
 *    $totalRecordCount = "SELECT COUNT(*) FROM personnel;
 *    $rowCount   = "SELECT COUNT(*) FROM personnel where group = 'faculty';
 *
 *    if ($totalRecordCount === 0) { print 'No records exist in the database.';}
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
 * (+) PaginationInterface __construct();
 * (+) void __destruct();
 * (+) int getPageCount();
 * (+) string __toString();
 * (+) string getUrlPattern();
 * (+) string getPageUrl($pageNumber);
 * (+) mixed __call($callback, $parameters);
 * (+) PaginationInterface recalculate(array $pageSettings);
 * (+) PaginationInterface setUrlPattern(string $urlPattern);
 * (+) PaginationInterface setMaxPagesToShow(int $maxPagesToShow);
 * (+) PaginationInterface loadStartupSettings(array $pageSettings);
 * (+) PaginationInterface setRenderAsJson(\Closure $renderAsJson);
 * (+) PaginationInterface setLimitPerPageOffset(\Closure $limitPerPageOffset);
 * (+) array getLimitPerPageOffset(\Closure $overridePerPageOffset = null, int $newPage = null);
 * (-) int getPageOffset();
 * (-) bool isValidPageNumber($page);
 * (-) PaginationInterface setPageCount();
 * (-) PaginationInterface setPageOffset();
 * (-) PaginationInterface normalizePageCounts();
 * (-) array createPage($pageNumber, $isCurrentPage = false);
 *
 * @author Daryl Eisner <deisner@ucsd.edu>
 */
abstract class AbstractPagination implements PaginationInterface, ServiceFunctionsInterface
{
    /**
     * Constants.
     *
     * @var string VERSION A version number
     *
     * @api
     */
    const VERSION = '1.7.0';

    //--------------------------------------------------------------------------

    /**
     * Properties.
     *
     * @var    int                 $pageCount           A number of pages to render (e.g., a calculation) (e.g., 780)
     * @var    int                 $totalRecordCount    A total number of found records in table (e.g., 8500)
     * @var    int                 $pageOffset          A interger used to define our SQL OFFSET (e.g., 60)
     * @var    string              $urlPattern          A default url with placeholders (e.g., '/sso/1/news/(:page)/(:rows)/(:search)/')
     * @var    string              $sortPattern         A default sort url pattern (:sort) (e.g., 'group-lastname-firstname')
     * @var    int                 $itemsPerPage        A display setting showing a number of records per page (e.g., 15)
     * @var    int                 $maxPagesToShow      A maximum number of pages for the <select> menu (e.g., 10)
     * @var    string              $searchPattern       A search pattern used in the url (:search) (e.g., 'dillon-or-drop')
     * @var    int                 $currentPageNumber   A current page number (e.g., 8)
     * @var    \Closure            $renderAsJson        A closure callback for encoding json data
     * @var    \Closure            $limitPerPageOffset  A closure callback for the limit offset
     * @var    bool                $isUrlPatternUsed    A boolean option that enables the pattern type
     * @var    bool                $isSortPatternUsed   A boolean option that enables the pattern type
     * @var    bool                $isItemsPerPageUsed  A boolean option that enables the pattern type
     * @var    bool                $isSearchPatternUsed A boolean option that enables the pattern type
     * @static PaginationInterface $instance            A static instance PaginationInterface
     * @static int                 $objectCount         A static count of PaginationInterface
     * @var    array               $storageRegister     A stored set of data structures used by this class
     */
    protected $pageCount = null;
    protected $totalRecordCount = null;
    protected $pageOffset = null;
    protected $urlPattern = null;
    protected $sortPattern = null;
    protected $itemsPerPage = 20;
    protected $maxPagesToShow = 10;
    protected $renderAsJson = null;
    protected $searchPattern = null;
    protected $currentPageNumber = 1;
    protected $limitPerPageOffset = null;
    protected $isUrlPatternUsed = false;
    protected $isSortPatternUsed = false;
    protected $isItemsPerPageUsed = false;
    protected $isSearchPatternUsed = false;
    protected $renderPageEllipsis = [
        'pageUrl'       => null,
        'isCurrentPage' => false,
        'pageNumber'    => self::NAVIGATION_ELLIPSES,
    ];
    protected static $instance    = null;
    protected static $objectCount = 0;
    protected $storageRegister    = [];

    //--------------------------------------------------------------------------

    /**
     * Constructor.
     *
     * Business rules expected on setup:
     *    $this->itemsPerPage can never be < 1
     *    $this->maxPagesToShow can never be < 3
     *    $this->currentPageNumber can never be < 1
     *
     * @param array $pageSettings A list of page settings.
     *
     * @api
     */
    public function __construct(array $pageSettings)
    {
        $this->loadStartupSettings($pageSettings);
        /* Callback to {$limitPerPageOffset} for Page Override. */
        $this->setLimitPerPageOffset(function($currentPageNumber) use (&$pageSettings) {
            $offset = $this->setPageOffset()->getPageOffset() + ($currentPageNumber * $this->itemsPerPage);
            return [$offset, $this->itemsPerPage];
        });
        $this->setPageCount()->setCurrentPageNumber();
    }

    //--------------------------------------------------------------------------

    /**
     * Load Settings into assigned properties.
     *
     * Example of array items:
     *    'totalRecordCount'    => (int) 1082
     *    'itemsPerPage'        => (int) 20
     *    'currentPageNumber'   => (int) 5
     *    'maxPagesToShow'      => (int) 10
     *    'urlPattern'          => (string) '/sso/1/course-petitions/edit-record/(:page)/(:rows)/(:search)/(:sort)/'
     *    'sortPattern'         => (string) 'primary_group_select-lastname-firstname'
     *    'searchPattern'       => (string) 'dillon-and-drop'
     *    'isUrlPatternUsed'    => (bool) true
     *    'isItemsPerPageUsed'  => (bool) false
     *    'isSearchPatternUsed' => (bool) false
     *    'isSortPatternUsed'   => (bool) false
     *
     * @param string $pageSettings A startup configuration setting.
     *
     * @return PaginationInterface The current instance
     *
     * @api
     */
    public function loadStartupSettings(array $pageSettings): PaginationInterface
    {
        foreach ($pageSettings as $key => $value) {
            if (property_exists($this, $key)) {
                $this->setProperty($key, $value);
            }
        }

        return $this;
    }

    //--------------------------------------------------------------------------

    /**
     * Set the maximum pages to display.
     *
     * @param int $maxPagesToShow A number of pages to display.
     *
     * @return PaginationInterface The current instance
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

        return $this->setProperty('maxPagesToShow', (int) $maxPagesToShow);
    }

    //--------------------------------------------------------------------------

    /**
     * Destructor.
     *
     * @api
     */
    public function __destruct()
    {
        static::$objectCount--;
    }

    //--------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setLimitPerPageOffset(\Closure $limitPerPageOffset): PaginationInterface
    {
        return $this->setProperty('limitPerPageOffset', $limitPerPageOffset);
    }

    //--------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function setRenderAsJson(\Closure $renderAsJson): PaginationInterface
    {
        return $this->setProperty('renderAsJson', $renderAsJson);
    }

    //--------------------------------------------------------------------------

    /**
     * Get the limit per page offset (for SQL LIMIT statement).
     *
     * @param \Closure $overridePerPageOffset A Closure to render.
     * @param int      $newPage               A new page number.
     *
     * @return array The Per-Page offset as Array: [(int) 1, (int) 20]
     *
     * @api
     */
    public function getLimitPerPageOffset(\Closure $overridePerPageOffset = null, int $newPage = null): array
    {
        $offset = $this->setPageOffset()->getPageOffset();

        return ($overridePerPageOffset instanceof \Closure)
            ? $overridePerPageOffset($newPage)
            : [$offset, $this->itemsPerPage];
    }

    //--------------------------------------------------------------------------

    /**
     * Forward to any callable, including anonymous functions
     * (or any instances of \Closure).
     *
     * @param string $callback   A named callable to be called.
     * @param mixed  $parameters A parameter set to be passed to the callback (as an indexed array).
     *
     * @return mixed  the return value of the callback, or false on error.
     *
     * @api
     */
    public function __call($callback, $parameters)
    {
        return call_user_func_array($this->$callback, $parameters);
    }

    //--------------------------------------------------------------------------

    /**
     * Recalculates any updated $pageSettings parameter.
     *
     * Buisiness rules expected on setup:
     *    $this->itemsPerPage can never be < 1
     *    $this->maxPagesToShow can never be < 3
     *    $this->currentPageNumber can never be < 1
     *
     * @param array $pageSettings A list of per page settings.
     *
     * @return PaginationInterface The current instance
     *
     * @throws \InvalidArgumentException if $pageSettings is null.
     *
     * @api
     */
    public function recalculate(array $pageSettings): PaginationInterface
    {
        $this->limitPerPageOffset instanceof \Closure
            ?: $this->throwExceptionError([__METHOD__, __CLASS__, 'LimitPerPageOffset callback not found, set it using Paginator::setLimitPerPageOffset()', 'A105']);
        foreach ($pageSettings as $key => $value) {
            if (property_exists($this, $key)) {
                $this->setProperty($key, $value);
            }
        }
        $this->setPageCount();
        $this->setCurrentPageNumber();

        return $this;
    }

    //--------------------------------------------------------------------------

    /**
     * Get the page url.
     *
     * @param int $pageNumber A page number for the url pattern
     *
     * @return string
     *
     * @api
     */
    public function getPageUrl($pageNumber): string
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

    //--------------------------------------------------------------------------

    /**
     * Create a page data structure.
     *
     * @param int  $pageNumber    A page number for data structure
     * @param bool $isCurrentPage A boolean if is the current page
     *
     * @return array
     */
    protected function createPage($pageNumber, $isCurrentPage = false): array
    {
        return [
            'pageNumber'    => (int) $pageNumber,
            'pageUrl'       => $this->getPageUrl($pageNumber),
            'isCurrentPage' => (bool) $isCurrentPage,
        ];
    }

    //--------------------------------------------------------------------------

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->renderCompactPaging();
    }

    //--------------------------------------------------------------------------

    /**
     * Set the page offset.
     *
     * @return PaginationInterface The current instance
     *
     * @api
     */
    protected function setPageOffset(): PaginationInterface
    {
        $this->normalizePageCounts();

        return $this->setProperty('pageOffset', abs(intval($this->currentPageNumber * $this->itemsPerPage - $this->itemsPerPage)));
    }

    //--------------------------------------------------------------------------

    /**
     * Normalize and check page counts.
     *
     * @return PaginationInterface The current instance
     *
     * @api
     */
    protected function normalizePageCounts(): PaginationInterface
    {
        if ($this->currentPageNumber > $this->pageCount || $this->currentPageNumber < static::BASE_PAGE) {
            $this->setProperty('currentPageNumber', static::BASE_PAGE);
        }
        if ($this->itemsPerPage < 1) {
            $this->setProperty('itemsPerPage', static::BASE_PAGE);
        }

        return $this;
    }

    //--------------------------------------------------------------------------

    /**
     * Get the page offset.
     *
     * @return int
     *
     * @api
     */
    protected function getPageOffset(): int
    {
        return (int) $this->getProperty('pageOffset');
    }

    //--------------------------------------------------------------------------

    /**
     * Calculate the number of pages.
     *
     * @return PaginationInterface The current instance
     */
    protected function setPageCount(): PaginationInterface
    {
        return ((int) $this->itemsPerPage === 0)
            ? $this->setProperty('pageCount', 0)
            : $this->setProperty('pageCount', (int) ceil((int) $this->totalRecordCount / (int) $this->itemsPerPage));
    }

    //--------------------------------------------------------------------------

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

    //--------------------------------------------------------------------------

    /**
     * Determine if the given value is a valid page number.
     *
     * @param int  $page A page number.
     *
     * @return bool
     */
    protected function isValidPageNumber($page): bool
    {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
    }

    //--------------------------------------------------------------------------

    /**
     * Set the url pattern for rendering pagination (scheme).
     *
     * @param string $urlPattern A base SEO url pattern
     *
     * @return PaginationInterface The current instance
     *
     * @api
     */
    public function setUrlPattern(string $urlPattern): PaginationInterface
    {
        return $this->setProperty('urlPattern', $urlPattern);
    }

    //--------------------------------------------------------------------------

    /**
     * Get the assigned url pattern.
     *
     * @return string
     *
     * @api
     */
    public function getUrlPattern(): string
    {
        return $this->getProperty('urlPattern');
    }

    //--------------------------------------------------------------------------

    /**
     * Method implementations inserted:
     *
     * Method list: (+) @api, (-) protected or private visibility.
     *
     * (+) array all();
     * (+) object init();
     * (+) string version();
     * (+) bool isString($str);
     * (+) bool has(string $key);
     * (+) string getClassName();
     * (+) int getInstanceCount();
     * (+) array getClassInterfaces();
     * (+) mixed getConst(string $key);
     * (+) bool isValidUuid(string $uuid);
     * (+) bool isValidEmail(string $email);
     * (+) bool isValidSHA512(string $hash);
     * (+) mixed __call($callback, $parameters);
     * (+) bool doesFunctionExist($functionName);
     * (+) bool isStringKey(string $str, array $keys);
     * (+) mixed get(string $key, string $subkey = null);
     * (+) mixed getProperty(string $name, string $key = null);
     * (+) object set(string $key, $value, string $subkey = null);
     * (+) object setProperty(string $name, $value, string $key = null);
     * (-) Exception throwExceptionError(array $error);
     * (-) InvalidArgumentException throwInvalidArgumentExceptionError(array $error);
     */
    use ServiceFunctions;

    //--------------------------------------------------------------------------
}
