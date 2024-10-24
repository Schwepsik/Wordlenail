const audio = document.getElementById("myAudio");
const musicButton = document.getElementById("musicButton");

let isPlaying = false; // Флаг, чтобы отслеживать, играет ли музыка

musicButton.addEventListener("click", () => {
  if (isPlaying) {
    audio.pause();
    musicButton.textContent = "Включить музыку";
  } else {
    audio.play();
    musicButton.textContent = "Выключить музыку";
  }
  isPlaying = !isPlaying;
});

// Проверка, играет ли музыка при загрузке страницы
if (audio.paused) {
  musicButton.textContent = "Включить музыку";
} else {
  musicButton.textContent = "Выключить музыку";
  isPlaying = true;
}