<?php

namespace Admin\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="upload", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
class Upload {
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
     * @ORM\Column(name="description", type="string", length=200, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="reference", type="string", length=200, nullable=false)
     */
    private $reference;

    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=200, nullable=false)
     */
    private $filename;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * })
     */
    private $createdBy;

    private $file;    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    public function getDescription(){
		return $this->description;
	}

	public function setDescription($description){
		$this->description = $description;
	}

	public function getReference(){
		return $this->reference;
	}

	public function setReference($reference){
		$this->reference = $reference;
    }
    
    public function getFilename(){
		return $this->filename;
	}

	public function setFile($val){
		$this->file = $val;
	}

	public function getFile(){
		return $this->file;
	}

	public function setFilename($filename){
		$this->filename = $filename;
	}

	public function getCreatedAt(){
		return $this->createdAt;
	}

	public function setCreatedAt($createdAt){
		$this->createdAt = $createdAt;
	}

	public function getCreatedBy(){
		return $this->createdBy;
	}

	public function setCreatedBy($createdBy){
		$this->createdBy = $createdBy;
	}
}
