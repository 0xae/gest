<?php

namespace Admin\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="af_notification", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
class Notification {
    const READ='READ';
    const UNREAD='UNREAD';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="complaint", type="text", nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \Text
     *
     * @ORM\Column(name="object_id", type="string", nullable=false)
     */
    private $objectId;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="AppEntity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $entityId;

    /**
     * @var \Text
     *
     * @ORM\Column(name="state", type="string", nullable=false)
     */
    private $state;

    public function getId(){
        return $this->id;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setDescription($description){
        $this->description = $description;
    }

    public function getCreatedAt(){
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt){
        $this->createdAt = $createdAt;
    }

    public function getObjectId(){
        return $this->objectId;
    }

    public function setObjectId($objectId){
        $this->objectId = $objectId;
    }

    public function getEntityId(){
        return $this->entityId;
    }

    public function setEntityId($entityId){
        $this->entityId = $entityId;
    }

    public function getState(){
        return $this->state;
    }

    public function setState($state){
        $this->state = $state;
    }
}
