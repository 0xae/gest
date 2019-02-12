<?php

namespace Admin\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sugestion
 *
 * @ORM\Table(name="sugestion", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
class Sugestion {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string (reclamacao | sugestao)
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(name="state", type="string", length=45, nullable=true)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ocurrence_date", type="date", length=45, nullable=true)
     */
    private $date;

    /**
     * @var \Location
     *
     * @ORM\Column(name="address", type="string", length=250, nullable=true)
     */
     private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=45, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=45, nullable=true)
     */
    private $email;    

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=250, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="annex", type="string", length=250, nullable=true)
     */
    private $annex;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="date", nullable=true)
     */
    private $createdAt;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * })
     * 
     * old: ORM\Column(name="created_by", type="bigint", nullable=true)
     */
    private $createdBy;

    /**
     * @var \Module
     *
     * @ORM\ManyToOne(targetEntity="Module")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="module_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $module;
    
    /**
     * @var \Stage
     *
     * @ORM\ManyToOne(targetEntity="Stage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stage_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $stage;

    /**
     * @var \Admin\Backend\Entity\AppEntity
     *
     * @ORM\ManyToOne(targetEntity="Admin\Backend\Entity\AppEntity")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="entity", referencedColumnName="id", nullable=true)
     * })
     */
    private $entity;

    /**
     * @var \Stage
     *
     * @ORM\Column(name="approval_reason", type="text", nullable=true)
     */
    private $approvalReason;

    /**
     * @var \Stage
     *
     * @ORM\Column(name="rejection_reason", type="text", nullable=true)
     */
    private $rejectionReason;

    /**
     * @ORM\Column(name="client_response", type="text", nullable=true)
     */
    private $clientResponse;

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

    /**
     * @ORM\Column(name="par_type", type="string", length=250, nullable=true)
     */
    private $parType;

    /**
     * @ORM\Column(name="par_code", type="string", length=250, nullable=true)
     */
    private $parCode;

    /**
     * @ORM\Column(name="par_date", type="datetime", length=250, nullable=true)
     */
    private $parDate;

    /**
     * @ORM\Column(name="par_subject", type="string", length=250, nullable=true)
     */
    private $parSubject;

    /**
     * @ORM\Column(name="par_dest", type="string", length=250, nullable=true)
     */
    private $parDest;

    /**
     * @ORM\Column(name="par_description", type="string", length=250, nullable=true)
     */
    private $parDescription;

    /**
     * @var integer
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="par_author", referencedColumnName="id", nullable=true)
     * })
     * 
     */
    private $parAuthor;

    /**
     * @var string
     *
     * @ORM\Column(name="annex_reference", type="string", length=250, nullable=true)
     */
    private $annexReference;

    public $isDisabled=false;    
    public function isDisabled() {
        return $this->isDisabled ||
            $this->state == Stage::RESPONDIDO ||
            $this->state == Stage::SEM_RESPOSTA;
    }

    /**
     * Set entity
     *
     * @param \Admin\Backend\Entity\AppEntity $entity
     * @return User
     */
    public function setEntity(\Admin\Backend\Entity\AppEntity $entity = null)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Get entity
     *
     * @return \Admin\Backend\Entity\AppEntity 
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function isNoCompetence() {
        return $this->state == Stage::NO_COMP;
    }

    /**
     * Set factAnnex
     *
     * @param string $factAnnex
     * @return IReclamation
     */
    public function setAnnexReference($ref) {
        $this->annexReference = $ref;
        return $this;
    }

    /**
     * Get factAnnex
     *
     * @return string
     */
    public function getAnnexReference() {
        return $this->annexReference;
    }

    public function setAnnex($value) {        
        $this->annex = $value;
        return $this;
    }

    public function getAnnex() {
        return $this->annex;
    }

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

    public function getParDate(){
		return $this->parDate;
	}

	public function setParDate($date){
		$this->parDate = $date;
	}

    private $_respDate='NONE';
    public function setRespDate($value)
    {
        # code...
        $this->_respDate=$value;
    }

    public function getRespDate(){
        // $date = clone $this->createdAt;
        // $date->add(new \DateInterval("P15D"));
        return $this->_respDate;
	}

    public function getParType(){
		return $this->parType;
	}

	public function setParType($parType){
		$this->parType = $parType;
	}

    public function getParAuthor(){
		return $this->parAuthor;
	}

	public function setParAuthor($parAuthor){
		$this->parAuthor = $parAuthor;
	}

    public function getParCode(){
		return $this->parCode;
	}

	public function setParCode($parCode){
		$this->parCode = $parCode;
	}

	public function getParSubject(){
		return $this->parSubject;
	}

	public function setParSubject($parSubject){
		$this->parSubject = $parSubject;
	}

	public function getParDest(){
		return $this->parDest;
	}

	public function setParDest($parDest){
		$this->parDest = $parDest;
	}

	public function getParDescription(){
		return $this->parDescription;
	}

	public function setParDescription($parDescription){
		$this->parDescription = $parDescription;
	}

	public function getRejectionReason(){
		return $this->rejectionReason;
	}

	public function setRejectionReason($rejectionReason){
		$this->rejectionReason = $rejectionReason;
	}

    public function setClientResponse($value) {
        $this->clientResponse = $value;
    }

    public function getClientResponse() {
        return $this->clientResponse;
    }

    public function getApprovalReason(){
        return $this->approvalReason;
    }

    public function setApprovalReason($approvalReason){
        $this->approvalReason = $approvalReason;
    }

    public function getObjCode() {
        if ($this->type == 'reclamacao') {
            $ty = '/RE/';
        } else {
            $ty = '/SG/';
        }

        $entityId = 00;
        if ($this->createdBy->getEntity()){
            $entityId=$this->createdBy
                           ->getEntity()
                           ->getCode(); 
        }

        $id = str_pad($this->id, 3, '0', STR_PAD_LEFT);
        return $id . $ty . $entityId .
                '/' . $this->createdAt->format("Y");
    }

    public function getState(){
		return $this->state;
	}

	public function setState($value){
		$this->state = $value;
	}

    public function getModule(){
		return $this->module;
	}

	public function setModule($module){
		$this->module = $module;
	}

	public function getStage(){
		return $this->stage;
	}

	public function setStage($stage){
		$this->stage = $stage;
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
     * Set type
     *
     * @param string $type
     * @return Sugestion
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Sugestion
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Sugestion
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Sugestion
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Sugestion
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set date
     *
     * @param string $date
     * @return Sugestion
     */
    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Sugestion
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Sugestion
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param integer $createdBy
     * @return Sugestion
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return integer 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }
}
