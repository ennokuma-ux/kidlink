@extends('layouts.app')

@section('title', __('game.whats_missing_stage_title'))

@section('content')
    <x-stage-select game="whats-missing" topic="numbers" lang="whats_missing" bg="numbers"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
