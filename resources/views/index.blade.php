@extends('base')

@section('title', 'Home')

@section('content')
  <div class="drawer lg:drawer-open">
    <input id="my-drawer-2" type="checkbox" class="drawer-toggle" />
    <div class="drawer-content flex flex-col items-center justify-center">
      <!-- Page content here -->
      <p>Current environment: @if(!empty(env('APP_ENV'))){{ env('APP_ENV') }}@else Not set @endif</p>
      <p>Session API Key: @if(!empty($_SESSION['api_key'])) Set @else Not set @endif</p>
      <label for="my-drawer-2" class="btn btn-primary drawer-button lg:hidden">
        Open drawer
      </label>
      <a href="/unset-api-key" class="btn btn-error">
        Unset API Key
      </a>
    </div>
    <div class="drawer-side">
      <label for="my-drawer-2" aria-label="close sidebar" class="drawer-overlay"></label>
      <ul class="menu bg-base-200 text-base-content min-h-full w-80 p-4">
        <!-- Sidebar content here -->
        <li><a>Sidebar Item 1</a></li>
        <li><a>Sidebar Item 2</a></li>
      </ul>
    </div>
  </div>
@endsection
