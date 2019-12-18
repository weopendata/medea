<template>
  <div class="ui container">
    <h1>
      {{ profile.firstName }} {{ profile.lastName }}
      <template v-if="roles.includes('detectorist')">
        <small>
          Metaaldetectorist: {{ findCount }} vondst{{ findCount == 1 ? '' : 'en' }}
        </small>
      </template>
    </h1>
     <p>
      Lid van MEDEA sinds {{ profile.created_at }}
    </p>

    <!--@if (!empty($profile['expertise']) || !empty($profile['bio']) || !empty($profile['research']) || !empty($profile['function']) || !empty($profile['affiliation']))-->
    <template v-if="profile.expertise || profile.bio || profile.research || profile.function || profile.affiliation">
      <h3>Over mij</h3>
      <!--@if (!empty($profile['function'])-->
      <template v-if="profile.function">
        <p>
          <b>Functie</b>: {{profile.function}} <!--{!! nl2br(e($profile['function'])) !!}-->
        </p>
      </template>
      <!--@endif-->
      <!--@if (!empty($profile['affiliation']))-->
      <template v-if="profile.affiliation">
        <p>
          <b>Instelling</b>: {{profile.affiliation}}<!--{!! nl2br(e($profile['affiliation'])) !!}-->
        </p>
      </template>
      <!--@endif-->
      <!--@if (!empty($profile['research']))-->
      <p>
        <b>Onderzoek</b>: {{profile.research}} <!--{!! nl2br(e($profile['research'])) !!}-->
      </p>
      <!--@endif-->
      <!--@if (!empty($profile['bio']))-->
      <p>
        <b>Bio</b>: {{profile.bio}}<!--{!! nl2br(e($profile['bio'])) !!}-->
      </p>
      <!--@endif-->
      <!--@if (!empty($profile['expertise']))-->
      <p>
        <b>Expertise</b>: {{profile.expertise}}<!--{!! nl2br(e($profile['expertise'])) !!}-->
      </p>
      <!--@endif-->
    </template>
    <!--@endif-->
  <!--@endif-->

  <!--@if (! empty(session('message')))
  <div class="ui positive message">
    <i class="close icon"></i>
    <p>{!! nl2br(e(session('message'))) !!}</p>
  </div>
  @endif-->

  <template v-if="user.id != id">
    <h3>Contact</h3>
    <!--@if (@$profile['showEmail'])-->
    <template v-if="profile.showEmail">
      <p>
        Email: <a :href="'mailto:' + profile.email">{{ profile.email }}</a>
      </p>
    </template>
    <template v-else>
      <form @submit.prevent="sendMessage" method="POST" class="ui form" style="max-width:25em">
        <input type="hidden" name="user_id" :value="id">
        <div class="field">
          <label>Bericht aan {{ profile.firstName }}</label>
          <textarea rows="3" name="message" v-model="message"></textarea>
        </div>
        <div class="field">
          <button type="submit" class="ui small blue button">Verzenden</button>
        </div>
      </form>
      <div class="ui message" :class="messageStatus" v-if="messageSent != null" style="width: 250px;">
        <i class="close icon"></i>
        <p>{{messageResponse}}</p>
      </div>
    </template>
  </template>
  <!--@endif-->

  <h3>Rollen:</h3>
  <ul>
    <li v-for="role in roles">{{ role }}</li>
  </ul>

  <!--@if (isset($collections))-->
  <template v-if="collections">
    <div>
      <h3>Collecties:</h3>
      <user-collections
      :collections="collections"
      :profile="profile"
      :errors="collectionErrors"
      @assignCollection="assignCollection"
      @removeCollection="removeCollection"
      >
      </user-collections>
    </div>
  </template>
  <!--@endif-->
  </div>
</template>

<script>
  import UserCollections from '@/components/UserCollections'

  import Notifications from '@/mixins/Notifications'

  export default {
    data () {
      return {
        user: window.medeaUser,
        profile: window.profile,
        findCount: window.findCount,
        roles: window.roles,
        collections: window.collections,
        profileAccessLevel: window.profileAccessLevel,
        id: window.id,
        message: '',
        messageSent: null,
        messageResponse: '',
        collectionErrors: {}
      }
    },
    computed: {
      messageStatus () {
        if (this.messageSent == null) {
          return;
        }

        return this.messageSent ? 'positive' : 'negative';
      }
    },
    methods: {
      sendMessage () {
        this.messageSent = null;

        axios.post('/sendMessage', {message: this.message, user_id: id})
        .then(response => {
          this.messageSent = true;
          this.message = '';
          this.messageResponse = response.data.message;
        })
        .catch(err => {
          this.messageSent = false;
          this.messageResponse = err.response.data.message && err.response.data.message[0];
        })
      },
      assignCollection(collection) {
        axios.put('/collections/' + collection.identifier + '/persons/' + this.profile.identifier)
        .then(persons => {
          this.collectionErrors = {}
          this.collections.push(collection);
        })
        .catch(errors => {
          this.collectionErrors = errors.data;
        })
      },
      removeCollection(collection) {
        axios.delete('/collections/' + collection.identifier + '/persons/' + this.profile.identifier)
        .then(persons => {
          this.collectionErrors = {}
          this.collections.splice(this.collections.indexOf(collection), 1)
        })
        .catch(errors => {
          this.collectionErrors = errors.data
        })
      }
    },
    components: {
      UserCollections
    },
    mixins: [Notifications]
  }
</script>