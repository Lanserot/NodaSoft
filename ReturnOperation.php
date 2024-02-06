<?php

declare(strict_types=1);

namespace NW\WebService\References\Operations\Notification;

class ReturnOperation extends ReferencesOperation
{


    private Contractor $client;
    private Employee $creator;
    private Employee $employee;
    private Seller $reseller;

    private array $data;

    protected function filterRecursive(array $data, array $filters): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = $this->filterRecursive($value, $filters);
                continue;
            }
            if (isset($filters[$key])) {
                $filter = filter_var($value, $filters[$key]);
                if($filter === false || $filter === null || empty($value)){
                    throw new \Exception('Значение не прошло фильтрацию или пустое');
                }

                $value = $filter;
            }
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public function doOperation(): array
    {
        $filters = [
            'clientId' => FILTER_SANITIZE_NUMBER_INT,
            'resellerId' => FILTER_SANITIZE_NUMBER_INT,
            'to' => FILTER_SANITIZE_EMAIL,
            'from' => FILTER_SANITIZE_EMAIL,
            'notificationType' => FILTER_SANITIZE_NUMBER_INT,
            'creatorId' => FILTER_SANITIZE_NUMBER_INT,
            'expertId' => FILTER_SANITIZE_NUMBER_INT,
            'complaintId' => FILTER_SANITIZE_NUMBER_INT,
            'complaintNumber' => FILTER_SANITIZE_STRING,
            'consumptionNumber' => FILTER_SANITIZE_STRING,
            'agreementNumber' => FILTER_SANITIZE_STRING,
            'date' => FILTER_SANITIZE_STRING,
        ];
        
        $this->data = $this->filterRecursive($this->getRequest('data'), $filters);

        $result = [
            'notificationEmployeeByEmail' => false,
            'notificationClientByEmail' => false,
            'notificationClientBySms' => [
                'isSent' => false,
                'message' => '',
            ],
        ];

        if (empty($this->data))
            return $result;

        if (empty($this->data['resellerId'])) {
            $result['notificationClientBySms']['message'] = 'Empty resellerId';
            return $result;
        }

        $resellerId = $this->data['resellerId'];
        $notificationType = $this->data['notificationType'];

        $this->validateInputData();

        $differences = '';

        if ($notificationType === Contractor::TYPE_NEW) {
            $differences = __('NewPositionAdded', null, $resellerId);
        } elseif ($notificationType === Contractor::TYPE_CHANGE && !empty($this->data['differences'])) {
            $differences = __('PositionStatusHasChanged', [
                'FROM' => StatusCode::getName($this->data['differences']['from']),
                'TO' => StatusCode::getName($this->data['differences']['to']),
            ], $resellerId);
        }

        $templateData = $this->buildTemplateData($differences);

        $emailFrom = $this->reseller->getEmail();

        foreach ($this->getEmailsByPermit($this->reseller->getId(), 'tsGoodsReturn') as $email) {
            if (empty($emailFrom))
                break;
            $result['notificationEmployeeByEmail'] = EmailSenderOperation::sendEmployeeEmail($email, $this->reseller->getId(), $templateData, $emailFrom);
        }

        if ($notificationType === Contractor::TYPE_CHANGE && !empty($this->data['differences']['to'])) {
            $result['notificationClientByEmail'] = EmailSenderOperation::sendClientEmail(
                $this->reseller->getId(),
                $this->data['differences']['to'],
                $templateData,
                $this->client->getEmail(),
                $this->client->getId(),
                $emailFrom
            );

            $result['notificationClientBySms'] = EmailSenderOperation::sendMobileEmail($this->data, $this->client->getId(), $templateData);
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    private function buildTemplateData(string $differences): array
    {
        $templateData = [
            'COMPLAINT_ID' => $this->data['complaintId'],
            'COMPLAINT_NUMBER' => $this->data['complaintNumber'],
            'CREATOR_ID' => $this->data['creatorId'],
            'EXPERT_ID' => $this->data['expertId'],
            'CLIENT_ID' => $this->data['clientId'],
            'CONSUMPTION_ID' => $this->data['consumptionId'],
            'CONSUMPTION_NUMBER' => $this->data['consumptionNumber'],
            'AGREEMENT_NUMBER' => $this->data['agreementNumber'],
            'DATE' => $this->data['date'],
            'CREATOR_NAME' => $this->creator->getFullName(),
            'EXPERT_NAME' => $this->employee->getFullName(),
            'CLIENT_NAME' => $this->client->getFullName(),
            'DIFFERENCES' => $differences,
        ];

        foreach ($templateData as $key => $val) {
            if (empty($val))
                ErrorCodes::throwError("Template Data ($key) is empty!", 500);
        }

        return $templateData;
    }

    /**
     * @throws \Exception
     */
    private function validateInputData(): void
    {
        if (empty($this->data['notificationType'])) {
            ErrorCodes::throwError400(ErrorCodes::EMPTY_NOTIFICATIONTYPE);
        }

        $this->reseller = Seller::getById($this->data['resellerId']);
        if ($this->reseller === null) {
            ErrorCodes::throwError400(ErrorCodes::SELLER_NOT_FOUND);
        }

        $this->client = Contractor::getById($this->data['clientId']);
        if ($this->client === null || $this->client->getType() !== Contractor::TYPE_CUSTOMER) {
            ErrorCodes::throwError400(ErrorCodes::CLIENT_NOT_FOUND);
        }

        $this->creator = Employee::getById($this->data['creatorId']);
        if ($this->creator === null) {
            ErrorCodes::throwError400(ErrorCodes::CREATOR_NOT_FOUND);
        }

        $this->employee = Employee::getById($this->data['expertId']);
        if ($this->employee === null) {
            ErrorCodes::throwError400(ErrorCodes::EXPERT_NOT_FOUND);
        }
    }

    public function getEmailsByPermit(int $resellerId, string $event = ''): array
    {
        // fakes the method
        return ['someemeil@example.com', 'someemeil2@example.com'];
    }
}










