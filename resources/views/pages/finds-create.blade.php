@extends('main')

@section('title', 'Vondst toevoegen')

@section('content')
@endsection

@section('component')
<create-find></create-find>
@endsection

@section('script')
<script type="text/javascript">
window.categoryMap = {
  armband: ['diameter', 'diepte'],
  munt: ['diameter', 'diepte'],
  gesp: ['lengte', 'breedte'],
  vingerhoed: ['diepte'],
  mantelspeld: ['lengte', 'diameter']
};
window.fields = {!! json_encode($fields) !!};
@if (isset($find))
window.initialFind = {!! json_encode($find) !!};
@endif
</script>
@endsection
