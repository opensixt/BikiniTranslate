<?php

namespace Opensixt\BikiniTranslateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Opensixt\BikiniTranslateBundle\Entity\Language
 *
 * @ORM\Table(name="language")
 * @ORM\Entity(repositoryClass="Opensixt\BikiniTranslateBundle\Repository\LanguageRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("locale")
 */
class Language
{
    const ENTITY_LANGUAGE  = 'Opensixt\BikiniTranslateBundle\Entity\Language';

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
     * @var string $locale
     *
     * @Assert\NotBlank()
     * @Assert\MinLength(limit=2)
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=false, unique=true)
     */
    private $locale;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=true)
     */
    private $description;

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
     * Set locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
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
     * @ORM\PrePersist
     * @ORM\PreUpdate()
     */
    public function onPrePersist()
    {
        $this->setUpdated(new \DateTime());
    }
}
