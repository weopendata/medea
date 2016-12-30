@extends('main')

@section('title', 'Leden')

@section('content')
<div class="ui container">
  <h1>Leden</h1>
  <table class="ui celled table">
    <thead>
      <tr>
        <th class="th-sortable {{ $sortBy != 'firstName' ? '' : ($sortOrder == 'DESC' ? 'down' : 'up') }}">
          <a href="?sortBy=firstName&sortOrder={{ $sortOrder == 'DESC' ? 'ASC' : 'DESC' }}">Voornaam</a>
        </th>
        <th class="th-sortable {{ $sortBy != 'lastName' ? '' : ($sortOrder == 'DESC' ? 'down' : 'up') }}">
          <a href="?sortBy=lastName&sortOrder={{ $sortOrder == 'DESC' ? 'ASC' : 'DESC' }}">Achternaam</a>
        </th>
        <th>Vondsten</th>
      </tr>
    </thead>
    @foreach ($users as $user)
    <tr>
      @if ($user['hasPublicProfile'] || \Auth::user()->id == $user['id'] || \Auth::user()->hasRole('administrator'))
        <td><a href="/persons/{{ $user['id'] }}">{{ $user['firstName'] }}</a></td>
        <td><a href="/persons/{{ $user['id'] }}">{{ $user['lastName'] }}</a></td>
      @else
        <td>{{ $user['firstName'] }}</td>
        <td>{{ $user['lastName'] }}</td>
      @endif
      <td>{{ $user['finds'] }}</td>
    </tr>
    @endforeach
  </table>

  @if (@$paging)
    <div class="paging">
      {{-- First Page Link --}}
      @if (@$paging['first'])
        <a href="{{ $paging['first'] }}" rel="first" class="ui blue button"><i class="double angle left icon"></i></a>
      @else
        <button disabled class="ui blue disabled button"><i class="double angle left icon"></i></button>
      @endif

      {{-- Previous Page Link --}}
      @if (@$paging['previous'])
        <a href="{{ $paging['previous'] }}" rel="prev" class="ui blue button">Vorige</a>
      @else
        <button disabled class="ui blue disabled button">Vorige</button>
      @endif

      {{-- Next Page Link --}}
      @if (@$paging['next'])
        <a href="{{ $paging['next'] }}" rel="next" class="ui blue button">Volgende</a>
      @else
        <button disabled class="ui blue disabled button">Volgende</button>
      @endif

      {{-- Last Page Link --}}
      @if (@$paging['next'])
        <a href="{{ $paging['last'] }}" rel="last" class="ui blue button"><i class="double angle right icon"></i></a>
      @else
        <button disabled class="ui blue disabled button"><i class="double angle right icon"></i></button>
      @endif
    </div>
  @endif
</div>

@endsection
