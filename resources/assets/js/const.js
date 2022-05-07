export const GEO_ROUND_LAT = 0.1
export const GEO_ROUND_LNG = 0.1

const thisYear = new Date().getFullYear()
const thisYearMonth = new Date().toJSON().slice(0, 7)

// Remove reactivity
export function inert (v) {
  return JSON.parse(JSON.stringify(v))
}

// Query
export function fromQuery (query) {
  var match
  var output = {}
  var pl = /\+/g
  var search = /([^&=]+)=?([^&]*)/g
  var decode = function (s) {
    return decodeURIComponent(s.replace(pl, ' '))
  }
  query = query || window.location.search.substring(1)

  while (match = search.exec(query)) {
    output[decode(match[1])] = decode(match[2])
  }
  return output
}

// FindEvent helpers
export function doesFindBelongToExcavation (find) {
  return find.excavationTitle
}

export function doesFindHaveAPanTypology (find) {
  return find._panTypologyInfo && find._panTypologyInfo.label
}

export function findPanTypologyTitle (find) {
  if (!doesFindHaveAPanTypology(find)) {
    return
  }

  let title = find._panTypologyInfo.label + '(' + find._panTypologyInfo.mainCategory + ')'

  if (find.material) {
    title += ', ' + find.material
  }

  return title
}

export function findExcavationInfoString (find) {
  if (!doesFindBelongToExcavation(find)) {
    return
  }

  let title = find.excavationTitle

  if (find.objectNr) {
    title += ', vondstnummer: ' + find.objectNr
  }

  return title
}

export function findTitle (find) {
  if (!find) {
    return 'Probleem met vondst'
  }

  if (doesFindHaveAPanTypology(find)) {
    let title = find._panTypologyInfo.label + '(' + find._panTypologyInfo.mainCategory + ')'

    if (find.material) {
      title += ', ' + find.material
    }

    if (find.excavationTitle) {
      title += ', ' + find.excavationTitle
    }

    if (find.objectNr) {
      title += ', vondstnummer: ' + find.objectNr
    }

    return title
  }

  const title = (find.object ? [
    find.object.objectCategory || 'ongeïdentificeerd',
    find.object.period,
    find.object.objectMaterial
  ] : [
    find.category || 'ongeïdentificeerd',
    find.period,
    find.material
  ]).filter(f => f && f !== 'onbekend').join(', ')

  return title + ' (ID-' + find.identifier + ')'
}

// Collection helpers
export function incomingCollection (collection) {
  // Concatenate the institution appellations to a string and attribute it to the institutions property of the collection
  collection.institutions = collection.institution ? collection.institution.map(inst => inst.institutionAppellation).join(', ') : ''
  return collection
}

// Classification helpers
export function emptyClassification () {
  return {
    productionClassificationCulturePeople: '',
    productionClassificationDescription: '',
    productionClassificationPeriod: '',
    productionClassificationRulerNation: '',
    productionClassificationSource: [],
    productionClassificationType: '',
    startDate: '',
    endDate: '',
    publication: [],
  }
}

// Publication helpers
const TYPE_AUTHOR = 'author'
const TYPE_EDITOR = 'editor'

const TYPE = 'publicationCreationActorType'
const NAME = 'publicationCreationActorName'
const ACTOR = 'publicationCreationActor'

export function fromPublication (p) {
  p = inert(p)

  const creations = (p.publicationCreation || [])

  // Get author, their names and split them
  let authorsCreation = creations.find(a => a[ACTOR] && a[ACTOR].length && a[ACTOR][0][TYPE] === TYPE_AUTHOR || a[ACTOR][TYPE] === TYPE_AUTHOR) || {}
  let authors = authorsCreation && authorsCreation[ACTOR] || []
  authors = [].concat(authors)

  let editors = []

  // Special treatment for 'boekbijdrage'
  if (p.publicationType == 'boekbijdrage' && p.publication) {
    var bookCreation = (p.publication.publicationCreation || [])

    // Get the editors
    let editorsCreation = bookCreation.find(a => a[ACTOR] && a[ACTOR].length && a[ACTOR][0][TYPE] === TYPE_EDITOR || a[ACTOR][TYPE] === TYPE_EDITOR) || {}
    editors = editorsCreation && editorsCreation[ACTOR] || []
    editors = [].concat(editors)

    return Object.assign(p, {
      author: authors.map((a) => {
        return a[NAME]
      }).join(' & '),
      editor: editors.map((a) => {
        return a[NAME]
      }).join(' & '),
      pubTimeSpan: editorsCreation.publicationCreationTimeSpan && editorsCreation.publicationCreationTimeSpan.date || '',
      pubLocation: editorsCreation.publicationCreationLocation && editorsCreation.publicationCreationLocation.publicationCreationLocationAppellation || '',
      parentTitle: p.publication ? p.publication.publicationTitle : null
    })
  }

  return Object.assign(p, {
    author: authors.map((a) => {
      return a[NAME]
    }).join(' & '),
    pubTimeSpan: authorsCreation.publicationCreationTimeSpan && authorsCreation.publicationCreationTimeSpan.date || '',
    pubLocation: authorsCreation.publicationCreationLocation && authorsCreation.publicationCreationLocation.publicationCreationLocationAppellation || '',
    parentVolume: p.publication ? p.publication.publicationVolume : null,
    parentTitle: p.publication ? p.publication.publicationTitle : null
  })
}

// TODO: separate logic for publications that have nested structures such as tijdschriftartikel and boekbijdrage
export function toPublication (p) {
  p = inert(p)

  const authorNames = p.author.split('&', 2)
  var authorArr = []

  for (var i = 0; i < authorNames.length; i++) {
    authorArr.push({ [NAME]: authorNames[i].trim(), [TYPE]: TYPE_AUTHOR })
  }

  var timeSpan = p.pubTimeSpan ? { date: p.pubTimeSpan } : null
  var location = p.pubLocation ? { publicationCreationLocationAppellation: p.pubLocation } : null
  var relatedPublication = null;

  if (p.publicationType == 'boekbijdrage') {
    const editorNames = p.editor.split('&', 2)
    var editorArr = []

    for (var i = 0; i < editorNames.length; i++) {
      editorArr.push({ [NAME]: editorNames[i].trim(), [TYPE]: TYPE_EDITOR })
    }

    var book = {
      publicationCreation: [
        {
          publicationCreationActor: editorArr,
          publicationCreationTimeSpan: timeSpan,
          publicationCreationLocation: location
        },
      ],
      publicationTitle: p.parentTitle
    }

    return Object.assign(p, {
          publicationCreation: [
            {
              // Include author if available
              publicationCreationActor: authorArr,
            }
          ]
        },
        {
          publication: book
        })
  }

  if (p.publicationType == 'tijdschriftartikel') {
    relatedPublication = { publicationVolume: p.parentVolume, publicationTitle: p.parentTitle }
  }

  if (p.publicationType == 'internetbron') {
    relatedPublication = { publicationTitle: p.parentTitle }
  }

  if (relatedPublication) {
    return Object.assign(p, {
          publicationCreation: [
            {
              // Include author if available
              publicationCreationActor: authorArr,
              publicationCreationTimeSpan: timeSpan,
              publicationCreationLocation: location
            }
          ]
        },
        {
          publication: relatedPublication
        })
  }

  return Object.assign(p, {
    publicationCreation: [
      {
        // Include author if available
        publicationCreationActor: authorArr,
        publicationCreationTimeSpan: timeSpan,
        publicationCreationLocation: location
      }
    ]
  });
}

export function urlify (u) {
  if (!u) {
    return
  }
  if (u.slice(0, 4) === 'http') {
    return {
      href: u
    }
  }
  return {
    text: u
  }
}

// Date & time
export const MONTHS = 'jan,feb,mar,apr,may,jun,jul,aug,sept,oct,nov,dec'.split(',')

export function toMonth (d) {
  d = new Date(Date.parse(d))
  return MONTHS[d.getMonth()] + ', ' + d.getFullYear()
}

export function fromDate (d) {
  if (!d || d.length < 10) {
    return d
  }
  d = new Date(Date.parse(d))
  return d.getDate() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear()
}

export function validDate (s) {
  s = s || ''

  // Unknown
  if (s === 'onbekend') {
    return true
  }

  // Year only
  if (/(20|19)[0-9][0-9]/.test(s) && s <= thisYear) {
    return true
  }

  // Year-month
  if (/(20|19)[0-9][0-9]\-[0-1][0-9]/.test(s) && s <= thisYearMonth) {
    return true
  }

  // Year-month
  if (s.length < 6) {
    return false
  }

  // Year-month-day
  const d = new Date(Date.parse(s))
  d.setHours(3)
  if (d.toJSON() && d.getFullYear() > 1900 && d <= new Date()) {
    const format = d.toJSON().slice(0, 10)
    if (format !== s) {
      return format
    }
    return true
  }
}

// Filesize
export const MAX_FILE_SIZE = 4 * 1024 * 1024

export function toBytes (bytes) {
  return bytes < 10000
      ? bytes.toFixed(0) + ' B'
      : bytes < 1024000
          ? (bytes / 1024).toPrecision(3) + ' kB'
          : (bytes / 1024 / 1024).toPrecision(3) + ' MB'
}

// Public bounds rounded to 7km
export function toPublicBounds (f) {
  let pubLat = Math.round(f.lat / GEO_ROUND_LAT) * GEO_ROUND_LAT
  let pubLng = Math.round(f.lng / GEO_ROUND_LNG) * GEO_ROUND_LNG
  return {
    north: pubLat + GEO_ROUND_LAT / 2,
    south: pubLat - GEO_ROUND_LAT / 2,
    east: pubLng + GEO_ROUND_LNG / 2,
    west: pubLng - GEO_ROUND_LNG / 2
  }
}
