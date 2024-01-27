<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class SqlDumpProcessor
{
   static function processSqlDump($file)
   {
      // Читаем содержимое файла
      $fileContent = Storage::disk('public')->get($file->file_path);

      // Удаляем комментарии и разделяем на отдельные инструкции
      $statements = array_filter(array_map(
         'trim',
         explode(';', preg_replace('/\s*--.*[\r\n]|\s*\/\*[\s\S]*?\*\//', '', $fileContent))
      ));

      $tables = [];
      $inserts = [];

      foreach ($statements as $statement) {
         if (self::startsWith(strtolower($statement), 'create table')) {
            // Обработка инструкций создания таблицы
            $table = self::processCreateTable($statement);
            $tables[$table['name']] = $table;
         } elseif (self::startsWith(strtolower($statement), 'insert into')) {
            // Получаем название таблицы из инструкции INSERT INTO
            preg_match('/INSERT INTO `?([^ `]+)`?/', $statement, $tableNameMatches);
            $tableName = $tableNameMatches[1];

            // Обработка инструкций вставки с передачей структуры соответствующей таблицы
            $insert = self::processInsertInto($statement, $tables[$tableName] ?? []);
            $inserts[$tableName] = $insert; // Сохраняем данные вставки, сгруппированные по названию таблицы
         }
      }

      return [
         'tables' => $tables,
         'inserts' => $inserts
      ];
   }

   static function processCreateTable($statement)
   {
      // Удаляем переводы строк и лишние пробелы
      $statement = preg_replace('/\s+/', ' ', $statement);

      // Извлекаем название таблицы
      preg_match('/CREATE TABLE `?([^ `]+)`?/', $statement, $tableNameMatches);
      $tableName = $tableNameMatches[1];

      // Извлекаем определения полей
      preg_match('/\((.*)\)/s', $statement, $fieldsMatches);
      $fieldsDefinitions = $fieldsMatches[1];

      // Разделяем определения полей и ограничений
      $fields = [];
      foreach (explode(',', $fieldsDefinitions) as $fieldDefinition) {
         $fieldDefinition = trim($fieldDefinition);

         // Игнорируем ключи и ограничения
         if (
            self::startsWith(strtolower($fieldDefinition), 'primary key') ||
            self::startsWith(strtolower($fieldDefinition), 'unique key') ||
            self::startsWith(strtolower($fieldDefinition), 'key') ||
            self::startsWith(strtolower($fieldDefinition), 'index') ||
            self::startsWith(strtolower($fieldDefinition), 'constraint')
         ) {
            continue;
         }

         // Извлекаем имя поля, тип и дополнительные атрибуты
         preg_match('/`?([^ `]+)`? ([^ ]+)(.*)/', $fieldDefinition, $fieldMatches);
         $fieldName = $fieldMatches[1];
         $fieldType = $fieldMatches[2];
         $attributes = trim($fieldMatches[3]);

         // Обработка дополнительных атрибутов поля
         $isAutoIncrement = strpos($attributes, 'AUTO_INCREMENT') !== false;
         $isNull = strpos($attributes, 'NOT NULL') === false; // Предположим, что по умолчанию поля NULL, если NOT NULL не указано

         // Обработка значения по умолчанию
         preg_match("/DEFAULT '([^']*)'/", $attributes, $defaultMatches);
         $defaultValue = $defaultMatches[1] ?? null;

         // Добавляем информацию о поле в массив
         $fields[] = [
            'name' => $fieldName,
            'type' => $fieldType,
            'isNull' => $isNull,
            'isAutoIncrement' => $isAutoIncrement,
            'defaultValue' => $defaultValue,
            // Добавьте другие атрибуты по аналогии, если необходимо
         ];
      }

      return [
         'name' => $tableName,
         'fields' => $fields
      ];
   }


   static function mapDataToTableStructure($rowValues, $tableStructure)
   {
      $rowData = [];
      $fields = $tableStructure['fields'] ?? [];

      foreach ($fields as $index => $fieldInfo) {
         $fieldName = $fieldInfo['name'];
         $rowData[$fieldName] = $rowValues[$index] ?? null;
      }

      return $rowData;
   }

   static function processInsertInto($statement, $tableStructure)
   {
      // Удаляем переводы строк и лишние пробелы
      $statement = preg_replace('/\s+/', ' ', $statement);

      // Извлекаем название таблицы
      preg_match('/INSERT INTO `?([^ `]+)`?/', $statement, $tableNameMatches);
      $tableName = $tableNameMatches[1];

      // Извлекаем часть запроса с данными для вставки
      preg_match("/VALUES (.+)$/", $statement, $valuesMatches);
      $valuesPart = $valuesMatches[1];

      // Удаляем скобки в начале и конце и разделяем строки данных
      $valuesPart = trim($valuesPart, '()');
      $rows = explode('), (', $valuesPart);

      // Преобразование строк данных в массивы
      $data = [];
      foreach ($rows as $row) {
         // Обрабатываем каждую строку данных
         $row = trim($row, '()');

         // Разделяем значения в строке, учитывая кавычки
         preg_match_all("/(?<=,|\A)(?:'[^']*'|[^,]+)(?=,|\z)/", $row, $valuesMatches);
         $values = $valuesMatches[0] ?? [];

         // Очищаем значения и удаляем лишние кавычки
         $values = array_map(function ($value) {
            $value = trim($value);
            // Удаляем кавычки в начале и конце, если они есть
            if (self::startsWith($value, "'") && self::endsWith($value, "'")) {
               $value = substr($value, 1, -1);
            }
            return $value;
         }, $values);

         $data[] = $values;
      }

      $rowsData = [];
      //dd($data); 

      foreach ($data as $rowValues) {
         $rowData = self::mapDataToTableStructure($rowValues, $tableStructure);
         $rowsData[] = $rowData;
      }

      return [
         'table' => $tableName,
         'data' => $rowsData
      ];
   }



   static function endsWith($haystack, $needle)
   {
      $length = strlen($needle);
      if ($length == 0) {
         return true;
      }
      return (substr($haystack, -$length) === $needle);
   }

   static function startsWith($haystack, $needle)
   {
      $length = strlen($needle);
      return substr($haystack, 0, $length) === $needle;
   }


   function processSqlDumpPrint($file)
   {
      return  self::processSqlDump($file);
   }
}
