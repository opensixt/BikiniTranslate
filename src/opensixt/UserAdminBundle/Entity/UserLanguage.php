<?php

namespace opensixt\UserAdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\UserAdminBundle\Entity\UserLanguage
 *
 * @ORM\Table(name="user_language")
 * @ORM\Entity
 */
class UserLanguage
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
     * @var integer $userId
     *
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer $languageId
     *
     * @ORM\Column(name="language_id", type="integer", nullable=false)
     */
    private $languageId;



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
     * Set userId
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set languageId
     *
     * @param integer $languageId
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;
    }

    /**
     * Get languageId
     *
     * @return integer 
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }
}