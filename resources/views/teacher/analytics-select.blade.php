@extends('layouts.teacher')

@section('title', __('page.dashboard'))
@section('page-title', __('page.teacher_dashboard'))

@section('content')
@include('teacher.partials.headline-cards')

<x-analytics-scope-select route="teacher.dashboard" :students="$allStudents" :sections="$availableSections" />
@endsection
