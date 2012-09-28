<?php

namespace Opensixt\BikiniTranslateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Opensixt\BikiniTranslateBundle\Entity\TextRevision
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
     * @var datetime $created
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var text $target
     *
     * @ORM\Column(name="target", type="text", nullable=true)
     */
    private $target;

    public function __construct()
    {
        $this->setCreated(new \DateTime());
    }

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
     * Set created
     *
     * @param \DateTime $created
     */
    protected function setCreated(\DateTime $created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
