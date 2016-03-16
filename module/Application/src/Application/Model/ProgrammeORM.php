<?php
namespace Application\Model;

use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Application\Entity\Institute;
use Application\Utilities\NumberPlay;
use Application\Entity\Programme;

class ProgrammeORM
{

    CONST PROGRAMME_ENTITY = 'Application\Entity\Programme';

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

    public function register($instituteId, $data)
    {
        $institute = $this->getInstituteModel()->fetchAll(array(
            'id' => $instituteId
        ), null, true)[0];
        if (empty($institute)) {
            return false;
        }
        $problem = false;
        $requiredFields = array(
            'name' => true,
            'enabled' => true,
            'feeAmount' => true,
            'feeCurrency' => true,
            'abbreviation' => false
        );
        $errors = array();
        $errors['name'] = array();
        $errors['abbreviation'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $programme = new Programme();
        $programme->setInstitute($institute);
        if (empty($data['name'])) {
            if ($requiredFields['name']) {
                $errors['name'][] = 'name is required';
                $problem = true;
            }
        } else {
            $programmeG = $this->fetchAll(array(
                'name' => $data['name'],
                'institute' => $institute
            ))[0];
            if (! empty($programmeG)) {
                $errors['name'][] = sprintf('The name: %s is already registered', $data['name']);
                $problem = true;
            } else {
                $programme->setName($data['name']);
            }
        }
        if (empty($data['abbreviation'])) {
            if ($requiredFields['abbreviation']) {
                $errors['abbreviation'][] = 'abbreviation is required';
                $problem = true;
            }
        } else {
            $programmeXT = $this->fetchAll(array(
                'abbreviation' => $data['abbreviation'],
                'institute' => $institute
            ))[0];
            if (! empty($programmeXT)) {
                $errors['abbreviation'][] = sprintf('abbreviation: %s is already registered', $data['abbreviation']);
                $problem = true;
            } else {
                $programme->setAbbreviation($data['abbreviation']);
            }
        }
        //
        
        if ($problem) {
            return $errors;
        }
        if (! empty($data['feeAmount'])) {
            $programme->setFeeAmount($data['feeAmount']);
        }
        if (! empty($data['feeCurrency'])) {
            $programme->setFeeCurrency($data['feeCurrency']);
        }
        if (isset($data['enabled'])) {
            $programme->setEnabled($data['enabled']);
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->persist($programme);
            $om->flush($programme);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        
        return $programme->getId();
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

    public function update($id, $data)
    {
        $problem = false;
        $errors = array();
        $errors['name'] = array();
        $errors['abbreviation'] = array();
        if ($data instanceof \Traversable) {
            $data = ArrayUtils::iteratorToArray($data);
        }
        if (is_object($data)) {
            $data = (array) $data;
        }
        if (! is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Invalid data provided to %s; must be an array or Traversable', __METHOD__));
        }
        $programme = $this->fetchAll(array(
            'id' => $id
        ), null, true)[0];
        /* @var $programme Programme */
        $oldName = $programme->getName();
        $oldAbbr = $programme->getAbbreviation();
        $institute = $programme->getInstitute();
        if (! empty($data['name']) && ($oldName !== $data['name'])) {
            $newName = $data['name'];
            $programmeG = $this->fetchAll(array(
                'name' => $newName,
                'institute' => $institute
            ))[0];
            if (! empty($programmeG)) {
                $errors['name'][] = sprintf('The name: %s is already registered', $data['name']);
                $problem = true;
            } else {
                $programme->setName($newName);
            }
        }
        
        if (! empty($data['abbreviation']) && ($oldAbbr !== $data['abbreviation'])) {
            $newAbbr = $data['abbreviation'];
            $programmeXT = $this->fetchAll(array(
                'abbreviation' => $newAbbr,
                'institute' => $institute
            ))[0];
            if (! empty($programmeXT)) {
                $errors['abbreviation'][] = sprintf('abbreviation: %s is already registered', $data['abbreviation']);
                $problem = true;
            } else {
                $programme->setAbbreviation($newAbbr);
            }
        }
        //
        
        if ($problem) {
            return $errors;
        }
        if (! empty($data['feeAmount'])) {
            $programme->setFeeAmount($data['feeAmount']);
        }
        if (! empty($data['feeCurrency'])) {
            $programme->setFeeCurrency($data['feeCurrency']);
        }
        if (isset($data['enabled'])) {
            $programme->setEnabled($data['enabled']);
        }
        $om = $this->getOrmEntityMgr();
        $connection = $om->getConnection();
        $connection->beginTransaction();
        try {
            $om->flush($programme);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            $om->close();
            throw $e;
        }
        
        return $programme->getId();
    }
}
