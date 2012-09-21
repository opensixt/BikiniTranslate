<?php
namespace opensixt\SxTranslateBundle\IntermediateLayer;

use opensixt\BikiniTranslateBundle\IntermediateLayer\HandleText;
use opensixt\BikiniTranslateBundle\Entity\Resource;
use opensixt\BikiniTranslateBundle\Entity\Language;

use opensixt\BikiniTranslateBundle\Entity\Text;
use opensixt\SxTranslateBundle\Entity\Mobile;
use opensixt\SxTranslateBundle\Repository\MobileTextRepository as TextRepository;

/**
 * Handle mobile texts
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class HandleMobile extends HandleText
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public $securityContext;

    public function __construct($doctrine)
    {
        parent::__construct($doctrine);
        $this->textRepository = $this->em->getRepository(Mobile::ENTITY_MOBILE);
    }

    /**
     * Returns search results and pagination data
     *
     * @param int $page
     * @param int $languageId
     * @return Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function getTranslations($page, $languageId)
    {

        $defaultResource = $this->doctrine
            ->getRepository(Resource::ENTITY_RESOURCE)
                ->findOneByName('Default');

        if (!$defaultResource) {
            throw new \Exception(
                __METHOD__ . ': resource "Default" not found!'
            );
        }

        $resourceId = $defaultResource->getId();
        $this->textRepository->init(
            TextRepository::TASK_MISSING_TRANS_BY_LANG,
            $languageId,
            $resourceId,
            Text::TRANSLATION_TYPE_MOBILE
        );

        $query = $this->textRepository->getTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }
        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        return $data;
    }
}

