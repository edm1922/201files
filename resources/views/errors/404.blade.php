@extends('errors::layout')

@section('title', __('Page Not Found'))
@section('code', '404')
@section('icon')
    <i class="fas fa-search"></i>
@endsection
@section('message', __('Sorry, the page you are looking for could not be found. It may have been moved or deleted.'))
