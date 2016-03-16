<?php
namespace Application\Model;

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Application\Utilities\NumberPlay;
use Application\Entity\Programme;
use Application\Entity\Agent;
use Application\Entity\Student;
use Application\Entity\StudentFeeBreakDown;

class StudentORM
{

    CONST STUDENT_ENTITY = 'Application\Entity\Student';

    CONST INSTITUTE_ENTITY = 'Application\Entity\Institute';

    CONST PROGRAMME_ENTITY = 'Application\Entity\Programme';

    CONST STUDENT_FEE_BBEAKDOWN = 'Application\Entity\StudentFeeBreakDown';

    /**
     *
     * @var EntityManager
     */
    protected $ormEntityMgr;

    /**
     *
     * @var ProgrammeORM
     */
    protected $programmeModel;

    /**
     *
     * @var AgentORM
     */
    protected $agentModel;

    /**
     *
     * @var InstituteORM
     */
    protected $instituteModel;

    /**
     *
     * @return InstituteORM $instituteModel
     */
    public function getInstituteModel()
    {
        return $this->instituteModel;
    }

    /**
     *
     * @param \Application\Model\InstituteORM $instituteModel            
     */
    public function setInstituteModel($instituteModel)
    {
        $this->instituteModel = $instituteModel;
    }

    /**
     *
     * @return EntityManager $ormEntityMgr
     */
    public function getOrmEntityMgr()
    {
        return $this->ormEntityMgr;
    }

    /**
     *
     * @return ProgrammeORM $programmeModel
     */
    public function getProgrammeModel()
    {
        return $this->programmeModel;
    }

    /**
     *
     * @return AgentORM $agentModel
     */
    public function getAgentModel()
    {
        return $this->agentModel;
    }

    /**
     *
     * @param \Doctrine\ORM\EntityManager $ormEntityMgr            
     */
    public function setOrmEntityMgr($ormEntityMgr)
    {
        $this->ormEntityMgr = $ormEntityMgr;
    }

    /**
     *
     * @param \Application\Model\ProgrammeORM $programmeModel            
     */
    public function setProgrammeModel($programmeModel)
    {
        $this->programmeModel = $programmeModel;
    }

    /**
     *
     * @param \Application\Model\AgentORM $agentModel            
     */
    public function setAgentModel($agentModel)
    {
        $this->agentModel = $agentModel;
    }

    public function updateUser($id, $data, $structOptions)
    {
        $data = NumberPlay::cleaner($data);
        
        $problem = false;
        $errors = array();
        $errors['emailId'] = array();
        $errors['mobile'] = array();
        $errors['programmeId'] = array();
        $errors['agentId'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $student = $this->fetchAll(array(
            'id' => $id
        ), null, true)[0];
        if (empty($student)) {
            return false;
        }
        /* @var $student Student */
        $oldEmaildId = $student->getEmailId();
        $oldMobile = $student->getMobile();
        if (! empty($data['emailId']) && ($data['emailId'] !== $oldEmaildId)) {
            $stByEmail = $this->fetchAll(array(
                'emailId' => $data['emailId']
            ))[0];
            if (! empty($stByEmail)) {
                $errors['emailId'][] = sprintf('Email id: %s is already registered', $data['emailId']);
                $problem = true;
            } else {
                $student->setEmailId($data['emailId']);
            }
        }
        
        if (! empty($data['mobile']) && ($data['mobile'] !== $oldMobile)) {
            $stByMobile = $this->fetchAll(array(
                'mobile' => $data['mobile']
            ))[0];
            if (! empty($stByMobile)) {
                $errors['mobile'][] = sprintf('mobile : %s is already registered', $data['mobile']);
                $problem = true;
            } else {
                $student->setMobile($data['mobile']);
            }
        }
        if (! empty($data['agentId'])) {
            $agentId = $data['agentId'];
            $agent = $this->getAgentModel()->fetchAll(array(
                'id' => $agentId
            ), null, true)[0];
            if (empty($agent)) {
                $errors['agentId'][] = sprintf('agentId : %s is invalid', $data['agentId']);
                $problem = true;
            } else {
                $student->setAgent($agent);
            }
        }
        if (! empty($data['programmeId'])) {
            $programmeId = $data['programmeId'];
            $programme = $this->getProgrammeModel()->fetchAll(array(
                'id' => $programmeId
            ), null, true)[0];
            if (empty($programme)) {
                $errors['programmeId'][] = sprintf('programmeId : %s is invalid', $data['programmeId']);
                $problem = true;
            } else {
                $student->setProgramme($programme);
            }
        }
        if ($problem) {
            return $errors;
        }
        if (! empty($data['name'])) {
            $student->setName($data['name']);
        }
        if (! empty($data['dateOfBirth'])) {
            $student->setDateOfBirth($data['dateOfBirth']);
        }
        if (! empty($data['gender'])) {
            $student->setGender($data['gender']);
        }
        if (! empty($data['address'])) {
            $student->setAddress($data['address']);
        }
        if (isset($data['commissionStatus'])) {
            $student->setCommissionStatus($data['commissionStatus']);
        }
        if (! empty($data['commissionToBePaidByInstitute'])) {
            $student->setCommissionToBePaidByInstitute($data['commissionToBePaidByInstitute']);
        }
        if (isset($data['feeAmount'])) {
            $student->setFeeAmount($data['feeAmount']);
        }
        if (isset($data['feeCurrency'])) {
            $student->setFeeCurrency($data['feeCurrency']);
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->flush($student);
            $connection->commit();
        } catch (\Exception $e) {
            
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        $studentId = $student->getId();
        foreach ($structOptions as $kk => $structOption) {
            $exp = explode('---', $kk);
            $componentId = $exp[1];
            $componentName = $exp[0];
            $this->saveFeeBreakDown($studentId, $componentId, $data[$kk]);
        }
        return $student->getId();
    }

    public function register($data, $structOptions)
    {
        $data = NumberPlay::cleaner($data);
        $problem = false;
        $errors = array();
        $requiredFields = array(
            'emailId' => true,
            'agentId' => true,
            'programmeId' => true,
            'mobile' => true
        );
        $errors['emailId'] = array();
        $errors['agentId'] = array();
        $errors['programmeId'] = array();
        $errors['mobile'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $student = new Student();
        if (empty($data['emailId'])) {
            if ($requiredFields['emailId']) {
                $errors['emailId'][] = 'emailId is required';
                $problem = true;
            }
        } else {
            $stuByEMail = $this->fetchAll(array(
                'emailId' => $data['emailId']
            ))[0];
            if (! empty($stuByEMail)) {
                $errors['emailId'][] = sprintf('Email id: %s is already registered', $data['emailId']);
                $problem = true;
            } else {
                $student->setEmailId($data['emailId']);
            }
        }
        if (empty($data['mobile'])) {
            if ($requiredFields['mobile']) {
                $errors['mobile'][] = 'mobile is required';
                $problem = true;
            }
        } else {
            $stuByMbl = $this->fetchAll(array(
                'mobile' => $data['mobile']
            ))[0];
            if (! empty($stuByMbl)) {
                $errors['mobile'][] = sprintf('mobile : %s is already registered', $data['mobile']);
                $problem = true;
            } else {
                $student->setMobile($data['mobile']);
            }
        }
        if (empty($data['agentId'])) {
            if ($requiredFields['agentId']) {
                $errors['agentId'][] = 'agentId is required';
                $problem = true;
            }
        } else {
            $agentId = $data['agentId'];
            $agent = $this->getAgentModel()->fetchAll(array(
                'id' => $agentId
            ), null, true)[0];
            if (empty($agent)) {
                $errors['agentId'][] = sprintf('agentId : %s is invalid', $data['agentId']);
                $problem = true;
            } else {
                $student->setAgent($agent);
            }
        }
        if (empty($data['programmeId'])) {
            if ($requiredFields['programmeId']) {
                $errors['programmeId'][] = 'programmeId is required';
                $problem = true;
            }
        } else {
            $programmeId = $data['programmeId'];
            $programme = $this->getProgrammeModel()->fetchAll(array(
                'id' => $programmeId
            ), null, true)[0];
            if (empty($programme)) {
                $errors['programmeId'][] = sprintf('programmeId : %s is invalid', $data['programmeId']);
                $problem = true;
            } else {
                $student->setProgramme($programme);
            }
        }
        
        $om = $this->getOrmEntityMgr();
        
        if ($problem) {
            return $errors;
        }
        if (! empty($data['name'])) {
            $student->setName($data['name']);
        }
        if (! empty($data['dateOfBirth'])) {
            $student->setDateOfBirth($data['dateOfBirth']);
        }
        if (! empty($data['gender'])) {
            $student->setGender($data['gender']);
        }
        if (! empty($data['address'])) {
            $student->setAddress($data['address']);
        }
        if (isset($data['commissionStatus'])) {
            $student->setCommissionStatus($data['commissionStatus']);
        }
        if (! empty($data['commissionToBePaidByInstitute'])) {
            $student->setCommissionToBePaidByInstitute($data['commissionToBePaidByInstitute']);
        }
        if (isset($data['feeAmount'])) {
            $student->setFeeAmount($data['feeAmount']);
        }
        
        if (isset($data['feeCurrency'])) {
            $student->setFeeCurrency($data['feeCurrency']);
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            
            $om->persist($student);
            $om->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        $studentId = $student->getId();
        foreach ($structOptions as $kk => $structOption) {
            $exp = explode('---', $kk);
            $componentId = $exp[1];
            $componentName = $exp[0];
            $this->saveFeeBreakDown($studentId, $componentId, $data[$kk]);
        }
        return $student->getId();
    }

    public function fetchStudentFeeBreakDown($searchParams, $getQ = false, $returnObject = false, $returnedInfo = null)
    {
        if (! empty($searchParams)) {
            $searchParams = NumberPlay::cleaner($searchParams);
            if ($searchParams instanceof \Traversable) {
                $searchParams = ArrayUtils::iteratorToArray($searchParams);
            }
            if (is_object($searchParams)) {
                $searchParams = (array) $searchParams;
            }
            if (! is_array($searchParams)) {
                throw new \InvalidArgumentException(sprintf('Invalid searchParams provided to %s; must be an array or Traversable', __METHOD__));
            }
        }
        
        $params = array();
        $om = $this->getOrmEntityMgr();
        $qbs = $om->createQueryBuilder();
        $requiredFields = array();
        if (! empty($returnedInfo)) {
            foreach ($returnedInfo as $field) {
                $requiredFields[] = 'al.' . $field;
            }
            $qbs->select($requiredFields);
        } else {
            $qbs->select(array(
                'al'
            ));
        }
        $qbs->from(static::STUDENT_FEE_BBEAKDOWN, 'al');
        if (isset($searchParams['id'])) {
            $params[':idA'] = $searchParams['id'];
            $qbs->andWhere('al.id = :idA');
        }
        if (isset($searchParams['studentId'])) {
            $params[':studentId'] = $searchParams['studentId'];
            $qbs->andWhere('al.studentId = :studentId');
        }
        if (isset($searchParams['componentId'])) {
            $params[':componentId'] = $searchParams['componentId'];
            $qbs->andWhere('al.componentId = :componentId');
        }
        if (isset($searchParams['amount'])) {
            $params[':amount'] = $searchParams['amount'];
            $qbs->andWhere('al.amount = :amount');
        }
        
        $qbs->setParameters($params);
        $queryq = $qbs->getQuery();
        if ($getQ) {
            return $queryq;
        }
        if ($returnObject) {
            $results = $queryq->getResult();
        } else {
            $results = $queryq->getArrayResult();
        }
        if (empty($results)) {
            return false;
        }
        return $results;
    }

    public function saveFeeBreakDown($studentId, $componentId, $amtInvoiced)
    {
        $breakDownObj = $this->fetchStudentFeeBreakDown(array(
            'studentId' => $studentId,
            'componentId' => $componentId,
            'amountPaid' => $amtInvoiced
        ), null, true)[0];
        if (empty($breakDownObj)) {
            $breakDownObj = new StudentFeeBreakDown();
        }
        $breakDownObj->setAmount($amtInvoiced);
        $breakDownObj->setStudentId($studentId);
        $breakDownObj->setComponentId($componentId);
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->persist($breakDownObj);
            $om->flush($breakDownObj);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
    }

    public function fetchAll($searchParams, $getQ = false, $returnObject = false, $returnedInfo = null)
    {
        if (! empty($searchParams)) {
            $searchParams = NumberPlay::cleaner($searchParams);
            if ($searchParams instanceof \Traversable) {
                $searchParams = ArrayUtils::iteratorToArray($searchParams);
            }
            if (is_object($searchParams)) {
                $searchParams = (array) $searchParams;
            }
            if (! is_array($searchParams)) {
                throw new \InvalidArgumentException(sprintf('Invalid searchParams provided to %s; must be an array or Traversable', __METHOD__));
            }
        }
        
        $params = array();
        $om = $this->getOrmEntityMgr();
        $qbs = $om->createQueryBuilder();
        $requiredFields = array();
        if (empty($returnObject)) {
            $requiredFields[] = 'IDENTITY(al.programme) as programmeId';
        }
        if (! empty($returnedInfo)) {
            foreach ($returnedInfo as $field) {
                $requiredFields[] = 'al.' . $field;
            }
            $qbs->select($requiredFields);
        } else {
            $requiredFields[] = 'al';
            $qbs->select($requiredFields);
        }
        $qbs->from(static::STUDENT_ENTITY, 'al');
        if (isset($searchParams['id'])) {
            $params[':idA'] = $searchParams['id'];
            $qbs->andWhere('al.id = :idA');
        }
        if (isset($searchParams['name'])) {
            $params[':nm'] = $searchParams['name'];
            $qbs->andWhere('al.name = :nm');
        }
        if (isset($searchParams['emailId'])) {
            $params[':email'] = $searchParams['emailId'];
            $qbs->andWhere('al.emailId = :email');
        }
        if (isset($searchParams['dateOfBirth'])) {
            $params[':dob'] = $searchParams['dateOfBirth'];
            $qbs->andWhere('al.dateOfBirth = :dob');
        }
        if (isset($searchParams['mobile'])) {
            $params[':mbl'] = $searchParams['mobile'];
            $qbs->andWhere('al.mobile = :mbl');
        }
        if (isset($searchParams['gender'])) {
            $params[':gen'] = $searchParams['gender'];
            $qbs->andWhere('al.gender = :gen');
        }
        if (isset($searchParams['programmeId'])) {
            $programmeId = $searchParams['programmeId'];
            $programme = $this->getProgrammeModel()->fetchAll(array(
                'id' => $programmeId
            ), null, true)[0];
            if (! empty($programme)) {
                $params[':programme'] = $programme;
                $qbs->andWhere('al.programme = :programme');
            }
        }
        if (isset($searchParams['instituteId'])) {
            $instituteId = $searchParams['instituteId'];
            $institute = $this->getInstituteModel()->fetchAll(array(
                'id' => $instituteId
            ), null, true)[0];
            if (! empty($institute)) {
                $qbs->join(static::PROGRAMME_ENTITY, 'programme');
                $qbs->andWhere('programme.institute = :inst');
                $params[':inst'] = $institute;
            }
        }
        if (isset($searchParams['agentId'])) {
            $agentId = $searchParams['agentId'];
            $agent = $this->getAgentModel()->fetchAll(array(
                'id' => $agentId
            ), null, true)[0];
            if (! empty($agent)) {
                $params[':agent'] = $agent;
                $qbs->andWhere('al.agent = :agent');
            }
        }
        if (isset($searchParams['feeAmount'])) {
            $params[':feeAmount'] = $searchParams['feeAmount'];
            $qbs->andWhere('al.feeAmount = :feeAmount');
        }
        
        if (isset($searchParams['feeCurrency'])) {
            $params[':feeCurrency'] = $searchParams['feeCurrency'];
            $qbs->andWhere('al.feeCurrency = :feeCurrency');
        }
        if (isset($searchParams['commissionToBePaidByInstitute'])) {
            $params[':cms'] = $searchParams['commissionToBePaidByInstitute'];
            $qbs->andWhere('al.commissionToBePaidByInstitute = :cms');
        }
        if (isset($searchParams['commissionStatus'])) {
            $params[':commissionStatus'] = $searchParams['commissionStatus'];
            $qbs->andWhere('al.commissionStatus = :commissionStatus');
        }
        if (isset($searchParams['enabled'])) {
            $params[':enabled'] = $searchParams['enabled'];
            $qbs->andWhere('al.enabled = :enabled');
        }
        
        $qbs->setParameters($params);
        $queryq = $qbs->getQuery();
        if ($getQ) {
            return $queryq;
        }
        if ($returnObject) {
            $results = $queryq->getResult();
        } else {
            $results = $queryq->getArrayResult();
            
            if (! empty($results)) {
                foreach ($results as $k => $result) {
                    $prgId = $result['programmeId'];
                    $result[0]['programmeId'] = $prgId;
                    unset($result['programmeId']);
                    $prgInfo = $this->programmeModel->fetchAll(array(
                        'id' => $prgId
                    ))[0];
                    $result[0]['programmeInfo'] = $prgInfo;
                    $results[$k] = $result[0];
                }
            }
        }
        if (empty($results)) {
            return false;
        }
        return $results;
    }
}
