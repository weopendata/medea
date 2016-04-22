@extends('main')

@section('title', 'Vondst toevoegen')

@section('content')
{!! Form::open(array(
'files' => true,
'novalidate' => '',
'class' => 'ui container container-723 form',
'@submit.prevent' => 'submit',
)) !!}

<step number="1" title="Algemene vondstgegevens" class="required" :class="{completed:step1valid}">
  <div class="fields">
    <div class="required field">
      <label>Datum</label>
      <input type="date" v-model="find.findDate" placeholder="YYYY-MM-DD">
    </div>
    <div class="required field" v-if="user.isdRegistrar">
      <label>Vinder</label>
      <input type="text" v-model="find.finderName" value="John Dfoe" placeholder="Naam van de vinder" :disabled="!user.registrator">
    </div>
  </div>
  <div class="field" v-if="!show.spotdescription||!show.place||!show.address">
    <label>Vondstlocatie verfijnen</label>
    <button v-if="!show.spotdescription" @click.prevent="show.spotdescription=1" class="ui button">Beschrijving</button>
    <button v-if="!show.place" @click.prevent="show.place=1" class="ui button">Plaatsnaam</button>
    <button v-if="!show.address" @click.prevent="show.address=1" class="ui button">Adres</button>
  </div>
  <div class="field" v-if="show.spotdescription">
    <label>Beschrijving van de vindplaats</label>
    <input type="text" v-model="find.findSpot.description" placeholder="Beschrijving van de vindplaats">
  </div>
  <div class="field" v-if="show.place">
    <label>Plaatsnaam</label>
    <input type="text" v-model="find.findSpot.location.locationPlaceName.appellation" placeholder="">
  </div>
  <div class="fields" v-if="show.address">
    <div class="six wide field">
      <label>Straat</label>
      <input type="text" v-model="find.findSpot.location.address.street" :placeholder="find.findSpot.location.lat?'Automatisch o.b.v.coördinaten':''" id="street">
    </div>
    <div class="two wide field">
      <label>Nummer</label>
      <input type="number" v-model="find.findSpot.location.address.number" placeholder="">
    </div>
  </div>
  <div class="fields" id="location-picker">
    <div class="two wide field" v-if="show.address">
      <label>Postcode</label>
      <input type="text" v-model="find.findSpot.location.address.postalCode">
    </div>
    <div class="six wide required field" v-bind:class="{six:show.address,eight:!show.address}">
      <label v-text="show.address||show.map?'Stad/gemeente':'Straat en/of gemeente/stad'">Stad/gemeente</label>
      <input type="text" v-model="find.findSpot.location.address.locality" :placeholder="find.findSpot.location.lat?'Automatisch o.b.v.coördinaten':''" @keydown.enter.prevent.stop="showOnMap">
    </div>
    <div class="eight wide field" v-if="show.map">
      <label>Nauwkeurigheid (meter)</label>
      <input type="number" v-model="find.findSpot.location.accuracy" min="0" :step="accuracyStep">
    </div>
  </div>
  <div class="field">
    <button v-if="!show.map" @click.prevent="showOnMap" class="ui button" :class="{blue:find.findSpot.location.address.locality}">
      Aanduiden op kaart
    </button>
  </div>
  <div class="field" v-if="show.map&&step==1">
    <map :center.sync="map.center" :zoom.sync="map.zoom" @g-click="setMarker" class="vue-map-size">
      <marker v-if="marker.visible&&markerNeeded"  :position.sync="latlng" :clickable.sync="true" :draggable.sync="true"></marker>
      <circle v-if="marker.visible&&!markerNeeded" :center.sync="latlng" :radius.sync="accuracy" :draggable.sync="true" :options="marker.options"></circle>
    </map>
  </div>
  <div class="two fields" v-if="show.co||show.map">
    <div class="field">
      <label>Breedtegraad</label>
      <input type="number" v-model="find.findSpot.location.lat" placeholder="lat">
    </div>
    <div class="field">
      <label>
        Lengtegraad
        <span style="float:right;font-weight: normal;">
          <u>Web Mercator</u> &nbsp;
          <span style="color:#999;" title="Lambert coordinaten worden niet ondersteund">Lambert</span>
        </span>
      </label>
      <input type="number" v-model="find.findSpot.location.lng" placeholder="lng">
    </div>
  </div>
  <p>
    <button class="ui button" v-if="show.map" :class="{green:step1valid}" :disabled="!step1valid" @click.prevent="toStep(2)">Bevestig locatie</button>
  </p>
</step>

<step number="2" title="Foto's" class="required" :class="{completed:step2valid}">
  <p>
    Foto's zijn zeer belangrijk voor dit platform. Hier zijn enkele tips:
  </p>
  <ul>
    <li>Zorg ervoor dat een meetschaal in zicht is. Gebruik hiervoor een lat of <a href="http://www.kjarrett.com/livinginthepast/wp-content/uploads/2012/05/Scale-5cm.jpg">zoiets</a></li>
    <li>Let erop dat de belichting van overal komt</li>
    <li>De resolutie van de foto's is best hoger dan 1600x900</li>
  </ul>
  <div class="field cleared">
    <div :is="'photo-upload'" :images.sync="find.object.images">
      <label>Foto's</label>
      <input type="file" class="">
    </div>
  </div>
  <p v-if="!hasImages" style="color:red">
    Zorg voor minstens 1 foto
  </p>
  <p>
    <button class="ui button" :class="{green:hasImages}" :disabled="!hasImages" @click.prevent="toStep(3)">Volgende stap</button>
    <button class="ui button" v-if="hasImages" @click.prevent="toStep(6)">Laatste stap</button>
  </p>
</step>

<step number="3" title="Gestructureerde beschrijving">
  <div class="two fields">
    <div class="required field">
      <label>Categorie</label>
      <select class="ui dropdown" v-model="find.object.category">
        <option selected>onbekend</option>
        @foreach ($fields['object']['category'] as $category)
        <option value="{{$category}}">{{$category}}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Materiaal</label>
      <select class="ui dropdown" v-model="find.object.objectMaterial">
        <option selected>onbekend</option>
        @foreach ($fields['object']['material'] as $material)
        <option value="{{$material}}">{{$material}}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="two fields">
    <div class="field">
      <label>Techniek</label>
      <select class="ui dropdown" v-model="find.object.productionEvent.productionTechnique.type">
        <option value="" selected>onbekend</option>
        <option>meerdere</option>
        @foreach ($fields['object']['technique'] as $technique)
        <option value="{{$technique}}">{{$technique}}</option>
        @endforeach
      </select>
    </div>
    <div class="field">
      <label>Oppervlaktebehandeling</label>
      <select class="ui dropdown" v-model="find.object.surfaceTreatment">
        <option selected>onbekend</option>
        <option>meerdere</option>
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
    <input type="text" v-model="find.object.inscription" placeholder="-- geen opschrift --">
  </div>
  <dating-picker :model="find.object"></dating-picker>
  <div class="field">
    <label>Opmerkingen bij het object</label>
    <input type="text" v-model="find.object.description">
  </div>
  <p>
    <button class="ui green button" @click.prevent="toStep(4)">Volgende stap</button>
  </p>
</step>

<step number="4" title="Dimensies">
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
  <p>
    <button class="ui green button" @click.prevent="toStep(5)">Volgende stap</button>
  </p>
</step>

<step number="5" title="Classificatie">
  <div class="field">
    <div v-if="find.object.productionEvent.productionClassification">
      <add-classification-form :cls.sync="find.object.productionEvent.productionClassification"></add-classification-form>
      <p>
        <button class="ui green button" @click.prevent="toStep(6)">Volgende stap</button>
      </p>
    </div>
    <div v-else>
      <p>
        Jouw vondstfiche zal voorgelegd worden aan vondstexperten om te classificeren.
      </p>
      <p>
        <button @click.prevent="pushCls" class="ui blue button" type="submit">Zelf classificeren</button>
      </p>
      <p>
        <button class="ui green button" @click.prevent="toStep(6)">Overslaan</button>
      </p>
    </div>
  </div>
</step>

<step number="6" title="Klaar met vondstfiche" :class="{active:submittable}">
  <div class="field">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden" v-model="toValidate">
      <label>
        <b>Vondstfiche laten valideren</b>
        <br>Na validatie wordt de vondst zichtbaar voor andere bezoekers en kunnen vondstexperten informatie toevoegen.
        <br>Uw identiteitsgegevens en de precieze vondstlocatie worden afgeschermd voor niet-geautoriseerde gebruikers.
      </label>
    </div>
  </div>
  <div class="field">
    <p>
      <button v-if="!toValidate" class="ui button" type="submit" :class="{orange:submittable}" :disabled="!submittable">Voorlopig bewaren</button>
      <button v-if="toValidate" class="ui button" type="submit" :class="{green:submittable}" :disabled="!submittable">Bewaren en laten valideren</button>
    </p>
    <p v-if="!submittable" style="color:red">
      Niet alle verplichte velden zijn ingevuld.
    </p>
  </div>
</step>

{!! Form::close() !!}
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
@endsection

@section('script')
<script type="text/javascript">
window.categoryMap = {munt:['diameter', 'diepte'], gesp:['lengte', 'breedte'], vingerhoed: ['diepte', 'omtrek'], mantelspeld: ['lengte', 'diameter']};
@if (isset($find))
window.initialFind = {!! json_encode($find) !!};
console.log('finds.edit:', window.initialFind)
@endif
</script>
<script src="{{ asset('js/finds-create.js') }}"></script>
@endsection
