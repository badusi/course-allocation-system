document.addEventListener("DOMContentLoaded", () => {
  // Smooth scrolling for navigation links
  const navLinks = document.querySelectorAll('a[href^="#"]')
  navLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()
      const targetId = this.getAttribute("href")
      const targetSection = document.querySelector(targetId)
      if (targetSection) {
        targetSection.scrollIntoView({
          behavior: "smooth",
          block: "start",
        })
      }
    })
  })

  // Mobile menu toggle
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle")
  const navLinksContainer = document.querySelector(".nav-links")

  if (mobileMenuToggle) {
    mobileMenuToggle.addEventListener("click", function () {
      navLinksContainer.classList.toggle("active")
      this.classList.toggle("active")
    })
  }

  // Navbar scroll effect
  const navbar = document.querySelector(".landing-nav")
  let lastScrollTop = 0

  window.addEventListener("scroll", () => {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop

    if (scrollTop > 100) {
      navbar.style.background = "rgba(15, 15, 35, 0.95)"
      navbar.style.backdropFilter = "blur(20px)"
    } else {
      navbar.style.background = "rgba(15, 15, 35, 0.9)"
      navbar.style.backdropFilter = "blur(20px)"
    }

    // Hide/show navbar on scroll
    if (scrollTop > lastScrollTop && scrollTop > 100) {
      navbar.style.transform = "translateY(-100%)"
    } else {
      navbar.style.transform = "translateY(0)"
    }

    lastScrollTop = scrollTop
  })

  // Animated counters for statistics
  const observerOptions = {
    threshold: 0.5,
    rootMargin: "0px 0px -100px 0px",
  }

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const statValues = entry.target.querySelectorAll(".stat-value")
        statValues.forEach((statValue) => {
          const target = Number.parseInt(statValue.getAttribute("data-target"))
          animateCounter(statValue, target)
        })
        observer.unobserve(entry.target)
      }
    })
  }, observerOptions)

  const statsSection = document.querySelector(".stats-section")
  if (statsSection) {
    observer.observe(statsSection)
  }

  function animateCounter(element, target) {
    let current = 0
    const increment = target / 100
    const timer = setInterval(() => {
      current += increment
      if (current >= target) {
        current = target
        clearInterval(timer)
      }

      // Format numbers
      let displayValue = Math.floor(current)
      if (target >= 1000) {
        displayValue = (displayValue / 1000).toFixed(1) + "K"
      }
      if (target >= 1000000) {
        displayValue = (displayValue / 1000000).toFixed(1) + "M"
      }

      element.textContent = displayValue
    }, 20)
  }

  // Parallax effect for hero background
  window.addEventListener("scroll", () => {
    const scrolled = window.pageYOffset
    const parallaxElements = document.querySelectorAll(".bg-pattern")

    parallaxElements.forEach((element) => {
      const speed = 0.5
      element.style.transform = `translateY(${scrolled * speed}px)`
    })
  })

  // Add loading animation to feature cards
  const featureCards = document.querySelectorAll(".feature-card")
  const cardObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = "1"
          entry.target.style.transform = "translateY(0)"
        }
      })
    },
    { threshold: 0.1 },
  )

  featureCards.forEach((card) => {
    card.style.opacity = "0"
    card.style.transform = "translateY(30px)"
    card.style.transition = "all 0.6s ease"
    cardObserver.observe(card)
  })

  // Add hover effect to floating cards
  const floatingCards = document.querySelectorAll(".floating-card")
  floatingCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.1) translateY(-10px)"
    })

    card.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1) translateY(0)"
    })
  })

  // Add typing effect to hero title
  const heroTitle = document.querySelector(".hero-title")
  if (heroTitle) {
    const text = heroTitle.innerHTML
    heroTitle.innerHTML = ""
    let i = 0

    function typeWriter() {
      if (i < text.length) {
        heroTitle.innerHTML += text.charAt(i)
        i++
        setTimeout(typeWriter, 50)
      }
    }

    // Start typing effect after a delay
    setTimeout(typeWriter, 1000)
  }

  // Add scroll-triggered animations
  const animateOnScroll = document.querySelectorAll(".testimonial-card, .stat-box")
  const scrollObserver = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("animate-in")
        }
      })
    },
    { threshold: 0.1 },
  )

  animateOnScroll.forEach((element) => {
    scrollObserver.observe(element)
  })

  // Add CSS for scroll animations
  const style = document.createElement("style")
  style.textContent = `
        .animate-in {
            animation: slideInUp 0.6s ease forwards;
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    `
  document.head.appendChild(style)

  // Add interactive cursor effect
  const cursor = document.createElement("div")
  cursor.className = "custom-cursor"
  cursor.style.cssText = `
        position: fixed;
        width: 20px;
        height: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        pointer-events: none;
        z-index: 9999;
        transition: transform 0.1s ease;
        opacity: 0;
    `
  document.body.appendChild(cursor)

  document.addEventListener("mousemove", (e) => {
    cursor.style.left = e.clientX - 10 + "px"
    cursor.style.top = e.clientY - 10 + "px"
    cursor.style.opacity = "0.7"
  })

  document.addEventListener("mouseleave", () => {
    cursor.style.opacity = "0"
  })

  // Add click ripple effect
  document.addEventListener("click", (e) => {
    const ripple = document.createElement("div")
    ripple.style.cssText = `
            position: fixed;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.3) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9998;
            animation: rippleEffect 0.6s ease-out;
            left: ${e.clientX - 50}px;
            top: ${e.clientY - 50}px;
        `

    document.body.appendChild(ripple)

    setTimeout(() => {
      ripple.remove()
    }, 600)
  })

  // Add ripple animation
  const rippleStyle = document.createElement("style")
  rippleStyle.textContent = `
        @keyframes rippleEffect {
            0% {
                transform: scale(0);
                opacity: 1;
            }
            100% {
                transform: scale(1);
                opacity: 0;
            }
        }
    `
  document.head.appendChild(rippleStyle)
})
