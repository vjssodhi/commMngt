<?php
namespace Application\Model;

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Application\Entity\Institute;
use Application\Utilities\NumberPlay;
use Application\Entity\Agent;
use Application\Entity\AgentPayment;

class AgentORM
{

    CONST PROGRAMME_ENTITY = 'Application\Entity\Programme';

    CONST AGENT_ENTITY = 'Application\Entity\Agent';

    CONST STUDENT_ENTITY = 'Application\Entity\Student';

    CONST AGENT_PAYMENT_ENTITY = 'Application\Entity\AgentPayment';

    /**
     *
     * @var InstituteORM
     */
    protected $instituteModel;

    /**
     *
     * @var EntityManager
     */
    protected $ormEntityMgr;

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

    public function getOrmEntityMgr()
    {
        return $this->ormEntityMgr;
    }

    public function setOrmEntityMgr($ormEntityMgr)
    {
        $this->ormEntityMgr = $ormEntityMgr;
    }

    public function fetchAgentPaymentInfo($searchParams, $getQ = false, $returnObject = false, $returnedInfo = null)
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
        $qbs->from(static::AGENT_PAYMENT_ENTITY, 'al');
        if (isset($searchParams['id'])) {
            $params[':idA'] = $searchParams['id'];
            $qbs->andWhere('al.id = :idA');
        }
        if (isset($searchParams['emailId'])) {
            $params[':emailIdP'] = $searchParams['emailId'];
            $qbs->andWhere('al.emailId = :emailIdP');
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

    public function updatePayment($agentEmail, $totalCommission, $paidAmmount)
    {
        $payment = new AgentPayment();
        $payment->setEmailId($agentEmail);
        $payment->setTotalCommission($totalCommission);
        $payment->setPaidAmmount($paidAmmount);
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            
            $om->persist($payment);
            $om->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        
        return $payment->getId();
    }

    public function register($institutId, $data)
    {
        $data = NumberPlay::cleaner($data);
        $problem = false;
        $requiredFields = array(
            'name' => true,
            'emailId' => true,
            'mobile' => true,
            'address' => true,
            'institute' => true,
            'enabled' => true,
            'commissionPercentage' => true
        );
        $errors = array();
        $errors['emailId'] = array();
        $errors['mobile'] = array();
        $errors['institute'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $agent = new Agent();
        $institute = $this->getInstituteModel()->fetchAll(array(
            'id' => $institutId
        ), null, true)[0];
        if (empty($institute)) {
            $errors['institute'] = sprintf('institute id: %s is invalid', $institutId);
            return $errors;
        }
        //
        if (empty($data['name'])) {
            if ($requiredFields['name']) {
                $errors['name'][] = 'name is required';
                $problem = true;
            }
        } else {
            $agent->setName($data['name']);
        }
        if (empty($data['emailId'])) {
            if ($requiredFields['emailId']) {
                $errors['emailId'][] = 'email Id is required';
                $problem = true;
            }
        } else {
            $agentEml = $this->fetchAll(array(
                'emailId' => $data['emailId'],
                'institute' => $institute
            ))[0];
            if (! empty($agentEml)) {
                $errors['emailId'][] = sprintf('Email id: %s is already registered', $data['emailId']);
                $problem = true;
            } else {
                $agent->setEmailId($data['emailId']);
            }
        }
        if (empty($data['mobile'])) {
            if ($requiredFields['mobile']) {
                $errors['mobile'][] = 'mobile is required';
                $problem = true;
            }
        } else {
            $agentMbl = $this->fetchAll(array(
                'mobile' => $data['mobile'],
                'institute' => $institute
            ))[0];
            if (! empty($agentMbl)) {
                $errors['mobile'][] = sprintf('mobile : %s is already registered', $data['mobile']);
                $problem = true;
            } else {
                $agent->setMobile($data['mobile']);
            }
        }
        if ($problem) {
            return $errors;
        }
        //
        if (! empty($data['address'])) {
            $agent->setAddress($data['address']);
        }
        if (isset($data['commissionPercentage'])) {
            $agent->setCommissionPercentage($data['commissionPercentage']);
        }
        if (isset($data['enabled'])) {
            $agent->setEnabled($data['enabled']);
        }
        $agent->setInstitute($institute);
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            
            $om->persist($agent);
            $om->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        
        return $agent->getId();
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
            $requiredFields[] = 'IDENTITY(al.institute) as instituteId';
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
        $qbs->from(static::AGENT_ENTITY, 'al');
        $qbs->where('al.createdOn > 1000');
        if (isset($searchParams['id'])) {
            $params[':idA'] = $searchParams['id'];
            $qbs->andWhere('al.id = :idA');
        }
        if (isset($searchParams['name'])) {
            $params[':nm'] = $searchParams['name'];
            $qbs->andWhere('al.name = :nm');
        }
        if (isset($searchParams['address'])) {
            $params[':add'] = $searchParams['address'];
            $qbs->andWhere('al.address = :add');
        }
        if (isset($searchParams['mobile'])) {
            $params[':mbl'] = $searchParams['mobile'];
            $qbs->andWhere('al.mobile = :mbl');
        }
        if (isset($searchParams['emailId'])) {
            $params[':eml'] = $searchParams['emailId'];
            $qbs->andWhere('al.emailId = :eml');
        }
        if (isset($searchParams['enabled'])) {
            $params[':sta'] = $searchParams['enabled'];
            $qbs->andWhere('al.enabled = :sta');
        }
        if (isset($searchParams['institute'])) {
            $institute = $searchParams['institute'];
            if ($institute instanceof Institute) {
                $params[':inst'] = $institute;
                $qbs->andWhere('al.institute = :inst');
            }
        }
        if (isset($searchParams['instituteId'])) {
            $instituteId = $searchParams['instituteId'];
            $institute = $this->getInstituteModel()->fetchAll(array(
                'id' => $instituteId
            ), null, true)[0];
            if (! empty($institute)) {
                $params[':inst'] = $institute;
                $qbs->andWhere('al.institute = :inst');
            }
        }
        if (isset($data['commissionPercentage'])) {
            $params[':cmm'] = $searchParams['commissionPercentage'];
            $qbs->andWhere('al.commissionPercentage = :cmm');
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
                    $result[0]['instituteId'] = $result['instituteId'];
                    unset($result['instituteId']);
                    $results[$k] = $result[0];
                }
            }
        }
        if (empty($results)) {
            return false;
        }
        
        return $results;
    }

    public function update($id, $data)
    {
        $problem = false;
        $errors = array();
        $errors['emailId'] = array();
        $errors['mobile'] = array();
        $errors['institutes'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $agent = $this->fetchAll(array(
            'id' => $id
        ), null, true)[0];
        /* @var $agent Agent */
        if (empty($agent)) {
            return false;
        }
        $institute = $agent->getInstitute();
        $oldMobile = $agent->getMobile();
        $oldEmailId = $agent->getEmailId();
        if (! empty($data['emailId']) && ($data['emailId'] !== $oldEmailId)) {
            $agentEml = $this->fetchAll(array(
                'emailId' => $data['emailId'],
                'institute' => $institute
            ))[0];
            if (! empty($agentEml)) {
                $errors['emailId'][] = sprintf('Email id: %s is already registered', $data['emailId']);
                $problem = true;
            } else {
                $agent->setEmailId($data['emailId']);
            }
        }
        
        if (! empty($data['mobile']) && ($data['mobile'] !== $oldMobile)) {
            $agentMbl = $this->fetchAll(array(
                'mobile' => $data['mobile'],
                'institute' => $institute
            ))[0];
            if (! empty($agentMbl)) {
                $errors['mobile'][] = sprintf('mobile : %s is already registered', $data['mobile']);
                $problem = true;
            } else {
                $agent->setMobile($data['mobile']);
            }
        }
        if ($problem) {
            return $errors;
        }
        //
        if (! empty($data['address'])) {
            $agent->setAddress($data['address']);
        }
        if (isset($data['commissionPercentage'])) {
            $agent->setCommissionPercentage($data['commissionPercentage']);
        }
        if (isset($data['enabled'])) {
            $agent->setEnabled($data['enabled']);
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            
            $om->persist($agent);
            $om->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        
        return $agent->getId();
    }

    public function fetchProgrammes($searchParams, $getQ = false, $returnObject = false, $returnedInfo = null)
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
            $requiredFields[] = 'IDENTITY(al.institute) as instituteId';
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
        $qbs->from(static::PROGRAMME_ENTITY, 'al');
        
        if (isset($searchParams['institute'])) {
            $institute = $searchParams['institute'];
            if ($institute instanceof Institute) {
                $params[':inst'] = $institute;
                $qbs->andWhere('al.institute = :inst');
            }
        }
        if (isset($searchParams['instituteId'])) {
            $instituteId = $searchParams['instituteId'];
            $institute = $this->getInstituteModel()->fetchAll(array(
                'id' => $instituteId
            ), null, true)[0];
            if (! empty($institute)) {
                $params[':inst'] = $institute;
                $qbs->andWhere('al.institute = :inst');
            }
        }
        if (isset($searchParams['id'])) {
            $params[':idA'] = $searchParams['id'];
            $qbs->andWhere('al.id = :idA');
        }
        if (isset($searchParams['name'])) {
            $params[':nm'] = $searchParams['name'];
            $qbs->andWhere('al.name = :nm');
        }
        if (isset($searchParams['abbreviation'])) {
            $params[':abbr'] = $searchParams['abbreviation'];
            $qbs->andWhere('al.abbreviation = :abbr');
        }
        if (isset($searchParams['feeAmount'])) {
            $params[':feeAmt'] = $searchParams['feeAmount'];
            $qbs->andWhere('al.feeAmount = :feeAmt');
        }
        if (isset($searchParams['feeCurrency'])) {
            $params[':feeCurr'] = $searchParams['feeCurrency'];
            $qbs->andWhere('al.feeCurrency = :feeCurr');
        }
        if (isset($searchParams['enabled'])) {
            $params[':sta'] = $searchParams['enabled'];
            $qbs->andWhere('al.enabled = :sta');
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
                    $insId = $result['instituteId'];
                    $result[0]['instituteId'] = $insId;
                    unset($result['instituteId']);
                    $insInfo = $this->instituteModel->fetchAll(array(
                        'id' => $insId
                    ))[0];
                    $result[0]['instituteInfo'] = $insInfo;
                    $results[$k] = $result[0];
                }
            }
        }
        if (empty($results)) {
            return false;
        }
        return $results;
    }

    public function fetchStudents($searchParams, $getQ = false, $returnObject = false, $returnedInfo = null)
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
        
        if (isset($searchParams['agentId'])) {
            $agentId = $searchParams['agentId'];
            $agent = $this->fetchAll(array(
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
                    $prgInfo = $this->fetchProgrammes(array(
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