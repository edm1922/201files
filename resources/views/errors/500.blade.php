@extends('errors::layout')

@section('title', __('System Error'))
@section('code', '500')
@section('icon')
    <i class="fas fa-server"></i>
@endsection
@section('message', __('Something went wrong on our end. We are working to fix it as quickly as possible. Please try again later.'))
