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
     * @var date $exp
     *
     * @ORM\Column(name="exp", type="date", nullable=true)
     */
    private $exp;

    /**
     * @var boolean $rel
     *
     * @ORM\Column(name="rel", type="boolean", nullable=true)
     */
    private $rel;

    /**
     * @var boolean $hts
     *
     * @ORM\Column(name="hts", type="boolean", nullable=true)
     */
    private $hts;

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
     * Set exp
     *
     * @param date $exp
     */
    public function setExp($exp)
    {
        $this->exp = $exp;
    }

    /**
     * Get exp
     *
     * @return date
     */
    public function getExp()
    {
        return $this->exp;
    }

    /**
     * Set rel
     *
     * @param boolean $rel
     */
    public function setRel($rel)
    {
        $this->rel = $rel;
    }

    /**
     * Get rel
     *
     * @return boolean
     */
    public function getRel()
    {
        return $this->rel;
    }

    /**
     * Set hts
     *
     * @param boolean $hts
     */
    public function setHts($hts)
    {
        $this->hts = $hts;
    }

    /**
     * Get hts
     *
     * @return boolean
     */
    public function getHts()
    {
        return $this->hts;
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
}