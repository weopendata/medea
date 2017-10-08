@extends('main')

@section('title', 'Collecties')

@section('content')
  <div class="ui container">
    <div class="ui message">
      Collecties’ op MEDEA zijn sets van vondstfiches die het resultaat zijn van een specifiek
      registratieproject, of die een fysieke collectie van een instelling (bijv. museum of heemkundige
      vereniging) vertegenwoordigen. Een collectie kan vondsten van verschillende vinders bevatten, of
      vondsten waarvan de vinder onbekend is. Datasets met bijzondere waarde kunnen afwijken van de
      kwaliteitseisen voor vondstinformatie op MEDEA.
      Wil je zelf andere vinders helpen met het invoeren van vondsten, of heb je al een ruime dataset
      verzameld buiten MEDEA om? Neem dan contact met de MEDEA-beheerder via <a href="mailto:info@vondsten.be">info@vondsten.be</a>.
    </div>
    <collections-list></collections-list>
  </div>
@endsection

@section('script')
  <script type="text/javascript">
    window.initialCollections = {!! json_encode($collections) !!}
    window.filterState = {!! json_encode($filterState) !!}
    window.fields = {!! json_encode($fields) !!}
    window.link = {!! json_encode($link) !!}
  </script>
  <script src="{{ asset('js/collections-list.js') }}?{{ Config::get('app.version') }}"></script>
@endsection
