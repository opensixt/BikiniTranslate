<?php
namespace Opensixt\SxTranslateBundle\IntermediateLayer;

use Opensixt\BikiniTranslateBundle\IntermediateLayer\EditText;
use Opensixt\BikiniTranslateBundle\Entity\Resource;

use Opensixt\BikiniTranslateBundle\Entity\Text;
use Opensixt\SxTranslateBundle\Entity\Mobile;
use Opensixt\SxTranslateBundle\Repository\MobileTextRepository as TextRepository;

/**
 * Handle mobile texts
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class HandleMobile extends EditText
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
     * @param int   $page
     * @param int   $languageId
     * @param array $searchDomains
     * @return Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function getTranslations($page, $languageId, $searchDomains)
    {
        $this->textRepository->setCommonLanguage($this->commonLanguage);

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

        $this->textRepository->setDomains($searchDomains);

        $query = $this->textRepository->getTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }
        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        if (count($searchDomains) == 1) {
            $data->setParam('domain', $searchDomains[0]);
        }

        // set messages in common language for any text in $translations
        $this->textRepository->setMessagesInCommonLanguage($data);

        return $data;
    }
}
