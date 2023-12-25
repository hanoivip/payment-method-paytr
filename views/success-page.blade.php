@extends('hanoivip::layouts.app')

@section('title', 'Order paid success')

@section('content')

<p>Thank for payment. You should login the game and check for diamonds/items.</p>

<p>Detail transaction can be found <a href="{{ route('shopv2.history')}}"> here </a></p>
@endsection
