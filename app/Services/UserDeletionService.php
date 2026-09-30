<?php

namespace App\Services;

use App\Models\User;
use App\Models\MediaDirectory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class UserDeletionService
{
    /**
     * Delete a user along with their related records (Employee, Customer, Vendor, Media files)
     * while preserving financial transactions and accounting history.
     *
     * @param User $user
     * @return void
     */
    public function deleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            // 1. Delete Employee record and employee documents
            $this->deleteEmployee($user);

            // 2. Delete Customer record
            $this->deleteCustomer($user);

            // 3. Delete Vendor record
            $this->deleteVendor($user);

            // 4. Delete user's own media files & avatar
            $this->deleteUserMediaFiles($user);

            // 5. Delete Sanctum API tokens if applicable
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            // 6. Delete the User model instance
            $user->delete();
        });
    }

    /**
     * Delete associated Employee and related documents
     *
     * @param User $user
     * @return void
     */
    protected function deleteEmployee(User $user): void
    {
        if (class_exists(\Automas\Hrm\Models\Employee::class)) {
            $employee = \Automas\Hrm\Models\Employee::where('user_id', $user->id)->first();
            if ($employee) {
                if (class_exists(\Automas\Hrm\Events\DestroyEmployee::class)) {
                    event(new \Automas\Hrm\Events\DestroyEmployee($employee));
                }

                // Delete employee documents and their files
                if (class_exists(\Automas\Hrm\Models\EmployeeDocument::class)) {
                    $documents = \Automas\Hrm\Models\EmployeeDocument::where('user_id', $employee->id)
                        ->orWhere('user_id', $user->id)
                        ->get();

                    foreach ($documents as $document) {
                        if (!empty($document->file_path) && function_exists('delete_file')) {
                            delete_file($document->file_path);
                        }
                        $document->delete();
                    }
                }

                $employee->delete();
            }
        }
    }

    /**
     * Delete associated Customer record (transactions preserved)
     *
     * @param User $user
     * @return void
     */
    protected function deleteCustomer(User $user): void
    {
        if (class_exists(\Automas\Account\Models\Customer::class)) {
            $customers = \Automas\Account\Models\Customer::where('user_id', $user->id)->get();
            foreach ($customers as $customer) {
                if (class_exists(\Automas\Account\Events\DestroyCustomer::class)) {
                    event(new \Automas\Account\Events\DestroyCustomer($customer));
                }
                $customer->delete();
            }
        }
    }

    /**
     * Delete associated Vendor record (transactions preserved)
     *
     * @param User $user
     * @return void
     */
    protected function deleteVendor(User $user): void
    {
        if (class_exists(\Automas\Account\Models\Vendor::class)) {
            $vendors = \Automas\Account\Models\Vendor::where('user_id', $user->id)->get();
            foreach ($vendors as $vendor) {
                if (class_exists(\Automas\Account\Events\DestroyVendor::class)) {
                    event(new \Automas\Account\Events\DestroyVendor($vendor));
                }
                $vendor->delete();
            }
        }
    }

    /**
     * Delete user profile avatar, media files, and media directories created by the user
     *
     * @param User $user
     * @return void
     */
    protected function deleteUserMediaFiles(User $user): void
    {
        // 1. Delete user avatar image if set and not default avatar
        if (!empty($user->avatar) && strpos($user->avatar, 'avatar.png') === false && function_exists('delete_file')) {
            delete_file($user->avatar);
        }

        // 2. Delete media library files created by or attached to user
        if (class_exists(Media::class)) {
            $mediaItems = Media::where('creator_id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    $query->where('model_type', User::class)->where('model_id', $user->id);
                })
                ->get();

            foreach ($mediaItems as $media) {
                try {
                    if (!empty($media->file_name) && function_exists('delete_file')) {
                        delete_file($media->file_name);
                    } elseif (!empty($media->disk) && !empty($media->file_name)) {
                        Storage::disk($media->disk)->delete('media/' . $media->file_name);
                    }
                } catch (\Exception $e) {
                    Log::warning("Could not delete media file: {$media->file_name}. Error: {$e->getMessage()}");
                }
                $media->delete();
            }
        }

        // 3. Delete media directories created by the user
        if (class_exists(MediaDirectory::class)) {
            MediaDirectory::where('creator_id', $user->id)
                ->orWhere('created_by', $user->id)
                ->delete();
        }
    }
}
