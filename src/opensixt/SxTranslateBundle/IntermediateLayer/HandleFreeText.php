<?php
namespace opensixt\SxTranslateBundle\IntermediateLayer;

use opensixt\BikiniTranslateBundle\Entity\Text;
use opensixt\BikiniTranslateBundle\Entity\Resource;
use opensixt\BikiniTranslateBundle\Entity\Language;

/**
 * Handle FreeText
 * Intermediate layer between Controller and Model
 *
 * @author Dmitri Mansilia <dmitri.mansilia@sixt.com>
 */
class HandleFreeText
{
    /** @var \Doctrine\ORM\EntityManager */
    public $em;

    /** @var Doctrine\Bundle\DoctrineBundle\Registry */
    public $doctrine;

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

        $ftext->setResource(
            $this->doctrine->getRepository(Resource::ENTITY_RESOURCE)
                ->findOneByName('Default')
        );

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
}

