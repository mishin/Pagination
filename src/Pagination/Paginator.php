<?php/* * This file is part of the UCSDMath package. * * (c) UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu> * * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code. */namespace UCSDMath\Pagination;/** * Pagination is the default implementation of {@link PaginationInterface} which * provides routine Paginator methods that are commonly used throughout the framework. * * Paginator provides a process of dividing (content) into discrete pages that are * acceptable or desirable to the enduser. * * Important considerations in writing this class are: *    - SEO Friendly URLS *    - Dynamic search results (sticky or hold state) *    - Standard scheme for Front Controllers *    - Provide options for template generator (e.g., Twig, Plates, Smarty) *    - Provided via a data structure (a raw data option) * * Technically, for pagination to work, all is needed is the page number of the current set. * *    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT); *    $itemsPerPage = 4; * *    $totalItems = "SELECT COUNT(*) FROM personnel; *    $rowCount   = "SELECT COUNT(*) FROM personnel where group = 'faculty'; * *    if ($totalItems === 0) { print 'No records exist in the database.';} *    if ($rowCount === 0)   { print 'No records found in database with you exact match.';} * *    $pageCount = (int) ceil($rowCount / $itemsPerPage); * *    // range error; we could just set page = 1 *    if ($page > $pageCount) {$page = 1;} * *    $offset = ($page - 1) * $itemsPerPage; *    $sql = "SELECT * FROM personnel where (group = 'faculty') (ORDER BY lastname, firstname) LIMIT " . $offset . "," . $itemsPerPage; * *    SQL looks like:  SELECT * FROM personnel LIMIT 4,4 * * Consider some common url patterns: *    - /sso/1/personnel/(:page)/(:rows)/(:sort)/ *    - /sso/1/personnel/quick-search/(:page)/(:rows)/(:search)/(:sort)/ *    - /sso/1/personnel/edit-search/page-(:page)/show-(:rows)/(:search)/(:sort)/ *    - /sso/1/personnel/edit-record/page-(:page)/ * * Method list: * * The notation below illustrates visibility: (+) @api, (-) protected or private. * * @method PaginationInterface __construct(); * @method mixed getPrevUrl(); * @method integer getNextUrl(); * @method integer getNumPages(); * @method integer getNextPage(); * @method integer getPrevPage(); * @method integer getPageCount(); * @method string getUrlPattern(); * @method integer getTotalItems(); * @method integer getPageOffset(); * @method integer getItemsPerPage(); * @method integer getMaxPagesToShow(); * @method integer getCurrentPageNumber(); * @method PaginationInterface setPageCount(); * @method PaginationInterface setPageOffset(); * @method PaginationInterface setUrlPattern($urlPattern); * @method PaginationInterface setTotalItems($totalItems); * @method PaginationInterface setItemsPerPage($itemsPerPage); * @method PaginationInterface setMaxPagesToShow($maxPagesToShow); * @method PaginationInterface setCurrentPageNumber($currentPageNumber = null); * * @author Daryl Eisner <deisner@ucsd.edu> * * @api */class Paginator extends AbstractPagination implements PaginationInterface{    /**     * Constants.     *     * @var string VERSION  A version number     *     * @api     */    const VERSION = '1.4.0';    // --------------------------------------------------------------------------    /**     * Properties.     */    // --------------------------------------------------------------------------    /**     * Constructor.     *     * @param array  $settings  A associated list of page settings.     *     * @api     */    public function __construct(array $settings = null)    {        parent::__construct($settings);    }    // --------------------------------------------------------------------------    /**     * Set the page offset.     *     * @return PaginationInterface     *     * @api     */    protected function setPageOffset()    {        $this->normalizePageCounts();        $this->setProperty('pageOffset', abs(intval($this->currentPageNumber * $this->itemsPerPage - $this->itemsPerPage)));        return $this;    }    // --------------------------------------------------------------------------    /**     * Normalize and check page counts.     *     * @return PaginationInterface     *     * @api     */    protected function normalizePageCounts()    {        if ($this->currentPageNumber > $this->pageCount            || $this->currentPageNumber < static::BASE_PAGE        ) {            $this->setProperty('currentPageNumber', static::BASE_PAGE);        }        if ($this->itemsPerPage < 1) {            $this->setProperty('itemsPerPage', static::BASE_PAGE);        }        return $this;    }    // --------------------------------------------------------------------------    /**     * Get the page offset.     *     * @return integer     *     * @api     */    protected function getPageOffset()    {        return (int) $this->getProperty('pageOffset');    }    // --------------------------------------------------------------------------    /**     * Calculate the number of pages.     *     * @return bool     */    protected function setPageCount()    {        ((int) $this->itemsPerPage === 0)            ? $this->setProperty('pageCount', 0)            : $this->setProperty('pageCount', (int) ceil((int) $this->totalItems / (int) $this->itemsPerPage));        return $this;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getPageCount()    {        return (int) $this->getProperty('pageCount');    }    // --------------------------------------------------------------------------    /**     * Determine if the given value is a valid page number.     *     * @param  integer $page  A page number.     *     * @return Boolean     */    protected function isValidPageNumber($page)    {        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function setMaxPagesToShow($maxPagesToShow)    {        if ((int) $maxPagesToShow < 3) {            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');        }        $this->setProperty('maxPagesToShow', (int) $maxPagesToShow);        return $this;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getMaxPagesToShow()    {        return (int) $this->getProperty('maxPagesToShow');    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function setCurrentPageNumber($currentPageNumber = null)    {        if (null !== $currentPageNumber) {            $this->setProperty('currentPageNumber', (int) $currentPageNumber);        }        $this->normalizePageCounts();        return $this;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getCurrentPageNumber()    {        return $this->currentPageNumber > $this->pageCount ? static::BASE_PAGE : $this->currentPageNumber;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function setItemsPerPage($itemsPerPage)    {        $this->setProperty('itemsPerPage', (int) $itemsPerPage);        $this->updateNumPages();        return $this;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getItemsPerPage()    {        return (int) $this->getProperty('itemsPerPage');    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function setTotalItems($totalItems)    {        $this->setProperty('totalItems', (int) $totalItems);        $this->updateNumPages();        return $this;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getTotalItems()    {        return (int) $this->getProperty('totalItems');    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getNumPages()    {        return (int) $this->getProperty('pageCount');    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getNextPage()    {        return (int) $this->currentPageNumber < $this->pageCount            ? (int) $this->currentPageNumber + 1            : null;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getPrevPage()    {        return (int) $this->currentPageNumber > 1            ? (int) $this->currentPageNumber - 1            : null;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getNextUrl()    {        return $this->getNextPage()            ? $this->getPageUrl($this->getNextPage())            : null;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getPrevUrl()    {        return $this->getPrevPage()            ? $this->getPageUrl($this->getPrevPage())            : null;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function setUrlPattern($urlPattern)    {        $this->setProperty('urlPattern', (string) $urlPattern);        return $this;    }    // --------------------------------------------------------------------------    /**     * {@inheritdoc}     */    public function getUrlPattern()    {        return $this->getProperty('urlPattern');    }}