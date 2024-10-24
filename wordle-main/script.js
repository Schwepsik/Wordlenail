$(document).ready(() => {
    const wordList = ['frank', 'fraud', 'freak', 'freed', 'freer', 'fresh', 'friar', 'fried', 'frill', 'frisk', 'fritz', 'frock', 'frond', 'front', 'frost', 'froth'];
    let word = 'Hello';
    let correctWords = 0; // Счетчик угаданных слов за все игры
    let attempts = 0; // Счетчик попыток 
    let gameCompleted = false;
    const tg = window.Telegram.WebApp;
  
    // Функция для установки нового слова
    function setGameWord() {
      let randomNum = Math.floor(Math.random() * 16);
      word = wordList[randomNum].toUpperCase();
      console.log(word);
      return word;
    }
    
    function parseInitData (initData) {
      const params = new URLSearchParams(initData);
      const userParam = params.get('user');
      const auth_date = params.get('auth_date');
      const query_id = params.get('query_id');
      const hash = params.get('hash');
      if (userParam) {
        const decodedUser = decodeURIComponent(userParam);
        return {
          user: JSON.parse(decodedUser) || '',
          auth_date: auth_date || '',
          query_id: query_id || '',
          hash: hash || '',
        };
      }
      return null;
    };

    const tg_data = tg.initData;
    if (tg_data != ""){
    let res = parseInitData(tg_data);
    alert(JSON.stringify(res));
    }

    // Функция обновления счетчика
    function updateWordCount() {
      $('#total-words-count').text(correctWords);
      $('#total-words-count-label').text("Верно угаданных слов: " + correctWords);
    }
  
    // Функция перезапуска игры
    function newGame() {
      attempts = 0; // Сброс счетчика попыток
      gameCompleted = false;
      setGameWord(); // Установка нового слова
      $('input').val(''); // Очистка полей ввода
      $('input').attr('disabled', true); // Отключение всех полей
      $('input').eq(0).attr('disabled', false); // Включение первого поля
  
      // Сброс стилей ячеек
      $('input').removeClass('correct includes incorrect prevent flip');
      $('input').attr('disabled', 'disabled');
      $('input').eq(0).attr('disabled', false); // Включение первого поля
      $('input').eq(0).focus(); // Установка фокуса на первую ячейку
    }
  
    // Функция проверки слова
    async function check(word, index) {
      word = word.toLowerCase();
      let start = index - 4;
      let check = '';
      let counter = 0;
      let correctWord = 1;
  
      for (var i = start; i <= index; i++) {
        char = word[counter];
        char_check = $('input').eq(i).val().toLowerCase();
  
        $('input').eq(i).attr('disabled', 'disabled');
        $('input').eq(i).addClass('prevent');
        $('input').eq(i).addClass('flip');
        await sleep(500);
  
        if (char == char_check) {
          $('input').eq(i).addClass('correct');
        } else {
          if (word.includes(char_check)) {
            $('input').eq(i).addClass('includes');
          } else {
            $('input').eq(i).addClass('incorrect');
            correctWord = 0;
          }
        }
  
        check = check + char_check;
        counter++;
      }
  
      if (check == word) {
        $('input').attr('disabled', 'disabled');
        correctWords += 1;
        newGame(); // Вызов newGame() после успешного слова
        return correctWords;
      } else {
        $('input').eq(index + 1).attr('disabled', false);
        $('input').eq(index + 1).focus();
        return correctWord;
      }
    }
  
    // Обработчик события 
    $('input').keyup(function () {
      let value = $(this).val().trim();
      let index = $('input').index(this);
      let prev = index - 1;
      let next = index + 1;
  
      if (value != '') {
        if (index != 0 && $('input').eq(prev).val() == '') {
          $(this).val('');
        } else {
          $('input').attr('disabled', true);
          $('input').eq(next).attr('disabled', false);
          $('input').eq(next).focus();
  
          if (index != 0 && next % 5 == 0) {
            $('input').eq(next).attr('disabled', true);
            attempts++; // Увеличиваем счетчик попыток
            // Вызываем check, но не выводим результат сразу
            check(word, index)
              .then(updateWordCount)
              .then(() => {
                if (attempts === 6 && !gameCompleted) {
                  // Игра завершена, если 6 попыток
                  
                  newGame(); 
                }
              }); 
          }
        }
      } else {
        if (index != 0 && !$('input').eq(prev).hasClass('prevent')) {
          $('input').attr('disabled', true);
          $('input').eq(prev).attr('disabled', false);
          $('input').eq(prev).focus();
          $('input').eq(next).val('');
        } else {
          $('input').attr('disabled', true);
          $('input').eq(next).val('');
          $(this).attr('disabled', false);
        }
      }
    });
  
    // Инициализация игры
    newGame();
    updateWordCount();
});

// Функция задержки (для анимации)
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}