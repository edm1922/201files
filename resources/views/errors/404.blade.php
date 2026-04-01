@extends('errors.layout')

@section('title', __('Whoops! Page Lost.'))
@section('code', '404')
@section('media')
    <img src="{{ asset('gif image/mmm-7tv.webp') }}" alt="404 Meme">
@endsection
@section('message', __('The document or page you\'re searching for seems to have vanished into the archives. Let\'s get you back to safety.'))
