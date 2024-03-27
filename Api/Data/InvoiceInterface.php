<?php

namespace Pointspay\Pointspay\Api\Data;

interface InvoiceInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case.
     */
    const ENTITY_ID = 'entity_id';
    const ORIGINAL_REFERENCE = 'original_reference';
    const INVOICE_ID = 'invoice_id';
    const POINTSPAY_ORDER_PAYMENT_ID = 'pointspay_order_payment_id';
    const AMOUNT = 'amount';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STATUS = 'status';

    const STATUS_PENDING_WEBHOOK = 'Pending Webhook';
    const STATUS_SUCCESSFUL = 'Successful';
    const STATUS_FAILED = 'Failed';

    /**
     * Gets the ID for the invoice.
     *
     * @return int|null Entity ID.
     */
    public function getEntityId();

    /**
     * Sets entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * @return mixed
     */
    public function getOriginalReference();

    /**
     * @param  $originalReference
     * @return mixed
     */
    public function setOriginalReference($originalReference);


    /**
     * Gets the InvoiceID for the invoice.
     *
     * @return int|null Invoice ID.
     */
    public function getInvoiceId();

    /**
     * Sets InvoiceID.
     *
     * @param int $invoiceId
     * @return $this
     */
    public function setInvoiceId($invoiceId);

    /**
     * @return int|null
     */
    public function getAmount();

    /**
     * @param $amount
     */
    public function setAmount($amount);

    /**
     * @return int|null
     */
    public function getPointspayPaymentOrderId();

    /**
     * @param $id
     * @return mixed
     */
    public function setPointspayPaymentOrderId($id);

    /**
     * @return string|null
     */
    public function getStatus();

    /**
     * @param $status
     */
    public function setStatus($status);

    /**
     * @return mixed
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     */
    public function setCreatedAt($createdAt);

    /**
     * @return mixed
     */
    public function getUpdatedAt();

    /**
     * @param $updatedAt
     */
    public function setUpdatedAt($updatedAt);
}
