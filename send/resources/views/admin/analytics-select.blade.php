@extends('layouts.admin')

@section('title', __('analytics.title_admin'))
@section('page-title', __('analytics.page_title_admin'))

@section('content')
<x-analytics-scope-select route="admin.analytics.index" :students="$allStudents" :sections="$availableSections" />
@endsection
