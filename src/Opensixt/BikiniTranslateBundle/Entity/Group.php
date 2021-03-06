<?php

namespace Opensixt\BikiniTranslateBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Opensixt\BikiniTranslateBundle\Entity\Group
 *
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="Opensixt\BikiniTranslateBundle\Repository\GroupRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 */
class Group
{
    const ENTITY_GROUP  = 'Opensixt\BikiniTranslateBundle\Entity\Group';

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
     * @Assert\NotBlank()
     * @Assert\MinLength(limit=2)
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=false, unique=true)
     */
    private $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Opensixt\BikiniTranslateBundle\Entity\Resource")
     * @ORM\JoinTable(name="group_resource",
     *     joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $resources
     */
    protected $resources;

    /**
     *
     */
    public function __construct()
    {
        $this->resources = new ArrayCollection();
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
     * Set description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set resources
     *
     * @param array $resources
     */
    public function setResources($resources)
    {
        $this->resources = $resources;
    }
    /**
     * Get resources
     *
     * @return ArrayCollection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate()
     */
    public function onPrePersist()
    {
        $this->setUpdated(new \DateTime());
    }
}
