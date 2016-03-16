<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="itfi_finmgmt_institute")
 */
class Institute
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     */
    protected $id;

    /**
     * @ORM\Column(type="string",nullable = true)
     */
    protected $name;

    /**
     * @ORM\Column(type="string",nullable = true)
     */
    protected $emailId;

    /**
     * @ORM\Column(type="string",nullable = true)
     */
    protected $emailIdTwo;

    /**
     * @ORM\Column(type="bigint",nullable = true,options={"unsigned"=true})
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(type="bigint",nullable = true,options={"unsigned"=true})
     */
    protected $phoneNumberTwo;

    /**
     * @ORM\Column(type="bigint",nullable = true,options={"unsigned"=true})
     */
    protected $phoneNumberThree;

    /**
     * @ORM\Column(type="string",nullable = true)
     */
    protected $country;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true},nullable = true)
     */
    protected $pincode;

    /**
     * @ORM\Column(type="boolean", nullable = true)
     */
    protected $enabled;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     */
    protected $updatedOn;

    /**
     * @ORM\Column(type="bigint", options={"unsigned"=true})
     */
    protected $createdOn;

    /**
     *
     * @return the $emailIdTwo
     */
    public function getEmailIdTwo()
    {
        return $this->emailIdTwo;
    }

    /**
     *
     * @return the $phoneNumberTwo
     */
    public function getPhoneNumberTwo()
    {
        return $this->phoneNumberTwo;
    }

    /**
     *
     * @return the $phoneNumberThree
     */
    public function getPhoneNumberThree()
    {
        return $this->phoneNumberThree;
    }

    /**
     *
     * @param field_type $emailIdTwo            
     */
    public function setEmailIdTwo($emailIdTwo)
    {
        $this->emailIdTwo = $emailIdTwo;
    }

    /**
     *
     * @param field_type $phoneNumberTwo            
     */
    public function setPhoneNumberTwo($phoneNumberTwo)
    {
        $this->phoneNumberTwo = $phoneNumberTwo;
    }

    /**
     *
     * @param field_type $phoneNumberThree            
     */
    public function setPhoneNumberThree($phoneNumberThree)
    {
        $this->phoneNumberThree = $phoneNumberThree;
    }

    /**
     * @ORM\PrePersist
     */
    public function logDatesOnCreate()
    {
        $currentTimestamp = time();
        $this->updatedOn = $currentTimestamp;
        $this->createdOn = $currentTimestamp;
    }

    /**
     * @ORM\PreUpdate
     */
    public function logDatesOnUpdate()
    {
        $currentTimestamp = time();
        $this->updatedOn = $currentTimestamp;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return the $emailId
     */
    public function getEmailId()
    {
        return $this->emailId;
    }

    /**
     *
     * @return the $phoneNumber
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     *
     * @return the $country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     *
     * @return the $pincode
     */
    public function getPincode()
    {
        return $this->pincode;
    }

    /**
     *
     * @return the $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     *
     * @return the $updatedOn
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     *
     * @return the $createdOn
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     *
     * @param field_type $name            
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     *
     * @param field_type $emailId            
     */
    public function setEmailId($emailId)
    {
        $this->emailId = $emailId;
    }

    /**
     *
     * @param field_type $phoneNumber            
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     *
     * @param field_type $country            
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     *
     * @param field_type $pincode            
     */
    public function setPincode($pincode)
    {
        $this->pincode = $pincode;
    }

    /**
     *
     * @param field_type $enabled            
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     *
     * @param number $updatedOn            
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;
    }

    /**
     *
     * @param number $createdOn            
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;
    }
}