@extends('errors::layout')

@section('title', __('Page Expired'))
@section('code', '419')
@section('icon')
    <i class="fas fa-history"></i>
@endsection
@section('message', __('Sorry, your session has expired. Please refresh the page and try again to continue.'))
