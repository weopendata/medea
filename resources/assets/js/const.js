export const EMPTY_CLS = {
	productionClassificationCulturePeople: '',
	productionClassificationDescription: '',
	productionClassificationNation: '',
	productionClassificationPeriod: '',
	productionClassificationType: '',
	startDate: '',
	endDate: '',
	publication: [{ publicationTitle: '' }],
}

// Date & time
export const MONTHS = 'jan,feb,mar,apr,may,jun,jul,aug,sept,oct,nov,dec'.split(',')

export function toMonth (d) {
  d = new Date(Date.parse(d))
  return MONTHS[d.getMonth()] + ', ' + d.getFullYear()
}

export const fromDate = function (d) {
  d = new Date(Date.parse(d))
  return d.getDay() + ' ' + MONTHS[d.getMonth()] + ' ' + d.getFullYear()
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
