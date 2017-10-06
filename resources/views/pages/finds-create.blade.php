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
  <div style="white-space:pre-wrap">@{{validation.remarks | json}}</div>
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
  <div class="field">
    <div class="required field" v-if="user.registrator&&debugging">
      <label>Vinder</label>
      <input type="text" v-model="find.finderName" value="John Doe" placeholder="Naam van de vinder" style="max-width: 16em">
    </div>

    <div class="required field" :class="{error:validation.feedback.objectCategory}">
      <label>Categorie</label>
      <select class="ui search dropdown category" v-model="find.object.objectCategory" style="max-width: 16em">
        <option>onbekend</option>
        <option v-for="opt in fields.object.category" :value="opt" v-text="opt" track-by="$index"></option>
      </select>
      <div class="ui message" v-if="!HelpText.create">
        <p>
          Kies hier tot welke categorie object je vondst behoort. Als je de categorie niet terugvindt, vul dan ‘andere’ in (onderaan de lijst). Als je het niet weet, laat dan ‘onbekend’ staan.
        </p>
      </div>
    </div>

    <div class="required field" :class="{error:validation.feedback.period}">
      <label>Datering per periode</label>
      <select class="ui search dropdown category" v-model="find.object.period" style="max-width: 16em">
        <option>onbekend</option>
        <option v-for="opt in fields.classification.period" :value="opt" v-text="opt"></option>
      </select>
      <div class="ui message" v-if="!HelpText.create">
        <p>
          Kies hier tot welke periode je vondst volgens jou behoort. Als je het niet weet, laat dan ‘onbekend’ staan.
        </p>
      </div>
    </div>

    <div class="required fluid field" :class="{error:!validFindDate||validation.feedback.findDate}">
      <label>Vondstdatum</label>
      <input type="text" v-model="find.findDate" placeholder="YYYY-MM-DD" @blur="blurDate" style="max-width: 16em ">
      <button type="button" class="ui basic button" v-if="find.findDate!=='onbekend'" @click="find.findDate='onbekend'">onbekend</button>
    </div>
    <div class="ui message" v-if="find.findDate >= '2016-04-01'">
      Vergeet deze recente vondst niet aan te melden bij Onroerend Erfgoed. Je kunt daarvoor het MEDEA-ID gebruiken dat je na afwerking bovenaan je vondstfiche zult vinden. Als je je vondst al meldde, kun je het OE-meldingsnummer opnemen in het veld 'eigen inventarisnummer'.
    </div>
    <div class="ui message" v-if="!HelpText.create || !validFindDate">
      <p>
        Vul hier de datum in waarop je de vondst gedaan hebt.
        <br>Aanvaardbare formaten: <code>YYYY-MM-DD</code> <code>YYYY-MM</code> <code>YYYY</code> <code>onbekend</code>
      </p>
    </div>
  </div>

  <div class="field" v-if="show.map&&(!show.spotdescription||!show.place||!show.address)" id="location-picker">
    <label>Vondstlocatie verfijnen</label>
    <button type="button" v-if="!show.spotdescription" @click.prevent="show.spotdescription=1" class="ui button">Beschrijving</button>
    <button type="button" v-if="!show.place" @click.prevent="show.place=1" class="ui button">Plaatsnaam</button>
    <button type="button" v-if="!show.address" @click.prevent="show.address=1" class="ui button">Adres</button>
  </div>
  <div class="field" v-if="show.spotdescription">
    <label>Opmerkingen over de vindplaats</label>
    <input type="text" v-model="find.findSpot.findSpotDescription" placeholder="Opmerkingen over de vindplaats">
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
  <div class="ui message" v-if="!HelpText.create">
    <p>
      Kies het type vindplaats. Als je het niet weet, laat dit veld dan leeg.
    </p>
  </div>
  <div class="field">
    <label>Lokale plaatsnaam</label>
    <input type="text" v-model="find.findSpot.findSpotTitle" placeholder="">
  </div>
  <div class="ui message" v-if="!HelpText.create">
    <p>
      Vul hier de lokale plaatsnaam van de vindplaats in (toponiem of veldnaam).
    </p>
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
      <button v-else @click.prevent="showOnMap" class="ui button" :class="{blue:find.findSpot.location.address.locationAddressLocality}">
        Aanduiden op kaart
      </button>
    </div>
  </div>
  <div class="ui message" v-if="!HelpText.create">
    <p v-if="!show.map">
      Vul eerst de gemeente of stad in en klik vervolgens op "Aanduiden op kaart".
    </p>
    <p v-else>
      Het zwarte kader geeft aan welke locatie zichtbaar is voor bezoekers en andere detectoristen.
    </p>
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
        <img src="/assets/img/help-marker.png" height="40px"> Precieze vondstlocatie is enkel zichtbaar voor eigen vondsten.
      </p>
      <p>
        <button class="ui green button" @click="hideHelp('map')">OK, niet meer tonen</button>
      </p>
    </div>
    <map :center.sync="map.center" :zoom.sync="map.zoom" @g-click="setMarker" class="vue-map-size">
      <rectangle v-if="marker.visible" :bounds="publicBounds" :options="publicOptions"></rectangle>
      <marker v-if="marker.visible&&markerNeeded"  :position.sync="latlng" :clickable.sync="true" :draggable.sync="true"></marker>
      <circle v-if="marker.visible&&!markerNeeded" :center.sync="latlng" :radius.sync="accuracy" :options="marker.options"></circle>
      <div class="gm-panel" style="direction: ltr; overflow: hidden; position: absolute; color: rgb(0, 0, 0); font-family: Roboto, Arial, sans-serif; -webkit-user-select: none; font-size: 11px; padding: 8px; border-bottom-left-radius: 2px; border-top-left-radius: 2px; -webkit-background-clip: padding-box; box-shadow: rgba(0, 0, 0, 0.298039) 0px 1px 4px -1px; min-width: 27px; font-weight: 500; background-color: rgb(255, 255, 255); background-clip: padding-box;top: 10px;top:auto;left: 10px;bottom: 10px;" @click="showHelp('map')">Help</div>

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
      <select class="ui dropdown" v-model="find.findSpot.location.accuracy">
        <option value="1">1 - 5m (GPS)</option>
        <option value="5">5 - 20m</option>
        <option value="20">20 - 50m</option>
        <option value="50">50 - 100m</option>
        <option value="100">100 - 500m</option>
        <option value="500">500 - 2000m</option>
        <option value="2000">Gemeenteniveau</option>
      </select>
    </div>
  </div>
  <div class="ui message" v-if="(show.co||show.map)&&!HelpText.create">
    <p>
      Nadat je de marker op de kaart verplaatst hebt, of wijzigingen hebt gemaakt aan de coördinaten kan je klikken op "Omzetten naar adres". Dan zal het adres boven de kaart overschreven worden met de locatie die aangeduid staat.
    </p>
  </div>
  <p>
    <button class="ui button" v-if="show.map" :class="{green:step1valid}" :disabled="!step1valid" @click.prevent="handleStep1()">Bevestig locatie</button>
  </p>
</step>

<step number="2" title="Foto's" :class="{required:! this.user || !this.user.registrator}" :class="{completed:step2valid}" data-step="2" data-intro="Er moeten minstens 2 foto's opgeladen worden: de voor- en achterkant van het object.">
  <p>
    Foto's zijn zeer belangrijk voor dit platform. Hier zijn enkele tips:
  </p>
  <ul>
  <li>
    Gebruik een witte achtergrond.
  </li>
  <li>
    Maak gebruik van natuurlijk daglicht indien mogelijk, maar geen direcht zonlicht.
  </li>
  <li>
    Zorg voor een schaallat.
  </li>
  <li>
    Ga niet te dichtbij je onderwerp, snij liever achteraf bij.
  </li>
  <li>
    Minimaal twee invalshoeken (boven en zij/achter). Minimumresolutie 400x400 pixels, richtgrootte 1600x900 of meer.
  </li>
  <li>
    <a target="_blank" href="https://blog.vondsten.be/tips/vondstfotografie">Meer tips</a>
  </li>
  </ul>
  <div class="field cleared">
    <div is="photo-upload" :photograph.sync="find.object.photograph">
      <label>Foto's</label>
      <input type="file">
    </div>
  </div>
  <p v-if="!hasImages && ! this.user.registrator" style="color:red">
    Zorg voor minstens 2 foto's
  </p>
  <p>
    <button class="ui button" :class="{green:hasImages}" :disabled="! step2valid" @click.prevent="toStep(3)">Volgende stap</button>
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
  <div class="ui message" v-if="!HelpText.create">
    <p>
      Kies hier het voornaamste materiaal waaruit je vondst bestaat. Let wel: brons, messing, e.d. vallen onder de categorie ‘koperlegering’. Als je het niet weet, vul dan ‘onbekend’ in.
    </p>
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
      <div class="ui message" v-if="!HelpText.create">
        <p>
          Kies hier welke techniek gebruikt werd om het object te vervaardigen. Weet je het niet, laat dit veld dan leeg.
        </p>
      </div>
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
      <div class="ui message" v-if="!HelpText.create">
        <p>
          Kies hier welke oppervlaktebehandeling gebruikt werd om het object te vervaardigen. Weet je het niet, laat dit veld dan leeg.
        </p>
      </div>
    </div>
  </div>
  <div class="field" :class="{error:validation.feedback.objectInscriptionNote}" v-show="show.technique">
    <label>Opschrift</label>
    <input type="text" v-model="inscription" placeholder="-- geen opschrift --">
    <div class="ui message" v-if="!HelpText.create">
      <p>Als de vondst een inscriptie heeft, kun je die hier neerschrijven.</p>
    </div>
  </div>

  <div class="two fields">
    <div class="field">
      <label>Inventaris nummer</label>
      <input type="text" v-model="find.object.objectNr" placeholder="Inventaris nummer">
    </div>
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
  <div class="three fields" v-if="show.diameter">
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
  <div class="field" v-if="!show.lengte||!show.breedte||!show.diepte||!show.diameter||!show.gewicht">
    <button class="ui button" @click.prevent="show.lengte=show.breedte=show.diepte=show.diameter=show.gewicht=1">Alle dimensies toevoegen</button>
  </div>
  <div class="ui message" v-if="!HelpText.create">
    <p>Vul hier de afmetingen van je vondst in, liefst met millimeterprecisie. Kies voor de maximale afmetingen, en vul minstens twee dimensies in. Bij de knop ‘alle dimensies tonen’ kun je andere maten kiezen.</p>
  </div>
  <p>
    <button class="ui green button" @click.prevent="toStep(4)">Volgende stap</button>
  </p>
</step>

<step number="4" title="Classificatie">
  <div class="field">
    <div v-if="find.object.productionEvent.productionClassification.length">
      <add-classification-form :cls.sync="cls" v-for="cls in find.object.productionEvent.productionClassification"></add-classification-form>
      <div class="ui message" v-if="!HelpText.create">
        <p>
          Kies hier welk soort informatie je wil toevoegen aan deze vondstfiche. <b>'Typologie'</b> laat je toe om deze vondst toe te wijzen aan een bepaald objecttype. Je kunt ook verwijzen naar een <b>'Gelijkaardige vondst'</b>, bijvoorbeeld afkomstig uit een opgraving. In beide gevallen kun je de datering preciseren en verwijzen naar een boek, internetpagina of andere gepubliceerde bron. Als de vondst op deze fiche zelf in detail beschreven en besproken werd in een publicatie, kies dan voor de laatste optie <b>'Publicatie van deze vondst'</b>. Zo kun je een verwijzing naar die bron toevoegen, en de voornaamste informatie uit die bron aan deze fiche koppelen.
        </p>
      </div>
      <br>
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

<step number="5" title="Linken aan een collectie / persoon" v-if="user.administrator">
  <div class="field">
    <add-collections @select="assignCollection" @remove="removeCollection" :collection.sync="collection" placeholder="Koppel aan een collectie"></add-collections>
  </div>
  <div class="field">
    <add-persons @select="assignPerson" @remove="removePerson" :person.sync="person" placeholder="Koppel aan een persoon"></add-persons>
  </div>
</step>

<step :number="user.administrator ? 6 : 5" title="Klaar met vondstfiche">
<div class="grouped fields">
  <div class="field" :class="{error:validation.feedback.objectDescription}" data-step="3" data-intro="Onzekerheden mag je vermelden bij de beschrijving van het object.">
    <label>Bijkomende opmerkingen</label>
    <textarea-growing v-model="find.object.objectDescription"></textarea-growing>
    <p>
      Voeg hier belangrijke informatie over de vondst toe die niet eerder in het formulier aan bod kwam.
    </p>
  </div>
  <div data-step="4" data-intro="Als je de vondstfiche laat valideren kan je ze niet meer aanpassen.">
  <div class="ui message" v-if="!HelpText.create">
    <p>
      Kies hier of je deze vondstfiche wil doorsturen, zodat de vondst gevalideerd en gepubliceerd kan worden door een validator. Deze krijgt je identiteit en de exacte vondstlocatie niet te zien. Na publicatie kunnen experten en het brede publiek je vondst (maar niet de exacte vondstlocatie) raadplegen.
      <br><b>Let wel</b>: na versturen kun je de meeste velden niet meer wijzigen, tenzij met tussenkomst van de databeheerder. Als je niet klaar bent met deze fiche, kies er dan voor om ze tijdelijk op te slaan als een voorlopige versie.
    </p>
  </div>

  <label for="toValidate">Je kan jouw vondstfiche bewaren en meteen doorsturen voor validatie of tijdelijk bewaren als voorlopige versie.</label>
  <div class="field">
    <div class="ui radio checkbox">
      <input type="radio" tabindex="0" name="toValidate" v-model="find.object.objectValidationStatus" value="Klaar voor validatie">
      <label>Vondstfiche is klaar voor validatie.</label>
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
</step>



<div v-if="submittable || (user.administrator && step==5 || !user.administrator && step == 6)">
  <p v-if="submitting&&toValidate" style="color:#090">
    Bedankt, jouw vondstfiche wordt bewaard en doorgestuurd voor validatie.
    <br>Je krijgt een verwittiging van de uitkomst zodra dit is gebeurd.
  </p>
  <p v-if="submitting&&!toValidate" style="color:#090">
    Bedankt, jouw vondstfiche wordt bewaard.
  </p>
  <div v-show="confirmNextMessage" class="ui success message visible">
    <button class="ui button green pull-right" type="button" @click="confirmNext">OK!</button>
    <div class="header">
      De vondst werd opgeslaan
    </div>
    <p>U kan nu een volgende vondst ingeven</p>
  </div>
  <div v-show="! confirmNextMessage">
    <div v-if="!submitting" class="field">
      <div class="ui checkbox" v-if="user.registrator && !isEditing">
        <br>
        <input type="checkbox" v-model="addAnother" id="addAnother">
        <label for="addAnother">
          <b>Nog een vondst toevoegen met dezelfde data</b>
        </label>
      </div><br><br>
      <button v-if="!toValidate" class="ui orange button" type="submit">Voorlopig bewaren</button>
      <button v-if="toValidate" class="ui button" type="submit" :class="{green:submittable}" :disabled="!submittable">Bewaren en laten valideren</button>
    </div>
    <div v-else>
      <button type="button" class="ui disabled grey button" disabled>Even geduld...</button>
    </div>
  </div>
</div>
<div class="field" v-if="!submittable">
  <p  style="color:red">
    Niet alle verplichte velden zijn ingevuld.
  </p>
</div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div class="ui message" v-if="!HelpText.create">
  <p>
    Deze helpteksten hebben je hopelijk op weg geholpen. Hieronder kan je ze uitschakelen. Het is steeds mogelijk om ze terug te tonen.
  </p>
  <p>
    <button class="ui green button" type="button" @click="hideHelp('create')">OK, helptekst verbergen</button>
  </p>
</div>
<p v-else>
  <button class="ui green button" type="button" @click="showHelp('create')">Helptekst tonen</button>
</p>

{!! Form::close() !!}
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
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
console.log('finds.edit:', window.initialFind)
@endif
</script>
<script src="{{ asset('js/finds-create.js') }}?{{ Config::get('app.version') }}"></script>
@endsection
