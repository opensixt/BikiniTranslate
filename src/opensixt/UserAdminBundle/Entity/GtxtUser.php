<?php

namespace opensixt\UserAdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\UserAdminBundle\Entity\GtxtUser
 *
 * @ORM\Table(name="gtxt_user")
 * @ORM\Entity
 */
class GtxtUser
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
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=32, nullable=false)
     */
    private $username;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=32, nullable=false)
     */
    private $password;

    /**
     * @var string $groups
     *
     * @ORM\Column(name="groups", type="string", length=32, nullable=false)
     */
    private $groups;

    /**
     * @var string $role
     *
     * @ORM\Column(name="role", type="string", length=45, nullable=false)
     */
    private $role;

    /**
     * @var text $email
     *
     * @ORM\Column(name="email", type="text", nullable=false)
     */
    private $email;

    /**
     * @var string $salt
     *
     * @ORM\Column(name="salt", type="string", length=32, nullable=false)
     */
    private $salt;

    /**
     * @var boolean $isactive
     *
     * @ORM\Column(name="isactive", type="boolean", nullable=false)
     */
    private $isactive;

    /**
     * @var datetime $created
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
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
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set groups
     *
     * @param string $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * Get groups
     *
     * @return string 
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set role
     *
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set email
     *
     * @param text $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get email
     *
     * @return text 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set salt
     *
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set isactive
     *
     * @param boolean $isactive
     */
    public function setIsactive($isactive)
    {
        $this->isactive = $isactive;
    }

    /**
     * Get isactive
     *
     * @return boolean 
     */
    public function getIsactive()
    {
        return $this->isactive;
    }

    /**
     * Set created
     *
     * @param datetime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }
}