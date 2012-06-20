<?php

namespace opensixt\UserAdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\UserAdminBundle\Entity\Gtxt
 *
 * @ORM\Table(name="gtxt")
 * @ORM\Entity
 */
class Gtxt
{
    /**
     * @var bigint $id
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $hash
     *
     * @ORM\Column(name="hash", type="string", length=32, nullable=true)
     */
    private $hash;

    /**
     * @var text $msgid
     *
     * @ORM\Column(name="msgid", type="text", nullable=true)
     */
    private $msgid;

    /**
     * @var text $msgstr
     *
     * @ORM\Column(name="msgstr", type="text", nullable=true)
     */
    private $msgstr;

    /**
     * @var string $module
     *
     * @ORM\Column(name="module", type="string", length=255, nullable=true)
     */
    private $module;

    /**
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", length=5, nullable=true)
     */
    private $locale;

    /**
     * @var string $user
     *
     * @ORM\Column(name="user", type="string", length=32, nullable=true)
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
     * Set msgid
     *
     * @param text $msgid
     */
    public function setMsgid($msgid)
    {
        $this->msgid = $msgid;
    }

    /**
     * Get msgid
     *
     * @return text 
     */
    public function getMsgid()
    {
        return $this->msgid;
    }

    /**
     * Set msgstr
     *
     * @param text $msgstr
     */
    public function setMsgstr($msgstr)
    {
        $this->msgstr = $msgstr;
    }

    /**
     * Get msgstr
     *
     * @return text 
     */
    public function getMsgstr()
    {
        return $this->msgstr;
    }

    /**
     * Set module
     *
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * Get module
     *
     * @return string 
     */
    public function getModule()
    {
        return $this->module;
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