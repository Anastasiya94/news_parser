# news_parser

Парсер новостей нескольких популярных сайтов.<br/>
Состоит из двух основных компонентов, сайта с лентой новостей и скрипта для парсинга новостных сайтов.<br/>
Оба компонента реализованы на php с использованием <code>codeigniter</code>.
Предусмотрен запуск на Windows (WAMP).

**Установка на сервер:**
1) pull
2) Установить composer (если еще не установлен) и добавить его в PATH, открыть cmd, зайти в директорию проекта из выполнить команду<br/>
```cmd
php composer.phar install
``` 
3) Установить Java SE версии 8+, если еще не установлена
4) Усановить веб-сервер связку (WAMP), после настройки сервера переместить директорию данного проекта в папку на которую будет ссылаться localhost
5) Создать таблицу в MySQL базе данных:
```sql
CREATE TABLE `articles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `description` text,
  `full_content` text NOT NULL,
  `pubDate` datetime DEFAULT NULL,
  `count_views` int(11) DEFAULT '0',
  `site` varchar(50) DEFAULT NULL,
  `enclosure` text,
  `link` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1591 DEFAULT CHARSET=utf8;
 ```
**Запуск парсера сайтов:**<br/>
1) открыть cmd, зайти в директорию проекта из выполнить команду<br/>
```cmd
java -jar selenium-server-standalone-3.6.0.jar -enablePassThrough false 
``` 
2) открыть новое окно cmd, зайти в директорию проекта из выполнить команду<br/>
```cmd
php index.php parser index
``` 
Подразумевается, что PHP.exe находится в PATH <br/>
После вышеописанных действий, во втором окне cmd можно будет следить за бесконечным процессом парсинга новостных сайтов и заполнения собственной БД полученными новостями (процесс повторяется со случайным интервалом 50 - 120 секунд).<br/>
Парсер использует <code>selenium</code> и <code>facebook/webdriver</code> биндинги для php.

**Проверка работы сайта:**
Если все предыдущие пункты были выполнены корректно, и парсер успел пройтись по RSS лентам из заполнить базу данных, то для проверки работоспособности сайта достаточно открыть в браузере адреc <code>localhost/news_parser</code>
 
