<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"/>
    <title>MEDEA</title>
</head>

<body :class="{registered:registered}">
  <div class="hero">
    <div class="ui container">
      <p class="lead">Welkom op het <strong>MEDEA</strong> platform, een community platform dat experten, onderzoekers en detectoristen samenbrengt.</p>
      <p>
        <a href="/finds" class="ui green big button"><i class="eye icon"></i> Vondsten bekijken </a>
        <a href="#register" class="ui green big button"><i class="edit icon"></i> Doe mee </a>
      </p>
    </div>
  </div>

{!! Form::open(array(
'action' => 'Auth\AuthController@register',
'novalidate' => '',
'class' => 'ui register-container form',
'@submit.prevent' => 'submit',
)) !!}
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
  <div class="field">
    <label for="email">Email</label>
    <input v-model="user.email" type="email">
  </div>
  <div class="field">
    <label for="pw" @click="show.password=!show.password">Wachtwoord <a href="#" @click.prevent style="color:#999;font-weight:normal" v-text="show.password?'wordt getoond':'tonen'">tonen</a></label>
    <input v-model="user.password" :type="show.password?'text':'password'">
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
  <div class="required field" v-if="roles.expert">
    <label for="role">Expertise</label>
    <textarea id="description" name="description" rows="5" placeholder="Schrijf iets kort over jouw expertisedomein. Graag ook categorie en/of periode en/of regio vermelden."></textarea>
  </div>
  <div class="required field" v-if="roles.onderzoeker">
    <label for="role">Onderzoek</label>
    <textarea id="description" name="description" rows="5" placeholder="Schrijf iets kort over je onderzoeksproject."></textarea>
  </div>
  <div class="required field" v-if="roles.detectorist||roles.registrator">
    <label for="role">Biografie</label>
    <textarea id="description" name="description" rows="5" placeholder="Schrijf een korte biografie."></textarea>
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
  <div v-show="roles.detectorist">
    <h3>Privacy</h3>
    <div class="field">
      <div class="ui checkbox">
        <input type="checkbox" tabindex="0" class="hidden" v-model="user.showContactInfo">
        <label>Vermeld mijn contactgegevens op vondstfiche</label>
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
{!! Form::close() !!}

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('js/home.js') }}"></script>

@if (Config::get('app.debug'))
<script type="text/javascript">
document.write('<script src="//localhost:35729/livereload.js?snipver=1" type="text/javascript"><\/script>')
</script>
@endif
</body>
</html>