const customInputRadio = document.getElementById("js-custom-amount-radio");
const customInputText = document.getElementById("js-custom-amount-text");

if (customInputRadio) {
  customInputRadio.addEventListener("click", () => {
    customInputText.focus();
  });
}

if (customInputText) {
  customInputText.addEventListener("focus", () => {
    customInputRadio.checked = true;
  });

  customInputText.addEventListener("change", (ev) => {
    customInputRadio.value = ev.target.value;
  });
}
