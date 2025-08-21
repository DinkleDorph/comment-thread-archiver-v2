@extends('base')

@section('title', 'Home')

@section('content')
  <h1>Welcome!</h1>
  <p>This is a simple application to archive comment threads from screenshots.</p>
  <h2>Endpoints</h2>
  <ul>
    <li><a href="/">/</a> - Home page (this page)</li>
    <li><a href="/image">/image</a> - Upload images in order</li>
  </ul>
  <h2>Environment</h2>
  <p>Current environment: @if(!empty(env('APP_ENV'))){{ env('APP_ENV') }}@else Not set @endif</p>
  <p>OpenAI API Key: @if(!empty(env('OPENAI_API_KEY'))) Set @else Not set @endif</p>
@endsection
