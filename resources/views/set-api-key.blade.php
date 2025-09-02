@extends('base')

@section('title', 'Set API Key')

@section('content')
  <div class="drawer-content flex flex-col items-center justify-center">
    <form method="POST" action="/set-api-key" class="w-full max-w-sm">
      @csrf
      <div class="form-control w-full max-w-sm">
        <label class="label" for="api_key">
          <span class="label-text">Enter your OpenAI API Key</span>
        </label>
        <input
          type="text"
          id="api_key"
          name="api_key"
          class="input input-bordered w-full max-w-sm"
          placeholder="sk-..."
          required
        />
      </div>
      <button type="submit" class="btn btn-primary mt-4">Start</button>
    </form>
  </div>
@endsection
