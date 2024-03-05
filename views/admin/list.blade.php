@extends('hanoivip::admin.layouts.admin')

@section('title', 'Paytr admin')

@section('content')

<style type="text/css">
	table tr td{
		border: 1px solid;
	}
	table tr th{
		border: 1px solid;
	}
</style>

<form method="post" action="{{ route('ecmin.paytr.list') }}">
{{ csrf_field() }}
Find by order: <input type="text" name="order" id="order" value="" />
<button type="submit">Filter</button>
</form>

@if (!empty($records))

<table>
<tr>
	<th>Order</th>
	<th>Amount</th>
	<th>Status</th>
	<th>Time</th>
	<th>Action</th>
</tr>
@foreach ($records as $record)
<tr>
	<td>{{$record->trans}}
	</td>
	<td>{{$record->amount}}
	</td>
	<td>{{__("hanoivip.paytr::paytr.status.$record->status")}}
	</td>
	<td>
	{{$record->updated_at}}
	</td>
	<td>
		<form method="POST" action="{{ route('ecmin.paytr.detail') }}">
                {{ csrf_field() }}
            <input id="order" name="order" type="hidden" value="{{ $record->trans }}">
            <button type="submit" class="btn btn-primary">Detail</button>
        </form>
	</td>
</tr>
@endforeach
</table>

{{ $records->links() }}

@else

<p>No payments</p>

@endif

@endsection
