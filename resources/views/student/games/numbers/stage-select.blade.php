@extends('layouts.app')

@section('title', __('game.numbers_stage_title'))

@section('content')
    <x-stage-select game="numbers" topic="numbers" lang="numbers" bg="numbers"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
