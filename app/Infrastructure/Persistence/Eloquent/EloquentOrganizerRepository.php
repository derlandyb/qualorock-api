<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Constants\AdminPanelConstants;
use App\Domain\Contracts\OrganizerRepositoryInterface;
use App\Domain\Entities\Organizer as OrganizerEntity;
use App\Domain\Enums\OrganizerApprovalState;

class EloquentOrganizerRepository implements OrganizerRepositoryInterface
{
    public function findById(int $id): ?OrganizerEntity
    {
        $model = Organizer::find($id);

        return $model ? $this->toEntity($model) : null;
    }

    public function findByEmail(string $email): ?OrganizerEntity
    {
        $model = Organizer::where('email', $email)->first();

        return $model ? $this->toEntity($model) : null;
    }

    public function findByApprovalState(OrganizerApprovalState $state): array
    {
        return Organizer::where('approval_state', $state->value)
            ->get()
            ->map(fn (Organizer $model) => $this->toEntity($model))
            ->all();
    }

    public function create(OrganizerEntity $organizer): OrganizerEntity
    {
        $model = Organizer::create([
            'org_name' => $organizer->orgName,
            'contact_name' => $organizer->contactName,
            'email' => $organizer->email,
            'phone' => $organizer->phone,
            'password_hash' => $organizer->passwordHash,
            'plan_tier' => $organizer->planTier,
            'approval_state' => $organizer->approvalState,
            'rejection_reason' => $organizer->rejectionReason,
            'consent_given_at' => $organizer->consentGivenAt,
        ]);

        return $this->toEntity($model);
    }

    public function update(int $id, array $attributes): OrganizerEntity
    {
        $model = Organizer::findOrFail($id);
        $model->update($attributes);

        return $this->toEntity($model);
    }

    public function softDelete(int $id): void
    {
        Organizer::findOrFail($id)->delete();
    }

    public function findPendingPersonalDataPurge(int $retentionDays): array
    {
        return Organizer::onlyTrashed()
            ->whereNull('personal_data_purged_at')
            ->where('deleted_at', '<=', now()->subDays($retentionDays))
            ->get()
            ->map(fn (Organizer $model) => $this->toEntity($model))
            ->all();
    }

    public function purgePersonalData(int $id): OrganizerEntity
    {
        $model = Organizer::withTrashed()->findOrFail($id);
        $model->update([
            'org_name' => AdminPanelConstants::PURGED_PERSONAL_DATA_PLACEHOLDER,
            'contact_name' => AdminPanelConstants::PURGED_PERSONAL_DATA_PLACEHOLDER,
            'email' => "deleted-organizer-{$id}@example.invalid",
            'phone' => '',
            'password_hash' => '',
            'personal_data_purged_at' => now(),
        ]);

        return $this->toEntity($model);
    }

    private function toEntity(Organizer $model): OrganizerEntity
    {
        return new OrganizerEntity(
            id: $model->id,
            orgName: $model->org_name,
            contactName: $model->contact_name,
            email: $model->email,
            phone: $model->phone,
            passwordHash: $model->password_hash,
            planTier: $model->plan_tier,
            approvalState: $model->approval_state,
            rejectionReason: $model->rejection_reason,
            consentGivenAt: $model->consent_given_at->toImmutable(),
            deletedAt: $model->deleted_at?->toImmutable(),
            personalDataPurgedAt: $model->personal_data_purged_at?->toImmutable(),
        );
    }
}
