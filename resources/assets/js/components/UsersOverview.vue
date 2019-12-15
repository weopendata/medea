<template>
  <div>
    <div class="ui container">
      <h3>Totaal aantal vondsten: {{ stats.finds }}</h3>
      <ul>
        <li>Gevalideerde vondsten: {{ stats.validatedFinds }}</li>
        <li>Aantal classificaties: {{ stats.classifications }}</li>
      </ul>

      <h3>Download vondsten</h3>
      <a class="ui button" href="/api/export">Download</a>
      <table class="ui unstackable table" style="text-align:center;min-width:600px;width:100%">
        <thead>
          <tr>
            <th width="50">Actief</th>
            <th class="th-sortable" :class="$sortBy != 'firstName' ? '' : ($sortOrder == 'DESC' ? 'down' : 'up')">
              <a :href="'?sortBy=firstName&sortOrder=' + $sortOrder == 'DESC' ? 'ASC' : 'DESC'">Voornaam</a>
            </th>
            <th class="th-sortable" :class="$sortBy != 'lastName' ? '' : ($sortOrder == 'DESC' ? 'down' : 'up')">
              <a :href="'?sortBy=lastName&sortOrder=' + $sortOrder == 'DESC' ? 'ASC' : 'DESC'">Achternaam</a>
            </th>
            <th>Detectorist</th>
            <th>Vondstexpert</th>
            <th>Registrator</th>
            <th>Validator</th>
            <th>Onderzoeker</th>
            <th>Administrator</th>
          </tr>
        </thead>
        <tr is="TrUser" v-for="user in users" :user="user"></tr>
      </table>

      <div class="paging">
        <template v-if="paging.first">
          <a :href="paging.first" rel="first" class="ui blue button"><i class="double angle left icon"></i></a>
        </template>
        <template v-else>
          <button disabled class="ui blue disabled button"><i class="double angle left icon"></i></button>
        </template>

        <template v-if="paging.previous">
          <a :href="paging.previous" rel="prev" class="ui blue button">Vorige</a>
        </template>
        <template v-else>
          <button disabled class="ui blue disabled button">Vorige</button>
        </template>

        <template v-if="paging.next">
          <a :href="paging.next" rel="next" class="ui blue button">Volgende</a>
        </template>
        <template v-else>
          <button disabled class="ui blue disabled button">Volgende</button>
        </template>

        <template v-if="paging.last">
          <a :href="paging.last" rel="last" class="ui blue button"><i class="double angle right icon"></i></a>
        </template>
        <template v-else>
          <button disabled class="ui blue disabled button"><i class="double angle right icon"></i></button>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
  import TrUser from '@/components/TrUser';

  import Notifications from '@/mixins/Notifications';

  export default {
    data () {
      return {
        users: [],
        user: {},
        paging: {},
        stats: {},
      }
    },
    mounted() {
      console.log("hiiii");
      this.users = window.users;
      this.user = window.medeaUser;
      this.paging = window.paging;
      this.stats = window.stats;
    },
    components: {
      TrUser
    },
    mixins: [Notifications]
  }
</script>