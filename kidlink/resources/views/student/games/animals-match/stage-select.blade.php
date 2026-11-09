@extends('layouts.app')

@section('title', __('game.animals_match_stage_title'))

@section('content')
    <x-stage-select game="animals-match" topic="animals" lang="animals_match" bg="animals"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
