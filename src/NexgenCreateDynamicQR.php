<?php

namespace Reliva\Nexgen;

class NexgenCreateDynamicQR
{

    public String $fieldAmount;
    public String $fieldPaymentDescription;
    public ?String $fieldCallbackUrl = null;
    public ?String $fieldExternalReferenceLabel1 = null;
    public ?String $fieldExternalReferenceValue1 = null;
    public ?String $fieldExternalReferenceLabel2 = null;
    public ?String $fieldExternalReferenceValue2 = null;

    public function __construct(String $fieldAmount, String $fieldPaymentDescription, ?String $fieldCallbackUrl = null, ?String $fieldExternalReferenceLabel1 = null, ?String $fieldExternalReferenceValue1 = null, ?String $fieldExternalReferenceLabel2 = null, ?String $fieldExternalReferenceValue2 = null)
    {

        $this->fieldAmount = $fieldAmount;
        $this->fieldPaymentDescription = $fieldPaymentDescription;
        $this->fieldCallbackUrl = $fieldCallbackUrl;
        $this->fieldExternalReferenceLabel1 = $fieldExternalReferenceLabel1;
        $this->fieldExternalReferenceValue1 = $fieldExternalReferenceValue1;
        $this->fieldExternalReferenceLabel2 = $fieldExternalReferenceLabel2;
        $this->fieldExternalReferenceValue2 = $fieldExternalReferenceValue2;
    }

    public function toArray(): array
    {
        return [
            'fieldAmount' => $this->fieldAmount,
            'fieldPaymentDescription' => $this->fieldPaymentDescription,
            'fieldCallbackUrl' => $this->fieldCallbackUrl,
            'fieldExternalReferenceLabel1' => $this->fieldExternalReferenceLabel1,
            'fieldExternalReferenceValue1' => $this->fieldExternalReferenceValue1,
            'fieldExternalReferenceLabel2' => $this->fieldExternalReferenceLabel2,
            'fieldExternalReferenceValue2' => $this->fieldExternalReferenceValue2,
        ];
    }

    public function getFieldAmount(): String
    {
        return $this->fieldAmount;
    }

    public function getFieldPaymentDescription(): String
    {
        return $this->fieldPaymentDescription;
    }

    public function getFieldCallbackUrl(): ?String
    {
        return $this->fieldCallbackUrl;
    }

    public function getFieldExternalReferenceLabel1(): ?String
    {
        return $this->fieldExternalReferenceLabel1;
    }

    public function getFieldExternalReferenceValue1(): ?String
    {
        return $this->fieldExternalReferenceValue1;
    }

    public function getFieldExternalReferenceLabel2(): ?String
    {
        return $this->fieldExternalReferenceLabel2;
    }

    public function getFieldExternalReferenceValue2(): ?String
    {
        return $this->fieldExternalReferenceValue2;
    }


}