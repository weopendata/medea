@extends('main')

@section('title', 'Vondst toevoegen')

@section('content')
{!! Form::open(array(
'files' => true,
'novalidate' => '',
'class' => 'ui container container-723 form',
'@submit.prevent' => 'submit',
)) !!}

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
      <div class="title">Classificatie</div>
      <div class="description">Typologische info</div>
    </div>
  </div>
</div>

<step number="1" v-show="step==1">
  <div class="two fields">
    <div class="field" :class="{required:user.isRegistrar}">
      <label>Vinder</label>
      <input type="text" v-model="find.finderName" value="John Dfoe" placeholder="Naam van de vinder" :disabled="!user.isRegistrar">
    </div>
    <div class="required field">
      <label>Datum</label>
      <input type="date" v-model="find.findDate" placeholder="YYYY-MM-DD">
    </div>
  </div>
  <div class="required field">
    <label>Vondstlocatie</label>
    <input type="text" v-model="find.findSpot.description" placeholder="Beschrijving van de vindplaats">
  </div>
  <div class="field">
    <label>Plaatsnaam</label>
    <button v-if="!show.place" @click.prevent="show.place=1" class="ui button">Plaatsnaam toevoegen</button>
    <input v-if="show.place" type="text" v-model="find.findSpot.location.locationPlaceName.appellation" placeholder="">
  </div>
  <div class="fields">
    <div class="eight wide field" v-if="!show.address" @click.prevent="show.address=1">
      <label for="street">Straat</label>
      <button v-if="!show.address" class="ui button">Adres toevoegen</button>
    </div>
    <div class="six wide field" v-if="show.address">
      <label>Straat</label>
      <input type="text" v-model="find.findSpot.location.address.street" placeholder="" id="street">
    </div>
    <div class="two wide field" v-if="show.address">
      <label>Nummer</label>
      <input type="number" v-model="find.findSpot.location.address.number" placeholder="">
    </div>
  </div>
  <div class="fields">
    <div class="two wide field" v-if="show.address">
      <label>Postcode</label>
      <input type="text" v-model="find.findSpot.location.address.postalCode" placeholder="">
    </div>
    <div class="six wide required field" v-bind:class="{six:show.address,eight:!show.address}">
      <label>Gemeente</label>
      <input type="text" v-model="find.findSpot.location.address.locality" placeholder="">
    </div>
    <div class="eight wide field" v-if="!show.map">
      <label>&nbsp;</label>
      <button @click.prevent="show.map=1" class="ui blue button">Aanduiden op kaart</button>
    </div>
  </div>
  <div class="field" v-if="show.map&&step==1">  
    <map :center.sync="centerStart" :zoom.sync="8" @g-click="setMarker" style="display:block;height:300px;">
      <marker v-if="marker.visible" @g-drag="setMarker" @g-dragend="setMarker" :position.sync="marker.position" :clickable.sync="true" :draggable.sync="true"></marker>
    </map>
  </div>
  <div class="two fields" v-if="show.co||show.map">
    <div class="field">
      <label>Breedtegraad</label>
      <input type="number" v-model="find.findSpot.location.lat" placeholder="lat">
    </div>
    <div class="field">
      <label>Lengtegraad</label>
      <input type="number" v-model="find.findSpot.location.lng" placeholder="lng">
    </div>
  </div>
  <button class="ui button" v-bind:class="{green:step1valid}" :disabled="!step1valid" @click.prevent="toStep(2)">Ga naar stap 2</button>
</step>

<step number="2" v-show="step==2">
  <div class="field cleared">
    <div :is="'photo-upload'" :images.sync="find.object.images">
      <label>Foto's</label>
      <input type="file" class="">
    </div>
  </div>
  <div class="required field">
    <label>Beschrijving van het object</label>
    <input type="text" v-model="find.object.description">
  </div>
  <div class="two fields">
    <div class="required field">
      <label>Categorie</label>
      <select class="ui dropdown" v-model="find.object.category">
        <option>Kiezen...</option>
        @foreach ($fields['object']['category'] as $category)
        <option value="{{$category}}">{{$category}}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Materiaal</label>
      <select class="ui dropdown" v-model="find.object.material">
        <option>Kiezen...</option>
        @foreach ($fields['object']['material'] as $material)
        <option value="{{$material}}">{{$material}}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="two fields">
    <div class="field">
      <label>Techniek</label>
      <select class="ui dropdown" v-model="find.object.technique">
        <option>Kiezen...</option>
        @foreach ($fields['object']['technique'] as $technique)
        <option value="{{$technique}}">{{$technique}}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Oppervlaktebehandeling</label>
      <select class="ui dropdown" v-model="find.object.surfaceTreatment">
        <option>Kiezen...</option>
        <option>email (cloissoné)</option>
        <option>niello</option>
        <option>filigraan</option>
        <option>gegraveerd</option>
        <option>opengewerkt</option>
        <option>verguld</option>
        <option>verzilverd</option>
        <option>gedreven</option>
        <option>gedamasceerd</option>
        <option>email (groeven)</option>
        <option>andere</option>
      </select>
    </div>
  </div>
  <div class="field">
    <label>Opschrift</label>
    <input type="text" v-model="find.findSpot.inscription" placeholder="-- geen opschrift --">
  </div>
  <h3>Dimensies</h3>
  <div class="three fields" v-if="show.lengte||show.breedte||show.diepte">
    <div class="field" v-if="show.lengte">
      <label>Lengte</label>
      <dim-input :dim="dims.lengte"></dim-input>
    </div>
    <div class="field" v-if="show.breedte">
      <label>Breedte</label>
      <dim-input :dim="dims.breedte"></dim-input>
    </div>
    <div class="field" v-if="show.diepte">
      <label>Diepte</label>
      <dim-input :dim="dims.diepte"></dim-input>
    </div>
    <div class="field" v-if="!show.lengte||!show.breedte||!show.diepte">
    </div>
  </div>
  <div class="three fields" v-if="show.omtrek||show.diameter">
    <div class="field" v-if="show.omtrek">
      <label>Omtrek</label>
      <dim-input :dim="dims.omtrek"></dim-input>
    </div>
    <div class="field" v-if="show.diameter">
      <label>Diameter</label>
      <dim-input :dim="dims.diameter"></dim-input>
    </div>
    <div class="field">
    </div>
  </div>
  <div class="three fields" v-if="show.gewicht">
    <div class="field">
      <label>Gewicht</label>
      <dim-input :dim="dims.gewicht" unit="g" altunit="kg"></dim-input>
    </div>
    <div class="field">
    </div>
  </div>
  <div class="field" v-if="!show.lengte||!show.breedte||!show.diepte||!show.omtrek||!show.diameter||!show.gewicht">
    <button class="ui button" @click.prevent="show.lengte=show.breedte=show.diepte=show.omtrek=show.diameter=show.gewicht=1">Alle dimensies tonen</button>
  </div>
  <button class="ui button" v-bind:class="{green:step2valid}" :disabled="!step2valid" @click.prevent="toStep(3)">Ga naar stap 3</button>
</step>

<step number="3" v-show="step==3">
  <div class="ui very relaxed items">
    <find-event :find="find" :user="user"></find-event>
    
    <add-classification-form :cls.sync="cls"></add-classification-form>
  </div>

  <div class="field">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden" v-model="find.toValidate">
      <label>
        <b>Vondstfiche laten valideren</b>
        <br>Na validatie wordt de vondst zichtbaar voor andere bezoekers en kunnen vondstexperten informatie toevoegen.
        <br>Uw identiteitsgegevens en de precieze vondstlocatie worden afgeschermd voor niet-geautoriseerde gebruikers.
      </label>
    </div>
  </div>
  <div class="field">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden" v-model="user.findNotifications">
      <label>Hou mij op de hoogte van wijzigingen aan vondstfiches</label>
    </div>
  </div>
  <p v-if="!submittable" style="color:red">
    Niet alle verplichte velden zijn ingevuld.
  </p>
  <p>
    <button v-if="!find.toValidate" class="ui button" type="submit" :class="{orange:submittable}" :disabled="!submittable">Voorlopig bewaren</button>
    <button v-if="find.toValidate" class="ui button" type="submit" :class="{green:submittable}" :disabled="!submittable">Bewaren en laten valideren</button>
  </p>
</step>

{!! Form::close() !!}
@endsection

@section('script')
<script type="text/javascript">window.categoryMap = {munt:['diameter', 'diepte'], gesp:['lengte', 'breedte'], vingerhoed: ['diepte', 'omtrek'], mantelspeld: ['lengte', 'diameter']};</script>
<script src="{{ asset('js/finds-create.js') }}"></script>
@endsection