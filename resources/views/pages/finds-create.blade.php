@extends('main')

@section('title', 'Vondst toevoegen')

@section('content')
<p v-cloak class="ui container container-723 loading">Deze pagina wordt geladen...</p>
{!! Form::open(array(
'files' => true,
'novalidate' => '',
'class' => 'ui container container-723 form',
'@submit.prevent' => 'submit',
'v-cloak' => 'true',
)) !!}

<div class="ui visible warning message" v-if="validation.remarks">
  <div class="header">
    Opmerking van de validator:
  </div>
  <div style="white-space:pre-wrap">@{{validation.remarks}}</div>
</div>

<div class="ui visible warning message" v-if="hasFeedback">
  <div class="header">
    Te reviseren gegevens:
  </div>
  <ul>
    <li class="li-feedback" v-if="f.objectCategory" @click="toStep(1)">Categorie</li>
    <li class="li-feedback" v-if="f.period" @click="toStep(1)">Datering per periode</li>
    <li class="li-feedback" v-if="f.findDate" @click="toStep(1)">Vondstdatum</li>
    <li class="li-feedback" v-if="f.location" @click="toStep(1)">Locatie</li>
    <li class="li-feedback" v-if="f.objectMaterial" @click="toStep(3)">Materiaal</li>
    <li class="li-feedback" v-if="f.productionTechniqueType" @click="toStep(3, 'technique')">Techniek</li>
    <li class="li-feedback" v-if="f.modificationTechniqueType" @click="toStep(3, 'technique')">Oppervlaktebehandeling</li>
    <li class="li-feedback" v-if="f.objectInscriptionNote" @click="toStep(3, 'technique')">Opschrift</li>
    <li class="li-feedback" v-if="f.dimensions" @click="toStep(3, true)">Dimensies</li>
    <li class="li-feedback" v-if="f.objectDescription">Beschrijving?</li>
  </ul>
</div>

<step number="1" title="Algemene vondstgegevens" class="required" :class="{completed:step1valid}" data-step="1" data-intro="Er zijn 5 stappen. Vul de velden in waarvan je zeker bent.">
  <div class="field" style="max-width: 16em">
    <div class="required field" v-if="user.registrator&&debugging">
      <label>Vinder</label>
      <input type="text" v-model="find.finderName" value="John Doe" placeholder="Naam van de vinder">
    </div>
    <div class="required field" :class="{error:validation.feedback.objectCategory}">
      <label>Categorie</label>
      <select class="ui search dropdown category" v-model="find.object.objectCategory">
        <option>onbekend</option>
        <option v-for="opt in fields.object.category" :value="opt" v-text="opt"></option>
      </select>
    </div>
    <div class="required field" :class="{error:validation.feedback.period}">
      <label>Datering per periode</label>
      <select class="ui search dropdown category" v-model="find.object.period">
        <option>onbekend</option>
        <option v-for="opt in fields.classification.period" :value="opt" v-text="opt"></option>
      </select>
    </div>
    <div class="required fluid field" :class="{error:!validFindDate||validation.feedback.findDate}">
      <label>Vondstdatum</label>
      <input type="text" v-model="find.findDate" placeholder="YYYY-MM-DD" @blur="blurDate">
      <i class="delete icon" v-if="find.findDate!=='onbekend'" @click="find.findDate='onbekend'"></i>
    </div>
  </div>
  <div class="field" v-if="show.map&&(!show.spotdescription||!show.place||!show.address)" id="location-picker">
    <label>Vondstlocatie verfijnen</label>
    <button type="button" v-if="!show.spotdescription" @click.prevent="show.spotdescription=1" class="ui button">Beschrijving</button>
    <button type="button" v-if="!show.place" @click.prevent="show.place=1" class="ui button">Plaatsnaam</button>
    <button type="button" v-if="!show.address" @click.prevent="show.address=1" class="ui button">Adres</button>
  </div>
  <div class="field" v-if="show.spotdescription">
    <label>Beschrijving van de vindplaats</label>
    <input type="text" v-model="find.findSpot.findSpotDescription" placeholder="Beschrijving van de vindplaats">
  </div>
   <div class="two fields">
    <div class="field">
      <label>Type vindplaats</label>
      <select class="ui dropdown" v-model="find.findSpot.findSpotType">
        @foreach ($fields['find_spot']['type'] as $type)
        <option value="{{$type}}">{{$type}}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="field" v-if="show.place">
    <label>Plaatsnaam</label>
    <input type="text" v-model="find.findSpot.findSpotTitle" placeholder="">
  </div>
  <div class="fields" v-if="show.address">
    <div class="six wide field">
      <label>Straat</label>
      <input type="text" v-model="find.findSpot.location.address.locationAddressStreet" id="street">
    </div>
    <div class="two wide field">
      <label>Nummer</label>
      <input type="number" v-model="find.findSpot.location.address.locationAddressNumber">
    </div>
  </div>
  <div class="fields">
    <div class="two wide field" v-if="show.address">
      <label>Postcode</label>
      <input type="text" v-model="find.findSpot.location.address.locationAddressPostalCode">
    </div>
    <div class="six wide required field" v-bind:class="{six:show.address,eight:!show.address}">
      <label v-text="show.address||show.map?'Stad/Gemeente':'Straat en/of Gemeente/stad'">Stad/Gemeente</label>
      <input type="text" v-model="find.findSpot.location.address.locationAddressLocality" :placeholder="find.findSpot.location.lat?'Automatisch o.b.v.coördinaten':''" @keydown.enter.prevent.stop="showOnMap">
    </div>
    <div class="eight wide field">
      <label>&nbsp;</label>
      <button v-if="show.map" @click.prevent="showOnMap" class="ui button">Omzetten naar coördinaten</button>
      <button v-else @click.prevent="showOnMap" class="ui button" :class="{blue:find.findSpot.location.address.locationAddressLocality}">
        Aanduiden op kaart
      </button>
    </div>
  </div>
  <div class="field" v-if="show.map">
    Het zwarte kader geeft aan welke locatie zichtbaar is voor bezoekers en andere detectoristen.
  </div>
  <div class="field" v-if="show.map&&step==1">
    <div v-if="!HelpText.map" class="card-help">
      <h1>Kaart</h1>
      <p>
        Deze kaart geeft aan waar de vondsten op deze pagina gedaan werden.
        De lijst van vondsten hieronder bevat tot 20 vondsten per pagina.
      </p>
      <p>
        <img src="/assets/img/help-area.png" height="40px"> Ruwe vondstlocatie
      </p>
      <p>
        <img src="/assets/img/help-marker.png" height="40px"> Precieze vondstlocatie (alleen bij eigen vondst)
      </p>
      <p>
        <button class="ui green button" @click="hideHelp('map')">OK, niet meer tonen</button>
      </p>
    </div>
    <map :center.sync="map.center" :zoom.sync="map.zoom" @g-click="setMarker" class="vue-map-size">
      <rectangle v-if="marker.visible" :bounds="publicBounds" :options="publicOptions"></rectangle>
      <marker v-if="marker.visible&&markerNeeded"  :position.sync="latlng" :clickable.sync="true" :draggable.sync="true"></marker>
      <circle v-if="marker.visible&&!markerNeeded" :center.sync="latlng" :radius.sync="accuracy" :options="marker.options"></circle>
    </map>
  </div>
  <div class="fields" v-if="show.co||show.map">
    <div class="three wide field">
      <label>Breedtegraad</label>
      <input type="number" pattern="[0-9]+([\.,][0-9]+)?" v-model="find.findSpot.location.lat" :step="accuracyStep/100000" placeholder="lat">
    </div>
    <div class="three wide field">
      <label>Lengtegraad</label>
      <input type="number" pattern="[0-9]+([\.,][0-9]+)?" v-model="find.findSpot.location.lng" :step="accuracyStep/100000" placeholder="lng">
    </div>
    <div class="four wide field" v-if="show.map">
      <label>Nauwkeurigheid (meter)</label>
      <input type="number" v-model="find.findSpot.location.accuracy" min="1" :step="accuracyStep">
    </div>
    <div class="five wide field" v-if="show.map">
      <label>&nbsp;</label>
      <button v-if="show.map" @click.prevent="reverseGeocode" class="ui button">Omzetten naar adres</button>
    </div>
  </div>
  <p>
    <button class="ui button" v-if="show.map" :class="{green:step1valid}" :disabled="!step1valid" @click.prevent="toStep(2)">Bevestig locatie</button>
  </p>
</step>

<step number="2" title="Foto's" class="required" :class="{completed:step2valid}" data-step="2" data-intro="Er moeten minstens 2 foto's opgeladen worden: de voor- en achterkant van het object.">
  <p>
    Foto's zijn zeer belangrijk voor dit platform. Hier zijn enkele tips:
  </p>
  <ul>
  <li>
    Plaats je object op een egale achtergrond die goed contrasteert met kleur van object (bij voorkeur wit).
Zorg voor goede belichting, die de details van het object duidelijk zichtbaar maakt. Vermijd scherpe schaduwen. Belicht eventueel van verschillende kanten, of gebruik een wit blad om licht te reflecteren. Daglicht (geen direct zonlicht) levert vaak goede resultaten op.
  </li>
  <li>
Zorg dat er steeds een schaallat op de foto staat. Als je geen schaallat bij de hand hebt, download dan <a href="http://static1.squarespace.com/static/50edd649e4b0829d0c5030a0/t/55f48a78e4b0a0b78958f884/1442089592936/Scales.pdf">dit pdf-bestand</a>, druk het af (let op dat de afdrukgrootte ingesteld staat op 100%!), kleef eventueel op dun karton, en knip de benodigde schaallatjes uit.
  </li>
  <li>
Zorg dat je foto goed is scherpgesteld. Gebruik eventueel de ‘macro’-stand van je fototoestel.
Plaats het object centraal en voldoende groot op de foto. Snijd je foto eventueel bij na het nemen om overtollige witruimte te verwijderen.
  </li>
  <li>
Zorg dat je foto een voldoende resolutie heeft. (best hoger dan 1600x900)
Neem verschillende foto’s, minstens van voor- en achterkant, en eventuele van andere invalshoeken of details wanneer dat nodig is om het object goed te documenteren.
  </li>
  </ul>
  <div class="field cleared">
    <div :is="'photo-upload'" :photograph.sync="find.object.photograph">
      <label>Foto's</label>
      <input type="file">
    </div>
  </div>
  <p v-if="!hasImages" style="color:red">
    Zorg voor minstens 2 foto's
  </p>
  <p>
    <button class="ui button" :class="{green:hasImages}" :disabled="!hasImages" @click.prevent="toStep(3)">Volgende stap</button>
  </p>
</step>

<step number="3" title="Gestructureerde beschrijving" class="required" :class="{completed:step3valid}">
  <div class="two fields">
    <div class="field" :class="{error:validation.feedback.objectMaterial}">
      <label>Materiaal</label>
      <select class="ui dropdown" v-model="find.object.objectMaterial">
        <option>onbekend</option>
        @foreach ($fields['object']['objectMaterial'] as $material)
        <option value="{{$material}}">{{$material}}</option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="field" v-if="!show.technique">
    <button @click.prevent="show.technique=1" class="ui button">Techniek, behandeling, opschrift</button>
  </div>
  <div class="two fields" v-show="show.technique">
    <div class="field" :class="{error:validation.feedback.productionTechniqueType}">
      <label>Techniek</label>
      <select class="ui dropdown" v-model="technique">
        <option>onbekend</option>
        <option>meerdere</option>
        @foreach ($fields['object']['technique'] as $technique)
        <option value="{{$technique}}">{{$technique}}</option>
        @endforeach
      </select>
    </div>
    <div class="field" :class="{error:validation.feedback.modificationTechniqueType}">
      <label>Oppervlaktebehandeling</label>
      <select class="ui dropdown" v-model="treatment">
        <option>onbekend</option>
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
  <div class="field" :class="{error:validation.feedback.objectInscriptionNote}" v-show="show.technique">
    <label>Opschrift</label>
    <input type="text" v-model="inscription" placeholder="-- geen opschrift --">
  </div>

  <h4 class="required">Dimensies</h4>
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
      <label>Hoogte/dikte</label>
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
    <button class="ui button" @click.prevent="show.lengte=show.breedte=show.diepte=show.omtrek=show.diameter=show.gewicht=1">Alle dimensies toevoegen</button>
  </div>
  <p>
    <button class="ui green button" @click.prevent="toStep(4)">Volgende stap</button>
  </p>
</step>

<step number="4" title="Classificatie">
  <div class="field">
    <div v-if="find.object.productionEvent.productionClassification.length">
      <add-classification-form :cls.sync="cls" v-for="cls in find.object.productionEvent.productionClassification"></add-classification-form>
      <p>
        <button class="ui green button" @click.prevent="toStep(5)">Volgende stap</button>
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
        <button class="ui green button" @click.prevent="toStep(5)">Overslaan</button>
      </p>
    </div>
  </div>
</step>

<div class="grouped fields">
  <h3>5. Klaar met vondstfiche</h3>
  <div class="field" :class="{error:validation.feedback.objectDescription}" data-step="3" data-intro="Onzekerheden mag je vermelden bij de beschrijving van het object.">
    <label>Beschrijving van het object</label>
    <textarea-growing v-model="find.object.objectDescription"></textarea-growing>
    <p>
      Dit is een vrije beschrijving van het object.
    </p>
  </div>
  <div data-step="4" data-intro="Als je de vondstfiche laat valideren kan je ze niet meer aanpassen.">

  <label for="toValidate">Je kan jouw vondstfiche bewaren en meteen doorsturen voor validatie of tijdelijk bewaren als voorlopige versie.</label>
  <div class="field">
    <div class="ui radio checkbox">
      <input type="radio" tabindex="0" name="toValidate" v-model="find.object.objectValidationStatus" value="Klaar voor validatie">
      <label>Vondstfiche is klaar voor validatie</label>
    </div>
  </div>
  <div class="field" v-if="currentStatus=='Voorlopige versie'">
    <div class="ui radio checkbox">
      <input type="radio" tabindex="0" name="toValidate" v-model="find.object.objectValidationStatus" value="Voorlopige versie">
      <label>Vondstfiche is een voorlopige versie. Ik vul ze later aan.</label>
    </div>
  </div>
  <div class="field" v-else>
    <div class="ui radio checkbox">
      <input type="radio" tabindex="0" name="toValidate" v-model="find.object.objectValidationStatus" value="Voorlopige versie">
      <label>Vondstfiche bewaren maar nog niet laten valideren. Ik vul ze later aan.</label>
    </div>
  </div>
  </div>
  <p v-if="currentStatus!='Voorlopige versie'&&currentStatus!='Aan te passen'">
    Huidige status: @{{currentStatus}}
  </p>
</div>

<div v-if="submittable||step==5">
  <p v-if="submitting&&toValidate" style="color:#090">
    Bedankt, jouw vondstfiche wordt bewaard en doorgestuurd voor validatie.
    <br>Je krijgt een verwittiging van de uitkomst zodra dit is gebeurd.
  </p>
  <p v-if="submitting&&!toValidate" style="color:#090">
    Bedankt, jouw vondstfiche wordt bewaard.
  </p>
  <div v-if="!submitting" class="field">
    <button v-if="!toValidate" class="ui orange button" type="submit">Voorlopig bewaren</button>
    <button v-if="toValidate" class="ui button" type="submit" :class="{green:submittable}" :disabled="!submittable">Bewaren en laten valideren</button>
  </div>
  <div v-else>
    <button type="button" class="ui disabled grey button" disabled>Even geduld...</button>
  </div>
</div>
<div class="field" v-if="!submittable">
  <p  style="color:red">
    Niet alle verplichte velden zijn ingevuld.
  </p>
</div>

{!! Form::close() !!}
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
@endsection

@section('script')
<script type="text/javascript">
window.categoryMap = {munt:['diameter', 'diepte'], gesp:['lengte', 'breedte'], vingerhoed: ['diepte', 'omtrek'], mantelspeld: ['lengte', 'diameter']};
window.fields = {!! json_encode($fields) !!};
@if (isset($find))
window.initialFind = {!! json_encode($find) !!};
console.log('finds.edit:', window.initialFind)
@endif
</script>
<script src="{{ asset('js/finds-create.js') }}"></script>
@endsection
