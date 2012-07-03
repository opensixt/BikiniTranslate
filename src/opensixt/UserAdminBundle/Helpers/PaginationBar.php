<?php

namespace opensixt\UserAdminBundle\Helpers;

/**
 * Description of Pagination
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class PaginationBar {

    /**
     * Count all elements in a data array
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

    public function __construct($count, $limit, $page)
    {
        $this->setCount($count);
        $this->setLimit($limit);
        $this->setPage($page);
        $this->paginationBar = array();
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function setPage($page)
    {
        if (!$page) $page = 1;
        $this->page = $page;
    }

    public function setLimit($limit)
    {
        $this->limit = $limit > 0 ? $limit : 1;
    }

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
        return ($this->page - 1) * $this->limit;
    }

}
