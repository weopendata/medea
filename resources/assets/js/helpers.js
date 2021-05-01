/*!
 * array-unique <https://github.com/jonschlinkert/array-unique>
 *
 * Copyright (c) 2014-2015, Jon Schlinkert.
 * Licensed under the MIT License.
 */
export function _unique (arr) {
  if (!Array.isArray(arr)) {
    console.warn(arr)
    throw new TypeError('array-unique expects an array.')
  }

  var len = arr.length
  var i = -1

  while (i++ < len) {
    var j = i + 1

    for (; j < arr.length; ++j) {
      if (arr[i] === arr[j]) {
        arr.splice(j--, 1)
      }
    }
  }
  return arr
}
