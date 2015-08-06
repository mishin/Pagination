<?php/* * This file is part of the UCSDMath package. * * (c) UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu> * * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code. */namespace UCSDMath\Pagination;/** * PaginationInterface is the interface implemented by all Pagination classes. * * @author Daryl Eisner <deisner@ucsd.edu> */interface PaginationInterface{    /**     * Constants.     */    const DEFAULT_PAGE = 1;    const DEFAULT_CHARSET = 'UTF-8';    const REQUIRED_PHP_VERSION = '5.5.0';    const PAGE_PLACEHOLDER   = '(:page)';    const ROWS_PLACEHOLDER   = '(:rows)';    const SORT_PLACEHOLDER   = '(:sort)';    const SEARCH_PLACEHOLDER = '(:search)';    const NAVIGATION_ARROW_NEXT = 'Next&#160;&#10095;';    const NAVIGATION_ARROW_PREV = '&#10094;&#160;Prev';    const NAVIGATION_ELLIPSES   = '&#183;&#160;&#183;&#160;&#183;';    /**     * Recalculates any updated settings parameter.     *     * @param array $settings  A list of page settings.     * @param array $options   A list of page options.     *     * @return PaginationInterface     *     * @throws \InvalidArgumentException if $settings is null.     *     * @api     */    public function recalculate(array $settings = null, array $options = array());    /**     * Get the calculated page count.     *     * @return integer     *     * @api     */    public function getPageCount();    /**     * Set the maximum pages to display.     *     * @param  integer $maxPagesToShow  A number of pages to display.     *     * @return PaginationInterface     *     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.     *     * @api     */    public function setMaxPagesToShow($maxPagesToShow);    /**     * Get the maximum pages to display.     *     * @return integer     *     * @api     */    public function getMaxPagesToShow();    /**     * Get the limit per page offset (for SQL LIMIT statement).     *     * @return array     *     * @api     */    public function getLimitPerPageOffset(\Closure $overridePerPageOffset = null, $newPage = null);    /**     * Set the current page number.     *     * @param  integer $currentPageNumber  A current page number.     *     * @return PaginationInterface     *     * @api     */    public function setCurrentPageNumber($currentPageNumber = null);    /**     * Get the current page number.     *     * @return integer     *     * @api     */    public function getCurrentPageNumber();    /**     * Set the number of items (records) per page.     *     * @param  integer $itemsPerPage  A number of items per page     *     * @return PaginationInterface     *     * @api     */    public function setItemsPerPage($itemsPerPage);    /**     * Get the number of items (records) per page.     *     * @return integer     *     * @api     */    public function getItemsPerPage();    /**     * Set the total number of records in total.     *     * @param  integer $totalItems  A number of total records in database     *     * @return PaginationInterface     *     * @api     */    public function setTotalItems($totalItems);    /**     * Get the number of items in database.     *     * @return integer     *     * @api     */    public function getTotalItems();    /**     * Get the number of pages.     *     * @return integer     *     * @api     */    public function getNumPages();    /**     * Set the url pattern for rendering pagination (scheme).     *     * @param  string $urlPattern  A base SEO url pattern     *     * @return PaginationInterface     *     * @api     */    public function setUrlPattern($urlPattern);    /**     * Get the assigned url pattern.     *     * @return string     *     * @api     */    public function getUrlPattern();    /**     * Get the page url.     *     * @param  integer $pageNum  A page number for the url pattern     *     * @return string     *     * @api     */    public function getPageUrl($pageNum);    /**     * Get the next page number.     *     * @return integer     *     * @api     */    public function getNextPage();    /**     * Get the previous page number.     *     * @return integer     *     * @api     */    public function getPrevPage();    /**     * Get the next page url.     *     * @return string|null     *     * @api     */    public function getNextUrl();    /**     * Get the previous page url.     *     * @return string|null     *     * @api     */    public function getPrevUrl();    /**     * Get pagination via data array.     *     * Example:     * array(     *     array ('num' => 1,     'url' => '/Personnel/page-1/',  'isCurrent' => false),     *     array ('num' => '...', 'url' => null,                  'isCurrent' => false),     *     array ('num' => 3,     'url' => '/Personnel/page-3/',  'isCurrent' => false),     *     array ('num' => 4,     'url' => '/Personnel/page-4/',  'isCurrent' => true ),     *     array ('num' => 5,     'url' => '/Personnel/page-5/',  'isCurrent' => false),     *     array ('num' => '...', 'url' => null,                  'isCurrent' => false),     *     array ('num' => 10,    'url' => '/Personnel/page-10/', 'isCurrent' => false),     * )     *     * @return array     *     * @api     */    public function renderAsArray();    /**     * Render a small HTML pagination control.     *     * @return array     *     * @api     */    public function renderCompactPaging();    /**     * Render a long HTML pagination control.     *     * @return array     *     * @api     */    public function renderLargePaging();    /**     * Get the next page number.     *     * @return integer     *     * @api     */    public function getCurrentPageFirstItem();    /**     * Get the last item for the current page.     *     * @return integer     *     * @api     */    public function getCurrentPageLastItem();}