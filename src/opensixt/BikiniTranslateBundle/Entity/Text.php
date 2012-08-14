<?php

namespace opensixt\BikiniTranslateBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\BikiniTranslateBundle\Entity\Text
 *
 * @ORM\Table(name="text")
 * @ORM\Entity(repositoryClass="opensixt\BikiniTranslateBundle\Repository\TextRepository")
 */
class Text
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
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", length=32, nullable=false)
     */
    private $hash;

    /**
     * @var text $source
     *
     * @ORM\Column(name="source", type="text", nullable=false)
     */
    private $source;

    /**
     * @var int $textRevisionId
     *
     * @ORM\Column(name="text_revision_id", type="integer", nullable=false)
     */
    private $textRevisionId;

    /**
     * @ORM\ManyToOne(targetEntity="opensixt\BikiniTranslateBundle\Entity\TextRevision")
     * @ORM\JoinColumn(name="text_revision_id", referencedColumnName="id")
     *
     * @var ArrayCollection $target
     */
    private $target;

    /**
     * @var int $resourceId
     *
     * @ORM\Column(name="resource_id", type="integer", nullable=false)
     */
    private $resourceId;

    /**
     * @ORM\ManyToOne(targetEntity="opensixt\BikiniTranslateBundle\Entity\Resource")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id")
     *
     * @var ArrayCollection $resource
     */
    private $resource;

    /**
     * @var int $localeId
     *
     * @ORM\Column(name="locale_id", type="integer", nullable=false)
     */
    private $localeId;

    /**
     * @ORM\ManyToOne(targetEntity="opensixt\BikiniTranslateBundle\Entity\Language")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     *
     * @var ArrayCollection $locale
     */
    private $locale;

    /**
     * @var integer $userId
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @ORM\ManyToOne(targetEntity="opensixt\BikiniTranslateBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @var ArrayCollection $user
     */
    private $user;

    /**
     * @var date $expiryDate
     *
     * @ORM\Column(name="expiry_date", type="date", nullable=true)
     */
    private $expiryDate;

    /**
     * @var boolean $released
     *
     * @ORM\Column(name="released", type="boolean", nullable=true)
     */
    private $released;

    /**
     * @var boolean $translationService
     *
     * @ORM\Column(name="translation_service", type="boolean", nullable=true)
     */
    private $translationService;

    /**
     * @var boolean $block
     *
     * @ORM\Column(name="block", type="boolean", nullable=true)
     */
    private $block;

    /**
     * @var boolean dont_translate
     *
     * @ORM\Column(name="dont_translate", type="boolean", nullable=true)
     */
    private $dontTranslate;

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
     * Set hash
     *
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set source
     *
     * @param text $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Get source
     *
     * @return text
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set textRevisionId
     *
     * @param int $textRevisionId
     */
    public function setTextRevisionId($textRevisionId)
    {
        $this->textRevisionId = $textRevisionId;
    }

    /**
     * Get textRevisionId
     *
     * @return int
     */
    public function getTextRevisionId()
    {
        return $this->textRevisionId;
    }

    /**
     * Set target
     *
     * @param ArrayCollection $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Get target
     *
     * @return ArrayCollection
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set resourceId
     *
     * @param string $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * Get resourceId
     *
     * @return string
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Set resource
     *
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Get resource
     *
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Set localeId
     *
     * @param string $localeId
     */
    public function setLocaleId($localeId)
    {
        $this->localeId = $localeId;
    }

    /**
     * Get localeId
     *
     * @return string
     */
    public function getLocaleId()
    {
        return $this->localeId;
    }

    /**
     * Set locale
     *
     * @param ArrayCollection $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get locale
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set userId
     *
     * @param string $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set user
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set expiryDate
     *
     * @param date $exp
     */
    public function setExpiryDate($exp)
    {
        $this->expiryDate = $exp;
    }

    /**
     * Get expiryDate
     *
     * @return date
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * Set released
     *
     * @param boolean $rel
     */
    public function setReleased($rel)
    {
        $this->released = $rel;
    }

    /**
     * Get released
     *
     * @return boolean
     */
    public function getReleased()
    {
        return $this->released;
    }

    /**
     * Set translationService
     *
     * @param boolean $ts
     */
    public function setTranslationService($ts)
    {
        $this->translationService = $ts;
    }

    /**
     * Get translationService
     *
     * @return boolean
     */
    public function getTranslationService()
    {
        return $this->translationService;
    }

    /**
     * Set block
     *
     * @param boolean $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * Get block
     *
     * @return boolean
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * Set dontTranslate
     *
     * @param boolean $flag
     */
    public function setDontTranslate($flag)
    {
        $this->dontTranslate = $flag;
    }

    /**
     * Get dontTranslate
     *
     * @return boolean
     */
    public function getDontTranslate()
    {
        return $this->dontTranslate;
    }
}