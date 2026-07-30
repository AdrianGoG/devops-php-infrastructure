<x-guest-layout>
    <h1 class="h5 mb-1">Verify your email address</h1>
    <p class="card-text-sm mb-4">
        Thanks for signing up. Before getting started, please click the link we just emailed to you.
        If you did not receive it, we will gladly send another one.
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="alert-soft alert-soft-ok mb-4" role="alert">
            A new verification link has been sent to your email address.
        </div>
    @endif

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-accent">Resend verification email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-link-muted border-0 bg-transparent p-0">Log out</button>
        </form>
    </div>
</x-guest-layout>
