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
    /** @var array */
    private $domains;


    /**
     * Sets domains
     *
     * @param array $domains
     */
    public function setDomains($domains)
    {
        $this->domains = $domains;
    }

    /**
     * Set query parameters by $this->task
     *
     * @param QueryBuilder $query
     */
    protected function setQueryParameters($query)
    {
        // Exceptions
        if (!isset($this->domains)) {
            throw new \Exception(
                __METHOD__ . ': domains is not set. Please set it with '
                . __CLASS__ . '::setDomains() !'
            );
        }

        switch ($this->task) {
            case self::TASK_SEARCH_PHRASE_BY_LANG:
            case self::TASK_MISSING_TRANS_BY_LANG:
            default:
                parent::setQueryParameters($query);

                $query->andWhere('m.domainId' . ' IN (?99)')
                    ->setParameter(99, $this->domains);

                break;
        }
    }

    /**
     * Set messages in $locale language for any hash from $texts
     * if current locale not equal common language
     *
     * @param array $texts
     */
    public function setMessagesInLanguage(&$data, $languageId)
    {
        $texts = array();
        foreach ($data as $mobile) {
            $texts[] = $mobile->getText();
        }

        $textsLang = $this->getMessagesByLanguage($texts, array($languageId));
        foreach ($data as $mobile) {
            $message = '';
            foreach ($textsLang as $textLang) {
                if ($mobile->getText()->getHash() == $textLang->getText()->getHash()) {
                    $message = $textLang->getText()->getTarget();
                    break;
                }
            }
            $mobile->getText()->setTextInCommonLanguage($message);
        }
    }

    /**
     * @return QueryBuilder
     */
    protected function getBaseQuery()
    {
        $query = $this->createQueryBuilder('m')
            ->select('m, t, r, l, u')
            ->leftJoin('m.text', 't')
            ->leftJoin('t.resource', 'r')
            ->leftJoin('t.locale', 'l')
            ->leftJoin('t.user', 'u');

        return $query;
    }
}

