<?php
namespace opensixt\SxTranslateBundle\IntermediateLayer;

use opensixt\BikiniTranslateBundle\IntermediateLayer\HandleText;
use opensixt\BikiniTranslateBundle\Entity\Text;
use opensixt\BikiniTranslateBundle\Entity\Resource;
use opensixt\BikiniTranslateBundle\Entity\Language;
use opensixt\BikiniTranslateBundle\Repository\TextRepository;

/**
 * Handle FreeText
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class HandleFreeText extends HandleText
{
    /** @var \Symfony\Component\Security\Core\SecurityContext */
    public $securityContext;

    /**
     * Add a new free text
     *
     * @param string $title      headline
     * @param string $text       text
     * @param int    $languageId free text locale
     *
     * @return void
     */
    public function addFreeText($title, $text, $languageId)
    {
        $ftext = new Text;

        $defaultResource = $this->doctrine
            ->getRepository(Resource::ENTITY_RESOURCE)
                ->findOneByName('Default');

        if (!$defaultResource) {
            throw new \Exception(
                __METHOD__ . ': resource "Default" not found!'
            );
        }

        $ftext->setResource($defaultResource);

        $ftext->setLocale(
            $this->em->find(
                Language::ENTITY_LANGUAGE,
                $languageId
            )
        );
        $ftext->setSource($title);

        if (strlen(trim($text))) {
            $ftext->setTarget($text);
            $ftext->setTranslateMe(false);
        } else {
            $ftext->setTranslateMe(true);
        }

        $ftext->setUser($this->securityContext->getToken()->getUser());
        $ftext->setTranslationType(Text::TRANSLATION_TYPE_FTEXT);

        $this->em->persist($ftext);
        $this->em->flush();
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
            Text::TRANSLATION_TYPE_FTEXT
        );

        $query = $this->textRepository->getTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }
        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        return $data;
    }

    /**
     * Returns search results and pagination data
     *
     * @param int $page
     * @param int $languageId
     * @return Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function getDataByStatus($page, $languageId, $searchMode)
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
            TextRepository::TASK_SEARCH_BY_TRANSLATED_STATUS,
            $languageId,
            $resourceId,
            Text::TRANSLATION_TYPE_FTEXT
        );

        switch ($searchMode) {
            case Text::TRANSLATED:
                $this->textRepository->setTranslated(true);
                break;
            default:
            case Text::NOT_TRANSLATED:
                $this->textRepository->setTranslated(false);
                break;
        }

        if (empty($this->locales)) {
            $this->textRepository->setLocales($this->locales);
        }

        $query = $this->textRepository->getTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }
        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        // set GET parameter for paginator links
        $data->setParam('mode', $searchMode);
        if (!empty($languageId)) {
            $data->setParam('locale', $languageId);
        }
        return $data;
    }
}

