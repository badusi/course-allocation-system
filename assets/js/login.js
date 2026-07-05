document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.querySelector(".login-form")
  const loginBtn = document.querySelector(".login-btn")

  if (loginForm) {
    loginForm.addEventListener("submit", (e) => {
      loginBtn.textContent = "Signing In..."
      loginBtn.disabled = true
    })
  }

  // Demo credential click handlers
  const demoAccounts = document.querySelectorAll(".demo-account")
  demoAccounts.forEach((account) => {
    account.addEventListener("click", function () {
      const credentials = this.querySelector("span").textContent.split(" / ")
      document.getElementById("username").value = credentials[0]
      document.getElementById("password").value = credentials[1]
    })
  })
})
