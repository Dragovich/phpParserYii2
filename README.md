# Парсер вузов с [данной страницы](https://www.princetonreview.com/college-search?ceid=cp-1022984)

Сайт написан на Yii2, Symphony

Для запуска требуется:
 - запустить `composer install`
 - подключить базу данных с названием `parser` и двумя таблица: `parser_schools` и `parser_schools_description`
 - запустить `runCron.sh` для старта парсинга