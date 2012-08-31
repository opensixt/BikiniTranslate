<?php

namespace opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Language Admin Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class LanguageRepository extends EntityRepository
{
    const ENTITY_LANGUAGE  = 'opensixt\BikiniTranslateBundle\Entity\Language';

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForAllLanguages()
    {
        $q = $this->createQueryBuilder('l')
            ->select('l')
            ->orderBy('l.locale', 'ASC');
        return $q;
    }
}

