<?php

namespace EcclesiaCRM
{
// Sourced from http://stackoverflow.com/questions/147821/loading-sql-files-from-within-php
  class SQLUtils
  {
      // Prepare data for entry into MySQL database.
      // This function solves the problem of inserting a NULL value into MySQL since
      // MySQL will not accept 'NULL'.  One drawback is that it is not possible
      // to insert the character string "NULL" because it will be inserted as a MySQL NULL!
      // This will produce a database error if NULL's are not allowed!  Do not use this
      // function if you intend to insert the character string "NULL" into a field.
      public static function MySQLquote($sfield)
      {
          $sfield = trim($sfield);

          if ($sfield == 'NULL') {
              return 'NULL';
          } elseif ($sfield == "'NULL'") {
              return 'NULL';
          } elseif ($sfield == '') {
              return 'NULL';
          } elseif ($sfield == "''") {
              return 'NULL';
          } else {
              if ((mb_substr($sfield, 0, 1) == "'") && (mb_substr($sfield, mb_strlen($sfield) - 1, 1)) == "'") {
                  return $sfield;
              } else {
                  return "'".$sfield."'";
              }
          }
      }

      /**
     * Import SQL from file.
     *
     * @param string path to sql file
     */
      public static function sqlImport($file, $pdo)
      {
          $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 0);

          $delimiter = ';';
          $file = fopen($file, 'r');
          $isFirstRow = true;
          $isMultiLineComment = false;
          $sql = '';

          while (!feof($file)) {
              $row = fgets($file);

              // remove BOM for utf-8 encoded file
              if ($isFirstRow) {
                  $row = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $row);
                  $isFirstRow = false;
              }

              // 1. ignore empty string and comment row
              if (trim($row) == '' || preg_match('/^\s*(#|--\s)/sUi', $row)) {
                  continue;
              }

              // 2. clear comments
              $row = trim(self::clearSQL($row, $isMultiLineComment));

              // 3. parse delimiter row
              if (preg_match('/^DELIMITER\s+[^ ]+/sUi', $row)) {
                  $delimiter = preg_replace('/^DELIMITER\s+([^ ]+)$/sUi', '$1', $row);
                  continue;
              }

              // 4. separate sql queries by delimiter
              $offset = 0;
              while (strpos($row, $delimiter, $offset) !== false) {
                  $delimiterOffset = strpos($row, $delimiter, $offset);
                  if (self::isQuoted($delimiterOffset, $row)) {
                      $offset = $delimiterOffset + strlen($delimiter);
                  } else {
                      $sql = trim($sql.' '.trim(mb_substr($row, 0, $delimiterOffset)));
                      self::query($sql, $pdo);
                      $row = mb_substr($row, $delimiterOffset + strlen($delimiter));
                      $offset = 0;
                      $sql = '';
                  }
              }
              $sql = trim($sql.' '.$row);
          }
          if (strlen($sql) > 0) {
              self::query($row, $pdo);
          }

          fclose($file);

          $pdo->setAttribute(\PDO::ATTR_AUTOCOMMIT, 1);
      }

      /**
       * Remove comments from sql.
       *
       * @param string sql
       * @param bool is multicomment line
       *
       * @return string
       */
      private static function clearSQL($sql, &$isMultiComment)
      {
          if ($isMultiComment) {
              if (preg_match('#\*/#sUi', $sql)) {
                  $sql = preg_replace('#^.*\*/\s*#sUi', '', $sql);
                  $isMultiComment = false;
              } else {
                  $sql = '';
              }
              if (trim($sql) == '') {
                  return $sql;
              }
          }

          $offset = 0;
          while (preg_match('{--\s|#|/\*[^!]}sUi', $sql, $matched, PREG_OFFSET_CAPTURE, $offset)) {
              list($comment, $foundOn) = $matched[0];
              if (self::isQuoted($foundOn, $sql)) {
                  $offset = $foundOn + strlen($comment);
              } else {
                  if (mb_substr($comment, 0, 2) == '/*') {
                      $closedOn = strpos($sql, '*/', $foundOn);
                      if ($closedOn !== false) {
                          $sql = mb_substr($sql, 0, $foundOn).mb_substr($sql, $closedOn + 2);
                      } else {
                          $sql = mb_substr($sql, 0, $foundOn);
                          $isMultiComment = true;
                      }
                  } else {
                      $sql = mb_substr($sql, 0, $foundOn);
                      break;
                  }
              }
          }

          return $sql;
      }

      /**
       * Check if "offset" position is quoted.
       *
       * @param int    $offset
       * @param string $text
       *
       * @return bool
       */
      private static function isQuoted($offset, $text)
      {
          if ($offset > strlen($text)) {
              $offset = strlen($text);
          }

          $isQuoted = false;
          for ($i = 0; $i < $offset; $i++) {
              if ($text[$i] == "'") {
                  $isQuoted = !$isQuoted;
              }
              if ($text[$i] == '\\' && $isQuoted) {
                  $i++;
              }
          }

          return $isQuoted;
      }

      private static function query($sql, $pdo)
      {
          if (preg_match("/DEFINER\s*=.*@.*/", $sql)) {
               return;
          }
          if (!$query = $pdo->query($sql)) {
              throw new \Exception("Cannot execute request to the database {$sql}: ".$pdo->error);
          }
      }
  }

}
