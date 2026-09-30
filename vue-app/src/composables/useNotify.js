const ICONS = {
  success: '✓',
  error: '✕',
  warning: '!',
  info: ''
}
const activeNotifies = new Map()
let counter = 0

const getContainer = () => document.getElementById(`notifyContainer`)

function show(type, title, text, duration = 4000) {
  const container = getContainer()
  if (!container) return null

  const id = `notify-${++counter}`
  const isPermanent = duration === -1

  const el = document.createElement(`div`)
  el.className = `notify ${type}`
  el.dataset.id = id
  el.innerHTML = `
    <div class="notify-icon">${ICONS[type] ?? ""}</div>
    <div class="notify-body">
      <div class="notify-title">${title}</div>
      <div class="notify-text">${text}</div>
    </div>
    ${isPermanent ? "" : '<button class="notify-close">✕</button>'}
    <div class="notify-progress" style="animation-duration: ${duration}ms;"></div>
  `

  container.appendChild(el)
  activeNotifies.set(id, el)

  const closeBtn = el.querySelector(".notify-close")
  if (closeBtn) closeBtn.addEventListener("click", () => close(id))

  if (isPermanent) {
    const p = el.querySelector(".notify-progress")
    if (p) p.style.display = "none"
  } else {
    let timer = setTimeout(() => close(id), duration)
    el.addEventListener("mouseenter", () => {
      clearTimeout(timer)
      const p = el.querySelector(".notify-progress")
      if (p) p.style.animationPlayState = "paused"
    })
    el.addEventListener("mouseleave", () => {
      const p = el.querySelector(".notify-progress")
      if (p) p.style.animationPlayState = "running"
      timer = setTimeout(() => close(id), 1500)
    })
  }
  return id
}

function close(id) {
  const el = activeNotifies.get(id)
  if (!el) return
  activeNotifies.delete(id)
  el.classList.add("hide")
  el.addEventListener("animationend", () => el.remove())
}

function update(id, type, title, text, duration = 4000) {
  const el = activeNotifies.get(id)
  if (!el) return

  el.className = `notify ${type}`
  el.querySelector(".notify-icon").textContent = ICONS[type] ?? ""
  el.querySelector(".notify-title").textContent = title
  el.querySelector(".notify-text").textContent = text

  const p = el.querySelector(".notify-progress")
  let closeBtn = el.querySelector(".notify-close")

  if (duration === -1) {
    if (closeBtn) closeBtn.remove()
    if (p) { p.style.display = "none"; p.style.animation = "none" }
  } else {
    if (!closeBtn) {
      closeBtn = document.createElement("button")
      closeBtn.className = "notify-close"
      closeBtn.textContent = "x"
      el.insertBefore(closeBtn, p)
      closeBtn.addEventListener("click", () => close(id))
    }
    if (p && duration > 0) {
      p.style.display = ""
      p.style.animation = "none"
      void p.offsetHeight
      p.style.animation = `progress ${duration}ms linear forwards`
      setTimeout(() => close(id), duration)
    }
  }
}

function closeAll() {
  [...activeNotifies.keys()].forEach(close)
}

export function useNotify() {
  return {
    success: (t, x, d) => show("success", t, x, d),
    error:   (t, x, d) => show("error", t, x, d),
    warning: (t, x, d) => show("warning", t, x, d),
    info:    (t, x, d) => show("info", t, x, d),
    close, update, closeAll
  }
}