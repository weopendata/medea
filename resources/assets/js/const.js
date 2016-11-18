
export const GEO_ROUND_LAT = 0.1
export const GEO_ROUND_LNG = 0.1

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
export const EMPTY_CLS = {
	productionClassificationCulturePeople: '',
	productionClassificationDescription: '',
	productionClassificationRulerNation: '',
	productionClassificationPeriod: '',
	productionClassificationType: '',
	startDate: '',
	endDate: '',
	publication: [{ publicationTitle: '' }],
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
