<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">Profile</h1>
        <p class="page-subtitle">Update your account details or delete your account.</p>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-7">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="col-lg-5">
            @include('profile.partials.update-password-form')
        </div>

        <div class="col-12">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
