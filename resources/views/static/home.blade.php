@extends('main')

@section('title', 'Home')

@section('content')
  <div class="hero">
    <div class="ui container">
      <p class="lead">Welkom op het <strong>MEDEA</strong> platform, een community platform dat experten, onderzoekers en detectoristen samenbrengt.</p>
      <p>
        Verken de metaalvondsten die we samen in Vlaanderen in kaart brachten
      </p>
      <p>
        <div class="ui action input" style="width: 30%;">
          <input type="text" placeholder="Search...">
          <button class="ui blue icon button">
            <i class="search icon"></i>
          </button>
        </div>
      </p>
      <p>
        <a href="/finds" class="ui green button"><i class="sign in icon"></i> Log in </a>
        <a href="#register" class="ui green button"><i class="edit icon"></i> Word lid </a>
      </p>
    </div>
  </div>
{!! Form::open(array(
'action' => 'Auth\AuthController@register',
'novalidate' => '',
'class' => 'ui register-container form',
'@submit.prevent' => 'submit',
)) !!}
  <a href="/login" style="float:right" class="ui basic small button">Login</a>
  <h2 id="register">Registreren</h2>
  <h3>Contactgegevens</h3>
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
  <div class="fields" :class="{error:errors.phone}>
    <div class="field">
      <label for="phone">Telefoon</label>
      <input v-model="user.phone" type="text">
    </div>
  </div>
  <div class="field" :class="{error:errors.email}">
    <label for="email">Email</label>
    <input v-model="user.email" type="email">
    <div v-for="msg in errors.email" v-text="msg" class="input"></div>
  </div>
  <div class="field" :class="{error:errors.password}">
    <label for="pw" @click="show.password=!show.password">Wachtwoord <a href="#" @click.prevent style="color:#999;font-weight:normal" v-text="show.password?'wordt getoond':'tonen'">tonen</a></label>
    <input v-model="user.password" :type="show.password?'text':'password'">
    <div v-for="msg in errors.password" v-text="msg" class="input"></div>
  </div>
  <h3>Rollen</h3>
  <div class="field">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden" v-model="roles.detectorist">
      <label>
        <b>Detectorist</b>
        <br>registreert eigen vondsten.
      </label>
    </div>
  </div>
  <p v-if="!show.roles">
    <a href="#" @click.prevent="show.roles=1">Toon andere rollen: vondstexpert, onderzoeker &amp; registrator</a>
  </p>
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
      <input type="checkbox" tabindex="0" class="hidden" v-model="roles.registrator">
      <label>
        <b>Registrator</b>
        <br>beheert de vondsten van een bestaande collectie of project.
      </label>
    </div>
  </div>
  <div class="field" v-show="show.roles">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden" v-model="roles.validator">
      <label>
        <b>Validator</b>
        <br>kan vondsten valideren.
      </label>
    </div>
  </div>
  <div class="required field" v-if="roles.expert">
    <label for="expertise">Expertise</label>
    <textarea-growing style="min-height: 60px" id="expertise" :model.sync="user.expertise" placeholder="Schrijf iets kort over jouw expertisedomein. Graag ook categorie en/of periode en/of regio vermelden."></textarea>
  </div>
  <div class="field" v-show="roles.expert">
    <div class="ui checkbox">
      <input type="checkbox" tabindex="0" class="hidden" v-model="roles.onderzoeker">
      <label>Ik heb als onderzoeker toegang tot de CAI en wil ook op MEDEA volledige toegang krijgen tot exacte vondstlocaties</label>
    </div>
  </div>
  <div class="required field" v-if="roles.onderzoeker">
    <label for="research">Onderzoek</label>
    <textarea-growing id="research" :model.sync="user.research" placeholder="Schrijf iets kort over je onderzoeksproject."></textarea>
  </div>
  <div class="field" v-if="roles.detectorist||roles.registrator">
    <label for="bio">Biografie</label>
    <textarea-growing id="bio" :model.sync="user.bio" placeholder="Schrijf een korte biografie."></textarea-growing>
  </div>
  <div class="field" v-if="roles.detectorist">
    <label for="email">Erkenningsnummer</label>
    <div class="ui labeled input double-labeled">
      <div class="ui default label">
        OE/ERK/Metaaldetectorist/
      </div>
      <input type="text" v-model="oeerk.jjjj" placeholder="jjjj" style="min-width: 0;flex-shrink: 1">
      <div class="ui default label">
      /
      </div>
      <input type="text" v-model="oeerk.nnnnn" placeholder="00000" style="min-width: 0;flex-shrink: 1">
    </div>
  </div>
  <div v-show="roles.detectorist">
    <h3>Privacy</h3>
    <div class="field">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="user.showContactInfo">
        <label>Vermeld mijn naam op publiek toegankelijke vondstfiches</label>
      </div>
    </div>
    <div class="field">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="user.passContactInfoToAgency">
        <label>
          <b>Ik laat toe dat mijn naam doorgegeven wordt bij meldingen aan Agentschap Onroerend Erfgoed</b>
          <br><i>Merk op: Vanaf 1 april 2016 geldt de verplichting om als detectorist erkend te zijn door Onroerend Erfgoed. Indien u een erkenning heeft, dient u zich dus steeds bekend te maken bij melding van vondsten gedaan vanaf deze datum. Enkel toevalsvondsten kunnen nog gemeld worden zonder persoonsgegevens.</i>
        </label>
      </div>
    </div>
    <br>
  </div>
  <div class="field">
    <button type="submit" class="ui button" :class="{green:submittable}" :disabled="!submittable">Registreer</button>
  </div>
  <div v-if="registered" style="color:#090">
    <h3>Registratie wordt gevalideerd</h3>
    <p>De administrator zal je registratie bevestigen. Dit kan even duren. In volgende iteraties van het platform kan dit automatisch gebeuren.</p>
  </div>
{!! Form::close() !!}
@endsection

@section('script')
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('js/home.js') }}"></script>

@if (Config::get('app.debug'))
<script type="text/javascript">
document.write('<script src="//localhost:35729/livereload.js?snipver=1" type="text/javascript"><\/script>')
</script>
@endif
@endsection