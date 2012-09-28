<?php
namespace Opensixt\SxTranslateBundle\IntermediateLayer;

use Opensixt\BikiniTranslateBundle\IntermediateLayer\SearchString;
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
class SearchMobile extends SearchString
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
    public function getData($page, $languageId, $searchDomains)
    {
        // Exceptions
        if (!$this->searchString) {
            throw new \Exception(
                __METHOD__ . ': searchString is not set. ' .
                'Please set it with ' . __CLASS__ . '::setSearchParameters() !'
            );
        }

        $defaultResource = $this->getDefaultResource();
        $resourceId = $defaultResource->getId();

        $this->textRepository->setSearchString($this->searchString);
        $this->textRepository->setDomains($searchDomains);
        $this->textRepository->init(
            TextRepository::TASK_SEARCH_PHRASE_BY_LANG,
            $languageId,
            array($resourceId),
            Text::TRANSLATION_TYPE_MOBILE
        );


        $query = $this->textRepository->getTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }

        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        // set GET parameter for paginator links
        if (count($searchDomains) == 1) {
            $data->setParam('domain', $searchDomains[0]);
        }
        $data->setParam('search', $this->searchPhrase);
        $data->setParam('locale', $languageId);

        return $data;
    }
}
