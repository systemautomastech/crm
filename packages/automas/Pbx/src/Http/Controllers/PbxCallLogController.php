<?php

namespace Automas\Pbx\Http\Controllers;

use App\Models\User;
use Automas\Pbx\Models\PbxExtension;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PbxCallLogController extends Controller
{
    public function storeEvent(Request $request): JsonResponse
    {
        // if (! Auth::user()?->can('pbx use softphone')) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => __('Permission denied.'),
        //     ], 403);
        // }

        $data = $request->validate([
            'number' => ['required', 'string', 'max:50'],

            'direction' => [
                'nullable',
                'in:inbound,outbound,internal,unknown',
            ],

            'status' => [
                'required',
                'string',
                'max:50',
            ],

            'uniqueid' => [
                'nullable',
                'string',
                'max:100',
            ],

            'duration' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'call_started_at' => [
                'nullable',
                'date',
            ],

            'call_ended_at' => [
                'nullable',
                'date',
            ],
        ]);

        $creatorId = (int) creatorId();
        $userId = (int) Auth::id();

        $originalNumber = trim($data['number']);
        $digits = $this->normalizeNumber($originalNumber);
        $direction = $data['direction'] ?? 'outbound';
        $status = strtolower(trim($data['status']));
        $duration = (int) ($data['duration'] ?? 0);
        $uniqueId = $data['uniqueid'] ?? null;

        if ($digits === '') {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Only save after the call reaches a final state
        |--------------------------------------------------------------------------
        */
        if (! $this->isFinalStatus($status)) {
            return response()->json([
                'success' => true,
                'saved' => false,
                'message' => 'Call is still active.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Check extension
        |--------------------------------------------------------------------------
        */
        if ($this->extensionExists($digits, $creatorId)) {
            return response()->json([
                'success' => true,
                'saved' => false,
                'matched_type' => 'extension',
                'message' => 'Internal extension call was not saved.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Check system user
        |--------------------------------------------------------------------------
        */
        if ($this->userExists($digits, $creatorId)) {
            return response()->json([
                'success' => true,
                'saved' => false,
                'matched_type' => 'user',
                'message' => 'System user call was not saved.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Build possible phone formats
        |--------------------------------------------------------------------------
        */
        $phoneVariants = $this->getPhoneVariants($digits);
        $last10 = strlen($digits) >= 10
            ? substr($digits, -10)
            : $digits;

        /*
        |--------------------------------------------------------------------------
        | 3. Search lead
        |--------------------------------------------------------------------------
        */
        $lead = $this->findLead(
            creatorId: $creatorId,
            phoneVariants: $phoneVariants,
            last10: $last10,
        );

        if ($lead) {
            $callId = $this->saveLeadCall(
                leadId: (int) $lead->id,
                userId: $userId,
                number: $originalNumber,
                direction: $direction,
                status: $status,
                duration: $duration,
                uniqueId: $uniqueId,
            );

            return response()->json([
                'success' => true,
                'saved' => true,
                'matched_type' => 'lead',
                'matched_id' => $lead->id,
                'call_id' => $callId,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Search deal
        |--------------------------------------------------------------------------
        */
        $deal = $this->findDeal(
            creatorId: $creatorId,
            phoneVariants: $phoneVariants,
            last10: $last10,
        );

        if ($deal) {
            $callId = $this->saveDealCall(
                dealId: (int) $deal->id,
                userId: $userId,
                number: $originalNumber,
                direction: $direction,
                status: $status,
                duration: $duration,
                uniqueId: $uniqueId,
            );

            return response()->json([
                'success' => true,
                'saved' => true,
                'matched_type' => 'deal',
                'matched_id' => $deal->id,
                'call_id' => $callId,
            ]);
        }

        return response()->json([
            'success' => true,
            'saved' => false,
            'matched_type' => null,
            'message' => 'No matching user, extension, lead, or deal found.',
        ]);
    }

    private function extensionExists(
        string $digits,
        int $creatorId,
    ): bool {
        /*
         * Short numbers are normally extensions.
         */
        if (strlen($digits) >= 9) {
            return false;
        }

        return PbxExtension::query()
            ->where('created_by', $creatorId)
            ->where('extension', $digits)
            ->exists();
    }

    private function userExists(
        string $digits,
        int $creatorId,
    ): bool {
        /*
         * Do not search phone fields for short extension numbers.
         */
        if (strlen($digits) < 9) {
            return false;
        }

        $phoneVariants = $this->getPhoneVariants($digits);
        $last10 = substr($digits, -10);

        return User::query()
            ->where('created_by', $creatorId)
            ->where(function ($query) use (
                $phoneVariants,
                $last10,
            ): void {
                $query->whereIn('mobile_no', $phoneVariants)
                    ->orWhereRaw(
                        $this->normalizedPhoneSql('mobile_no') . ' LIKE ?',
                        ['%' . $last10],
                    );
            })
            ->exists();
    }

    private function findLead(
        int $creatorId,
        array $phoneVariants,
        string $last10,
    ): ?object {
        if (! DB::getSchemaBuilder()->hasTable('leads')) {
            return null;
        }

        return DB::table('leads')
            ->where('created_by', $creatorId)
            ->where(function ($query) use (
                $phoneVariants,
                $last10,
            ): void {
                $query->whereIn('phone', $phoneVariants)
                    ->orWhereRaw(
                        $this->normalizedPhoneSql('phone') . ' LIKE ?',
                        ['%' . $last10],
                    );
            })
            ->first();
    }

    private function findDeal(
        int $creatorId,
        array $phoneVariants,
        string $last10,
    ): ?object {
        if (! DB::getSchemaBuilder()->hasTable('deals')) {
            return null;
        }

        return DB::table('deals')
            ->where('created_by', $creatorId)
            ->where(function ($query) use (
                $phoneVariants,
                $last10,
            ): void {
                $query->whereIn('phone', $phoneVariants)
                    ->orWhereRaw(
                        $this->normalizedPhoneSql('phone') . ' LIKE ?',
                        ['%' . $last10],
                    );
            })
            ->first();
    }

    private function saveLeadCall(
        int $leadId,
        int $userId,
        string $number,
        string $direction,
        string $status,
        int $duration,
        ?string $uniqueId,
    ): int {
        /*
         * Prevent duplicate final events when uniqueid exists.
         */
        if ($uniqueId) {
            $existingId = DB::table('lead_calls')
                ->where('lead_id', $leadId)
                ->where('description', 'like', "%PBX ID: {$uniqueId}%")
                ->value('id');

            if ($existingId) {
                DB::table('lead_calls')
                    ->where('id', $existingId)
                    ->update([
                        'duration' => $duration,
                        'call_result' => $this->resolveCallResult($status),
                        'updated_at' => now(),
                    ]);

                return (int) $existingId;
            }
        }

        return DB::table('lead_calls')->insertGetId([
            'lead_id' => $leadId,
            'subject' => $this->getSubject($direction),
            'call_type' => $this->getCallType($direction),
            'duration' => $duration,
            'user_id' => $userId,
            'description' => $this->getDescription(
                number: $number,
                direction: $direction,
                uniqueId: $uniqueId,
            ),
            'call_result' => $this->resolveCallResult($status),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function saveDealCall(
        int $dealId,
        int $userId,
        string $number,
        string $direction,
        string $status,
        int $duration,
        ?string $uniqueId,
    ): int {
        if ($uniqueId) {
            $existingId = DB::table('deal_calls')
                ->where('deal_id', $dealId)
                ->where('description', 'like', "%PBX ID: {$uniqueId}%")
                ->value('id');

            if ($existingId) {
                DB::table('deal_calls')
                    ->where('id', $existingId)
                    ->update([
                        'duration' => $duration,
                        'call_result' => $this->resolveCallResult($status),
                        'updated_at' => now(),
                    ]);

                return (int) $existingId;
            }
        }

        return DB::table('deal_calls')->insertGetId([
            'deal_id' => $dealId,
            'subject' => $this->getSubject($direction),
            'call_type' => $this->getCallType($direction),
            'duration' => $duration,
            'user_id' => $userId,
            'description' => $this->getDescription(
                number: $number,
                direction: $direction,
                uniqueId: $uniqueId,
            ),
            'call_result' => $this->resolveCallResult($status),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function isFinalStatus(string $status): bool
    {
        return in_array($status, [
            'ended',
            'completed',
            'terminated',
            'hangup',
            'failed',
            'rejected',
            'missed',
            'busy',
            'no-answer',
            'no_answer',
            'cancelled',
            'canceled',
        ], true);
    }

    private function getSubject(string $direction): string
    {
        return match ($direction) {
            'inbound' => 'Inbound PBX Call',
            'internal' => 'Internal PBX Call',
            default => 'Outbound PBX Call',
        };
    }

    private function getCallType(string $direction): string
    {
        /*
         * Change these values if your CRM uses Incoming/Outgoing instead.
         */
        return match ($direction) {
            'inbound' => 'Inbound',
            'internal' => 'Internal',
            default => 'Outbound',
        };
    }

    private function getDescription(
        string $number,
        string $direction,
        ?string $uniqueId,
    ): string {
        $description = sprintf(
            '%s PBX call with %s.',
            ucfirst($direction),
            $number,
        );

        if ($uniqueId) {
            $description .= " PBX ID: {$uniqueId}";
        }

        return $description;
    }

    private function resolveCallResult(string $status): string
    {
        return match ($status) {
            'ended',
            'completed',
            'terminated',
            'hangup' => 'Completed',

            'busy' => 'Busy',
            'rejected' => 'Rejected',
            'missed' => 'Missed',

            'no-answer',
            'no_answer' => 'No Answer',

            'failed' => 'Failed',

            'cancelled',
            'canceled' => 'Cancelled',

            default => ucfirst($status),
        };
    }

    private function normalizeNumber(?string $number): string
    {
        return preg_replace('/\D+/', '', (string) $number) ?? '';
    }

    private function getPhoneVariants(string $digits): array
    {
        $variants = [$digits];

        if (str_starts_with($digits, '880')) {
            $local = '0' . substr($digits, 3);

            $variants[] = $local;
            $variants[] = '+' . $digits;
        }

        if (str_starts_with($digits, '0')) {
            $withoutZero = substr($digits, 1);

            $variants[] = '880' . $withoutZero;
            $variants[] = '+880' . $withoutZero;
        }

        if (
            strlen($digits) === 10 &&
            str_starts_with($digits, '1')
        ) {
            $variants[] = '0' . $digits;
            $variants[] = '880' . $digits;
            $variants[] = '+880' . $digits;
        }

        return array_values(array_unique($variants));
    }

    private function normalizedPhoneSql(string $column): string
    {
        return sprintf(
            "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(%s, '+', ''), '-', ''), ' ', ''), '(', ''), ')', '')",
            $column,
        );
    }
}