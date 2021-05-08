<template>
  <div class="typology-info-card__container">
    <div v-if="typology && typology.code">
      <div class="ui card">
        <div class="content">
          <h2 class="header typology-info-card__header">{{typology.label}}&nbsp;[{{typology.code}}]</h2>
        </div>

        <dl class="typology-info-card__info-list">
          <dt>
            PAN-type
          </dt>
          <dd>
            <a :href="'https://data.cultureelerfgoed.nl/term/id/pan/' + typology.code + '.html'" target="_blank">{{typology.code}}</a>
          </dd>
        </dl>

        <!-- image / sketch -->
        <div class="typology-info-card__image">
          <img :src="imageUrl"/>
        </div>

        <dl class="typology-info-card__info-list">
          <dt>PAN naam</dt>
          <dd>{{typology.label}}</dd>

          <dt v-if="typologyTimeFrame">Datering</dt>
          <dd v-if="typologyTimeFrame">{{typologyTimeFrame}}</dd>

          <dt v-if="typologyPeriod">Period</dt>
          <dd v-if="typologyPeriod">{{typologyPeriod}}</dd>

          <dt v-if="typologyDefinition">Definitie</dt>
          <dd v-if="typologyDefinition">{{typologyDefinition}}</dd>

          <dt v-if="typologyExplanation && typologyExplanation.length > 0">Toelichting</dt>
          <dd v-for="(explanation, index) in typologyExplanation">{{explanation}}</dd>

          <dt v-if="typologyReferences && typologyReferences.length > 0">Verwijzingen</dt>
          <dd v-for="(reference, index) in typologyReferences">{{reference}}</dd>

          <dt v-if="sourceLinks && sourceLinks.length > 0">Links</dt>
          <dd v-for="(link, index) in sourceLinks"><a :href="link" target="_blank">{{link}}</a></dd>
        </dl>
      </div>
    </div>
    <div v-else>
      Klik op het oog-icoon in de typologie-boom om details ervan te bekijken.
    </div>
  </div>
</template>

<script>
  export default {
    name: "TypologyInfoCard",
    props: ['typology'],
    computed: {
      imageUrl () {
        if (this.typology.imageUrl === 'resources/images/dummyreferencetypeinlay.png') {
          return 'https://portable-antiquities.nl/pan/resources/images/dummyreferencetypeinlay.png'
        }

        return this.typology.imageUrl
      },
      typologyProperties () {
        return (this.typology && this.typology.properties) || {}
      },
      typologyTimeFrame () {
        var start;
        var end;

        if (this.typologyProperties && this.typologyProperties.InitialDate && this.typologyProperties.InitialDate.length > 0) {
          start = this.typologyProperties.InitialDate[0]
        }

        if (this.typologyProperties && this.typologyProperties.FinalDate && this.typologyProperties.FinalDate.length > 0) {
          end = this.typologyProperties.FinalDate[0]
        }

        return this.formatTimeString(start, end)
      },
      typologyPeriod () {
        var start;
        var end;

        if (! this.typology) {
          return
        }

        if (this.typology && this.typology.initialperiod && this.typology.initialperiod.label) {
          var startYear = this.typology.initialperiod.startyear ? this.typology.initialperiod.startyear : ''
          var endYear = this.typology.initialperiod.endyear ? this.typology.initialperiod.endyear : ''

          start = this.typology.initialperiod.label + (startYear || endYear ? ' (' + startYear + ' / ' + endYear + ')' : '')
        }

        if (this.typology && this.typology.finalperiod && this.typology.finalperiod.label) {
          var startYear = this.typology.finalperiod.startyear ? this.typology.finalperiod.startyear : ''
          var endYear = this.typology.finalperiod.endyear ? this.typology.finalperiod.endyear : ''

          end = this.typology.finalperiod.label + (startYear || endYear ? ' (' + startYear + ' / ' + endYear + ')' : '')
        }

        return this.formatTimeString(start, end)
      },
      typologyDefinition () {
        if (!this.typology || !this.typology.scopeNotes) {
          return
        }

        return this.typology.scopeNotes[0]
      },
      typologyExplanation () {
        if (!this.typology || !this.typology.definitions) {
          return []
        }

        return this.typology.definitions
      },
      typologyReferences () {
        if (!this.typologyProperties || !this.typologyProperties.Reference) {
          return []
        }

        return this.typologyProperties.Reference
      },
      sourceLinks () {
        if (!this.typologyProperties || !this.typologyProperties.SourceLink) {
          return []
        }

        return this.typologyProperties.SourceLink
      }
    },
    methods: {
      formatTimeString (start, end) {
        if (!end && !start) {
          return
        }

        if (!end && start) {
          return start
        }

        if (end && !start) {
          return end
        }

        return start + ' tot ' + end
      }
    }
  }
</script>

<style lang="scss" scoped>
  .typology-info-card__container {
    max-width: 700px;
    width: 700px;
    margin-left: auto;
    margin-right: auto;

    dd {
      margin-bottom: 0.5rem;
    }
  }

  .typology-info-card__header {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
    margin-bottom: 2.5rem;
    padding: 1rem;
  }

  .typology-info-card__info-list {
    margin-left: 2rem;
    margin-right: 1rem;
  }

  .typology-info-card__image {
    display: flex;
    justify-content: center;
    margin-bottom: 2.5rem;
  }
</style>
