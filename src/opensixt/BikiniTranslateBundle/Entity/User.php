<?php

namespace opensixt\BikiniTranslateBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Mapping\ClassMetadata;

use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

/**
 * opensixt\BikiniTranslateBundle\Entity\User
 *
 * @ORM\Table(name="user")
 * @UniqueEntity("username")
 * @UniqueEntity("email")
 * @ORM\Entity(repositoryClass="opensixt\UserAdminBundle\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 */
class User implements AdvancedUserInterface
{
    const ACTIVE_USER     = 1;
    const NOT_ACTIVE_USER = 0;

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
     * @var string $username
     *
     * @Assert\NotBlank()
     * @Assert\MinLength(limit=5)
     *
     * @ORM\Column(name="username", type="string", length=32, nullable=false, unique=true)
     */
    private $username;

    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=32, nullable=false)
     */
    private $password;

    /**
     * @var text $email
     *
     * @Assert\Email()
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false, unique=true)
     */
    private $email;

    /**
     * @var boolean $isactive
     *
     * @ORM\Column(name="isactive", type="boolean", nullable=false)
     */
    private $isactive;

    /**
     * @ORM\ManyToMany(targetEntity="opensixt\BikiniTranslateBundle\Entity\Role")
     * @ORM\JoinTable(name="user_role",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $userRoles
     */
    protected $userRoles;

    /**
     * @ORM\ManyToMany(targetEntity="opensixt\BikiniTranslateBundle\Entity\Language")
     * @ORM\JoinTable(name="user_language",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="language_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $userLanguages
     */
    protected $userLanguages;

    /**
     * @ORM\ManyToMany(targetEntity="opensixt\BikiniTranslateBundle\Entity\Group")
     * @ORM\JoinTable(name="user_group",
     *     joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     *
     * @var ArrayCollection $userGroups
     */
    protected $userGroups;

    /**
     * @var string $salt
     */
    protected $salt;

    /**
     *
     */
    public function __construct()
    {
        //$this->setSalt('');
        $this->userRoles = new ArrayCollection();
        $this->userLanguages = new ArrayCollection();
        $this->setIsactive(self::NOT_ACTIVE_USER);
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
        $encoder = new MessageDigestPasswordEncoder('md5', false, 1);

        $this->password = $encoder->encodePassword($password, $this->getSalt());
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
        return (boolean)$this->isactive;
    }

    /**
     * adds a role to userRoles ArrayCollection
     *
     * @param mixed $role
     * @return bool
     */
    public function addUserRole($role)
    {
        return $this->userRoles->add($role);
    }

    /**
     * @param array $roles
     */
    public function setUserRoles(array $roles = array())
    {
        $this->userRoles = new ArrayCollection($roles);
    }

    /**
     * Get userRoles
     * Returns Roles as ArrayCollection, in contrast to the Method getRoles
     * that returns an array.
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getUserRoles()
    {
        return $this->userRoles;
    }

    /**
     * adds a role to userRoles ArrayCollection
     *
     * @return boolean
     */
    public function addUserLanguage($userLanguage)
    {
        return $this->userLanguages->add($userLanguage);
    }

    /**
     * Set userLanguages
     *
     * @param ArrayCollection $userLanguages
     */
    public function setUserLanguages($userLanguages)
    {
        $this->userLanguages = $userLanguages;
    }

    /**
     * Get userLanguages
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getUserLanguages()
    {
        return $this->userLanguages;
    }

    /**
     * Set userGroups
     *
     * @param ArrayCollection $userGroups
     */
    public function setUserGroups($userGroups)
    {
        $this->userGroups = $userGroups;
    }

    /**
     * Get userGroups
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getUserGroups()
    {
        return $this->userGroups;
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
     * @inheritDoc
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return $this->getUserRoles()->toArray();
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
    }

    /**
     * @inheritDoc
     */
    public function equals(UserInterface $user)
    {
        return md5($this->username) === md5($user->getUsername());
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return $this->isactive;
    }

    /** @ORM\PrePersist */
    public function onPrePersist()
    {
        $this->setUpdated(new \DateTime());
    }
}

