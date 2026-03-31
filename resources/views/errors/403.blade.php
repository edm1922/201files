@extends('errors::layout')

@section('title', __('Access Forbidden'))
@section('code', '403')
@section('icon')
    <i class="fas fa-user-shield"></i>
@endsection
@section('message', __($exception->getMessage() ?: 'Sorry, you do not have permission to access this page. Please contact your administrator if you believe this is an error.'))
