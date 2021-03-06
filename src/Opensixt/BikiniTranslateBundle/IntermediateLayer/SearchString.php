<?php
namespace Opensixt\BikiniTranslateBundle\IntermediateLayer;

use Opensixt\BikiniTranslateBundle\IntermediateLayer\HandleText;
use Opensixt\BikiniTranslateBundle\Repository\TextRepository;

/**
 * SearchSearch
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class SearchString extends HandleText
{
    const SEARCH_EXACT = 1;
    const SEARCH_LIKE  = 2;

    /** @var string */
    protected $searchString;

    /** @var string */
    protected $searchPhrase;

    /** @var int */
    protected $searchMode;

    /**
     * Set search phrase
     *
     * @param string $searchPhrase
     * @param int $mode
     */
    public function setSearchParameters($searchPhrase, $searchMode = self::SEARCH_EXACT)
    {
        $searchPhrase = urldecode($searchPhrase);
        $this->searchPhrase = $searchPhrase;
        $this->searchMode = $searchMode;
        if ($searchMode == self::SEARCH_LIKE) {
            $searchPhrase = preg_replace('/\s+/', ' ', $searchPhrase);
            $searchPhrase = str_replace(' ', '%', $searchPhrase);
        }
        $searchPhrase = '%' . $searchPhrase . '%';
        //TODO: sanitize input, fulltext search (MATCH...AGAINST....)
        $this->searchString = $searchPhrase;
    }

    /**
     * Returns search results and pagination data
     *
     * @param int $page
     * @param int $locale
     * @param array $resources
     * @return Knp\Component\Pager\Pagination\PaginationInterface
     * @throws \Exception
     */
    public function getData($page, $locale, $resources)
    {
        // Exceptions
        if (!$this->searchString) {
            throw new \Exception(
                __METHOD__ . ': searchString is not set. ' .
                'Please set it with ' . __CLASS__ . '::setSearchParameters() !'
            );
        }

        $this->textRepository->setSearchString($this->searchString);

        $this->textRepository->init(
            TextRepository::TASK_SEARCH_PHRASE_BY_LANG,
            $locale,
            $resources
        );

        $query = $this->textRepository->getTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }

        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        // set GET parameter for paginator links
        if (count($resources) == 1) {
            $data->setParam('resource', $resources[0]);
        }
        $data->setParam('search', $this->searchPhrase);
        $data->setParam('mode', $this->searchMode);
        $data->setParam('locale', $locale);

        return $data;
    }

    /**
     *
     * @param string $hash
     * @return array
     */
    public function getTextsByHash($hash)
    {
        $textObjects = $this->textRepository->getTextsByHash($hash);
        $texts = array();
        foreach ($textObjects as $text) {
            $data = array(
                'id'      => $text->getId(),
                'target'  => $text->getTarget(),
                'locale'  => $text->getLocale()->getLocale(),
                'user'    => $text->getUser()->getUsername(),
                'updated' => $text->getUpdated()->format($this->translator->trans('date_format_full')),
            );
            $texts[] = $data;
        }

        return $texts;
    }
}
