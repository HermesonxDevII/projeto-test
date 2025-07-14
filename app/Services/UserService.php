<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\{Address, User, Role};
use Illuminate\Support\Facades\{Hash, Storage, Auth};
use Illuminate\Database\Eloquent\Collection;
use App\Http\Requests\{UserRequest};

class UserService
{
    public static function getAllUsers(array $roles = null): Collection
    {
        $users = User::query();

        if ($roles != null) {
            $users = $users->hasRoles($roles);
        }

        return $users->whereHas('roles', function ($q) {
            $q->where('name', "<>", "guardian");
        })->get();
    }

    public static function getUsers(Request $request): Collection
    {
        $users = User::with('address');

        if ($request->filled('status')) {
            $users = $users->whereStatus($request->status);
        }

        if ($request->filled('id')) {
            $users = $users->whereId($request->id);
        }

        if ($request->filled('email')) {
            $users = $users->whereEmail($request->email);
        }

        $response = $users->get();
        $response->fake_password = '****';
        return $response;
    }

    public static function getUserById(int $id): User
    {
        return User::findOrFail($id);
    }

    public static function getAdministrators(): Collection
    {
        return Role::find(1)->users;
    }

    public static function getTeachers(): Collection
    {
        return Role::find(2)->users;
    }

    public static function getGuardians(): Collection
    {
        return Role::find(3)->users;
    }

    public static function getFullAddress(User $user): string
    {
        $address = $user->address->first();
        if ($address === null) {
            return '';
        }

        $fullAddress = [];

        if ($address->zip_code !== null) {
            $fullAddress[] = $address->zip_code;
        }

        if ($address->province !== null) {
            $fullAddress[] = $address->province;
        }

        if ($address->city !== null) {
            $fullAddress[] = $address->city;
        }

        if ($address->district !== null) {
            $fullAddress[] = $address->district;
        }

        if ($address->number !== null) {
            $fullAddress[] = $address->number;
        }

        if ($address->complement !== null) {
            $fullAddress[] = $address->complement;
        }

        return implode(', ', $fullAddress);
    }


    public static function storeUser(Request $request): User
    {
        $requestData = $request->validated();
        $requestData['password'] = Hash::make($request['password']);
        $requestData['profile_photo'] = Self::uploadProfilePhoto($request);

        $user = User::create($requestData);
        $user->roles()->attach(
            $request['role_id']
        );
        return $user;
    }

    public static function uploadProfilePhoto(Request $request, User $user = null)
    {
        if (!is_null($user)) {
            if ($user->profile_photo != 'default.png') {
                deleteFile($user->getRawOriginal('profile_photo'));
            }
        }

        if (
            $request->hasFile('profile_photo')
            && $request->file('profile_photo')
        ) {
            $fileName = uniqid(date('HisYmd')) . ".{$request->file('profile_photo')->extension()}";

            Storage::putFileAs(
                'public/profile_photos',
                $request->file('profile_photo'),
                $fileName
            );

            return 'profile_photos/' . $fileName;
        } else {
            return 'default.png';
        }
    }

    public static function updateUser(UserRequest $request, User $user)
    {
        $requestData = $request->validated();

        if ($request->hasFile('profile_photo')) {
            $requestData['profile_photo'] = Self::uploadProfilePhoto($request, $user);
        }

        if ($request->filled('password')) {
            $requestData['password'] = Hash::make($requestData['password']);
        }

        $user->update($requestData);

        if ($request->filled('role_id')) {
            $user->roles()->sync(
                $request->role_id
            );
        }
    }

    public static function deleteUser(User $user)
    {
        $user->delete();
    }

    public static function forceDeleteUser(User $user)
    {
        $user->forceDelete();
    }

    public static function restoreUser(User $user)
    {
        $user->restore();
    }
}
