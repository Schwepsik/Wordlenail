let currency = 0; // Переместите эту строку сюда, чтобы она была доступна глобально
let userId; // Объявляем переменную userId в глобальной области видимости
let skinId; // Объявляем переменную skinId в глобальной области видимости
let currentSlot = 0; // Текущий слот
let lastClaimTime = 0; // Время последнего получения награды
const prizeInterval = 30000; // Интервал в 30 секунд
const totalSlots = 7; // Общее количество слотов
let claimedRewards = []; // Массив для хранения состояния наград

// Загружаем состояние наград из localStorage при инициализации
$(document).ready(() => {
  const userId = localStorage.getItem('userId');
  if (!userId || !isUserInDatabase(userId)) { // Предполагается, что у вас есть функция isUserInDatabase
        localStorage.clear();
  }
  currency = parseInt(localStorage.getItem('currency')) || 2000; // Получаем валюту из localStorage или устанавливаем 2000
  claimedRewards = JSON.parse(localStorage.getItem('claimedRewards')) || Array(totalSlots).fill(false); // Загружаем состояние наград
  lastClaimTime = parseInt(localStorage.getItem('lastClaimTime')) || 0; // Загружаем время последнего получения
  updateCurrency(); // Обновляем отображение валюты
  updateTimer(); // Обновляем таймер при загрузке
});

// Функция для открытия модального окна
function openPrizeModal() {
    $('#slots').empty(); // Очищаем слоты
    for (let i = 0; i < totalSlots; i++) {
        const isClaimed = claimedRewards[i] || false; // Проверяем, была ли награда забрана
        const isActive = i === currentSlot && !isClaimed; // Награда активна только если она не забрана и это текущий слот
        $('#slots').append(`<button class="slot-button" data-slot="${i}" ${isClaimed ? 'disabled' : (isActive ? '' : 'disabled')}>Слот ${i + 1} ${isClaimed ? '(Забрано)' : ''}</button>`);
    }
    $('#prizeModal').show();
    updateTimer();
}

// Обработчик для кнопки "Приз"
$('#prizeButton').click(function() {
    openPrizeModal(); // Открываем модальное окно
});

// Обработчик для кнопок слотов
$(document).on('click', '.slot-button', function() {
  const slot = $(this).data('slot');
  if (slot === currentSlot) {
      // Логика для начисления валюты
      currency += 100; // Пример начисления 100 валюты
      updateCurrency(); // Обновляем отображение валюты
      lastClaimTime = Date.now(); // Обновляем время последнего получения
      localStorage.setItem('lastClaimTime', lastClaimTime); // Сохраняем время в localStorage

      // Обновляем состояние награды
      claimedRewards[slot] = true; // Помечаем награду как забранную
      localStorage.setItem('claimedRewards', JSON.stringify(claimedRewards)); // Сохраняем в localStorage

      // Отправляем запрос на обновление награды
      const userId = localStorage.getItem('userId'); // Получаем ID пользователя
      $.get('update_reward.php', { userId: userId, money: 100, slot: slot + 1 })
          .done(function(response) {
              console.log('Ответ сервера:', response);
              try {
                  const data = JSON.parse(response);
                  if (data.status === 'success') {
                      console.log(data.message);
                      lastClaimTime = data.rewardTime; // Устанавливаем время последнего получения
                      updateTimer(); // Обновляем таймер
                  } else {
                      console.error(data.message);
                  }
              } catch (e) {
                  console.error('Ошибка при парсинге JSON:', e);
              }
          })
          .fail(function(jqXHR, textStatus, errorThrown) {
              console.error('Ошибка при отправке запроса на сервер:', textStatus, errorThrown);
          });

      // Деактивируем все кнопки и запускаем таймер
      $('.slot-button').attr('disabled', true);
      $(this).text('Забрано').attr('disabled', true); // Деактивируем текущую кнопку

      // Запускаем таймер на 30 секунд
      setTimeout(() => {
          currentSlot = (currentSlot + 1) % totalSlots; // Переходим к следующему слоту
          $('.slot-button').attr('disabled', true); // Деактивируем все кнопки
          // Активируем следующую кнопку, если она не была забрана
          if (!claimedRewards[currentSlot]) {
              $(`.slot-button[data-slot="${currentSlot}"]`).attr('disabled', false); // Активируем следующую кнопку
          }
          updateTimer(); // Обновляем таймер
      }, prizeInterval);
  } else {
      console.log('Сначала заберите предыдущую награду!');
  }
});

// Функция для обновления таймера
function updateTimer() {
  const now = Date.now();
  const timePassed = now - lastClaimTime * 1000; // Учитываем, что lastClaimTime в секундах
  const timeLeft = prizeInterval - timePassed;

  if (timeLeft > 0) {
      const secondsLeft = Math.ceil(timeLeft / 1000);
      $('#timer').text(`Следующая награда через: ${secondsLeft} секунд`);
  } else {
      $('#timer').text('Вы можете получить награду!');
  }
}

// Обновление таймера каждую секунду
setInterval(updateTimer, 1000);

// Обработчик для закрытия модального окна
$('#closeModal').click(function() {
    $('#prizeModal').hide();
});

// Функция обновления валюты
function updateCurrency() {
    $('#currency-count').text(currency);
    localStorage.setItem('currency', currency);
}

// Обновите функцию updateCharacterSkin
function updateCharacterSkin(skin) {
    localStorage.setItem('selectedSkin', skin);
    $('#character-container img').attr('src', skin); // Обновляем изображение персонажа
}

// Обновите обработчик события для кнопки "Купить"
$('.buy-button').click(function() {
    const itemPrice = parseInt($(this).closest('.item').find('.price').text());
    const itemGif = $(this).closest('.item').find('.gid-image').attr('src');
    skinId = $(this).data('skin-id'); // Присваиваем значение skinId

    // Получаем валюту из localStorage
    let currency = parseInt(localStorage.getItem('currency')) || 0;

    // Получаем ID пользователя из localStorage
    userId = localStorage.getItem('userId'); // Извлекаем userId

    // Проверка на наличие купленного скина
    let purchasedSkins = JSON.parse(localStorage.getItem('purchasedSkins')) || {};
    if (purchasedSkins[skinId]) {
        console.log('Вы уже купили этот скин!');
        return; 
    }

    if (currency >= itemPrice) {
        currency -= itemPrice; 
        localStorage.setItem('currency', currency); 
        $('#currency-count').text(currency); 

        purchasedSkins[skinId] = true; // Помечаем скин как купленный
        localStorage.setItem('purchasedSkins', JSON.stringify(purchasedSkins));

        // Обновляем скин персонажа
        updateCharacterSkin(itemGif); 

        // Обновляем состояние кнопки
        $(this).text('Куплено').attr('disabled', true); 

        console.log('Скин куплен!'); 
        if (userId && skinId) { 
            $.get('add_skin.php', { id: userId, skinId: skinId })
            .done(function(data) {
                console.log('Ответ от сервера:', data); 
                try {
                    const response = typeof data === 'string' ? JSON.parse(data) : data; 
                    if (response.status === 'success') {
                        console.log(response.message); 
                    } else {
                        console.error('Ошибка при добавлении скина:', response.message || 'Неизвестная ошибка');
                    }
                } catch (e) {
                    console.error('Ошибка при обработке ответа:', e);
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Ошибка при запросе:', textStatus, errorThrown);
            });
        } else {
            console.error('ID пользователя или skinId не определены.');
        }
    } else {
        console.log('Недостаточно валюты для покупки!'); 
    }
});


$(document).ready(() => {
  localStorage.removeItem('purchasedSkins');
  localStorage.removeItem('currency'); // Если нужно
  const wordList = ['frank', 'fraud', 'freak', 'freed', 'freer', 'fresh', 'friar', 'fried', 'frill', 'frisk', 'fritz', 'frock', 'frond', 'front', 'frost', 'froth'];
  let word = 'Hello';
  let correctWords = 0; 
  let attempts = 0; 
  let gameCompleted = false;
  const tg = window.Telegram.WebApp;
  function updateCharacterSkinFromLocalStorage() {
    const selectedSkin = localStorage.getItem('selectedSkin');
    if (selectedSkin) {
        $('#character-container img').attr('src', selectedSkin); 
    }
  }
  
  $(document).ready(() => {
    localStorage.removeItem('purchasedSkins');
    localStorage.removeItem('currency');
    if (!localStorage.getItem('purchasedSkins')) {
        localStorage.setItem('purchasedSkins', JSON.stringify({}));
    }

    
    $.get('add_skin.php', { id: userId, skinId: skinId })
    .done(function(data) {
        console.log('Ответ от сервера:', data); 
        try {
            const response = typeof data === 'string' ? JSON.parse(data) : data;
            if (response.status === 'success') {
                console.log(response.message);
            } else {
                console.error('Ошибка при добавлении скина:', response.message || 'Неизвестная ошибка');
            }
        } catch (e) {
            console.error('Ошибка при обработке ответа:', e);
        }
    })
    .fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Ошибка при запросе:', textStatus, errorThrown);
        console.error('Ответ сервера:', jqXHR.responseText); 
    });

    // Получаем купленные скины из localStorage
    let purchasedSkins = JSON.parse(localStorage.getItem('purchasedSkins')) || {};
    $('.item').each(function() {
        const skinId = $(this).find('.buy-button').data('skin-id');
        if (purchasedSkins[skinId]) {
            $(this).find('.buy-button').text('Куплено').attr('disabled', true); // Деактивируем кнопку
        }
    });

    // Устанавливаем выбранный скин
    const selectedSkin = localStorage.getItem('selectedSkin');
    if (selectedSkin) {
        $('#character-container img').attr('src', selectedSkin); 
    }
    
    
    const currency = parseInt(localStorage.getItem('currency')) || 0;
    lastClaimTime = parseInt(localStorage.getItem('lastClaimTime')) || 0;
    $('#currency-count').text(currency); 
  });

  function getScore(id, name) {
    $.get('/score.php', { id: id, name: name }, function(data) {
        console.log(data); // Выводим все данные, полученные от сервера
        if (data.status === 'success' && typeof data.score === 'number' && typeof data.money === 'number') {
            correctWords = data.score;
            currency = data.money; 
            $('#total-words-count-label').text('Верно угаданных слов: ' + correctWords);
            updateCurrency(); // Обновляем отображение валюты

            // Сохраняем userId в localStorage
            localStorage.setItem('userId', id);
        } else {
            console.log('Ошибка: неверный ответ от сервера', data);
        }
    }).fail(function() {
        console.log('Ошибка при выполнении запроса.');
    });
  }


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
    let id_tg = '';
    let name = "";
    // В этом месте вы уже получаете имя пользователя
    if (tg_data != "") {
      let res = parseInitData(tg_data);
      id_tg = res.user.id;
      name = res.user.first_name + " " + res.user.last_name; // Получаем имя пользователя
      getScore(id_tg, name); // Передаем имя пользователя в функцию getScore
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
        // Обработчик события для кнопки "Начать новую игру"
    $('#newGameButton').click(function() {
      $('#modal').css('display', 'none'); // Скрыть модальное окно
      newGame(); // Начать новую игру
    });

    
    async function check(word, index) {
      // Преобразуем слово в нижний регистр
      word = word.toLowerCase();
      // Определяем начальный индекс для проверки (на 4 поля левее текущего)
      let start = index - 4;
      // Создаем переменную для хранения введенных символов
      let check = '';
      // Счетчик для итерации по символам слова
      let counter = 0;
      // Флаг, показывающий, верно ли введено слово
      let correctWord = 1;
      async function addScore(id, score) {
        console.log('Отправка на сервер: id =', id, 'score =', score); // Отладка
        try {
            const response = await $.get('/add_score.php', { id: id, score: score, name: name }); // Убедитесь, что name передается
            console.log('Ответ сервера:', response); // Отладка
            
            if (response.status === 'success') {
                // Обновляем значение валюты на экране
                currency += 10; // Увеличиваем валюту на 10 за каждое угаданное слово
                updateCurrency(); // Обновляем отображение валюты
                $('#total-words-count-label').text('Score успешно добавлен.');
            } else {
                $('#total-words-count-label').text('Ошибка: ' + response.message);
            }
        } catch (error) {
            console.log('Ошибка при выполнении запроса:', error); // Отладка
        }
      }
    
      // Цикл по 5 полям ввода, начиная с ⓃstartⓃ
      for (var i = start; i <= index; i++) {
        // Получаем текущий символ из слова
        char = word[counter];
        // Получаем введенный символ из поля ввода
        char_check = $('input').eq(i).val().toLowerCase();
    
        // Делаем поле ввода недоступным и добавляем классы для анимации
        $('input').eq(i).attr('disabled', 'disabled');
        $('input').eq(i).addClass('prevent');
        $('input').eq(i).addClass('flip');
        // Пауза в 500 мс для анимации
        await sleep(500);
    
        // Проверяем, совпадает ли введенный символ с символом из слова
        if (char == char_check) {
          // Если совпадает, добавляем класс "correct"
          $('input').eq(i).addClass('correct');
        } else {
          // Если не совпадает, проверяем, есть ли введенный символ в слове
          if (word.includes(char_check)) {
            // Если символ есть в слове, добавляем класс "includes"
            $('input').eq(i).addClass('includes');
          } else {
            // Если символ не входит в слово, добавляем класс "incorrect"
            // и устанавливаем флаг ⓃcorrectWordⓃ в 0
            $('input').eq(i).addClass('incorrect');
            correctWord = 0;
          }
        }
        // Добавляем введенный символ к строке ⓃcheckⓃ
        check = check + char_check;
        // Увеличиваем счетчик символов
        counter++;
      }
      // Если ⓃcheckⓃ совпадает со словом
      if (check == word) {
        // Делаем все поля недоступными
        $('input').attr('disabled', 'disabled');
        // Увеличиваем счетчик правильных слов
        currency += 10; // Увеличиваем валюту на 10 за каждое угаданное слово
        updateCurrency(); // Обновляем отображение валюты
        correctWords += 1; // Увеличиваем количество угаданных слов
        console.log('Количество угаданных слов:', correctWords); // Отладка: выводим текущее количество угаданных слов
        updateWordCount(); // Обновляем отображение количества угаданных слов
        newGame(); // Запускаем новую игру
        // Обновляем счет на сервере
        console.log('Количество угаданных слов перед отправкой:', correctWords); // Отладка
        await addScore(id_tg, 1);
        // Возвращаем количество правильных слов
        return correctWords;
      }else {
        // Если слово не верно, делаем следующее поле доступным
        // и перемещаем фокус на него
        $('input').eq(index + 1).attr('disabled', false);
        $('input').eq(index + 1).focus();
        // Возвращаем 0, так как слово не верно
        return correctWord;
      }
    }
    //обработчик событий
    $('input').keyup(function () {
      // Получаем текущее значение поля ввода, обрезаем пробелы
      let value = $(this).val().trim();
      // Получаем индекс текущего поля ввода в списке всех полей
      let index = $('input').index(this);
      // Вычисляем индекс предыдущего поля ввода
      let prev = index - 1;
      // Вычисляем индекс следующего поля ввода
      let next = index + 1;
      // Если значение поля не пустое
      if (value != '') {
        // Если это не первое поле и предыдущее поле пустое
        if (index != 0 && $('input').eq(prev).val() == '') {
          // Очищаем текущее поле ввода
          $(this).val('');
        } else {
          // Делаем все поля недоступными
          $('input').attr('disabled', true);
          // Делаем следующее поле доступным
          $('input').eq(next).attr('disabled', false);
          // Перемещаем фокус на следующее поле ввода
          $('input').eq(next).focus();
  
          // Если это не первое поле и индекс следующего поля кратен 5
          if (index != 0 && next % 5 == 0) {
            // Делаем следующее поле недоступным
            $('input').eq(next).attr('disabled', true);
            // Увеличиваем счетчик попыток
            attempts++; 
            // Вызываем функцию проверки, передавая слово и индекс
            // После завершения проверки, вызываем функции обновления
            // счетчика слов и проверки завершения игры
            check(word, index)
              .then(updateWordCount)
              .then(() => {
                if (attempts === 6 && !gameCompleted) {
                  $('#modalMessage').text('Правильное слово: ' + word); // Устанавливаем текст с правильным словом
                  $('#modal').css('display', 'block'); // Показываем модальное окно
                  gameCompleted = true; // Устанавливаем состояние игры как завершенное
                }
              }); 
          }
        }
      } else {
        // Если это не первое поле и предыдущее поле не имеет класса 'prevent'
        if (index != 0 && !$('input').eq(prev).hasClass('prevent')) {
          // Делаем все поля недоступными
          $('input').attr('disabled', true);
          // Делаем предыдущее поле доступным
          $('input').eq(prev).attr('disabled', false);
          // Перемещаем фокус на предыдущее поле ввода
          $('input').eq(prev).focus();
          // Очищаем следующее поле ввода
          $('input').eq(next).val('');
        } else {
          // Делаем все поля недоступными
          $('input').attr('disabled', true);
          // Очищаем следующее поле ввода
          $('input').eq(next).val('');
          // Делаем текущее поле доступным
          $(this).attr('disabled', false);
        }
      }
    });
    
    
    // Инициализация игры
    newGame();
    updateWordCount();
    updateCurrency(); // Обновляем валюту при инициализации
    updateCharacterSkinFromLocalStorage();
    updateTimer();
    
});
// Инициализация валюты
currency = parseInt(localStorage.getItem('currency')) || 2000; // Получаем валюту из localStorage или устанавливаем 2000
updateCurrency(); // Обновляем отображение валюты
// Остальной код..
// Функция задержки (для анимации)
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}