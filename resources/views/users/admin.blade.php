@extends('main')

@section('title', 'Leden')

@section('content')
<div class="ui container">
<h3>Totaal aantal vondsten: {{ $stats['finds'] }}</h3>
<ul>
    <li>Gevalideerde vondsten: {{ $stats['validatedFinds'] }}
    <li>Aantal classificaties: {{ $stats['classifications'] }}
</ul>

<h3>Download vondsten</h3>
<a class="ui button" href="/api/export">Download</a>
    <table class="ui unstackable table" style="text-align:center;min-width:600px;width:100%">
        <thead>
            <tr>
                <th width="50">Actief</th>
                <th class="th-sortable {{ $sortBy != 'firstName' ? '' : ($sortOrder == 'DESC' ? 'down' : 'up') }}">
                    <a href="?sortBy=firstName&sortOrder={{ $sortOrder == 'DESC' ? 'ASC' : 'DESC' }}">Voornaam</a>
                </th>
                <th class="th-sortable {{ $sortBy != 'lastName' ? '' : ($sortOrder == 'DESC' ? 'down' : 'up') }}">
                    <a href="?sortBy=lastName&sortOrder={{ $sortOrder == 'DESC' ? 'ASC' : 'DESC' }}">Achternaam</a>
                </th>
                <th>Detectorist</th>
                <th>Vondstexpert</th>
                <th>Registrator</th>
                <th>Validator</th>
                <th>Onderzoeker</th>
                <th>Administrator</th>
            </tr>
        </thead>
        <tr is="TrUser" v-for="user in users" :user="user"></tr>
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

@section('script')
<script type="text/javascript">
window.users = {!! json_encode($users) !!};
</script>
<script src="{{ asset('js/users-admin.js') }}"></script>
@endsection
