<?php
namespace Opensixt\SxTranslateBundle\IntermediateLayer;

use Opensixt\BikiniTranslateBundle\IntermediateLayer\HandleText;
use Opensixt\BikiniTranslateBundle\Entity\Text;
use Opensixt\BikiniTranslateBundle\Entity\Language;
use Opensixt\BikiniTranslateBundle\Repository\TextRepository;

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

    /** @var string */
    public $toolLanguage;

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

        $defaultResource = $this->getDefaultResource();

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

        $defaultResource = $this->getDefaultResource();
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
        $defaultResource = $this->getDefaultResource();
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

        if ($searchMode != Text::TRANSLATED) {
            // use toolLanguage as commonLanguage for free texts;
            // set messages in tool language for any text in $translations
            $toolLanguageId = $this->textRepository
                ->getIdByLocale($this->toolLanguage);
            $this->textRepository->setMessagesInLanguage($data, $toolLanguageId);
        }

        // set GET parameter for paginator links
        $data->setParam('mode', $searchMode);
        if (!empty($languageId)) {
            $data->setParam('locale', $languageId);
        }
        return $data;
    }
}
