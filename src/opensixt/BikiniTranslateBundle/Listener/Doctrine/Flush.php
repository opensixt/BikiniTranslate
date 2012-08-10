<?php

namespace opensixt\BikiniTranslateBundle\Listener\Doctrine;

use \Doctrine\ORM\Event\PostFlushEventArgs;
use \Doctrine\ORM\Event\OnFlushEventArgs;

use \opensixt\BikiniTranslateBundle\Entity\TextRevision;

class Flush
{
    /**
     * @var array
     */
    private $newEntities;

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();

        // store the array of entities that will be flushed. This is required to be able to work with them in
        // Flush::postFlush since in Doctrine 2.2, EntityManager/UnitOfWork doesn't provide methods for accessing them
        // after a flush.
        $this->newEntities = $em->getUnitOfWork()
                                ->getScheduledEntityInsertions();
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $em = $args->getEntityManager();

        /** @var $textRepository \opensixt\BikiniTranslateBundle\Repository\TextRepository */
        $textRepository = $em->getRepository('opensixt\BikiniTranslateBundle\Entity\Text');

        $changedFlag = false;
        foreach($this->newEntities as $entity) {
            if ($entity instanceof TextRevision) {
                /** @var $entity TextRevision */
                $newTextRevisionId = $entity->getId();

                /** @var $text \opensixt\BikiniTranslateBundle\Entity\Text */
                $text = $textRepository->findOneByTarget($entity);

                if (!$text) continue;

                if ($text->getTextRevisionId() != $newTextRevisionId) {
                    $text->setTextRevisionId($newTextRevisionId);

                    $em->persist($text);
                    $changedFlag = true;
                }
            }
        }
        if ($changedFlag) {
            $em->flush();
        }

        $this->newEntities = null;
    }
}