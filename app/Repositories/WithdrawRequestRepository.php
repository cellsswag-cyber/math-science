<?php

namespace App\Repositories;

use App\Domain\Wallet\Enums\WithdrawStatus;
use App\Models\WithdrawRequest;
use Illuminate\Database\Eloquent\Collection;

class WithdrawRequestRepository
{
    public function createWithdrawRequest(array $attributes): WithdrawRequest
    {
        return WithdrawRequest::query()->create($attributes);
    }

    public function findById(int $withdrawRequestId, bool $lock = false): WithdrawRequest
    {
        $query = WithdrawRequest::query()->with('user');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->findOrFail($withdrawRequestId);
    }

    public function getPendingRequests(): Collection
    {
        return WithdrawRequest::query()
            ->with('user')
            ->where('status', WithdrawStatus::Pending)
            ->orderBy('created_at')
            ->get();
    }

    public function updateStatus(WithdrawRequest $withdrawRequest, WithdrawStatus $status, ?int $approvedBy = null): WithdrawRequest
    {
        $withdrawRequest->forceFill([
            'status' => $status,
            'approved_by' => $approvedBy,
        ])->save();

        return $withdrawRequest->refresh();
    }
}
