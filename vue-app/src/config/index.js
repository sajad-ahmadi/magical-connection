export const MagicalConnection = window.MagicalConnectionObject

export const AJAX_HEADERS = {
  "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
}

export function __(text, domain = "magical-connection") {
  if (window.wp?.i18n?.__) {
    return window.wp.i18n.__(text, domain)
  }
  return text
}