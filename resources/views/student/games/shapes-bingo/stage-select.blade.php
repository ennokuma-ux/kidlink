@extends('layouts.app')

@section('title', __('game.shapes_bingo_stage_title'))

@section('content')
    <x-stage-select game="shapes-bingo" topic="shapes" lang="shapes_bingo" bg="shapes"
        :levels="$levels" :current-level="$currentLevel ?? 1" :total-score="$totalScore ?? 0" />
@endsection
