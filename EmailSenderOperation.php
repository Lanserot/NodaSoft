<?php
namespace NW\WebService\References\Operations\Notification;

use Exception;
use NW\WebService\References\Operations\MessagesClient;

class EmailSenderOperation
{

    public static function sendEmployeeEmail(string $email, int $resellerId, array $templateData, string $emailFrom): bool
    {
        try {
            MessagesClient::sendMessage(
                [
                    0 => [ // MessageTypes::EMAIL
                        'emailFrom' => $emailFrom,
                        'emailTo' => $email,
                        'subject' => __('complaintEmployeeEmailSubject', $templateData, $resellerId),
                        'message' => __('complaintEmployeeEmailBody', $templateData, $resellerId),
                    ],
                ],
                $resellerId,
                NotificationEvents::CHANGE_RETURN_STATUS
            );
        } catch (Exception $e) {
            return false;
        }


        return true;
    }

    public static function sendClientEmail(int $resellerId, string $to, array $templateData, string $email, int $id, ?string $emailFrom = null): bool
    {
        if (empty($emailFrom) || empty($client->email))
            return false;
        
        try {
            MessagesClient::sendMessage(
                [
                    0 => [ // MessageTypes::EMAIL
                        'emailFrom' => $emailFrom,
                        'emailTo' => $email,
                        'subject' => __('complaintClientEmailSubject', $templateData, $resellerId),
                        'message' => __('complaintClientEmailBody', $templateData, $resellerId),
                    ],
                ],
                $resellerId,
                $id,
                NotificationEvents::CHANGE_RETURN_STATUS,
                $to
            );
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
    
    public static function sendMobileEmail(array $data, int $id, array $templateData): array
    {
        $result = [
            'isSent' => false,
            'message' => ''
        ];

        if (empty($client->email))
            return $result;

        $error = null;

        try {
            $res = NotificationManager::send(
                $data['resellerId'],
                $id,
                NotificationEvents::CHANGE_RETURN_STATUS,
                (int) $data['differences']['to'],
                $templateData,
                $error
            );
        } catch (Exception $e) {
            $result = [
                'isSent' => true,
                'message' => $e->getMessage()
            ];
            return $result;
        }

        if ($res) {
            $result['isSent'] = true;
        }
        if (!empty($error)) {
            $result['message'] = $error;
        }

        return $result;
    }
}