<?php

namespace Admin\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CompBook
 *
 * @ORM\Table(name="comp_book", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
class CompBook {
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
     * @ORM\Column(name="client_name", type="string", length=45, nullable=false)
     */
    private $clientName;


    /**
     * @var string
     *
     * @ORM\Column(name="supplier_name", type="string", length=45, nullable=false)
     */
    private $supplierName;
    
    /**
     *
     * @ORM\Column(name="supplier_address", type="string", length=250, nullable=true)
     */
    private $supplierAddress;

    /**
     *
     * @ORM\Column(name="state", type="string", length=250, nullable=true)
     */
    private $state;

    /**
     * @ORM\Column(name="client_address", type="string", length=250, nullable=true)
     */
    private $clientAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="client_nacionality", type="string", length=45, nullable=false)
     */
    private $clientNacionality;

    /**
     * @var string
     *
     * @ORM\Column(name="client_phone", type="string", length=45, nullable=false)
     */
    private $clientPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="client_passport", type="string", length=45, nullable=true)
     */
    private $clientPassport;

    /**
     * @var string
     *
     * @ORM\Column(name="client_bi", type="string", length=45, nullable=true)
     */
    private $clientBI;

    /**
     * @var string
     *
     * @ORM\Column(name="client_email", type="string", length=45, nullable=false)
     */
    private $clientEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="complaint", type="string", length=250, nullable=false)
     */
    private $complaint;

    /**
     * @var string
     *
     * @ORM\Column(name="send_date", type="date", nullable=true)
     */
    private $sendDate;

    /**
     * @var string
     *
     * @ORM\Column(name="send_to", type="string", length=250, nullable=true)
     */
    private $sendTo;    

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="complaint_date", type="date", nullable=false)
     */
    private $complaintDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=false)
     * })
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="annex_reference", type="string", length=250, nullable=true)
     */
    private $annexReference;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="response_author", referencedColumnName="id", nullable=true)
     * })
     * 
     */
    private $responseAuthor;

    /**
     * @var \DateTime
     * @ORM\Column(name="response_date", type="date", length=45, nullable=true)
     */
    private $responseDate;

    public function setResponseDate($value) {        
        $this->responseDate = $value;
        return $this;
    }

    public function getResponseDate() {
        return $this->responseDate;
    }

    public function setResponseAuthor($value) {        
        $this->responseAuthor = $value;
        return $this;
    }

    public function getResponseAuthor() {
        return $this->responseAuthor;
    }

    public function setSendDate($value) {
        $this->sendDate = $value;        
    }
    public function getSendDate() {
        return $this->sendDate;      
    }
    public function setSendTo($value) {
        $this->sendTo = $value;
    }
    public function getSendTo() {
        return $this->sendTo;
    }
    
    public function setAnnexReference($ref) {
        $this->annexReference = $ref;
        return $this;
    }

    public function getState() {
        return $this->state;
    }

    public function setState($val){
		return $this->state = $val;
    }    

    public function getObjCode() {
        return str_pad($this->id, 3, '0', STR_PAD_LEFT);
    }

    
    /**
     * Get factAnnex
     *
     * @return string
     */
    public function getAnnexReference() {
        return $this->annexReference;
    }

    public function getComplaintDate(){
		return $this->complaintDate;
    }
    
    public $isDisabled=false;    
    public function isDisabled() {
        return $this->isDisabled ||
            $this->state == Stage::RESPONDIDO ||
            $this->state == Stage::SEM_RESPOSTA;
    }

    public function getRespDate(){
        $date = clone $this->createdAt;
        $date->add(new \DateInterval("P10D"));
        return $date;
	}

	public function setComplaintDate($complaintDate){
		$this->complaintDate = $complaintDate;
	}

    /**
     * Get id
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    public function setId($d) {
        return $this->id=$d;
    }

    /**
     * Set name
     * @param string $name
     * @return Category
     */
    public function setClientName($name) {
        $this->clientName = $name;
        return $this;
    }

    /**
     * Get name
     * @return string 
     */
    public function getClientName() {
        return $this->clientName;
    }

    /**
     * Set createdAt
     * @param \DateTime $createdAt
     * @return Category
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     * @param integer $createdBy
     * @return Category
     */
    public function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * Get createdBy
     * @return integer 
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set description
     * @param string $description
     * @return Document
     */
    public function setComplaint($description) {
        $this->complaint = $description;
        return $this;
    }

    /**
     * Get description
     * @return string 
     */
    public function getComplaint() {
        return $this->complaint;
    }
    public function getSupplierName(){
		return $this->supplierName;
	}

	public function setSupplierName($supplierName){
		$this->supplierName = $supplierName;
	}

	public function getSupplierAddress(){
		return $this->supplierAddress;
	}

	public function setSupplierAddress($supplierAddress){
		$this->supplierAddress = $supplierAddress;
    }
    
    public function getClientBi(){
		return $this->clientBI;
	}

	public function setClientBi($val){
		$this->clientBI = $val;
	}

	public function getClientAddress(){
		return $this->clientAddress;
	}

	public function setClientAddress($clientAddress){
		$this->clientAddress = $clientAddress;
	}

	public function getClientNacionality(){
		return $this->clientNacionality;
	}

	public function setClientNacionality($clientNacionality){
		$this->clientNacionality = $clientNacionality;
	}

	public function getClientPhone(){
		return $this->clientPhone;
	}

	public function setClientPhone($clientPhone){
		$this->clientPhone = $clientPhone;
	}

	public function getClientPassport(){
		return $this->clientPassport;
	}

	public function setClientPassport($clientPassport){
		$this->clientPassport = $clientPassport;
	}

	public function getClientEmail(){
		return $this->clientEmail;
	}

	public function setClientEmail($clientEmail){
		$this->clientEmail = $clientEmail;
	}
}
