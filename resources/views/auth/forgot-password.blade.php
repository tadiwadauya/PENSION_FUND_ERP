<x-guest-layout>
    <p class="login-box-msg">
        Forgot your password? No problem.
        <br>
        Please contact the system administrator or HR for assistance.
    </p>

    @if (session('status'))
        <div class="alert alert-success mb-3">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="#">
        @csrf

        <div class="input-group mb-3">
            <input type="email"
                   name="email"
                   class="form-control"
                   placeholder="Enter your email"
                   value="{{ old('email') }}"
                   required
                   autofocus>

            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>

        @error('email')
            <div class="text-danger mb-2">{{ $message }}</div>
        @enderror

        <div class="row">
            <div class="col-12">
                <button type="submit"
                        class="btn btn-primary btn-block"
                        disabled>
                    Request Reset (Disabled)
                </button>
            </div>
        </div>
    </form>

    <hr>

    <p class="mb-1 text-center">
        <a href="{{ route('login') }}">Back to Login</a>
    </p>
</x-guest-layout>