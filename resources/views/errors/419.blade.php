@extends('errors::layout')

@section('title', __('Page Expired'))
@section('code', '419')
@section('media')
    <img src="{{ asset('gif image/bleh.webp') }}" alt="404 Meme">
@endsection
@section('message', __('Sorry, your session has expired. Please refresh the page and try again to continue.'))