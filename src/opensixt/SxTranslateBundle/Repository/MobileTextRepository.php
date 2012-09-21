<?php

namespace opensixt\SxTranslateBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
//use Doctrine\ORM\Query\Expr\Join;
//use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

use opensixt\BikiniTranslateBundle\Repository\TextRepository;

/**
 * Text Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class MobileTextRepository extends TextRepository
{
    /**
     * Gets list of texts without translations
     *
     * @return QueryBuilder
     */
    public function getTranslations()
    {
        $query = $this->createQueryBuilder('m')
            ->select('m, t, l, dev')
            ->leftJoin('m.text', 't')
            ->leftJoin('m.device', 'dev')
            ->leftJoin('t.locale', 'l')
            ->leftJoin('t.user', 'u');

        $this->setQueryParameters($query);

        return $query;
    }
}
