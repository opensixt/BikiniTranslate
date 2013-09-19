<?php

namespace Opensixt\BikiniTranslateBundle\IntermediateLayer;

use Opensixt\BikiniTranslateBundle\Entity\Text;
use Opensixt\BikiniTranslateBundle\Entity\Resource;

/**
 * HandleText
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */

abstract class HandleText
{
    /** @var Doctrine\Bundle\DoctrineBundle\Registry */
    protected $doctrine;

    /** @var Opensixt\BikiniTranslateBundle\Repository\TextRepository */
    protected $textRepository;

    /** @var \Doctrine\ORM\EntityManager */
    protected $em;

    /** @var int */
    protected $locale;

    /** @var array */
    protected $locales;

    /** @var string */
    public $commonLanguage;

    /** @var int */
    protected $commonLanguageId;

    /** @var int */
    public $paginationLimit;

    /** @var \Knp\Component\Pager\Paginator */
    public $paginator;

    /**
     * Constructor
     *
     * @param type $doctrine
     */
    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
        $this->em = $doctrine->getEntityManager();
        $this->textRepository = $this->em->getRepository(Text::ENTITY_TEXT);
    }

    /**
     * Sets locale
     *
     * @param int $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Sets locales
     *
     * @param array $locales
     */
    public function setLocales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * Set pagination limit
     *
     * @param int $page
     */
    public function setPaginationLimit($lim)
    {
        if ($lim >= 0) {
            $this->paginationLimit = $lim;
        }
    }

    /**
     * Update texts
     *
     * @param array $texts
     */
    public function updateTexts(array $texts)
    {
        $this->textRepository->setUserId($this->securityContext->getToken()->getUser()->getId());
        $this->textRepository->updateTexts($texts);
    }

    /**
     * Set texts as released
     *
     * @param array $textIds
     */
    public function releaseTexts(array $textIds)
    {
        $this->textRepository->releaseTexts($textIds);
    }

    /**
     * Set texts as released
     *
     * @param array $textIds
     */
    public function deleteTexts(array $textIds)
    {
        $this->textRepository->markTextsAsDeleted($textIds);
    }

    /**
     * Get Default resource
     */
    public function getDefaultResource()
    {
        $defaultResource = $this->doctrine
            ->getRepository(Resource::ENTITY_RESOURCE)
                ->findOneByName('Default');

        if (!$defaultResource) {
            throw new \Exception(
                __METHOD__ . ': resource "Default" not found!'
            );
        }

        return $defaultResource;
    }
}
