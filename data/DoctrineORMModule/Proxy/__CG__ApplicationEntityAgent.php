<?php

namespace DoctrineORMModule\Proxy\__CG__\Application\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Agent extends \Application\Entity\Agent implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', 'id', 'name', 'emailId', 'mobile', 'address', 'institute', 'commissionPercentage', 'enabled', 'updatedOn', 'createdOn');
        }

        return array('__isInitialized__', 'id', 'name', 'emailId', 'mobile', 'address', 'institute', 'commissionPercentage', 'enabled', 'updatedOn', 'createdOn');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Agent $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function logDatesOnCreate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'logDatesOnCreate', array());

        return parent::logDatesOnCreate();
    }

    /**
     * {@inheritDoc}
     */
    public function logDatesOnUpdate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'logDatesOnUpdate', array());

        return parent::logDatesOnUpdate();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int)  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', array());

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', array());

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getEmailId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEmailId', array());

        return parent::getEmailId();
    }

    /**
     * {@inheritDoc}
     */
    public function getMobile()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMobile', array());

        return parent::getMobile();
    }

    /**
     * {@inheritDoc}
     */
    public function getAddress()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAddress', array());

        return parent::getAddress();
    }

    /**
     * {@inheritDoc}
     */
    public function getInstitute()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getInstitute', array());

        return parent::getInstitute();
    }

    /**
     * {@inheritDoc}
     */
    public function getEnabled()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEnabled', array());

        return parent::getEnabled();
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedOn()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedOn', array());

        return parent::getUpdatedOn();
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedOn()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCreatedOn', array());

        return parent::getCreatedOn();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', array($name));

        return parent::setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmailId($emailId)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEmailId', array($emailId));

        return parent::setEmailId($emailId);
    }

    /**
     * {@inheritDoc}
     */
    public function setMobile($mobile)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMobile', array($mobile));

        return parent::setMobile($mobile);
    }

    /**
     * {@inheritDoc}
     */
    public function setAddress($address)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAddress', array($address));

        return parent::setAddress($address);
    }

    /**
     * {@inheritDoc}
     */
    public function setInstitute($institute)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setInstitute', array($institute));

        return parent::setInstitute($institute);
    }

    /**
     * {@inheritDoc}
     */
    public function setEnabled($enabled)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEnabled', array($enabled));

        return parent::setEnabled($enabled);
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedOn($updatedOn)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdatedOn', array($updatedOn));

        return parent::setUpdatedOn($updatedOn);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedOn($createdOn)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCreatedOn', array($createdOn));

        return parent::setCreatedOn($createdOn);
    }

    /**
     * {@inheritDoc}
     */
    public function getCommissionPercentage()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCommissionPercentage', array());

        return parent::getCommissionPercentage();
    }

    /**
     * {@inheritDoc}
     */
    public function setCommissionPercentage($commissionPercentage)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCommissionPercentage', array($commissionPercentage));

        return parent::setCommissionPercentage($commissionPercentage);
    }

}
