<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Prize;
use Illuminate\Auth\Access\HandlesAuthorization;

class PrizePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_prize');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Prize $prize): bool
    {
        return $user->can('view_prize');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_prize');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Prize $prize): bool
    {
        return $user->can('update_prize');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prize $prize): bool
    {
        return $user->can('delete_prize');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_prize');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Prize $prize): bool
    {
        return $user->can('force_delete_prize');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_prize');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Prize $prize): bool
    {
        return $user->can('restore_prize');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_prize');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Prize $prize): bool
    {
        return $user->can('replicate_prize');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_prize');
    }
}
