<?php/* * This file is part of the UCSDMath package. * * (c) UCSD Mathematics | Math Computing Support <mathhelp@math.ucsd.edu> * * For the full copyright and license information, please view the LICENSE * file that was distributed with this source code. */namespace UCSDMath\Pagination;/** * Pagination is the default implementation of {@link PaginationInterface} which * provides routine Paginator methods that are commonly used throughout the framework. * * @author Daryl Eisner <deisner@ucsd.edu> * * @api */class Paginator extends AbstractPagination implements PaginationInterface{    /**     * Constants.     *     * @var string VERSION  A version number     *     * @api     */    const VERSION = '1.2.0';    /**     * Properties.     */    /**     * Constructor.     *     * @param array  $settings  A associated list of page settings.     *     * @api     */    public function __construct(array $settings = null)    {        parent::__construct($settings);    }}