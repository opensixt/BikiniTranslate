<?php

namespace opensixt\BikiniTranslateBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\Container;

/**
 * Text Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class TextRepository extends EntityRepository
{
    //const TABLE_NAME    = 'text';

    const FIELD_ID        = 't.id';
    const FIELD_HASH      = 't.hash';
    const FIELD_SOURCE    = 't.source';
    const FIELD_TARGET    = 't.target';
    const FIELD_RESOURCE  = 't.resourceId';
    const FIELD_LOCALE    = 't.localeId';
    const FIELD_USER      = 't.userId';
    const FIELD_EXP       = 't.exp';
    const FIELD_REL       = 't.rel';
    const FIELD_HTS       = 't.hts';
    const FIELD_BLOCK     = 't.block';

    const MISSING_TRANS_BY_LANG = 0;

    /**
     * @var string
     */
    private $_task;

    /**
     * @var array
     */
    private $_resources;

    /**
     * @var int
     */
    private $_hts;

    /**
     * @var int
     */
    private $_locale;

    /**
     * @var string
     */
    private $_container;


    /**
     *
     * @param string $task
     */
    public function setTask($task)
    {
        $this->_task = $task;
    }

    /**
     *
     * @param array $resources
     */
    public function setResources($resources)
    {
        $this->_resources = $resources;
    }

    /**
     *
     * @param int $hts
     */
    public function setHts($hts)
    {
        $this->_hts = $hts;
    }

    /**
     * Sets locale
     *
     * @param int $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     *
     * @param Container $locale
     */
    public function setContainer(Container $container)
    {
        $this->_container = $container;
    }

    /**
     * Gets list of texts without translations
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $limit Pagination limit
     * @param int $offset Pagination offset
     */
    public function getMissingTranslations($limit, $offset)
    {

        $query = $this->createQueryBuilder('t')
             ->select('t, l, r, u')
             ->leftJoin('t.locale', 'l')
             ->leftJoin('t.resource', 'r')
             ->leftJoin('t.user', 'u');

        $this->setQueryParameters($query);

        // pagination limit and offset
        $query->setMaxResults($limit)
            ->setFirstResult($offset);

        $translations = $query->getQuery() //->getResult();//
            ->getArrayResult();

        // set messages in common language for any text in $translations
        $commonLanguage = $this->_container->getParameter('common_language');
        $this->getMessagesByLanguage($translations, $commonLanguage);

        return $translations;
    }


    /**
     * Get messages in $lang language for any hash from $texts
     * and set it in $texts
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $texts
     * @param string $locale
     */
    public function getMessagesByLanguage(&$texts, $locale)
    {
        $langId = $this->getIdByLocale($locale); // language id by locale

        if (count($texts) && $langId){
            $hashes = array();
            foreach ($texts as $text) {
                $hashes[] = $text['hash'];
            }
            array_unique($hashes);

            $query = $this->createQueryBuilder('t')
                ->select('t')
                ->where(self::FIELD_HASH . ' IN (?1)')
                ->andWhere(self::FIELD_LOCALE . '=' . $langId)
                ->setParameter(1, $hashes);
            $textsLang = $query->getQuery()->getArrayResult();

            foreach ($texts as &$text) {
                $mess = '';
                foreach ($textsLang as $textLang) {
                    if ($text['hash'] == $textLang['hash']) {
                        $mess = $textLang['target'];
                        break;
                    }
                }
                $text['commonLang'] = $mess;
            }

        }
    }

    /**
     * Get count of records in text table
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $task
     * @param int locale id
     * @param array $resources
     * @param int $hts
     * @return int texts count
     */
    public function getTextCount($task, $locale, $resources, $hts = false)
    {
        $this->setTask($task);
        $this->setLocale($locale);
        $this->setResources($resources);
        $this->setHts($hts);

        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t)');

        $this->setQueryParameters($query);

        $count = $query->getQuery()
            ->getSingleScalarResult();

        return $count;
    }

    /**
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param array $parameters
     */
    private function setQueryParameters($query)
    {
        //$this->_queryParameters = $parameters;
        switch ($this->_task) {
        default:
        case self::MISSING_TRANS_BY_LANG:
            $query->where(self::FIELD_RESOURCE . ' IN (' . implode(',', $this->_resources) . ')')
                ->andWhere(self::FIELD_LOCALE . "=" . (int)$this->_locale)
                //->where(self::FIELD_TARGET . ' != \'DONT_TRANSLATE\'')
                ->andWhere(self::FIELD_TARGET . ' = \'TRANSLATE_ME\'')
                ->andWhere(self::FIELD_EXP . ' IS NULL')
                ->andWhere(self::FIELD_REL . ' IS NOT NULL');

            // just get the unflagged translations
            // 0 = open state
            // 1 = already sent to hts
            if ($this->_hts === true) {
                $query->addWhere(self::FIELD_HTS . ' IS NULL');
            }
            break;

        }
    }

    /**
     * Updates text table: set target = $text for $id
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param int $id
     * @param string $text
     */
    public function updateText ($id, $text)
    {
        if ($id) {
            $this->createQueryBuilder('t')
                ->update()
                ->set(self::FIELD_TARGET, '?1')
                ->where(self::FIELD_ID . ' = ?2')
                ->setParameter(1, trim($text))
                ->setParameter(2, $id)
                ->getQuery()
                ->execute();
        }
    }

    /**
     * Get language id from table Languages by locale
     *
     * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
     * @param string $langId
     */
    private function getIdByLocale($locale)
    {
        if ($locale) {
            $repository = $this->getEntityManager()
                ->getRepository('opensixtBikiniTranslateBundle:Language');

            $langData = $repository->findBy(array('locale' => $locale));
            return $langData[0]->getId();
        }
    }

}
