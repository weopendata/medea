import { createCancelIfLateRequest } from './requests.js'

// Keep track of all finds related api call cancel tokens
const findsCancelTokens = {}

/**
 * @param query
 * @returns {Promise<AxiosResponse<*>>}
 */
export const fetchFinds = (query) => {
  return createCancelIfLateRequest('/api' + query, 'fetchFinds', findsCancelTokens)
}

/**
 * @param query
 * @returns {Promise<AxiosResponse<*>>}
 */
export const fetchFindsPagingInfo = (query) => {
  return createCancelIfLateRequest('/api' + query + '&type=count', 'fetchFindsCountInfo', findsCancelTokens)
}

/**
 * @param query
 * @returns {Promise<AxiosResponse<*>>}
 */
export const fetchFindsFacetInfo = (query) => {
  return createCancelIfLateRequest('/api' + query + '&type=facets', 'fetchFindsFacetsInfo', findsCancelTokens)
}

/**
 * @param query
 * @returns {Promise<AxiosResponse<*>>}
 */
export const fetchFindsMap = (query) => {
  return createCancelIfLateRequest('/api' + query + '&type=heatmap', 'fetchFindsMap', findsCancelTokens)
}