@extends('layouts.app')

@section('title', __('game.shapes_stage_title'))

@section('content')
    <x-stage-select game="shapes" topic="shapes" lang="shapes" bg="shapes"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
