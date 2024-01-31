<?php

namespace NW\WebService\References\Operations\Notification;

class TsReturnOperation extends ReferencesOperation
{
    public const TYPE_NEW = 1;
    public const TYPE_CHANGE = 2;

    /**
     * @throws \Exception
     */
    public function doOperation(): array
    {
        $data = $this->validateAndParseData();

        // Проверка наличия необходимых сущностей
        $reseller = $this->getEntity(Seller::class, $data['resellerId']);
        $client = $this->getEntity(Contractor::class, $data['clientId']);
        $creator = $this->getEntity(Employee::class, $data['creatorId']);
        $expert = $this->getEntity(Employee::class, $data['expertId']);


        $templateData = $this->prepareTemplateData($data, $client, $creator, $expert);


        return $this->sendNotifications($data, $templateData, $reseller, $client);
    }

    /**
     * Валидирует и парсит входные данные.
     * @return array
     * @throws \Exception
     */
    private function validateAndParseData(): array
    {
        $data = (array) $this->getRequest('data');

        if (!isset($data['resellerId'], $data['notificationType'])) {
            throw new \Exception('Required data is missing', 400);
        }

        $resellerId = filter_var($data['resellerId'], FILTER_VALIDATE_INT);
        $notificationType = filter_var($data['notificationType'], FILTER_VALIDATE_INT);

        if (false === $resellerId || false === $notificationType) {
            throw new \Exception('Invalid data format', 400);
        }


        $clientId = $this->validateAndParseInt($data, 'clientId');
        $creatorId = $this->validateAndParseInt($data, 'creatorId');
        $expertId = $this->validateAndParseInt($data, 'expertId');
        $complaintId = $this->validateAndParseInt($data, 'complaintId');
        $complaintNumber = $this->validateAndParseString($data, 'complaintNumber');
        $consumptionId = $this->validateAndParseInt($data, 'consumptionId');
        $consumptionNumber = $this->validateAndParseString($data, 'consumptionNumber');
        $agreementNumber = $this->validateAndParseString($data, 'agreementNumber');
        $date = $this->validateAndParseString($data, 'date');
        $differences = $this->validateAndParseDifferences($data);
        $email = $this->validateAndParseString($data, 'email');
        $mobile = $this->validateAndParseString($data, 'mobile');

        return [
            'resellerId' => $resellerId,
            'notificationType' => $notificationType,
            'clientId' => $clientId,
            'creatorId' => $creatorId,
            'expertId' => $expertId,
            'complaintId' => $complaintId,
            'complaintNumber' => $complaintNumber,
            'consumptionId' => $consumptionId,
            'consumptionNumber' => $consumptionNumber,
            'agreementNumber' => $agreementNumber,
            'date' => $date,
            'differences' => $differences,
            'email' => $email,
            'mobile' => $mobile,
        ];
    }


    /**
     * Получает сущность по ID.
     * @param string $class
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
    private function getEntity(string $class, int $id)
    {
        $entity = $class::getById($id);

        if (!$entity) {
            throw new \Exception("Entity of class {$class} with ID {$id} not found", 404);
        }

        return $entity;
    }


    /**
     * Подготавливает данные для шаблона.
     * @param array $data
     * @param Contractor $client
     * @param Employee $creator
     * @param Employee $expert
     * @return array
     */
    private function prepareTemplateData(array $data, Contractor $client, Employee $creator, Employee $expert): array
    {

        $complaintNumber = $data['complaintNumber'] ?? 'N/A';
        $consumptionNumber = $data['consumptionNumber'] ?? 'N/A';
        $agreementNumber = $data['agreementNumber'] ?? 'N/A';
        $date = $data['date'] ?? date('Y-m-d');

        $differencesFormatted = 'No changes';
        if (isset($data['differences'])) {
            $fromStatusName = Status::getName($data['differences']['from']);
            $toStatusName = Status::getName($data['differences']['to']);
            $differencesFormatted = "Changed from {$fromStatusName} to {$toStatusName}";
        }

        $clientEmail = $client->email ?? 'No email provided';
        $creatorRole = $creator->getRole() ?? 'Unknown role';
        $expertiseArea = $expert->getExpertiseArea() ?? 'General';


        $additionalInformation = $data['additionalInfo'] ?? 'No additional information';

        return [
            'CLIENT_NAME' => $client->getFullName(),
            'CREATOR_NAME' => $creator->getFullName(),
            'EXPERT_NAME' => $expert->getFullName(),
            'COMPLAINT_NUMBER' => $complaintNumber,
            'CONSUMPTION_NUMBER' => $consumptionNumber,
            'AGREEMENT_NUMBER' => $agreementNumber,
            'DATE' => $date,
            'DIFFERENCES' => $differencesFormatted,
            'CLIENT_EMAIL' => $clientEmail,
            'CREATOR_ROLE' => $creatorRole,
            'EXPERTISE_AREA' => $expertiseArea,
            'ADDITIONAL_INFORMATION' => $additionalInformation,
        ];
    }




    /**
     * Отправляем уведомления.
     * @param array $data
     * @param array $templateData
     * @param Seller $reseller
     * @param Contractor $client
     * @return array
     */
    private function sendNotifications(array $data, array $templateData, Seller $reseller, Contractor $client): array
    {
        $result = [
            'notificationEmployeeByEmail' => $this->sendEmailToEmployee($data, $templateData, $reseller),
            'notificationClientByEmail' => $this->sendEmailToClient($data, $templateData, $client),
        ];

        return $result;
    }

    private function sendEmailToEmployee(array $data, array $templateData, Seller $reseller): bool
    {
        return true;
    }

    private function sendEmailToClient(array $data, array $templateData, Contractor $client): bool
    {
        return true;
    }


    private function validateAndParseDifferences($data)
    {
        if (!isset($data['differences']) || !is_array($data['differences'])) {
            throw new \Exception("Invalid or missing 'differences' data", 400);
        }

        // Предположим, что 'differences' содержит поля 'from' и 'to'
        $fromStatus = $this->validateAndParseInt($data['differences'], 'from');
        $toStatus = $this->validateAndParseInt($data['differences'], 'to');

        return [
            'from' => $fromStatus,
            'to' => $toStatus
        ];
    }

}
