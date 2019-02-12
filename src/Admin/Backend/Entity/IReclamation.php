<?php

namespace Admin\Backend\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="reclamation_internal", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
class IReclamation {
    const START=1;
    const ANALYSIS=2;
    const DECISION=3;
    const ACTION=4;
    const CONCLUDED=5;

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
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="direction", type="string", length=45, nullable=true)
     */
    private $direction;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=250, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="type_data", type="string", length=250, nullable=true)
     */
    private $typeData;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=250, nullable=true)
     */
    private $state;    

    /**
     * @var DateTime
     *
     * @ORM\Column(name="fact_date", type="date", length=45, nullable=false)
     */
    private $factDate;

    /**
     * @var string
     *
     * @ORM\Column(name="annex_reference", type="string", length=250, nullable=true)
     */
    private $annexReference;

    /**
     * @var string
     *
     * @ORM\Column(name="fact_detail", type="string", length=250, nullable=false)
     */
    private $factDetail;

    /**
     * @var string
     *
     * @ORM\Column(name="analysis_detail", type="string", length=250, nullable=true)
     */
    private $analysisDetail;

    /**
     * @var \Profile
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="analysis_resp", referencedColumnName="id", nullable=true)
     * })
     */
    private $analysisResp;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="analysis_date", type="date", nullable=true)
     */
    private $analysisDate;

    /**
     * @var string
     *
     * @ORM\Column(name="decision_detail", type="string", length=250, nullable=true)
     */
    private $decisionDetail;

    /**
     * @var \Profile
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="decision_resp", referencedColumnName="id", nullable=true)
     * })
     */
    private $decisionResp;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="decision_date", type="date", nullable=true)
     */
    private $decisionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="action_detail", type="string", length=250, nullable=true)
     */
    private $actionDetail;

    /**
     * @var string
     *
     * @ORM\Column(name="step", type="string", length=250, nullable=true)
     */
    private $step;

    /**
     * @var \Profile
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_resp", referencedColumnName="id", nullable=true)
     * })
     */
    private $actionResp;

    /**
     * @var string
     *
     * @ORM\Column(name="action_date", type="date", nullable=true)
     */
    private $actionDate;

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
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id", nullable=true)
     * })
     * 
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="response_content", type="string", length=250, nullable=true)
     */
    private $responseContent;

    /**
     * @var \DateTime
     * @ORM\Column(name="response_date", type="datetime", nullable=true)
     */
    private $responseDate;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="response_author", referencedColumnName="id", nullable=true)
     * })
     */
    private $responseAuthor;   

    public $isDisabled=false;    
    public function isDisabled() {
        return $this->isDisabled ||
            $this->state == Stage::RESPONDIDO ||
            $this->state == Stage::SEM_RESPOSTA;
    }

    public function getLabelStep() {
        $label = "";
        if ($this->step == 2) {
            $label="Análise";
        } else if ($this->step == 3) {
            $label="Decisão";            
        } else if ($this->step == 4) {
            $label="Ação";            
        }
        return $label;        
    }

	public function setStep($val){
		$this->step = $val;
	}

	public function getStep(){
		return $this->step;
	}

    public function getResponseContent(){
		return $this->responseContent;
	}

	public function setResponseContent($responseContent){
		$this->responseContent = $responseContent;
	}

	public function getResponseDate(){
		return $this->responseDate;
	}

	public function setResponseDate($responseDate){
		$this->responseDate = $responseDate;
	}

	public function getResponseAuthor(){
		return $this->responseAuthor;
	}

	public function setResponseAuthor($responseAuthor){
		$this->responseAuthor = $responseAuthor;
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
        // return $date;
        return $this->_respDate;
	}

    public function getObjCode() {
        $id = str_pad($this->id, 3, '0', STR_PAD_LEFT);
        $code = '';

        if ($this->getCreatedBy()->getEntity()) {
            $code = $this->getCreatedBy()
                         ->getEntity()
                         ->getCode();
        }

        return $id . '/RI/' . 
                $code .
                '/' . $this->createdAt->format("Y");
    }

    public function getTypeLabel() {
        $ary = [
            'c1' => 'Ações de Formação',
            'c2' => 'Atendimento',
            'c3' => 'Assistência Médica',
            'c4' => 'Equipamentos',
            'c5' => 'Instalações',
            'c6' => 'Limpeza',
            'c7' => 'Segurança',
            'c8' => 'Viaturas e logística de deslocação',
            'c9' => 'Outros'
        ];

        return @$ary[$this->type];
    }

    public function getTypeData() {
        return $this->typeData;
    }

    public function setTypeData($val) {
        $this->typeData = $val;
        return $this;
    }

    public function getState() {
        return $this->state;
    }

    public function setState($val) {
        $this->state = $val;
        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Category
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Category
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set createdBy
     *
     * @param integer $createdBy
     * @return Category
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

    /**
     * Set direction
     *
     * @param string $direction
     * @return IReclamation
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get direction
     *
     * @return string 
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return IReclamation
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
     * Set factDate
     *
     * @param \DateTime $factDate
     * @return IReclamation
     */
    public function setFactDate($factDate) {
        $this->factDate = $factDate;
        return $this;
    }

    /**
     * Get factDate
     *
     * @return \DateTime 
     */
    public function getFactDate()
    {
        return $this->factDate;
    }

    /**
     * Set factAnnex
     *
     * @param string $factAnnex
     * @return IReclamation
     */
    public function setAnnexReference($val) {
        $this->annexReference = $val;
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

    /**
     * Set factDetail
     *
     * @param string $factDetail
     * @return IReclamation
     */
    public function setFactDetail($factDetail)
    {
        $this->factDetail = $factDetail;

        return $this;
    }

    /**
     * Get factDetail
     *
     * @return string 
     */
    public function getFactDetail()
    {
        return $this->factDetail;
    }

    /**
     * Set analysisDetail
     *
     * @param string $analysisDetail
     * @return IReclamation
     */
    public function setAnalysisDetail($analysisDetail)
    {
        $this->analysisDetail = $analysisDetail;

        return $this;
    }

    /**
     * Get analysisDetail
     *
     * @return string 
     */
    public function getAnalysisDetail()
    {
        return $this->analysisDetail;
    }

    /**
     * Set analysisDate
     *
     * @param \DateTime $analysisDate
     * @return IReclamation
     */
    public function setAnalysisDate($analysisDate)
    {
        $this->analysisDate = $analysisDate;

        return $this;
    }

    /**
     * Get analysisDate
     *
     * @return \DateTime 
     */
    public function getAnalysisDate()
    {
        return $this->analysisDate;
    }

    /**
     * Set decisionDetail
     *
     * @param string $decisionDetail
     * @return IReclamation
     */
    public function setDecisionDetail($decisionDetail)
    {
        $this->decisionDetail = $decisionDetail;

        return $this;
    }

    /**
     * Get decisionDetail
     *
     * @return string 
     */
    public function getDecisionDetail()
    {
        return $this->decisionDetail;
    }

    /**
     * Set decisionDate
     *
     * @param \DateTime $decisionDate
     * @return IReclamation
     */
    public function setDecisionDate($decisionDate)
    {
        $this->decisionDate = $decisionDate;

        return $this;
    }

    /**
     * Get decisionDate
     *
     * @return \DateTime 
     */
    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    /**
     * Set actionDetail
     *
     * @param string $actionDetail
     * @return IReclamation
     */
    public function setActionDetail($actionDetail)
    {
        $this->actionDetail = $actionDetail;

        return $this;
    }

    /**
     * Get actionDetail
     *
     * @return string 
     */
    public function getActionDetail()
    {
        return $this->actionDetail;
    }

    /**
     * Set actionDate
     *
     * @param \DateTime $actionDate
     * @return IReclamation
     */
    public function setActionDate($actionDate)
    {
        $this->actionDate = $actionDate;

        return $this;
    }

    /**
     * Get actionDate
     *
     * @return \DateTime 
     */
    public function getActionDate()
    {
        return $this->actionDate;
    }

    /**
     * Set analysisResp
     *
     * @param \Admin\Backend\Entity\User $analysisResp
     * @return IReclamation
     */
    public function setAnalysisResp(\Admin\Backend\Entity\User $analysisResp = null)
    {
        $this->analysisResp = $analysisResp;

        return $this;
    }

    /**
     * Get analysisResp
     *
     * @return \Admin\Backend\Entity\User 
     */
    public function getAnalysisResp()
    {
        return $this->analysisResp;
    }

    /**
     * Set decisionResp
     *
     * @param \Admin\Backend\Entity\User $decisionResp
     * @return IReclamation
     */
    public function setDecisionResp(\Admin\Backend\Entity\User $decisionResp = null)
    {
        $this->decisionResp = $decisionResp;

        return $this;
    }

    /**
     * Get decisionResp
     *
     * @return \Admin\Backend\Entity\User 
     */
    public function getDecisionResp()
    {
        return $this->decisionResp;
    }

    /**
     * Set actionResp
     *
     * @param \Admin\Backend\Entity\User $actionResp
     * @return IReclamation
     */
    public function setActionResp(\Admin\Backend\Entity\User $actionResp = null)
    {
        $this->actionResp = $actionResp;

        return $this;
    }

    /**
     * Get actionResp
     *
     * @return \Admin\Backend\Entity\User 
     */
    public function getActionResp()
    {
        return $this->actionResp;
    }
}
