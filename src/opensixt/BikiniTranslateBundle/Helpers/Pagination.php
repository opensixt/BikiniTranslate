<?php

namespace opensixt\BikiniTranslateBundle\Helpers;

/**
 * Pagination
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class Pagination
{
    /**
     * Count of all elements in a data array
     * @var int
     */
    private $count;

    /**
     * pagination limit
     * @var int
     */
    private $limit;

    /**
     * current page
     * @var int
     */
    private $page;

    /**
     * @var array
     */
    private $paginationBar;

    /**
     * Pagination
     *
     * @param int $count Count of all elements
     * @param int $limit Pagination limit
     * @param int $page Current page
     */
    public function __construct($count, $limit, $page)
    {
        $this->setCount($count);
        $this->setLimit($limit);
        $this->setPage($page);
        $this->paginationBar = array();
    }

    /**
     * Set a count of all elements
     *
     * @param int $count
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * Set a current page
     *
     * @param int $page
     */
    public function setPage($page)
    {
        if (!$page) {
            $page = 1;
        }
        $this->page = $page;
    }

    /**
     * Set a pagination limit
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit > 0 ? $limit : 1;
    }

    /**
     * Generate data for pagination bar:
     * first-, last-, next-, current-, previous-page etc.
     *
     * @return array Pagination bar data
     */
    public function getPaginationBar()
    {

        $numOfPages = ceil($this->count / $this->limit);

        // next page
        if ($this->page < $numOfPages) {
            $next = $this->page + 1;
        } else {
            $next = $numOfPages;
        }

        // previous page
        if ($this->page > 1) {
            $prev = $this->page - 1;
        } else {
            $prev = 1;
        }

        // list with a current page, and two previous and next pages
        $pages = array();
        if ($this->count > 1) {
            // $i: -2, -1, 0, 1, 2
            for ($i = -2; $i < 3; $i++) {
                if ((($this->page + $i) > 0) && (($this->page + $i) <= $numOfPages)) {
                    $pages[] = $this->page + $i;
                }
            }
        }

        $this->paginationBar = array(
            'first'   => 1,
            'last'    => $numOfPages,
            'next'    => $next,
            'prev'    => $prev,
            'current' => $this->page,
            'pages'   => $pages
        );

        return $this->paginationBar;
    }

    /**
     * Get pagination offset
     */
    public function getOffset()
    {
        $offset = ($this->page - 1) * $this->limit;
        if ($offset > $this->count) {
            $offset = 0;
        }
        return $offset;
    }
}

