@extends('base')

@section('title', 'Home')

@section('content')
  <h1>This is the home page</h1>
  <a href="/">Go to index</a>
  <h2>Environment</h2>
  <p>Current environment: @if(!empty(env('APP_ENV'))){{ env('APP_ENV') }}@else Not set @endif</p>
  <p>OpenAI API Key: @if(!empty(env('OPENAI_API_KEY'))) Set @else Not set @endif</p>
@endsection
