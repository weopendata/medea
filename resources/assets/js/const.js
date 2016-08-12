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
