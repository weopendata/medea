@extends('main')

@section('content')
{!! Form::open(array('url' => 'finds', 'files' => true, ':is' => '"ajax-form"', ':submittable' => 'step1valid&&step2valid&&step==3')) !!}

<div class="ui fluid ordered steps">
  <div class="step" v-bind:class="{active:step==1, completed:step1valid}" @click="toStep(1)">
    <div class="content">
      <div class="title">Vondst</div>
      <div class="description">Omstandigheden vondst</div>
    </div>
  </div>
  <div class="step" v-bind:class="{active:step==2, completed:step2valid}" @click="toStep(2)">
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

<step number="1" v-show="step==1">
  <div class="two fields">
    <div class="field">
      <label>Vinder</label>
      <input type="text" v-model="find.finderName" value="John Doe" placeholder="Naam van de vinder">
    </div>
    <div class="field">
      <label>Datum</label>
      <input type="date" v-model="find.findDate" placeholder="YYYY-MM-DD">
    </div>
  </div>
  <div class="field">
    <label>Vondstlocatie</label>
    <input type="text" v-model="find.findSpot.location.description" placeholder="Beschrijving van de vindplaats">
  </div>
  <div class="field">
    <div class="ui action input">
      <input type="text" v-model="find.findSpot.location.address.street" placeholder="Adres of plaatsnaam">
      <button class="ui button">Tonen op kaart</button>
    </div>
  </div>
  <div class="field" v-if="step==1">  
    <map :center.sync="centerStart" :zoom.sync="8" @g-click="setMarker" style="display:block;height:300px;">
      <marker v-if="marker.visible" :position.sync="find.findSpot.location.latlng" :clickable.sync="true" :draggable.sync="true"></marker>
    </map>
  </div>
  <button class="ui button" v-bind:class="{green:step1valid}" @click="toStep(2)">Ga naar stap 2</button>
</step>

<step number="2" v-show="step==2">
  <div class="field cleared">
    <div :is="'photo-upload'" :images.sync="find.object.images">
      <label>Foto's</label>
      <input type="file" class="">
    </div>
  </div>
  <div class="three fields">
    <div class="field">
      <label>Materiaal</label>
      <select class="ui dropdown" v-model="find.object.material" placeholder="Kiezen..." required>
        <option>Kiezen...</option>
        @foreach ($fields['object']['material'] as $material)
        <option value="{{$material}}">{{$material}}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Techniek</label>
      <select class="ui dropdown" v-model="find.object.technique" required>
        <option>Kiezen...</option>
        @foreach ($fields['object']['technique'] as $technique)
        <option value="{{$technique}}">{{$technique}}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Oppervlaktebehandeling</label>
      <select class="ui dropdown" v-model="find.object.surfaceTreatment" required>
        <option>Kiezen...</option>
        @foreach ($fields['object']['material'] as $material)
        <option value="{{$material}}">{{$material}}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="field">
    <label>Dimensies</label>
    <div class="ui grid two column">
      <div class="column"><textarea rows="2" placeholder="lengte: 111cm  
diameter: 11cm" v-model="dimensionText"></textarea></div>
      <div class="column">
        <div class="three fields">
          <div class="field">Afmeting</div>
          <div class="field">Hoeveelheid</div>
          <div class="field">Eenheid</div>
        </div>
        <div class="three fields" v-for="dim in find.object.dimensions">
          <div class="field" v-text="dim.type"></div>
          <div class="field" v-text="dim.value"></div>
          <div class="field" v-text="dim.unit"></div>
        </div>
      </div>
    </div>
  </div>
  <button class="ui button" v-bind:class="{green:step2valid}" @click="toStep(3)">Ga naar stap 3</button>
</step>

<step number="3" v-show="step==3">
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
  <button class="ui button" type="submit">Bewaren als draft</button>
  <button class="ui green button" type="submit">Bewaren en publiceren</button>
</step>


<br><br><br><br><br><br><br><br>
<pre v-text="find|json"></pre>

{!! Form::close() !!}
@endsection

@section('script')
<script src="{{ asset('js/finds-create.js') }}"></script>
@endsection
