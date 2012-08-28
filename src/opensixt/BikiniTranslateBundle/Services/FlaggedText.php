<?php
namespace opensixt\BikiniTranslateBundle\Services;

use opensixt\BikiniTranslateBundle\Services\HandleText;
use opensixt\BikiniTranslateBundle\Repository\TextRepository;

/**
 * FlaggedText
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class FlaggedText extends HandleText
{
    /**
     * Returns search results and pagination data
     * if $this->date is set - get expired texts
     * else returns non released texts
     *
     * @param int $locale
     * @param array $resources
     * @param int $page
     * @param date $expiryDate
     * @return Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function getData($page, $locale, $resources, $expiryDate = null)
    {
        if (!empty($expiryDate)) {
            $this->textRepository->setExpiryDate($expiryDate);
        }
        if (count($this->locales)) {
            $this->textRepository->setLocales($this->locales);
        }

        $this->textRepository->init(
            TextRepository::TASK_SEARCH_FLAGGED_TEXTS,
            $locale,
            $resources
        );

        $query = $this->textRepository->getSearchResults();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }
        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        // set GET parameter for paginator links
        if (count($resources) == 1) {
            $data->setParam('resource', $resources[0]);
        }
        if (count($locale) == 1) {
            $data->setParam('locale', $locale);
        }

        return $data;
    }
}

