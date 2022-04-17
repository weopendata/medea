export default {
  computed: {
    findTitle () {
      if (! this.find) {
        return 'De vondst bestaat niet'
      }

      // Add a fallback for when we lack the PAN typology meta-data
      if (!this.typologyInformation || !this.excavation) {
        const title = (this.find.object ? [
          this.find.object.objectCategory || 'ongeïdentificeerd',
          this.periodOverruled,
          this.find.object.objectMaterial
        ] : [
          this.find.category || 'ongeïdentificeerd',
          this.periodOverruled,
          this.find.material
        ]).filter(f => f && f !== 'onbekend').join(', ')

        return title + ' (ID-' + this.find.identifier + ')'
      }

      return this.typologyInformation.label + ', ' + this.excavation.excavationTitle
    },
    periodOverruled () {
      if (!this.find.object || !this.find.object.productionEvent) {
        return 'onzeker'
      }

      const periods = (this.find.object.productionEvent.productionClassification || [])
        .map(c => c.productionClassificationCulturePeople)
        .filter(Boolean)
      if (periods.length > 1 && !sameValues(periods)) {
        return 'onzeker'
      }

      return periods[0]
    }
  }
}


