<?php

namespace opensixt\UserAdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * opensixt\UserAdminBundle\Entity\UserGroup
 *
 * @ORM\Table(name="user_group")
 * @ORM\Entity
 */
class UserGroup
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
     * @var integer $roleId
     *
     * @ORM\Column(name="role_id", type="integer", nullable=false)
     */
    private $roleId;

    /**
     * @var integer $groupId
     *
     * @ORM\Column(name="group_id", type="integer", nullable=false)
     */
    private $groupId;



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
     * Set roleId
     *
     * @param integer $roleId
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    }

    /**
     * Get roleId
     *
     * @return integer 
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set groupId
     *
     * @param integer $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * Get groupId
     *
     * @return integer 
     */
    public function getGroupId()
    {
        return $this->groupId;
    }
}