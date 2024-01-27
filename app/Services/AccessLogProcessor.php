<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class AccessLogProcessor
{
   static function processFile($file)
   {
      $logEntries = [];

      $isAccessLog = $file->file_type === 'apache_access';

      // Открытие потока для чтения файла
      $stream = Storage::disk('public')->readStream($file->file_path);

      if ($stream) {
         while (!feof($stream)) {
            $line = fgets($stream);

            // Удаление пустых строк и продолжение, если строка пуста
            if (trim($line) === '') {
               continue;
            }

            // Выбор функции парсинга в зависимости от типа файла
            $parsedLine = $isAccessLog ? self::parseLine($line) : self::parseErrorLine($line);
            if ($parsedLine) {
               $logEntries[] = $parsedLine;
            }
         }

         fclose($stream);
      }

      return $logEntries;
   }

   static function parseLine($line)
   {
      // Основной шаблон
      $mainPattern = '/(\S+) (\S+) (\S+) \[([\w\/:]+) (\+\d{4})\] "(\S+) (.+?) (\S+)" (\d{3}) (\d+) "([^"]*)" "([^"]*)"/';
      preg_match($mainPattern, $line, $matches);

      if ($matches && count($matches) === 13) {
         // Сдвиг массива и передача в функцию fillData без изменений
         return self::fillData(array_slice($matches, 1));
      }

      // Альтернативный шаблон
      $altPattern = '/(\S+) (\S+) (\S+) \[([\w\/:]+) (\+\d{4})\] "([^"]*)" (\d{3}) (\d+) "([^"]*)" "([^"]*)"/';
      preg_match($altPattern, $line, $matches);

      if ($matches && count($matches) === 11) {
         $data = array_slice($matches, 1);
         // Вставляем null значения для отсутствующих данных
         array_splice($data, 5, 0, [null, null, null]); // Добавляем три null значения начиная с индекса 5 ('method', 'url', 'protocol')
         return self::fillData($data);
      }

      // Если ни один шаблон не подошел
      dd([$line, $matches]);
      return null; // Не удалось распознать строку
   }

   static function fillData($data)
   {
      return [
         'ip' => $data[0],
         'identity' => $data[1],
         'userid' => $data[2],
         'datetime' => $data[3],
         'timezone' => $data[4],
         'method' => $data[5],
         'url' => $data[6],
         'protocol' => $data[7],
         'status' => $data[8],
         'size' => $data[9],
         'referer' => $data[10],
         'user_agent' => $data[11]
      ];
   }



   static function parseErrorLine($line)
   {
      $pattern = '/^\[([^\]]+)\] \[([\w:]+)\] \[pid (\d+)\]( \[client ([^\]]+)\])? (.*)$/';
      preg_match($pattern, $line, $matches);

      if ($matches && count($matches) >= 6) {
         $errorMessage = $matches[6];

         $errorDetails = [
            'datetime' => $matches[1],
            'errorLevel' => $matches[2],
            'module' => $matches[2],
            'pid' => $matches[3],
            'client' => $matches[5] ?? null,
            'message' => $errorMessage,
            'errors' => []
         ];

         // Обработка вложенных PHP сообщений об ошибке
         if (preg_match_all('/PHP (Notice|Warning|Error|Deprecated):  (.+?)( in \/[^ ]+ on line (\d+)|$)/', $errorMessage, $phpErrorMatches, PREG_SET_ORDER)) {
            foreach ($phpErrorMatches as $phpError) {
               $fileAndLine = isset($phpError[3]) ? explode(' on line ', trim($phpError[3], ' in')) : [null, null];
               $errorDetails['errors'][] = [
                  'type' => $phpError[1], // Notice, Warning, Error, Deprecated
                  'message' => $phpError[2],
                  'file' => $fileAndLine[0], // Файл, в котором произошла ошибка
                  'line' => isset($fileAndLine[1]) ? intval($fileAndLine[1]) : null // Номер строки, на которой произошла ошибка
               ];
            }
         }

         // Обработка сообщений о таймауте
         if (preg_match('/The timeout specified has expired: \[client (\S+):(\d+)\] (.+)/', $errorMessage, $timeoutMatches)) {
            $errorDetails['errors'][] = [
               'type' => 'Timeout',
               'clientIP' => $timeoutMatches[1], // IP клиента
               'clientPort' => $timeoutMatches[2], // Порт клиента
               'message' => $timeoutMatches[3], // Сообщение об ошибке
            ];
         }

         return $errorDetails;
      }

      return null; // Не удалось распознать строку
   }


   public function processAccessLogPrint($file)
   {
     return self::processFile($file);
   }
}
