<?php

namespace App\Models;

use CodeIgniter\Model;

class WalletMemberModel extends Model
{
    protected $table            = 'wallet_members';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'wallet_id', 'owner_user_id', 'member_user_id', 'member_email',
        'member_name', 'role', 'status'
    ];
    protected $useTimestamps    = true;

    /**
     * Get all collaborators of a wallet
     */
    public function getMembers(int $walletId): array
    {
        return $this->where('wallet_id', $walletId)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Invite / Add a collaborator to a wallet
     */
    public function addMember(int $walletId, int $ownerUserId, string $email, string $role = 'editor', ?string $name = null): array
    {
        $userModel = new UserModel();
        $existingUser = $userModel->where('email', $email)->first();

        $memberUserId = $existingUser ? (int)$existingUser['id'] : null;
        $memberName = $name ?: ($existingUser ? $existingUser['name'] : explode('@', $email)[0]);

        // Check if already invited
        $existing = $this->where('wallet_id', $walletId)->where('member_email', $email)->first();
        if ($existing) {
            $this->update($existing['id'], [
                'role'           => in_array($role, ['editor', 'viewer']) ? $role : 'editor',
                'member_user_id' => $memberUserId,
                'member_name'    => $memberName,
                'status'         => 'active',
            ]);
            return ['success' => true, 'id' => $existing['id'], 'action' => 'updated'];
        }

        $id = $this->insert([
            'wallet_id'      => $walletId,
            'owner_user_id'  => $ownerUserId,
            'member_user_id' => $memberUserId,
            'member_email'   => $email,
            'member_name'    => $memberName,
            'role'           => in_array($role, ['editor', 'viewer']) ? $role : 'editor',
            'status'         => 'active',
        ]);

        return ['success' => true, 'id' => $id, 'action' => 'created'];
    }

    /**
     * Remove a collaborator from a wallet
     */
    public function removeMember(int $id, int $ownerUserId): bool
    {
        return (bool) $this->where('id', $id)->where('owner_user_id', $ownerUserId)->delete();
    }

    /**
     * Get all shared wallet IDs accessible by a user (as invited member)
     */
    public function getSharedWalletIds(int $userId, string $userEmail): array
    {
        $rows = $this->groupStart()
                        ->where('member_user_id', $userId)
                        ->orWhere('member_email', $userEmail)
                     ->groupEnd()
                     ->where('status', 'active')
                     ->findAll();

        return array_map(fn($r) => (int)$r['wallet_id'], $rows);
    }
}
