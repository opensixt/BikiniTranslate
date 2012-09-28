<?php
namespace Opensixt\SxTranslateBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Opensixt\BikiniTranslateBundle\Controller\AbstractController;
use Opensixt\BikiniTranslateBundle\Entity\Text;
use Opensixt\BikiniTranslateBundle\Entity\Resource;
use Opensixt\BikiniTranslateBundle\Entity\Language;

/**
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class ImportController extends AbstractController
{
    /** @var \Opensixt\BikiniTranslateBundle\Helpers\BikiniExport */
    public $importer;

    /** @var \Opensixt\BikiniTranslateBundle\Repository\TextRepository */
    protected $textRepository;

    /**
     * Get Data from Translation Service
     *
     * @return Response A Response instance
     */
    public function getFromTsAction()
    {
        $this->textRepository = $this->em->getRepository(Text::ENTITY_TEXT);

        $langRepository = $this->em->getRepository(Language::ENTITY_LANGUAGE);
        $resRepository = $this->em->getRepository(Resource::ENTITY_RESOURCE);

        $allLanguages = array_flip($langRepository->getAllLanguages());
        $allResources = array_flip($resRepository->getAllResources());

        $xliff = $this->getFieldFromRequest('xliff');
        if (1) {
            $fname = "/tmp/sendtots_20120828-102914_1655.xliff";
            $xliff = file_get_contents($fname);
        }
        if (empty($xliff)) {
            return Response("0\nXliff couldn't be parsed!");
        }
        // TODO: Exceptons if same text id not found: response like '0:same texts not found'
        $texts = array();
        $translations = $this->importer->parseXliffToTexts($xliff);
        // TODO: Source loop to intermediate layer
        if (count($translations)) {
            foreach ($translations as $textData) {
                if ($textData['locale'] && $textData['resource']) {
                    $sxLocale = str_replace('-', '_', $textData['locale']);

                    if (!empty($allLanguages[$sxLocale])) {
                        $localeId = $allLanguages[$sxLocale];
                    }
                    if (!empty($allResources[$textData['resource']])) {
                        $resourceId = $allResources[$textData['resource']];
                    }

                    if (!empty($localeId) && !empty($resourceId)
                            && !empty($textData['hash'])) {
                        $textId = $this->textRepository
                            ->getIdByHashAndLocaleAndResource(
                                $textData['hash'],
                                $localeId,
                                $resourceId
                            );
                        if ($textId) {
                            $texts[$textId] = $textData['target'];
                        }
                    }
                }
            }
        }

        //print_r($texts);
        // Update texts and set it as not released and translated
        $this->textRepository->updateTexts($texts, true);

        return new Response("1\nOK");
    }
}
