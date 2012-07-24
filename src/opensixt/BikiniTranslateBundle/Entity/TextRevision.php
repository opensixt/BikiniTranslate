<?php

namespace opensixt\BikiniTranslateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\BikiniTranslateBundle\Entity\TextRevision
 *
 * @ORM\Table(name="text_revision")
 * @ORM\Entity
 */
class TextRevision
{
    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int $textId
     *
     * @ORM\Column(name="text_id", type="integer", nullable=false)
     */
    private $textId;

    /**
     * @var text $target
     *
     * @ORM\Column(name="target", type="text", nullable=false)
     */
    private $target;

    /**
     * Get id
     *
     * @return bigint
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set textId
     *
     * @param int $textId
     */
    public function setTextId($textId)
    {
        $this->textId = $textId;
    }

    /**
     * Get textId
     *
     * @return int
     */
    public function getTextId()
    {
        return $this->textId;
    }

    /**
     * Set target
     *
     * @param text $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Get target
     *
     * @return text
     */
    public function getTarget()
    {
        return $this->target;
    }
}