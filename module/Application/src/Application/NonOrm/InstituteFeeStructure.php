<?php
namespace Application\NonOrm;

class InstituteFeeStructure
{

    /**
     *
     * @var array
     */
    protected $feeComponents;

    /**
     *
     * @return the $feeComponents
     */
    public function getFeeComponents()
    {
        return $this->feeComponents;
    }

    /**
     *
     * @param multitype: $feeComponents            
     */
    public function setFeeComponents($feeComponents)
    {
        $this->feeComponents = $feeComponents;
    }
}