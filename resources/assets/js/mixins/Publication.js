export default {
  methods: {
    // Build a literature citation for the given publication based on its type
    cite (pub) {
      if (!pub) {
        return
      }

      switch (pub.publicationType) {
        case 'boek':
          return this.citeBook(pub);
        case 'boekbijdrage':
          return this.citeBookAttribution(pub);
        case 'tijdschriftartikel':
          return this.citeArticle(pub);
        case 'internetbron':
          return this.citeInternetSource(pub);
        default:
          return [
            (pub.author || 'Auteur'),
            (pub.pubTimeSpan ? ' (' + pub.pubTimeSpan + '). ' : ''),
            (pub.publicationTitle || 'Titel')
            ].join('') + ', ' + pub.pubLocation
      }
    },
    citeBook (pub) {
      return (pub.author || 'Unknown Author') + ', '
        + (pub.pubTimeSpan ? pub.pubTimeSpan : '') + '. '
        + (pub.publicationTitle || 'Unknown title')
        + ', ' + pub.pubLocation + '.'
    },
    citeArticle (pub) {
      if (pub.parentVolume) {
        return (pub.author || 'Unknown Author') + ', '
          + (pub.pubTimeSpan ? pub.pubTimeSpan : '') + '. '
          + (pub.publicationTitle ? "'" + pub.publicationTitle + "'" : "'Unknown title'")
          + ', ' + pub.parentTitle + ' ' + pub.parentVolume
          + ': ' + pub.publicationPages + '.'
      }

      return (pub.author || 'Unknown Author') + ', '
        + (pub.pubTimeSpan ? pub.pubTimeSpan : '') + '. '
        + (pub.publicationTitle ? "'" + pub.publicationTitle + "'" : "'Unknown title'")
        + ', ' + pub.pubLocation + '.'
    },
    citeBookAttribution (pub) {
      // backwards compatible
      if (pub.editor) {
        return (pub.author || 'Unknown Author') + ', '
          + (pub.pubTimeSpan ? pub.pubTimeSpan : '') + '. '
          + (pub.publicationTitle ? "'" + pub.publicationTitle + "'" : "'Unknown title'")
          + ', ' + pub.parentTitle + ' (ed. ' + pub.editor + ')'
          + ', ' + pub.pubLocation + ': ' + pub.publicationPages + '.'
      }

      return (pub.author || 'Unknown Author') + ', '
        + (pub.pubTimeSpan ? pub.pubTimeSpan : '') + '. '
        + (pub.publicationTitle ? "'" + pub.publicationTitle + "'" : "'Unknown title'")
        + ', ' + pub.pubLocation + '.'
    },
    citeInternetSource (pub) {
      if (pub.parentTitle) {
        return (pub.author ? pub.author + ', ' : '')
          + (pub.publicationTitle ? pub.publicationTitle : "Unknown title")
          + ', ' + pub.parentTitle
          + ', ' + pub.pubLocation
      }

      return (pub.author ? pub.author + ', ' : '')
        + (pub.publicationTitle ? pub.publicationTitle : "Unknown title")
        + ', ' + pub.pubLocation
    }
  }
}
