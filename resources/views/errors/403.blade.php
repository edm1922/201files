@extends('errors::layout')

@section('title', __('Access Forbidden'))
@section('code', '403')
@section('media')
    <img src="{{ asset('gif image/bleh.webp') }}" alt="404 Meme">
@endsection
@section('message', __($exception->getMessage() ?: 'Sorry, you do not have permission to access this page. Please contact your administrator if you believe this is an error.'))
