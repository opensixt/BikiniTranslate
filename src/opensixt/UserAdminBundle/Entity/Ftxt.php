<?php

namespace opensixt\UserAdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\UserAdminBundle\Entity\Ftxt
 *
 * @ORM\Table(name="ftxt")
 * @ORM\Entity
 */
class Ftxt
{
    /**
     * @var integer $id
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
     * @var string $lng
     *
     * @ORM\Column(name="lng", type="string", length=5, nullable=false)
     */
    private $lng;

    /**
     * @var text $msg
     *
     * @ORM\Column(name="msg", type="text", nullable=false)
     */
    private $msg;

    /**
     * @var string $ident
     *
     * @ORM\Column(name="ident", type="string", length=255, nullable=false)
     */
    private $ident;

    /**
     * @var string $creator
     *
     * @ORM\Column(name="creator", type="string", length=255, nullable=false)
     */
    private $creator;

    /**
     * @var date $created
     *
     * @ORM\Column(name="created", type="date", nullable=false)
     */
    private $created;



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
     * Set lng
     *
     * @param string $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * Get lng
     *
     * @return string 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set msg
     *
     * @param text $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Get msg
     *
     * @return text 
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * Set ident
     *
     * @param string $ident
     */
    public function setIdent($ident)
    {
        $this->ident = $ident;
    }

    /**
     * Get ident
     *
     * @return string 
     */
    public function getIdent()
    {
        return $this->ident;
    }

    /**
     * Set creator
     *
     * @param string $creator
     */
    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * Get creator
     *
     * @return string 
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Set created
     *
     * @param date $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return date 
     */
    public function getCreated()
    {
        return $this->created;
    }
}