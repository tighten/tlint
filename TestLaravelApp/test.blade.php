@if (!Illuminate\Support\Facades\Auth::check())
    <label for="email">Email</label>
    <input id="email" class="form-control" type="email" name="email" required>
@else
    <input id="email" type="hidden" name="email" value="{{ auth()->user()->email }}" required>
@endif