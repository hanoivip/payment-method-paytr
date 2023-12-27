@extends('hanoivip::layouts.app')

@section('title', 'Pay the order via Paytr')

@section('content')

@if (!empty($guide))
	<h3>{{$guide}}</h3>
@endif

<div class="col-md-8 col-md-offset-2">
    <div class="panel panel-default">
    
@if ($errors->has('error'))
	<p>{{$errors->first('error')}}</p>
@endif

<form method="post" action="{{route('newtopup.do')}}" class="form-horizontal" >
	{{ csrf_field() }}
	<input type="hidden" id="trans" name="trans" value="{{$trans}}"/>
	<div class="form-group">
    	<label class="col-md-4 control-label">Card Holder Name:</label>
    	<div class="col-md-6">
    		<input type="text" name="card_owner" value="{{$data['owner']}}"><br>
    	</div>
	</div>
	<div class="form-group">
        <label class="col-md-4 control-label">Card Number:</label>
        <div class="col-md-6">
        	<input type="text" name="card_number" value="{{$data['number']}}"><br>
    	</div>
	</div>
	<div class="form-group">
        <label class="col-md-4 control-label">Card Expiration:</label>
        <div class="col-md-6">
        	<input type="text" name="expiry_month" value="{{$data['expire_month']}}" style="width: 50px;" >/<input type="text" name="expiry_year" value="{{$data['expire_year']}}" style="width: 50px;"><br>
    	</div>
	</div>
	<div class="form-group">
        <label class="col-md-4 control-label">Card CVV:</label>
        <div class="col-md-6">
        	<input type="text" name="cvv" value="{{$data['cvv']}}"><br>
    	</div>
	</div>
    <label class="col-md-4 control-label">Save for later</label>
	<input type="checkbox" name="savecard" value="true" checked /><br/>
	
	<button type="submit" class="btn btn-primary">Pay</button>
</form>

</div></div>

@endsection
