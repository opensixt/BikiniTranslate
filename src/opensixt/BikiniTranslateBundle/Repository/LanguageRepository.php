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
    const FIELD_ID     = 'l.id';
    const FIELD_LOCALE = 'l.locale';
    const FIELD_DESCR  = 'l.description';

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

    /**
     * execute QueryBuilder from getQueryForAllLanguages()
     *
     * @return array
     */
    public function getAllLanguages()
    {
        $result = array();
        $allLanguages = $this->getQueryForAllLanguages()->getQuery()
            ->getResult();

        if (count($allLanguages)) {
            foreach ($allLanguages as $language) {
                $result[$language->getId()] = $language->getLocale();
            }
        }
        return $result;
    }
}

