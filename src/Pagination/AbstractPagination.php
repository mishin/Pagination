<?php/* * This file is part of the UCSDMath package. * * (c) UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu> * * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code. */namespace UCSDMath\Pagination;use Carbon\Carbon;use UCSDMath\Functions\ServiceFunctions;use UCSDMath\Pagination\PaginatorInterface;use UCSDMath\Functions\ServiceFunctionsInterface;/** * AbstractPagination provides an abstract base class implementation of {@link PaginationInterface}. * Primarily, this services the fundamental implementations for all Pagination classes. * * Paginator provides a process of dividing (content) into discrete pages that are * acceptable or desirable to the user. * * Important considerations in this class are: *    - SEO Friendly URLS *    - Dynamic search results (sticky or hold state) *    - Standard scheme for Front Controllers *    - Provide options for template generator (e.g., Twig, Plates, Smarty) *    - Provided via data structure * * Method list: * * @see (+) __construct(); * @see (+) __destruct(); * * @author Daryl Eisner <deisner@ucsd.edu> */abstract class AbstractPagination implements PaginationInterface, ServiceFunctionsInterface{    /**     * Constants.     */    const VERSION = '1.0.5';    /**     * Properties.     *     * @var    integer             $totalItems     A total number of records (e.g., 5000)     * @var    integer             $itemsPerPage   A display setting of records per page (e.g., 50)     * @var    integer             $currentPage    A current page number (e.g., 8)     * @var    string              $urlPattern     A default url pattern with placeholder (:num) (e.g., '/sso/1/course-petitions/(:num)')     * @var    string              $searchPattern  A current search pattern used by the user (e.g., 'dillon-and-drop')     * @var    integer             $numPages       A number of pages to display (Pagenation) (e.g., 10)     * @static PaginationInterface $instance       A PaginationInterface instance     * @static integer             $objectCount    A PaginationInterface instance count     */    protected $totalItems;    protected $itemsPerPage;    protected $currentPage;    protected $urlPattern;    protected $searchPattern;    protected $numPages;    protected $maxPagesToShow = 10;    protected $storageRegister = array();    protected static $instance = null;    protected static $objectCount = 0;    /**     * Constructor.     *     * @param array  $settings  A associated list of page settings.     *     * @api     */    public function __construct(array $settings = null)    {        $this->totalItems    = isset($settings['total_items']) ? (int) $settings['total_items'] : null;        $this->itemsPerPage  = isset($settings['items_per_page']) ? (int) $settings['items_per_page'] : null;        $this->currentPage   = isset($settings['current_page']) ? (int) $settings['current_page'] : null;        $this->urlPattern    = isset($settings['url_pattern']) ? (string) $settings['url_pattern'] : null;        $this->searchPattern = isset($settings['search_pattern']) ? (string) $settings['search_pattern'] : null;        $this->setNumberOfPages();        static::$instance = $this;        static::$objectCount++;    }    /**     * Destructor.     */    public function __destruct()    {        static::$objectCount--;    }    /**     * Calculate the number of pages.     *     * @return bool     */    protected function setNumberOfPages()    {        $this->numPages = (int) $this->itemsPerPage === 0            ? 0            : (int) ceil($this->totalItems/$this->itemsPerPage);    }    /**     * @param int $maxPagesToShow     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.     */    /**     * Display maximum pages to the user.     *     * @return bool     */    public function setMaxPagesToShow($maxPagesToShow)    {        if ((int) $maxPagesToShow < 3) {            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');        }        $this->maxPagesToShow = $maxPagesToShow;        return $this;    }    /**     * @return int     */    public function getMaxPagesToShow()    {        return $this->maxPagesToShow;    }    /**     * @param int $currentPage     */    public function setCurrentPage($currentPage)    {        $this->currentPage = $currentPage;    }    /**     * @return int     */    public function getCurrentPage()    {        return $this->currentPage;    }    /**     * @param int $itemsPerPage     */    public function setItemsPerPage($itemsPerPage)    {        $this->itemsPerPage = $itemsPerPage;        $this->updateNumPages();    }    /**     * @return int     */    public function getItemsPerPage()    {        return $this->itemsPerPage;    }    /**     * @param int $totalItems     */    public function setTotalItems($totalItems)    {        $this->totalItems = $totalItems;        $this->updateNumPages();    }    /**     * @return int     */    public function getTotalItems()    {        return $this->totalItems;    }    /**     * @return int     */    public function getNumPages()    {        return $this->numPages;    }    /**     * @param string $urlPattern     */    public function setUrlPattern($urlPattern)    {        $this->urlPattern = $urlPattern;    }    /**     * @return string     */    public function getUrlPattern()    {        return $this->urlPattern;    }    /**     * @param int $pageNum     * @return string     */    public function getPageUrl($pageNum)    {        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);    }    public function getNextPage()    {        if ($this->currentPage < $this->numPages) {            return $this->currentPage + 1;        }        return null;    }    public function getPrevPage()    {        if ($this->currentPage > 1) {            return $this->currentPage - 1;        }        return null;    }    public function getNextUrl()    {        if (!$this->getNextPage()) {            return null;        }        return $this->getPageUrl($this->getNextPage());    }    /**     * @return string|null     */    public function getPrevUrl()    {        if (!$this->getPrevPage()) {            return null;        }        return $this->getPageUrl($this->getPrevPage());    }    /**     * Get via array.     *     * Example:     * array(     *     array ('num' => 1,     'url' => '/Personnel/page-1/',  'isCurrent' => false),     *     array ('num' => '...', 'url' => null,                  'isCurrent' => false),     *     array ('num' => 3,     'url' => '/Personnel/page-3/',  'isCurrent' => false),     *     array ('num' => 4,     'url' => '/Personnel/page-4/',  'isCurrent' => true ),     *     array ('num' => 5,     'url' => '/Personnel/page-5/',  'isCurrent' => false),     *     array ('num' => '...', 'url' => null,                  'isCurrent' => false),     *     array ('num' => 10,    'url' => '/Personnel/page-10/', 'isCurrent' => false),     * )     *     * @return array     */    public function getPages()    {        $pages = array();        if ($this->numPages <= 1) {            return array();        }        if ($this->numPages <= $this->maxPagesToShow) {            for ($i = 1; $i <= $this->numPages; $i++) {                $pages[] = $this->createPage($i, $i == $this->currentPage);            }        } else {            // Determine the sliding range, centered around the current page.            $numAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);            if ($this->currentPage + $numAdjacents > $this->numPages) {                $slidingStart = $this->numPages - $this->maxPagesToShow + 2;            } else {                $slidingStart = $this->currentPage - $numAdjacents;            }            if ($slidingStart < 2) $slidingStart = 2;            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;            if ($slidingEnd >= $this->numPages) $slidingEnd = $this->numPages - 1;            // Build the list of pages.            $pages[] = $this->createPage(1, $this->currentPage == 1);            if ($slidingStart > 2) {                $pages[] = $this->createPageEllipsis();            }            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {                $pages[] = $this->createPage($i, $i == $this->currentPage);            }            if ($slidingEnd < $this->numPages - 1) {                $pages[] = $this->createPageEllipsis();            }            $pages[] = $this->createPage($this->numPages, $this->currentPage == $this->numPages);        }        return $pages;    }    /**     * Create a page data structure.     *     * @param int $pageNum     * @param bool $isCurrent     * @return Array     */    protected function createPage($pageNum, $isCurrent = false)    {        return array(            'num' => $pageNum,            'url' => $this->getPageUrl($pageNum),            'isCurrent' => $isCurrent,        );    }    /**     * @return array     */    protected function createPageEllipsis()    {        return array(            'num' => '...',            'url' => null,            'isCurrent' => false,        );    }    /**     * Render an small HTML pagination user control.     *     * @return string     */    public function getSmallHtml()    {        $html = '';        if ($this->getNumPages() > 1) {            $html .= '<div class="input-group" style="width: 1px;">'."\n";            if ($this->getPrevUrl()) {                $html .= '<span class="input-group-btn">'."\n";                $html .= '   <a href="'  . $this->getPrevUrl() . '" class="btn btn-default" type="button">&laquo; Prev</a>'."\n";                $html .= '</span>'."\n\n";            }            $html .= '<select class="form-control paginator-select-page" style="width: auto; cursor: pointer; -webkit-appearance: none; -moz-appearance: none; appearance: none;">'."\n";            foreach ($this->getPages() as $page) {                if ($page['url']) {                    $html .= '    <option value="'. $page['url'] .'"';                    $html .= $page['isCurrent'] ? ' selected="selected">' : '>' ;                    $html .= 'Page '.  $page['num']  . '</option>'."\n";                } else {                    $html .= '    <option disabled>'.   $page['num']   .     '</option>' ."\n";                }            }            $html .= '</select>'."\n\n";            if ($this->getNextUrl()) {                $html .= '<span class="input-group-btn">'."\n";                $html .= '   <a href="'  . $this->getNextUrl() . '" class="btn btn-default" type="button">Next &raquo;</a>'."\n";                $html .= '</span>'."\n";            }            $html .= '</div>'."\n";        }        $html .= "<script>                    $(function() {                        $('.paginator-select-page').on('change', function() { document.location = $(this).val(); });                        // Workaround to prevent iOS from zooming the page when clicking the select list:                        $('.paginator-select-page').on('focus', function() {                                if (/(iPad|iPhone|iPod)/g.test(navigator.userAgent)) {                                    $(this).css('font-size', '16px');                                }                        })                        .on('blur', function() {                            if (/(iPad|iPhone|iPod)/g.test(navigator.userAgent)) {                                $(this).css('font-size', '');                            }                        });                    });        </script>";        return $html;    }    /**     * Render an HTML pagination control.     *     * @return string     */    public function getDefaultHtml()    {        if ($this->numPages <= 1) {            return '';        }        $html = '<ul class="pagination">'."\n";        if ($this->getPrevUrl()) {            $html .= '<li><a href="'.$this->getPrevUrl().'">&laquo; Previous</a></li>'."\n";        }        foreach ($this->getPages() as $page) {            if ($page['url']) {                $html .= '<li'.($page['isCurrent'] ? ' class="active"' : '').'><a href="'.$page['url'].'">'.$page['num'].'</a></li>'."\n";            } else {                $html .= '<li class="disabled"><span>'.$page['num'].'</span></li>'."\n";            }        }        if ($this->getNextUrl()) {            $html .= '<li><a href="'.$this->getNextUrl().'">Next &raquo;</a></li>'."\n";        }        $html .= '</ul>'."\n";        return $html;    }    public function __toString()    {        return $this->getSmallHtml();    }    public function getCurrentPageFirstItem()    {        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;        if ($first > $this->totalItems) {            return null;        }        return $first;    }    public function getCurrentPageLastItem()    {        $first = $this->getCurrentPageFirstItem();        if ($first === null) {            return null;        }        $last = $first + $this->itemsPerPage - 1;        if ($last > $this->totalItems) {            return $this->totalItems;        }        return $last;    }    /**     * Method implementations inserted.     *     * The notation below illustrates visibility: (+) @api, (-) protected or private.     *     * @see (+) all();     * @see (+) init();     * @see (+) get($key);     * @see (+) has($key);     * @see (+) version();     * @see (+) getClassName();     * @see (+) getConst($key);     * @see (+) set($key, $value);     * @see (+) isString($string);     * @see (+) getInstanceCount();     * @see (+) getClassInterfaces();     * @see (+) getProperty($name, $key = null);     * @see (-) doesFunctionExist($functionName);     * @see (+) isStringKey($string, array $keys);     * @see (-) throwExceptionError(array $error);     * @see (+) setProperty($name, $value, $key = null);     * @see (-) throwInvalidArgumentExceptionError(array $error);     */    use ServiceFunctions;}