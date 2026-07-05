document.addEventListener("DOMContentLoaded", () => {
  // Logout functionality
  const logoutBtn = document.querySelector(".logout-btn")
  if (logoutBtn) {
    logoutBtn.addEventListener("click", (e) => {
      e.preventDefault()
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "/course-allocation-system/logout.php"
      }
    })
  }

  // Auto-refresh dashboard stats every 30 seconds
  setInterval(refreshStats, 30000)

  function refreshStats() {
    fetch("/api/dashboard-stats.php")
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          updateStatCards(data.stats)
        }
      })
      .catch((error) => console.error("Error refreshing stats:", error))
  }

  function updateStatCards(stats) {
    Object.keys(stats).forEach((key) => {
      const element = document.querySelector(`[data-stat="${key}"]`)
      if (element) {
        element.textContent = stats[key]
      }
    })
  }

  // Modal functionality
  const modals = document.querySelectorAll(".modal")
  const modalTriggers = document.querySelectorAll("[data-modal]")
  const closeBtns = document.querySelectorAll(".close-btn")

  modalTriggers.forEach((trigger) => {
    trigger.addEventListener("click", function (e) {
      e.preventDefault()
      const modalId = this.getAttribute("data-modal")
      const modal = document.getElementById(modalId)
      if (modal) {
        modal.classList.add("active")
      }
    })
  })

  closeBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      const modal = this.closest(".modal")
      if (modal) {
        modal.classList.remove("active")
      }
    })
  })

  // Close modal when clicking outside
  modals.forEach((modal) => {
    modal.addEventListener("click", function (e) {
      if (e.target === this) {
        this.classList.remove("active")
      }
    })
  })

  // Form validation
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const requiredFields = this.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.style.borderColor = "#ef4444"
        } else {
          field.style.borderColor = "rgba(255, 255, 255, 0.3)"
        }
      })

      if (!isValid) {
        e.preventDefault()
        showMessage("Please fill in all required fields", "error")
      }
    })
  })

  function showMessage(text, type = "info") {
    const message = document.createElement("div")
    message.className = `message ${type}`
    message.textContent = text

    const container = document.querySelector(".main-content")
    if (container) {
      container.insertBefore(message, container.firstChild)

      setTimeout(() => {
        message.remove()
      }, 5000)
    }
  }

  // Search functionality
  const searchInputs = document.querySelectorAll("[data-search]")
  searchInputs.forEach((input) => {
    input.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase()
      const targetSelector = this.getAttribute("data-search")
      const targets = document.querySelectorAll(targetSelector)

      targets.forEach((target) => {
        const text = target.textContent.toLowerCase()
        if (text.includes(searchTerm)) {
          target.style.display = ""
        } else {
          target.style.display = "none"
        }
      })
    })
  })

  // Export functionality
  const exportBtns = document.querySelectorAll(".btn-export")
  exportBtns.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      e.preventDefault()
      const format = this.getAttribute("data-format")
      const type = this.getAttribute("data-type")

      // Show loading state
      const originalText = this.textContent
      this.textContent = "Exporting..."
      this.disabled = true

      // Simulate export process
      setTimeout(() => {
        this.textContent = originalText
        this.disabled = false
        showMessage(`${type} exported successfully as ${format.toUpperCase()}`, "success")
      }, 2000)
    })
  })
})
