import axios from 'axios'

const CancelToken = axios.CancelToken

/**
 * Make a request that cancels requests that return after a subsequent request has been return, essentially avoiding a race condition
 * An alternative approach to passing cancelTokens with the function would be
 * that we keep request names in a const file, to avoid developers overwriting/re-using the wrong cancel tokens, and keep the cancel tokens in this file
 *
 * @param String requestUri
 * @param String requestName
 * @param Object cancelTokens For now we expect each consumer of this function to pass its own collection of cancel tokens
 * @returns {Promise<AxiosResponse<any>>}
 */
export const createCancelIfLateRequest = (requestUri, requestName, cancelTokens) => {
  return axios.get(requestUri, { cancelToken: handleNewCancelToken(requestName, cancelTokens).token })
}

/**
 * Cancels previous made requests based on the requestName and returns a new CancelToke Source object
 *
 * @param requestName
 * @param cancelTokens
 * @returns {*}
 */
export const handleNewCancelToken = (requestName, cancelTokens) => {
  cancelTokens[requestName] && cancelTokens[requestName].cancelToken && cancelTokens[requestName].cancelToken.cancel()

  if (!cancelTokens[requestName]) {
    cancelTokens[requestName] = {}
  }

  cancelTokens[requestName].cancelToken = CancelToken.source();

  return cancelTokens[requestName].cancelToken;
}