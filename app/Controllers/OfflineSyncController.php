<?php

namespace App\Controllers;

class OfflineSyncController extends BaseController
{
    public static function supportsOperation(string $operation): bool
    {
        return in_array($operation, ['clearance_request', 'blotter_submission'], true);
    }

    public function sync()
    {
        $payload = $this->request->getJSON(true);
        $operationId = trim((string) ($payload['operation_id'] ?? ''));
        $operation = trim((string) ($payload['operation'] ?? ''));
        $data = is_array($payload['data'] ?? null) ? $payload['data'] : [];

        if (! preg_match('/^[a-f0-9-]{16,64}$/i', $operationId)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid operation ID.'], 422);
        }
        if (! self::supportsOperation($operation)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Unsupported offline operation.'], 422);
        }

        $sessionUserId = (int) session()->get('user_id');
        if ($sessionUserId <= 0) {
            return $this->jsonResponse(['success' => false, 'message' => 'An active internet-authenticated session is required to sync this transaction.'], 401);
        }

        $db = \Config\Database::connect();
        if ($db->table('offline_sync_operations')->where('operation_id', $operationId)->get()->getRowArray()) {
            return $this->jsonResponse(['success' => true, 'duplicate' => true]);
        }

        if ($operation === 'clearance_request') {
            $userId = $sessionUserId;
            $documentType = trim((string) ($data['document_type'] ?? ''));
            $purpose = trim((string) ($data['purpose'] ?? ''));
            if ($userId <= 0 || $documentType === '' || $purpose === '') {
                return $this->jsonResponse(['success' => false, 'message' => 'Required clearance fields are missing.'], 422);
            }

            $forMember = trim((string) ($data['for_member'] ?? ''));
            $clearanceModel = new \App\Models\ClearanceRequestModel();
            $slot = $clearanceModel->claimOpenSlot($userId, $documentType, $forMember);
            if ($slot === null) {
                return $this->jsonResponse(['success' => false, 'message' => 'Please wait a moment and try again.'], 409);
            }
            if ($slot === false) {
                $db->table('offline_sync_operations')->insert([
                    'operation_id' => $operationId,
                    'operation' => $operation,
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);

                return $this->jsonResponse(['success' => true, 'duplicate' => true]);
            }

            $db->transStart();
            $db->table('clearance_requests')->insert([
                'user_id' => $userId,
                'for_member' => trim((string) ($data['for_member'] ?? '')) ?: null,
                'member_relationship' => trim((string) ($data['member_relationship'] ?? '')) ?: null,
                'household_no' => trim((string) ($data['household_no'] ?? '')) ?: null,
                'document_type' => $documentType,
                'purpose' => $purpose,
                'notes' => trim((string) ($data['notes'] ?? '')) ?: null,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $db->table('offline_sync_operations')->insert([
                'operation_id' => $operationId,
                'operation' => $operation,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $db->transComplete();

            if (! $db->transStatus()) {
                $clearanceModel->releaseOpenSlot($slot);

                return $this->jsonResponse(['success' => false, 'message' => 'Could not save the offline clearance request.'], 500);
            }

            $clearanceModel->releaseOpenSlot($slot);

            return $this->jsonResponse(['success' => true, 'duplicate' => false]);
        }

        if ($operation === 'blotter_submission') {
            $userId = $sessionUserId;
            $incidentType = trim((string) ($data['incident_type'] ?? ''));
            $narrative = trim((string) ($data['narrative'] ?? ''));
            if ($userId <= 0 || $incidentType === '' || $narrative === '') {
                return $this->jsonResponse(['success' => false, 'message' => 'Required blotter fields are missing.'], 422);
            }

            $db->transStart();
            $db->table('blotter_reports')->insert([
                'complainant_user_id' => $userId,
                'complainant_name' => trim((string) ($data['complainant_name'] ?? '')) ?: null,
                'complainant_email' => trim((string) ($data['complainant_email'] ?? '')) ?: null,
                'complainant_contact' => trim((string) ($data['complainant_contact'] ?? '')) ?: null,
                'incident_type' => $incidentType,
                'incident_date' => trim((string) ($data['incident_date'] ?? '')) ?: null,
                'narrative' => $narrative,
                'respondent_name' => trim((string) ($data['respondent_name'] ?? '')) ?: null,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $db->table('offline_sync_operations')->insert([
                'operation_id' => $operationId,
                'operation' => $operation,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $db->transComplete();

            if (! $db->transStatus()) {
                return $this->jsonResponse(['success' => false, 'message' => 'Could not save the offline blotter report.'], 500);
            }

            return $this->jsonResponse(['success' => true, 'duplicate' => false]);
        }

        return $this->jsonResponse(['success' => false, 'message' => 'Unsupported offline operation.'], 422);
    }
}
