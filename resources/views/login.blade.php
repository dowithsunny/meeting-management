@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $error)
            <li> {{ $error }} </li>
        @endforeach
    </ul>
@endif

@if (Session::has('error'))
    <p style="color:red">{{ Session::get('error') }}</p>
@endif

<form action="{{ route('userLogin') }}" method="POST">
    @csrf
   
    <input type="email" name="email" placeholder="Enter email"><br><br>
    <input type="password" name="password" placeholder="Enter password"><br><br>
    <input type="submit" value="Log in"><br><br>

</form>


