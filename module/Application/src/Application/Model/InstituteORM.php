<?php
namespace Application\Model;

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Application\Entity\Institute;
use Application\Utilities\NumberPlay;
use Application\Entity\InstituteFeeStructure;

class InstituteORM
{

    CONST INSTITUTE_ENTITY = 'Application\Entity\Institute';

    CONST FEE_STRUCT_ENTITY = 'Application\Entity\InstituteFeeStructure';

    /**
     *
     * @var EntityManager
     */
    protected $ormEntityMgr;

    public function getOrmEntityMgr()
    {
        return $this->ormEntityMgr;
    }

    public function setOrmEntityMgr($ormEntityMgr)
    {
        $this->ormEntityMgr = $ormEntityMgr;
    }

    public function update($id, $data)
    {
        $data = NumberPlay::cleaner($data);
        $problem = false;
        $errors = array();
        $errors['emailId'] = array();
        $errors['emailIdTwo'] = array();
        $errors['phoneNumber'] = array();
        $errors['phoneNumberTwo'] = array();
        $errors['phoneNumberThree'] = array();
        $oldEmailId = $data['emailId'];
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $institute = $this->fetchAll(array(
            'id' => $id
        ), null, true)[0];
        if (empty($institute)) {
            return false;
        }
        /* @var $institute Institute */
        $oldEmailId = $institute->getEmailId();
        if (! empty($data['emailId']) && ($data['emailId'] !== $oldEmailId)) {
            $instituteXT = $this->fetchAll(array(
                'emailId' => $data['emailId']
            ))[0];
            $instituteXT222 = $this->fetchAll(array(
                'emailIdTwo' => $data['emailId']
            ))[0];
            if (! empty($instituteXT) || ! empty($instituteXT222)) {
                $errors['emailId'][] = sprintf('%s is already registered', $data['emailId']);
                $problem = true;
            } else {
                $institute->setEmailId($data['emailId']);
            }
        }
        // ************Email id 2***************************//
        $oldEmailId2 = $institute->getEmailIdTwo();
        if (! empty($data['emailIdTwo']) && ($data['emailIdTwo'] !== $oldEmailId2)) {
            $instituteXT22 = $this->fetchAll(array(
                'emailIdTwo' => $data['emailIdTwo']
            ))[0];
            $instituteXT444 = $this->fetchAll(array(
                'emailId' => $data['emailIdTwo']
            ))[0];
            if (! empty($instituteXT22) || ! empty($instituteXT444)) {
                $errors['emailIdTwo'][] = sprintf('%s is already registered', $data['emailIdTwo']);
                $problem = true;
            } else {
                $institute->setEmailIdTwo($data['emailIdTwo']);
            }
        }
        // //////////////////////////////////////////////////
        $oldphoneNumber = $institute->getPhoneNumber();
        if (! empty($data['phoneNumber']) && ($data['phoneNumber'] !== $oldphoneNumber)) {
            $instituteXDT = $this->fetchAll(array(
                'phoneNumber' => $data['phoneNumber']
            ))[0];
            $instituteXDT1234 = $this->fetchAll(array(
                'phoneNumberTwo' => $data['phoneNumber']
            ))[0];
            $instituteXDT12345 = $this->fetchAll(array(
                'phoneNumberThree' => $data['phoneNumber']
            ))[0];
            if (! empty($instituteXDT) || ! empty($instituteXDT1234) || ! empty($instituteXDT12345)) {
                $errors['phoneNumber'][] = sprintf('%s is already registered', $data['phoneNumber']);
                $problem = true;
            } else {
                $institute->setPhoneNumber($data['phoneNumber']);
            }
        }
        // ************Phone 2***************************//
        $oldPh2 = $institute->getPhoneNumberTwo();
        if (! empty($data['phoneNumberTwo']) && ($data['phoneNumberTwo'] !== $oldPh2)) {
            $institute2XDT = $this->fetchAll(array(
                'phoneNumber' => $data['phoneNumberTwo']
            ))[0];
            $instituteXDT2234 = $this->fetchAll(array(
                'phoneNumberTwo' => $data['phoneNumberTwo']
            ))[0];
            $instituteXDT22345 = $this->fetchAll(array(
                'phoneNumberThree' => $data['phoneNumberTwo']
            ))[0];
            if (! empty($institute2XDT) || ! empty($instituteXDT2234) || ! empty($instituteXDT22345)) {
                $errors['phoneNumberTwo'][] = sprintf('%s is already registered', $data['phoneNumberTwo']);
                $problem = true;
            } else {
                $institute->setPhoneNumberTwo($data['phoneNumberTwo']);
            }
        }
        // ************Phone 3***************************//
        $oldPh3 = $institute->getPhoneNumberThree();
        if (! empty($data['phoneNumberThree']) && ($data['phoneNumberThree'] !== $oldPh3)) {
            $institute3XDT = $this->fetchAll(array(
                'phoneNumber' => $data['phoneNumberThree']
            ))[0];
            $instituteXDT3234 = $this->fetchAll(array(
                'phoneNumberTwo' => $data['phoneNumberThree']
            ))[0];
            $instituteXDT32345 = $this->fetchAll(array(
                'phoneNumberThree' => $data['phoneNumberThree']
            ))[0];
            if (! empty($institute3XDT) || ! empty($instituteXDT3234) || ! empty($instituteXDT32345)) {
                $errors['phoneNumberThree'][] = sprintf('%s is already registered', $data['phoneNumberThree']);
                $problem = true;
            } else {
                $institute->setPhoneNumberThree($data['phoneNumberThree']);
            }
        }
        if ($problem) {
            return $errors;
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->persist($institute);
            $om->flush($institute);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        return $institute->getId();
    }

    public function updateFeeStructure($id, $data)
    {
        $feeStructure = $this->fetchStructures(array(
            'id' => $id
        ), null, true)[0];
        if (empty($feeStructure)) {
            return;
        }
        $institute = $this->fetchAll(array(
            'id' => $data['instituteId']
        ), null, true)[0];
        if (empty($institute)) {
            return;
        }
        /* @var $feeStructure InstituteFeeStructure */
        $feeStructure->setAmount($data['amount']);
        $feeStructure->setEnabled($data['enabled']);
        $feeStructure->setName($data['name']);
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->flush($feeStructure);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
    }

    public function saveFeeStructure($data)
    {
        $problem = false;
        $requiredFields = array(
            'name' => true,
            'instituteId' => true,
            'amount' => true
        );
        $errors = array();
        $errors['name'] = array();
        $errors['instituteId'] = array();
        $errors['amount'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        if (empty($data['instituteId'])) {
            if ($requiredFields['instituteId']) {
                $errors['instituteId'][] = 'instituteId is required';
                $problem = true;
                return $errors;
            }
        }
        $instituteId = $data['instituteId'];
        $feeStructure = new InstituteFeeStructure();
        $institute = $this->fetchAll(array(
            'id' => $instituteId
        ), null, true)[0];
        if (! empty($institute)) {
            $feeStructure->setInstitute($institute);
        } else {
            $errors['instituteId'][] = sprintf('The instituteId: %s does not exist', $data['instituteId']);
            $problem = true;
        }
        if (empty($data['name'])) {
            if ($requiredFields['name']) {
                $errors['name'][] = 'name is required';
                $problem = true;
            }
        } else {
            $feeStructureByName = $this->fetchStructures(array(
                'name' => $data['name'],
                'instituteId' => $instituteId
            ))[0];
            if (! empty($feeStructureByName)) {
                $errors['name'][] = sprintf('The name: %s is already registered', $data['name']);
                $problem = true;
            } else {
                $feeStructure->setName($data['name']);
            }
        }
        if (empty($data['amount'])) {
            if ($requiredFields['amount']) {
                $errors['amount'][] = 'amount is required';
                $problem = true;
            }
        } else {
            $feeStructure->setAmount($data['amount']);
        }
        if ($problem) {
            return $errors;
        }
        if (isset($data['enabled'])) {
            $feeStructure->setEnabled($data['enabled']);
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->persist($feeStructure);
            $om->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        
        return $feeStructure->getId();
    }

    public function register($data)
    {
        $problem = false;
        $requiredFields = array(
            'name' => true,
            'emailId' => true,
            'phoneNumber' => true,
            'country' => true,
            'pincode' => true
        );
        $errors = array();
        $errors['name'] = array();
        $errors['emailId'] = array();
        $errors['phoneNumber'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $institute = new Institute();
        if (empty($data['name'])) {
            if ($requiredFields['name']) {
                $errors['name'][] = 'name is required';
                $problem = true;
            }
        } else {
            $instituteG = $this->fetchAll(array(
                'name' => $data['name']
            ))[0];
            if (! empty($instituteG)) {
                $errors['name'][] = sprintf('The name: %s is already registered', $data['name']);
                $problem = true;
            } else {
                $institute->setName($data['name']);
            }
        }
        if (empty($data['emailId'])) {
            if ($requiredFields['emailId']) {
                $errors['emailId'][] = 'emailId is required';
                $problem = true;
            }
        } else {
            $instituteXT = $this->fetchAll(array(
                'emailId' => $data['emailId']
            ))[0];
            if (! empty($instituteXT)) {
                $errors['emailId'][] = sprintf('emailId id: %s is already registered', $data['emailId']);
                $problem = true;
            } else {
                $institute->setEmailId($data['emailId']);
            }
        }
        // //////////
        if (empty($data['phoneNumber'])) {
            if ($requiredFields['phoneNumber']) {
                $errors['phoneNumber'][] = 'phoneNumber is required';
                $problem = true;
            }
        } else {
            $instituteXDT = $this->fetchAll(array(
                'phoneNumber' => $data['phoneNumber']
            ))[0];
            if (! empty($instituteXDT)) {
                $errors['phoneNumber'][] = sprintf('phoneNumber : %s is already registered', $data['phoneNumber']);
                $problem = true;
            } else {
                $institute->setPhoneNumber($data['phoneNumber']);
            }
        }
        $om = $this->getOrmEntityMgr();
        
        //
        if (! empty($data['emailIdTwo'])) {
            $instituteXT12 = $this->fetchAll(array(
                'emailIdTwo' => $data['emailIdTwo']
            ))[0];
            if (! empty($instituteXT12)) {
                $errors['emailIdTwo'][] = sprintf('emailId Two : %s is already registered', $data['emailIdTwo']);
                $problem = true;
            } else {
                $institute->setEmailIdTwo($data['emailIdTwo']);
            }
        }
        if (! empty($data['phoneNumberTwo'])) {
            $instituteXT13 = $this->fetchAll(array(
                'phoneNumberTwo' => $data['phoneNumberTwo']
            ))[0];
            if (! empty($instituteXT13)) {
                $errors['phoneNumberTwo'][] = sprintf('phoneNumber Two : %s is already registered', $data['phoneNumberTwo']);
                $problem = true;
            } else {
                $institute->setPhoneNumberTwo($data['phoneNumberTwo']);
            }
        }
        if (! empty($data['phoneNumberThree'])) {
            $instituteXT13 = $this->fetchAll(array(
                'phoneNumberThree' => $data['phoneNumberThree']
            ))[0];
            if (! empty($instituteXT13)) {
                $errors['phoneNumberThree'][] = sprintf('phoneNumber Three : %s is already registered', $data['phoneNumberThree']);
                $problem = true;
            } else {
                $institute->setPhoneNumberThree($data['phoneNumberThree']);
            }
        }
        if ($problem) {
            return $errors;
        }
        if (! empty($data['country'])) {
            $institute->setCountry($data['country']);
        }
        if (! empty($data['pincode'])) {
            $institute->setPincode($data['pincode']);
        }
        if (isset($data['enabled'])) {
            $institute->setEnabled($data['enabled']);
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->persist($institute);
            $om->flush($institute);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        
        return $institute->getId();
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
        $qbs->from(static::INSTITUTE_ENTITY, 'al');
        if (isset($searchParams['id'])) {
            $params[':idA'] = $searchParams['id'];
            $qbs->andWhere('al.id = :idA');
        }
        if (isset($searchParams['name'])) {
            $params[':nm'] = $searchParams['name'];
            $qbs->andWhere('al.name = :nm');
        }
        if (isset($searchParams['emailId'])) {
            $params[':emailIdP'] = $searchParams['emailId'];
            $qbs->andWhere('al.emailId = :emailIdP');
        }
        if (isset($searchParams['emailIdTwo'])) {
            $params[':emailIdTwoE'] = $searchParams['emailIdTwo'];
            $qbs->andWhere('al.emailIdTwo = :emailIdTwoE');
        }
        if (isset($searchParams['phoneNumber'])) {
            $params[':phn'] = $searchParams['phoneNumber'];
            $qbs->andWhere('al.phoneNumber = :phn');
        }
        if (isset($searchParams['phoneNumberTwo'])) {
            $params[':ph2'] = $searchParams['phoneNumberTwo'];
            $qbs->andWhere('al.phoneNumberTwo = :ph2');
        }
        if (isset($searchParams['phoneNumberThree'])) {
            $params[':ph3'] = $searchParams['phoneNumberThree'];
            $qbs->andWhere('al.phoneNumberThree = :ph3');
        }
        if (isset($searchParams['country'])) {
            $params[':ctry'] = $searchParams['country'];
            $qbs->andWhere('al.country = :ctry');
        }
        
        if (isset($searchParams['pincode'])) {
            $params[':pCode'] = $searchParams['pincode'];
            $qbs->andWhere('al.pincode = :pCode');
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
        }
        if (empty($results)) {
            return false;
        }
        return $results;
    }

    public function fetchStructures($searchParams, $getQ = false, $returnObject = false, $returnedInfo = null)
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
        
        $qbs->from(static::FEE_STRUCT_ENTITY, 'al');
        if (isset($searchParams['id'])) {
            $params[':idA'] = $searchParams['id'];
            $qbs->andWhere('al.id = :idA');
        }
        if (isset($searchParams['name'])) {
            $params[':nm'] = $searchParams['name'];
            $qbs->andWhere('al.name = :nm');
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
            $institute = $this->fetchAll(array(
                'id' => $instituteId
            ), null, true)[0];
            if (! empty($institute)) {
                $params[':inst'] = $institute;
                $qbs->andWhere('al.institute = :inst');
            }
        }
        if (isset($searchParams['amount'])) {
            $params[':amount'] = $searchParams['amount'];
            $qbs->andWhere('al.amount = :amount');
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
                    // $result[0]['instituteInfo'] = $this->fetchAll(array(
                    // 'instituteId' => $result['instituteId']
                    // ))[0];
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
}
