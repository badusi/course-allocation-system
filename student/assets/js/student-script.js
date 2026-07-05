// Tab Navigation Functionality
document.addEventListener("DOMContentLoaded", () => {
  // Smooth scrolling for better UX
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault()
      const target = document.querySelector(this.getAttribute("href"))
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Add hover effects and animations
  const statCards = document.querySelectorAll(".stat-card")
  statCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-4px) scale(1.02)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)"
    })
  })

  // Course card hover effects
  const courseCards = document.querySelectorAll(".course-card")
  courseCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-2px)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)"
    })
  })

  // Form validation
  const forms = document.querySelectorAll("form")
  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const requiredFields = form.querySelectorAll("[required]")
      let isValid = true

      requiredFields.forEach((field) => {
        if (!field.value.trim()) {
          isValid = false
          field.style.borderColor = "rgba(239, 68, 68, 0.5)"
        } else {
          field.style.borderColor = "rgba(255, 255, 255, 0.2)"
        }
      })

      if (!isValid) {
        e.preventDefault()
        alert("Please fill in all required fields.")
      }
    })
  })

  // Real-time search functionality (if search input exists)
  const searchInput = document.querySelector("#search")
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase()
      const searchableItems = document.querySelectorAll(".course-item, .announcement-item, .request-item")

      searchableItems.forEach((item) => {
        const text = item.textContent.toLowerCase()
        if (text.includes(searchTerm)) {
          item.style.display = "block"
        } else {
          item.style.display = "none"
        }
      })
    })
  }

  // Only initialize dashboard features if on dashboard page
  if (window.location.pathname.includes("student-dashboard.php")) {
    // Auto-refresh functionality for dashboard (every 5 minutes)
    setInterval(() => {
      refreshDashboardStats()
    }, 300000)

    // Show notifications on page load
    showNotifications()
  }
})

// Utility functions
function refreshDashboardStats() {
  fetch("api/get-student-stats.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.text()
    })
    .then((text) => {
      try {
        const data = JSON.parse(text)
        if (data.success) {
          updateStatistics(data.stats)
        }
      } catch (e) {
        console.log("Stats refresh not available - invalid JSON response")
      }
    })
    .catch((error) => {
      console.log("Stats refresh not available:", error.message)
    })
}

function updateStatistics(stats) {
  const statNumbers = document.querySelectorAll(".stat-number")

  if (stats.enrolled_courses !== undefined && statNumbers[0]) {
    statNumbers[0].textContent = stats.enrolled_courses
  }
  if (stats.pending_requests !== undefined && statNumbers[1]) {
    statNumbers[1].textContent = stats.pending_requests
  }
  if (stats.current_gpa !== undefined && statNumbers[2]) {
    statNumbers[2].textContent = Number.parseFloat(stats.current_gpa).toFixed(1)
  }
  if (stats.credits_earned !== undefined && statNumbers[3]) {
    statNumbers[3].textContent = stats.credits_earned
  }
}

function showNotifications() {
  fetch("api/get-notifications.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`)
      }
      return response.text()
    })
    .then((text) => {
      try {
        const data = JSON.parse(text)
        if (data.success && data.notifications && data.notifications.length > 0) {
          displayNotifications(data.notifications)
        }
      } catch (e) {
        console.log("Notifications not available - invalid JSON response")
      }
    })
    .catch((error) => {
      console.log("Notifications not available:", error.message)
    })
}

function displayNotifications(notifications) {
  notifications.forEach((notification) => {
    showToast(notification.message, notification.type)
  })
}

function showToast(message, type = "info") {
  const toast = document.createElement("div")
  toast.className = `toast toast-${type}`
  toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `

  // Add toast styles if not already present
  if (!document.querySelector("#toast-styles")) {
    const style = document.createElement("style")
    style.id = "toast-styles"
    style.textContent = `
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                background: rgba(255, 255, 255, 0.9);
                color: #333;
                padding: 1rem;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                z-index: 1000;
                display: flex;
                align-items: center;
                gap: 1rem;
                min-width: 300px;
                animation: slideIn 0.3s ease;
            }
            
            .toast-success { border-left: 4px solid #22c55e; }
            .toast-error { border-left: 4px solid #ef4444; }
            .toast-warning { border-left: 4px solid #f59e0b; }
            .toast-info { border-left: 4px solid #3b82f6; }
            
            .toast-content {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                flex: 1;
            }
            
            .toast-close {
                background: none;
                border: none;
                cursor: pointer;
                color: #666;
                padding: 0.25rem;
            }
            
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `
    document.head.appendChild(style)
  }

  document.body.appendChild(toast)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (toast.parentElement) {
      toast.remove()
    }
  }, 5000)
}

function getToastIcon(type) {
  switch (type) {
    case "success":
      return "check-circle"
    case "error":
      return "exclamation-circle"
    case "warning":
      return "exclamation-triangle"
    default:
      return "info-circle"
  }
}

// Course management functions
function enrollCourse(courseId) {
  if (confirm("Are you sure you want to enroll in this course?")) {
    fetch("actions/enroll-course.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ course_id: courseId }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.text()
      })
      .then((text) => {
        try {
          const data = JSON.parse(text)
          if (data.success) {
            showToast("Successfully enrolled in course!", "success")
            setTimeout(() => location.reload(), 1500)
          } else {
            showToast("Error: " + data.message, "error")
          }
        } catch (e) {
          console.error("Invalid JSON response:", text)
          showToast("Server error occurred", "error")
        }
      })
      .catch((error) => {
        console.error("Network error:", error)
        showToast("Network error occurred", "error")
      })
  }
}

function dropCourse(courseId) {
  if (confirm("Are you sure you want to drop this course? This action cannot be undone.")) {
    fetch("actions/drop-course.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ course_id: courseId }),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`)
        }
        return response.text()
      })
      .then((text) => {
        try {
          const data = JSON.parse(text)
          if (data.success) {
            showToast("Successfully dropped course!", "success")
            setTimeout(() => location.reload(), 1500)
          } else {
            showToast("Error: " + data.message, "error")
          }
        } catch (e) {
          console.error("Invalid JSON response:", text)
          showToast("Server error occurred", "error")
        }
      })
      .catch((error) => {
        console.error("Network error:", error)
        showToast("Network error occurred", "error")
      })
  }
}

// Export functions for global access
window.StudentDashboard = {
  updateStatistics: updateStatistics,
  showToast: showToast,
  enrollCourse: enrollCourse,
  dropCourse: dropCourse,
  refreshDashboardStats: refreshDashboardStats,
}