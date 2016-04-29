@extends('main')

@section('title', 'Instellingen')

@section('content')
{!! Form::open(array(
'novalidate' => '',
'class' => 'ui container form',
'@submit.prevent' => 'submit',
)) !!}
    <a href="/logout" class="ui red button" style="position:absolute;z-index:2;top:0;right:0">Afmelden</a>
<div class="ui stackable grid two columns">
  <div class="column">
    
    <h2>Contactgegevens</h2>
    <div class="two fields">
      <div class="field">
        <label for="first_name">Voornaam</label>
        <input v-model="user.firstName" type="text">
      </div>
      <div class="field">
        <label for="last_name">Achternaam</label>
        <input v-model="user.lastName" type="text">
      </div>
    </div>
    <div class="two fields">
      <div class="field">
        <label for="email">Email</label>
        <input v-model="user.email" type="email">
      </div>
      <div class="field">
        <label for="last_name">Telefoon</label>
        <input v-model="user.lastName" type="text">
      </div>
    </div>
    <div class="field">
      <label for="adr">Adres</label>
      <input v-model="user.address" type="text">
    </div>
    <div class="field">
      <label for="pw" @click="show.password=!show.password">Wachtwoord <a href="#" @click.prevent style="display:none;color:#999;font-weight:normal" v-text="show.password?'wordt getoond':'tonen'">tonen</a></label>
      <input v-model="user.password" :type="show.password?'text':'password'" placeholder="Laat dit leeg om je wachtwoord te behouden">
    </div>

    <h2>Profiel</h2>
    <div class="two fields">
      <div class="field">
        <label for="email">Functie</label>
        <input v-model="user.function" type="text">
      </div>
      <div class="field">
        <label for="last_name">Affiliatie</label>
        <input v-model="user.affiliation" type="text">
      </div>
    </div>
    <div class="field" v-if="roles.detectorist">
      <label for="email">Erkenningsnummer</label>
      <div class="ui labeled input double-labeled">
        <div class="ui default label">
          OE/ERK/Metaaldetectorist/
        </div>
        <input type="text" placeholder="jjjj" style="min-width: 0;flex-shrink: 1">
        <div class="ui default label">
        /
        </div>
        <input type="text" placeholder="00000" style="min-width: 0;flex-shrink: 1">
      </div>
    </div>
    <div class="field" v-if="roles.expert">
      <label for="role">Expertise</label>
      <textarea id="description" name="description" rows="5" placeholder="Schrijf iets kort over jouw expertisedomein. Graag ook categorie en/of periode en/of regio vermelden."></textarea>
    </div>
    <div class="field" v-if="roles.onderzoeker">
      <label for="role">Onderzoek</label>
      <textarea id="description" name="description" rows="5" placeholder="Schrijf iets kort over je onderzoeksproject."></textarea>
    </div>
    <div class="field" v-if="roles.detectorist||roles.registrator">
      <label for="role">Biografie</label>
      <textarea id="description" name="description" rows="5" placeholder="Schrijf een korte biografie."></textarea>
    </div>

  </div>
  <div class="column">

    <h2>Stuur mij een email...</h2>
    <div class="field" v-show="show.roles">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="roles.registrator">
        <label>bij een wijzigingen aan mijn vondsten</label>
      </div>
    </div>
    <div class="field" v-show="show.roles">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="roles.registrator">
        <label>bij een nieuwe classificatie van mijn vondsten</label>
      </div>
    </div>
    <div class="field" v-show="show.roles">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="roles.registrator">
        <label>over de laatste nieuwtjes van MEDEA platform</label>
      </div>
    </div>

    <h2>Rollen</h2>
    <div class="field">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="roles.detectorist">
        <label>
          <b>Detectorist</b>
          <br>registreert eigen vondsten.
        </label>
      </div>
    </div>
    <div class="field" v-show="show.roles">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="roles.expert">
        <label>
          <b>Vondstexpert</b>
          <br>classificeert vondsten volgens expertise.
        </label>
      </div>
    </div>
    <div class="field" v-show="show.roles">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="roles.onderzoeker">
        <label>
          <b>Onderzoeker</b>
          <br>krijgt toegang tot gevoelige vondstgegevens.
        </label>
      </div>
    </div>
    <div class="field" v-show="show.roles">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="roles.registrator">
        <label>
          <b>Registrator</b>
          <br>kan alle vondstgegevens beheren.
        </label>
      </div>
    </div>

    <h2>Privacy</h2>
    <div class="field">
      <label>Profiel openstellen voor</label>
      <select class="ui dropdown" v-model="">
        <option>Iedereen (publiek)</option>
        <option>Geregistreerde gebruikers</option>
        <option>Onderzoekers en overheid</option>
        <option>Onderzoekers</option>
        <option>Onderzoekers na verzoek</option>
        <option>Alleen ik</option>
      </select>
    </div>
    <div class="field">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="user.showContactInfo">
        <label>
          <b>Vermeld mijn naam op publiek toegankelijke vondstfiches</b>
        </label>
      </div>
    </div>
    <div class="field">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="user.passContactInfoToAgency">
        <label>
          <b>Mijn naam mag bij meldingen aan AOE doorgegeven worden</b>
          <br>Alleen toevalsvondsten en vondsten ouder dan april 2016 zullen gemeld worden aan Agentschap Onroerend Erfgoed
        </label>
      </div>
    </div>
  </div>
</div>


{!! Form::close() !!}

<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<dev-bar :user="user"></dev-bar>
@endsection

@section('script')

@endsection
