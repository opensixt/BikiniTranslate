<?php

namespace opensixt\SxTranslateBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use opensixt\BikiniTranslateBundle\Entity\TextRevision;

/**
 * opensixt\BikiniTranslateBundle\Entity\Text
 *
 * @ORM\Table(name="mobile")
 * @ORM\Entity(repositoryClass="opensixt\SxTranslateBundle\Repository\MobileTextRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Mobile
{
    const ENTITY_MOBILE = 'opensixt\SxTranslateBundle\Entity\Mobile';

    /**
     * @var int $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int $deviceId
     *
     * @ORM\Column(name="device_id", type="integer", nullable=false)
     */
    private $deviceId;

    /**
     * @ORM\ManyToOne(targetEntity="opensixt\SxTranslateBundle\Entity\Device")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     *
     * @var ArrayCollection $device
     */
    private $device;

    /**
     * @var int $domainId
     *
     * @ORM\Column(name="domain_id", type="integer", nullable=false)
     */
    private $domainId;

    /**
     * @ORM\ManyToOne(targetEntity="opensixt\SxTranslateBundle\Entity\Domain")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     *
     * @var ArrayCollection $domain
     */
    private $domain;

    /**
     * @var int $controllerId
     *
     * @ORM\Column(name="controller_id", type="integer", nullable=false)
     */
    private $controllerId;

    /**
     * @ORM\ManyToOne(targetEntity="opensixt\SxTranslateBundle\Entity\Controller")
     * @ORM\JoinColumn(name="controller_id", referencedColumnName="id")
     *
     * @var ArrayCollection $controller
     */
    private $controller;

    /**
     * @var int $textId
     *
     * @ORM\Column(name="text_id", type="integer", nullable=false)
     */
    private $textId;

    /**
     * @ORM\OneToOne(targetEntity="opensixt\BikiniTranslateBundle\Entity\Text")
     * @ORM\JoinColumn(name="text_id", referencedColumnName="id")
     *
     * @var ArrayCollection $text
     */
    private $text;



    public function __construct()
    {
        $this->text = new ArrayCollection;
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
     * Set deviceId
     *
     * @param int $deviceId
     */
    public function setDeviceId($deviceId)
    {
        $this->deviceId = $deviceId;
    }

    /**
     * Get deviceId
     *
     * @return int
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * Set device
     *
     * @param ArrayCollection $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * Get device
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set domainId
     *
     * @param int $domainId
     */
    public function setDomainId($domainId)
    {
        $this->domainId = $domainId;
    }

    /**
     * Get domainId
     *
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * Set domain
     *
     * @param ArrayCollection $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Get domain
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set controllerId
     *
     * @param int $controllerId
     */
    public function setControllerId($controllerId)
    {
        $this->controllerId = $controllerId;
    }

    /**
     * Get controllerId
     *
     * @return int
     */
    public function getControllerId()
    {
        return $this->controllerId;
    }

    /**
     * Set controller
     *
     * @param ArrayCollection $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get controller
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Set textId
     *
     * @param int $textId
     */
    public function setTextId($textId)
    {
        $this->textId = $textId;
    }

    /**
     * Get textId
     *
     * @return int
     */
    public function getTextId()
    {
        return $this->textId;
    }

    /**
     * Set text
     *
     * @param ArrayCollection $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * Get text
     *
     * @return ArrayCollection A Doctrine ArrayCollection
     */
    public function getText()
    {
        return $this->text;
    }
}

