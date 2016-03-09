@extends('main')

@section('content')
<ajax-form>
{!! Form::open(array('url' => 'finds', 'files' => true)) !!}
{!! Form::token() !!}

<div class="ui fluid ordered steps">
  <div class="step" v-bind:class="{active:step==1}" @click="toStep(1)">
    <div class="content">
      <div class="title">Vondst</div>
      <div class="description">Omstandigheden vondst</div>
    </div>
  </div>
  <div class="step" v-bind:class="{active:step==2}" @click="toStep(2)">
    <div class="content">
      <div class="title">Object</div>
      <div class="description">Eigenschappen object</div>
    </div>
  </div>
  <div class="step" v-bind:class="{active:step==3}" @click="toStep(3)">
    <div class="content">
      <div class="title">Overzicht</div>
      <div class="description">Notificaties en publiceren</div>
    </div>
  </div>
</div>

<step number="1" v-if="step==1">
  <div class="field">
    <label>Vinder</label>
    <input type="text" name="last-name" placeholder="Naam van de vinder">
  </div>
  <div class="field">
    <label>Datum</label>
    <input type="text" name="last-name" placeholder="YYYY-MM-DD">
  </div>
  <div class="field">
    <label>Vondstlocatie</label>
    <input type="text" name="last-name" placeholder="Adres of plaatsnaam">
  </div>
  <div class="field">
    <div class="leaflet-map"></div>
  </div>
  <button class="ui green button" @click="toStep(2)">Ga naar stap 2</button>
</step>

<step number="2" v-if="step==2">
  <div class="photo-upload">
    <div class="field">
      <label>Foto's</label>
      <input type="file" class="">
    </div>
  </div>
  <div class="field">
    <label>Materiaal</label>
    <select class="ui dropdown">
      @foreach ($fields['object']['material'] as $material)
      <option value="{{$material}}">{{$material}}</option>
      @endforeach
    </select>
  </div>
  <div class="field">
    <label>Techniek</label>
    <select class="ui dropdown">
      @foreach ($fields['object']['technique'] as $technique)
      <option value="{{$technique}}">{{$technique}}</option>
      @endforeach
    </select>
  </div>
  <div class="field">
    <label>Oppervlaktebehandeling</label>
    <select class="ui dropdown">
      @foreach ($fields['object']['material'] as $material)
      <option value="{{$material}}">{{$material}}</option>
      @endforeach
    </select>
  </div>
  <div class="field">
    <label>Dimensies</label>
    <div class="ui grid two column">
      <div class="column"><textarea rows="2" placeholder="lengte: 111cm  
diameter: 11cm"></textarea></div>
      <div class="column">
        <div class="ui grid three column">
          <div class="column">Afmeting</div>
          <div class="column">Hoeveelheid</div>
          <div class="column">Eenheid</div>
        </div>
      </div>
    </div>
  </div>
  <button class="ui green button" @click="toStep(3)">Ga naar stap 3</button>
</step>

<step number="3" v-if="step==3">
  <div class="field">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden">
      <label>Delen met instantie</label>
    </div>
  </div>
  <div class="field">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden">
      <label>Publiek maken</label>
    </div>
  </div>
  <button class="ui button" @click="toStep(1)">Bewaren als draft</button>
  <button class="ui green button" @click="toStep(3)">Bewaren en publiceren</button>
</step>

{!! Form::close() !!}
</ajax-form>
@endsection

@section('script')
<script src="{{ asset('js/finds-create.js') }}"></script>
@endsection
