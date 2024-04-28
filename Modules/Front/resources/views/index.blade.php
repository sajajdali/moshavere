@extends('front::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('front.name') !!}</p>
@endsection
