@if(true)

@endif

@foreach($thing as $things)

@endforeach

@section ('sidebar')
    This is the master sidebar.
@show

<div class="container">
    @yield ('content')
</div>