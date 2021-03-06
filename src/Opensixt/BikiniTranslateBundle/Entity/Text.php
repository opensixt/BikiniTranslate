<?php

namespace Opensixt\BikiniTranslateBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Opensixt\BikiniTranslateBundle\Entity\TextRevision;

/**
 * Opensixt\BikiniTranslateBundle\Entity\Text
 *
 * @ORM\Table(
 *     name="text",
 *     indexes={
 *         @ORM\Index(name="IDX_text_expiry_date", columns={"expiry_date"}),
 *         @ORM\Index(name="IDX_text_translate_me", columns={"translate_me"}),
 *         @ORM\Index(name="IDX_text_dont_translate", columns={"dont_translate"}),
 *         @ORM\Index(name="IDX_text_translation_type", columns={"translation_type"}),
 *
 *         @ORM\Index(name="IDX_resource_locale_type_deleted", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date"
 *         }),
 *
 *         @ORM\Index(name="IDX_resource_locale_transtype_deleted_expiry", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "expiry_date"
 *         }),
 *
 *         @ORM\Index(name="IDX_getMessagesResourceByLanguage", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "hash"
 *         }),
 *
 *         @ORM\Index(name="IDX_getMessagesByLanguage", columns={
 *             "locale_id", "translation_type", "deleted_date", "hash"
 *         }),
 *
 *         @ORM\Index(name="IDX_getSuggestionByHashAndLanguage", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "hash", "translate_me"
 *         }),
 *
 *         @ORM\Index(name="IDX_getIdByHashAndLocaleAndResource", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "hash", "translate_me",
 *             "translation_service"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_SEARCH_PHRASE_BY_LANG", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "expiry_date"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_ALL_CONTENT_BY_LANG_OR_RES", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "expiry_date", "translate_me", "released"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_ALL_CONTENT_BY_DOMAIN", columns={
 *             "locale_id", "translation_type", "deleted_date", "expiry_date", "translate_me", "released"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_SEARCH_FLAGGED_TEXTS_LOCALE_EXPIRY", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "expiry_date", "released"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_SEARCH_FLAGGED_TEXTS_LOCALE", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "released"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_SEARCH_FLAGGED_TEXTS_EXPIRY", columns={
 *             "resource_id", "translation_type", "deleted_date", "expiry_date", "released"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_SEARCH_FLAGGED_TEXTS", columns={
 *             "resource_id", "translation_type", "deleted_date", "released"
 *         }),
 *
 *         @ORM\Index(name="IDX_TASK_MISSING_TRANS_BY_LANG", columns={
 *             "resource_id", "locale_id", "translation_type", "deleted_date", "expiry_date",
 *             "translate_me", "released", "dont_translate", "translation_service"
 *         }),
 *     },
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="UNIQ_text_res_loc_hash", columns={"resource_id", "locale_id", "hash"})
 *     }
 * )
 * @ORM\Entity(repositoryClass="Opensixt\BikiniTranslateBundle\Repository\TextRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Text
{
    const ENTITY_TEXT = 'Opensixt\BikiniTranslateBundle\Entity\Text';

    const TRANSLATION_TYPE_TEXT   = 1;
    const TRANSLATION_TYPE_FTEXT  = 2;
    const TRANSLATION_TYPE_MOBILE = 3;

    const TRANSLATED     = 1;
    const NOT_TRANSLATED = 2;

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
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", length=32, nullable=false)
     */
    private $hash;

    /**
     * @var text $source
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="source", type="text", nullable=false)
     */
    private $source;

    /**
     * @var text $target
     *
     * @ORM\Column(name="target", type="text", nullable=true)
     */
    private $target;

    /**
     * @var int $textRevisionId
     *
     * @ORM\Column(name="text_revision_id", type="integer", nullable=true)
     * @ORM\JoinColumn(name="text_revision_id", referencedColumnName="id")
     */
    private $textRevisionId;

    /**
     * @ORM\ManyToMany(targetEntity="Opensixt\BikiniTranslateBundle\Entity\TextRevision", cascade={"persist"})
     * @ORM\JoinColumn(name="text_revision_id", referencedColumnName="id")
     *     joinColumns={@ORM\JoinColumn(name="text_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="text_revision_id", referencedColumnName="id")}
     *
     * @var ArrayCollection $targets
     */
    private $targets;

    /**
     * @var int $resourceId
     *
     * @ORM\Column(name="resource_id", type="integer", nullable=false)
     */
    private $resourceId;

    /**
     * @ORM\ManyToOne(targetEntity="Opensixt\BikiniTranslateBundle\Entity\Resource")
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
     * @ORM\ManyToOne(targetEntity="Opensixt\BikiniTranslateBundle\Entity\Language")
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
     * @ORM\ManyToOne(targetEntity="Opensixt\BikiniTranslateBundle\Entity\User")
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
     * @var date $expiryDate
     *
     * @ORM\Column(name="deleted_date", type="date", nullable=true)
     */
    private $deletedDate;

    /**
     * @var boolean $released
     *
     * @ORM\Column(name="released", type="boolean", nullable=true)
     */
    private $released = true;

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
     * @var boolean translate_me
     *
     * @ORM\Column(name="translate_me", type="boolean", nullable=true)
     */
    private $translateMe = true;

    /**
     * @var boolean dont_translate
     *
     * @ORM\Column(name="dont_translate", type="boolean", nullable=true)
     */
    private $dontTranslate = false;

    /**
     * @var int translation_type
     *
     * @ORM\Column(name="translation_type", type="integer", nullable=true)
     */
    private $translationType = self::TRANSLATION_TYPE_TEXT;

    /** @var text */
    private $textInCommonLanguage;

    /** @var ArrayCollection */
    private $suggestions;

    public function __construct()
    {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
        $this->targets = new ArrayCollection;
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
     * Set updated
     *
     * @param \DateTime $updated
     */
    protected function setUpdated(\DateTime $updated)
    {
        $this->updated = $updated;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * CAUTION: only ::setSource must set hash and only once
     * Set hash
     *
     * @param string $hash
     */
    protected function setHash($hash)
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
        // set hash once only
        if (!$this->hash) {
            $this->setHash(md5($source));
        }

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
     * Set target
     *
     * @param string $target
     */
    public function setTarget($target)
    {
        if (!empty($this->target)) {
            $textRev = new TextRevision;
            $textRev->setTarget($this->target);
            $this->targets[] = $textRev;
        }

        $this->target = $target;

        // mark as translated
        $this->setTranslateMe(false);
    }

    /**
     * Get target
     *
     * @return string $target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Get target
     *
     * @return ArrayCollection
     */
    public function getTargets()
    {
        return $this->targets;
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
     * Set resourceId
     *
     * @param int $resourceId
     */
    public function setResourceId($resourceId)
    {
        $this->resourceId = $resourceId;
    }

    /**
     * Get resourceId
     *
     * @return int
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
     * Set deletedDate
     *
     * @param date $del
     */
    public function setDeletedDate($del)
    {
        $this->deletedDate = $del;
    }

    /**
     * Get deletedDate
     *
     * @return date
     */
    public function getDeletedDate()
    {
        return $this->deletedDate;
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
     * Set translateMe
     *
     * @param boolean $flag
     */
    public function setTranslateMe($flag)
    {
        $this->translateMe = $flag;
    }

    /**
     * Get translateMe
     *
     * @return boolean
     */
    public function getTranslateMe()
    {
        return $this->translateMe;
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

    /**
     * Set translationType
     *
     * @param int $type
     */
    public function setTranslationType($type)
    {
        $this->translationType = $type;
    }

    /**
     * Get translationType
     *
     * @return int
     */
    public function getTranslationType()
    {
        return $this->translationType;
    }

    /**
     * Set textInCommonLanguage
     *
     * @param text $text
     */
    public function setTextInCommonLanguage($text)
    {
        $this->textInCommonLanguage = $text;
    }

    /**
     * Get textInCommonLanguage
     *
     * @return text
     */
    public function getTextInCommonLanguage()
    {
        return $this->textInCommonLanguage;
    }

    /**
     * Set suggestions
     *
     * @param ArrayCollection $suggestions
     */
    // TODO: type hinting ArrayCollection?
    public function setSuggestions($suggestions)
    {
        $this->suggestions = $suggestions;
    }

    /**
     * Get suggestions
     *
     * @return ArrayCollection
     */
    public function getSuggestions()
    {
        return $this->suggestions;
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
