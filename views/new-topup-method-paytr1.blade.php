@extends('hanoivip::layouts.app')

@section('title', 'Pay the order via Paytr')

@section('content')

@if (!empty($guide))
	<p>{{$guide}}</p>
@endif

@if ($errors->has('error'))
	<p>{{$errors->first('error')}}</p>
@endif

<form method="post" action="{{route('newtopup.do')}}">
	{{ csrf_field() }}
	<input type="hidden" id="trans" name="trans" value="{{$trans}}"/>
	<label>Card Holder Name:</label>
	<input type="text" name="card_owner" value="TEST CARD"><br>
    <label>Card Number:</label>
    <input type="text" name="card_number" value="5406675406675403"><br>
    <label>Card Expiration:</label>
    <input type="text" name="expiry_month" value="12" >/<input type="text" name="expiry_year" value="99"><br>
    <label>Card CVV:</label>
    <input type="text" name="cvv" value="000"><br>
    <input type="checkbox" name="savecard" value="true" />
    <label>Save for using later</label><br/>
	<button type="submit" class="btn btn-primary">Pay</button>
</form>

@endsection
