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
     * @param string $title  headline
     * @param string $text   text
     * @param int    $locale free text locale
     *
     * @return void
     */
    public function addFreeText($title, $text, $locale)
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
                $locale
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
     * @param int $locale
     * @return Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function getMissingTranslations($page, $languageId)
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

        $query = $this->textRepository->getMissingTranslations();

        if (empty($this->paginationLimit)) {
            $this->paginationLimit = PHP_INT_MAX;
        }
        $data = $this->paginator->paginate($query, $page, $this->paginationLimit);

        return $data;
    }
}

