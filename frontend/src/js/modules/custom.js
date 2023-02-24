const navButton = document.querySelector(".js-toggle-nav");
const mainNav = document.querySelector(".mainnavigation > nav");

navButton.addEventListener("click", (e) => {
  e.preventDefault();
  mainNav.classList.toggle("active");
});
