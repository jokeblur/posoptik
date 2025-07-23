<?php

namespace App\Actions\Fortify;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function update($user, array $input)
    {
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];

        if ($user->canAccessAllBranches()) {
            $validationRules['default_branch_id'] = ['nullable', 'exists:branches,id'];
        }

        Validator::make($input, $validationRules)->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        $updateData = [
            'name' => $input['name'],
            'email' => $input['email'],
        ];

        if ($user->canAccessAllBranches() && isset($input['default_branch_id'])) {
            $updateData['default_branch_id'] = $input['default_branch_id'];
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill($updateData)->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    protected function updateVerifiedUser($user, array $input)
    {
        $updateData = [
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
        ];

        if ($user->canAccessAllBranches() && isset($input['default_branch_id'])) {
            $updateData['default_branch_id'] = $input['default_branch_id'];
        }
        
        $user->forceFill($updateData)->save();

        $user->sendEmailVerificationNotification();
    }
}
