// Parse link header
import parseLinkHeader from "parse-link-header";

export function getPaging (header) {
  if (typeof header === 'function') {
    return parseLinkHeader(header('link')) || {}
  }
  if (typeof header === 'string') {
    return parseLinkHeader(header) || {}
  }



  const linkHeader = (header && header.map && header.map.Link) || (header && header.map && header.map.link) || (header.link)
  return linkHeader && parseLinkHeader(linkHeader[0]) || {}
}
