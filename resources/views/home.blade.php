@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">User Dashboard</div>
                <div class="panel-body">
                    @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                    @endif
                    @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                    @endif
                    <a class="btn btn-primary" href="{{ route('user.update') }}">Edit information</a>
                    <a class="btn btn-primary" href="{{ route('index') }}">Start Book Flights</a>
                    <a class="btn btn-primary" href="{{ route('MannageTicket', ['userid'=>Auth::user()->id]) }}">Ticket Management</a> 
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
