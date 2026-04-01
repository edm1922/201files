@extends('errors.layout')

@section('title', __('System Error'))
@section('code', '500')
@section('media')
    <img src="{{ asset('gif image/bleh.webp') }}" alt="404 Meme">
@endsection
@section('message', __('Something went wrong on our end. We are working to fix it as quickly as possible. Please try again later.'))
