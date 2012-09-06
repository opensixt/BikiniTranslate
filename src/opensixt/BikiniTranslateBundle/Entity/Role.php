<?php

namespace opensixt\BikiniTranslateBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * opensixt\BikiniTranslateBundle\Entity\Role
 *
 * @ORM\Table(name="role")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 * @UniqueEntity("label")
 */
class Role implements RoleInterface
{
    const ENTITY_ROLE  = 'opensixt\BikiniTranslateBundle\Entity\Role';

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
     * @var datetime $updated
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false, unique=true)
     */
    private $name;

    /**
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=100, nullable=false, unique=true)
     */
    private $label;

    /**
     *
     */
    public function __construct()
    {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created
     *
     * @param \Datetime $created
     */
    protected function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return \Datetime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \Datetime $updated
     */
    protected function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * Get updated
     *
     * @return \Datetime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->setUpdated(new \DateTime());
    }
}

