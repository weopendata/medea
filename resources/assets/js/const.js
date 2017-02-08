
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
export function findTitle (find) {
  // Not showing undefined and onbekend in title
  var title = [
    find.object.objectCategory,
    find.object.period,
    find.object.objectMaterial
  ].filter(f => f && f !== 'onbekend').join(', ')

  return title + ' (ID-' + find.identifier + ')'
}

// Classification helpers
export function emptyClassification () {
  return {
    productionClassificationCulturePeople: '',
    productionClassificationDescription: '',
    productionClassificationMainType: '',
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
export function fromPublication (p) {
  p = inert(p)
  const creations = (p.publicationCreation || [])
  const publisher = creations.find(a => a[ACTOR][TYPE] === TYPE_PUBLISHER) || {}

  // Get author, their names and split them
  let authors = creations.find(a => a[ACTOR][TYPE] === TYPE_AUTHOR)
  authors = authors && authors[ACTOR] && authors[ACTOR][NAME].split('&', 2) || []

  return Object.assign(p, {
    author: (authors[0] || '').trim(),
    coauthor: (authors[1] || '').trim(),
    publisher: publisher[ACTOR] && publisher[ACTOR][NAME] || '',
    pubTimeSpan: publisher.publicationCreationTimeSpan || '',
    pubLocation: publisher.publicationCreationLocation && publisher.publicationCreationLocation.publicationCreationLocationAppellation || ''
  })
}

export function toPublication (p) {
  p = inert(p)
  const author = [p.author, p.coauthor].filter(Boolean).join(' & ')
  return Object.assign(p, {
    publicationCreation: [

      // Include author if available
      author && {
        publicationCreationActor: {
          [NAME]: author,
          [TYPE]: TYPE_AUTHOR
        }
      } || null,

      // Include publisher if available
      (p.publisher || p.pubTimeSpan || p.pubLocation) && {
        publicationCreationActor: p.publisher && {
          [NAME]: p.publisher,
          [TYPE]: TYPE_PUBLISHER
        } || null,
        publicationCreationTimeSpan: p.pubTimeSpan,
        publicationCreationLocation: p.pubLocation && {
          publicationCreationLocationAppellation: p.pubLocation
        }
      } || null

    ].filter(Boolean)
  })
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
  if (/(20|19)[0-9][0-9]/.test(s) && s <= thisYear ) {
    return true
  }

  // Year-month
  if (/(20|19)[0-9][0-9]\-[0-1][0-9]/.test(s) && s <= thisYearMonth ) {
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
