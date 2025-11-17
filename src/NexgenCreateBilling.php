<?php

namespace Reliva\Nexgen;

class NexgenCreateBilling
{

    public String $fieldName;
    public String $fieldEmail;
    public String $fieldPhone;
    public String $fieldAmount;
    public String $fieldPaymentDescription;
    public ?String $fieldDueDate = null;
    public ?String $fieldRedirectUrl = null;
    public ?String $fieldCallbackUrl = null;
    public ?String $fieldExternalReferenceLabel1 = null;
    public ?String $fieldExternalReferenceValue1 = null;
    public ?String $fieldExternalReferenceLabel2 = null;
    public ?String $fieldExternalReferenceValue2 = null;
    public ?String $fieldExternalReferenceLabel3 = null;
    public ?String $fieldExternalReferenceValue3 = null;
    public ?String $fieldExternalReferenceLabel4 = null;
    public ?String $fieldExternalReferenceValue4 = null;
    public function __construct(String $fieldName, String $fieldEmail, String $fieldPhone, String $fieldAmount, String $fieldPaymentDescription, ?String $fieldDueDate = null, ?String $fieldRedirectUrl = null, ?String $fieldCallbackUrl = null, ?String $fieldExternalReferenceLabel1 = null, ?String $fieldExternalReferenceValue1 = null, ?String $fieldExternalReferenceLabel2 = null, ?String $fieldExternalReferenceValue2 = null, ?String $fieldExternalReferenceLabel3 = null, ?String $fieldExternalReferenceValue3 = null, ?String $fieldExternalReferenceLabel4 = null, ?String $fieldExternalReferenceValue4 = null)
    {
        $this->fieldName = $fieldName;
        $this->fieldEmail = $fieldEmail;
        $this->fieldPhone = $fieldPhone;
        $this->fieldAmount = $fieldAmount;
        $this->fieldPaymentDescription = $fieldPaymentDescription;
        $this->fieldDueDate = $fieldDueDate;
        $this->fieldRedirectUrl = $fieldRedirectUrl;
        $this->fieldCallbackUrl = $fieldCallbackUrl;
        $this->fieldExternalReferenceLabel1 = $fieldExternalReferenceLabel1;
        $this->fieldExternalReferenceValue1 = $fieldExternalReferenceValue1;
        $this->fieldExternalReferenceLabel2 = $fieldExternalReferenceLabel2;
        $this->fieldExternalReferenceValue2 = $fieldExternalReferenceValue2;
        $this->fieldExternalReferenceLabel3 = $fieldExternalReferenceLabel3;
        $this->fieldExternalReferenceValue3 = $fieldExternalReferenceValue3;
        $this->fieldExternalReferenceLabel4 = $fieldExternalReferenceLabel4;
        $this->fieldExternalReferenceValue4 = $fieldExternalReferenceValue4;
    }

    public function toArray(): array
    {
        return [
            'fieldName' => $this->fieldName,
            'fieldEmail' => $this->fieldEmail,
            'fieldPhone' => $this->fieldPhone,
            'fieldAmount' => $this->fieldAmount,
            'fieldPaymentDescription' => $this->fieldPaymentDescription,
            'fieldDueDate' => $this->fieldDueDate,
            'fieldRedirectUrl' => $this->fieldRedirectUrl,
            'fieldCallbackUrl' => $this->fieldCallbackUrl,
            'fieldExternalReferenceLabel1' => $this->fieldExternalReferenceLabel1,
            'fieldExternalReferenceValue1' => $this->fieldExternalReferenceValue1,
            'fieldExternalReferenceLabel2' => $this->fieldExternalReferenceLabel2,
            'fieldExternalReferenceValue2' => $this->fieldExternalReferenceValue2,
            'fieldExternalReferenceLabel3' => $this->fieldExternalReferenceLabel3,
            'fieldExternalReferenceValue3' => $this->fieldExternalReferenceValue3,
            'fieldExternalReferenceLabel4' => $this->fieldExternalReferenceLabel4,
            'fieldExternalReferenceValue4' => $this->fieldExternalReferenceValue4,
        ];
    }

    public function getFieldName(): String
    {
        return $this->fieldName;
    }

    public function getFieldEmail(): String
    {
        return $this->fieldEmail;
    }

    public function getFieldPhone(): String
    {
        return $this->fieldPhone;
    }

    public function getFieldAmount(): String
    {
        return $this->fieldAmount;
    }

    public function getFieldPaymentDescription(): String
    {
        return $this->fieldPaymentDescription;
    }

    public function getFieldDueDate(): ?String
    {
        return $this->fieldDueDate;
    }

    public function getFieldRedirectUrl(): ?String
    {
        return $this->fieldRedirectUrl;
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

    public function getFieldExternalReferenceLabel3(): ?String
    {
        return $this->fieldExternalReferenceLabel3;
    }

    public function getFieldExternalReferenceValue3(): ?String
    {
        return $this->fieldExternalReferenceValue3;
    }

    public function getFieldExternalReferenceLabel4(): ?String
    {
        return $this->fieldExternalReferenceLabel4;
    }

    public function getFieldExternalReferenceValue4(): ?String
    {
        return $this->fieldExternalReferenceValue4;
    }
}