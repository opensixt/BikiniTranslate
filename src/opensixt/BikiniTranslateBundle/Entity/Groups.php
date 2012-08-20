<?php

namespace opensixt\BikiniTranslateBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\BikiniTranslateBundle\Entity\Groups
 *
 * @ORM\Table(name="groups")
 * @UniqueEntity("name")
 * @ORM\Entity(repositoryClass="opensixt\BikiniTranslateBundle\Repository\GroupsRepository")
 */
class Groups
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
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=100, nullable=true)
     */
    private $name;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="opensixt\BikiniTranslateBundle\Entity\Resource")
     * @ORM\JoinTable(name="group_resource",
     *     joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="resource_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $resources
     */
    protected $resources;

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
}

