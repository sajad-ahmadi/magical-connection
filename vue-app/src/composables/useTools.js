export function useTools() {
  const onlyNumbers = (tag) => {
    tag.target.value = tag.target.value.replace(/[^0-9]/g, "")
  }
  const preventNonNumeric = (e) => {
    const allowed = ["0","1","2","3","4","5","6","7","8","9","Backspace","Delete","Tab","ArrowLeft","ArrowRight"]
    if (!allowed.includes(e.key) && !e.ctrlKey) e.preventDefault()
  }
  const mgsIsObject = (v) => v !== null && typeof v === "object" && !Array.isArray(v)
  const millisToMinutesAndSeconds = (ms) => {
    const m = Math.floor(ms / 60000)
    const s = ((ms % 60000) / 1000).toFixed(0)
    return `${m}:${s < 10 ? "0" : ""}${s}`
  }
  const formatBytes = (bytes, decimals = 2) => {
    if (!+bytes) return "0 Bytes"
    const k = 1000
    const sizes = ["B","KB","MB","GB","TB","PB","EB","ZB","YB"]
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`
  }
  const toFa = (n) => {
    const map = { "0":"0","1":"1","2":"2","3":"3","4":"4","5":"5","6":"6","7":"7","8":"8","9":"9" }
    return String(n).replace(/[0-9]/g, (d) => map[d])
  }
  const truncateMiddle = (str, maxLength = 200) => {
    if (str.length <= maxLength) return str
    const available = maxLength - 3
    const leftLength = Math.ceil(available / 2)
    const rightLength = Math.floor(available / 2)
    return `${str.substring(0, leftLength)}...${str.substring(str.length - rightLength)}`
  }
  function toMinutes(seconds) {
    return Math.floor(seconds / 60)
  }
  return { onlyNumbers, preventNonNumeric, mgsIsObject, millisToMinutesAndSeconds, toMinutes , formatBytes, toFa, truncateMiddle }
}